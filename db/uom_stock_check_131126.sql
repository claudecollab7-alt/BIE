-- 2026-11-13 items whose stock may be in the wrong unit - for the stores team to recount and correct with Stock Adjustment.
-- Read only (two lists, nothing is changed). Run in phpMyAdmin on the bie database, any time.

-- 1. received on a GRN in another unit than the item's main unit, added to stock unconverted (before 2026-11-13)
SELECT i.item_code, i.item_desciption, u.uom_code AS main_unit, TRIM(g.grn_unit) AS received_in,
       COUNT(*) AS grn_lines, SUM(g.grn_accepted_qty) AS qty_added, MAX(gr.grn_date) AS last_grn,
       s.ho_stock, s.kl_stock, s.rjpm_stock
FROM tbl_grn_details g
JOIN tbl_grn gr ON gr.grn_id = g.grn_id
JOIN tbl_item_details i ON i.item_id = g.grn_item_id
JOIN mst_uom u ON u.uom_id = i.item_uom
LEFT JOIN tbl_item_stock s ON s.stock_id = (SELECT MIN(x.stock_id) FROM tbl_item_stock x WHERE x.item_id = i.item_id)
WHERE g.grn_accepted_qty > 0 AND gr.grn_date < '2026-11-13'
  AND TRIM(g.grn_unit) NOT IN ('', 'NA', u.uom_code, u.uom_name, LEFT(u.uom_name, 4))
GROUP BY i.item_id, TRIM(g.grn_unit)
ORDER BY last_grn DESC;

-- 2. main unit changed on the Item Price screen with stock already there (the old unit was kept in the *_old_item_hsn
--    columns until 2026-11-13 - the two were swapped)
SELECT i.item_code, i.item_desciption, uo.uom_code AS old_unit, un.uom_code AS new_unit, h.created_dtm AS changed_on,
       s.ho_stock, s.kl_stock, s.rjpm_stock
FROM tbl_itemprice_history h
JOIN tbl_item_details i ON i.item_id = h.item_id
JOIN mst_uom uo ON uo.uom_id = COALESCE(NULLIF(h.ho_old_item_hsn, 0), NULLIF(h.kl_old_item_hsn, 0), h.rjpm_old_item_hsn)
JOIN mst_uom un ON un.uom_id = COALESCE(NULLIF(h.ho_new_item_uom, 0), NULLIF(h.kl_new_item_uom, 0), h.rjpm_new_item_uom)
LEFT JOIN tbl_item_stock s ON s.stock_id = (SELECT MIN(x.stock_id) FROM tbl_item_stock x WHERE x.item_id = i.item_id)
WHERE uo.uom_id > 0 AND un.uom_id > 0 AND uo.uom_id <> un.uom_id AND h.created_dtm < '2026-11-13'
ORDER BY h.created_dtm DESC;
