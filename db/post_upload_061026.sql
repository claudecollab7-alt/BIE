-- ============================================================================
--  Run on the server - checked against bie_db_061026.sql
-- ----------------------------------------------------------------------------
--  Every change from bie_db_051026 onwards is already live: stock ledger
--  columns, tbl_stock_adjustment, menus 147/148 and their rights, cash
--  denominations, invoice amounts, temp tables dropped. Only this is missing.
--  Safe to run twice.
-- ============================================================================


-- ---------------------------------------------------------------------------
-- STEP 1  mst_principal has no audit columns
-- ---------------------------------------------------------------------------
--  mst_principal.php writes created_by / created_dtm on save and modify_by /
--  modify_dtm on update, but the table never had them, so both fail with
--  "Unknown column 'modify_by'". Broken since the first upload, not a recent
--  change. Same types as the other masters (mst_brand, mst_category); the
--  existing six rows get 0 / NULL.

ALTER TABLE mst_principal
  ADD COLUMN IF NOT EXISTS created_by  SMALLINT(6) NOT NULL DEFAULT 0,
  ADD COLUMN IF NOT EXISTS created_dtm DATETIME NULL,
  ADD COLUMN IF NOT EXISTS modify_by   SMALLINT(6) NOT NULL DEFAULT 0,
  ADD COLUMN IF NOT EXISTS modify_dtm  DATETIME NULL;

-- check - expect the four columns listed
SHOW COLUMNS FROM mst_principal;
