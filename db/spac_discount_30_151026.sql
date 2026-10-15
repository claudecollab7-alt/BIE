-- 2026-10-15 SPAC (Shree Prayag Air Controls Pvt Ltd) items: item discount 28% -> 30%
-- Run once in phpMyAdmin on the bie database. Safe to run again - only rows still at 28% are touched.
--
-- Per branch (Head Office, Kerala, Rajapalayam) in tbl_item_stock, and the copy in tbl_item_details:
--   discount 30, cost price = item price less 30%, margin % = (selling price - cost price) / cost price * 100
--   item price and selling price stay as they are; min / max discount, stock and uom not touched
-- One tbl_itemprice_history row per item per branch (old and new values), as the Item Price screen writes.
-- Quotations, sales orders, POs and invoices already made keep their own line prices.

SET @principal = (SELECT MIN(principal_id) FROM mst_principal WHERE principal_code = 'SPAC');

-- before: how many rows will change (run on its own first if you want to check)
SELECT
  (SELECT COUNT(*) FROM tbl_item_details WHERE item_principal = @principal) AS spac_items,
  (SELECT COUNT(*) FROM tbl_item_details WHERE item_principal = @principal AND item_discount = 28) AS items_at_28,
  (SELECT COUNT(*) FROM tbl_item_stock s JOIN tbl_item_details d ON d.item_id = s.item_id WHERE d.item_principal = @principal AND s.ho_item_discount = 28) AS ho_at_28,
  (SELECT COUNT(*) FROM tbl_item_stock s JOIN tbl_item_details d ON d.item_id = s.item_id WHERE d.item_principal = @principal AND s.kl_item_discount = 28) AS kl_at_28,
  (SELECT COUNT(*) FROM tbl_item_stock s JOIN tbl_item_details d ON d.item_id = s.item_id WHERE d.item_principal = @principal AND s.rjpm_item_discount = 28) AS rjpm_at_28;

START TRANSACTION;

-- price history, HO (branch 1)
INSERT INTO tbl_itemprice_history (item_id, branch_id, created_dtm, created_by,
  ho_new_price, ho_new_discount, ho_new_min_discount, ho_new_max_discount, ho_new_cost_price, ho_new_selling_price, ho_new_item_margin, ho_new_msq, ho_new_maq, ho_new_moq, ho_new_item_curr_stock, ho_new_item_uom, ho_new_item_hsn,
  ho_old_price, ho_old_discount, ho_old_min_discount, ho_old_max_discount, ho_old_cost_price, ho_old_selling_price, ho_old_item_margin, ho_old_msq, ho_old_maq, ho_old_moq, ho_old_item_curr_stock, ho_old_item_uom, ho_old_item_hsn,
  kl_new_price, kl_new_discount, kl_new_min_discount, kl_new_max_discount, kl_new_cost_price, kl_new_selling_price, kl_new_item_margin, kl_new_msq, kl_new_maq, kl_new_moq, kl_new_item_curr_stock, kl_new_item_uom, kl_new_item_hsn,
  kl_old_price, kl_old_discount, kl_old_min_discount, kl_old_max_discount, kl_old_cost_price, kl_old_selling_price, kl_old_item_margin, kl_old_msq, kl_old_maq, kl_old_moq, kl_old_item_curr_stock, kl_old_item_uom, kl_old_item_hsn,
  rjpm_new_price, rjpm_new_discount, rjpm_new_min_discount, rjpm_new_max_discount, rjpm_new_cost_price, rjpm_new_selling_price, rjpm_new_item_margin, rjpm_new_msq, rjpm_new_maq, rjpm_new_moq, rjpm_new_item_curr_stock, rjpm_new_item_uom, rjpm_new_item_hsn,
  rjpm_old_price, rjpm_old_discount, rjpm_old_min_discount, rjpm_old_max_discount, rjpm_old_cost_price, rjpm_old_selling_price, rjpm_old_item_margin, rjpm_old_msq, rjpm_old_maq, rjpm_old_moq, rjpm_old_item_curr_stock, rjpm_old_item_uom, rjpm_old_item_hsn)
SELECT s.item_id, 1, NOW(), 1,
  s.ho_item_price, 30, s.ho_item_min_discount, s.ho_item_max_discount, ROUND(s.ho_item_price - s.ho_item_price * 30 / 100, 2), s.ho_item_selling_price, IF(ROUND(s.ho_item_price - s.ho_item_price * 30 / 100, 2) = 0, 0, ROUND((s.ho_item_selling_price - ROUND(s.ho_item_price - s.ho_item_price * 30 / 100, 2)) / ROUND(s.ho_item_price - s.ho_item_price * 30 / 100, 2) * 100, 2)), s.ho_item_min_qty, s.ho_item_order_min_qty, s.ho_item_max_qty, s.ho_stock, IFNULL(d.item_uom, 0), IFNULL(d.item_hsn, 0),
  s.ho_item_price, s.ho_item_discount, s.ho_item_min_discount, s.ho_item_max_discount, s.ho_item_cost_price, s.ho_item_selling_price, s.ho_item_margin, s.ho_item_min_qty, s.ho_item_order_min_qty, s.ho_item_max_qty, s.ho_stock, IFNULL(d.item_uom, 0), IFNULL(d.item_hsn, 0),
  0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0,
  0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0,
  0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0,
  0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0
FROM tbl_item_stock s JOIN tbl_item_details d ON d.item_id = s.item_id
WHERE d.item_principal = @principal AND s.ho_item_discount = 28;

-- price history, KL (branch 2)
INSERT INTO tbl_itemprice_history (item_id, branch_id, created_dtm, created_by,
  ho_new_price, ho_new_discount, ho_new_min_discount, ho_new_max_discount, ho_new_cost_price, ho_new_selling_price, ho_new_item_margin, ho_new_msq, ho_new_maq, ho_new_moq, ho_new_item_curr_stock, ho_new_item_uom, ho_new_item_hsn,
  ho_old_price, ho_old_discount, ho_old_min_discount, ho_old_max_discount, ho_old_cost_price, ho_old_selling_price, ho_old_item_margin, ho_old_msq, ho_old_maq, ho_old_moq, ho_old_item_curr_stock, ho_old_item_uom, ho_old_item_hsn,
  kl_new_price, kl_new_discount, kl_new_min_discount, kl_new_max_discount, kl_new_cost_price, kl_new_selling_price, kl_new_item_margin, kl_new_msq, kl_new_maq, kl_new_moq, kl_new_item_curr_stock, kl_new_item_uom, kl_new_item_hsn,
  kl_old_price, kl_old_discount, kl_old_min_discount, kl_old_max_discount, kl_old_cost_price, kl_old_selling_price, kl_old_item_margin, kl_old_msq, kl_old_maq, kl_old_moq, kl_old_item_curr_stock, kl_old_item_uom, kl_old_item_hsn,
  rjpm_new_price, rjpm_new_discount, rjpm_new_min_discount, rjpm_new_max_discount, rjpm_new_cost_price, rjpm_new_selling_price, rjpm_new_item_margin, rjpm_new_msq, rjpm_new_maq, rjpm_new_moq, rjpm_new_item_curr_stock, rjpm_new_item_uom, rjpm_new_item_hsn,
  rjpm_old_price, rjpm_old_discount, rjpm_old_min_discount, rjpm_old_max_discount, rjpm_old_cost_price, rjpm_old_selling_price, rjpm_old_item_margin, rjpm_old_msq, rjpm_old_maq, rjpm_old_moq, rjpm_old_item_curr_stock, rjpm_old_item_uom, rjpm_old_item_hsn)
SELECT s.item_id, 2, NOW(), 1,
  0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0,
  0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0,
  s.kl_item_price, 30, s.kl_item_min_discount, s.kl_item_max_discount, ROUND(s.kl_item_price - s.kl_item_price * 30 / 100, 2), s.kl_item_selling_price, IF(ROUND(s.kl_item_price - s.kl_item_price * 30 / 100, 2) = 0, 0, ROUND((s.kl_item_selling_price - ROUND(s.kl_item_price - s.kl_item_price * 30 / 100, 2)) / ROUND(s.kl_item_price - s.kl_item_price * 30 / 100, 2) * 100, 2)), s.kl_item_min_qty, s.kl_item_order_min_qty, s.kl_item_max_qty, s.kl_stock, IFNULL(d.item_uom, 0), IFNULL(d.item_hsn, 0),
  s.kl_item_price, s.kl_item_discount, s.kl_item_min_discount, s.kl_item_max_discount, s.kl_item_cost_price, s.kl_item_selling_price, s.kl_item_margin, s.kl_item_min_qty, s.kl_item_order_min_qty, s.kl_item_max_qty, s.kl_stock, IFNULL(d.item_uom, 0), IFNULL(d.item_hsn, 0),
  0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0,
  0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0
FROM tbl_item_stock s JOIN tbl_item_details d ON d.item_id = s.item_id
WHERE d.item_principal = @principal AND s.kl_item_discount = 28;

-- price history, RJPM (branch 3)
INSERT INTO tbl_itemprice_history (item_id, branch_id, created_dtm, created_by,
  ho_new_price, ho_new_discount, ho_new_min_discount, ho_new_max_discount, ho_new_cost_price, ho_new_selling_price, ho_new_item_margin, ho_new_msq, ho_new_maq, ho_new_moq, ho_new_item_curr_stock, ho_new_item_uom, ho_new_item_hsn,
  ho_old_price, ho_old_discount, ho_old_min_discount, ho_old_max_discount, ho_old_cost_price, ho_old_selling_price, ho_old_item_margin, ho_old_msq, ho_old_maq, ho_old_moq, ho_old_item_curr_stock, ho_old_item_uom, ho_old_item_hsn,
  kl_new_price, kl_new_discount, kl_new_min_discount, kl_new_max_discount, kl_new_cost_price, kl_new_selling_price, kl_new_item_margin, kl_new_msq, kl_new_maq, kl_new_moq, kl_new_item_curr_stock, kl_new_item_uom, kl_new_item_hsn,
  kl_old_price, kl_old_discount, kl_old_min_discount, kl_old_max_discount, kl_old_cost_price, kl_old_selling_price, kl_old_item_margin, kl_old_msq, kl_old_maq, kl_old_moq, kl_old_item_curr_stock, kl_old_item_uom, kl_old_item_hsn,
  rjpm_new_price, rjpm_new_discount, rjpm_new_min_discount, rjpm_new_max_discount, rjpm_new_cost_price, rjpm_new_selling_price, rjpm_new_item_margin, rjpm_new_msq, rjpm_new_maq, rjpm_new_moq, rjpm_new_item_curr_stock, rjpm_new_item_uom, rjpm_new_item_hsn,
  rjpm_old_price, rjpm_old_discount, rjpm_old_min_discount, rjpm_old_max_discount, rjpm_old_cost_price, rjpm_old_selling_price, rjpm_old_item_margin, rjpm_old_msq, rjpm_old_maq, rjpm_old_moq, rjpm_old_item_curr_stock, rjpm_old_item_uom, rjpm_old_item_hsn)
SELECT s.item_id, 3, NOW(), 1,
  0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0,
  0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0,
  0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0,
  0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0,
  s.rjpm_item_price, 30, s.rjpm_item_min_discount, s.rjpm_item_max_discount, ROUND(s.rjpm_item_price - s.rjpm_item_price * 30 / 100, 2), s.rjpm_item_selling_price, IF(ROUND(s.rjpm_item_price - s.rjpm_item_price * 30 / 100, 2) = 0, 0, ROUND((s.rjpm_item_selling_price - ROUND(s.rjpm_item_price - s.rjpm_item_price * 30 / 100, 2)) / ROUND(s.rjpm_item_price - s.rjpm_item_price * 30 / 100, 2) * 100, 2)), s.rjpm_item_min_qty, s.rjpm_item_order_min_qty, s.rjpm_item_max_qty, s.rjpm_stock, IFNULL(d.item_uom, 0), IFNULL(d.item_hsn, 0),
  s.rjpm_item_price, s.rjpm_item_discount, s.rjpm_item_min_discount, s.rjpm_item_max_discount, s.rjpm_item_cost_price, s.rjpm_item_selling_price, s.rjpm_item_margin, s.rjpm_item_min_qty, s.rjpm_item_order_min_qty, s.rjpm_item_max_qty, s.rjpm_stock, IFNULL(d.item_uom, 0), IFNULL(d.item_hsn, 0)
FROM tbl_item_stock s JOIN tbl_item_details d ON d.item_id = s.item_id
WHERE d.item_principal = @principal AND s.rjpm_item_discount = 28;

-- branch prices, HO (cost first, margin from the new cost)
UPDATE tbl_item_stock s JOIN tbl_item_details d ON d.item_id = s.item_id
SET s.ho_item_cost_price = ROUND(s.ho_item_price - s.ho_item_price * 30 / 100, 2),
    s.ho_item_margin = IF(ROUND(s.ho_item_price - s.ho_item_price * 30 / 100, 2) = 0, 0, ROUND((s.ho_item_selling_price - ROUND(s.ho_item_price - s.ho_item_price * 30 / 100, 2)) / ROUND(s.ho_item_price - s.ho_item_price * 30 / 100, 2) * 100, 2)),
    s.ho_item_discount = 30
WHERE d.item_principal = @principal AND s.ho_item_discount = 28;

-- branch prices, KL (cost first, margin from the new cost)
UPDATE tbl_item_stock s JOIN tbl_item_details d ON d.item_id = s.item_id
SET s.kl_item_cost_price = ROUND(s.kl_item_price - s.kl_item_price * 30 / 100, 2),
    s.kl_item_margin = IF(ROUND(s.kl_item_price - s.kl_item_price * 30 / 100, 2) = 0, 0, ROUND((s.kl_item_selling_price - ROUND(s.kl_item_price - s.kl_item_price * 30 / 100, 2)) / ROUND(s.kl_item_price - s.kl_item_price * 30 / 100, 2) * 100, 2)),
    s.kl_item_discount = 30
WHERE d.item_principal = @principal AND s.kl_item_discount = 28;

-- branch prices, RJPM (cost first, margin from the new cost)
UPDATE tbl_item_stock s JOIN tbl_item_details d ON d.item_id = s.item_id
SET s.rjpm_item_cost_price = ROUND(s.rjpm_item_price - s.rjpm_item_price * 30 / 100, 2),
    s.rjpm_item_margin = IF(ROUND(s.rjpm_item_price - s.rjpm_item_price * 30 / 100, 2) = 0, 0, ROUND((s.rjpm_item_selling_price - ROUND(s.rjpm_item_price - s.rjpm_item_price * 30 / 100, 2)) / ROUND(s.rjpm_item_price - s.rjpm_item_price * 30 / 100, 2) * 100, 2)),
    s.rjpm_item_discount = 30
WHERE d.item_principal = @principal AND s.rjpm_item_discount = 28;

-- item master copy
UPDATE tbl_item_details
SET item_cost_price = ROUND(item_price - item_price * 30 / 100, 2),
    margin_percent = IF(ROUND(item_price - item_price * 30 / 100, 2) = 0, 0, ROUND((item_selling_price - ROUND(item_price - item_price * 30 / 100, 2)) / ROUND(item_price - item_price * 30 / 100, 2) * 100, 2)),
    item_discount = 30
WHERE item_principal = @principal AND item_discount = 28;

COMMIT;

-- after: all should be 0 at 28, and cost / margin should match the formula (mismatch = 0)
SELECT
  (SELECT COUNT(*) FROM tbl_item_details WHERE item_principal = @principal AND item_discount = 28) AS items_still_28,
  (SELECT COUNT(*) FROM tbl_item_details WHERE item_principal = @principal AND item_discount = 30) AS items_at_30,
  (SELECT COUNT(*) FROM tbl_item_stock s JOIN tbl_item_details d ON d.item_id = s.item_id WHERE d.item_principal = @principal
     AND (s.ho_item_discount = 28 OR s.kl_item_discount = 28 OR s.rjpm_item_discount = 28)) AS branch_rows_still_28,
  (SELECT COUNT(*) FROM tbl_item_stock s JOIN tbl_item_details d ON d.item_id = s.item_id WHERE d.item_principal = @principal
     AND s.ho_item_discount = 30 AND s.ho_item_cost_price <> ROUND(s.ho_item_price - s.ho_item_price * 30 / 100, 2)) AS ho_cost_mismatch;
