<?PHP
ob_start();
session_start();
require_once("inc/common/userclass.php");
// require_once ("inc/common/dbrowiterator.php");	

isAdmin();



$conn = new dbconnect();
$dbconn = new dbhandler();
// ini_set('display_errors', '1');ini_set('display_startup_errors', '1');error_reporting(E_ALL);


$grn_date = date("Y-m-d");

if (isset($_REQUEST['Draft'])) {
	try {

		$_REQUEST['grn_date'] = ($_REQUEST['grn_date'] != '') ? date('Y-m-d', strtotime($_REQUEST['grn_date'])) : NULL;

		$_REQUEST['modify_by'] = $_SESSION['_user_id'];
		$_REQUEST['modify_date_time'] = date('Y-m-d H:i:s');
		$_REQUEST['branch'] = $dbconn->GetSingleReconrd("mst_branch", "branch_code", "branch_id='".$_SESSION['_user_branch']."' AND branch_status", 1);
		// $_REQUEST['grn_slno'] = $dbconn->GetMaxValue('tbl_grn', 'grn_slno','grn_finyr',$_REQUEST['grn_finyr']) +1;
		// $_REQUEST['si_slno'] = $dbconn->GetMaxValue('tbl_store_indent', 'si_slno', ' si_finyr',$_REQUEST['si_finyr']) +1;
		$_REQUEST['grn_finyr'] = $dbconn->GetSingleReconrd("mst_finyear", "finyr", "finyr_active", 1);
		$_REQUEST['grn_slno'] = $dbconn->GetMaxValue('tbl_grn', 'grn_slno', 'grn_finyr = "' . $_REQUEST['grn_finyr'] . '" AND branch_id="'.$_SESSION['_user_branch'].'" AND 1 ', 1) + 1;

		$_REQUEST['grn_ref_code'] = 'GRN/' . leadingZeros($_REQUEST['grn_slno'], 4) . '/BIE/'.$_REQUEST['branch'].'/' . $_REQUEST['grn_finyr'];


		// $is_exist = $dbconn->GetSingleReconrd("tbl_grn", "grn_id", "grn_ref_code = '" . $_REQUEST['grn_ref_code'] . "' AND 1", 1);
		// if ($is_exist != "") {
		// 	$_SESSION['_msg_err'] = "GRN already exist..!";
		// 	header("location:grn.php");
		// 	die();
		// }

		$stmt = null;
		$stmt = $conn->prepare("INSERT INTO tbl_grn (grn_slno, grn_finyr, grn_refno, grn_ref_code, grn_date ,supp_id, branch_id, company_id,
								common_remarks, party_dc_no, party_bill_date, grn_qty ,po_id, grn_status, modify_by ,modify_date_time)
								VALUES 
								(:grn_slno, :grn_finyr, :grn_refno, :grn_ref_code, :grn_date, :supp_id, :branch_id, :company_id, :common_remarks, :party_dc_no, :party_bill_date, :grn_qty, :po_id,
								:grn_status, :modify_by, :modify_date_time)");
		$data = array(
			':grn_slno' =>  $_REQUEST['grn_slno'],
			':grn_finyr' =>  $_REQUEST['grn_finyr'],
			':grn_refno' =>  $_REQUEST['grn_refno'],
			':grn_ref_code' =>  $_REQUEST['grn_ref_code'],
			':grn_date' =>  $_REQUEST['grn_date'],
			':supp_id' =>  $_REQUEST['supp_id'],
			':branch_id' =>  $_SESSION['_user_branch'],
			':company_id' =>  $_SESSION['_user_branch'],
			':common_remarks' => $_REQUEST['common_remarks'],
			':party_dc_no' => $_REQUEST['party_dc_no'],
			':party_bill_date' => $_REQUEST['party_bill_date'],
			':grn_qty' => '',
			':po_id' => $_REQUEST['po_id'],
			':grn_status' => 0,
			':modify_by' => $_REQUEST['modify_by'],
			':modify_date_time' => $_REQUEST['modify_date_time']
		);
		$stmt->execute($data);
		$grn_id = $conn->lastInsertId();

		/* details */
		$delete_details  = $conn->query('DELETE FROM tbl_grn_details WHERE grn_id = ' . $grn_id);
		$delete_details->execute();

		for ($x = 0; $x < count($_REQUEST['temp_item_id']); $x++) {
			$stmt1 = null;
			$stmt1 = $conn->prepare("INSERT INTO tbl_grn_details (grn_id, grn_item_id, grn_unit, po_qty, grn_accepted_qty, grn_rejected_qty, grn_pending_qty, grn_recived_qty ) 
                        VALUES (:grn_id, :grn_item_id, :grn_unit, :po_qty, :grn_accepted_qty, :grn_rejected_qty, :grn_pending_qty, :grn_recived_qty)");

			$data = array(
				':grn_id' =>  $grn_id,
				':grn_item_id' => $_REQUEST['temp_item_id'][$x],
				':grn_unit' => $_REQUEST['grn_unit'][$x],
				':po_qty' => $_REQUEST['order_qty'][$x],
				':grn_accepted_qty' => $_REQUEST['grn_accepted_qty'][$x],
				':grn_rejected_qty' => $_REQUEST['grn_rejected_qty'][$x],
				':grn_pending_qty' => $_REQUEST['grn_pending_qty'][$x],
				':grn_recived_qty' => $_REQUEST['grn_recived_qty'][$x]
			);
			$stmt1->execute($data);
			
		}

		/* details */
	} catch (Exception $e) {
		$str = filter_var($e->getMessage(), FILTER_SANITIZE_STRING);
		echo $_SESSION['_msg_err'] = $str;
	}

	$_SESSION['_msg'] = "GRN succesfully Sent..!";
	header("location:grn_list.php");
	die();
}



if (isset($_POST['UPDATE'])) {
	$update_id = $_REQUEST['txtHid'];
	try {

		$_REQUEST['modify_date_time'] = date('Y-m-d H:i:s');
		$_REQUEST['modify_by'] = $_SESSION['_user_id'];
		$stmt = null;
		$stmt = $conn->prepare("UPDATE tbl_grn SET common_remarks = :common_remarks, grn_refno = :grn_refno, grn_date = :grn_date, supp_id = :supp_id, party_dc_no = :party_dc_no, party_bill_date = :party_bill_date, 
					modify_by = :modify_by, modify_date_time = :modify_date_time WHERE grn_id = :grn_id");
		$data = array(
			':common_remarks' => $_REQUEST['common_remarks'],
			':grn_refno' =>  $_REQUEST['grn_refno'],
			':grn_date' =>  $_REQUEST['grn_date'],
			':supp_id' =>  $_REQUEST['supp_id'],
			':party_dc_no' => $_REQUEST['party_dc_no'],
			':party_bill_date' => $_REQUEST['party_bill_date'],
			':modify_by' => $_REQUEST['modify_by'],
			':modify_date_time' => $_REQUEST['modify_date_time'],
			':grn_id' => $update_id
		);

		$stmt->execute($data);

		$grn_status = $dbconn->GetSingleReconrd("tbl_grn","grn_status","grn_id",$update_id);


        if($grn_status > 1)
		{
			$update_grn = $conn->prepare("UPDATE tbl_grn SET  grn_status = :grn_status, modify_date_time = :modify_date_time WHERE grn_id = :grn_id");
			$data1 = array(
				':grn_id' => $update_id,
				':grn_status' => 2,
			    ':modify_date_time' => $_REQUEST['modify_date_time']
			);
			$update_grn->execute($data1);
			// print_r($data1);
			// die();
		}

	    // $delete_details  = $conn->query('DELETE FROM tbl_grn_details WHERE grn_id = ' . $update_id);

		// $sql =  "DELETE FROM tbl_grn_details WHERE grn_id = '" . $update_id . "'";


		// $result = $conn->prepare($sql);
		// $result->execute();

		/* details */

		$delete_details =  "DELETE FROM tbl_grn_details WHERE grn_id = '" . $update_id . "'";
		$result = $conn->prepare($delete_details);
		$result->execute();
		

		for ($x = 0; $x < count($_REQUEST['temp_item_id']); $x++) {
			$stmt1 = null;
			$stmt1 = $conn->prepare("INSERT INTO tbl_grn_details (grn_id, grn_item_id, grn_unit, po_qty, grn_accepted_qty, grn_rejected_qty, grn_pending_qty, grn_recived_qty ) 
                VALUES (:grn_id, :grn_item_id, :grn_unit, :po_qty, :grn_accepted_qty, :grn_rejected_qty, :grn_pending_qty, :grn_recived_qty)");

			$data = array(
				':grn_id' =>  $update_id,
				':grn_item_id' => $_REQUEST['temp_item_id'][$x],
				':grn_unit' => $_REQUEST['grn_unit'][$x],
				':po_qty' => $_REQUEST['order_qty'][$x],
				':grn_accepted_qty' => $_REQUEST['grn_accepted_qty'][$x],
				':grn_rejected_qty' => $_REQUEST['grn_rejected_qty'][$x],
				':grn_pending_qty' => $_REQUEST['grn_pending_qty'][$x],
				':grn_recived_qty' => $_REQUEST['grn_recived_qty'][$x]
			);
			$stmt1->execute($data);
		}

		
		

		/* details */
	} catch (Exception $e) {
		$str = filter_var($e->getMessage(), FILTER_SANITIZE_STRING);
		$_SESSION['_msg_err'] = $str;
	}
	$_SESSION['_msg'] = "GRN succesfully Sent..!";
	header("location:grn_list.php");
	die();
}



if (isset($_POST['FINALIZE'])) {
	$update_id = $_REQUEST['txtHid'];
	if ($update_id != '' && $update_id > 0) {
		try {

			$_REQUEST['modify_date_time'] = date('Y-m-d H:i:s');
			$_REQUEST['modify_by'] = $_SESSION['_user_id'];
			$stmt = null;
			$stmt = $conn->prepare("UPDATE tbl_grn SET common_remarks = :common_remarks, grn_refno = :grn_refno, grn_date = :grn_date, supp_id = :supp_id, branch_id = :branch_id, party_dc_no = :party_dc_no, party_bill_date = :party_bill_date, 
					modify_by = :modify_by, modify_date_time = :modify_date_time, grn_status = :grn_status WHERE grn_id = :grn_id");
			$data = array(
				':common_remarks' => $_REQUEST['common_remarks'],
				':grn_refno' =>  $_REQUEST['grn_refno'],
				':grn_date' =>  $_REQUEST['grn_date'],
				':supp_id' =>  $_REQUEST['supp_id'],
				':branch_id' =>  $_SESSION['_user_branch'],
				':party_dc_no' => $_REQUEST['party_dc_no'],
				':party_bill_date' => $_REQUEST['party_bill_date'],
				':modify_by' => $_REQUEST['modify_by'],
				':modify_date_time' => $_REQUEST['modify_date_time'],
				':grn_status' => 2,
				':grn_id' => $update_id
			);

			$stmt->execute($data);


			// $delete_details  = $conn->query('DELETE FROM tbl_grn_details WHERE grn_id = ' . $update_id);

			$delete_details =  "DELETE FROM tbl_grn_details WHERE grn_id = '" . $update_id . "'";
			$result = $conn->prepare($delete_details);
			$result->execute();

			/* details */

			for ($x = 0; $x < count($_REQUEST['temp_item_id']); $x++) {
				$stmt1 = null;
				$stmt1 = $conn->prepare("INSERT INTO tbl_grn_details (grn_id, grn_item_id, grn_unit, po_qty, grn_accepted_qty, grn_rejected_qty, grn_pending_qty, grn_recived_qty ) 
                VALUES (:grn_id, :grn_item_id, :grn_unit, :po_qty, :grn_accepted_qty, :grn_rejected_qty, :grn_pending_qty, :grn_recived_qty)");

				$data = array(
					':grn_id' =>  $update_id,
					':grn_item_id' => $_REQUEST['temp_item_id'][$x],
					':grn_unit' => $_REQUEST['grn_unit'][$x],
					':po_qty' => $_REQUEST['order_qty'][$x],
					':grn_accepted_qty' => $_REQUEST['grn_accepted_qty'][$x],
					':grn_rejected_qty' => $_REQUEST['grn_rejected_qty'][$x],
					':grn_pending_qty' => $_REQUEST['grn_pending_qty'][$x],
					':grn_recived_qty' => $_REQUEST['grn_recived_qty'][$x]
				);
				$stmt1->execute($data);
			 	// print_r($data);
		     	// die();
			}

			/* details */

			/* STOCK DETAILS */

			for ($x = 0; $x < count($_REQUEST['temp_item_id']); $x++) {
			    if($_REQUEST['grn_accepted_qty'][$x] > 0)
			    {
    				$stmt1 = null;
    				$stmt1 = $conn->prepare("INSERT INTO tbl_stock_flow 
                                (trans_type, trans_id, branch_id, trans_date, item_id, item_price, before_qty, rcvd_qty, trans_qty, reje_qty, pend_qty, after_qty, modify_by, modify_date_time) 
                                VALUES
                                (:trans_type, :trans_id, :branch_id, :trans_date, :item_id, :item_price, :before_qty, :rcvd_qty, :trans_qty, :reje_qty, :pend_qty, :after_qty, :modify_by, :modify_date_time)");
                
    			   /* New Current Stock Update Branch */
    				$field_name = $dbconn->GetSingleReconrd("mst_branch","branch_stock_field","branch_id",$_SESSION['_user_branch']);
    				$stmt2 = null;
    				$stmt2 = $conn->prepare("UPDATE tbl_item_stock SET ".$field_name." = :branch_stock WHERE item_id = :item_id ");
    				$after_qty =  $_REQUEST['item_curr_stock'][$x] + $_REQUEST['grn_accepted_qty'][$x];
    				$price = $dbconn->GetSingleReconrd("tbl_item_details", "item_cost_price", "item_id", $_REQUEST['temp_item_id'][$x]);
    
    				$data = array(
    					':trans_type' => 'GRN',
    					':trans_id' => $update_id,
    					':branch_id' => $_SESSION['_user_branch'],
    					':trans_date' => date('Y-m-d'),
    					':item_id' => $_REQUEST['temp_item_id'][$x],
    					':item_price' => $price,
    					':before_qty' => $_REQUEST['item_curr_stock'][$x],
    					':rcvd_qty' => $_REQUEST['grn_recived_qty'][$x],
    					':trans_qty' => $_REQUEST['grn_accepted_qty'][$x],
    					':reje_qty' => $_REQUEST['grn_rejected_qty'][$x],
    					':pend_qty' => $_REQUEST['grn_pending_qty'][$x],
    					':after_qty' => $after_qty,
    					':modify_by' => $_SESSION['_user_id'],
    					':modify_date_time' => date('Y-m-d H:i:s')
    				);
    				$stmt1->execute($data);
    				$branch_item_curr_stock = $dbconn->GetSingleReconrd("tbl_item_stock", "$field_name", "item_id", $_REQUEST['temp_item_id'][$x]);
                    $after_qty2 =  (int)$branch_item_curr_stock + (int)$_REQUEST['grn_accepted_qty'][$x];
    
                    $data2 = array(
                        ':item_id' => $_REQUEST['temp_item_id'][$x],
                        ':branch_stock' => $after_qty2,
                    );
                    $stmt2->execute($data2);
			    }
			}

			/* STOCK DETAILS */

			/* ITEM DETAILS */
			// $_REQUEST['modify_by'] = $_SESSION['_user_id'];
			// $_REQUEST['modify_date_time'] = date('Y-m-d H:i:s');

			// for ($x = 0; $x < count($_REQUEST['temp_item_id']); $x++) {
			// 	$stmt1 = null;
			// 	$stmt1 = $conn->prepare("UPDATE tbl_item_details SET item_curr_stock = :item_curr_stock, modify_date_time=:modify_date_time, modify_by=:modify_by WHERE item_id = :item_id ");

			// 	$after_qty =  $_REQUEST['item_curr_stock'][$x] + $_REQUEST['grn_accepted_qty'][$x];

			// 	$data = array(
			// 		':item_id' => $_REQUEST['temp_item_id'][$x],
			// 		':item_curr_stock' => $after_qty,
			// 		':modify_date_time' => $_REQUEST['modify_date_time'],
			// 		':modify_by' => $_REQUEST['modify_by'],
			// 	);
			// 	$stmt1->execute($data);
			// }

			/* ITEM DETAILS */

			/* GRN status */

			for ($x = 0; $x < count($_REQUEST['temp_item_id']); $x++) {
				$pend_qty +=  $_REQUEST['grn_pending_qty'][$x];
			}

			if ($pend_qty <= 0) {
				$sql2 = "UPDATE tbl_purchase_order SET grn_status = 2 WHERE po_id = " . $_REQUEST['po_id'] . " ";
				$conn->query($sql2);
			} else {
				$sql2 = "UPDATE tbl_purchase_order SET grn_status = 1 WHERE po_id = " . $_REQUEST['po_id'] . " ";
				$conn->query($sql2);
			}

			$_SESSION['_msg'] = "GRN Successfully Recorded..!";
			/* GRN status */
		} catch (Exception $e) {
			$str = filter_var($e->getMessage(), FILTER_SANITIZE_STRING);
			$_SESSION['_msg_err'] = $str;
		}
		$_SESSION['_msg'] = "GRN succesfully Sent..!";
		header("location:grn_list.php");
		die();
		
	} else {
		try {

			$_REQUEST['grn_date'] = ($_REQUEST['grn_date'] != '') ? date('Y-m-d', strtotime($_REQUEST['grn_date'])) : NULL;

			$_REQUEST['modify_by'] = $_SESSION['_user_id'];
			$_REQUEST['modify_date_time'] = date('Y-m-d H:i:s');
    		$_REQUEST['branch'] = $dbconn->GetSingleReconrd("mst_branch", "branch_code", "branch_id='".$_SESSION['_user_branch']."' AND branch_status", 1);
			// $_REQUEST['grn_slno'] = $dbconn->GetMaxValue('tbl_grn', 'grn_slno', 'grn_finyr',$_REQUEST['grn_finyr']) +1;
			$_REQUEST['grn_finyr'] = $dbconn->GetSingleReconrd("mst_finyear", "finyr", "finyr_active", 1);
	    	$_REQUEST['grn_slno'] = $dbconn->GetMaxValue('tbl_grn', 'grn_slno', 'grn_finyr = "' . $_REQUEST['grn_finyr'] . '" AND branch_id="'.$_SESSION['_user_branch'].'" AND 1 ', 1) + 1;

			$_REQUEST['grn_ref_code'] = 'GRN/' . leadingZeros($_REQUEST['grn_slno'], 4). '/BIE/'.$_REQUEST['branch'].'/' . $_REQUEST['grn_finyr'];

			// $is_exist = $dbconn->GetSingleReconrd("tbl_grn", "grn_id", "grn_ref_code = '" . $_REQUEST['grn_ref_code'] . "' AND 1", 1);
			// if ($is_exist != "") {
			// 	$_SESSION['_msg_err'] = "GRN already exist..!";
			// 	header("location:grn.php");
			// 	die();
			// }

			$stmt = null;
			$stmt = $conn->prepare("INSERT INTO tbl_grn (grn_slno, grn_finyr, grn_refno, grn_ref_code, grn_date ,supp_id, branch_id, company_id,
								common_remarks, party_dc_no, party_bill_date, grn_qty ,po_id, grn_status, modify_by ,modify_date_time)
								VALUES 
								(:grn_slno, :grn_finyr, :grn_refno, :grn_ref_code, :grn_date, :supp_id, :branch_id, :company_id, :common_remarks, :party_dc_no, :party_bill_date, :grn_qty, :po_id, :grn_status, :modify_by,
                                :modify_date_time)");
			$data = array(
				':grn_slno' =>  $_REQUEST['grn_slno'],
				':grn_finyr' =>  $_REQUEST['grn_finyr'],
				':grn_refno' =>  $_REQUEST['grn_refno'],
				':grn_ref_code' =>  $_REQUEST['grn_ref_code'],
				':grn_date' =>  $_REQUEST['grn_date'],
				':supp_id' =>  $_REQUEST['supp_id'],
				':company_id' =>  $_SESSION['_user_branch'],
				':branch_id' =>  $_SESSION['_user_branch'],
				':common_remarks' => $_REQUEST['common_remarks'],
				':party_dc_no' => $_REQUEST['party_dc_no'],
				':party_bill_date' => $_REQUEST['party_bill_date'],
				':grn_qty' => '',
				':po_id' => $_REQUEST['po_id'],
				':grn_status' => 2,
				':modify_by' => $_REQUEST['modify_by'],
				':modify_date_time' => $_REQUEST['modify_date_time']
			);
			$stmt->execute($data);
			$grn_id = $conn->lastInsertId();

			/* details */
			// $delete_details  = $conn->query('DELETE FROM tbl_grn_details WHERE grn_id = ' . $grn_id);
			// $delete_details->execute();

			$delete_details =  "DELETE FROM tbl_grn_details WHERE grn_id = '" . $update_id . "'";
			$result = $conn->prepare($delete_details);
			$result->execute();

			for ($x = 0; $x < count($_REQUEST['temp_item_id']); $x++) {
				$stmt1 = null;
				$stmt1 = $conn->prepare("INSERT INTO tbl_grn_details (grn_id, grn_item_id, grn_unit, po_qty, grn_accepted_qty, grn_rejected_qty, grn_pending_qty, grn_recived_qty ) 
                        VALUES (:grn_id, :grn_item_id, :grn_unit, :po_qty, :grn_accepted_qty, :grn_rejected_qty, :grn_pending_qty, :grn_recived_qty)");
				
				$data = array(
					':grn_id' =>  $grn_id,
					':grn_item_id' => $_REQUEST['temp_item_id'][$x],
					':grn_unit' => $_REQUEST['grn_unit'][$x],
					':po_qty' => $_REQUEST['order_qty'][$x],
					':grn_accepted_qty' => $_REQUEST['grn_accepted_qty'][$x],
					':grn_rejected_qty' => $_REQUEST['grn_rejected_qty'][$x],
					':grn_pending_qty' => $_REQUEST['grn_pending_qty'][$x],
					':grn_recived_qty' => $_REQUEST['grn_recived_qty'][$x]
				);
				$stmt1->execute($data);
				// print_r($data);
		     	// die();
			}

			/* details */
			/* STOCK DETAILS */

			for ($x = 0; $x < count($_REQUEST['temp_item_id']); $x++) {
			    if($_REQUEST['grn_accepted_qty'][$x] > 0)
			    {
    				$stmt1 = null;
    				$stmt1 = $conn->prepare("INSERT INTO tbl_stock_flow 
                                (trans_type, trans_id, branch_id, trans_date, item_id, item_price, before_qty, rcvd_qty, trans_qty, reje_qty, pend_qty, after_qty, modify_by, modify_date_time) 
                                VALUES
                                (:trans_type, :trans_id, :branch_id, :trans_date, :item_id, :item_price, :before_qty, :rcvd_qty, :trans_qty, :reje_qty, :pend_qty, :after_qty, :modify_by, :modify_date_time)");
    				$field_name = $dbconn->GetSingleReconrd("mst_branch","branch_stock_field","branch_id",$_SESSION['_user_branch']);
    				$stmt2 = null;
    				$stmt2 = $conn->prepare("UPDATE tbl_item_stock SET ".$field_name." = :branch_stock WHERE item_id = :item_id ");
    				$after_qty =  $_REQUEST['item_curr_stock'][$x] + $_REQUEST['grn_accepted_qty'][$x];
    				$price = $dbconn->GetSingleReconrd("tbl_item_details", "item_cost_price", "item_id", $_REQUEST['temp_item_id'][$x]);
    				$data = array(
    					':trans_type' => 'GRN',
    					':trans_id' => $grn_id,
    					':branch_id' => $_SESSION['_user_branch'],
    					':trans_date' => date('Y-m-d'),
    					':item_id' => $_REQUEST['temp_item_id'][$x],
    					':item_price' => $price,
    					':before_qty' => $_REQUEST['item_curr_stock'][$x],
    					':rcvd_qty' => $_REQUEST['grn_recived_qty'][$x],
    					':trans_qty' => $_REQUEST['grn_accepted_qty'][$x],
    					':reje_qty' => $_REQUEST['grn_rejected_qty'][$x],
    					':pend_qty' => $_REQUEST['grn_pending_qty'][$x],
    					':after_qty' => $after_qty,
    					':modify_by' => $_SESSION['_user_id'],
    					':modify_date_time' => date('Y-m-d H:i:s')
    				);
    				$stmt1->execute($data);
    				$branch_item_curr_stock = $dbconn->GetSingleReconrd("tbl_item_stock", "$field_name", "item_id", $_REQUEST['temp_item_id'][$x]);
                    $after_qty2 =  (int)$branch_item_curr_stock + (int)$_REQUEST['grn_accepted_qty'][$x];
    
                    $data2 = array(
                        ':item_id' => $_REQUEST['temp_item_id'][$x],
                        ':branch_stock' => $after_qty2,
                    );
                    $stmt2->execute($data2);
			    }
			}

			/* STOCK DETAILS */

			/* ITEM DETAILS */
			// $_REQUEST['modify_by'] = $_SESSION['_user_id'];
			// $_REQUEST['modify_date_time'] = date('Y-m-d H:i:s');

			// for ($x = 0; $x < count($_REQUEST['temp_item_id']); $x++) {
			// 	$stmt1 = null;
			// 	$stmt1 = $conn->prepare("UPDATE tbl_item_details SET item_curr_stock = :item_curr_stock, modify_date_time=:modify_date_time, modify_by=:modify_by WHERE item_id = :item_id ");

			// 	$after_qty =  $_REQUEST['item_curr_stock'][$x] + $_REQUEST['grn_accepted_qty'][$x];

			// 	$data = array(
			// 		':item_id' => $_REQUEST['temp_item_id'][$x],
			// 		':item_curr_stock' => $after_qty,
			// 		':modify_date_time' => $_REQUEST['modify_date_time'],
			// 		':modify_by' => $_REQUEST['modify_by'],
			// 	);
			// 	$stmt1->execute($data);
			// }

			/* ITEM DETAILS */

			/* GRN status */

			for ($x = 0; $x < count($_REQUEST['temp_item_id']); $x++) {
				$pend_qty +=  $_REQUEST['grn_pending_qty'][$x];
			}

			if ($pend_qty <= 0) {
				$sql2 = "UPDATE tbl_purchase_order SET grn_status = 2 WHERE po_id = " . $_REQUEST['po_id'] . " ";
				$conn->query($sql2);
			} else {
				$sql2 = "UPDATE tbl_purchase_order SET grn_status = 1 WHERE po_id = " . $_REQUEST['po_id'] . " ";
				$conn->query($sql2);
			}

			$_SESSION['_msg'] = "GRN Successfully Recorded..!";
			header("location:grn_list.php");
			die;
			/* GRN status */
		} catch (Exception $e) {
			$str = filter_var($e->getMessage(), FILTER_SANITIZE_STRING);
			echo $_SESSION['_msg_err'] = $str;
		}
		$_SESSION['_msg'] = "GRN succesfully Recorded..!";
		header("location:grn_list.php");
		die();
	}
}


$disabled="";
if (isset($_REQUEST['grn_id']) && $_REQUEST['grn_id'] != "") {

	$result = $conn->query("SELECT * FROM tbl_grn WHERE grn_id = " . $_REQUEST['grn_id']);
	if ($result->rowCount() > 0) {
		$getGrn = $result->fetch(PDO::FETCH_OBJ);

		$common_remarks = $getGrn->common_remarks;
		$po_id = $getGrn->po_id;

		$_REQUEST['po_id'] = $po_id;
		if($getGrn->grn_status==2){
			$disabled="disabled";
		}
		if ($getGrn->grn_date != "0000-00-00" && $getGrn->grn_date != "") {
			$grn_date = date("Y-m-d", strtotime($getGrn->grn_date));
		}
		if ($getGrn->party_bill_date != "0000-00-00" && $getGrn->party_bill_date != "") {
			$party_bill_date = date("Y-m-d", strtotime($getGrn->party_bill_date));
		}
	}
}

if (isset($_REQUEST['po_id']) && $_REQUEST['po_id'] != "") {

	$result = $conn->query("SELECT * FROM tbl_purchase_order WHERE po_id = " . $_REQUEST['po_id']);
	if ($result->rowCount() > 0) {
		$get = $result->fetch(PDO::FETCH_OBJ);
		$supp_id = $get->supp_id;
		if ($get->po_date != "0000-00-00" && $get->po_date != "") {
			$po_date = date("d-m-Y", strtotime($get->po_date));
		}
		$po_id = $get->po_id;

		$po_remarks = $get->po_remarks;

		$po_no = leadingZeros($dbconn->GetSingleReconrd('tbl_purchase_order', 'po_slno', 'po_id', $_REQUEST['po_id']), 4);

		$resSupp = $conn->query("SELECT * FROM mst_supplier_new WHERE supp_id = '" . $get->supp_id . "'");
		if ($resSupp->rowCount() > 0) {
			$sup = $resSupp->fetch(PDO::FETCH_OBJ);
		}
	}
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta type="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title><?php echo PAGE_TITLE; ?> - GRN</title>
	<link rel="icon" href="favicon.ico" type="image/x-icon" />
	<?php include_once("inc/common/css-js.php"); ?>

</head>

<script type="text/javascript">
	var wasSubmitted=false;
	/*function fnValidate() {
		document.thisForm.submit();
	}*/
	function fnValidate2() {

		if (notSelected(document.thisForm.supp_id, "Supplier..!")) {
			return false;
		}
		if (isNull(document.thisForm.grn_date, "GRN Date..!")) {
			return false;
		}
		// if (isNull(document.thisForm.grn_refno, "Party Bill No..!")) {
		// 	return false;
		// }
		// if (isNull(document.thisForm.party_dc_no, "Party DC No...!")) {
		// 	return false;
		// }
		if($('#grn_refno').val() =='' && $('#party_dc_no').val() =='')
		{
			
		   alert('Please enter Party DC Number or Party Bill Number');
           return false;		   
		}

		if (isNull(document.thisForm.party_bill_date, "Party Bill Date..!")) {
			return false;
		}
		if (isNull(document.thisForm.common_remarks, "GRN Note..!")) {
			return false;
		}

		
		var a=0;
		var tot_accepted_qty = 0 ;
		$('.grn_accepted_qty').each(function()
		{
			var accepted_qty = $(this).val();

			tot_accepted_qty = tot_accepted_qty + accepted_qty ;
		    	
		});
        if(tot_accepted_qty == 0){
			alert('Please enter Atleast One Accepted Quantity');
			//  $('.grn_recived_qty').val('');
			return false;
		   
		}

	

		var rowCnt = parseFloat($('#grn_items').val());

		var rcvd_qty_cnt = 0;
		var rej_qty_cnt = 0;
		$(".grn_recived_qty").each(function() {
			if ($(this).closest('tr').find('.grn_recived_qty').val() != '') {
				rcvd_qty_cnt++;
			}
		});
		$(".grn_rejected_qty").each(function() {
			if ($(this).closest('tr').find('.grn_rejected_qty').val() != '') {
				rej_qty_cnt++;
			}
		});

		


		// if (rcvd_qty_cnt != rowCnt) {
		// 	alert("Please Enter All Received Qty..!");
		// 	return false;
		// }
		// if (rej_qty_cnt != rowCnt) {
		// 	alert("Please Enter All Rejected Qty..!");
		// 	return false;
		// }

		if (!wasSubmitted) {
			wasSubmitted = true;
			document.thisForm.submit();
			return true;
		}
		return false;
	}

	$(function() {

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
		
  //      if($('.grn_unit').val() === $('.item_uom_code').val()){
//alert();
		
			$('.grn_recived_qty').change(function() {
				// alert("add");
				var grn_recived_qty = $(this).closest('tr').find('.grn_recived_qty').val();
				var grn_pending_qty = $(this).closest('tr').find('.grn_pending_qty').val();

				var order_qty = $(this).closest('tr').find('.order_qty').val();

				var prev_rec_qty = $(this).closest('tr').find('.prev_rec_qty').val();
				var order_qty = $(this).closest('tr').find('.order_qty').val();
				var grn_rejected_qty = $(this).closest('tr').find('.grn_rejected_qty').val();
				
				var grn_rejected_qty = parseFloat(grn_rejected_qty)
				if (isNaN(grn_rejected_qty)) grn_rejected_qty = 0;

				var rec_qty = parseFloat(grn_recived_qty)
				if (isNaN(rec_qty)) rec_qty = 0;

				var already_rec_qty = order_qty - prev_rec_qty;

				var pen = ((order_qty - rec_qty + grn_rejected_qty) - prev_rec_qty);
				var acc = (order_qty - pen) - prev_rec_qty;
				$(this).closest('tr').find('.grn_pending_qty').val(pen.toFixed(3));
				$(this).closest('tr').find('.grn_accepted_qty').val(acc.toFixed(3));

				if (grn_recived_qty > already_rec_qty) {
					alert("Received Quantity cannot be higher than PO Quantity");
					$(this).closest('tr').find('.grn_recived_qty').val('');
					$(this).closest('tr').find('.grn_pending_qty').val('');
					$(this).closest('tr').find('.grn_accepted_qty').val('');
				}

			});
 


			$('.grn_rejected_qty').change(function() {

				//alert("add2");
				var grn_recived_qty = $(this).closest('tr').find('.grn_recived_qty').val();
				var prev_rec_qty = $(this).closest('tr').find('.prev_rec_qty').val();
				var order_qty = $(this).closest('tr').find('.order_qty').val();
				var grn_rejected_qty = $(this).closest('tr').find('.grn_rejected_qty').val();

				var grn_rejected_qty = parseFloat(grn_rejected_qty)
				if (isNaN(grn_rejected_qty)) grn_rejected_qty = 0;

				var rec_qty = parseFloat(grn_recived_qty)
				if (isNaN(rec_qty)) rec_qty = 0;


				//var pen = order_qty - rec_qty + grn_rejected_qty;
				var pen = ((order_qty - rec_qty + grn_rejected_qty) - prev_rec_qty);
				var acc = (order_qty - pen) - prev_rec_qty;
				$(this).closest('tr').find('.grn_pending_qty').val(pen.toFixed(3));
				$(this).closest('tr').find('.grn_accepted_qty').val(acc.toFixed(3));
				
				if (grn_rejected_qty > rec_qty) {
					alert("Rejected value cannot be higher than Received Value");
					$(this).closest('tr').find('.grn_rejected_qty').val('');
					$(this).closest('tr').find('.grn_pending_qty').val('');
					$(this).closest('tr').find('.grn_accepted_qty').val('');
					
				}

			}); 
		//}
	});
</script>

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
							<a href="home.php" class="breadcrumb-item"><i class="icon-home2 mr-2"></i> Home</a>
							<a href="#" class="breadcrumb-item"> Work Area</a>
							<span class="breadcrumb-item active">GRN</span>
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
						<div class="card">
							<div class="card-header bg-pgheader text-white header-elements-inline">
								<h6 class="card-title"> GRN</h6>
								<div class="header-elements">
									<div class="list-icons">
										<a class="list-icons-item" href="grn_list.php" title="GRN List"><i class="icon-arrow-left52 mr-2"></i></a>
										<a class="list-icons-item" data-action="fullscreen"></a>
									</div>
								</div>

							</div>
							<form name='thisForm' class="form-horizontal" method='POST' action="">
								<input type="hidden" name="txtHid" id="txtHid" value="<?php echo $_REQUEST['po_id']; ?>">
								<input type="hidden" name="grn_items1" id="grn_items1" value="-1">
								<input type="hidden" name="gst" id="gst" value="">
								<input type="hidden" name="po_id" id="po_id" value="<?php echo $po_id; ?>">
								<input type="hidden" name="po_qty" id="po_qty" value="<?php echo $po_qtyy; ?>">
								<input type="hidden" name="item_hsn" id="item_hsn" value="">
								<input type="hidden" name="supp_id2" id="supp_id2" value="<?php echo $sup->supp_id; ?>">
								<input type="hidden" name="grn_date2" id="grn_date2" value="<?php echo date('Y-m-d'); ?>">
								<fieldset>
									<?php
									if ($_REQUEST['grn_id'] != "") {
										$grn_no = leadingZeros($dbconn->GetSingleReconrd('tbl_grn', 'grn_slno', 'grn_id', $_REQUEST['grn_id']), 3);
									} else {
										// $grn_no = leadingZeros($dbconn->GetMaxValue('tbl_grn', 'grn_slno',' grn_finyr',$_REQUEST['grn_finyr']) + 1, 3);
										$grn_finyr = $dbconn->GetSingleReconrd("mst_finyear", "finyr", "finyr_active", 1);
										$grn_no = leadingZeros($dbconn->GetMaxValue('tbl_grn', 'grn_slno','grn_finyr = "' . $grn_finyr . '" AND branch_id="'.$_SESSION['_user_branch'].'" AND 1 ', 1) + 1, 3);
										

									}
									?>
									<div class="card-body">
										<legend class="font-weight-semibold pb-0 mb-2"><span class="po_title">GRN<?php echo $grn_no; ?></span></legend>
										<div class="form-group">

											<div class="row">
												<div class="col-lg-12">
													<div class="row">
														<div class="col-md-4 text-left" style="line-height:2rem;">
															<input type="hidden" name="po_no" id="po_no" class="form-control" value="<?php echo $po_no; ?>" />
															<b> PO Code : </b><span class="txt-highlight"><?php echo $get->po_refno; ?></span><br>
														</div>
														<div class="col-md-4 text-left" style="line-height:2rem;">
															<input type="hidden" name="po_no" id="po_no" class="form-control" value="<?php echo $po_no; ?>" />
															<b> Supplier : </b><span class="txt-highlight"><?php echo $sup->supp_name ?></span><br>
														</div>
														<div class="col-md-4 text-left" style="line-height:2rem;">
															<b> PO Date : </b><?php echo $po_date; ?><br>
														</div>
													</div>
												</div>
											</div>
											<hr>

											<div class="row">
												<label class="col-lg-1 col-form-label">Supplier <span class="text-mandatory"> *</span></label>
												<div class="col-lg-3">
													<select name="supp_id" id="supp_id" data-placeholder="Choose a Supplier.." class="select">
														<option value=""></option>
														<?php
														$dbconn = new dbhandler();
														echo $dbconn->fnFillComboFromTable_Where("supp_id", "supp_name", "mst_supplier_new", "supp_id", " WHERE supp_status = '1' AND supp_type = 'S' AND supp_approve_status = '1'") ?>
													</select>
													<script>
														document.thisForm.supp_id.value = "<?php echo $get->supp_id; ?>";
													</script>
												</div>

												<label class="col-lg-1 col-form-label">GRN Date <span class="text-mandatory"> *</span></label>
												<div class="col-lg-3">
													<input type="date" name="grn_date" id="grn_date" class="form-control" max="<?php echo date('Y-m-d'); ?>" value="<?php echo $grn_date; ?>" placeholder="Date" />
												</div>
											</div>

											<div class="row pt-2">
												<label class="col-lg-1 col-form-label">Party Bill No. <span class="text-mandatory"> *</span></label>
												<div class="col-lg-3">
													<input type="text" name="grn_refno" id="grn_refno" class="form-control" maxlength="50" placeholder="Party Bill No" value="<?php echo $getGrn->grn_refno; ?>" />
												</div>
												<label class="col-lg-1 col-form-label">Party DC No. <span class="text-mandatory"> *</span></label>
												<div class="col-lg-3">
													<input type="text" name="party_dc_no" id="party_dc_no" class="form-control" maxlength="15" placeholder="Party DC No" value="<?php echo $getGrn->party_dc_no; ?>" />
												</div>
												<label class="col-lg-1 col-form-label">Party Bill Date <span class="text-mandatory"> *</span></label>
												<div class="col-lg-3">
													<input type="date" name="party_bill_date" id="party_bill_date" class="form-control" placeholder="Party Bill Date" value="<?php echo $party_bill_date; ?>" />
												</div>
											</div>


										</div>

										<div class="row">
											<div id="show_table" class="col-md-12">
												<legend class="font-weight-semibold"><i class="icon-cart mr-2"></i>GRN Details</legend>

												<table class="table table-xs table-bordered table-dets-responsive" style="font-size: small !important;">
													<thead>
														<tr class="bg-teal">
															<th width="10%">Item Code</th>
															<th width="20%">Description</th>
															<th width="5%" style="text-align:center;">Item UOM</th>
															<th width="5%" style="text-align:center;">PO Qty</th>
															<th width="5%" style="text-align:center;">Purchase UOM</th>
															<th width="5%" style="text-align:center;">Stock Received Qty</th>
															<th width="5%">Received Quantity</th>
															<th width="5%">Rejected Quantity</th>
															<th width="5%">Pending Quantity</th>
															<th width="5%">Accepted Quantity</th>
														</tr>
													</thead>
													<tbody>
														<?php if ($_REQUEST['po_id'] != '') {

														   $dets_sql = "SELECT * FROM tbl_purchase_order_details WHERE po_id = " . $_REQUEST['po_id'];
															$result_dets = $conn->query($dets_sql);
															$rowCnt = $result_dets->rowCount();
															if ($result_dets->rowCount() > 0) {
																$rowCnt = $result_dets->rowCount();
																$sno = 1;
																$tot_po_qty = $tot_unit_price = $tot_po_value = $tot_net_value = 0;
																while ($itm = $result_dets->fetch()) {

																	$item_desciption = $dbconn->GetSingleReconrd("tbl_item_details", "item_desciption", "item_id", $itm->item_id);
																	$item_purchase_code = $dbconn->GetSingleReconrd("tbl_item_details", "item_purchase_code", "item_id", $itm->item_id);
																	$item_code = $dbconn->GetSingleReconrd("tbl_item_details", "supp_item_code", "item_id", $itm->item_id);
																	$item_uom = $dbconn->GetSingleReconrd("tbl_item_details","item_uom","item_status = '1' AND item_id",$itm->item_id);
															        $uom_code = $dbconn->GetSingleReconrd("mst_uom","uom_code","uom_status = '1' AND uom_id",$item_uom);
																	
																	
																	$field_name = $dbconn->GetSingleReconrd("mst_branch","branch_stock_field","branch_id",$_SESSION['_user_branch']);
																	$item_curr_stock = $dbconn->GetSingleReconrd("tbl_item_stock", "$field_name", "item_id",$itm->item_id);
															        //$item_curr_stock = $dbconn->GetSingleReconrd("tbl_item_details", "item_curr_stock", "item_id", $itm->item_id);

																	$all_grn_id = $dbconn->GetSingleReconrd("tbl_grn", "group_concat(grn_id SEPARATOR ', ')", "po_id IN (" . $_REQUEST['po_id'] . ") AND 1", 1);
																	$grn_status = $dbconn->GetSingleReconrd("tbl_grn", "grn_status", "grn_id", $_REQUEST['grn_id']);

																	// if ($all_grn_id != '') {
																	// 	$grn_id = $dbconn->GetSingleReconrd("tbl_grn", "grn_id", "po_id ", $_REQUEST['po_id']);
																	// 	$prev_rec_qty = $dbconn->GetSingleReconrd("tbl_grn_details", "SUM(grn_accepted_qty)", "grn_id in ($all_grn_id) AND grn_item_id ", $itm->item_id);
																	// } else {
																	// 	$prev_rec_qty = 0;
																	// }

																// echo ("SELECT SUM(grn_accepted_qty) as total_recived_qty FROM `tbl_grn_details` WHERE grn_id IN (SELECT grn_id FROM tbl_grn WHERE po_id='".$_REQUEST['po_id']."' AND grn_status >'0') AND grn_item_id='".$itm->item_id."'");	
																    $already_pi_recived_qty = $conn->query("SELECT SUM(grn_accepted_qty) as total_recived_qty FROM `tbl_grn_details` WHERE grn_id IN (SELECT grn_id FROM tbl_grn WHERE po_id='".$_REQUEST['po_id']."' AND grn_status>'0') AND grn_item_id='".$itm->item_id."'");
																	if ($already_pi_recived_qty->rowCount()>0)
																	{
																		$obj1 = $already_pi_recived_qty->fetch(PDO::FETCH_OBJ);

																		
																		// $revble = $obj->temp_po_qty - $obj1->total_recived_qty;
																		$pending_qty = $itm->po_qty - $obj1->total_recived_qty;
																		// $grn=$obj1->total_recived_qty;
																		
																	}

																	

																	
																	$grn_rcvd = $grn_reje = $grn_acpt = '0';

																	

																	if($_REQUEST['grn_id'] != ''){
																		$grn_rcvd = $dbconn->GetSingleReconrd("tbl_grn_details", "grn_recived_qty", "grn_id = ".$_REQUEST['grn_id']." AND grn_item_id ", $itm->item_id);
																		$grn_reje = $dbconn->GetSingleReconrd("tbl_grn_details", "grn_rejected_qty", "grn_id = ".$_REQUEST['grn_id']." AND grn_item_id ", $itm->item_id);
																		$grn_acpt = $dbconn->GetSingleReconrd("tbl_grn_details", "grn_accepted_qty", "grn_id = ".$_REQUEST['grn_id']." AND grn_item_id ", $itm->item_id);
																	}
																	// if($pending_qty ==0)
																	// {

																	    if($itm->po_qty <= $obj1->total_recived_qty)
																		{
																			$grn_rcvd = $grn_reje = $grn_acpt = 0;
																			echo '
																			<tr id="' . $itm->item_id . '" style="color:green;">
																				<td>' . $item_purchase_code . '
																					<input type="hidden" class="temp_item_id" name="temp_item_id[]" value="' . $itm->item_id . '" />
																					<input type="hidden" class="prev_rec_qty" name="prev_rec_qty[]" value="' . $obj1->total_recived_qty . '" />
																					<input type="hidden" class="item_curr_stock" name="item_curr_stock[]" value="' . $item_curr_stock . '" />
																					<input type="hidden" class="act_pending_qty" name="act_pending_qty[]" value="' . $pending_qty . '" />
																				</td>
																				<td>' . $item_desciption . '
																					<input type="hidden" class="item_desciption" name="item_desciption[]" value="' . $item_desciption . '" />
																					
																				</td>
																				<td style = "text-align:center;" name="item_uom_code" class="item_uom_code">'.$uom_code.'
																		            <input type="hidden" name="item_uom_code" class="item_uom_code" value="'.$uom_code.'"></td>
																				
																				<td class="text-center">' . $itm->po_qty . '
																					<input type="hidden" class="order_qty" name="order_qty[]" value="' . $itm->po_qty . '" />
																				</td>
																				<td class="text-center">' . $itm->po_unit . '
																					<input class="form-control text-center po_unit number_only"  name="po_unit[]" type="hidden" 
																					value="' . $itm->po_unit . '" maxlength="3">
																					<input type="hidden" class="grn_unit" name="grn_unit[]" value="' . $itm->po_unit . '" />
																					
																				</td>
																				<td class="text-center">' . $obj1->total_recived_qty . '
																					<input type="hidden" class="prev_rec_qty" name="prev_rec_qty[]"  value="' . $obj1->total_recived_qty . '" />
																				</td>
																			
																				<td class="text-right">
																					<input class="form-control text-center grn_recived_qty number_only_dot "  readonly  name="grn_recived_qty[]" type="text" 
																					value="'.$grn_rcvd.'" maxlength="6">
																				</td>
																				<td class="text-right">
																					<input class="form-control text-center grn_rejected_qty number_only_dot" readonly  name="grn_rejected_qty[]" type="text" 
																					value="'.$grn_reje.'" maxlength="6">
																				</td>
																				<td class="text-right">
																					<input class="form-control text-center grn_pending_qty number_only_dot" readonly tabindex="-1" name="grn_pending_qty[]" type="text" 
																					value="' . $pending_qty . '" maxlength="6">
																				</td>
																				<td class="text-right">
																					<input class="form-control text-center grn_accepted_qty number_only_dot" readonly tabindex="-1" name="grn_accepted_qty[]" type="text" 
																					value="'.$grn_acpt.'" maxlength="6">
																				</td>
																				
																			</tr>';
																		}else
																		{
																			if($itm->po_unit == $uom_code)
																			{
																				echo '
																				<tr id="' . $itm->item_id . '" >
																					<td>' . $item_purchase_code . '
																						<input type="hidden" class="temp_item_id" name="temp_item_id[]" value="' . $itm->item_id . '" />
																						<input type="hidden" class="prev_rec_qty" name="prev_rec_qty[]" value="' . $obj1->total_recived_qty . '" />
																						<input type="hidden" class="item_curr_stock" name="item_curr_stock[]" value="' . $item_curr_stock . '" />
																						<input type="hidden" class="act_pending_qty" name="act_pending_qty[]" value="' . $pending_qty . '" />
																					</td>
																					<td>' . $item_desciption . '
																						<input type="hidden" class="item_desciption" name="item_desciption[]" value="' . $item_desciption . '" />
																					</td>
																					<td style = "text-align:center; color:green;" name="item_uom_code" class="item_uom_code"><b>'.$uom_code.'</b>
																						<input type="hidden" name="item_uom_code" class="item_uom_code" value="'.$uom_code.'"></td>
																					
																					<td class="text-center">' . $itm->po_qty . '
																						<input type="hidden" class="order_qty" name="order_qty[]" value="' . $itm->po_qty . '" />
																					</td>
																					<td class="text-center">' . $itm->po_unit . '
																						<input class="form-control text-center po_unit number_only"  name="po_unit[]" type="hidden" 
																						value="' . $itm->po_unit . '" maxlength="3">
																						<input type="hidden" class="grn_unit" name="grn_unit[]" value="' . $itm->po_unit . '" />
																					</td>
																					<td class="text-center">' . $obj1->total_recived_qty. '
																						<input type="hidden" class="prev_rec_qty" name="prev_rec_qty[]" value="' . $obj1->total_recived_qty . '" />
																					</td>
																				
																					<td class="text-right">
																						<input class="form-control text-center grn_recived_qty number_only_dot"   name="grn_recived_qty[]" type="text" 
																						value="'.$grn_rcvd.'" maxlength="6">
																					</td>
																					<td class="text-right">
																						<input class="form-control text-center grn_rejected_qty number_only_dot"  name="grn_rejected_qty[]" type="text" 
																						value="'.$grn_reje.'" maxlength="6">
																					</td>
																					<td class="text-right">
																						<input class="form-control text-center grn_pending_qty number_only_dot" readonly tabindex="-1" name="grn_pending_qty[]" type="text" 
																						value="' . $pending_qty . '" maxlength="6">
																					</td>
																					<td class="text-right">
																						<input class="form-control text-center grn_accepted_qty number_only_dot" readonly tabindex="-1" name="grn_accepted_qty[]" type="text" 
																						value="'.$grn_acpt.'" maxlength="6">
																					</td>
																					
																				</tr>';
																			}else{
																						echo '
																					<tr id="' . $itm->item_id . '" style="color:red;">
																						<td>' . $item_purchase_code . '
																							<input type="hidden" class="temp_item_id" name="temp_item_id[]" value="' . $itm->item_id . '" />
																							<input type="hidden" class="prev_rec_qty" name="prev_rec_qty[]" value="' . $obj1->total_recived_qty . '" />
																							<input type="hidden" class="item_curr_stock" name="item_curr_stock[]" value="' . $item_curr_stock . '" />
																							<input type="hidden" class="act_pending_qty" name="act_pending_qty[]" value="' . $pending_qty . '" />
																						</td>
																						<td>' . $item_desciption . '
																							<input type="hidden" class="item_desciption" name="item_desciption[]" value="' . $item_desciption . '" />
																						</td>
																						<td style = "text-align:center; color:green;" name="item_uom_code" class="item_uom_code"><b>'.$uom_code.'</b>
																							<input type="hidden" name="item_uom_code" class="item_uom_code" value="'.$uom_code.'"></td>
																						
																						<td class="text-center">' . $itm->po_qty . '
																							<input type="hidden" class="order_qty" name="order_qty[]" value="' . $itm->po_qty . '" />
																						</td>
																						<td class="text-center">' . $itm->po_unit . '
																							<input class="form-control text-center po_unit number_only"  name="po_unit[]" type="hidden" 
																							value="' . $itm->po_unit . '" maxlength="3">
																							<input type="hidden" class="grn_unit" name="grn_unit[]" value="' . $itm->po_unit . '" />
																						</td>
																						<td class="text-center">' . $obj1->total_recived_qty. '
																							<input type="hidden" class="prev_rec_qty" name="prev_rec_qty[]" value="' . $obj1->total_recived_qty . '" />
																						</td>
																					
																						<td class="text-right">
																							<input class="form-control text-center grn_recived_qty number_only_dot"   name="grn_recived_qty[]" type="text" 
																							value="'.$grn_rcvd.'" maxlength="6">
																						</td>
																						<td class="text-right">
																							<input class="form-control text-center grn_rejected_qty number_only_dot"  name="grn_rejected_qty[]" type="text" 
																							value="'.$grn_reje.'" maxlength="6">
																						</td>
																						<td class="text-right">
																							<input class="form-control text-center grn_pending_qty number_only_dot" readonly tabindex="-1" name="grn_pending_qty[]" type="text" 
																							value="' . $pending_qty . '" maxlength="6">
																						</td>
																						<td class="text-right">
																							<input class="form-control text-center grn_accepted_qty number_only_dot" readonly tabindex="-1" name="grn_accepted_qty[]" type="text" 
																							value="'.$grn_acpt.'" maxlength="6">
																						</td>
																						
																					</tr>';
																				}
																			
																		}
																	// }
																	$sno++;
																	$tot_po_qty += $itm->po_qty;
																	$tot_unit_price += $itm->cost_price;
																	$tot_po_value += $itm->po_value;
																	$tot_net_value += $itm->net_value;
																}
															}
														}
														?>
													</tbody>
												</table>
												<input type="hidden" name="grn_items" id="grn_items" value="<?php if ($rowCnt > 0) {
																												echo $rowCnt;
																											} else {
																												echo '-1';
																											} ?>">
											</div>
										</div>
										<script type="text/javascript">
											//emove_item(0);
										</script>


										<div class="form-group pl-0 col-md-12 pt-4">
											<label>GRN Note <span class="text-mandatory">*</span></label>
											<textarea name="common_remarks" maxlength="500" id="common_remarks" class="form-control"><?php echo $common_remarks; ?></textarea>
										</div>
									</div>
									
										
										    	<div class="card-footer text-center">
													<?php

													if (isset($_REQUEST["grn_id"]) && ($_REQUEST["grn_id"] != '')) { ?>
														<INPUT class="btn btn-primary mr-2" type="submit" name="UPDATE" value="UPDATE">
														<INPUT class="btn btn-success mr-2" type="submit" name="FINALIZE" <?php echo $disabled; ?> value="Save GRN - Stock Will be Updated" onclick="return fnValidate2();">
														<INPUT class="btn btn-light" type="button" name="Cancel" value="Cancel" onClick="javascript:window.location.href='grn_list.php'">
														<input type="hidden" name="txtHid" id="txtHid" value="<?php echo $_REQUEST['grn_id']; ?>">
														
													
													<?php }  elseif(isset($_REQUEST['po_id']) ){ ?> 
														<INPUT class="btn btn-primary mr-2" type="submit" name="Draft" id="Draft" value="Draft" onclick="return fnValidate2();">
														<INPUT class="btn btn-success mr-2" type="submit" name="FINALIZE" <?php echo $disabled; ?> value="Save GRN - Stock Will be Updated" onclick="return fnValidate2();">
														<INPUT class="btn btn-light" type="button" name="Cancel" value="Cancel" onClick="javascript:window.location.href='grn_list.php'">
														<input type="hidden" name="txtHid" value="0">
													<?php } ?>
												</div>
										
								</fieldset>
							</form>
						</div>
					</div>
				</div>
			</div>
	<?php include("inc/common/footer.php") ?>

		</div>
	</div>
</body>

</html>