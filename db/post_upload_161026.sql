-- 2026-10-16 Item prices: margin % on cost price everywhere, Bulk Discount / Margin and Item Price Update screens
-- Run once in phpMyAdmin on the bie database before uploading the 16-10-26 files. Safe to run again.

-- 1. menus under Item Masters (admin sees every menu); given to the Item creators in Task Settings (ITEM C) -
--    when someone is made an Item creator later, tick these two menus for them in User Rights too
INSERT INTO mst_sub_menu (mm_id, sm_index, sm_name, sm_url, sm_show, sm_class)
SELECT 3, 12, 'Bulk Discount / Margin', 'item_bulk_discount.php', 1, ''
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM mst_sub_menu WHERE sm_url = 'item_bulk_discount.php');

INSERT INTO mst_sub_menu (mm_id, sm_index, sm_name, sm_url, sm_show, sm_class)
SELECT 3, 13, 'Item Price Update', 'item_price_list.php', 1, ''
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM mst_sub_menu WHERE sm_url = 'item_price_list.php');

INSERT INTO tbl_user_rights (usr_id, mm_id, sm_id)
SELECT DISTINCT a.usr_id, s.mm_id, s.sm_id
FROM tbl_task_assign a
JOIN mst_task t ON t.task_id = a.task_id AND t.task_code = 'ITEM' AND a.task_role = 'C'
JOIN mst_sub_menu s ON s.sm_url IN ('item_bulk_discount.php', 'item_price_list.php')
WHERE NOT EXISTS (SELECT 1 FROM tbl_user_rights x WHERE x.usr_id = a.usr_id AND x.sm_id = s.sm_id);

-- 2. indexes on item_id - every price / stock lookup by item scanned the whole table
CREATE INDEX IF NOT EXISTS item_id ON tbl_item_stock (item_id);
CREATE INDEX IF NOT EXISTS item_branch ON tbl_itemprice_history (item_id, branch_id);

-- 3. stored margin % worked out again on cost price: (selling - cost) / cost x 100
--    prices are not changed; about 2,800 items held 0 and some held margin on item price. No cost or no selling price = 0.
UPDATE tbl_item_stock SET ho_item_margin = IF(ho_item_cost_price > 0 AND ho_item_selling_price > 0, ROUND((ho_item_selling_price - ho_item_cost_price) / ho_item_cost_price * 100, 2), 0)
WHERE ho_item_margin <> IF(ho_item_cost_price > 0 AND ho_item_selling_price > 0, ROUND((ho_item_selling_price - ho_item_cost_price) / ho_item_cost_price * 100, 2), 0);

UPDATE tbl_item_stock SET kl_item_margin = IF(kl_item_cost_price > 0 AND kl_item_selling_price > 0, ROUND((kl_item_selling_price - kl_item_cost_price) / kl_item_cost_price * 100, 2), 0)
WHERE kl_item_margin <> IF(kl_item_cost_price > 0 AND kl_item_selling_price > 0, ROUND((kl_item_selling_price - kl_item_cost_price) / kl_item_cost_price * 100, 2), 0);

UPDATE tbl_item_stock SET rjpm_item_margin = IF(rjpm_item_cost_price > 0 AND rjpm_item_selling_price > 0, ROUND((rjpm_item_selling_price - rjpm_item_cost_price) / rjpm_item_cost_price * 100, 2), 0)
WHERE rjpm_item_margin <> IF(rjpm_item_cost_price > 0 AND rjpm_item_selling_price > 0, ROUND((rjpm_item_selling_price - rjpm_item_cost_price) / rjpm_item_cost_price * 100, 2), 0);

UPDATE tbl_item_details SET margin_percent = IF(item_cost_price > 0 AND item_selling_price > 0, ROUND((item_selling_price - item_cost_price) / item_cost_price * 100, 2), 0)
WHERE margin_percent <> IF(item_cost_price > 0 AND item_selling_price > 0, ROUND((item_selling_price - item_cost_price) / item_cost_price * 100, 2), 0);

-- check: both should be 0
SELECT
  (SELECT COUNT(*) FROM tbl_item_stock WHERE ho_item_cost_price > 0 AND ho_item_selling_price > 0
     AND ho_item_margin <> ROUND((ho_item_selling_price - ho_item_cost_price) / ho_item_cost_price * 100, 2)) AS ho_margin_not_on_cost,
  (SELECT COUNT(*) FROM tbl_item_details WHERE item_cost_price > 0 AND item_selling_price > 0
     AND margin_percent <> ROUND((item_selling_price - item_cost_price) / item_cost_price * 100, 2)) AS item_margin_not_on_cost;
