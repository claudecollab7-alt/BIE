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
// the number a new call of this state will get (given again on save)
$finyr = $dbconn->GetSingleReconrd("mst_finyear", "finyr", "finyr_active", 1);
echo ((int)$_POST['state_id'] > 0) ? fnScNextNo($conn, (int)$_POST['state_id'], $finyr) : '';
