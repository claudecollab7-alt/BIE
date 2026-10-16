<?php
// 2026-10-16 new - item price changes per branch for the bulk discount / margin screen and the price list screen
// cost = item price less discount, margin % on cost price; every change writes a tbl_itemprice_history row
// Head Office prices are also kept in the item master copy (tbl_item_details) - GRN values stock from it

function fnPriceCost($price, $discount)
{
	return round((float)$price - (float)$price * (float)$discount / 100, 2);
}

function fnPriceMargin($selling, $cost)
{
	return (float)$cost > 0 && (float)$selling > 0 ? round(((float)$selling - (float)$cost) / (float)$cost * 100, 2) : 0;
}

// active branches with their tbl_item_stock / tbl_itemprice_history column names, by branch id
function fnPriceBranches($conn)
{
	static $b = null;
	if ($b === null) {
		$b = array();
		foreach ($conn->query("SELECT * FROM mst_branch WHERE branch_status = 1 ORDER BY branch_id")->fetchAll(PDO::FETCH_OBJ) as $r) {
			$b[(int)$r->branch_id] = $r;
		}
	}
	return $b;
}

// item filter from the screen: principal, brand, division, item type, code / name text
function fnPriceFilterSql($f, &$params)
{
	$sql = " d.item_status = 1";
	$map = array('principal' => 'd.item_principal', 'brand' => 'd.item_brand_make', 'division' => 'd.item_division', 'item_type' => 'd.item_type');
	foreach ($map as $k => $col) {
		if (isset($f[$k]) && $f[$k] !== '' && ctype_digit((string)$f[$k])) {
			$sql .= " AND " . $col . " = :" . $k;
			$params[':' . $k] = (int)$f[$k];
		}
	}
	if (isset($f['search']) && trim($f['search']) !== '') {
		$sql .= " AND (d.item_code LIKE :s1 OR d.item_desciption LIKE :s2)";
		$params[':s1'] = $params[':s2'] = '%' . trim($f['search']) . '%';
	}
	return $sql;
}

// one row per item per branch with the branch's current prices
function fnPriceList($conn, $f, $branch_ids, $limit = 0)
{
	$branches = fnPriceBranches($conn);
	$params = array();
	$where = fnPriceFilterSql($f, $params);
	$cols = array();
	foreach ($branch_ids as $bid) {
		$b = $branches[$bid];
		foreach (array('price', 'discount', 'cost_price', 'selling_price', 'margin') as $c) {
			$cols[] = "IFNULL(s." . $b->{'branch_item_' . $c} . ", 0) AS b" . $bid . "_" . $c;
		}
	}
	$stmt = $conn->prepare("SELECT d.item_id, d.item_code, d.item_desciption, " . implode(', ', $cols) . "
							FROM tbl_item_details d LEFT JOIN tbl_item_stock s ON s.item_id = d.item_id
							WHERE " . $where . " ORDER BY d.item_code" . ($limit > 0 ? " LIMIT " . (int)$limit : ""));
	$stmt->execute($params);
	$rows = array();
	foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
		foreach ($branch_ids as $bid) {
			$p = "b" . $bid . "_";
			$rows[] = array(
				'item_id' => (int)$r['item_id'], 'branch_id' => $bid, 'branch' => $branches[$bid]->branch_code,
				'code' => $r['item_code'], 'name' => $r['item_desciption'],
				'price' => (float)$r[$p . 'price'], 'discount' => (float)$r[$p . 'discount'], 'cost' => (float)$r[$p . 'cost_price'],
				'margin' => (float)$r[$p . 'margin'], 'selling' => (float)$r[$p . 'selling_price'],
			);
		}
	}
	return $rows;
}

// history row with old and new values for one branch; the other branches' columns 0
function fnPriceHistory($conn, $item_id, $bid, $old, $new)
{
	$branches = fnPriceBranches($conn);
	$fields = array('price' => 'price', 'discount' => 'discount', 'min_discount' => 'min_discount', 'max_discount' => 'max_discount',
		'cost_price' => 'cost_price', 'selling_price' => 'selling_price', 'margin' => 'margin', 'msq' => 'msq', 'maq' => 'maq', 'moq' => 'moq',
		'item_curr_stock' => 'stock', 'item_uom' => 'uom', 'item_hsn' => 'hsn');
	$cols = array('item_id', 'branch_id', 'created_dtm', 'created_by');
	$vals = array(':item_id', ':branch_id', ':dtm', ':by');
	$p = array(':item_id' => $item_id, ':branch_id' => $bid, ':dtm' => date('Y-m-d H:i:s'), ':by' => (int)$_SESSION['_user_id']);
	foreach ($branches as $id => $b) {
		foreach (array('new' => $new, 'old' => $old) as $kind => $v) {
			foreach ($fields as $f => $key) {
				$col = $b->{'branch_' . $kind . '_' . $f};
				$cols[] = $col;
				$vals[] = ':' . $col;
				$p[':' . $col] = $id == $bid ? $v[$key] : 0;
			}
		}
	}
	$conn->prepare("INSERT INTO tbl_itemprice_history (" . implode(', ', $cols) . ") VALUES (" . implode(', ', $vals) . ")")->execute($p);
}

// current prices of one item in one branch, read for update
function fnPriceCurrent($conn, $item_id, $bid)
{
	$b = fnPriceBranches($conn)[$bid];
	fnEnsureItemStockRow($conn, $item_id);
	$stmt = $conn->prepare("SELECT s." . $b->branch_item_price . " AS price, s." . $b->branch_item_discount . " AS discount,
		s." . $b->branch_item_min_discount . " AS min_discount, s." . $b->branch_item_max_discount . " AS max_discount,
		s." . $b->branch_item_cost_price . " AS cost_price, s." . $b->branch_item_selling_price . " AS selling_price, s." . $b->branch_item_margin . " AS margin,
		s." . $b->branch_item_msq . " AS msq, s." . $b->branch_item_maq . " AS maq, s." . $b->branch_item_moq . " AS moq, s." . $b->branch_stock_field . " AS stock,
		IFNULL(d.item_uom, 0) AS uom, IFNULL(d.item_hsn, 0) AS hsn
		FROM tbl_item_stock s JOIN tbl_item_details d ON d.item_id = s.item_id WHERE s.item_id = :id FOR UPDATE");
	$stmt->execute(array(':id' => $item_id));
	$r = $stmt->fetch(PDO::FETCH_ASSOC);
	if ($r) {
		foreach (array('uom', 'hsn') as $k) {
			$r[$k] = (int)$r[$k];
		}
	}
	return $r;
}

// save new price / discount / selling for one item in one branch, cost and margin worked out here;
// false when nothing changed. Runs inside the caller's transaction.
function fnPriceSave($conn, $item_id, $bid, $price, $discount, $selling)
{
	$old = fnPriceCurrent($conn, $item_id, $bid);
	if (!$old) {
		return false;
	}
	$new = $old;
	$new['price'] = round((float)$price, 2);
	$new['discount'] = round((float)$discount, 2);
	$new['cost_price'] = fnPriceCost($new['price'], $new['discount']);
	$new['selling_price'] = round((float)$selling, 2);
	$new['margin'] = fnPriceMargin($new['selling_price'], $new['cost_price']);
	$same = true;
	foreach (array('price', 'discount', 'cost_price', 'selling_price', 'margin') as $k) {
		if (abs((float)$old[$k] - (float)$new[$k]) >= 0.005) {
			$same = false;
		}
	}
	if ($same) {
		return false;
	}
	fnPriceHistory($conn, $item_id, $bid, $old, $new);
	$b = fnPriceBranches($conn)[$bid];
	$conn->prepare("UPDATE tbl_item_stock SET " . $b->branch_item_price . " = :p, " . $b->branch_item_discount . " = :d, " . $b->branch_item_cost_price . " = :c, "
		. $b->branch_item_selling_price . " = :s, " . $b->branch_item_margin . " = :m WHERE item_id = :id")
		->execute(array(':p' => $new['price'], ':d' => $new['discount'], ':c' => $new['cost_price'], ':s' => $new['selling_price'], ':m' => $new['margin'], ':id' => $item_id));
	if ($bid == 1) {
		$conn->prepare("UPDATE tbl_item_details SET item_price = :p, item_discount = :d, item_cost_price = :c, item_selling_price = :s, margin_percent = :m WHERE item_id = :id")
			->execute(array(':p' => $new['price'], ':d' => $new['discount'], ':c' => $new['cost_price'], ':s' => $new['selling_price'], ':m' => $new['margin'], ':id' => $item_id));
	}
	return $new;
}

// bulk discount or margin for many items of one branch: same rules as fnPriceSave, done 1000 items at a time in sql
// (one row at a time took over two minutes for 10,000 rows). Returns how many item rows changed. Inside the caller's transaction.
function fnPriceBulk($conn, $bid, $item_ids, $what, $value)
{
	$branches = fnPriceBranches($conn);
	$b = $branches[$bid];
	$v = sprintf('%.2F', round((float)$value, 2));
	$P = 's.' . $b->branch_item_price; $D = 's.' . $b->branch_item_discount; $C = 's.' . $b->branch_item_cost_price;
	$S = 's.' . $b->branch_item_selling_price; $M = 's.' . $b->branch_item_margin;
	$nD = $what == 'discount' ? $v : $D;
	$nC = "ROUND(" . $P . " - " . $P . " * " . $nD . " / 100, 2)";
	$nS = $what == 'discount' ? $S : "ROUND(" . $nC . " + " . $nC . " * " . $v . " / 100, 2)";
	$nM = $what == 'discount' ? "IF(" . $nC . " > 0 AND " . $S . " > 0, ROUND((" . $S . " - " . $nC . ") / " . $nC . " * 100, 2), 0)" : $v;
	$changed_sql = "NOT (" . $D . " <=> " . $nD . " AND " . $C . " <=> " . $nC . " AND " . $S . " <=> " . $nS . " AND " . $M . " <=> " . $nM . ")";

	// history columns: this branch old / new, the other branches 0
	$fields = array('price', 'discount', 'min_discount', 'max_discount', 'cost_price', 'selling_price', 'margin', 'msq', 'maq', 'moq', 'item_curr_stock', 'item_uom', 'item_hsn');
	$same = array('price' => $P, 'discount' => $D, 'min_discount' => 's.' . $b->branch_item_min_discount, 'max_discount' => 's.' . $b->branch_item_max_discount,
		'cost_price' => $C, 'selling_price' => $S, 'margin' => $M, 'msq' => 's.' . $b->branch_item_msq, 'maq' => 's.' . $b->branch_item_maq,
		'moq' => 's.' . $b->branch_item_moq, 'item_curr_stock' => 's.' . $b->branch_stock_field, 'item_uom' => 'IFNULL(d.item_uom, 0)', 'item_hsn' => 'IFNULL(d.item_hsn, 0)');
	$new = array_merge($same, array('discount' => $nD, 'cost_price' => $nC, 'selling_price' => $nS, 'margin' => $nM));
	$cols = array('item_id', 'branch_id', 'created_dtm', 'created_by');
	$vals = array('s.item_id', (int)$bid, $conn->quote(date('Y-m-d H:i:s')), (int)$_SESSION['_user_id']);
	foreach ($branches as $id => $x) {
		foreach (array('new' => $new, 'old' => $same) as $kind => $src) {
			foreach ($fields as $f) {
				$cols[] = $x->{'branch_' . $kind . '_' . $f};
				$vals[] = $id == $bid ? $src[$f] : '0';
			}
		}
	}

	$count = 0;
	foreach (array_chunk(array_values(array_unique(array_map('intval', $item_ids))), 1000) as $chunk) {
		$in = implode(',', $chunk);
		$conn->exec("INSERT INTO tbl_item_stock (item_id) SELECT d.item_id FROM tbl_item_details d
					 WHERE d.item_id IN (" . $in . ") AND NOT EXISTS (SELECT 1 FROM tbl_item_stock x WHERE x.item_id = d.item_id)");
		$ids = $conn->query("SELECT s.item_id FROM tbl_item_stock s WHERE s.item_id IN (" . $in . ") AND " . $changed_sql . " FOR UPDATE")->fetchAll(PDO::FETCH_COLUMN);
		if (!$ids) {
			continue;
		}
		$in = implode(',', array_map('intval', $ids));
		$conn->exec("INSERT INTO tbl_itemprice_history (" . implode(', ', $cols) . ")
					 SELECT " . implode(', ', $vals) . " FROM tbl_item_stock s JOIN tbl_item_details d ON d.item_id = s.item_id WHERE s.item_id IN (" . $in . ")");
		// cost, selling, margin worked out from the old values, discount set last
		$conn->exec("UPDATE tbl_item_stock s SET " . $C . " = " . $nC . ", " . $S . " = " . $nS . ", " . $M . " = " . $nM . ", " . $D . " = " . $nD . " WHERE s.item_id IN (" . $in . ")");
		if ($bid == 1) {
			$conn->exec("UPDATE tbl_item_details d JOIN tbl_item_stock s ON s.item_id = d.item_id SET d.item_price = " . $P . ", d.item_discount = " . $D . ",
						 d.item_cost_price = " . $C . ", d.item_selling_price = " . $S . ", d.margin_percent = " . $M . " WHERE d.item_id IN (" . $in . ")");
		}
		$count += count($ids);
	}
	return $count;
}
