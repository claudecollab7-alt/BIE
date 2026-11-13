<?PHP
ob_start();
session_start();
require_once("inc/common/userclass.php");
// require_once ("inc/common/dbrowiterator.php");	

isAdmin();

$conn = new dbconnect();
$dbconn = new dbhandler();

// if (!isset($_REQUEST['branch_id']) || $_REQUEST['branch_id'] == '') {
//     $_REQUEST['branch_id'] = 2	;
// //}
// 2026-09-22 Current Stock made read only, price save no longer writes qty
// 2026-09-24 csrf token check + transaction rollback on all post handlers
// 2026-10-06 csrf token added to the multi uom form too - its save and update were always rejected
// 2026-10-06 multi uom rows have no min / max discount field - bind the column default 0 instead of null
// 2026-10-07 posted row counts via fnRowCount() - count() on a single value is fatal on php 8
// 2026-10-13 _userid (never set at login) replaced by _user_id
// 2026-10-16 margin % on cost price (was on item price); selling price changes only when margin or selling is edited
// 2026-11-11 main form check used field names that do not exist; unit prices refuse text and a negative selling price
// 2026-11-11 extra unit prices: all branches per unit in one form, saved to tbl_item_uom_price (was a new history row per save, read oldest first; the form posted the main form)

//  ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);

// 2026-11-11 price per extra unit: one current row per item / unit / branch in tbl_item_uom_price,
// each change also written to tbl_multiuom_itemprice_history (old / new values)
if (isset($_POST['SAVE_UOM'])) {
	$item_id = (int)$_POST['item_id'];
	if (!csrf_check('itemprice_history')) {
		csrf_fail('mst_itemprice_history.php?item_id=' . $item_id);
	}
	try {
		db_begin($conn);
		$uoms = fnItemExtraUoms($conn, $item_id);
		$branches = $conn->query("SELECT branch_id, branch_name FROM mst_branch WHERE branch_status = 1 ORDER BY branch_id")->fetchAll(PDO::FETCH_OBJ);
		$now = date('Y-m-d H:i:s');
		$user = (int)$_SESSION['_user_id'];
		$changed = 0;
		$errors = array();
		foreach ($uoms as $uom_id => $u) {
			foreach ($branches as $b) {
				$r = isset($_POST['up'][$uom_id][$b->branch_id]) ? $_POST['up'][$uom_id][$b->branch_id] : null;
				if (!is_array($r)) {
					continue;
				}
				$price = trim($r['price']);
				$disc = trim($r['discount']);
				$sell = trim($r['selling']);
				if ($price === '' && $disc === '' && $sell === '') {
					continue;	// row left empty - not set
				}
				// 2026-11-11 plain numbers only (a pasted 100abc was read as 100), selling price not negative
				$num = '/^\d+(\.\d+)?$/';
				if (!preg_match($num, $price) || ($disc !== '' && !preg_match($num, $disc)) || ($sell !== '' && !preg_match($num, $sell))) {
					$errors[] = $u->uom_name . ' / ' . $b->branch_name;
					continue;
				}
				$price = round((float)$price, 2);
				$disc = round((float)$disc, 2);
				$sell = round((float)$sell, 2);
				$cost = round($price - $price * $disc / 100, 2);	// same as js/price_calc.js
				if ($price <= 0 || $disc < 0 || $disc >= 100 || $cost <= 0) {
					$errors[] = $u->uom_name . ' / ' . $b->branch_name;
					continue;
				}
				$margin = ($sell > 0) ? round(($sell - $cost) / $cost * 100, 2) : (is_numeric(trim($r['margin'])) ? round((float)$r['margin'], 2) : 0);
				$old = fnItemUomPrice($conn, $item_id, $uom_id, $b->branch_id);
				if ($old && (float)$old->item_price == $price && (float)$old->item_discount == $disc && (float)$old->cost_price == $cost
					&& (float)$old->selling_price == $sell && (float)$old->margin_percent == $margin) {
					continue;	// no change
				}
				if ($old) {
					$st = $conn->prepare("UPDATE tbl_item_uom_price SET item_price = ?, item_discount = ?, cost_price = ?, selling_price = ?, margin_percent = ?,
						modify_by = ?, modify_dtm = ? WHERE uom_price_id = ?");
					$st->execute(array($price, $disc, $cost, $sell, $margin, $user, $now, $old->uom_price_id));
				} else {
					$st = $conn->prepare("INSERT INTO tbl_item_uom_price (item_id, uom_id, branch_id, item_price, item_discount, cost_price, selling_price, margin_percent,
						created_by, created_dtm, modify_by, modify_dtm) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
					$st->execute(array($item_id, $uom_id, $b->branch_id, $price, $disc, $cost, $sell, $margin, $user, $now, $user, $now));
				}
				$st = $conn->prepare("INSERT INTO tbl_multiuom_itemprice_history (item_id, branch_id, new_price, new_discount, new_cost_price, new_selling_price,
					new_margin_percent, new_item_uom, old_price, old_discount, old_cost_price, old_selling_price, old_margin_percent, old_item_uom, created_dtm, created_by)
					VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
				$st->execute(array(
					$item_id, $b->branch_id, $price, $disc, $cost, $sell, $margin, $u->uom_name,
					$old ? $old->item_price : 0, $old ? $old->item_discount : 0, $old ? $old->cost_price : 0, $old ? $old->selling_price : 0,
					$old ? $old->margin_percent : 0, $old ? $u->uom_name : '', $now, $user
				));
				$changed++;
			}
		}
		if (count($errors) > 0) {
			throw new Exception('Enter numbers only - a price above 0, a discount below 100 and a selling price not below 0 - for: ' . implode(', ', $errors) . '. Nothing was saved.');
		}
		db_commit($conn);
		$_SESSION['_msg'] = $changed > 0 ? "Unit prices saved ($changed changed)..!" : "No unit price was changed..!";
	} catch (Exception $e) {
		if (strpos($e->getMessage(), 'Enter numbers only') !== 0) {
			fnLogError($e);	// a wrong entry is not an error to log
		}
		db_rollback($conn);
		$_SESSION['_msg_err'] = str_replace("'", "", $e->getMessage());
	}
	header("location:mst_itemprice_history.php?item_id=" . $item_id);
	die();
}

if (isset($_POST['SAVE1'])) {
	if (!csrf_check('itemprice_history')) {
		csrf_fail('mst_itemprice_history_list.php');
	}
	try {
		db_begin($conn);
		$result = $conn->query("SELECT * FROM mst_branch WHERE branch_id = '" . $_REQUEST['branch_id'] . "' ");
		if ($result->rowCount() > 0) {
			$obj = $result->fetch(PDO::FETCH_OBJ);
		}
		$result1 = $conn->query("SELECT * FROM tbl_item_details WHERE item_id = '" . $_REQUEST['item_id'] . "' ");
		if ($result1->rowCount() > 0) {
			$res = $result1->fetch(PDO::FETCH_OBJ);
		}
		// $update_id = $_REQUEST['item_id'];

		$result2 = $conn->query("SELECT 
		" . $obj->branch_item_maq . " as item_max_qty , 
		" . $obj->branch_stock_field . " as branch_stock_field, 
		" . $obj->branch_item_price . " as item_price, 
		" . $obj->branch_item_discount . " as item_discount,
		" . $obj->branch_item_min_discount . " as item_min_discount,
		" . $obj->branch_item_max_discount . " as item_max_discount,
		" . $obj->branch_item_cost_price . " as item_cost_price, 
		" . $obj->branch_item_selling_price . " as item_selling_price, 
		" . $obj->branch_item_margin . " as item_branch_margin,  
		" . $obj->branch_item_msq . " as item_min_qty, 
		" . $obj->branch_item_moq . " as item_order_min_qty 
		FROM tbl_item_stock WHERE item_id = " . $_REQUEST['item_id']);

		if ($result2->rowCount() > 0) {
			$old = $result2->fetch(PDO::FETCH_OBJ);
		} else {
			$stmt_stock = $conn->prepare("INSERT INTO tbl_item_stock (item_id) VALUES (:item_id)");
			$stmt_stock->execute(array(':item_id' => $_REQUEST['item_id']));

			$old = (object) array(
				'item_max_qty' => 0,
				'branch_stock_field' => 0,
				'item_price' => 0,
				'item_discount' => 0,
				'item_min_discount' => 0,
				'item_max_discount' => 0,
				'item_cost_price' => 0,
				'item_selling_price' => 0,
				'item_branch_margin' => 0,
				'item_min_qty' => 0,
				'item_order_min_qty' => 0
			);
		}

		if (
			$_REQUEST['branch_new_price'] != $obj->branch_new_price || $_REQUEST['branch_stock_field'] != $old->branch_stock_field || $_REQUEST['branch_new_discount'] != $obj->branch_new_discount || $_REQUEST['branch_new_cost_price'] != $obj->branch_new_cost_price ||
			$_REQUEST['branch_new_selling_price'] != $obj->branch_new_selling_price || $_REQUEST['margin_percent'] != $obj->branch_new_margin || $_REQUEST['branch_new_msq'] != $obj->branch_new_msq || $_REQUEST['branch_new_maq'] != $obj->branch_new_maq ||
			$_REQUEST['branch_new_moq'] != $obj->branch_new_moq || $_REQUEST['new_item_uom'] != $obj->branch_new_item_uom || $_REQUEST['new_item_hsn'] != $obj->branch_new_item_hsn
		) {

			$_REQUEST['created_dtm'] = date('Y-m-d H:i:s');
			$_REQUEST['created_by'] = $_SESSION['_user_id'];

			$stmt = null;
			$stmt = $conn->prepare("INSERT INTO tbl_itemprice_history (item_id, branch_id,  $obj->branch_new_price, $obj->branch_new_discount, $obj->branch_new_min_discount, $obj->branch_new_max_discount, $obj->branch_new_cost_price, $obj->branch_new_selling_price, $obj->branch_new_margin, $obj->branch_new_item_curr_stock, $obj->branch_new_msq, $obj->branch_new_maq, $obj->branch_new_moq, $obj->branch_new_item_uom, 
			$obj->branch_new_item_hsn, $obj->branch_old_price, $obj->branch_old_discount, $obj->branch_old_min_discount, $obj->branch_old_max_discount, $obj->branch_old_cost_price, $obj->branch_old_selling_price, $obj->branch_old_margin,  $obj->branch_old_item_curr_stock,  $obj->branch_old_msq, $obj->branch_old_maq, $obj->branch_old_moq, $obj->branch_old_item_hsn, $obj->branch_old_item_uom, created_dtm, created_by)
			VALUES (:item_id, :branch_id, :new_price, :new_discount, :new_min_discount, :new_max_discount, :new_cost_price, :new_selling_price, :new_item_margin,  :branch_stock_field, :new_msq, 
			:new_maq, :new_moq, :item_uom, :item_hsn, :old_price, :old_discount, :old_min_discount, :old_max_discount,
			 :old_cost_price, :old_selling_price, :old_item_margin,  :old_branch_stock_field, :old_msq, :old_maq, :old_moq, :old_item_uom, :old_item_hsn, :created_dtm, :created_by)");
			$data = array(
				':item_id' => $_REQUEST['item_id'],
				':branch_id' =>  $_REQUEST['branch_id'],
				':new_price' => $_REQUEST['branch_new_price'],
				':new_discount' => $_REQUEST['branch_new_discount'],
				':new_min_discount' => $_REQUEST['branch_new_min_discount'],
				':new_max_discount' => $_REQUEST['branch_new_max_discount'],
				':new_cost_price' => $_REQUEST['branch_new_cost_price'],
				':new_selling_price' => $_REQUEST['branch_new_selling_price'],
				':new_item_margin' => $_REQUEST['margin_percent'],
				':branch_stock_field' => $_REQUEST['branch_stock_field'],
				':new_msq' => $_REQUEST['branch_new_msq'],
				':new_maq' => $_REQUEST['branch_new_maq'],
				':new_moq' => $_REQUEST['branch_new_moq'],
				':item_uom' => $_REQUEST['new_item_uom'],
				':item_hsn' => $_REQUEST['new_item_hsn'],
				':old_price' => $old->item_price,
				':old_discount' => $old->item_discount,
				':old_min_discount' => $old->item_min_discount,
				':old_max_discount' => $old->item_max_discount,
				':old_cost_price' => $old->item_cost_price,
				':old_selling_price' => $old->item_selling_price,
				':old_item_margin' => $old->item_branch_margin,
				':old_branch_stock_field' => $old->branch_stock_field,
				':old_msq' => $old->item_min_qty,
				':old_maq' => $old->item_max_qty,
				':old_moq' => $old->item_order_min_qty,
				':old_item_uom' => $res->item_uom,
				':old_item_hsn' => $res->item_hsn,
				':created_dtm' => $_REQUEST['created_dtm'],
				':created_by' => $_REQUEST['created_by']
			);
			$stmt->execute($data);

			// print_r($data);
			// die();

			$stmt2 = $conn->prepare("UPDATE tbl_item_details SET item_uom = :item_uom, item_hsn = :item_hsn WHERE item_id = :item_id");
			$data2 = array(
				':item_id' => $_REQUEST['item_id'],
				':item_uom' => $_REQUEST['new_item_uom'],
				':item_hsn' => $_REQUEST['new_item_hsn']
			);
			$stmt2->execute($data2);

			$update_id = $_REQUEST['item_id'];
			if ($update_id > 0) {


				$stmt1 = null;
				$stmt1 = $conn->prepare("UPDATE tbl_item_stock SET 
				" . $obj->branch_item_price . " = :branch_item_price, 
				" . $obj->branch_item_discount . " = :branch_item_discount,  
				" . $obj->branch_item_min_discount . " = :branch_item_min_discount, 
				" . $obj->branch_item_max_discount . " = :branch_item_max_discount,
				" . $obj->branch_item_cost_price . " = :branch_item_cost_price,
				" . $obj->branch_item_selling_price . " = :branch_item_selling_price, 
				" . $obj->branch_item_margin . " = :branch_item_margin, 
				" . $obj->branch_item_msq . " = :branch_item_msq, 
				" . $obj->branch_item_maq . " = :branch_item_maq,
				" . $obj->branch_item_moq . " = :branch_item_moq 
				WHERE item_id = :item_id");

				$data1 = array(
					':item_id' => $update_id,
					':branch_item_price' => $_REQUEST['branch_new_price'],
					':branch_item_discount' => $_REQUEST['branch_new_discount'],
					':branch_item_min_discount' => $_REQUEST['branch_new_min_discount'],
					':branch_item_max_discount' => $_REQUEST['branch_new_max_discount'],
					':branch_item_cost_price' => $_REQUEST['branch_new_cost_price'],
					':branch_item_selling_price' => $_REQUEST['branch_new_selling_price'],
					':branch_item_margin' => $_REQUEST['margin_percent'],
					':branch_item_msq' => $_REQUEST['branch_new_msq'],
					':branch_item_maq' => $_REQUEST['branch_new_maq'],
					':branch_item_moq' => $_REQUEST['branch_new_moq']

				);
				$stmt1->execute($data1);
				//print_r($data1);
				//	 echo 1;

				// stock is not editable here, use Stock Adjustment. Anything that still
				// arrives goes through the ledger instead of being written silently.
				if (isset($_REQUEST['branch_stock_field']) && $_REQUEST['branch_stock_field'] !== '') {
					fnSetStockToQty($conn, $update_id, $_REQUEST['branch_stock_field'], array(
						'branch_id'  => $_SESSION['_user_branch'],
						'trans_type' => STOCK_TRANS_ADJ,
						'trans_id'   => 0,
						'item_price' => $_REQUEST['branch_new_cost_price'],
						'remarks'    => 'Stock changed from Item Price History screen'
					));
				}
			}
			//print_r($data1);
			//die();




			$_SESSION['_msg'] = "Item Price History successfully saved..!";
		}
		db_commit($conn);
	} catch (Exception $e) {
		fnLogError($e);
		db_rollback($conn);
		$str = filter_var($e->getMessage(), FILTER_SANITIZE_STRING);
		echo $_SESSION['_msg_err'] = $str;
	}
	header("location:mst_itemprice_history_list.php");
	die();
}


/*
if(isset($_POST['UPDATE']))
{
    if (!csrf_check('itemprice_history')) {
    	csrf_fail('mst_itemprice_history_list.php');
    }
	$update_id = $_REQUEST['txtHid'];
	try
		{
		db_begin($conn);
			$result = $conn->query("SELECT * FROM tbl_item_details WHERE item_id = ".$_REQUEST['item_id']);	
			if ($result->rowCount()>0)
			{
				$obj = $result->fetch(PDO::FETCH_OBJ);	
			}
				
				
			if($_REQUEST['item_price'] != $obj->item_price || $_REQUEST['item_discount'] != $obj->item_discount || $_REQUEST['item_cost_price'] != $obj->item_cost_price || $_REQUEST['item_selling_price'] != $obj->item_selling_price || $_REQUEST['item_min_qty'] != $obj->item_min_qty || $_REQUEST['item_order_min_qty'] != $obj->item_order_min_qty || $_REQUEST['item_max_qty'] != $obj->item_max_qty)
			{
				$_REQUEST['updated_dtm'] = date('Y-m-d H:i:s');
				$_REQUEST['updated_by'] = $_SESSION['_user_id'];
				
			
				$stmt = null;				
				$stmt = $conn->prepare("UPDATE tbl_itemprice_history SET item_id = :item_id, item_price = :item_price, item_discount = :item_discount, item_cost_price = :item_cost_price, item_selling_price = :item_selling_price, item_min_qty = :item_min_qty, item_order_min_qty = :item_order_min_qty, item_max_qty = :item_max_qty, updated_dtm = :updated_dtm, updated_by = :updated_by  WHERE auto_id = :auto_id");		
				$data = array(	
				':auto_id' => $update_id,
				':item_id' => $_REQUEST['item_id'],
				':item_price' => $_REQUEST['item_price'],
				':item_discount' => $_REQUEST['item_discount'],			
				':item_cost_price' => $_REQUEST['item_cost_price'],
				':item_selling_price' => $_REQUEST['item_selling_price'],
				':item_min_qty' => $_REQUEST['item_min_qty'],
				':item_order_min_qty' => $_REQUEST['item_order_min_qty'],
				':item_max_qty' => $_REQUEST['item_max_qty'],
				':updated_dtm' => $_REQUEST['updated_dtm'],
				':updated_by' => $_REQUEST['updated_by']
				);
				$stmt->execute($data);
				
				$stmt1 = $conn->prepare("UPDATE tbl_item_details SET item_price = :item_price, item_discount = :item_discount, item_cost_price = :item_cost_price, item_selling_price = :item_selling_price, item_min_qty = :item_min_qty, item_order_min_qty = :item_order_min_qty,  item_max_qty = :item_max_qty WHERE item_id = :item_id");		
				$data1 = array(	
				':item_id' => $_REQUEST['item_id'],
				':item_price' => $_REQUEST['item_price'],
				':item_discount' => $_REQUEST['item_discount'],			
				':item_cost_price' => $_REQUEST['item_cost_price'],
				':item_selling_price' => $_REQUEST['item_selling_price'],
				':item_min_qty' => $_REQUEST['item_min_qty'],
				':item_order_min_qty' => $_REQUEST['item_order_min_qty'],
				':item_max_qty' => $_REQUEST['item_max_qty']
				);
				$stmt1->execute($data1);
				
				$_SESSION['_msg'] = "Item Price History successfully updated..!";
			}
		}
			db_commit($conn);
		catch (Exception $e)
		{
			fnLogError($e);		
			db_rollback($conn);
			$str= filter_var($e->getMessage(), FILTER_SANITIZE_STRING);			
			$_SESSION['_msg_err'] = $str;			
		}
		
		header("location:mst_itemprice_history_list.php");	
		die();
}
*/
// $field_name = $dbconn->GetSingleReconrd("mst_branch","branch_stock_field","branch_id",$_SESSION['_user_branch']);


// $result = $conn->query("SELECT * FROM mst_branch WHERE branch_id = ".$_SESSION['_user_branch']);	
// if ($result->rowCount()>0)
// {
// 	$res = $result->fetch(PDO::FETCH_OBJ);	

// }

// $user_branch = $_SESSION['_user_branch'];

// $result_branch = $conn->query("SELECT * FROM mst_branch WHERE branch_id = $user_branch");
// // $auto_id = $dbconn->GetMaxValue("tbl_item_stock", "item_id", "item_id", $_REQUEST['item_id']);
// $update_id = $_REQUEST['item_id'];


// if ($result_branch->rowCount() > 0) {
// 	$branch_data = $result_branch->fetch(PDO::FETCH_OBJ);

// 	if ($update_id != "")
// 	{

// 		$result = $conn->query("SELECT ".$branch_data->branch_item_maq." as item_max_qty ,".$branch_data->branch_item_price." as item_price, ".$branch_data->branch_item_discount." as item_discount,
// 		 ".$branch_data->branch_item_cost_price." as item_cost_price, ".$branch_data->branch_item_selling_price." as item_selling_price, ".$branch_data->branch_item_msq." as item_min_qty, 
// 		 ".$branch_data->branch_item_moq." as item_order_min_qty FROM tbl_item_stock WHERE item_id = ".$update_id);	
// 		if ($result->rowCount()>0)
// 		{
// 			$obj1 = $result->fetch(PDO::FETCH_OBJ);	
// 			$item_id=$obj->item_id;
// 			$new_item_uom=$obj->new_item_uom;
// 			$new_item_hsn=$obj->new_item_hsn;



// 		}
// 	}

// }

$branch_id = $_SESSION['_user_branch'];

if ($_REQUEST['auto_id'] != "") {
	$result = $conn->query("SELECT * FROM tbl_itemprice_history WHERE auto_id = " . $_REQUEST['auto_id']);
	if ($result->rowCount() > 0) {
		$obj = $result->fetch(PDO::FETCH_OBJ);
		$item_id = $obj->item_id;
		$new_item_uom = $obj->new_item_uom;
		$new_item_hsn = $obj->new_item_hsn;
		$margin_per = $obj->new_margin_percent;
		$branch_id = $obj->branch_id;
	}
}


if ($_REQUEST['item_id'] != "") {

	$result = $conn->query("SELECT * FROM tbl_item_details WHERE item_id = " . $_REQUEST['item_id']);
	if ($result->rowCount() > 0) {
		$obj = $result->fetch(PDO::FETCH_OBJ);
		$item_id = $obj->item_id;
		$new_item_uom = $obj->item_uom;
		$new_item_hsn = $obj->item_hsn;
		// $branch_id = $obj->branch_id;	


		if ($obj->multi_uom_id != '') {
			$multi_uom = explode(",", $obj->multi_uom_id);
			$new_item_uoms = $multi_uom;
			// print_r($new_item_uoms);

		}
	}
}


?>
<!DOCTYPE html>
<html lang="en">

<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" />
	<title><?php echo PAGE_TITLE; ?> - Item Price History </title>
	<link href="css/main.css" rel="stylesheet" type="text/css" />
	<!--[if IE 8]><link href="css/ie8.css" rel="stylesheet" type="text/css" /><![endif]-->

	<?php include_once("inc/common/css-js.php"); ?>


	<script type="text/javascript">
		$(document).ready(function() {
			<?php
			if (isset($_SESSION['_msg']) && $_SESSION['_msg'] != "") {
				echo "$.jGrowl('" . $_SESSION['_msg'] . "', { sticky: false, theme: 'alert-styled-left alert-arrow-left alert-success', position: 'bottom-right', life:'2000', header: 'Success!' });";
				$_SESSION['_msg'] = "";
			}
			if (isset($_SESSION['_msg_err']) && $_SESSION['_msg_err'] != "") {
				echo "$.jGrowl('" . $_SESSION['_msg_err'] . "', { sticky: false, theme: 'alert-styled-left alert-arrow-left alert-danger', position: 'top-right', shutdown:'3000', header: 'Error!' });";
				$_SESSION['_msg_err'] = "";
			}
			?>
			$("#branch_id").change(function() {
				var branch_id = $(this).val();
				var item_id = $('#item_id').val();
				$.ajax({
					type: "POST",
					url: "inc/cis_ajax/jquery_get_item_details.php",
					data: {
						"branch_id": branch_id,
						"item_id": item_id,
						"mode": "single_uom"
					}
				}).done(function(msg) {
					// alert(msg);
					var data = msg.split('~');
					$('#branch_new_price').val(data[0]);
					$('#branch_new_discount').val(data[1]);
					$('#branch_new_min_discount').val(data[11]);
					$('#branch_new_max_discount').val(data[12]);
					$('#branch_new_cost_price').val(data[2]);
					$('#branch_new_selling_price').val(data[3]);
					$('#branch_new_msq').val(data[4]);
					$('#branch_new_maq').val(data[5]);
					$('#branch_new_moq').val(data[6]);
					$('#branch_stock_field').val(data[7]);
					$('#margin_percent').val(data[8]);
					fnMainPrice();	// margin shown on cost for the loaded branch

				});
			}).trigger('change');

			<?php
			//if ($_SESSION['_user_type'] == 'A')
			//{ 
			?>
			//$('.hide_price').show();
			<?php	//}else{ 
			?>
			//$('.hide_price').hide();
			<?php	//}  
			?>

			// 2026-10-16 margin on cost (was on item price); price / discount change cost and margin only
			var fnMainPrice = fnPrice.link({ price: '#branch_new_price', discount: '#branch_new_discount', cost: '#branch_new_cost_price', margin: '#margin_percent', selling: '#branch_new_selling_price' });

		});

		function isNumberKey_with_dot(evt) {
			var charCode = evt.which ? evt.which : evt.keyCode;
			var input = evt.target;
			var charStr = String.fromCharCode(charCode);

			// Allow control keys
			if (
				charCode === 8 || // Backspace
				charCode === 9 || // Tab
				charCode === 13 || // Enter
				charCode === 37 || // Left Arrow
				charCode === 39 || // Right Arrow
				charCode === 46 // Delete
			) {
				return true;
			}

			// Allow minus sign only as first character
			if (charStr === '-') {
				return input.selectionStart === 0 &&
					input.value.indexOf('-') === -1;
			}

			// Allow only one decimal point
			if (charStr === '.') {
				return input.value.indexOf('.') === -1;
			}

			// Allow digits only
			return /[0-9]/.test(charStr);
		}

		// 2026-11-11 unit price rows: an empty row is left as not set; a filled row needs a price above 0 and a discount below 100
		function fnValidateUom() {
			var bad = '';
			$('#thisForm2 .uom_row').each(function() {
				var price = $.trim($(this).find('.uom_price').val());
				var disc = $.trim($(this).find('.uom_discount').val());
				var sell = $.trim($(this).find('.uom_selling').val());
				if (price === '' && disc === '' && sell === '') {
					return;
				}
				var num = /^\d+(\.\d+)?$/;	// numbers only, no minus
				if (!num.test(price) || !(parseFloat(price) > 0) || (disc !== '' && !num.test(disc)) || parseFloat(disc || 0) >= 100 || (sell !== '' && !num.test(sell))) {
					bad = $(this).data('label');
					return false;
				}
			});
			if (bad !== '') {
				alert('Enter numbers only - a price above 0, a discount below 100 and a selling price not below 0 - for ' + bad + '..!');
				return false;
			}
			return true;
		}

		function fnValidate() {
			// alert("validation")

			// 2026-11-11 the fields are branch_new_*: new_price etc. do not exist, the check threw and never ran
			if (isNull(document.thisForm.branch_new_price, "Item Price..!")) {
				return false;
			}
			if (isNull(document.thisForm.branch_new_cost_price, "Cost Price..!")) {
				return false;
			}
			if (isNull(document.thisForm.branch_new_selling_price, "Selling Price..!")) {
				return false;
			}
			return true;	// a normal submit keeps the SAVE1 button in the post (form.submit() left it out)
		}
	</script>
</head>


<body>
	<!-- Main navbar -->
	<?php include("inc/common/header.php") ?>
	<!-- /main navbar -->


	<!-- Page content -->
	<div class="page-content">

		<!-- Main sidebar -->
		<?php include("inc/common/sidebar.php") ?>
		<!-- /main sidebar -->


		<!-- Main content -->
		<div class="content-wrapper">

			<!-- Page header -->
			<div class="page-header">
				<div class="breadcrumb-line breadcrumb-line-light header-elements-md-inline">
					<div class="d-flex">
						<div class="breadcrumb">
							<a href="home.php" class="breadcrumb-item"><i class="icon-home2 mr-2"></i>Dashboard</a>
							<a href="#" class="breadcrumb-item"> Item Masters</a>
							<span class="breadcrumb-item active">Item Price History</span>
						</div>
						<a href="#" class="header-elements-toggle text-default d-md-none"><i class="icon-more"></i></a>
					</div>
				</div>
			</div>
			<!-- /page header -->

			<!-- Content area -->
			<div class="content pt-0">
				<!-- Dashboard content -->
				<div class="row">
					<div class="col-md-12">
						<!-- This Form UI Starts here --->

						<!-- Basic datatable -->
						<form name='thisForm' class="form-horizontal" method='POST' action="" onSubmit="return fnValidate();" enctype="multipart/form-data">
							<?php csrf_fields('itemprice_history'); ?>
							<input type="hidden" name="item_id" id="item_id" value="<?php echo $_REQUEST['item_id']; ?>">

							<div class="card">
								<div class="card-header bg-pgheader text-white header-elements-inline">
									<h6 class="card-title">Update Item Details</h6>
									<div class="header-elements">
										<div class="list-icons">
											<a class="list-icons-item" href="home.php" title="Home"><i class="icon-home2 mr-2"></i></a>
											<a class="list-icons-item" href="lst_item_details.php" title="customer List"><i class="icon-arrow-left52 mr-2"></i></a>
											<a class="list-icons-item" data-action="fullscreen"></a>
										</div>
									</div>
								</div>

								<div class="card-body">
									<div class="form-group row pt-2">
										<label class="col-lg-2 col-form-label item_type7">Item<span class="text-mandatory"> *</span></label>

										<div class="col-lg-4">
											<select disabled name="item_id_old" id="item_id_old" data-placeholder="Choose a Item Type.." class="select">
												<option value="">--- Select Item ---</option>
												<?php

												echo $dbconn->fnFillComboFromTable_Where("item_id", "CONCAT(item_code,' - ',item_purchase_code)", "tbl_item_details", "item_id", " WHERE item_status = 1"); ?>
											</select>
											<script>
												document.thisForm.item_id_old.value = "<?php echo $obj->item_id; ?>";
											</script>
											<input type="hidden" class="form-control" name="item_id" id="item_id" maxlength="9" value="<?php echo $obj->item_id; ?>" />
										</div>
									</div>
									<div class="form-group row pt-2">
										<label class="col-lg-2 col-form-label">Branch<span class="text-mandatory"> *</span></label>
										<div class="col-lg-4">
											<select name="branch_id" id="branch_id" data-placeholder="Choose a Item Type.." class="select">
												<option value="">--- Select Branch ---</option>
												<?php echo $dbconn->fnFillComboFromTable_Where("branch_id", "branch_name", "mst_branch", "branch_id", " WHERE branch_status = 1"); ?>
											</select>
											<script>
												document.thisForm.branch_id.value = "<?php echo $branch_id; ?>"
											</script>
											<!-- <input type="hidden" class="form-control"  name="branch_id" id="branch_id"  maxlength="9" value="<?php //echo $obj->branch_id; 
																																					?>" /> -->
										</div>
										<label class="col-lg-2 col-form-label">Item Purchase Discount<span class="text-mandatory"> </span></label>
										<div class="col-lg-4">
											<div class="input-group">
												<span class="input-group-append">
													<input type="text" class="form-control" name="branch_new_discount" id="branch_new_discount" maxlength="9" value="" placeholder="Enter Discount" onKeyPress="return isNumberKey_with_dot(event)" />
												</span>
												<span class="input-group-text">%</span>
											</div>
										</div>
									</div>


									<div class="form-group row pt-2 hide_price">
										<label class="col-lg-2 col-form-label">Item Price<span class="text-mandatory"> *</span></label>
										<div class="col-lg-4">
											<input type="text" class="form-control" name="branch_new_price" id="branch_new_price" maxlength="9" value="" onKeyPress="return isNumberKey_with_dot(event)" placeholder="Enter Price" />
										</div>
										<label class="col-lg-2 col-form-label">Margin (%)<span class="text-mandatory"> *</span></label>
										<div class="col-lg-4">
											<div class="input-group">
												<span class="input-group-append">
													<input type="text" class="form-control" name="margin_percent" id="margin_percent" maxlength="9" value="" placeholder="Enter Margin" onKeyPress="return isNumberKey_with_dot(event)" />
												</span>
												<span class="input-group-text">%</span>
											</div>
										</div>
									</div>
									<div class="form-group row pt-2 hide_price">
										<label class="col-lg-2 col-form-label">Cost Price<span class="text-mandatory"> *</span></label>
										<div class="col-lg-4">
											<input type="text" class="form-control" name="branch_new_cost_price" id="branch_new_cost_price" maxlength="9" value="" onKeyPress="return isNumberKey_with_dot(event)" placeholder="Enter Cost Price" readonly />
										</div>
										<label class="col-lg-2 col-form-label">Selling Price<span class="text-mandatory"> * </span></label>
										<div class="col-lg-4">
											<input type="text" class="form-control" name="branch_new_selling_price" id="branch_new_selling_price" maxlength="9" value="" placeholder="Enter Selling Price" onKeyPress="return isNumberKey_with_dot(event)" />
										</div>
									</div>

									<div class="form-group row pt-2">
										<label class="col-lg-2 col-form-label">Min Discount<span class="text-mandatory"> </span></label>
										<div class="col-lg-4">
											<div class="input-group">
												<span class="input-group-append">
													<input type="text" class="form-control" name="branch_new_min_discount" id="branch_new_min_discount" maxlength="9" value="" placeholder="Enter Min Discount" onKeyPress="return isNumberKey_with_dot(event)" />
												</span>
												<span class="input-group-text">%</span>
											</div>
										</div>
										<label class="col-lg-2 col-form-label">Max Discount<span class="text-mandatory"> </span></label>
										<div class="col-lg-4">
											<div class="input-group">
												<span class="input-group-append">
													<input type="text" class="form-control" name="branch_new_max_discount" id="branch_new_max_discount" maxlength="9" value="" placeholder="Enter Max Discount" onKeyPress="return isNumberKey_with_dot(event)" />
												</span>
												<span class="input-group-text">%</span>
											</div>
										</div>
									</div>

									<div class="form-group row pt-2">
										<label class="col-lg-2 col-form-label">MSQ</label>
										<div class="col-lg-4">
											<input type="text" class="form-control" name="branch_new_msq" id="branch_new_msq" maxlength="9" value="" onKeyPress="return isNumberKey_with_dot(event)" placeholder="Enter MSQ" />
										</div>
										<label class="col-lg-2 col-form-label">MAQ</label>
										<div class="col-lg-4">
											<input type="text" class="form-control" name="branch_new_maq" id="branch_new_maq" maxlength="9" value="" placeholder="Enter MAQ" onKeyPress="return isNumberKey_with_dot(event)" />
										</div>
									</div>
									<div class="form-group row pt-2">
										<label class="col-lg-2 col-form-label">MOQ</label>
										<div class="col-lg-4">
											<input type="text" class="form-control" name="branch_new_moq" id="branch_new_moq" maxlength="9" value="" onKeyPress="return isNumberKey_with_dot(event)" placeholder="Enter MOQ" />
										</div>
										<label class="col-lg-2 col-form-label">Current Stock</label>
										<div class="col-lg-4">
											<input type="text" class="form-control bg-light" name="branch_stock_field" id="branch_stock_field" maxlength="9" value="" readonly="readonly" tabindex="-1" title="Stock cannot be changed from this screen" />
											<!-- <span class="form-text text-muted">Read only &mdash; use <a href="stock_adjustment.php">Stock Adjustment</a> to change stock.</span> -->
										</div>

									</div>

									<div class="form-group row pt-2">
										<label class="col-lg-2 col-form-label">UOM</label>
										<div class="col-lg-4">
											<select name="new_item_uom" id="new_item_uom" data-placeholder="Choose a UOM.." class="select-search">
												<option value="">--- Select UOM ---</option>
												<?php
												echo $dbconn->fnFillComboFromTable_Where("uom_id", "uom_name", "mst_uom", "uom_id", " WHERE uom_status = 1"); ?>
											</select>
											<script>
												document.thisForm.new_item_uom.value = "<?php echo $new_item_uom; ?>";
											</script>
										</div>
										<label class="col-lg-2 col-form-label">HSN</label>
										<div class="col-lg-4">
											<select name="new_item_hsn" id="new_item_hsn" data-placeholder="Choose a HSN.." class="select-search">
												<option value="">--- Select HSN ---</option>
												<?php

												echo $dbconn->fnFillComboFromTable_Where("hsn_id", "CONCAT(hsn_code,' - ',igst,'%')", "mst_hsn", "hsn_id", " WHERE hsn_status = 1"); ?>
											</select>
											<script>
												document.thisForm.new_item_hsn.value = "<?php echo $new_item_hsn; ?>";
											</script>

										</div>
									</div>


								</div>
								<div class="card-footer text-center pt-2">
									<?php if ($_REQUEST["auto_id"] != '') { ?>
										<INPUT class="btn btn-info" type="submit" name="UPDATE" value="UPDATE">
										<INPUT class="btn btn-light" type="button" name="cancel" value="Cancel" onClick="javascript:window.location.href='lst_item_details.php'">
										<input type="hiddden" name="txtHid" id="txtHid" value="<?php echo $_REQUEST['auto_id']; ?>">
									<?php } else { ?>
										<INPUT class="btn btn-info" type="submit" name="SAVE1" value="SAVE">
										<INPUT class="btn btn-light" type="button" name="cancel" value="Cancel" onClick="javascript:window.location.href='lst_item_details.php'">
										<input type="hidden" name="txtHid" value="0">
									<?php } ?>
								</div>


							</div>

						</form>

						<?php
						// 2026-11-11 price per extra unit (multiple UOM): every branch in one card per unit, one SAVE for all
						$extra_uoms = ($obj->item_id != '') ? fnItemExtraUoms($conn, $obj->item_id) : array();
						if (count($extra_uoms) > 0) {
							$main_uom = $dbconn->GetSingleReconrd("mst_uom", "uom_name", "uom_id", $dbconn->GetSingleReconrd("tbl_item_details", "item_uom", "item_id", $obj->item_id));
							$uom_branches = $conn->query("SELECT * FROM mst_branch WHERE branch_status = 1 ORDER BY branch_id")->fetchAll(PDO::FETCH_OBJ);
							$stock_row = $conn->query("SELECT * FROM tbl_item_stock WHERE item_id = " . (int)$obj->item_id . " ORDER BY stock_id LIMIT 1")->fetch(PDO::FETCH_ASSOC);
						?>
						<form name='thisForm2' id='thisForm2' class="form-horizontal" method='POST' action="mst_itemprice_history.php?item_id=<?php echo (int)$obj->item_id; ?>" onSubmit="return fnValidateUom();">
							<?php csrf_fields('itemprice_history'); ?>
							<input type="hidden" name="item_id" value="<?php echo (int)$obj->item_id; ?>">
							<?php foreach ($extra_uoms as $uom_id => $u) { ?>
								<div class="card">
									<div class="card-header bg-light header-elements-inline">
										<h6 class="card-title">Price per <b><?php echo $u->uom_name; ?></b> (extra unit) <span class="text-muted ml-2">main unit: <?php echo $main_uom; ?></span></h6>
									</div>
									<div class="card-body p-0">
										<table class="table table-xs table-bordered uom_price_table mb-0">
											<thead>
												<tr class="bg-teal">
													<th>Branch</th>
													<th class="text-right">Cost per <?php echo $main_uom; ?></th>
													<th>Item Price <span class="text-mandatory">*</span></th>
													<th>Discount %</th>
													<th>Cost Price</th>
													<th>Margin %</th>
													<th>Selling Price</th>
													<th>Last Change</th>
												</tr>
											</thead>
											<tbody>
												<?php foreach ($uom_branches as $b) {
													$p = fnItemUomPrice($conn, $obj->item_id, $uom_id, $b->branch_id);
													$main_cost = ($stock_row && $b->branch_item_cost_price != '' && isset($stock_row[$b->branch_item_cost_price])) ? $stock_row[$b->branch_item_cost_price] : '';
													$fid = $uom_id . '_' . $b->branch_id;
													$fname = 'up[' . $uom_id . '][' . $b->branch_id . ']';
													if ($p) {
														$who = $dbconn->GetSingleReconrd("tbl_user", "usr_name", "usr_id", $p->modify_by > 0 ? $p->modify_by : $p->created_by);
														$when = $p->modify_dtm ? $p->modify_dtm : $p->created_dtm;
														$last = ($when ? date('d-m-Y H:i', strtotime($when)) : '') . ($who != '' ? ' / ' . $who : '');
													}
												?>
													<tr class="uom_row<?php echo $p ? '' : ' table-danger'; ?>" data-label="<?php echo $u->uom_name . ' / ' . $b->branch_name; ?>">
														<td><?php echo $b->branch_name; ?></td>
														<td class="text-right"><?php echo $main_cost; ?></td>
														<td><input type="text" class="form-control form-control-sm uom_price" name="<?php echo $fname; ?>[price]" id="up_price_<?php echo $fid; ?>" maxlength="9" value="<?php echo $p ? $p->item_price : ''; ?>" onKeyPress="return isNumberKey_with_dot(event)" placeholder="Not set" /></td>
														<td><input type="text" class="form-control form-control-sm uom_discount" name="<?php echo $fname; ?>[discount]" id="up_discount_<?php echo $fid; ?>" maxlength="6" value="<?php echo $p ? $p->item_discount : ''; ?>" onKeyPress="return isNumberKey_with_dot(event)" /></td>
														<td><input type="text" class="form-control form-control-sm uom_cost" name="<?php echo $fname; ?>[cost]" id="up_cost_<?php echo $fid; ?>" value="<?php echo $p ? $p->cost_price : ''; ?>" readonly tabindex="-1" /></td>
														<td><input type="text" class="form-control form-control-sm" name="<?php echo $fname; ?>[margin]" id="up_margin_<?php echo $fid; ?>" maxlength="9" value="<?php echo $p ? $p->margin_percent : ''; ?>" onKeyPress="return isNumberKey_with_dot(event)" /></td>
														<td><input type="text" class="form-control form-control-sm uom_selling" name="<?php echo $fname; ?>[selling]" id="up_selling_<?php echo $fid; ?>" maxlength="9" value="<?php echo $p ? $p->selling_price : ''; ?>" onKeyPress="return isNumberKey_with_dot(event)" /></td>
														<td class="small"><?php echo $p ? $last : '<span class="text-danger">Not set</span>'; ?></td>
													</tr>
													<script>
														// cost / margin / selling as the main price form
														fnPrice.link({ price: '#up_price_<?php echo $fid; ?>', discount: '#up_discount_<?php echo $fid; ?>', cost: '#up_cost_<?php echo $fid; ?>', margin: '#up_margin_<?php echo $fid; ?>', selling: '#up_selling_<?php echo $fid; ?>' });
														if ($('#up_price_<?php echo $fid; ?>').val() === '') $('#up_cost_<?php echo $fid; ?>').val('');	// not set stays empty
													</script>
												<?php } ?>
											</tbody>
										</table>
									</div>
								</div>
							<?php } ?>
							<div class="card">
								<div class="card-footer text-center pt-2">
									<span class="text-muted d-block mb-2">A branch left empty has no price in that unit - PO Prepare will not send an indent line in that unit to admin until it is set.</span>
									<INPUT class="btn btn-info" type="submit" name="SAVE_UOM" value="SAVE UNIT PRICES">
									<INPUT class="btn btn-light" type="button" name="cancel" value="Cancel" onClick="javascript:window.location.href='lst_item_details.php'">
								</div>
							</div>
						</form>
						<?php } ?>

					</div>

					<!-- End of This Form UI  --->

				</div>

				<?php include("inc/common/footer.php") ?>
				<!-- /dashboard content -->
			</div>
			<!-- /content area -->


		</div>

	</div>

	</div>


	<!-- /page content -->
</body>

</html>