-- ============================================================================
--  Repair inv_value / pro_value zeroed by the edit screen
-- ----------------------------------------------------------------------------
--  The edit branch of dc_invoice.php, quo_invoice.php and quo_proforma.php
--  posted $obj->quo_value, a column those detail tables do not have. It came
--  through as an empty string and saved as 0.00, so any invoice or proforma
--  saved from its edit screen has the Amount column zeroed. Everything else
--  (qty, unit price, vat, tax_value, net_value) is intact.
--
--  The code is fixed. This repairs rows that were already zeroed.
--  Run steps 1 and 2 first and read them before running step 3.
-- ============================================================================

-- ---------------------------------------------------------------------------
-- 1. How many rows are affected
-- ---------------------------------------------------------------------------
SELECT 'tbl_invoice_details' AS tbl, COUNT(*) AS zeroed_rows
  FROM tbl_invoice_details WHERE inv_value = 0 AND net_value > 0
UNION ALL
SELECT 'tbl_proforma_details', COUNT(*)
  FROM tbl_proforma_details WHERE pro_value = 0 AND net_value > 0;

-- ---------------------------------------------------------------------------
-- 2. Preview. Two independent ways to rebuild the value:
--      a) net_value - tax_value        (both columns survived the bug)
--      b) qty * unit_price, less the discount percent
--    Check they agree before writing anything. Rows where they disagree are
--    listed by step 2b and are NOT touched by step 3.
-- ---------------------------------------------------------------------------
SELECT details_id, inv_id, item_id, inv_qty, unit_price, inv_discount, vat,
       inv_value                                   AS current_value,
       ROUND(net_value - tax_value, 2)             AS from_net,
       ROUND(inv_qty * unit_price * (1 - inv_discount / 100), 2) AS from_qty
  FROM tbl_invoice_details
 WHERE inv_value = 0 AND net_value > 0
 ORDER BY inv_id, details_id;

-- 2b. Rows the two methods disagree on (more than 1 rupee apart) - fix by hand
SELECT details_id, inv_id, item_id,
       ROUND(net_value - tax_value, 2)             AS from_net,
       ROUND(inv_qty * unit_price * (1 - inv_discount / 100), 2) AS from_qty
  FROM tbl_invoice_details
 WHERE inv_value = 0 AND net_value > 0
   AND ABS(ROUND(net_value - tax_value, 2)
         - ROUND(inv_qty * unit_price * (1 - inv_discount / 100), 2)) > 1.00;

-- ---------------------------------------------------------------------------
-- 3. Repair. Only rows where both methods agree within 1 rupee.
--    Take a backup of the two tables first.
-- ---------------------------------------------------------------------------
-- CREATE TABLE tbl_invoice_details_bkp_20260930  AS SELECT * FROM tbl_invoice_details;
-- CREATE TABLE tbl_proforma_details_bkp_20260930 AS SELECT * FROM tbl_proforma_details;

UPDATE tbl_invoice_details
   SET inv_value = ROUND(net_value - tax_value, 2)
 WHERE inv_value = 0
   AND net_value > 0
   AND ABS(ROUND(net_value - tax_value, 2)
         - ROUND(inv_qty * unit_price * (1 - inv_discount / 100), 2)) <= 1.00;

UPDATE tbl_proforma_details
   SET pro_value = ROUND(net_value - tax_value, 2)
 WHERE pro_value = 0
   AND net_value > 0
   AND ABS(ROUND(net_value - tax_value, 2)
         - ROUND(pro_qty * unit_price * (1 - pro_discount / 100), 2)) <= 1.00;

-- ---------------------------------------------------------------------------
-- 4. Check - should return no rows
-- ---------------------------------------------------------------------------
SELECT details_id, inv_id, inv_value, net_value, tax_value
  FROM tbl_invoice_details WHERE inv_value = 0 AND net_value > 0;
