<?php
// 2026-11-16 new - service call register (same flow as Benzear)
require_once("../common/dbconnect.php");
require_once("../common/functions.php");
require_once("../common/dbhandler.php");
require_once("../common/csrf.php");
require_once("../common/access.php");
require_once("../common/service_call.php");
session_start();
$conn = new dbconnect();
$dbconn = new dbhandler();
fnAjaxGuard();
// a customer's details to fill the call form
$st = $conn->prepare("SELECT supp_name, supp_contact_person1, supp_mobile1, supp_landline1, supp_email, supp_website, supp_gst, supp_pan,
	supp_add1, supp_add2, state_id, district_id, city_id, supp_pincode FROM mst_supplier_new WHERE supp_type = 'C' AND supp_id = ?");
$st->execute(array((int)(isset($_POST['supp_id']) ? $_POST['supp_id'] : 0)));
header('Content-Type: application/json');
echo json_encode($st->fetch(PDO::FETCH_ASSOC) ?: new stdClass());
