-- ============================================================================
--  Cash denominations - drop 2000, add 1 / 2 / 5
-- ----------------------------------------------------------------------------
--  Two tables hold the same denomination list:
--    tbl_cash_details  - Sales Receipt (pay_receipt.php)
--    mst_cash_details  - Invoice and Credit screens
--  Both are updated so the two screens stay in step.
--
--  2000 is deactivated, NOT deleted. Old receipts store cash_id 7 and the
--  screens that display them look the name up without checking cash_status,
--  so history keeps printing correctly. Deleting the row would blank it out.
--
--  Safe to re-run.
-- ============================================================================

-- ---------------------------------------------------------------------------
-- 1. Retire 2000
-- ---------------------------------------------------------------------------
UPDATE `tbl_cash_details` SET `cash_status` = 0 WHERE `cash_name` = 2000.00;
UPDATE `mst_cash_details` SET `cash_status` = 0 WHERE `cash_name` = 2000.00;

-- ---------------------------------------------------------------------------
-- 2. Add 1, 2 and 5
-- ---------------------------------------------------------------------------
INSERT INTO `tbl_cash_details` (`cash_id`, `cash_name`, `cash_status`)
SELECT 8, 1.00, 1 FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `tbl_cash_details` WHERE `cash_name` = 1.00);
INSERT INTO `tbl_cash_details` (`cash_id`, `cash_name`, `cash_status`)
SELECT 9, 2.00, 1 FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `tbl_cash_details` WHERE `cash_name` = 2.00);
INSERT INTO `tbl_cash_details` (`cash_id`, `cash_name`, `cash_status`)
SELECT 10, 5.00, 1 FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `tbl_cash_details` WHERE `cash_name` = 5.00);

INSERT INTO `mst_cash_details` (`cash_id`, `cash_name`, `cash_status`)
SELECT 8, 1.00, 1 FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `mst_cash_details` WHERE `cash_name` = 1.00);
INSERT INTO `mst_cash_details` (`cash_id`, `cash_name`, `cash_status`)
SELECT 9, 2.00, 1 FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `mst_cash_details` WHERE `cash_name` = 2.00);
INSERT INTO `mst_cash_details` (`cash_id`, `cash_name`, `cash_status`)
SELECT 10, 5.00, 1 FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `mst_cash_details` WHERE `cash_name` = 5.00);

-- ---------------------------------------------------------------------------
-- 3. Check
-- ---------------------------------------------------------------------------
-- Expected active list, both tables: 1, 2, 5, 10, 20, 50, 100, 200, 500
--   SELECT cash_id, cash_name, cash_status FROM tbl_cash_details ORDER BY cash_name;
--   SELECT cash_id, cash_name, cash_status FROM mst_cash_details ORDER BY cash_name;
