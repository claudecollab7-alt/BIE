<?php
// 2026-11-16 new - service call register (the same flow as Benzear): call number, complaint and spare rows,
// and the calls transferred from Benzear (BIE number, branch, customer and master ids from names)
// Line keys posted as complaints[<key>] / spares[<key>]: d<id> for a saved line, n<hex> for one added on the page.

// posted line key - saved (d + id) or added on the page (n + hex)
function fnLineKeyOk($key)
{
	return (bool)preg_match('/^(d[0-9]+|n[0-9a-f]{10})$/', $key);
}

function fnLineNewKey()
{
	return 'n' . bin2hex(random_bytes(5));
}

// PHP dropped the post for being over post_max_size
function fnPostTooLarge()
{
	return $_SERVER['REQUEST_METHOD'] === 'POST' && empty($_POST) && empty($_FILES)
		&& isset($_SERVER['CONTENT_LENGTH']) && (int)$_SERVER['CONTENT_LENGTH'] > 0;
}

// next call number of a state: BIE-<state>-<finyr>-001 (BIE- so it is not taken for a Benzear number)
function fnScNextNo($conn, $state_id, $finyr)
{
	$st = $conn->prepare("SELECT state_shname FROM mst_state WHERE state_id = ?");
	$st->execute(array((int)$state_id));
	$code = trim((string)$st->fetchColumn());
	$prefix = 'BIE-' . ($code != '' ? $code : 'XX') . '-' . $finyr . '-';
	$st = $conn->prepare("SELECT MAX(CAST(SUBSTRING(sc_no, ?) AS UNSIGNED)) FROM tbl_service_call_register WHERE sc_no LIKE ?");
	$st->execute(array(strlen($prefix) + 1, $prefix . '%'));
	return $prefix . str_pad((int)$st->fetchColumn() + 1, 3, '0', STR_PAD_LEFT);
}

// complaint text - not blank
function fnScComplaintDesc($desc)
{
	$desc = trim((string)$desc);
	if ($desc == '') {
		throw new Exception("Please enter Complaint Description..!");
	}
	return $desc;
}

// new complaint row (not saved yet)
function fnScComplaintRow($key, $desc)
{
	$d = htmlspecialchars($desc);
	return '<tr data-key="' . $key . '">
				<td class="line_sno"></td>
				<td>' . $d . '<input type="hidden" name="complaints[' . $key . '][desc]" value="' . $d . '"></td>
				<td align="center"><a href="javascript:;" onclick="remove_complaint(this);" title="Remove"><i class="icon-cross2 text-danger"></i></a></td>
			</tr>';
}

// spare part used on a complaint - an active sales spare (a saved line keeps its item and avl qty), qty above 0
// avl qty = stock of the user's branch when the line is first saved; stock itself does not move
function fnScSpareLine($conn, $item_id, $qty, $saved = null)
{
	$item_id = (int)$item_id;
	$same = $saved && $saved['item_id'] == $item_id;
	$item = $conn->query("SELECT item_uom FROM tbl_item_details WHERE item_id = " . $item_id
		. ($same ? "" : " AND item_status = 1 AND item_dept_sales = 1 AND item_type = 5"))->fetch(PDO::FETCH_OBJ);
	if (!$item) {
		throw new Exception("Please pick the Item from the list..!");
	}
	$qty = round((float)$qty, 2);
	if ($qty <= 0 || $qty > 99999999) {
		throw new Exception("Qty must be above 0.");
	}
	$unit = $conn->query("SELECT uom_code FROM mst_uom WHERE uom_id = " . (int)$item->item_uom)->fetchColumn();
	return array(
		'item_id' => $item_id,
		'qty' => $qty,
		'unit' => $same ? $saved['unit'] : (string)$unit,
		'avl' => $same ? (float)$saved['avl'] : fnScBranchStock($conn, $item_id)
	);
}

// stock of the item in the user's branch
function fnScBranchStock($conn, $item_id)
{
	$f = $conn->query("SELECT branch_stock_field FROM mst_branch WHERE branch_id = " . (int)$_SESSION['_user_branch'])->fetchColumn();
	if (!preg_match('/^[a-z_]+$/', (string)$f)) {
		return 0;
	}
	return round((float)$conn->query("SELECT `$f` FROM tbl_item_stock WHERE item_id = " . (int)$item_id . " ORDER BY stock_id LIMIT 1")->fetchColumn(), 2);
}

// spare part row
function fnScSpareRow($dbconn, $key, $l)
{
	$n = 'spares[' . $key . ']';
	$code = $dbconn->GetSingleReconrd("tbl_item_details", "item_code", "item_id", (int)$l['item_id']);
	$desc = $dbconn->GetSingleReconrd("tbl_item_details", "item_desciption", "item_id", (int)$l['item_id']);
	return '<tr data-key="' . $key . '" data-item="' . (int)$l['item_id'] . '">
				<td class="line_sno"></td>
				<td>' . htmlspecialchars($code) . '</td>
				<td>' . htmlspecialchars($desc) . '
					<input type="hidden" name="' . $n . '[item_id]" value="' . (int)$l['item_id'] . '">
					<input type="hidden" name="' . $n . '[qty]" value="' . $l['qty'] . '"></td>
				<td align="right">' . number_format((float)$l['avl'], 2) . '</td>
				<td align="center">' . number_format((float)$l['qty'], 2) . '</td>
				<td>' . htmlspecialchars($l['unit']) . '</td>
				<td align="center"><a href="javascript:;" onclick="remove_complaint_item(this);" title="Remove"><i class="icon-cross2 text-danger"></i></a></td>
			</tr>';
}

// charge type names (as in Benzear)
function fnScChargeTypes()
{
	return array(1 => 'Under Warranty', 2 => 'Pre Installation / Visit', 3 => 'AMC Visit', 4 => 'Paid Service', 5 => 'Paid Installation', 6 => 'Installation Visit');
}

// status label of a call
function fnScStatusLabel($sc)
{
	if ($sc->sc_status == 3 || $sc->sc_approve_status == 1) {
		return '<span class="badge badge-success">Approved</span>';
	}
	if ($sc->sc_status == 2) {
		return $sc->sc_verify_status == 1 ? '<span class="badge badge-info">Completed - in approval</span>' : '<span class="badge badge-success">Completed</span>';
	}
	return '<span class="badge badge-warning">Pending</span>';
}

// ---------- calls transferred from Benzear ----------

// id of a master row by its name (the two databases number their masters differently); 0 when not found
function fnScIdByName($conn, $table, $id_col, $name_col, $name, $where = '')
{
	$name = trim((string)$name);
	if ($name == '') {
		return 0;
	}
	$st = $conn->prepare("SELECT $id_col FROM $table WHERE UPPER(TRIM($name_col)) = UPPER(?) " . $where . " ORDER BY $id_col LIMIT 1");
	$st->execute(array($name));
	return (int)$st->fetchColumn();
}

// the BIE customer of a call: the same GST, else the same mobile, else the same name - or a new customer from the call
function fnScLinkCustomer($conn, $sc)
{
	$base = "SELECT supp_id FROM mst_supplier_new WHERE supp_type = 'C' AND supp_status = '1' AND ";
	foreach (array(array('supp_gst', $sc->enq_gst), array('supp_mobile1', $sc->mobile_no), array('supp_name', $sc->customer_name)) as $m) {
		if (trim((string)$m[1]) != '') {
			$st = $conn->prepare($base . "UPPER(TRIM(" . $m[0] . ")) = UPPER(TRIM(?)) ORDER BY supp_id LIMIT 1");
			$st->execute(array($m[1]));
			$id = (int)$st->fetchColumn();
			if ($id > 0) {
				return $id;
			}
		}
	}
	// a new customer, made the way the customer form makes one: its own ledger under Sundry Debtors, code C + next id
	$conn->prepare("INSERT INTO mst_ledger (group_id, ledger_name, ledger_type, open_bal, open_bal_type) VALUES (29, ?, 'DR', 0, '')")
		->execute(array(ucwords(strtolower(trim($sc->customer_name)))));
	$ledger_id = (int)$conn->lastInsertId();
	$code = 'C' . leadingZeros((int)$conn->query("SELECT IFNULL(MAX(supp_id), 0) + 1 FROM mst_supplier_new")->fetchColumn(), 4);
	$st = $conn->prepare("INSERT INTO mst_supplier_new (company_branch_id, branch_type, supp_type, supp_name, supp_code, supp_contact_person1, supp_mobile1, supp_landline1,
		supp_email, supp_website, supp_gst, supp_pan, supp_add1, supp_add2, state_id, district_id, city_id, supp_pincode, ledger_id, supp_approve_status, supp_status)
		VALUES (0, 'O', 'C', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0, 1)");
	$st->execute(array(trim($sc->customer_name), $code, $sc->contact_person, substr(preg_replace('/\D/', '', $sc->mobile_no), -10), $sc->tel_no, $sc->email, $sc->enq_website,
		strtoupper($sc->enq_gst), strtoupper($sc->enq_pan), $sc->enq_add1, $sc->enq_add2, $sc->state_id, $sc->district_id, $sc->city_id, $sc->enq_pincode, $ledger_id));
	return (int)$conn->lastInsertId();
}

// calls Benzear wrote here and BIE has not taken in yet: master ids from the names, branch from the state,
// a BIE number, and the customer (found or made). Runs when the list opens; each call once.
function fnScReceiveTransfers($conn)
{
	$ids = $conn->query("SELECT sc_id FROM tbl_service_call_register WHERE bie_received = 0")->fetchAll(PDO::FETCH_COLUMN);
	$finyr = $conn->query("SELECT finyr FROM mst_finyear WHERE finyr_active = 1")->fetchColumn();
	foreach ($ids as $sc_id) {
		try {
			$conn->beginTransaction();
			$sc = $conn->query("SELECT * FROM tbl_service_call_register WHERE sc_id = " . (int)$sc_id . " AND bie_received = 0 FOR UPDATE")->fetch(PDO::FETCH_OBJ);
			if (!$sc) {
				$conn->rollBack();
				continue;
			}
			$sc->state_id = fnScIdByName($conn, 'mst_state', 'state_id', 'state_name', $sc->src_state_name);
			$sc->district_id = fnScIdByName($conn, 'mst_district', 'district_id', 'district_name', $sc->src_district_name, $sc->state_id ? "AND state_id = " . (int)$sc->state_id : '');
			$sc->city_id = fnScIdByName($conn, 'mst_city', 'city_id', 'city_name', $sc->src_city_name, $sc->district_id ? "AND district_id = " . (int)$sc->district_id : '');
			$principal_id = fnScIdByName($conn, 'mst_principal', 'principal_id', 'principal_name', $sc->src_principal_name);
			// branch: the BIE branch in the call's state (GST state code), else Head Office
			$branch_id = 0;
			if ($sc->state_id > 0) {
				$branch_id = (int)$conn->query("SELECT b.branch_id FROM mst_branch b JOIN mst_state s ON s.state_code = b.branch_state_code
					WHERE s.state_id = " . (int)$sc->state_id . " AND b.branch_status = 1 ORDER BY b.branch_id LIMIT 1")->fetchColumn();
			}
			$sc->branch_id = $branch_id > 0 ? $branch_id : 1;
			$supp_id = fnScLinkCustomer($conn, $sc);
			$st = $conn->prepare("UPDATE tbl_service_call_register SET state_id = ?, district_id = ?, city_id = ?, principal_id = ?, branch_id = ?, supp_id = ?,
				sc_no = ?, sc_finyr = ?, sc_slno = (SELECT n FROM (SELECT IFNULL(MAX(sc_slno), 0) + 1 AS n FROM tbl_service_call_register) x), bie_received = 1
				WHERE sc_id = ?");
			$st->execute(array($sc->state_id, $sc->district_id, $sc->city_id, $principal_id, $sc->branch_id, $supp_id,
				fnScNextNo($conn, $sc->state_id, $finyr), $finyr, $sc_id));
			$conn->commit();
		} catch (Exception $e) {
			if ($conn->inTransaction()) {
				$conn->rollBack();
			}
			fnLogError($e);
		}
	}
}

// ---------- complaints and spares posted from the pages ----------

// posted complaints - new ones (text) and the saved ones still on the page
function fnScPostedComplaints($conn, $sc_id)
{
	$posted = isset($_POST['complaints']) && is_array($_POST['complaints']) ? $_POST['complaints'] : array();
	$out = array('new' => array(), 'keep' => array());
	foreach ($posted as $k => $p) {
		if (!fnLineKeyOk($k) || !is_array($p)) {
			throw new Exception("The complaints on this form are not valid. Please open it again.");
		}
		if ($k[0] == 'd') {
			$id = (int)substr($k, 1);
			if (!$conn->query("SELECT complaint_id FROM tbl_sc_complaints WHERE complaint_id = " . $id . " AND sc_id = " . (int)$sc_id)->fetch()) {
				throw new Exception("The complaints on this form are out of date. Please open it again.");
			}
			$out['keep'][$id] = 1;
		} else {
			$out['new'][] = fnScComplaintDesc(isset($p['desc']) ? $p['desc'] : '');
		}
	}
	if (count($out['new']) + count($out['keep']) == 0) {
		throw new Exception("Please add at least one complaint..!");
	}
	return $out;
}

// write the complaints - new ones added, saved ones taken off the page removed (open ones only)
function fnScSaveComplaints($conn, $sc_id, $posted)
{
	$saved = $conn->query("SELECT complaint_id, complaint_status FROM tbl_sc_complaints WHERE sc_id = " . (int)$sc_id)->fetchAll(PDO::FETCH_KEY_PAIR);
	$del = $conn->prepare("DELETE FROM tbl_sc_complaints WHERE complaint_id = :cid AND sc_id = :sc_id");
	$del_d = $conn->prepare("DELETE FROM tbl_sc_complaint_details WHERE complaint_id = :cid");
	foreach ($saved as $id => $status) {
		if (isset($posted['keep'][$id])) {
			continue;
		}
		if ($status == 1) {
			throw new Exception("A completed complaint can't be removed.");
		}
		$del->execute(array(':cid' => $id, ':sc_id' => $sc_id));
		$del_d->execute(array(':cid' => $id));
	}
	$ins = $conn->prepare("INSERT INTO tbl_sc_complaints (sc_id, complaint_desc, complaint_status) VALUES (:sc_id, :desc, 0)");
	foreach ($posted['new'] as $desc) {
		$ins->execute(array(':sc_id' => $sc_id, ':desc' => $desc));
	}
}

// call status from its complaints: pending while one is open, completed when all are done (approved stays approved)
function fnScRefreshStatus($conn, $sc_id)
{
	$c = $conn->query("SELECT COUNT(*) AS total, IFNULL(SUM(complaint_status = 1), 0) AS done FROM tbl_sc_complaints WHERE sc_id = " . (int)$sc_id)->fetch(PDO::FETCH_OBJ);
	$st = ($c->total > 0 && $c->total == $c->done) ? 2 : 1;
	$conn->prepare("UPDATE tbl_service_call_register SET sc_status = :st WHERE sc_id = :id AND sc_status IN (1, 2) AND sc_status <> :st2")
		->execute(array(':st' => $st, ':id' => (int)$sc_id, ':st2' => $st));
}

// posted spare parts - a saved line keeps its item
function fnScSparesPosted($conn, $complaint_id)
{
	$posted = isset($_POST['spares']) && is_array($_POST['spares']) ? $_POST['spares'] : array();
	$lines = array();
	$seen = array();
	foreach ($posted as $k => $p) {
		if (!fnLineKeyOk($k) || !is_array($p)) {
			throw new Exception("The spare parts on this form are not valid. Please open it again.");
		}
		$item_id = (int)(isset($p['item_id']) ? $p['item_id'] : 0);
		if (isset($seen[$item_id])) {
			throw new Exception("An item is added twice in the spare parts.");
		}
		$seen[$item_id] = 1;
		$saved = null;
		if ($k[0] == 'd') {
			$d = $conn->query("SELECT item_id, sc_unit, sc_avl_qty FROM tbl_sc_complaint_details WHERE auto_id = " . (int)substr($k, 1) . " AND complaint_id = " . (int)$complaint_id)->fetch(PDO::FETCH_OBJ);
			if (!$d || $d->item_id != $item_id) {
				throw new Exception("The spare parts on this form are out of date. Please open it again.");
			}
			$saved = array('item_id' => $d->item_id, 'unit' => $d->sc_unit, 'avl' => $d->sc_avl_qty);
		}
		$lines[$k] = fnScSpareLine($conn, $item_id, isset($p['qty']) ? $p['qty'] : 0, $saved);
	}
	return $lines;
}

// write the spare parts - saved lines kept by id, lines taken off the page removed
function fnScSaveSpares($conn, $complaint_id, $lines)
{
	$saved = array_flip($conn->query("SELECT auto_id FROM tbl_sc_complaint_details WHERE complaint_id = " . (int)$complaint_id)->fetchAll(PDO::FETCH_COLUMN));
	$ins = $conn->prepare("INSERT INTO tbl_sc_complaint_details (complaint_id, item_id, sc_qty, sc_unit, sc_avl_qty) VALUES (:cid, :iid, :qty, :unit, :avl)");
	$upd = $conn->prepare("UPDATE tbl_sc_complaint_details SET sc_qty = :qty WHERE auto_id = :id AND complaint_id = :cid");
	foreach ($lines as $k => $l) {
		if ($k[0] == 'd' && isset($saved[(int)substr($k, 1)])) {
			$upd->execute(array(':qty' => $l['qty'], ':id' => (int)substr($k, 1), ':cid' => $complaint_id));
			unset($saved[(int)substr($k, 1)]);
		} else {
			$ins->execute(array(':cid' => $complaint_id, ':iid' => $l['item_id'], ':qty' => $l['qty'], ':unit' => $l['unit'], ':avl' => $l['avl']));
		}
	}
	$del = $conn->prepare("DELETE FROM tbl_sc_complaint_details WHERE auto_id = :id AND complaint_id = :cid");
	foreach (array_keys($saved) as $id) {
		$del->execute(array(':id' => $id, ':cid' => $complaint_id));
	}
}

// page messages (jGrowl), the same as the other BIE pages
function fnScMessages()
{
	if (!empty($_SESSION['_msg'])) {
		echo "$.jGrowl('" . addslashes($_SESSION['_msg']) . "', { sticky: false, theme: 'alert-styled-left alert-arrow-left alert-success', position: 'bottom-right', life: '2000', header: 'Success!' });";
		$_SESSION['_msg'] = '';
	}
	if (!empty($_SESSION['_msg_err'])) {
		echo "$.jGrowl('" . addslashes($_SESSION['_msg_err']) . "', { sticky: false, theme: 'alert-styled-left alert-arrow-left alert-danger', position: 'top-right', life: '6000', header: 'Error!' });";
		$_SESSION['_msg_err'] = '';
	}
}
