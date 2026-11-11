-- 2026-11-11 price per extra unit (multiple UOM) per branch - used by PO Prepare when the store indent asks in an extra unit.
-- Before: each save added a row to tbl_multiuom_itemprice_history and PO Prepare read the first (oldest) row; with no row
-- the PO line went out at 0 (PO/0269/BIE/KL/2627 - PUT-B-12-8-B indented in MTR, no MTR price).
-- Now one current row per item + unit + branch here; tbl_multiuom_itemprice_history keeps the old / new values of each change.
-- Run once in phpMyAdmin on the bie database, after db/post_upload_031126.sql; safe to run again.

CREATE TABLE IF NOT EXISTS tbl_item_uom_price (
  uom_price_id INT(11) NOT NULL AUTO_INCREMENT,
  item_id INT(11) NOT NULL,
  uom_id INT(11) NOT NULL,
  branch_id INT(11) NOT NULL,
  item_price DECIMAL(12,2) NOT NULL DEFAULT 0,
  item_discount DECIMAL(6,2) NOT NULL DEFAULT 0,
  cost_price DECIMAL(12,2) NOT NULL DEFAULT 0,
  selling_price DECIMAL(12,2) NOT NULL DEFAULT 0,
  margin_percent DECIMAL(10,2) NOT NULL DEFAULT 0,
  created_by INT(11) NOT NULL DEFAULT 0,
  created_dtm DATETIME NULL,
  modify_by INT(11) NOT NULL DEFAULT 0,
  modify_dtm DATETIME NULL,
  PRIMARY KEY (uom_price_id),
  UNIQUE KEY item_uom_branch (item_id, uom_id, branch_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- carry over the latest saved price of each item / extra unit / branch (only a real extra unit of the item, not its main unit)
INSERT IGNORE INTO tbl_item_uom_price (item_id, uom_id, branch_id, item_price, item_discount, cost_price, selling_price, margin_percent, created_by, created_dtm)
SELECT h.item_id, u.uom_id, h.branch_id, h.new_price, h.new_discount, h.new_cost_price, h.new_selling_price, h.new_margin_percent,
       IFNULL(h.created_by, 0), IF(h.created_dtm > '2000-01-01', h.created_dtm, NULL)
FROM tbl_multiuom_itemprice_history h
JOIN (SELECT MAX(details_id) details_id FROM tbl_multiuom_itemprice_history GROUP BY item_id, new_item_uom, branch_id) l ON l.details_id = h.details_id
JOIN mst_uom u ON u.uom_name = h.new_item_uom AND u.uom_id > 0
JOIN tbl_item_details i ON i.item_id = h.item_id AND FIND_IN_SET(u.uom_id, i.multi_uom_id) AND u.uom_id <> i.item_uom
WHERE h.branch_id REGEXP '^[0-9]+$' AND h.branch_id > 0 AND h.new_cost_price > 0;

-- check: the table and how many prices were carried over
SHOW COLUMNS FROM tbl_item_uom_price;
SELECT COUNT(*) AS prices_carried_over FROM tbl_item_uom_price;
