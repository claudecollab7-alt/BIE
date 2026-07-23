<?php
ob_start();
session_start();

require_once("inc/common/userclass.php");
require_once("inc/common/css-js.php");

isAdmin();

$conn = new dbconnect();
$dbconn = new dbhandler();

// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);


$update_id = $_REQUEST['txtHid'];

$_REQUEST['quo_finyr'] = $dbconn->GetSingleReconrd("mst_finyear", "finyr", "finyr_active", 1);
$_REQUEST['branch'] = $dbconn->GetSingleReconrd("mst_branch", "branch_code", "branch_id='" . $_SESSION['_user_branch'] . "' AND branch_status", 1);

$supp_code = $dbconn->GetSingleReconrd("mst_supplier_new", "supp_code", "supp_id", $_REQUEST['supp_id']);

$quo_sts = $dbconn->GetSingleReconrd("tbl_quotation", "quo_version", "quo_id", "$update_id");

if ($quo_sts == 0) {
	$_REQUEST['quo_refno'] = leadingZeros($_REQUEST['quo_no'], 3) . '/BIE/' . $_REQUEST['branch'] . '/' . $supp_code . '/Q/' . $_REQUEST['quo_finyr'];
} elseif ($quo_sts > 0) {
	$_REQUEST['quo_refno'] = leadingZeros($_REQUEST['quo_no'], 3) . '-' . $_REQUEST['requote'] . '/BIE/' . $_REQUEST['branch'] . '/' . $supp_code . '/Q/' . $_REQUEST['quo_finyr'];
} else {
	$_REQUEST['quo_refno'] = leadingZeros($_REQUEST['quo_no'], 3) . '/BIE/' . $_REQUEST['branch'] . '/' . $supp_code . '/Q/' . $_REQUEST['quo_finyr'];
}


//----------------------------- Requote & Quotation Save-------------------------------------//

$_REQUEST['created_dtm'] = date('Y-m-d H:i:s');
if (isset($_POST['SAVE']) && $_POST['status'] == 'requote') {
	$_REQUEST['select_branch_id'] = (isset($_REQUEST['select_branch_id'])) ? ($_REQUEST['select_branch_id']) : '';
	try {
		$quo_slno = $dbconn->GetSingleReconrd('tbl_quotation', 'quo_slno', 'quo_id', $_REQUEST['txtHid']);
		$quo_version = $dbconn->GetMaxValue('tbl_quotation', 'quo_version', 'quo_finyr="' . $_REQUEST['quo_finyr'] . '" AND quo_slno', $quo_slno) + 1;
		$requote = $dbconn->requote($quo_version);

		$_REQUEST['quo_refno'] = leadingZeros($_REQUEST['quo_no'], 3) . '-' . $requote . '/BIE/' . $_REQUEST['branch'] . '/' . $supp_code . '/Q/' . $_REQUEST['quo_finyr'];

		$stmt = null;

		$stmt = $conn->prepare("INSERT INTO tbl_quotation (quo_finyr, quo_slno, quo_version, quo_refno, quo_date, prev_quo_id,  supp_id, ref_phone_no, ref_email, show_all_image, branch_id, quo_value, terms_con, terms_con_id1, terms_con_id2, terms_con_id3, terms_con_id4, terms_con_id5, terms_con_id6, terms_con_id7, terms_con_id8, modify_date_time, modify_by,bie_branch_id) VALUES (:quo_finyr, :quo_slno, :quo_version, :quo_refno, :quo_date, :prev_quo_id, :supp_id, :ref_phone_no, :ref_email, :show_all_image, :branch_id, :quo_value,:terms_con, :terms_con_id1, :terms_con_id2, :terms_con_id3, :terms_con_id4, :terms_con_id5, :terms_con_id6, :terms_con_id7, :terms_con_id8, :modify_date_time, :modify_by, :bie_branch_id )");

		$data = array(
			':quo_finyr' => $_REQUEST['quo_finyr'],
			':quo_slno' => $_REQUEST['quo_sln'],
			':quo_version' => $_REQUEST['quo_version'],
			':quo_refno' => $_REQUEST['quo_refno'],
			':quo_date' => $_REQUEST['quo_date'],
			':prev_quo_id' => $_REQUEST['txtHid'],
			':supp_id' => $_REQUEST['supp_id'],
			':ref_phone_no' => $_REQUEST['ref_phone_no'],
			':ref_email' => $_REQUEST['ref_email'],
			':show_all_image' => $_REQUEST['show_all_image'],
			':branch_id' => $_REQUEST['select_branch_id'],
			':quo_value' => $_REQUEST['txt_final_total'],
			':terms_con' => $_REQUEST['tc_group'],
			':terms_con_id1' => $_REQUEST['terms_con_id1'],
			':terms_con_id2' => $_REQUEST['terms_con_id2'],
			':terms_con_id3' => $_REQUEST['terms_con_id3'],
			':terms_con_id4' => $_REQUEST['terms_con_id4'],
			':terms_con_id5' => $_REQUEST['terms_con_id5'],
			':terms_con_id6' => $_REQUEST['terms_con_id6'],
			':terms_con_id7' => $_REQUEST['terms_con_id7'],
			':terms_con_id8' => $_REQUEST['terms_con_id8'],
			':modify_date_time' => $_REQUEST['created_dtm'],
			':modify_by' => $_SESSION['_user_id'],
			':bie_branch_id' => $_SESSION['_user_branch']
		);
		$stmt->execute($data);
		$last_id = $conn->lastInsertId();
		//$conn->query("UPDATE tbl_quotation SET quo_des_edit= 1 WHERE quo_id=" . $_REQUEST['txtHid']);

		//------Individual - Group SAVE---------//

		$stmt = null;
		$stmt = $conn->prepare("INSERT INTO tbl_quotation_details (quo_id,  item_id, quo_qty, show_image, quo_unit, selling_price, quo_discount, quo_discount_amt, vat, quo_value, tax_value, net_value) 
	    						VALUES (:quo_id, :item_id, :quo_qty, :show_image,  :quo_unit, :selling_price, :quo_discount, :quo_discount_amt, :vat, :quo_value, :tax_value, :net_value)");

		$row_count = count($_REQUEST['temp_item_id']);
		for ($n = 0; $n < $row_count; $n++) {

			$data = array(
				':quo_id' => $last_id,
				':item_id' => $_REQUEST['temp_item_id'][$n],
				':quo_qty' => $_REQUEST['temp_qty'][$n],
				':show_image' => $_REQUEST['show_image'][$n],
				':quo_unit' => $_REQUEST['temp_unit'][$n],
				':selling_price' => $_REQUEST['temp_selling_price'][$n],
				':quo_discount' => $_REQUEST['temp_discount_per'][$n],
				':quo_discount_amt' => $_REQUEST['temp_discount_val'][$n],
				':vat' => $_REQUEST['temp_vat'][$n],
				':quo_value' => $_REQUEST['temp_quo_price'][$n],
				':tax_value' => $_REQUEST['quo_pack_taxable_value'][$n],
				':net_value' => $_REQUEST['temp_net_amt'][$n]
			);
			$stmt->execute($data);
		}

		//----------------------package Save-----------------//

		$stmt = null;

		$stmt = $conn->prepare("INSERT INTO tbl_quo_pack_details (quo_id, quo_pack_decp, quo_pack_percent, quo_pack_text, quo_pack_taxable_val, gst_id, quo_pack_vat, quo_pack_value, quo_pack_total)
								VALUES (:quo_id, :quo_pack_decp, :quo_pack_percent, :quo_pack_text, :quo_pack_taxable_val, :gst_id, :quo_pack_vat, :quo_pack_value, :quo_pack_total)");

		$row_count = ($_REQUEST['pack_id']) ? count($_REQUEST['pack_id']) : 0;
		if ($row_count > 0) {
			for ($n = 0; $n < $row_count; $n++) {

				$data = array(
					':quo_id' => $last_id,
					':quo_pack_decp' => $_REQUEST['pack_id'][$n],
					':quo_pack_percent' => $_REQUEST['quo_pack_per_fa'][$n],
					':quo_pack_text' => $_REQUEST['quo_pack_per_fa_value'][$n],
					':quo_pack_taxable_val' => $_REQUEST['quo_pack_taxable_val'][$n],
					':gst_id' => $_REQUEST['quo_pack_gst_id'][$n],
					':quo_pack_vat' => $_REQUEST['quo_pack_gst_per'][$n],
					':quo_pack_value' => $_REQUEST['quo_pack_gst_amt'][$n],
					':quo_pack_total' => $_REQUEST['quo_pack_total'][$n]
				);
				$stmt->execute($data);
			}
		}
	} catch (Exception $e) {
		$str = filter_var($e->getMessage(), FILTER_SANITIZE_STRING);
		$_SESSION['_msg_err'] = $str;
	}
	$_SESSION['_msg'] = "Quotation succesfully Saved..!";
	header("location:lst_quotation.php");
	die();
} else if (isset($_POST['SAVE'])) {

	$_REQUEST['select_branch_id'] = (isset($_REQUEST['select_branch_id'])) ? ($_REQUEST['select_branch_id']) : '';
	$_REQUEST['branch'] = $dbconn->GetSingleReconrd("mst_branch", "branch_code", "branch_id='" . $_SESSION['_user_branch'] . "' AND branch_status", 1);
	$_REQUEST['quo_slno']  = leadingZeros($dbconn->GetMaxValue('tbl_quotation', 'quo_slno', 'quo_finyr', $_REQUEST['quo_finyr']) + 1, 3);
	$_REQUEST['quo_refno'] = leadingZeros($_REQUEST['quo_slno'], 3) . '/BIE/' . $_REQUEST['branch'] . '/' . $supp_code . '/Q/' . $_REQUEST['quo_finyr'];



	$stmt = null;

	$stmt = $conn->prepare("INSERT INTO tbl_quotation (quo_finyr, quo_refno, quo_slno, quo_date, supp_id, ref_phone_no, ref_email, show_all_image, branch_id, quo_value, created_by, created_dtm,terms_con, terms_con_id1, terms_con_id2, terms_con_id3, terms_con_id4, terms_con_id5, terms_con_id6, terms_con_id7, terms_con_id8, bie_branch_id) 
							VALUES (:quo_finyr, :quo_refno, :quo_slno, :quo_date, :supp_id, :ref_phone_no, :ref_email, :show_all_image, :branch_id, :quo_value, :created_by, :created_dtm,:terms_con, :terms_con_id1, :terms_con_id2, :terms_con_id3, :terms_con_id4, :terms_con_id5, :terms_con_id6, :terms_con_id7, :terms_con_id8, :bie_branch_id)");
	$data = array(
		':quo_finyr' => $_REQUEST['quo_finyr'],
		':quo_refno' => $_REQUEST['quo_refno'],
		':quo_slno' => $_REQUEST['quo_slno'],
		':quo_date' => $_REQUEST['quo_date'],
		':supp_id' => $_REQUEST['supp_id'],
		':ref_phone_no' => $_REQUEST['ref_phone_no'],
		':ref_email' => $_REQUEST['ref_email'],
		':show_all_image' => $_REQUEST['show_all_image'],
		':branch_id' => $_REQUEST['select_branch_id'],
		':quo_value' => $_REQUEST['txt_final_total'],
		':terms_con' => $_REQUEST['tc_group'],
		':terms_con_id1' => $_REQUEST['terms_con_id1'],
		':terms_con_id2' => $_REQUEST['terms_con_id2'],
		':terms_con_id3' => $_REQUEST['terms_con_id3'],
		':terms_con_id4' => $_REQUEST['terms_con_id4'],
		':terms_con_id5' => $_REQUEST['terms_con_id5'],
		':terms_con_id6' => $_REQUEST['terms_con_id6'],
		':terms_con_id7' => $_REQUEST['terms_con_id7'],
		':terms_con_id8' => $_REQUEST['terms_con_id8'],
		':created_by' =>  $_SESSION['_user_id'],
		':created_dtm' => $_REQUEST['created_dtm'],
		':bie_branch_id' => $_SESSION['_user_branch']
	);
	$stmt->execute($data);
	$last_id = $conn->lastInsertId();

	//----------------------------------------------product order details--------------------------------------------------------//

	$stmt = null;
	$stmt = $conn->prepare("INSERT INTO tbl_quotation_details (quo_id,  item_id, quo_qty, show_image, quo_unit, selling_price, quo_discount, quo_discount_amt, vat, quo_value, tax_value, net_value) 
		                    VALUES (:quo_id, :item_id, :quo_qty, :show_image, :quo_unit, :selling_price, :quo_discount, :quo_discount_amt, :vat, :quo_value, :tax_value, :net_value)");

	$row_count = count($_REQUEST['temp_item_id']);
	for ($n = 0; $n < $row_count; $n++) {
		$data = array(
			':quo_id' => $last_id,
			':item_id' => $_REQUEST['temp_item_id'][$n],
			':quo_qty' => $_REQUEST['temp_qty'][$n],
			':show_image' => $_REQUEST['show_image'][$n],
			':quo_unit' => $_REQUEST['temp_unit'][$n],
			':selling_price' => $_REQUEST['temp_selling_price'][$n],
			':quo_discount' => $_REQUEST['temp_discount_per'][$n],  //
			':quo_discount_amt' => $_REQUEST['temp_discount_val'][$n], //
			':vat' => $_REQUEST['temp_vat'][$n],
			':quo_value' => $_REQUEST['temp_quo_price'][$n],
			':tax_value' => $_REQUEST['quo_pack_taxable_value'][$n], ///
			':net_value' => $_REQUEST['temp_net_amt'][$n]

		);
		$stmt->execute($data);
	}

	//------------------------package----------------------//

	$quo_pack_total = 0;

	$stmt = null;

	$stmt = $conn->prepare("INSERT INTO tbl_quo_pack_details (quo_id, quo_pack_decp, quo_pack_percent, quo_pack_text, quo_pack_taxable_val, gst_id, quo_pack_vat, quo_pack_value, quo_pack_total)
		                    VALUES (:quo_id, :quo_pack_decp, :quo_pack_percent, :quo_pack_text, :quo_pack_taxable_val, :gst_id, :quo_pack_vat, :quo_pack_value, :quo_pack_total)");
	$row_count = ($_REQUEST['pack_id']) ? count($_REQUEST['pack_id']) : 0;
	if ($row_count > 0) {
		for ($n = 0; $n < $row_count; $n++) {
			$quo_pack_total = isset($_REQUEST['quo_pack_total'][$n]) ? $_REQUEST['quo_pack_total'][$n] : '';
			$data = array(
				':quo_id' => $last_id,
				':quo_pack_decp' => $_REQUEST['pack_id'][$n],
				':quo_pack_percent' => $_REQUEST['quo_pack_per_fa'][$n],
				':quo_pack_text' => $_REQUEST['quo_pack_per_fa_value'][$n],
				':quo_pack_taxable_val' => $_REQUEST['quo_pack_taxable_val'][$n],
				':gst_id' => $_REQUEST['quo_pack_gst_id'][$n],
				':quo_pack_vat' => $_REQUEST['quo_pack_gst_per'][$n],
				':quo_pack_value' => $_REQUEST['quo_pack_gst_amt'][$n],
				':quo_pack_total' => $quo_pack_total
			);
			$stmt->execute($data);
		}
	}
	$_SESSION['_msg'] = "Quotation succesfully Saved..!";
	header("location:lst_quotation.php");
	die();
}

//--------------------------------------------------UPDATE---------------------------------------------------------//


if (isset($_POST['UPDATE'])) {
	$_REQUEST['select_branch_id'] = (isset($_REQUEST['select_branch_id'])) ? ($_REQUEST['select_branch_id']) : '';
	$update_id = $_REQUEST['txtHid'];

	$stmt = null;

	$stmt = $conn->prepare("UPDATE tbl_quotation SET quo_finyr = :quo_finyr, quo_refno = :quo_refno, quo_slno = :quo_slno,quo_version = :quo_version, quo_date = :quo_date, supp_id = :supp_id,ref_phone_no = :ref_phone_no,  ref_email = :ref_email, show_all_image = :show_all_image, branch_id = :branch_id, quo_value = :quo_value, terms_con = :terms_con,
	terms_con_id1 = :terms_con_id1, terms_con_id2 = :terms_con_id2, terms_con_id3 = :terms_con_id3, terms_con_id4 = :terms_con_id4, terms_con_id5 = :terms_con_id5,terms_con_id6 = :terms_con_id6, terms_con_id7 = :terms_con_id7, terms_con_id8 = :terms_con_id8, modify_date_time = :modify_date_time,  modify_by = :modify_by, quo_approve_status = :quo_approve_status, quo_verify_status=:quo_verify_status WHERE quo_id = :quo_id");

	$data = array(
		':quo_id' => $update_id,
		':quo_finyr' => $_REQUEST['quo_finyr'],
		':quo_refno' => $_REQUEST['quo_refno'],
		':quo_slno' => $_REQUEST['quo_slno'],
		':quo_version' => $_REQUEST['quo_version'],
		':quo_date' => $_REQUEST['quo_date'],
		':supp_id' => $_REQUEST['supp_id'],
		':ref_phone_no' => $_REQUEST['ref_phone_no'],
		':ref_email' => $_REQUEST['ref_email'],
		':show_all_image' => $_REQUEST['show_all_image'],
		':branch_id' => $_REQUEST['select_branch_id'],
		':quo_value' => $_REQUEST['txt_final_total'],
		':terms_con' => $_REQUEST['tc_group'],
		':terms_con_id1' => $_REQUEST['terms_con_id1'],
		':terms_con_id2' => $_REQUEST['terms_con_id2'],
		':terms_con_id3' => $_REQUEST['terms_con_id3'],
		':terms_con_id4' => $_REQUEST['terms_con_id4'],
		':terms_con_id5' => $_REQUEST['terms_con_id5'],
		':terms_con_id6' => $_REQUEST['terms_con_id6'],
		':terms_con_id7' => $_REQUEST['terms_con_id7'],
		':terms_con_id8' => $_REQUEST['terms_con_id8'],
		':modify_date_time' => $_REQUEST['created_dtm'],
		':modify_by' => $_SESSION['_user_id'],
		':quo_approve_status' => 0,
		':quo_verify_status' => 0

	);
	$stmt->execute($data);


	//------------quotation Details update---------//


	$sql =  "DELETE FROM tbl_quotation_details WHERE quo_id = '" . $update_id . "'";
	$result = $conn->prepare($sql);
	$result->execute();

	$stmt = null;
	$stmt = $conn->prepare("INSERT INTO tbl_quotation_details (quo_id,  item_id, quo_qty, show_image,  quo_unit, selling_price, quo_discount, quo_discount_amt, vat, quo_value, tax_value, net_value) 
							VALUES (:quo_id, :item_id, :quo_qty, :show_image, :quo_unit, :selling_price, :quo_discount, :quo_discount_amt, :vat, :quo_value, :tax_value, :net_value)");
	$row_count = count($_REQUEST['temp_item_id']);

	for ($n = 0; $n < $row_count; $n++) {
		$data = array(
			':quo_id' => $update_id,
			':item_id' => $_REQUEST['temp_item_id'][$n],
			':quo_qty' => $_REQUEST['temp_qty'][$n],
			':show_image' => $_REQUEST['show_image'][$n],
			':quo_unit' => $_REQUEST['temp_unit'][$n],
			':selling_price' => $_REQUEST['temp_selling_price'][$n],
			':quo_discount' => $_REQUEST['temp_discount_per'][$n],
			':quo_discount_amt' => $_REQUEST['temp_discount_val'][$n],
			':vat' => $_REQUEST['temp_vat'][$n],
			':quo_value' => $_REQUEST['temp_quo_price'][$n],
			':tax_value' => $_REQUEST['quo_pack_taxable_value'][$n],
			':net_value' => $_REQUEST['temp_net_amt'][$n]

		);
		$stmt->execute($data);
	}

	///----------------------package Update-----------------//


	$sql =  "DELETE FROM tbl_quo_pack_details WHERE quo_id = '" . $update_id . "'";
	$result = $conn->prepare($sql);
	$result->execute();

	$quo_pack_total = 0;

	$stmt = null;

	$stmt = $conn->prepare("INSERT INTO tbl_quo_pack_details (quo_id, quo_pack_decp, quo_pack_percent, quo_pack_text, quo_pack_taxable_val, gst_id, quo_pack_vat, quo_pack_value, quo_pack_total)
							VALUES (:quo_id, :quo_pack_decp, :quo_pack_percent, :quo_pack_text, :quo_pack_taxable_val, :gst_id, :quo_pack_vat, :quo_pack_value, :quo_pack_total)");

	$row_count = ($_REQUEST['pack_id']) ? count($_REQUEST['pack_id']) : 0;
	if ($row_count > 0) {
		for ($n = 0; $n < $row_count; $n++) {
			$_REQUEST['quo_pack_total'][$n] = isset($_REQUEST['quo_pack_total'][$n]) ? $_REQUEST['quo_pack_total'][$n] : '';
			$data = array(
				':quo_id' => $update_id,
				':quo_pack_decp' => $_REQUEST['pack_id'][$n],
				':quo_pack_percent' => $_REQUEST['quo_pack_per_fa'][$n],
				':quo_pack_text' => $_REQUEST['quo_pack_per_fa_value'][$n],
				':quo_pack_taxable_val' => $_REQUEST['quo_pack_taxable_val'][$n],
				':gst_id' => $_REQUEST['quo_pack_gst_id'][$n],
				':quo_pack_vat' => $_REQUEST['quo_pack_gst_per'][$n],
				':quo_pack_value' => $_REQUEST['quo_pack_gst_amt'][$n],
				':quo_pack_total' => $_REQUEST['quo_pack_total'][$n]
			);
			$stmt->execute($data);
		}
	}
	$_SESSION['_msg'] = "Quotation succesfully Updated..!";
	header("location:lst_quotation.php");
	die();
}

//-------------------Send Approval Details Data-----------------//

if (isset($_POST['FINALIZE'])) {
	$_REQUEST['select_branch_id'] = (isset($_REQUEST['select_branch_id'])) ? ($_REQUEST['select_branch_id']) : '';
	$update_id = $_REQUEST['txtHid'];
	$_REQUEST['branch'] = $dbconn->GetSingleReconrd("mst_branch", "branch_code", "branch_id='" . $_SESSION['_user_branch'] . "' AND branch_status", 1);
	if (isset($_REQUEST["txtHid"]) && $_REQUEST['status'] == 'requote') {
		$quo_slno = $dbconn->GetSingleReconrd('tbl_quotation', 'quo_slno', 'quo_id', $_REQUEST['txtHid']);
		$quo_version = $dbconn->GetMaxValue('tbl_quotation', 'quo_version', 'quo_finyr="' . $_REQUEST['quo_finyr'] . '" AND quo_slno', $quo_slno) + 1;
		$requote = $dbconn->requote($quo_version);
		$_REQUEST['quo_refno'] = leadingZeros($_REQUEST['quo_no'], 3) . '-' . $requote . '/BIE/' . $_REQUEST['branch'] . '/' . $supp_code . '/Q/' . $_REQUEST['quo_finyr'];
		try {
			$quo_approve_id = $dbconn->GetSingleReconrd("tbl_task_user", "user_id", "task_id", 1);
			$stmt = null;
			$stmt = $conn->prepare("INSERT INTO tbl_quotation (quo_finyr, quo_refno, quo_slno, quo_date, supp_id,quo_version, ref_phone_no, ref_email, show_all_image, branch_id, quo_value, created_by, created_dtm,terms_con, terms_con_id1, terms_con_id2, terms_con_id3, terms_con_id4, terms_con_id5, terms_con_id6, terms_con_id7, terms_con_id8, quo_verify_status, quo_verify_by, quo_verify_date_time,bie_branch_id) 
							VALUES (:quo_finyr, :quo_refno, :quo_slno, :quo_date, :supp_id,:quo_version, :ref_phone_no, :ref_email, :show_all_image, :branch_id, :quo_value, :created_by, :created_dtm,:terms_con, :terms_con_id1, :terms_con_id2, :terms_con_id3, :terms_con_id4, :terms_con_id5, :terms_con_id6, :terms_con_id7, :terms_con_id8, :quo_verify_status, :quo_verify_by, :quo_verify_date_time,:bie_branch_id)");
			$data = array(
				':quo_finyr' => $_REQUEST['quo_finyr'],
				':quo_refno' => $_REQUEST['quo_refno'],
				':quo_slno' => $_REQUEST['quo_slno'],
				':quo_date' => $_REQUEST['quo_date'],
				':supp_id' => $_REQUEST['supp_id'],
				':quo_version' => $_REQUEST['quo_version'],
				':ref_phone_no' => $_REQUEST['ref_phone_no'],
				':ref_email' => $_REQUEST['ref_email'],
				':show_all_image' => $_REQUEST['show_all_image'],
				':branch_id' => $_REQUEST['select_branch_id'],
				':quo_value' => $_REQUEST['txt_final_total'],
				':terms_con' => $_REQUEST['tc_group'],
				':terms_con_id1' => $_REQUEST['terms_con_id1'],
				':terms_con_id2' => $_REQUEST['terms_con_id2'],
				':terms_con_id3' => $_REQUEST['terms_con_id3'],
				':terms_con_id4' => $_REQUEST['terms_con_id4'],
				':terms_con_id5' => $_REQUEST['terms_con_id5'],
				':terms_con_id6' => $_REQUEST['terms_con_id6'],
				':terms_con_id7' => $_REQUEST['terms_con_id7'],
				':terms_con_id8' => $_REQUEST['terms_con_id8'],
				':created_by' =>  $_SESSION['_user_id'],
				':created_dtm' => $_REQUEST['created_dtm'],
				':quo_verify_status' => 1,
				':quo_verify_by' => $_SESSION['_user_id'],
				':quo_verify_date_time' => date('Y-m-d H:i:s'),
				':bie_branch_id' => $_SESSION['_user_branch']
			);
			$stmt->execute($data);
			$last_id = $conn->lastInsertId();
			//$conn->query("UPDATE tbl_quotation SET quo_des_edit= 1 WHERE quo_id=" . $_REQUEST['txtHid']);

			//-------------------------Direct Approval Details------------------------//

			$stmt = null;
			$stmt = $conn->prepare("INSERT INTO tbl_quotation_details (quo_id,  item_id, quo_qty, show_image, quo_unit, selling_price, quo_discount, quo_discount_amt, vat, quo_value, tax_value, net_value) 
		                    VALUES (:quo_id, :item_id, :quo_qty, :show_image, :quo_unit, :selling_price, :quo_discount, :quo_discount_amt, :vat, :quo_value, :tax_value, :net_value)");

			$row_count = count($_REQUEST['temp_item_id']);
			for ($n = 0; $n < $row_count; $n++) {
				$data = array(
					':quo_id' => $last_id,
					':item_id' => $_REQUEST['temp_item_id'][$n],
					':quo_qty' => $_REQUEST['temp_qty'][$n],
					':show_image' => $_REQUEST['show_image'][$n],
					':quo_unit' => $_REQUEST['temp_unit'][$n],
					':selling_price' => $_REQUEST['temp_selling_price'][$n],
					':quo_discount' => $_REQUEST['temp_discount_per'][$n],
					':quo_discount_amt' => $_REQUEST['temp_discount_val'][$n],
					':vat' => $_REQUEST['temp_vat'][$n],
					':quo_value' => $_REQUEST['temp_quo_price'][$n],
					':tax_value' => $_REQUEST['quo_pack_taxable_value'][$n],
					':net_value' => $_REQUEST['temp_net_amt'][$n]

				);
				$stmt->execute($data);
			}

			//-----------------------Direct Approval Package Data----------------------//

			$quo_pack_total = 0;
			$stmt = null;
			$stmt = $conn->prepare("INSERT INTO tbl_quo_pack_details (quo_id, quo_pack_decp, quo_pack_percent, quo_pack_text, quo_pack_taxable_val, gst_id, quo_pack_vat, quo_pack_value, quo_pack_total)
		                    VALUES (:quo_id, :quo_pack_decp, :quo_pack_percent, :quo_pack_text, :quo_pack_taxable_val, :gst_id, :quo_pack_vat, :quo_pack_value, :quo_pack_total)");

			$row_count = ($_REQUEST['pack_id']) ? count($_REQUEST['pack_id']) : 0;
			if ($row_count > 0) {
				for ($n = 0; $n < $row_count; $n++) {
					$quo_pack_total = isset($_REQUEST['quo_pack_total'][$n]) ? $_REQUEST['quo_pack_total'][$n] : '';
					$data = array(
						':quo_id' => $last_id,
						':quo_pack_decp' => $_REQUEST['pack_id'][$n],
						':quo_pack_percent' => $_REQUEST['quo_pack_per_fa'][$n],
						':quo_pack_text' => $_REQUEST['quo_pack_per_fa_value'][$n],
						':quo_pack_taxable_val' => $_REQUEST['quo_pack_taxable_val'][$n],
						':gst_id' => $_REQUEST['quo_pack_gst_id'][$n],
						':quo_pack_vat' => $_REQUEST['quo_pack_gst_per'][$n],
						':quo_pack_value' => $_REQUEST['quo_pack_gst_amt'][$n],
						':quo_pack_total' => $quo_pack_total
					);
					$stmt->execute($data);
				}
			}
		} catch (Exception $e) {
			$str = filter_var($e->getMessage(), FILTER_SANITIZE_STRING);
			$_SESSION['_msg_err'] = $str;
		}
		$_SESSION['_msg'] = "Quotation succesfully Saved..!";
		header("location:lst_quotation.php");
		die();
	} else if ($_REQUEST['txtHid'] != '' && $_REQUEST['txtHid'] > 0) {
		try {
			//$quo_approve_id = $dbconn->GetSingleReconrd("tbl_task_user", "user_id", "task_id", 1);
			$stmt1 = null;
			$stmt1 = $conn->prepare("UPDATE tbl_quotation SET quo_date = :quo_date, supp_id = :supp_id ,quo_version = :quo_version, ref_phone_no = :ref_phone_no,ref_email = :ref_email, show_all_image = :show_all_image , branch_id = :branch_id,terms_con = :terms_con, quo_value = :quo_value,terms_con_id1 = :terms_con_id1,terms_con_id2 = :terms_con_id2,terms_con_id3 = :terms_con_id3,terms_con_id4 = :terms_con_id4,terms_con_id5 = :terms_con_id5, terms_con_id6 = :terms_con_id6,terms_con_id7 = :terms_con_id7, terms_con_id8 = :terms_con_id8,created_by = :created_by, quo_verify_status = :quo_verify_status, quo_verify_by = :quo_verify_by, quo_verify_date_time = :quo_verify_date_time, quo_approve_status = :quo_approve_status WHERE quo_id = :quo_id");

			$data1 = array(
				':quo_id' => $update_id,
				':quo_date' => $_REQUEST['quo_date'],
				':supp_id' => $_REQUEST['supp_id'],
				':quo_version' => $_REQUEST['quo_version'],
				':ref_phone_no' => $_REQUEST['ref_phone_no'],
				':ref_email' => $_REQUEST['ref_email'],
				':show_all_image' => $_REQUEST['show_all_image'],
				':branch_id' => $_REQUEST['select_branch_id'],
				':quo_value' => $_REQUEST['txt_final_total'],
				':terms_con' => $_REQUEST['tc_group'],
				':terms_con_id1' => $_REQUEST['terms_con_id1'],
				':terms_con_id2' => $_REQUEST['terms_con_id2'],
				':terms_con_id3' => $_REQUEST['terms_con_id3'],
				':terms_con_id4' => $_REQUEST['terms_con_id4'],
				':terms_con_id5' => $_REQUEST['terms_con_id5'],
				':terms_con_id6' => $_REQUEST['terms_con_id6'],
				':terms_con_id7' => $_REQUEST['terms_con_id7'],
				':terms_con_id8' => $_REQUEST['terms_con_id8'],
				':created_by' =>  $_SESSION['_user_id'],
				':quo_verify_status' => 1,
				':quo_approve_status' => 0,
				':quo_verify_by' => $_SESSION['_user_id'],
				':quo_verify_date_time' => date('Y-m-d H:i:s')
			);
			$stmt1->execute($data1);

			//------------Send Approval Quotation Data-----------//

			$sql =  "DELETE FROM tbl_quotation_details WHERE quo_id = '" . $update_id . "'";
			$result = $conn->prepare($sql);
			$result->execute();

			$stmt = null;

			$stmt = $conn->prepare("INSERT INTO tbl_quotation_details (quo_id,  item_id, quo_qty, show_image,  quo_unit, selling_price, quo_discount, quo_discount_amt, vat, quo_value, tax_value, net_value) 
				VALUES (:quo_id, :item_id, :quo_qty, :show_image, :quo_unit, :selling_price, :quo_discount, :quo_discount_amt, :vat, :quo_value, :tax_value, :net_value)");

			$row_count = count($_REQUEST['temp_item_id']);

			for ($n = 0; $n < $row_count; $n++) {
				$data = array(
					':quo_id' => $update_id,
					':item_id' => $_REQUEST['temp_item_id'][$n],
					':quo_qty' => $_REQUEST['temp_qty'][$n],
					':show_image' => $_REQUEST['show_image'][$n],
					':quo_unit' => $_REQUEST['temp_unit'][$n],
					':selling_price' => $_REQUEST['temp_selling_price'][$n],
					':quo_discount' => $_REQUEST['temp_discount_per'][$n],
					':quo_discount_amt' => $_REQUEST['temp_discount_val'][$n],
					':vat' => $_REQUEST['temp_vat'][$n],
					':quo_value' => $_REQUEST['temp_quo_price'][$n],
					':tax_value' => $_REQUEST['quo_pack_taxable_value'][$n],
					':net_value' => $_REQUEST['temp_net_amt'][$n]

				);
				$stmt->execute($data);
			}

			//----------Send Approval Package Data----------------//

			$sql =  "DELETE FROM tbl_quo_pack_details WHERE quo_id = '" . $update_id . "'";
			$result = $conn->prepare($sql);
			$result->execute();

			$stmt = null;
			$stmt = $conn->prepare("INSERT INTO tbl_quo_pack_details (quo_id, quo_pack_decp, quo_pack_percent, quo_pack_text, quo_pack_taxable_val, gst_id, quo_pack_vat, quo_pack_value, quo_pack_total)
									VALUES (:quo_id, :quo_pack_decp, :quo_pack_percent, :quo_pack_text, :quo_pack_taxable_val, :gst_id, :quo_pack_vat, :quo_pack_value, :quo_pack_total)");
			$row_count = ($_REQUEST['pack_id']) ? count($_REQUEST['pack_id']) : 0;
			// 			$row_count = count($_REQUEST['pack_id']);
			if ($row_count > 0) {
				for ($n = 0; $n < $row_count; $n++) {

					$data = array(
						':quo_id' => $update_id,
						':quo_pack_decp' => $_REQUEST['pack_id'][$n],
						':quo_pack_percent' => $_REQUEST['quo_pack_per_fa'][$n],
						':quo_pack_text' => $_REQUEST['quo_pack_per_fa_value'][$n],
						':quo_pack_taxable_val' => $_REQUEST['quo_pack_taxable_val'][$n],
						':gst_id' => $_REQUEST['quo_pack_gst_id'][$n],
						':quo_pack_vat' => $_REQUEST['quo_pack_gst_per'][$n],
						':quo_pack_value' => $_REQUEST['quo_pack_gst_amt'][$n],
						':quo_pack_total' => $_REQUEST['quo_pack_total'][$n]
					);
					$stmt->execute($data);
				}
			}
		} catch (Exception $e) {
			$str = filter_var($e->getMessage(), FILTER_SANITIZE_STRING);
			$_SESSION['_msg_err'] = $str;
		}
		$_SESSION['_msg'] = "Quotation succesfully Updated..!";
		header("location:lst_quotation.php");
		die();
	} else {
		try {
			$_REQUEST['branch'] = $dbconn->GetSingleReconrd("mst_branch", "branch_code", "branch_id='" . $_SESSION['_user_branch'] . "' AND branch_status", 1);
			$_REQUEST['quo_slno']  = leadingZeros($dbconn->GetMaxValue('tbl_quotation', 'quo_slno', 'quo_finyr', $_REQUEST['quo_finyr']) + 1, 3);
			$_REQUEST['quo_refno'] = leadingZeros($_REQUEST['quo_slno'], 3) . '/BIE/' . $_REQUEST['branch'] . '/' . $supp_code . '/Q/' . $_REQUEST['quo_finyr'];

			//$quo_approve_id = $dbconn->GetSingleReconrd("tbl_task_user", "user_id", "task_id", 1);
			$stmt = null;
			$stmt = $conn->prepare("INSERT INTO tbl_quotation (quo_finyr, quo_refno, quo_slno, quo_date, supp_id, ref_phone_no, ref_email, show_all_image, branch_id, quo_value, created_by, created_dtm,terms_con, terms_con_id1, terms_con_id2, terms_con_id3, terms_con_id4, terms_con_id5, terms_con_id6, terms_con_id7, terms_con_id8, quo_verify_status, quo_verify_by, quo_verify_date_time,bie_branch_id) 
							VALUES (:quo_finyr, :quo_refno, :quo_slno, :quo_date, :supp_id, :ref_phone_no, :ref_email, :show_all_image, :branch_id, :quo_value, :created_by, :created_dtm,:terms_con, :terms_con_id1, :terms_con_id2, :terms_con_id3, :terms_con_id4, :terms_con_id5, :terms_con_id6, :terms_con_id7, :terms_con_id8, :quo_verify_status, :quo_verify_by, :quo_verify_date_time,:bie_branch_id)");
			$data = array(
				':quo_finyr' => $_REQUEST['quo_finyr'],
				':quo_refno' => $_REQUEST['quo_refno'],
				':quo_slno' => $_REQUEST['quo_slno'],
				':quo_date' => $_REQUEST['quo_date'],
				':supp_id' => $_REQUEST['supp_id'],
				':ref_phone_no' => $_REQUEST['ref_phone_no'],
				':ref_email' => $_REQUEST['ref_email'],
				':show_all_image' => $_REQUEST['show_all_image'],
				':branch_id' => $_REQUEST['select_branch_id'],
				':quo_value' => $_REQUEST['txt_final_total'],
				':terms_con' => $_REQUEST['tc_group'],
				':terms_con_id1' => $_REQUEST['terms_con_id1'],
				':terms_con_id2' => $_REQUEST['terms_con_id2'],
				':terms_con_id3' => $_REQUEST['terms_con_id3'],
				':terms_con_id4' => $_REQUEST['terms_con_id4'],
				':terms_con_id5' => $_REQUEST['terms_con_id5'],
				':terms_con_id6' => $_REQUEST['terms_con_id6'],
				':terms_con_id7' => $_REQUEST['terms_con_id7'],
				':terms_con_id8' => $_REQUEST['terms_con_id8'],
				':created_by' =>  $_SESSION['_user_id'],
				':created_dtm' => $_REQUEST['created_dtm'],
				':quo_verify_status' => 1,
				':quo_verify_by' => $_SESSION['_user_id'],
				':quo_verify_date_time' => date('Y-m-d H:i:s'),
				':bie_branch_id' => $_SESSION['_user_branch']
			);
			$stmt->execute($data);
			$last_id = $conn->lastInsertId();


			//-------------------------Direct Approval Details------------------------//

			$stmt = null;
			$stmt = $conn->prepare("INSERT INTO tbl_quotation_details (quo_id,  item_id, quo_qty, show_image, quo_unit, selling_price, quo_discount, quo_discount_amt, vat, quo_value, tax_value, net_value) 
		                    VALUES (:quo_id, :item_id, :quo_qty, :show_image, :quo_unit, :selling_price, :quo_discount, :quo_discount_amt, :vat, :quo_value, :tax_value, :net_value)");

			$row_count = count($_REQUEST['temp_item_id']);
			for ($n = 0; $n < $row_count; $n++) {
				$data = array(
					':quo_id' => $last_id,
					':item_id' => $_REQUEST['temp_item_id'][$n],
					':quo_qty' => $_REQUEST['temp_qty'][$n],
					':show_image' => $_REQUEST['show_image'][$n],
					':quo_unit' => $_REQUEST['temp_unit'][$n],
					':selling_price' => $_REQUEST['temp_selling_price'][$n],
					':quo_discount' => $_REQUEST['temp_discount_per'][$n],
					':quo_discount_amt' => $_REQUEST['temp_discount_val'][$n],
					':vat' => $_REQUEST['temp_vat'][$n],
					':quo_value' => $_REQUEST['temp_quo_price'][$n],
					':tax_value' => $_REQUEST['quo_pack_taxable_value'][$n],
					':net_value' => $_REQUEST['temp_net_amt'][$n]

				);
				$stmt->execute($data);
			}

			//-----------------------Direct Approval Package Data----------------------//

			$quo_pack_total = 0;
			$stmt = null;
			$stmt = $conn->prepare("INSERT INTO tbl_quo_pack_details (quo_id, quo_pack_decp, quo_pack_percent, quo_pack_text, quo_pack_taxable_val, gst_id, quo_pack_vat, quo_pack_value, quo_pack_total)
		                    VALUES (:quo_id, :quo_pack_decp, :quo_pack_percent, :quo_pack_text, :quo_pack_taxable_val, :gst_id, :quo_pack_vat, :quo_pack_value, :quo_pack_total)");

			$row_count = ($_REQUEST['pack_id']) ? count($_REQUEST['pack_id']) : 0;
			if ($row_count > 0) {
				for ($n = 0; $n < $row_count; $n++) {
					$quo_pack_total = isset($_REQUEST['quo_pack_total'][$n]) ? $_REQUEST['quo_pack_total'][$n] : '';
					$data = array(
						':quo_id' => $last_id,
						':quo_pack_decp' => $_REQUEST['pack_id'][$n],
						':quo_pack_percent' => $_REQUEST['quo_pack_per_fa'][$n],
						':quo_pack_text' => $_REQUEST['quo_pack_per_fa_value'][$n],
						':quo_pack_taxable_val' => $_REQUEST['quo_pack_taxable_val'][$n],
						':gst_id' => $_REQUEST['quo_pack_gst_id'][$n],
						':quo_pack_vat' => $_REQUEST['quo_pack_gst_per'][$n],
						':quo_pack_value' => $_REQUEST['quo_pack_gst_amt'][$n],
						':quo_pack_total' => $quo_pack_total
					);
					$stmt->execute($data);
				}
			}
		} catch (Exception $e) {
			$str = filter_var($e->getMessage(), FILTER_SANITIZE_STRING);
			$_SESSION['_msg_err'] = $str;
		}
		$_SESSION['_msg'] = "Quotation succesfully Saved..!";
		header("location:lst_quotation.php");
		die();
	}
}

//---------------Edit Fetch data------------------//

$otdets_principal_id = '';
$ref_phone_no = $supp_id = $ref_email = $branch_id = $show_all_image = $terms_con_id1 = $terms_con_id2 = $terms_con_id3 = $terms_con_id4 = $terms_con_id6 = $terms_con_id7 = $terms_con_id8 = $quo_pack_total = '';
$tc_principal_id = '0';
$discount_apply = 0;
if (isset($_REQUEST['quo_id'])) {
	$get_val = $conn->query("SELECT * FROM tbl_quotation WHERE  quo_id =' " . $_REQUEST['quo_id'] . "'");

	if ($get_val->rowCount() > 0) {
		$obj = $get_val->fetch(PDO::FETCH_OBJ);
		$supp_id = $obj->supp_id;
		$ref_phone_no = $obj->ref_phone_no;
		$branch_id = $obj->branch_id;
		$ref_email = $obj->ref_email;
		$show_all_image = $obj->show_all_image;
		$terms_con = $obj->terms_con;
		$terms_con_id1 = $obj->terms_con_id1;
		$terms_con_id2 = $obj->terms_con_id2;
		$terms_con_id3 = $obj->terms_con_id3;
		$terms_con_id4 = $obj->terms_con_id4;
		$terms_con_id5 = $obj->terms_con_id5;
		$terms_con_id6 = $obj->terms_con_id6;
		$terms_con_id7 = $obj->terms_con_id7;
		$terms_con_id8 = $obj->terms_con_id8;
		$otdets_principal_id = $dbconn->GetSingleReconrd("mst_quo_details", "principal_id", "hsn_id", $temp_gst_id);
	}
} elseif (isset($_REQUEST['sal_repair_id'])) {
	$get_val = $conn->query("SELECT * FROM tbl_sales_repair WHERE sal_repair_status = '1' AND sal_repair_id = " . $_REQUEST['sal_repair_id']);
	if ($get_val->rowCount() > 0) {
		$get = $get_val->fetch(PDO::FETCH_OBJ);
		if ($get->sal_repair_date != "0000-00-00" && $get->sal_repair_date != "") {
			$enq_date = date("d-m-Y", strtotime($get->sal_repair_date));
		}

		$supp_id = $get->supp_id;

		$ref_phone_no = '';
		$ref_email = '';
		$buss_name = $dbconn->GetSingleReconrd("mst_supplier_new", "supp_name", "supp_id", $get->supp_id);
		$discount_apply = $dbconn->GetSingleReconrd("mst_supplier_new", "discount_apply", "supp_id", $supp_id);
	}
}
?>



<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta type="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title>
		<?php echo PAGE_TITLE; ?>-Quotation
	</title>
	<link rel="icon" href="favicon.ico" type="image/x-icon" />
	<?php include_once("inc/common/css-js.php"); ?>
</head>

<body>
	<!-- Main navbar -->
	<?php include("inc/common/header.php") ?>
	<!-- Page content -->
	<div class="page-content">
		<!-- Main sidebar -->
		<?php include("inc/common/sidebar.php") ?>
		<!-- Main content -->
		<div class="content-wrapper">
			<!-- Page header -->
			<div class="page-header">

				<div class="breadcrumb-line breadcrumb-line-light header-elements-md-inline">
					<div class="d-flex">
						<div class="breadcrumb">
							<a href="home.php" class="breadcrumb-item"><i class="icon-home2 mr-2"></i> Home</a>
							<a href="#" class="breadcrumb-item">Work Area</a>
							<span class="breadcrumb-item active">Quotation</span>
						</div>
						<a href="#" class="header-elements-toggle text-default d-md-none"><i class="icon-more"></i></a>
					</div>
				</div>
			</div>
			<!-- /page header -->

			<?php
			$quo_finyr = $dbconn->GetSingleReconrd("mst_finyear", "finyr", "finyr_active", 1);
			if ($_REQUEST['quo_id'] != "" && $_REQUEST['status'] == 'requote') {
				$quo_slno = $dbconn->GetSingleReconrd('tbl_quotation', 'quo_slno', 'quo_id', $_REQUEST['quo_id']);
				$quo_version = $dbconn->GetMaxValue('tbl_quotation', 'quo_version', 'quo_finyr="' . $quo_finyr . '" AND quo_slno', $quo_slno) + 1;
				$requote = $dbconn->requote($quo_version);
				$quo_no1 = leadingZeros($quo_slno, 3);
				$quo_no = $quo_no1 . ' - ' . $requote;
			} elseif ($_REQUEST['quo_id'] != "") {

				$quo_slno = $dbconn->GetSingleReconrd('tbl_quotation', 'quo_slno', 'quo_status=1 AND quo_id', $_REQUEST['quo_id']);

				$quo_version = $dbconn->GetMaxValue('tbl_quotation', 'quo_version', 'quo_finyr="' . $quo_finyr . '" AND quo_slno', $quo_slno);
				$requote = $dbconn->requote($quo_version);
				if ($quo_version != '0') {
					$requote = $dbconn->requote($quo_version);
					$quo_no1 = leadingZeros($quo_slno, 3);
					$quo_no = $quo_no1 . ' - ' . $requote;
				} else {
					$quo_no = leadingZeros($quo_slno, 3);
				}
			} else {
				$quo_no = leadingZeros($dbconn->GetMaxValue('tbl_quotation', 'quo_slno', 'quo_finyr', $_REQUEST['quo_finyr']) + 1, 3);
			}
			?>
			<!-- This Form UI Starts here --->
			<div class="content pt-0">
				<div class="row">
					<div class="col-md-12">
						<form name='thisForm' class="form-horizontal" method='post' action="quotation.php" onSubmit="return fnValidate();" enctype="multipart/form-data">

							<input type="hidden" name="quo_slno" id="quo_slno" value="<?php echo $quo_no; ?>">
							<input type="hidden" name="quo_sln" id="quo_sln" value="<?php echo $quo_no1; ?>">
							<input type="hidden" name="quo_version" id="quo_version" value="<?php echo $quo_version; ?>">
							<input type="hidden" name="quo_items" id="quo_items" value="-1">
							<input type="hidden" name="requote" id="requote" value="<?php echo $requote; ?>">
							<input type="hidden" name="discount_apply" id="discount_apply" value="<?php echo $discount_apply; ?>">

							<fieldset>
								<div class="card">
									<div class="card-header bg-pgheader text-white header-elements-inline">
										<?php if ($obj->quo_status == 5) { ?>
											<h6 class="card-title">Quotation - <span class="card-title" style="font-size: 14px; color: white;">Amendment</span></h6>
										<?php } else { ?>
											<h6 class="card-title"> Quotation</h6>
										<?php } ?>
										<div class="header-elements">
											<div class="list-icons">
												<a class="list-icons-item" href="lst_quotation.php" title="Quotation List"><i class="icon-arrow-left52 mr-2"></i></a>
												<a class="list-icons-item" data-action="fullscreen"></a>
											</div>
										</div>
									</div>
									<div class="card-body">

										<div class="form-group row">
											<label class="col-lg-2 col-form-label">Quotation No <span class="text-mandatory"></span></label>
											<div class="col-lg-4">
												<input type="text" class="form-control" readonly id="quo_no" name="quo_no" tabindex="-1" style="font-size: 16px; color: blue; font-weight: bold;" value="<?php echo $quo_no; ?>" />
											</div>
											<label class="col-lg-2 col-form-label">Quotation Date <span class="text-mandatory"> *</span></label>
											<div class="col-lg-4">
												<input type="date" name="quo_date" id="quo_date" class="form-control" maxlength="75" max="<?php echo date('Y-m-d'); ?>" value="<?php echo date('Y-m-d'); ?>" placeholder="Date" />
											</div>
										</div>

										<div class="form-group row">
											<label class="col-lg-2 col-form-label">Customer<span class="text-mandatory"> *</span></label>
											<div class="col-lg-4">
												<select name="supp_id" id="supp_id" data-placeholder="Choose a Customer.." class="select select-search">
													<option value="">-- Select Customer --</option>
													<?php
													$dbconn = new dbhandler();
													echo $dbconn->fnFillComboFromTable_Where("supp_id", "CONCAT(supp_name,' - ',supp_add2)", "mst_supplier_new", "supp_id", " WHERE supp_status = '1' AND supp_type = 'C'");
													?>
												</select>
												<script>
													document.thisForm.supp_id.value = "<?php echo $supp_id; ?>";
												</script>
											</div>

											<label class="col-lg-2 col-form-label">Reference </label>
											<div class="col-lg-4">
												<input type="text" class="form-control" onKeyPress="return isNumberKey(event)" id="ref_phone_no" name="ref_phone_no" readonly maxlength="15" tabindex="-1" value="<?php echo $ref_phone_no; ?>" />
											</div>
										</div>

										<div class="form-group row">
											<label class="col-lg-2 col-form-label">Branch<span class="text-mandatory"></span></label>
											<div class="col-lg-4">
												<select name="select_branch_id" id="select_branch_id" data-placeholder="Choose a Branch.." class="select select-search">
													<option value="0">-- Select Branch --</option>
													<?php
													$dbconn = new dbhandler();
													echo $dbconn->fnFillComboFromTable_Where("branch_id", "branch_name", "mst_customer_branch", "branch_id ", " WHERE branch_status ='1'");
													?>
												</select>
												<script>
													document.thisForm.select_branch_id.value = "<?php echo $branch_id; ?>";
												</script>
											</div>
											<label class="col-lg-2 col-form-label">Reference Email</label>
											<div class="col-lg-4">
												<input type="text" class="form-control" id="ref_email" readonly name="ref_email" tabindex="-1" value="<?php echo $ref_email; ?>" />
											</div>
										</div>

										<div class="form-group row">
											<label class="col-lg-2 col-form-label">Show Image For All Items</label>
											<div class="col-lg-4">
												<select name="show_all_image" id="show_all_image" data-placeholder="Choose a Type.." class="select">
													<option value="">---Select---</option>
													<option value="1">Yes</option>
													<option value="2">No</option>
												</select>
												<script>
													document.thisForm.show_all_image.value = "<?php echo $show_all_image; ?>";
												</script>
											</div>
										</div>
										<legend class="font-weight-semibold"></legend>
										<div class="form-group row">
											<label class="col-lg-2 col-form-label "><i class="icon-address-book  mr-2"></i>Other Details</label>
											<div class="col-lg-4">
												<select name="otdets_group" id="otdets_group" class="select">
													<option value="">Customise</option>
													<?php
													echo $dbconn->fnFillComboFromTable_Where(
														"principal_id",
														"principal_name",
														"mst_principal",
														"principal_id",
														"WHERE principal_status= 1"
													);
													?>
												</select>
												<script>
													document.thisForm.otdets_group.value = "<?php echo $otdets_principal_id; ?>";
												</script>
											</div>
										</div>
										<!-- <legend class="font-weight-semibold"></legend> -->
										<legend class="font-weight-semibold "><i class="icon-address-book  mr-2"></i>Product Order Details</legend>
										<div class="form-group row">
											<div class="form-group col-md-3" id="select_group">
												<p><b>Type <span class="text-mandatory">*</span></b></p>
												<div>
													<select name="group_type" id="group_type" data-placeholder="Choose a Type.." class="select">
														<option value="">Select Group Type</option>
														<option value="I">Individual</option>
														<option value="G">Group</option>
													</select>
												</div>
											</div>

											<div class="form-group col-md-4" id="quo_trading_group">
												<p><b>Group Name <span class="text-mandatory">*</span></b></p>
												<div>
													<select data-placeholder="Choose a Group Name.." name="group_id" id="group_id" class="select-search">
														<option value="">--Select Group Name----</option>
														<?php
														echo $dbconn->fnFillComboFromTable_Where("item_group_id", "concat(item_group_code,'-',item_group_name)", "tbl_item_group", "item_group_code", " WHERE status = 1");
														?>
													</select>
												</div>
											</div>
											<div class="form-group pl-2 col-md-1" id="quo_items_set">
												<p><b>Set <span class="text-mandatory">*</span></b></p>
												<input type="text" class="form-control" placeholder="Set" maxlength="3" onkeypress="return isNumberKey_With_Dot(event)" name="quo_set" id="quo_set" />
											</div>
											<div class="form-group pl-2 col-md-1" id="quo_group_add_btn">
												<button class="btn btn-success mr-2 mt-4 pt-1" id="item_group_add" name="item_group_add" type="button"> +
												</button>
											</div>
											<div class="form-group col-md-4" id="quo_desc">
												<p><b>Item Description <span class="text-mandatory">*</span></b></p>
												<div>
													<input type="text" class="form-control" placeholder="Item Description" name="item_code" id="item_code" autocomplete="" />
													<input type="hidden" readonly name="item_id" id="item_id" value="">
												</div>
											</div>
											<input type="hidden" readonly name="quo_pack_taxable_value" id="quo_pack_taxable_value" value="">
											<div class="form-group pl-0 col-md-1" id="quo_desc_qty">
												<p><b>Qty <span class="text-mandatory">*</span></b></p>
												<input type="text" class="form-control" name="quo_qty" id="quo_qty" maxlength="9" onkeypress="return isNumberKey_With_Dot(event)" value="" />
											</div>
											<div class="form-group pl-0 col-md-1" id="quo_desc_unit">
												<p><b>Unit</b></p>
												<div class="input-append">
													<input type="text" class="form-control" name="quo_unit" id="quo_unit" maxlength="7" tabIndex="-1" readonly value="" />
												</div>
											</div>
											<div class="form-group pl-0 col-md-1" id="quo_desc_unit_price">
												<p><b>Unit Price</b></p>
												<div class="input-append">
													<input type="text" class="form-control" name="quo_selling_price" id="quo_selling_price" maxlength="7" onkeypress="return isNumberKey_With_Dot(event)" value="" />
												</div>
											</div>
											<div class="form-group pl-0 col-md-1" id="quo_desc_discount">
												<p><b>Discount(%)</b></p>
												<input type="text" class="form-control" name="quo_discount" id="quo_discount" maxlength="9" onkeypress="return isNumberKey_With_Dot(event)" value="" />
												<input type="hidden" readonly name="quo_discount1" id="quo_discount1" value="">
												<input type="hidden" readonly name="quo_min_discount1" id="quo_min_discount1" value="">
												<input type="hidden" readonly name="quo_max_discount1" id="quo_max_discount1" value="">
											</div>
											<div class="form-group pl-2 col-md-2" id="quo_desc_amt">
												<p><b>Amount</b></p>
												<div class="input-append">
													<input type="text" class="form-control text-right" name="quo_price" id="quo_price" maxlength="7" readonly onkeypress="return isNumberKey_With_Dot(event)" value="" />
												</div>
											</div>
											<div class="form-group pl-2 col-md-1" id="quo_desc_gst">
												<p><b>GST</b></p>
												<div class="input-append">
													<input type="text" class="form-control" name="quo_vat" id="quo_vat" maxlength="7" readonly onkeypress="return isNumberKey_With_Dot(event)" value="" />
												</div>
											</div>
											<div class="form-group pl-0 col-md-2" id="quo_desc_net_amt">
												<p><b>Net Amount</b></p>
												<div class="input-append">
													<input type="text" class="form-control text-right" readonly name="quo_net_amt" id="quo_net_amt" tabIndex="-1" onkeypress="return isNumberKey_With_Dot(event)" value="" />
												</div>
											</div>
											<div class="col-lg-2 col-form-label" id="quo_desc_image">
												<p><b>Show Image for Print <span class="text-mandatory">*</span></b></p>
												<p>
													<span>Yes <input type="radio" class="show_image" name="show_image" value="Y" checked /></span>
													<span>No <input type="radio" class="show_image" name="show_image" value="N" />
													</span>
												</p>
											</div>
											<div class="form-group pl-0" id="item_indv_add_btn">
												<button class="btn btn-success mr-2 mt-4 pt-1" id="add_items" name="add_items" type="button"> +</button>
											</div>
										</div>

										<div class="form-group row">
											<div id="quo_table" class="col-md-12">
												<table class="table table-xs table-bordered" style="font-size: small !important;">
													<thead>
														<tr class="bg-teal">
															<th>Description</th>
															<th>Item Code</th>
															<th>Qty</th>
															<th>Unit</th>
															<th>Unit Price</th>
															<th>Disc %</th>
															<th>Amount</th>
															<th>GST %</th>
															<th>Net Amount</th>
															<th><i class=" icon-cog6 mr-2"></i></th>
														</tr>
													</thead>
													<tbody>
														<?php
														if (isset($_REQUEST['quo_id'])) {
															$get_quo_dets =  $conn->query("SELECT * FROM tbl_quotation_details WHERE  quo_id = '" . $_REQUEST['quo_id'] . "'");
															if ($get_quo_dets->rowCount() > 0) {
																while ($obj = $get_quo_dets->fetch(PDO::FETCH_OBJ)) {

																	echo '<tr id="' . $obj->item_id . '" class="g' . $obj->group_id . '">
																	<td>' . $dbconn->GetSingleReconrd("tbl_item_details", "item_desciption", "item_id", $obj->item_id) . '</td>
																	<input type="hidden" class="item_desciption" name="item_desciption[]" value="' . $obj->item_id . '" />

																	<td>' . $dbconn->GetSingleReconrd("tbl_item_details", "item_code", "item_id", $obj->item_id) . '</td>
																	<input type="hidden" class="temp_item_id" name="temp_item_id[]" value="' . $obj->item_id . '" />

																	<td class="text-right">' . $obj->quo_qty . '</td>
																	<input type="hidden" class="temp_qty" name="temp_qty[]" value="' . $obj->quo_qty . '" />

																	<td class="text-right">' . $obj->quo_unit . '</td>
																	<input type="hidden" class="temp_unit" name="temp_unit[]" value="' . $obj->quo_unit . '" />

																	<td class="text-right">' . $obj->selling_price . '</td>
																	<input type="hidden" class="temp_selling_price" name="temp_selling_price[]" value="' . $obj->selling_price . '" />

																	<td class="text-right">' . $obj->quo_discount . '</td>
																	<input type="hidden" class="temp_discount_per" name="temp_discount_per[]" value="' . $obj->quo_discount . '" />
																
																	<td class="text-right">' . $obj->quo_value . '</td>
																	<input type="hidden" class="temp_quo_price" name="temp_quo_price[]" value="' . $obj->quo_value . '" />
																	<input type="hidden" class="temp_discount_val" name="temp_discount_val[]" value="' .  $obj->quo_discount_amt . '">

																	<td class="text-right">' . $obj->vat . '</td>
																	<input type="hidden" class="temp_vat" name="temp_vat[]" value="' . $obj->vat . '" />
																	<input type="hidden" class="quo_pack_taxable_value" name="quo_pack_taxable_value[]" value="' . $tax_val . '"/>

																	<td class="text-right">' . $obj->net_value . '</td>
																	<input type="hidden" class="temp_net_amt" name="temp_net_amt[]"  id="net_total" value="' . $obj->net_value . '" />

																	<td class="text-center">
																		<a href="javascript:remove_item(' . $obj->item_id . ');" class="" rel="' . $obj->item_id . '"  title="Remove"><i class="icon-bin bg-delete mr-2"></i></a>
																	</td>
																</tr>';
																}
															}
															$tot_qty = $dbconn->GetSingleReconrd("tbl_quotation_details", "SUM(quo_qty)", "quo_id", $_REQUEST['quo_id']);
															$tot_amt = $dbconn->GetSingleReconrd("tbl_quotation_details", "SUM(quo_value)", "quo_id", $_REQUEST['quo_id']);
															$tot_netamt = $dbconn->GetSingleReconrd("tbl_quotation_details", "SUM(net_value)", "quo_id", $_REQUEST['quo_id']);
														} else if (isset($_REQUEST['sal_repair_id'])) {
															$tot_qty = 0;
															$tot_amt = 0;
															$tot_netamt = 0;
															$get_quo_pack_dets =  $conn->query("SELECT * FROM tbl_sales_repair_details WHERE sal_repair_id = '" . $_REQUEST['sal_repair_id'] . "'");

															if ($get_quo_pack_dets->rowCount() > 0) {
																while ($obj = $get_quo_pack_dets->fetch(PDO::FETCH_OBJ)) {
																	$repair_item_name = $dbconn->GetSingleReconrd(
																		"tbl_item_details",
																		"item_desciption",
																		"item_status = '1' AND item_id",
																		$obj->repair_item_id
																	);

																	$spare_item_name = $dbconn->GetSingleReconrd(
																		"tbl_item_details",
																		"item_code",
																		"item_status = '1' AND item_id",
																		$obj->repair_item_id
																	);

																	$row  = '<tr id="RI_' . $obj->repair_item_id . '">';
																	$row .= '<td>' . $repair_item_name . '
																			<input type="hidden" class="item_desciption" name="item_desciption[]" value="' . $obj->repair_item_id . '" />
																		</td>';
																	$row .= '<td>' . $spare_item_name . '
																			<input type="hidden" class="temp_item_id" name="temp_item_id[]" value="' . $obj->repair_item_id . '" />
																		</td>';
																	$row .= '<td class="text-right">' . $obj->repair_qty . '
																			<input type="hidden" class="temp_qty" name="temp_qty[]" value="' . $obj->repair_qty . '" />
																		</td>';
																	$row .= '<td class="text-center">' . $obj->repair_unit . '
																			<input type="hidden" class="temp_unit" name="temp_unit[]" value="' . $obj->repair_unit . '" />
																		</td>';
																	$row .= '<td class="text-right">' . number_format($obj->repair_selling_price, 2, '.', '') . '
																		<input type="hidden" class="temp_selling_price" name="temp_selling_price[]" value="' . $obj->repair_selling_price . '" />
																		</td>';
																	$row .= '<td class="text-center">
																			<input type="hidden" class="temp_discount_per" name="temp_discount_per[]" value="' . $obj->quo_discount . '" />
																		</td>';
																	$row .= '<td class="text-right">' . number_format($obj->repair_value, 2, '.', '') . '
																			<input type="hidden" class="temp_quo_price" name="temp_quo_price[]" value="' . $obj->repair_value . '" />
																			<input type="hidden" class="temp_discount_val" name="temp_discount_val[]" value="0">
																		</td>';
																	$row .= '<td class="text-right">' . $obj->repair_tax . ' %
																			<input type="hidden" class="temp_vat" name="temp_vat[]" value="' . $obj->repair_tax . '" />
																			<input type="hidden" class="quo_pack_taxable_value" name="quo_pack_taxable_value[]" value="' . $obj->repair_tax_val . '" />
																		</td>';
																	$row .= '<td class="text-right">' . number_format($obj->repair_net_val, 2, '.', '') . '
																			<input type="hidden" class="temp_net_amt" name="temp_net_amt[]" value="' . $obj->repair_net_val . '" />
																		</td>';

																	$row .= '<td class="text-center">
																		<a href="javascript:remove_item_RI(\'' . $obj->repair_item_id . '\');" title="Remove">
																			<i class="icon-bin bg-delete mr-2"></i>
																		</a>
																	</td>';
																	$row .= '</tr>';

																	echo $row;

																	$tot_qty    += $obj->repair_qty;
																	$tot_amt    += $obj->repair_value;
																	$tot_netamt += $obj->repair_net_val;
																}
															}
														}
														?>
													</tbody>
													<tfoot>
														<tr>
															<th colspan="2" class="text-right">Total </th>
															<th id="quo_total_qty" class="text-right"><?php echo $tot_qty; ?></th>
															<th></th>
															<th></th>
															<th></th>
															<th id="quo_total_amt" class="text-right"><?php echo $tot_amt; ?></th>
															<th></th>
															<th id="quo_total_netamt" name="quo_total_netamt" class="text-right"><?php echo $tot_netamt; ?></th>
															<th class="text-right"></th>
															<input type="hidden" id="txt_quo_total_amt" value="<?php echo $tot_amt; ?>">
															<input type="hidden" id="txt_quo_total_netamt" name="txt_quo_total_netamt" value="<?php echo $tot_netamt; ?>">
														</tr>
													</tfoot>
												</table>
											</div>
										</div>

										<div class="row pt-1">
											<div class="col-md-12">
												<fieldset>
													<div class="form-group row">
														<div class="form-group col-md-3">
															<p><b>Description</b></p>
															<div>
																<select name="pack_id" id="pack_id" class="select">
																	<option value="">-- Select --</option>
																	<?php
																	if ($otdets_principal_id > 0) {
																		echo $dbconn->fnFillComboFromTable_Where("quo_id", "quo_pack_decp", "mst_quo_details", "quo_id", " WHERE quo_status = '1' ANd principal_id=" . $otdets_principal_id);
																	} else {
																		echo $dbconn->fnFillComboFromTable_Where("quo_id", "quo_pack_decp", "mst_quo_details", "quo_id", " WHERE quo_status = '1'");
																	}
																	?>
																</select>
															</div>
														</div>
														<div class="form-group col-md-2">
															<p><b>% / Fixed Amount</b></p>
															<select name="quo_pack_per_fa" id="quo_pack_per_fa" data-placeholder="Choose .." class="select">
																<option value="">Select</option>
																<option value="1">% Percent</option>
																<option value="2">Fixed Amount</option>
															</select>
														</div>
														<div class="form-group pl-0 col-md-1">
															<p id='quo_per'><b> % Percent</b></p>
															<p id='quo_val'><b>Fixed Amt</b></p>
															<input type="text" class="form-control" name="quo_pack_per_fa_value" id="quo_pack_per_fa_value" onkeypress="return isNumberKey_With_Dot(event)" maxlength="9" value="" />
														</div>
														<div class="form-group pl-2 col-md-2">
															<p><b>HSN</b></p>
															<select name="quo_pack_gst_id" id="quo_pack_gst_id" data-placeholder="Choose a HSN.." class="select">
																<option value="">Select HSN</option>
																<?php
																$dbconn = new dbhandler();
																echo $dbconn->fnFillComboFromTable_Where("hsn_id", "CONCAT(hsn_code,' - ',igst,'%')", "mst_hsn", "hsn_id", " WHERE hsn_status = '1'") ?>
															</select>
														</div>
														<div class="form-group pl-0 col-md-1">
															<p><b>GST</b></p>
															<div class="input-append">
																<input type="text" class="form-control" name="quo_pack_gst_per" id="quo_pack_gst_per" maxlength="7" tabIndex="-1" readonly value="" />
																<input type="hidden" name="quo_pack_taxable_val" id="quo_pack_taxable_val" value="">
															</div>
														</div>
														<div class="form-group pl-1 col-md-2">
															<p><b>GST Amount</b></p>
															<div class="input-append">
																<input type="text" class="form-control" name="quo_pack_gst_amt" id="quo_pack_gst_amt" maxlength="7" tabIndex="-1" readonly value="" />
															</div>
														</div>
														<div class="form-group pl-0" id="item_indv_add_btn">
															<button class="btn btn-success mr-2 mt-4 pt-1" id="add_pack" name="add_pack" type="button"> +</button>
														</div>
													</div>
												</fieldset>
											</div>
										</div>

										<div class="form-group row">
											<div id="package_table" class="col-md-12">
												<table class="table table-xs table-bordered" style="font-size: small !important;">
													<thead>
														<tr class="bg-teal">
															<th>Description</th>
															<th>%/Fixed Amt</th>
															<th>Taxable Value</th>
															<th>HSN</th>
															<th>GST %</th>
															<th>GST Amount</th>
															<th>Total Value</th>
															<th><i class=" icon-cog6 mr-2"></i></th>
														</tr>
													</thead>
													<tbody>
														<?php
														if (isset($_REQUEST['quo_id'])) {
															$get_quo_pack_dets =  $conn->query("SELECT * FROM tbl_quo_pack_details WHERE  quo_id = '" . $_REQUEST['quo_id'] . "'");

															if ($get_quo_pack_dets->rowCount() > 0) {
																while ($obj = $get_quo_pack_dets->fetch(PDO::FETCH_OBJ)) {

																	$percent = $obj->quo_pack_percent;
																	if ($percent == 1) {
																		$percent_val =   $obj->quo_pack_text . " %";
																	} else {
																		$percent_val = number_format($obj->quo_pack_text, 0) . " - FA";
																	}
																	echo '<tr id="PK_' . $obj->pack_quo_details_id . '"  class="p' . $obj->quo_id . '">																
																		<td>' . $dbconn->GetSingleReconrd("mst_quo_details", "quo_pack_decp", "quo_id",  $obj->quo_pack_decp) . '
																			<input type="hidden" class="pack_id" name="pack_id[]" value="'  . $obj->quo_pack_decp . '" />
																			<input type="hidden" class="quo_id" name="quo_id[]" value="'  . $obj->quo_id . '" /></td>
																		<td>' . $percent_val . '
																			<input type="hidden" class="quo_pack_per_fa_value" name="quo_pack_per_fa_value[]" value="' . $percent_val . '" />
																			<input type="hidden" class="quo_pack_taxable_val" name="quo_pack_taxable_val[]" value="' . $obj->quo_pack_taxable_val . '" />
																			<input type="hidden" class="quo_pack_gst_amt" name="quo_pack_gst_amt[]" value="' . $obj->quo_pack_value . '" >
																		</td>
																		<td class="text-right disp_tax_val">' . number_format($obj->quo_pack_taxable_val, 2)  . '</td>
																		<td class="text-right">' . $obj->gst_id . '
																			<input type="hidden" class="quo_pack_gst_id" name="quo_pack_gst_id[]" value="' . $obj->gst_id . '" >
																			<input type="hidden" class="quo_pack_per_fa" name="quo_pack_per_fa[]" value="' . $percent . '" ></td>
																		<td class="text-right">' . $obj->quo_pack_vat . '	
																			<input type="hidden" class="quo_pack_gst_per" name="quo_pack_gst_per[]" value="' . $obj->quo_pack_vat . '" />
																			<input type="hidden" class="quo_pack_total" name="quo_pack_total[]" id="quo_pack_total" value="' . $obj->quo_pack_total . '" />
																		</td>
																		<td class="text-right disp_gst_amt">' . number_format($obj->quo_pack_value, 2) . '</td>
																		<td class="text-right disp_pack_total">' . number_format($obj->quo_pack_total, 2) . '</td>
																		<td class="text-center">
																			<a href="javascript:remove_item1(' . $obj->pack_quo_details_id . ');" class="" rel="' . $obj->pack_quo_details_id . '"  title="Remove"><i class="icon-bin bg-delete mr-2"></i></a>
																		</td>
																	</tr>';
																}
															}
														}
														$pack_total = $dbconn->GetSingleReconrd("tbl_quo_pack_details", "SUM(quo_pack_total)", "quo_id", $_REQUEST['quo_id']);
														?>
													</tbody>
													<tfoot>
														<th colspan="2" class="text-right">Total</th>
														<th></th>
														<th></th>
														<th></th>
														<th></th>
														<th id="quo_total_pack_netamt" class="text-right"><?php echo $pack_total; ?></th>
														<th></th>
													</tfoot>
												</table>
											</div>
										</div>
										<legend class="font-weight-semibold"></legend>
										<div class="form-group row">
											<div class="form-group col-md-12">
												<?php
												$PP = $dbconn->GetSingleReconrd("tbl_quotation", "quo_value", "quo_id", $_REQUEST['quo_id']);
												?>
												<table class="table table-bordered">
													<thead>
														<tr>
															<td colspan="4" text-align="right" style="font-size: small !important;"><b>Grand Total</b></td>
															<td width="10%" text-align="right" id="gran_total"><b>
																	<h5><span class="final_total"><?php echo $PP; ?></span></h5>
																</b>
															</td>
															<input type="hidden" id="txt_final_total" name="txt_final_total" value="<?php echo $PP; ?>">
														</tr>
													</thead>
												</table>
											</div>
										</div>
										<legend class="font-weight-semibold"></legend>

										<div class="form-group row">
											<label class="font-weight-semibold col-lg-2 col-form-label "><i class="icon-address-book  mr-2"></i>Terms and Condition</label>
											<div class="col-lg-4">
												<select name="tc_group" id="tc_group" class="select">
													<option value="">-----Select-----</option>
													<?php
													echo $dbconn->fnFillComboFromTable_Where(
														"principal_id",
														"principal_name",
														"mst_principal",
														"principal_id",
														"WHERE principal_status= 1"
													); ?>
												</select>
												<script>
													document.thisForm.tc_group.value = "<?php echo $terms_con; ?>";
												</script>
											</div>
										</div>
										<legend class="font-weight-semibold"></legend>
										<div class="form-group row">
											<label class="col-lg-2 col-form-label">Payment</label>
											<div class="col-lg-4">
												<select name="terms_con_id1" id="terms_con_id1" class="select select-search">
													<option value="">--Select--</option>
													<?php
													echo $dbconn->fnFillComboFromTable_Where("a.terms_con_id", "CONCAT(a.terms_con_title,'-',b.principal_name)", "mst_terms_condition as a,mst_principal as b", "a.terms_con_id", " WHERE a.principal_id = b.principal_id AND a.terms_con_status = '1' AND a.terms_con_category =1"); ?>
												</select>
												<script>
													document.thisForm.terms_con_id1.value = "<?php echo $terms_con_id1; ?>";
												</script>
											</div>
											<label class="col-lg-2 col-form-label">Customer Scope</label>
											<div class="col-lg-4">
												<select name="terms_con_id2" id="terms_con_id2" class="select select-search">
													<option value="">--Select--</option>
													<?php
													echo $dbconn->fnFillComboFromTable_Where("a.terms_con_id", "CONCAT(a.terms_con_title,'-',b.principal_name)", "mst_terms_condition as a,mst_principal as b", "a.terms_con_id", " WHERE a.principal_id = b.principal_id AND a.terms_con_status = '1' AND a.terms_con_category =2"); ?>
												</select>
												<script>
													document.thisForm.terms_con_id2.value = "<?php echo $terms_con_id2; ?>";
												</script>
											</div>
										</div>
										<br>
										<div class="control-group row">
											<label class="col-lg-2 col-form-label">Delivery Terms</label>
											<div class="col-lg-4">
												<select name="terms_con_id4" id="terms_con_id4" class="select select-search">
													<option value="">--Select--</option>
													<?php
													echo $dbconn->fnFillComboFromTable_Where("a.terms_con_id", "CONCAT(a.terms_con_title,'-',b.principal_name)", "mst_terms_condition as a,mst_principal as b", "a.terms_con_id", " WHERE a.principal_id = b.principal_id AND a.terms_con_status = '1' AND a.terms_con_category =4"); ?>
												</select>
												<script>
													document.thisForm.terms_con_id4.value = "<?php echo $terms_con_id4; ?>";
												</script>
											</div>
											<label class="col-lg-2 col-form-label">Delivery Period</label>
											<div class="col-lg-4">
												<select name="terms_con_id3" id="terms_con_id3" class="select select-search">
													<option value="">--Select--</option>
													<?php
													echo $dbconn->fnFillComboFromTable_Where("a.terms_con_id", "CONCAT(a.terms_con_title,'-',b.principal_name)", "mst_terms_condition as a,mst_principal as b", "a.terms_con_id", " WHERE a.principal_id = b.principal_id AND a.terms_con_status = '1' AND a.terms_con_category =3"); ?>
												</select>
												<script>
													document.thisForm.terms_con_id3.value = "<?php echo $terms_con_id3; ?>";
												</script>
												</select>
											</div>
										</div>
										<br>
										<div class="control-group row">
											<label class="col-lg-2 col-form-label">Transportation</label>
											<div class="col-lg-4">
												<select name="terms_con_id8" id="terms_con_id8" class="select select-search">
													<option value="">--Select--</option>
													<?php
													echo $dbconn->fnFillComboFromTable_Where("a.terms_con_id", "CONCAT(a.terms_con_title,'-',b.principal_name)", "mst_terms_condition as a,mst_principal as b", "a.terms_con_id", " WHERE a.principal_id = b.principal_id AND a.terms_con_status = '1' AND a.terms_con_category =8"); ?>
												</select>
												<script>
													document.thisForm.terms_con_id8.value = "<?php echo $terms_con_id8; ?>";
												</script>
											</div>
											<label class="col-lg-2 col-form-label">Installation</label>
											<div class="col-lg-4">
												<select name="terms_con_id5" id="terms_con_id5" class="select select-search">
													<option value="">--Select--</option>
													<?php
													echo $dbconn->fnFillComboFromTable_Where("a.terms_con_id", "CONCAT(a.terms_con_title,'-',b.principal_name)", "mst_terms_condition as a,mst_principal as b", "a.terms_con_id", " WHERE a.principal_id = b.principal_id AND a.terms_con_status = '1' AND a.terms_con_category =5"); ?>
												</select>
												<script>
													document.thisForm.terms_con_id5.value = "<?php echo $terms_con_id5; ?>";
												</script>
											</div>
										</div>
										<br>
										<div class="control-group row">
											<label class="col-lg-2 col-form-label">Validity</label>
											<div class="col-lg-4">
												<select name="terms_con_id7" id="terms_con_id7" class="select select-search">
													<option value="">--Select--</option>
													<?php
													echo $dbconn->fnFillComboFromTable_Where("a.terms_con_id", "CONCAT(a.terms_con_title,'-',b.principal_name)", "mst_terms_condition as a,mst_principal as b", "a.terms_con_id", " WHERE a.principal_id = b.principal_id AND a.terms_con_status = '1' AND a.terms_con_category =7"); ?>
												</select>
												<script>
													document.thisForm.terms_con_id7.value = "<?php echo $terms_con_id7; ?>";
												</script>
											</div>
											<label class="col-lg-2 col-form-label">Warranty</label>
											<div class="col-lg-4">
												<select name="terms_con_id6" id="terms_con_id6" class="select select-search">
													<option value="">--Select--</option>
													<?php
													echo $dbconn->fnFillComboFromTable_Where("a.terms_con_id", "CONCAT(a.terms_con_title,'-',b.principal_name)", "mst_terms_condition as a,mst_principal as b", "a.terms_con_id", " WHERE a.principal_id = b.principal_id AND a.terms_con_status = '1' AND a.terms_con_category =6"); ?>
												</select>
												<script>
													document.thisForm.terms_con_id6.value = "<?php echo $terms_con_id6; ?>";
												</script>
											</div>
										</div>
										<br>
									</div>
						</form>
						<div class="card-footer text-center pt-2">

							<?php if (isset($_REQUEST["quo_id"]) && $_REQUEST['status'] == 'requote') { ?>
								<INPUT class="btn btn-custom mr-2" type="submit" name="SAVE" value="Save">
								<INPUT class="btn btn-warning mr-2" type="submit" name="FINALIZE" value="Send for Approval" onclick="return fnValidate();">
								<INPUT class="btn btn-light" type="button" name="cancel" value="Cancel" onClick="javascript:history.go(-1);">
								<input type="hidden" name="txtHid" id="txtHid" value="<?php echo $_REQUEST['quo_id']; ?>">
								<input type="hidden" name="prev_quo_id" id="prev_quo_id" value="<?php echo $_REQUEST['quo_id']; ?>">
								<input type="hidden" name="status" value="<?php echo $_REQUEST['status']; ?>">
							<?php } elseif ($_REQUEST['quo_id'] != "" && $quo_rej == 4) { ?>
								<INPUT class="btn btn-custom mr-2" type="submit" name="UPDATE" value="Update">
								<INPUT class="btn btn-warning mr-2" type="submit" name="FINALIZE" value="Send for Approval" onclick="return fnValidate();">
								<INPUT class="btn btn-light" type="button" name="cancel" value="Cancel" onClick="javascript:history.go(-1);">
								<input type="hidden" name="txtHid" id="txtHid" value="<?php echo $_REQUEST['quo_id']; ?>">
							<?php } elseif (isset($_REQUEST["quo_id"])) { ?>
								<INPUT class="btn btn-custom mr-2" type="submit" name="UPDATE" value="Update">
								<INPUT class="btn btn-warning mr-2" type="submit" name="FINALIZE" value="Send for Approval" onclick="return fnValidate();">
								<INPUT class="btn btn-light" type="button" name="cancel" value="Cancel" onClick="javascript:history.go(-1);">
								<input type="hidden" name="txtHid" id="txtHid" value="<?php echo $_REQUEST['quo_id']; ?>">

							<?php } else { ?>
								<INPUT class="btn btn-custom mr-2" type="submit" name="SAVE" value="Save">
								<INPUT class="btn btn-warning mr-2" type="submit" name="FINALIZE" value="Send for Approval" onclick="return fnValidate();">
								<INPUT class="btn btn-light" type="button" name="cancel" value="Cancel" onClick="javascript:history.go(-1);">
								<input type="hidden" name="txtHid" value="0">
							<?php } ?>

							<!-- End of This Form UI  --->
						</div>
					</div>
				</div>
			</div>

		</div>
</body>
<!-- Footer -->
<?php include("inc/common/footer.php") ?>
<!-- /footer -->
<!---------script-------->

<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.js"></script>
<script language="javascript">
	function fnValidate() {

		if (isNull(document.thisForm.supp_id, "Customer...!")) {
			return false;
		}

		if (document.thisForm.ref_phone_no.value != '') {
			if ((document.thisForm.ref_phone_no.value.length) < 10) {
				alert("Please Enter 10 Digit Number");
				return false;
			}
		}
		if (document.thisForm.ref_email.value != '') {
			if (notEmail(document.thisForm.ref_email, "E-mail...!")) {
				return false;
			}
		}

		var rowCount = $('#quo_table tr').length;
		if (rowCount == 2) {
			alert("Please add Product Order Details");
			return false;
		}

		if (isNull(document.thisForm.tc_group, "Terms and Condition...!")) {
			return false;
		}
	}
</script>

<script type="text/javascript">
	$(function() {

		$("#group_type").val("I").change();

		$('.select-search').select2({
			placeholder: 'Select an Option',
			allowClear: true
		});

		// $("#tc_group").val("").change();

		$('.select-search').select2({
			placeholder: 'Select an Option',
			allowClear: true
		});
		$('#tc_group').select2({
			placeholder: 'Select an Option',
			allowClear: true
		});

		$("#supp_id").on('change', function() {
			var supp_id = $(this).val();
			$.ajax({
				type: "POST",
				url: "inc/cis_ajax/jquery_get_supp_dets.php",
				data: {
					"supp_id": supp_id
				}
			}).done(function(msg) {
				var data = msg.split('~');
				$('#ref_email').val(data[0]);
				$('#ref_phone_no').val(data[1]);
			});


			/*if (supp_id > 0) {

				$.ajax({
					type: "POST",
					url: "inc/cis_ajax/jquery_select_branch.php",
					async: false,
					data: {
						supp_id: supp_id

					}
				}).done(function(msg) {

					$('#select_branch_id option').remove();
					var dataArr = msg.split('#');

					$.each(dataArr, function(i, element) {
						if (dataArr[i] != "") {
							var dataArr2 = dataArr[i].split('~');

							$('#select_branch_id').append("<option value='" + dataArr2[0] + "'>" + dataArr2[1] + "</option>");
						}
					});

				});
			}
			$("#select_branch_id").val('<?php echo $branch_id; ?>').change();*/

			if (supp_id > 0) {

				$.ajax({
					type: "POST",
					url: "inc/cis_ajax/jquery_select_branch.php",
					async: false,
					data: {
						supp_id: supp_id
					}
				}).done(function(msg) {
					// Split response
					var response = msg.split("||");
					var discount_apply = response[0];
					var branchData = response[1];

					// Save to hidden input
					$('#discount_apply').val(discount_apply);

					$('#select_branch_id option').remove();

					var dataArr = branchData.split('#');

					$.each(dataArr, function(i, element) {
						if (element != "") {
							var dataArr2 = element.split('~');
							$('#select_branch_id').append(
								"<option value='" + dataArr2[0] + "'>" + dataArr2[1] + "</option>"
							);
						}
					});

				});
			}

			$("#select_branch_id").val('<?php echo $branch_id; ?>').change();
            $("#quo_discount").trigger("change");

		}).change();


	});

	$("#quo_trading_group").hide();
	$("#quo_items_set").hide();
	$("#quo_group_add_btn").hide();


	$("#group_type").change(function() {
		var group_type = $(this).val();
		$("#group_id").val('');

		if (group_type == 'G') {

			$("#quo_trading_group").show();
			$("#quo_items_set").show();
			$("#quo_group_add_btn").show();
			$("#quo_desc").hide();
			$("#quo_desc_qty").hide();
			$("#quo_desc_unit").hide();
			$("#quo_desc_unit_price").hide();
			$("#quo_desc_discount").hide();
			$("#quo_desc_amt").hide();
			$("#quo_desc_gst").hide();
			$("#quo_desc_net_amt").hide();
			$("#quo_desc_image").hide();
			$("#add_items").hide();

		} else if (group_type == 'I') {

			$("#quo_desc").show();
			$("#quo_desc_qty").show();
			$("#quo_desc_unit").show();
			$("#quo_desc_unit_price").show();
			$("#quo_desc_discount").show();
			$("#quo_desc_amt").show();
			$("#quo_desc_gst").show();
			$("#quo_desc_net_amt").show();
			$("#quo_desc_image").show();
			$("#add_items").show();
			$("#quo_group_add_btn").hide();
			$("#quo_trading_group").hide();
			$("#quo_items_set").hide();
		}
	});

	//-Individual Item Autocomplete /
	// $('#item_code').change(function() {

	// });

	// $('#item_code').change(function() {


	// 	// alert('quo_selling_price');
	// });

	$('#item_code').autocomplete({
		source: function(request, response) {
			$.ajax({
				url: "inc/auto/select_quotation_items.php",
				dataType: "json",
				data: {
					q: request.term
				},
				beforeSend: function() {
					$('#quo_qty').val('');
					$('#quo_discount').val('');
					$('#quo_selling_price').val('');
					$('#quo_unit').val('');
					$('#quo_price').val('');
					$('#quo_vat').val('');
					$('#quo_net_amt').val('');
				},
				success: function(data) {
					response(data);
				}
			});
		},
		minLength: 1,
		select: function(event, ui) {

			$('#search_item').val(ui.item.value);
			$("#quo_selling_price").val(ui.item.unit_price);
			$("#quo_unit").val(ui.item.uom);
			$("#quo_vat").val(ui.item.gst);

			$("#quo_discount").data("max_discount", ui.item.max_discount);
			$("#quo_discount").data("min_discount", ui.item.min_discount);

			if (ui.item.item_type == 6) {
				$("#quo_selling_price").attr("readonly", false);
			} else {
				$("#quo_selling_price").attr("readonly", true);
			}

			$('#item_id').val(ui.item.id);
		}
	}).data('ui-autocomplete')._renderItem = function(ul, item) {
		return $("<li class='ui-autocomplete-row'></li>")
			.data("item.autocomplete", item)
			.append(item.label)
			.appendTo(ul);
	};

	//------------------------------Discount Calculation----------------------//
	/*$('#quo_qty, #quo_selling_price, #item_code, #quo_discount, #group_id').change(function() {
		var qty = $('#quo_qty').val();
		var quo_discount = $('#quo_discount').val();
		var quo_selling_price = $("#quo_selling_price").val();
		var quo_vat = $("#quo_vat").val();

		final_price = 0;
		price = (quo_selling_price * qty);

		if (quo_discount > 0) {
			discount_amt = ((price * quo_discount) / 100);
			final_price = price - discount_amt;
		} else {
			final_price = price;
		}

		if (quo_vat > 0)
			tax_val = ((final_price * quo_vat) / 100);
		else
			tax_val = 0;

		net_val = final_price + tax_val;

		$('#quo_price').val(final_price.toFixed(2));
		$('#quo_net_amt').val(net_val.toFixed(2));
		$('#quo_discount1').val(final_price.toFixed(2));
	});*/

	$('#quo_qty, #quo_selling_price, #item_code, #quo_discount, #group_id').change(function() {
		var qty = parseFloat($('#quo_qty').val()) || 0;
		var quo_discount = parseFloat($('#quo_discount').val()) || 0;
		var quo_selling_price = parseFloat($("#quo_selling_price").val()) || 0;
		var quo_vat = parseFloat($("#quo_vat").val()) || 0;
		var discount_apply = $('#discount_apply').val();


		var max_discount = parseFloat($("#quo_discount").data("max_discount")) || 0;
		var min_discount = parseFloat($("#quo_discount").data("min_discount")) || 0;

		// Validate discount range
		if (discount_apply == 0) {
			if (quo_discount > 0) {
				if (quo_discount < min_discount || quo_discount > max_discount) {
					alert('Discount must be between ' + min_discount + '% and ' + max_discount + '%');
					$('#quo_discount').val(0);
					$('#quo_discount').trigger('change');
					return false;
				}
			}
		}

		var price = quo_selling_price * qty;
		var final_price = price;

		if (quo_discount > 0) {
			var discount_amt = (price * quo_discount) / 100;
			final_price = price - discount_amt;
		}

		var tax_val = (quo_vat > 0) ? ((final_price * quo_vat) / 100) : 0;
		var net_val = final_price + tax_val;

		$('#quo_price').val(final_price.toFixed(2));
		$('#quo_net_amt').val(net_val.toFixed(2));
		$('#quo_discount1').val(final_price.toFixed(2));
	});



	//------------+ Click Function --------------//-Quotation Individual Table-//----------------------------//

	$('#add_items').click(function() {
		if (isNull(document.thisForm.item_code, "item Description..!")) {
			return false;
		}
		if (isNull(document.thisForm.quo_qty, "Quantity...!")) {
			return false;
		}

		if (document.thisForm.quo_selling_price.value == 0) {
			alert("Please check the Unit Price...!");
			return false;
		}
		if (document.thisForm.quo_qty.value == 0) {
			alert("Please check the Unit Price...!");
			return false;
		}

		var table = document.getElementById("quo_table");
		var rowCount = 1;
		var item_id = $("#item_id").val();
		var supp_id = $("#supp_id").val();
		var quo_qty = $("#quo_qty").val();
		var quo_unit = $("#quo_unit").val();
		var quo_selling_price = $("#quo_selling_price").val();
		var quo_discount = $("#quo_discount").val();
		var quo_discount1 = $("#quo_discount1").val();
		var quo_price = $("#quo_price").val();
		var quo_vat = $("#quo_vat").val();
		var quo_net_amt = $("#quo_net_amt").val();
		var arr = [];
		var is_ch = 0;


		$("#quo_table tr").each(function() {
			arr.push(this.id);
		});

		if (jQuery.inArray(item_id, arr) != -1) {

			var is_ch = 1;
		}

		$.ajax({
			type: "POST",
			url: "inc/cis_ajax/jquery_quotation_details.php",
			data: {
				"item_id": item_id,
				"quo_qty": quo_qty,
				"quo_unit": quo_unit,
				"quo_selling_price": quo_selling_price,
				"quo_discount": quo_discount,
				"quo_discount1": quo_discount1,
				"quo_price": quo_price,
				"quo_vat": quo_vat,
				"quo_net_amt": quo_net_amt,
				'mode': 'save'
			}
		}).done(function(msg) {
			if (is_ch == 0) {
				$("#quo_table tbody").append(msg);
			} else {
				$("#" + item_id).replaceWith(msg);
			}
			$("#item_id").val('').trigger('change');
			$("#item_code").val('').trigger('click');
			$("#quo_unit").val('');
			$('#quo_qty').val('');
			$("#quo_selling_price").val('');
			$('#quo_discount').val('');
			$('#quo_price').val('');
			$('#quo_vat').val('');
			$('#quo_net_amt').val('');
			findQuotationItemTotal();
		});
	});

	//------------------//- Group- Items -//---------------- ---//

	$('#item_group_add').click(function() {

		if (notSelected(document.thisForm.group_id, "Group Name..!")) {
			return false;
		}
		if (isNull(document.thisForm.quo_set, "Set...!")) {
			return false;
		}
		if (document.thisForm.quo_set.value == 0) {
			alert("Please check the Set...!");
			return false;
		}

		var table = document.getElementById("quo_table");
		var rowCount = 1;
		var group_id = $("#group_id").val();
		var quo_set = $("#quo_set").val();
		var supp_id = $("#supp_id").val();

		$.ajax({
			type: "POST",
			url: "inc/cis_ajax/jquery_quotation_details.php",
			data: {
				"group_id": group_id,
				"supp_id": supp_id,
				"quo_set": quo_set,
				'mode': 'save'
			}
		}).done(function(msg) {
			$("#quo_table tbody").append(msg);
			$("#group_id").val('').trigger('change');
			$("#quo_set").val('');
			findQuotationItemTotal();
		});
	});

	function findQuotationItemTotal() {
		var quo_total_qty = quo_total_amt = quo_total_net = 0;

		$("#quo_table tr").each(function() {

			var temp_qty = parseFloat($(this).closest('tr').find('.temp_qty').val());
			if (isNaN(temp_qty)) temp_qty = 0;
			quo_total_qty += temp_qty;

			var temp_amt = parseFloat($(this).closest('tr').find('.temp_quo_price').val());
			if (isNaN(temp_amt)) temp_amt = 0;
			quo_total_amt += temp_amt;

			var temp_net_amt = parseFloat($(this).closest('tr').find('.temp_net_amt').val());
			if (isNaN(temp_net_amt)) temp_net_amt = 0;
			quo_total_net += temp_net_amt;

		});

		$('#quo_total_qty').html(quo_total_qty);
		$('#quo_total_amt').html(quo_total_amt.toFixed(2));
		$('#quo_total_netamt').html(quo_total_net.toFixed(2));
		$('#txt_quo_total_amt').val(quo_total_amt.toFixed(2));
		$('#txt_quo_total_netamt').val(quo_total_net.toFixed(2));
		findQuotationPackageTotal();

	}

	//------Package total function--------//

	function findQuotationPackageTotal() {
		// alert();
		var quo_pack_total = 0;
		var quo_total_amt = parseFloat($('#txt_quo_total_amt').val());

		if (isNaN(quo_total_amt)) quo_total_amt = 0;
		$("#package_table tr").each(function() {
			var per_fa = parseFloat($(this).closest('tr').find('.quo_pack_per_fa').val());

			if (per_fa == 1) {
				per_fa_value = parseFloat($(this).closest('tr').find('.quo_pack_per_fa_value').val());
				if (isNaN(per_fa_value)) per_fa_value = 0;

				if (per_fa_value > 0)
					taxable_value = quo_total_amt * per_fa_value / 100;
				else
					taxable_value = 0;
				$(this).closest('tr').find('.quo_pack_taxable_val').val(taxable_value);
				$(this).closest('tr').find('.disp_tax_val').html(taxable_value.toFixed(2));

				gst_per = parseFloat($(this).closest('tr').find('.quo_pack_gst_per').val());
				if (isNaN(gst_per)) gst_per = 0;
				if (gst_per > 0)
					gst_amt = taxable_value * gst_per / 100;
				else
					gst_amt = 0;
				$(this).closest('tr').find('.disp_gst_amt').html(gst_amt.toFixed(2));
				$(this).closest('tr').find('.quo_pack_gst_amt').val(gst_amt.toFixed(2));

				total = taxable_value + gst_amt;
				$(this).closest('tr').find('.quo_pack_total').val(total);
				$(this).closest('tr').find('.disp_pack_total').html(total.toFixed(2));
				quo_pack_total += total;
			} else {
				inv_quo_pack_total = parseFloat($(this).closest('tr').find('.quo_pack_total').val());
				if (isNaN(inv_quo_pack_total)) inv_quo_pack_total = 0;
				quo_pack_total += inv_quo_pack_total;
			}
		});
		$('#quo_total_pack_netamt').html(quo_pack_total.toFixed(2));
		GrandTotal();
	}

	//------Grand total function--------//

	function GrandTotal() {

		var quo_total_quo_netamt = parseFloat($('#quo_total_netamt').text());
		if (isNaN(quo_total_quo_netamt)) quo_total_quo_netamt = 0;

		var quo_total_pack_netamt = parseFloat($('#quo_total_pack_netamt').text());
		if (isNaN(quo_total_pack_netamt)) quo_total_pack_netamt = 0;

		var grand_total = quo_total_quo_netamt + quo_total_pack_netamt;

		$('.final_total').html(Math.round(grand_total).toFixed(2));
		$('#txt_final_total').val(Math.round(grand_total).toFixed(2));

	}

	//---- Individual remove----//

	function remove_item(auto_id) {
		$('#' + auto_id).remove();
		findQuotationItemTotal();
	}

	function remove_item1(auto_id) {
		$('#PK_' + auto_id).remove();
		findQuotationPackageTotal();
	}

	function remove_item_RI(temp_sal_repair_id) {
		$('#RI_' + temp_sal_repair_id).remove();
		var rowCount = $('#quo_table tbody tr').length;
		findQuotationPackageTotal();
	}

	//---- Get value package ---//

	function get_value() {
		var quo_pack_gst_id = $("#quo_pack_gst_id").val();
		var quo_total_amt = $("#quo_total_amt").text();
		var quo_pack_per_fa_value = $("#quo_pack_per_fa_value").val();
		var quo_pack_per_fa = $("#quo_pack_per_fa").val();

		$.ajax({
			type: "POST",
			url: "inc/cis_ajax/jquery_quotation_package_cal.php",
			data: {
				"quo_total_amt": quo_total_amt,
				"quo_pack_per_fa": quo_pack_per_fa,
				"quo_pack_per_fa_value": quo_pack_per_fa_value,
				"quo_pack_gst_id": quo_pack_gst_id
			}
		}).done(function(msg) {
			var data = msg.split('~');
			$("#quo_pack_gst_per").val(data[0]);
			$("#quo_pack_taxable_value").val(data[1]);
			$("#quo_pack_gst_amt").val(data[2]);
			GrandTotal();
		});
	}

	$('#quo_pack_per_fa_value').change(function() {
		get_value();
	});

	$('#quo_pack_gst_id').change(function() {
		get_value();
	});

	$("#quo_val").show();
	$("#quo_per").hide();
	$('#quo_pack_per_fa').change(function() {
		get_value();
		change_heading();
	});

	function change_heading() {

		$("#quo_per").hide();
		$("#quo_val").show();
		var per_val = $("#quo_pack_per_fa").val();
		if (per_val == 1) {
			$("#quo_per").show();
			$("#quo_val").hide();
		} else if (per_val == 2) {
			$("#quo_per").hide();
			$("#quo_val").show();
		}
	}

	//---------------------------------------//

	$("#pack_id").change(function() {
		var desc_id = $(this).val();

		$("#quo_pack_per_fa").val('');
		$("#quo_pack_per_fa_value").val('');
		$("#quo_pack_gst_id").val('');

		$.ajax({
			type: "POST",
			url: "inc/cis_ajax/jquery_quotation_other_dets.php",
			data: {
				"desc_id": desc_id
			}
		}).done(function(msg) {
			var data = msg.split('~');

			$("#quo_pack_per_fa option[value=" + data[0] + "]").attr('selected', 'selected').trigger('change');
			$("#quo_pack_per_fa_value").val(data[2]);
			setTimeout(function() {
				$("#quo_pack_gst_id option[value=" + data[1] + "]").attr('selected', 'selected').trigger('change');
			}, 300);
		});
	});



	//----- Add Package Details -----//

	$('#add_pack').click(function() {

		if (isNull(document.thisForm.pack_id, "Description..!")) {
			return false;
		}
		if (notSelected(document.thisForm.quo_pack_per_fa, "% / Fixed Amount...!")) {
			return false;
		}
		if (isNull(document.thisForm.quo_pack_per_fa_value, "% / Fixed Amount...!")) {
			return false;
		}
		var quo_pack_per_fa_value = document.thisForm.quo_pack_per_fa_value.value;
		if (quo_pack_per_fa_value <= 0) {
			alert("Please add % / Fixed Amount...!");
			return false;
		}
		if (notSelected(document.thisForm.quo_pack_gst_id, "HSN...!")) {
			return false;
		}

		var table = document.getElementById("package_table");
		var rowCount = 1;
		var pack_id = $("#pack_id").val();
		var quo_pack_per_fa = $("#quo_pack_per_fa").val();
		var quo_pack_per_fa_value = $("#quo_pack_per_fa_value").val();
		var quo_pack_gst_id = $("#quo_pack_gst_id").val();
		var quo_pack_gst_per = $("#quo_pack_gst_per").val();
		var quo_pack_gst_amt = $("#quo_pack_gst_amt").val();
		var quo_pack_taxable_value = $("#quo_pack_taxable_value").val();

		var arr = [];
		var is_pack = 0;

		$("#package_table tr").each(function() {
			arr.push(this.id);
		});
		if (jQuery.inArray(pack_id, arr) != -1) {

			var is_pack = 1;
		}

		$.ajax({
			type: "POST",
			url: "inc/cis_ajax/jquery_quotation_package_details.php",
			data: {

				"pack_id": pack_id,
				"quo_pack_per_fa": quo_pack_per_fa,
				"quo_pack_per_fa_value": quo_pack_per_fa_value,
				"quo_pack_gst_id": quo_pack_gst_id,
				"quo_pack_gst_per": quo_pack_gst_per,
				"quo_pack_gst_amt": quo_pack_gst_amt,
				"quo_pack_taxable_value": quo_pack_taxable_value,
				'mode': 'save'
			}
		}).done(function(msg) {

			if (is_pack == 0) {
				$("#package_table tbody").append(msg);
			} else {
				$("#" + pack_id).replaceWith(msg);
			}

			$("#pack_id").val('').trigger('change'); //id
			$("#quo_pack_per_fa").val('').trigger('change');
			$('#quo_pack_per_fa_value').val('').trigger('change');
			$("#quo_pack_taxable_val").val('');
			$('#quo_pack_gst_id').val('').trigger('change');
			$('#quo_pack_gst_per').val('');
			findQuotationPackageTotal();

		});
	});

	//-------- on change function-----------------------------//

	$("#tc_group").change(function() {
		var tc_principal_id = $(this).val();
		//alert(tc_principal_id);

		var emt = 0;
		$("#terms_con_id1 option[value=" + emt + "]").attr('selected', 'selected').trigger('change');
		$("#terms_con_id2 option[value=" + emt + "]").attr('selected', 'selected').trigger('change');
		$("#terms_con_id3 option[value=" + emt + "]").attr('selected', 'selected').trigger('change');
		$("#terms_con_id4 option[value=" + emt + "]").attr('selected', 'selected').trigger('change');
		$("#terms_con_id5 option[value=" + emt + "]").attr('selected', 'selected').trigger('change');
		$("#terms_con_id6 option[value=" + emt + "]").attr('selected', 'selected').trigger('change');
		$("#terms_con_id7 option[value=" + emt + "]").attr('selected', 'selected').trigger('change');
		$("#terms_con_id8 option[value=" + emt + "]").attr('selected', 'selected').trigger('change');

		$.ajax({
			type: "POST",
			url: "inc/cis_ajax/jquery_quotation_tc_group_dets.php",
			data: {
				"tc_principal_id": tc_principal_id
			}
		}).done(function(msg) {
			// alert(msg);
			var data = msg.split('~');

			if (data[0] != '' && data[0] > 0)
				$("#terms_con_id1 option[value=" + data[0] + "]").attr('selected', 'selected').trigger('change');
			else
				$("#terms_con_id1").val('').trigger('change');

			if (data[1] != '' && data[1] > 0)
				$("#terms_con_id2 option[value=" + data[1] + "]").attr('selected', 'selected').trigger('change');
			else
				$("#terms_con_id2").val('').trigger('change');

			if (data[2] != '' && data[2] > 0)
				$("#terms_con_id3 option[value=" + data[2] + "]").attr('selected', 'selected').trigger('change');
			else
				$("#terms_con_id3").val('').trigger('change');

			if (data[3] != '' && data[3] > 0)
				$("#terms_con_id4 option[value=" + data[3] + "]").attr('selected', 'selected').trigger('change');
			else
				$("#terms_con_id4").val('').trigger('change');

			if (data[4] != '' && data[4] > 0)
				$("#terms_con_id5 option[value=" + data[4] + "]").attr('selected', 'selected').trigger('change');
			else
				$("#terms_con_id5").val('').trigger('change');

			if (data[5] != '' && data[5] > 0)
				$("#terms_con_id6 option[value=" + data[5] + "]").attr('selected', 'selected').trigger('change');
			else
				$("#terms_con_id6").val('').trigger('change');

			if (data[6] != '' && data[6] > 0)
				$("#terms_con_id7 option[value=" + data[6] + "]").attr('selected', 'selected').trigger('change');
			else
				$("#terms_con_id7").val('').trigger('change');

			if (data[7] != '' && data[7] > 0)
				$("#terms_con_id8 option[value=" + data[7] + "]").attr('selected', 'selected').trigger('change');
			else
				$("#terms_con_id8").val('').trigger('change');
		});
	});

	$("#otdets_group").change(function() {
		var principal_id = $(this).val();

		$.ajax({
			type: "POST",
			url: "inc/cis_ajax/jquery_quotation_select_otdets.php",
			data: {
				principal_id: principal_id
			}
		}).done(function(msg) {
			$('#pack_id option').remove();
			var dataArr = msg.split('#');
			$.each(dataArr, function(i, element) {
				if (dataArr[i] != "") {
					var dataArr2 = dataArr[i].split('~');
					$('#pack_id').append("<option value='" + dataArr2[0] + "'>" + dataArr2[1] + "</option>");
				}
			});
			$("#s2id_pack_id").select2('val', '');
			$("#s2id_quo_pack_per_fa").select2('val', '');
			$("#s2id_quo_pack_gst_id").select2('val', '');
			//	$("#quo_pack_decp").trigger("liszt:updated");
		});
		$("#tc_group").val($(this).val()).change();

	});
</script>

</html>