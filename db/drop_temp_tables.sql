-- ============================================================================
--  Drop the four temp tables
-- ----------------------------------------------------------------------------
--  Form rows now live in the page and post back as arrays, so nothing reads or
--  writes these any more. Run this only after the four screens have been tested:
--
--    mst_customer_new.php     customer branches
--    mst_item_grouping.php    item group items
--    dc_add.php               DC item rows and packing boxes
--
--  Check first - every count should be a leftover from before the change, and
--  none of it is used by the application now:
--    SELECT COUNT(*) FROM mst_customer_branch_temp;
--    SELECT COUNT(*) FROM tbl_item_group_details_temp;
--    SELECT COUNT(*) FROM tbl_dc_details_temp;
--    SELECT COUNT(*) FROM tbl_package_box_details_temp;
--
--  Keep a backup until you are satisfied:
--    CREATE TABLE zz_bkp_package_box_details_temp AS SELECT * FROM tbl_package_box_details_temp;
-- ============================================================================

DROP TABLE IF EXISTS `mst_customer_branch_temp`;
DROP TABLE IF EXISTS `tbl_item_group_details_temp`;
DROP TABLE IF EXISTS `tbl_dc_details_temp`;
DROP TABLE IF EXISTS `tbl_package_box_details_temp`;
