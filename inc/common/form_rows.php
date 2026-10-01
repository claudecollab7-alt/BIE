<?php
// Row renderers shared by the add/edit screens and their ajax endpoints.
// The rows live in the page and post back as arrays - no temp tables.

if (!defined('BIE_FORM_ROWS_LOADED')) {
define('BIE_FORM_ROWS_LOADED', 1);

// Builds one row for the branch table. Nothing is written to the database -
// the row lives in the page and is posted back with the form.
function fnBranchRow($dbconn, $p, $sno)
{
	$name    = isset($p['branch_name']) ? trim($p['branch_name']) : '';
	$prefix  = isset($p['prefix']) ? trim($p['prefix']) : '';
	$person  = isset($p['branch_contact_person']) ? trim($p['branch_contact_person']) : '';
	$contact = isset($p['branch_contact_no']) ? trim($p['branch_contact_no']) : '';
	$add1    = isset($p['branch_add1']) ? trim($p['branch_add1']) : '';
	$add2    = isset($p['branch_add2']) ? trim($p['branch_add2']) : '';
	$state   = isset($p['state_id']) ? (int)$p['state_id'] : 0;
	$dist    = isset($p['district_id']) ? (int)$p['district_id'] : 0;
	$city    = isset($p['city_id']) ? (int)$p['city_id'] : 0;
	$pincode = isset($p['branch_pincode']) ? trim($p['branch_pincode']) : '';

	$city_name  = $city ? $dbconn->GetSingleReconrd("mst_city", "city_name", "city_id", $city) : '';
	$dist_name  = $dist ? $dbconn->GetSingleReconrd("mst_district", "district_name", "district_id", $dist) : '';
	$state_name = $state ? $dbconn->GetSingleReconrd("mst_state", "state_name", "state_id", $state) : '';

	$addr = htmlspecialchars($add1);
	if ($add2 != '') {
		$addr .= ', ' . htmlspecialchars($add2);
	}
	if ($city_name  != '') { $addr .= ',<br/>' . htmlspecialchars($city_name); }
	if ($dist_name  != '') { $addr .= ',<br/>' . htmlspecialchars($dist_name); }
	if ($state_name != '') { $addr .= ',<br/>' . htmlspecialchars($state_name); }
	if ($pincode    != '') { $addr .= ' - ' . htmlspecialchars($pincode); }

	$h = function ($v) { return htmlspecialchars($v, ENT_QUOTES); };

	return '<tr class="branch-row">
		<td style="vertical-align:top;" class="br-sno">' . (int)$sno . '</td>
		<td style="vertical-align:top;">
			<b>' . htmlspecialchars($name) . '<br>' . htmlspecialchars($prefix . ' ' . $person) . '</b><br>' . htmlspecialchars($contact) . '
			<input type="hidden" class="br_name"           name="br_name[]"           value="' . $h($name) . '" />
			<input type="hidden" class="br_prefix"         name="br_prefix[]"         value="' . $h($prefix) . '" />
			<input type="hidden" class="br_contact_person" name="br_contact_person[]" value="' . $h($person) . '" />
			<input type="hidden" class="br_contact_no"     name="br_contact_no[]"     value="' . $h($contact) . '" />
			<input type="hidden" class="br_add1"           name="br_add1[]"           value="' . $h($add1) . '" />
			<input type="hidden" class="br_add2"           name="br_add2[]"           value="' . $h($add2) . '" />
			<input type="hidden" class="br_state_id"       name="br_state_id[]"       value="' . (int)$state . '" />
			<input type="hidden" class="br_district_id"    name="br_district_id[]"    value="' . (int)$dist . '" />
			<input type="hidden" class="br_city_id"        name="br_city_id[]"        value="' . (int)$city . '" />
			<input type="hidden" class="br_pincode"        name="br_pincode[]"        value="' . $h($pincode) . '" />
		</td>
		<td style="vertical-align:top;">' . $addr . '</td>
		<td align="center" style="vertical-align:top;">
			<a href="javascript:;" class="br-edit" data-popup="tooltip" title="Edit"><i class="icon-pencil5 bg-edit mr-2"></i></a>
			<a href="javascript:;" class="br-remove" data-popup="tooltip" title="Remove"><i class="icon-bin bg-delete mr-2"></i></a>
		</td>
	</tr>';
}

}
