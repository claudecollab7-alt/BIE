-- 2026-11-24 SO/0110/BIE/KL/2627 (so_id 646) and quotations 365/BIE/KL/C1146, 362-A/BIE/KL/C1365, 340-A/BIE/KL/C1393
-- (quo_id 1375 - 1377) hold item 171 EG-VRLA-BC-2. Item 171 was deleted from the item list after those were made
-- (active on 28-10, deleted by 11-11); its live copy with the same code and name is item 6627, which has the stock.
-- On the DC the line showed "-" with stock 0. This moves those lines (and a DC of SO 646, if one was saved since) to
-- item 6627. Only rows still on 171 are changed; safe to run again. Run once in phpMyAdmin on the bie database.

SET @bie_user_id = 1;

-- only when 6627 is the active item with the same code as 171
SET @ok = (SELECT COUNT(*) FROM tbl_item_details a JOIN tbl_item_details b ON b.item_code = a.item_code
           WHERE a.item_id = 171 AND b.item_id = 6627 AND b.item_status = 1);

UPDATE tbl_sales_order_details SET item_id = 6627 WHERE so_id = 646 AND item_id = 171 AND @ok = 1;
UPDATE tbl_quotation_details SET item_id = 6627 WHERE quo_id IN (1375, 1376, 1377) AND item_id = 171 AND @ok = 1;
UPDATE tbl_dc_details d JOIN tbl_dc c ON c.dc_id = d.dc_id
SET d.dc_item_id = 6627 WHERE c.so_id = 646 AND d.dc_item_id = 171 AND @ok = 1;

SELECT 'SO 646' AS doc, item_id FROM tbl_sales_order_details WHERE so_id = 646
UNION ALL SELECT CONCAT('quotation ', quo_id), item_id FROM tbl_quotation_details WHERE quo_id IN (1375, 1376, 1377)
UNION ALL SELECT CONCAT('stock of 6627 HO / KL'), CONCAT(ho_stock, ' / ', kl_stock) FROM tbl_item_stock WHERE item_id = 6627;
