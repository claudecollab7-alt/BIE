-- ============================================================================
--  Run after uploading the files - checked against bie_db_051026.sql
-- ----------------------------------------------------------------------------
--  Already live, nothing to do for these:
--    tbl_stock_flow columns + indexes, tbl_stock_adjustment, menu rows 147/148,
--    cash denominations (2000 off, 1/2/5 added).
--
--  Run the steps in order. Each one prints what it should have done.
-- ============================================================================


-- ---------------------------------------------------------------------------
-- STEP 1  tbl_user_rights.sm_id is TINYINT - it tops out at 127
-- ---------------------------------------------------------------------------
--  The rights rows for the two new menus were inserted as sm_id 147 and 148
--  but the column is a signed TINYINT, so MariaDB clamped both to 127. The
--  menus never appeared for the branch users, and 127 is a real menu
--  (Employee Salary Setting), so the rights screen shows it wrongly ticked.
--  Widen the column first, or step 3 silently does the same thing again.

ALTER TABLE tbl_user_rights
  MODIFY sm_id SMALLINT(6) NOT NULL,
  MODIFY mm_id SMALLINT(6) NOT NULL;

-- check - sm_id and mm_id should both read smallint(6)
SHOW COLUMNS FROM tbl_user_rights;


-- ---------------------------------------------------------------------------
-- STEP 2  remove the six clamped rows
-- ---------------------------------------------------------------------------
--  sm_id 127 belongs to main menu 23, so anything with sm_id 127 under main
--  menu 3 or 20 is a clamped row. auto_id 507 (user 4, mm 23) is genuine and
--  is not touched.

-- look first - expect 6 rows, users 2 / 6 / 7, auto_id 662 to 667
SELECT * FROM tbl_user_rights WHERE sm_id = 127 AND mm_id IN (3, 20);

DELETE FROM tbl_user_rights WHERE sm_id = 127 AND mm_id IN (3, 20);
-- expect: 6 rows deleted


-- ---------------------------------------------------------------------------
-- STEP 3  grant the two new menus to whoever already has the parent menu
-- ---------------------------------------------------------------------------
--  Stock Adjustment  = sm_id 147 under main menu 20 (Stores)
--  Item Stock List   = sm_id 148 under main menu 3  (Item Masters)
--  Admin (usr_type A) sees every menu and needs no row.

INSERT INTO tbl_user_rights (usr_id, mm_id, sm_id)
SELECT DISTINCT usr_id, 20, 147
  FROM (SELECT usr_id FROM tbl_user_rights WHERE mm_id = 20) AS s;
-- expect: 3 rows (users 2, 6, 7)

INSERT INTO tbl_user_rights (usr_id, mm_id, sm_id)
SELECT DISTINCT usr_id, 3, 148
  FROM (SELECT usr_id FROM tbl_user_rights WHERE mm_id = 3) AS s;
-- expect: 3 rows (users 2, 6, 7)

-- check - expect 6 rows, no value showing as 127
SELECT * FROM tbl_user_rights WHERE sm_id IN (147, 148);


-- ---------------------------------------------------------------------------
-- STEP 4  invoice line Amount zeroed by the old edit screen
-- ---------------------------------------------------------------------------
--  111 lines across 35 invoices have inv_value = 0 with a good net_value.
--  net_value is the gross (tax included), so the taxable value is
--  net_value / (1 + vat/100). That matches qty x price less discount on all
--  111 rows, which is why the WHERE below checks both before writing.
--
--  The invoice print recomputes everything from qty, price, discount and the
--  HSN master, so printed invoices were always right - this repairs the
--  stored columns the list and edit screens read. Header totals
--  (tbl_invoice.inv_tot_value) were never affected.

-- 4a. count first - expect 111
SELECT COUNT(*) AS zeroed_rows
  FROM tbl_invoice_details
 WHERE inv_value = 0 AND net_value > 0;

-- 4b. backup before writing
CREATE TABLE tbl_invoice_details_bkp_051026 AS SELECT * FROM tbl_invoice_details;

-- 4c. repair. tax_value is rebuilt in the same statement because it is 0 on
--     106 of the 111 rows. The other 5 (details_id 2183, 2424, 17242, 17246,
--     17725) hold tax worked out on the undiscounted price and are corrected.
UPDATE tbl_invoice_details
   SET tax_value = ROUND(net_value - ROUND(net_value / (1 + vat / 100), 2), 2),
       inv_value = ROUND(net_value / (1 + vat / 100), 2)
 WHERE inv_value = 0
   AND net_value > 0
   AND vat > 0
   AND ABS(ROUND(net_value / (1 + vat / 100), 2)
         - ROUND(inv_qty * unit_price * (1 - inv_discount / 100), 2)) <= 1.00;
-- expect: 111 rows changed

-- 4d. check - should return no rows
SELECT details_id, inv_id, inv_value, tax_value, net_value
  FROM tbl_invoice_details
 WHERE inv_value = 0 AND net_value > 0;

-- 4e. proforma - same bug, no rows affected on this database. Run it anyway.
UPDATE tbl_proforma_details
   SET tax_value = ROUND(net_value - ROUND(net_value / (1 + vat / 100), 2), 2),
       pro_value = ROUND(net_value / (1 + vat / 100), 2)
 WHERE pro_value = 0
   AND net_value > 0
   AND vat > 0
   AND ABS(ROUND(net_value / (1 + vat / 100), 2)
         - ROUND(pro_qty * unit_price * (1 - pro_discount / 100), 2)) <= 1.00;
-- expect: 0 rows changed


-- ---------------------------------------------------------------------------
-- STEP 5  optional - the GST split columns on those same 111 rows
-- ---------------------------------------------------------------------------
--  cgst_per / sgst_per / igst_per and cgst_val / sgst_val / igst_val are all 0
--  on them. Nothing reads these columns (the print works them out from the HSN
--  master), so this is tidying only. Same convention the healthy rows use:
--  cgst and sgst each half, igst the full amount.
--
--  Scoped through the step 4b backup so it touches only the rows step 4 fixed.
--  Another 1945 rows elsewhere in the table have the same columns empty and are
--  deliberately left alone - they were never part of this bug.
--  Skip this step entirely if you would rather not touch them.

UPDATE tbl_invoice_details
   SET cgst_per = ROUND(vat / 2, 2),
       sgst_per = ROUND(vat / 2, 2),
       igst_per = vat,
       cgst_val = ROUND(tax_value / 2, 2),
       sgst_val = ROUND(tax_value / 2, 2),
       igst_val = tax_value
 WHERE tax_value > 0
   AND vat > 0
   AND details_id IN (SELECT details_id
                        FROM tbl_invoice_details_bkp_051026
                       WHERE inv_value = 0 AND net_value > 0);
-- expect: 111 rows changed


-- ---------------------------------------------------------------------------
-- STEP 6  drop the temp tables - only once the uploaded screens are tested
-- ---------------------------------------------------------------------------
--  DC, DC packing, item grouping and the customer branch screen build their
--  rows in the page now. No code refers to these four any more. Three are
--  empty; tbl_package_box_details_temp holds 12 abandoned scratch rows.
--  Test a DC, a DC with packing, an item group and a customer save first.

DROP TABLE IF EXISTS tbl_dc_details_temp;
DROP TABLE IF EXISTS tbl_package_box_details_temp;
DROP TABLE IF EXISTS tbl_item_group_details_temp;
DROP TABLE IF EXISTS mst_customer_branch_temp;


-- ---------------------------------------------------------------------------
-- STEP 7  final check - every line should read OK
-- ---------------------------------------------------------------------------
SELECT 'rights column widened' AS item,
       IF(COLUMN_TYPE LIKE 'smallint%', 'OK', 'NOT DONE') AS status
  FROM information_schema.COLUMNS
 WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tbl_user_rights' AND COLUMN_NAME = 'sm_id'
UNION ALL
SELECT 'new menu rights granted',
       IF(COUNT(*) = 6, 'OK', CONCAT('found ', COUNT(*), ', expected 6'))
  FROM tbl_user_rights WHERE sm_id IN (147, 148)
UNION ALL
SELECT 'clamped rights rows gone',
       IF(COUNT(*) = 0, 'OK', CONCAT(COUNT(*), ' left'))
  FROM tbl_user_rights WHERE sm_id = 127 AND mm_id IN (3, 20)
UNION ALL
SELECT 'invoice amounts repaired',
       IF(COUNT(*) = 0, 'OK', CONCAT(COUNT(*), ' still zero'))
  FROM tbl_invoice_details WHERE inv_value = 0 AND net_value > 0
UNION ALL
SELECT 'temp tables dropped',
       IF(COUNT(*) = 0, 'OK', CONCAT(COUNT(*), ' left'))
  FROM information_schema.TABLES
 WHERE TABLE_SCHEMA = DATABASE()
   AND TABLE_NAME IN ('tbl_dc_details_temp','tbl_package_box_details_temp',
                      'tbl_item_group_details_temp','mst_customer_branch_temp');
