<?php
// 2026-10-07 shared item search for the type-ahead boxes - one query, best matches first, capped

define('ITEM_SEARCH_LIMIT', 30);

// tbl_item_stock column named in mst_branch, checked against the table because it goes into sql
function fnItemStockColumn($conn, $branch_id, $branch_field)
{
	static $cols = null;
	if ($cols === null) {
		$cols = array();
		foreach ($conn->query("SHOW COLUMNS FROM tbl_item_stock") as $c) {
			$cols[] = $c->Field;
		}
	}
	$stmt = $conn->prepare("SELECT `" . preg_replace('/[^a-z_]/', '', $branch_field) . "` FROM mst_branch WHERE branch_id = :b");
	$stmt->execute(array(':b' => $branch_id));
	$col = (string)$stmt->fetchColumn();
	return in_array($col, $cols, true) ? $col : '';
}

// active items matching code, description or purchase code, visible to the branch,
// code-prefix matches first, then description-prefix, then the rest
function fnItemSearch($conn, $q, $branch_id)
{
	$like = '%' . $q . '%';
	$pre  = $q . '%';
	$stmt = $conn->prepare("SELECT i.item_id, i.item_code, i.item_desciption, i.item_type, i.item_uom,
								u.uom_name, h.igst
							FROM tbl_item_details i
							LEFT JOIN mst_uom u ON u.uom_id = i.item_uom
							LEFT JOIN mst_hsn h ON h.hsn_id = i.item_hsn
							WHERE i.item_status = 1
							  AND (i.item_code LIKE :q1 OR i.item_desciption LIKE :q2 OR i.item_purchase_code LIKE :q3)
							  AND (:b1 = 1 OR FIND_IN_SET(:b2, REPLACE(i.branch_id, ' ', '')))
							ORDER BY (i.item_code LIKE :p1) DESC, (i.item_desciption LIKE :p2) DESC, i.item_code
							LIMIT " . ITEM_SEARCH_LIMIT);
	$stmt->execute(array(':q1' => $like, ':q2' => $like, ':q3' => $like,
						 ':b1' => $branch_id, ':b2' => (string)$branch_id, ':p1' => $pre, ':p2' => $pre));
	return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// stock-row fields for the found items in one query - first row per item, as GetSingleReconrd gave
function fnItemStockFields($conn, $rows, $cols)
{
	$out = array();
	$cols = array_values(array_filter($cols));
	if (!$rows || !$cols) {
		return $out;
	}
	$ids = array_map('intval', array_column($rows, 'item_id'));
	$stmt = $conn->query("SELECT item_id, `" . implode('`, `', $cols) . "` FROM tbl_item_stock
						  WHERE item_id IN (" . implode(',', $ids) . ") ORDER BY stock_id");
	foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $s) {
		if (!isset($out[$s['item_id']])) {
			$out[$s['item_id']] = $s;
		}
	}
	return $out;
}
