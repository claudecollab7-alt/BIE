-- 2026-11-11 created by / created dtm / modified by / modified dtm on every document and master table the forms save to.
-- Adds the columns a table does not have yet (created_by, created_dtm, modify_by, modify_dtm), fills created_by / created_dtm
-- of the existing rows from what the table already keeps (modify_by + modify_date_time and the like - on these tables they are
-- written when the record is made), then adds two triggers per table so every form fills them with no code change:
--   on insert: created_by = the logged-in user (the app sets @bie_user_id on its connection), created_dtm = now (India time, as the app writes its times);
--   on update: modify_by = the logged-in user, modify_dtm = now; created_by / created_dtm are kept as they were.
-- A value a form writes itself is kept (insert: a created_by above 0; update with no logged-in user: its modify_by).
-- Columns the code already uses (modify_date_time, update_by ...) are left as they are.
-- Detail rows (item lines of a document) are not included: they belong to their document and are read together with it.
-- Run once in phpMyAdmin on the bie database, after db/post_upload_111126.sql; safe to run again.

-- the triggers are dropped first so filling the old rows below does not stamp them as modified now
DROP TRIGGER IF EXISTS audit_mst_branch_bi;
DROP TRIGGER IF EXISTS audit_mst_branch_bu;
DROP TRIGGER IF EXISTS audit_mst_brand_bi;
DROP TRIGGER IF EXISTS audit_mst_brand_bu;
DROP TRIGGER IF EXISTS audit_mst_category_bi;
DROP TRIGGER IF EXISTS audit_mst_category_bu;
DROP TRIGGER IF EXISTS audit_mst_city_bi;
DROP TRIGGER IF EXISTS audit_mst_city_bu;
DROP TRIGGER IF EXISTS audit_mst_color_bi;
DROP TRIGGER IF EXISTS audit_mst_color_bu;
DROP TRIGGER IF EXISTS audit_mst_customer_branch_bi;
DROP TRIGGER IF EXISTS audit_mst_customer_branch_bu;
DROP TRIGGER IF EXISTS audit_mst_department_bi;
DROP TRIGGER IF EXISTS audit_mst_department_bu;
DROP TRIGGER IF EXISTS audit_mst_designation_bi;
DROP TRIGGER IF EXISTS audit_mst_designation_bu;
DROP TRIGGER IF EXISTS audit_mst_district_bi;
DROP TRIGGER IF EXISTS audit_mst_district_bu;
DROP TRIGGER IF EXISTS audit_mst_division_bi;
DROP TRIGGER IF EXISTS audit_mst_division_bu;
DROP TRIGGER IF EXISTS audit_mst_employee_bi;
DROP TRIGGER IF EXISTS audit_mst_employee_bu;
DROP TRIGGER IF EXISTS audit_mst_employee_certificate_bi;
DROP TRIGGER IF EXISTS audit_mst_employee_certificate_bu;
DROP TRIGGER IF EXISTS audit_mst_hsn_bi;
DROP TRIGGER IF EXISTS audit_mst_hsn_bu;
DROP TRIGGER IF EXISTS audit_mst_labour_bi;
DROP TRIGGER IF EXISTS audit_mst_labour_bu;
DROP TRIGGER IF EXISTS audit_mst_ledger_bi;
DROP TRIGGER IF EXISTS audit_mst_ledger_bu;
DROP TRIGGER IF EXISTS audit_mst_principal_bi;
DROP TRIGGER IF EXISTS audit_mst_principal_bu;
DROP TRIGGER IF EXISTS audit_mst_salary_setting_bi;
DROP TRIGGER IF EXISTS audit_mst_salary_setting_bu;
DROP TRIGGER IF EXISTS audit_mst_shifts_bi;
DROP TRIGGER IF EXISTS audit_mst_shifts_bu;
DROP TRIGGER IF EXISTS audit_mst_state_bi;
DROP TRIGGER IF EXISTS audit_mst_state_bu;
DROP TRIGGER IF EXISTS audit_mst_supplier_new_bi;
DROP TRIGGER IF EXISTS audit_mst_supplier_new_bu;
DROP TRIGGER IF EXISTS audit_mst_task_bi;
DROP TRIGGER IF EXISTS audit_mst_task_bu;
DROP TRIGGER IF EXISTS audit_mst_uom_bi;
DROP TRIGGER IF EXISTS audit_mst_uom_bu;
DROP TRIGGER IF EXISTS audit_tbl_user_bi;
DROP TRIGGER IF EXISTS audit_tbl_user_bu;
DROP TRIGGER IF EXISTS audit_tbl_item_details_bi;
DROP TRIGGER IF EXISTS audit_tbl_item_details_bu;
DROP TRIGGER IF EXISTS audit_tbl_item_group_bi;
DROP TRIGGER IF EXISTS audit_tbl_item_group_bu;
DROP TRIGGER IF EXISTS audit_tbl_item_uom_price_bi;
DROP TRIGGER IF EXISTS audit_tbl_item_uom_price_bu;
DROP TRIGGER IF EXISTS audit_tbl_supp_items_bi;
DROP TRIGGER IF EXISTS audit_tbl_supp_items_bu;
DROP TRIGGER IF EXISTS audit_tbl_asset_details_bi;
DROP TRIGGER IF EXISTS audit_tbl_asset_details_bu;
DROP TRIGGER IF EXISTS audit_tbl_month_master_bi;
DROP TRIGGER IF EXISTS audit_tbl_month_master_bu;
DROP TRIGGER IF EXISTS audit_tbl_quotation_bi;
DROP TRIGGER IF EXISTS audit_tbl_quotation_bu;
DROP TRIGGER IF EXISTS audit_tbl_sales_order_bi;
DROP TRIGGER IF EXISTS audit_tbl_sales_order_bu;
DROP TRIGGER IF EXISTS audit_tbl_dc_bi;
DROP TRIGGER IF EXISTS audit_tbl_dc_bu;
DROP TRIGGER IF EXISTS audit_tbl_dc_package_box_bi;
DROP TRIGGER IF EXISTS audit_tbl_dc_package_box_bu;
DROP TRIGGER IF EXISTS audit_tbl_proforma_bi;
DROP TRIGGER IF EXISTS audit_tbl_proforma_bu;
DROP TRIGGER IF EXISTS audit_tbl_invoice_bi;
DROP TRIGGER IF EXISTS audit_tbl_invoice_bu;
DROP TRIGGER IF EXISTS audit_tbl_receipt_bi;
DROP TRIGGER IF EXISTS audit_tbl_receipt_bu;
DROP TRIGGER IF EXISTS audit_tbl_sales_repair_bi;
DROP TRIGGER IF EXISTS audit_tbl_sales_repair_bu;
DROP TRIGGER IF EXISTS audit_tbl_spare_mapping_bi;
DROP TRIGGER IF EXISTS audit_tbl_spare_mapping_bu;
DROP TRIGGER IF EXISTS audit_tbl_store_indent_bi;
DROP TRIGGER IF EXISTS audit_tbl_store_indent_bu;
DROP TRIGGER IF EXISTS audit_tbl_po_prepare_bi;
DROP TRIGGER IF EXISTS audit_tbl_po_prepare_bu;
DROP TRIGGER IF EXISTS audit_tbl_purchase_order_bi;
DROP TRIGGER IF EXISTS audit_tbl_purchase_order_bu;
DROP TRIGGER IF EXISTS audit_tbl_grn_bi;
DROP TRIGGER IF EXISTS audit_tbl_grn_bu;
DROP TRIGGER IF EXISTS audit_tbl_grn_pay_receipt_bi;
DROP TRIGGER IF EXISTS audit_tbl_grn_pay_receipt_bu;
DROP TRIGGER IF EXISTS audit_tbl_purchase_return_bi;
DROP TRIGGER IF EXISTS audit_tbl_purchase_return_bu;
DROP TRIGGER IF EXISTS audit_tbl_stock_adjustment_bi;
DROP TRIGGER IF EXISTS audit_tbl_stock_adjustment_bu;
DROP TRIGGER IF EXISTS audit_tbl_accounts_bi;
DROP TRIGGER IF EXISTS audit_tbl_accounts_bu;
DROP TRIGGER IF EXISTS audit_tbl_attendance_bi;
DROP TRIGGER IF EXISTS audit_tbl_attendance_bu;
DROP TRIGGER IF EXISTS audit_tbl_emp_advance_bi;
DROP TRIGGER IF EXISTS audit_tbl_emp_advance_bu;
DROP TRIGGER IF EXISTS audit_tbl_emp_advance_return_payment_bi;
DROP TRIGGER IF EXISTS audit_tbl_emp_advance_return_payment_bu;
DROP TRIGGER IF EXISTS audit_tbl_emp_debit_note_bi;
DROP TRIGGER IF EXISTS audit_tbl_emp_debit_note_bu;
DROP TRIGGER IF EXISTS audit_tbl_emp_debit_note_return_payment_bi;
DROP TRIGGER IF EXISTS audit_tbl_emp_debit_note_return_payment_bu;
DROP TRIGGER IF EXISTS audit_tbl_emp_salary_bi;
DROP TRIGGER IF EXISTS audit_tbl_emp_salary_bu;
DROP TRIGGER IF EXISTS audit_tbl_emp_monthly_salary_bi;
DROP TRIGGER IF EXISTS audit_tbl_emp_monthly_salary_bu;
DROP TRIGGER IF EXISTS audit_tbl_task_assign_bi;
DROP TRIGGER IF EXISTS audit_tbl_task_assign_bu;

-- ---------- Masters
ALTER TABLE mst_branch ADD COLUMN IF NOT EXISTS created_by INT(11) NOT NULL DEFAULT 0, ADD COLUMN IF NOT EXISTS created_dtm DATETIME NULL DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS modify_by INT(11) NOT NULL DEFAULT 0, ADD COLUMN IF NOT EXISTS modify_dtm DATETIME NULL DEFAULT NULL;
ALTER TABLE mst_brand ADD COLUMN IF NOT EXISTS created_by INT(11) NOT NULL DEFAULT 0, ADD COLUMN IF NOT EXISTS created_dtm DATETIME NULL DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS modify_by INT(11) NOT NULL DEFAULT 0, ADD COLUMN IF NOT EXISTS modify_dtm DATETIME NULL DEFAULT NULL;
ALTER TABLE mst_category ADD COLUMN IF NOT EXISTS created_by INT(11) NOT NULL DEFAULT 0, ADD COLUMN IF NOT EXISTS created_dtm DATETIME NULL DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS modify_by INT(11) NOT NULL DEFAULT 0, ADD COLUMN IF NOT EXISTS modify_dtm DATETIME NULL DEFAULT NULL;
ALTER TABLE mst_city ADD COLUMN IF NOT EXISTS created_by INT(11) NOT NULL DEFAULT 0, ADD COLUMN IF NOT EXISTS created_dtm DATETIME NULL DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS modify_by INT(11) NOT NULL DEFAULT 0, ADD COLUMN IF NOT EXISTS modify_dtm DATETIME NULL DEFAULT NULL;
ALTER TABLE mst_color ADD COLUMN IF NOT EXISTS created_by INT(11) NOT NULL DEFAULT 0, ADD COLUMN IF NOT EXISTS created_dtm DATETIME NULL DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS modify_by INT(11) NOT NULL DEFAULT 0, ADD COLUMN IF NOT EXISTS modify_dtm DATETIME NULL DEFAULT NULL;
ALTER TABLE mst_customer_branch ADD COLUMN IF NOT EXISTS created_by INT(11) NOT NULL DEFAULT 0, ADD COLUMN IF NOT EXISTS created_dtm DATETIME NULL DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS modify_by INT(11) NOT NULL DEFAULT 0, ADD COLUMN IF NOT EXISTS modify_dtm DATETIME NULL DEFAULT NULL;
ALTER TABLE mst_department ADD COLUMN IF NOT EXISTS created_by INT(11) NOT NULL DEFAULT 0, ADD COLUMN IF NOT EXISTS created_dtm DATETIME NULL DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS modify_by INT(11) NOT NULL DEFAULT 0, ADD COLUMN IF NOT EXISTS modify_dtm DATETIME NULL DEFAULT NULL;
ALTER TABLE mst_designation ADD COLUMN IF NOT EXISTS created_by INT(11) NOT NULL DEFAULT 0, ADD COLUMN IF NOT EXISTS created_dtm DATETIME NULL DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS modify_by INT(11) NOT NULL DEFAULT 0, ADD COLUMN IF NOT EXISTS modify_dtm DATETIME NULL DEFAULT NULL;
ALTER TABLE mst_district ADD COLUMN IF NOT EXISTS created_by INT(11) NOT NULL DEFAULT 0, ADD COLUMN IF NOT EXISTS created_dtm DATETIME NULL DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS modify_by INT(11) NOT NULL DEFAULT 0, ADD COLUMN IF NOT EXISTS modify_dtm DATETIME NULL DEFAULT NULL;
ALTER TABLE mst_division ADD COLUMN IF NOT EXISTS created_by INT(11) NOT NULL DEFAULT 0, ADD COLUMN IF NOT EXISTS created_dtm DATETIME NULL DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS modify_by INT(11) NOT NULL DEFAULT 0, ADD COLUMN IF NOT EXISTS modify_dtm DATETIME NULL DEFAULT NULL;
ALTER TABLE mst_employee ADD COLUMN IF NOT EXISTS created_by INT(11) NOT NULL DEFAULT 0, ADD COLUMN IF NOT EXISTS created_dtm DATETIME NULL DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS modify_by INT(11) NOT NULL DEFAULT 0, ADD COLUMN IF NOT EXISTS modify_dtm DATETIME NULL DEFAULT NULL;
ALTER TABLE mst_employee_certificate ADD COLUMN IF NOT EXISTS created_by INT(11) NOT NULL DEFAULT 0, ADD COLUMN IF NOT EXISTS created_dtm DATETIME NULL DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS modify_by INT(11) NOT NULL DEFAULT 0, ADD COLUMN IF NOT EXISTS modify_dtm DATETIME NULL DEFAULT NULL;
ALTER TABLE mst_hsn ADD COLUMN IF NOT EXISTS created_by INT(11) NOT NULL DEFAULT 0, ADD COLUMN IF NOT EXISTS created_dtm DATETIME NULL DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS modify_by INT(11) NOT NULL DEFAULT 0, ADD COLUMN IF NOT EXISTS modify_dtm DATETIME NULL DEFAULT NULL;
ALTER TABLE mst_labour ADD COLUMN IF NOT EXISTS created_by INT(11) NOT NULL DEFAULT 0, ADD COLUMN IF NOT EXISTS created_dtm DATETIME NULL DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS modify_by INT(11) NOT NULL DEFAULT 0, ADD COLUMN IF NOT EXISTS modify_dtm DATETIME NULL DEFAULT NULL;
ALTER TABLE mst_ledger ADD COLUMN IF NOT EXISTS created_by INT(11) NOT NULL DEFAULT 0, ADD COLUMN IF NOT EXISTS created_dtm DATETIME NULL DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS modify_by INT(11) NOT NULL DEFAULT 0, ADD COLUMN IF NOT EXISTS modify_dtm DATETIME NULL DEFAULT NULL;
ALTER TABLE mst_principal ADD COLUMN IF NOT EXISTS created_by INT(11) NOT NULL DEFAULT 0, ADD COLUMN IF NOT EXISTS created_dtm DATETIME NULL DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS modify_by INT(11) NOT NULL DEFAULT 0, ADD COLUMN IF NOT EXISTS modify_dtm DATETIME NULL DEFAULT NULL;
ALTER TABLE mst_salary_setting ADD COLUMN IF NOT EXISTS created_by INT(11) NOT NULL DEFAULT 0, ADD COLUMN IF NOT EXISTS created_dtm DATETIME NULL DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS modify_by INT(11) NOT NULL DEFAULT 0, ADD COLUMN IF NOT EXISTS modify_dtm DATETIME NULL DEFAULT NULL;
ALTER TABLE mst_shifts ADD COLUMN IF NOT EXISTS created_by INT(11) NOT NULL DEFAULT 0, ADD COLUMN IF NOT EXISTS created_dtm DATETIME NULL DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS modify_by INT(11) NOT NULL DEFAULT 0, ADD COLUMN IF NOT EXISTS modify_dtm DATETIME NULL DEFAULT NULL;
ALTER TABLE mst_state ADD COLUMN IF NOT EXISTS created_by INT(11) NOT NULL DEFAULT 0, ADD COLUMN IF NOT EXISTS created_dtm DATETIME NULL DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS modify_by INT(11) NOT NULL DEFAULT 0, ADD COLUMN IF NOT EXISTS modify_dtm DATETIME NULL DEFAULT NULL;
ALTER TABLE mst_supplier_new ADD COLUMN IF NOT EXISTS created_by INT(11) NOT NULL DEFAULT 0, ADD COLUMN IF NOT EXISTS created_dtm DATETIME NULL DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS modify_by INT(11) NOT NULL DEFAULT 0, ADD COLUMN IF NOT EXISTS modify_dtm DATETIME NULL DEFAULT NULL;
ALTER TABLE mst_task ADD COLUMN IF NOT EXISTS created_by INT(11) NOT NULL DEFAULT 0, ADD COLUMN IF NOT EXISTS created_dtm DATETIME NULL DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS modify_by INT(11) NOT NULL DEFAULT 0, ADD COLUMN IF NOT EXISTS modify_dtm DATETIME NULL DEFAULT NULL;
ALTER TABLE mst_uom ADD COLUMN IF NOT EXISTS created_by INT(11) NOT NULL DEFAULT 0, ADD COLUMN IF NOT EXISTS created_dtm DATETIME NULL DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS modify_by INT(11) NOT NULL DEFAULT 0, ADD COLUMN IF NOT EXISTS modify_dtm DATETIME NULL DEFAULT NULL;
ALTER TABLE tbl_user ADD COLUMN IF NOT EXISTS created_by INT(11) NOT NULL DEFAULT 0, ADD COLUMN IF NOT EXISTS created_dtm DATETIME NULL DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS modify_by INT(11) NOT NULL DEFAULT 0, ADD COLUMN IF NOT EXISTS modify_dtm DATETIME NULL DEFAULT NULL;
ALTER TABLE tbl_item_details ADD COLUMN IF NOT EXISTS created_by INT(11) NOT NULL DEFAULT 0, ADD COLUMN IF NOT EXISTS created_dtm DATETIME NULL DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS modify_by INT(11) NOT NULL DEFAULT 0, ADD COLUMN IF NOT EXISTS modify_dtm DATETIME NULL DEFAULT NULL;
UPDATE tbl_item_details SET created_by = modify_by WHERE (created_by IS NULL OR created_by = 0) AND modify_by > 0;
UPDATE tbl_item_details SET created_dtm = modify_date_time WHERE (created_dtm IS NULL OR created_dtm < '2000-01-01') AND modify_date_time >= '2000-01-01';
ALTER TABLE tbl_item_group ADD COLUMN IF NOT EXISTS created_by INT(11) NOT NULL DEFAULT 0, ADD COLUMN IF NOT EXISTS created_dtm DATETIME NULL DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS modify_by INT(11) NOT NULL DEFAULT 0, ADD COLUMN IF NOT EXISTS modify_dtm DATETIME NULL DEFAULT NULL;
UPDATE tbl_item_group SET created_by = modify_by WHERE (created_by IS NULL OR created_by = 0) AND modify_by > 0;
UPDATE tbl_item_group SET created_dtm = created_dt WHERE (created_dtm IS NULL OR created_dtm < '2000-01-01') AND created_dt >= '2000-01-01';
ALTER TABLE tbl_item_uom_price ADD COLUMN IF NOT EXISTS created_by INT(11) NOT NULL DEFAULT 0, ADD COLUMN IF NOT EXISTS created_dtm DATETIME NULL DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS modify_by INT(11) NOT NULL DEFAULT 0, ADD COLUMN IF NOT EXISTS modify_dtm DATETIME NULL DEFAULT NULL;
ALTER TABLE tbl_supp_items ADD COLUMN IF NOT EXISTS created_by INT(11) NOT NULL DEFAULT 0, ADD COLUMN IF NOT EXISTS created_dtm DATETIME NULL DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS modify_by INT(11) NOT NULL DEFAULT 0, ADD COLUMN IF NOT EXISTS modify_dtm DATETIME NULL DEFAULT NULL;
ALTER TABLE tbl_asset_details ADD COLUMN IF NOT EXISTS created_by INT(11) NOT NULL DEFAULT 0, ADD COLUMN IF NOT EXISTS created_dtm DATETIME NULL DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS modify_by INT(11) NOT NULL DEFAULT 0, ADD COLUMN IF NOT EXISTS modify_dtm DATETIME NULL DEFAULT NULL;
ALTER TABLE tbl_month_master ADD COLUMN IF NOT EXISTS created_by INT(11) NOT NULL DEFAULT 0, ADD COLUMN IF NOT EXISTS created_dtm DATETIME NULL DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS modify_by INT(11) NOT NULL DEFAULT 0, ADD COLUMN IF NOT EXISTS modify_dtm DATETIME NULL DEFAULT NULL;

-- ---------- Sales
ALTER TABLE tbl_quotation ADD COLUMN IF NOT EXISTS created_by INT(11) NOT NULL DEFAULT 0, ADD COLUMN IF NOT EXISTS created_dtm DATETIME NULL DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS modify_by INT(11) NOT NULL DEFAULT 0, ADD COLUMN IF NOT EXISTS modify_dtm DATETIME NULL DEFAULT NULL;
ALTER TABLE tbl_sales_order ADD COLUMN IF NOT EXISTS created_by INT(11) NOT NULL DEFAULT 0, ADD COLUMN IF NOT EXISTS created_dtm DATETIME NULL DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS modify_by INT(11) NOT NULL DEFAULT 0, ADD COLUMN IF NOT EXISTS modify_dtm DATETIME NULL DEFAULT NULL;
UPDATE tbl_sales_order SET created_by = modify_by WHERE (created_by IS NULL OR created_by = 0) AND modify_by > 0;
UPDATE tbl_sales_order SET created_dtm = modify_date_time WHERE (created_dtm IS NULL OR created_dtm < '2000-01-01') AND modify_date_time >= '2000-01-01';
ALTER TABLE tbl_dc ADD COLUMN IF NOT EXISTS created_by INT(11) NOT NULL DEFAULT 0, ADD COLUMN IF NOT EXISTS created_dtm DATETIME NULL DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS modify_by INT(11) NOT NULL DEFAULT 0, ADD COLUMN IF NOT EXISTS modify_dtm DATETIME NULL DEFAULT NULL;
UPDATE tbl_dc SET created_by = modify_by WHERE (created_by IS NULL OR created_by = 0) AND modify_by > 0;
UPDATE tbl_dc SET created_dtm = modify_date_time WHERE (created_dtm IS NULL OR created_dtm < '2000-01-01') AND modify_date_time >= '2000-01-01';
ALTER TABLE tbl_dc_package_box ADD COLUMN IF NOT EXISTS created_by INT(11) NOT NULL DEFAULT 0, ADD COLUMN IF NOT EXISTS created_dtm DATETIME NULL DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS modify_by INT(11) NOT NULL DEFAULT 0, ADD COLUMN IF NOT EXISTS modify_dtm DATETIME NULL DEFAULT NULL;
ALTER TABLE tbl_proforma ADD COLUMN IF NOT EXISTS created_by INT(11) NOT NULL DEFAULT 0, ADD COLUMN IF NOT EXISTS created_dtm DATETIME NULL DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS modify_by INT(11) NOT NULL DEFAULT 0, ADD COLUMN IF NOT EXISTS modify_dtm DATETIME NULL DEFAULT NULL;
UPDATE tbl_proforma SET created_by = modify_by WHERE (created_by IS NULL OR created_by = 0) AND modify_by > 0;
UPDATE tbl_proforma SET created_dtm = modify_date_time WHERE (created_dtm IS NULL OR created_dtm < '2000-01-01') AND modify_date_time >= '2000-01-01';
ALTER TABLE tbl_invoice ADD COLUMN IF NOT EXISTS created_by INT(11) NOT NULL DEFAULT 0, ADD COLUMN IF NOT EXISTS created_dtm DATETIME NULL DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS modify_by INT(11) NOT NULL DEFAULT 0, ADD COLUMN IF NOT EXISTS modify_dtm DATETIME NULL DEFAULT NULL;
UPDATE tbl_invoice SET created_by = modify_by WHERE (created_by IS NULL OR created_by = 0) AND modify_by > 0;
UPDATE tbl_invoice SET created_dtm = modify_date_time WHERE (created_dtm IS NULL OR created_dtm < '2000-01-01') AND modify_date_time >= '2000-01-01';
ALTER TABLE tbl_receipt ADD COLUMN IF NOT EXISTS created_by INT(11) NOT NULL DEFAULT 0, ADD COLUMN IF NOT EXISTS created_dtm DATETIME NULL DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS modify_by INT(11) NOT NULL DEFAULT 0, ADD COLUMN IF NOT EXISTS modify_dtm DATETIME NULL DEFAULT NULL;
UPDATE tbl_receipt SET created_dtm = modify_date_time WHERE (created_dtm IS NULL OR created_dtm < '2000-01-01') AND modify_date_time >= '2000-01-01';
ALTER TABLE tbl_sales_repair ADD COLUMN IF NOT EXISTS created_by INT(11) NOT NULL DEFAULT 0, ADD COLUMN IF NOT EXISTS created_dtm DATETIME NULL DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS modify_by INT(11) NOT NULL DEFAULT 0, ADD COLUMN IF NOT EXISTS modify_dtm DATETIME NULL DEFAULT NULL;
UPDATE tbl_sales_repair SET created_by = modify_by WHERE (created_by IS NULL OR created_by = 0) AND modify_by > 0;
UPDATE tbl_sales_repair SET created_dtm = modify_date_time WHERE (created_dtm IS NULL OR created_dtm < '2000-01-01') AND modify_date_time >= '2000-01-01';
ALTER TABLE tbl_spare_mapping ADD COLUMN IF NOT EXISTS created_by INT(11) NOT NULL DEFAULT 0, ADD COLUMN IF NOT EXISTS created_dtm DATETIME NULL DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS modify_by INT(11) NOT NULL DEFAULT 0, ADD COLUMN IF NOT EXISTS modify_dtm DATETIME NULL DEFAULT NULL;

-- ---------- Purchase and stores
ALTER TABLE tbl_store_indent ADD COLUMN IF NOT EXISTS created_by INT(11) NOT NULL DEFAULT 0, ADD COLUMN IF NOT EXISTS created_dtm DATETIME NULL DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS modify_by INT(11) NOT NULL DEFAULT 0, ADD COLUMN IF NOT EXISTS modify_dtm DATETIME NULL DEFAULT NULL;
UPDATE tbl_store_indent SET created_by = modify_by WHERE (created_by IS NULL OR created_by = 0) AND modify_by > 0;
UPDATE tbl_store_indent SET created_dtm = modify_date_time WHERE (created_dtm IS NULL OR created_dtm < '2000-01-01') AND modify_date_time >= '2000-01-01';
ALTER TABLE tbl_po_prepare ADD COLUMN IF NOT EXISTS created_by INT(11) NOT NULL DEFAULT 0, ADD COLUMN IF NOT EXISTS created_dtm DATETIME NULL DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS modify_by INT(11) NOT NULL DEFAULT 0, ADD COLUMN IF NOT EXISTS modify_dtm DATETIME NULL DEFAULT NULL;
UPDATE tbl_po_prepare SET created_by = modify_by WHERE (created_by IS NULL OR created_by = 0) AND modify_by > 0;
UPDATE tbl_po_prepare SET created_dtm = modify_date_time WHERE (created_dtm IS NULL OR created_dtm < '2000-01-01') AND modify_date_time >= '2000-01-01';
ALTER TABLE tbl_purchase_order ADD COLUMN IF NOT EXISTS created_by INT(11) NOT NULL DEFAULT 0, ADD COLUMN IF NOT EXISTS created_dtm DATETIME NULL DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS modify_by INT(11) NOT NULL DEFAULT 0, ADD COLUMN IF NOT EXISTS modify_dtm DATETIME NULL DEFAULT NULL;
UPDATE tbl_purchase_order SET created_by = modify_by WHERE (created_by IS NULL OR created_by = 0) AND modify_by > 0;
UPDATE tbl_purchase_order SET created_dtm = modify_date_time WHERE (created_dtm IS NULL OR created_dtm < '2000-01-01') AND modify_date_time >= '2000-01-01';
ALTER TABLE tbl_grn ADD COLUMN IF NOT EXISTS created_by INT(11) NOT NULL DEFAULT 0, ADD COLUMN IF NOT EXISTS created_dtm DATETIME NULL DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS modify_by INT(11) NOT NULL DEFAULT 0, ADD COLUMN IF NOT EXISTS modify_dtm DATETIME NULL DEFAULT NULL;
UPDATE tbl_grn SET created_by = modify_by WHERE (created_by IS NULL OR created_by = 0) AND modify_by > 0;
UPDATE tbl_grn SET created_dtm = modify_date_time WHERE (created_dtm IS NULL OR created_dtm < '2000-01-01') AND modify_date_time >= '2000-01-01';
ALTER TABLE tbl_grn_pay_receipt ADD COLUMN IF NOT EXISTS created_by INT(11) NOT NULL DEFAULT 0, ADD COLUMN IF NOT EXISTS created_dtm DATETIME NULL DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS modify_by INT(11) NOT NULL DEFAULT 0, ADD COLUMN IF NOT EXISTS modify_dtm DATETIME NULL DEFAULT NULL;
UPDATE tbl_grn_pay_receipt SET created_dtm = modify_date_time WHERE (created_dtm IS NULL OR created_dtm < '2000-01-01') AND modify_date_time >= '2000-01-01';
ALTER TABLE tbl_purchase_return ADD COLUMN IF NOT EXISTS created_by INT(11) NOT NULL DEFAULT 0, ADD COLUMN IF NOT EXISTS created_dtm DATETIME NULL DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS modify_by INT(11) NOT NULL DEFAULT 0, ADD COLUMN IF NOT EXISTS modify_dtm DATETIME NULL DEFAULT NULL;
UPDATE tbl_purchase_return SET created_by = modify_by WHERE (created_by IS NULL OR created_by = 0) AND modify_by > 0;
UPDATE tbl_purchase_return SET created_dtm = modify_date_time WHERE (created_dtm IS NULL OR created_dtm < '2000-01-01') AND modify_date_time >= '2000-01-01';
ALTER TABLE tbl_stock_adjustment ADD COLUMN IF NOT EXISTS created_by INT(11) NOT NULL DEFAULT 0, ADD COLUMN IF NOT EXISTS created_dtm DATETIME NULL DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS modify_by INT(11) NOT NULL DEFAULT 0, ADD COLUMN IF NOT EXISTS modify_dtm DATETIME NULL DEFAULT NULL;
ALTER TABLE tbl_accounts ADD COLUMN IF NOT EXISTS created_by INT(11) NOT NULL DEFAULT 0, ADD COLUMN IF NOT EXISTS created_dtm DATETIME NULL DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS modify_by INT(11) NOT NULL DEFAULT 0, ADD COLUMN IF NOT EXISTS modify_dtm DATETIME NULL DEFAULT NULL;

-- ---------- Accounts
ALTER TABLE tbl_attendance ADD COLUMN IF NOT EXISTS created_by INT(11) NOT NULL DEFAULT 0, ADD COLUMN IF NOT EXISTS created_dtm DATETIME NULL DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS modify_by INT(11) NOT NULL DEFAULT 0, ADD COLUMN IF NOT EXISTS modify_dtm DATETIME NULL DEFAULT NULL;

-- ---------- HR
ALTER TABLE tbl_emp_advance ADD COLUMN IF NOT EXISTS created_by INT(11) NOT NULL DEFAULT 0, ADD COLUMN IF NOT EXISTS created_dtm DATETIME NULL DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS modify_by INT(11) NOT NULL DEFAULT 0, ADD COLUMN IF NOT EXISTS modify_dtm DATETIME NULL DEFAULT NULL;
UPDATE tbl_emp_advance SET created_by = update_by WHERE (created_by IS NULL OR created_by = 0) AND update_by > 0;
UPDATE tbl_emp_advance SET created_dtm = update_dtm WHERE (created_dtm IS NULL OR created_dtm < '2000-01-01') AND update_dtm >= '2000-01-01';
ALTER TABLE tbl_emp_advance_return_payment ADD COLUMN IF NOT EXISTS created_by INT(11) NOT NULL DEFAULT 0, ADD COLUMN IF NOT EXISTS created_dtm DATETIME NULL DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS modify_by INT(11) NOT NULL DEFAULT 0, ADD COLUMN IF NOT EXISTS modify_dtm DATETIME NULL DEFAULT NULL;
UPDATE tbl_emp_advance_return_payment SET created_by = update_by WHERE (created_by IS NULL OR created_by = 0) AND update_by > 0;
UPDATE tbl_emp_advance_return_payment SET created_dtm = update_dtm WHERE (created_dtm IS NULL OR created_dtm < '2000-01-01') AND update_dtm >= '2000-01-01';
ALTER TABLE tbl_emp_debit_note ADD COLUMN IF NOT EXISTS created_by INT(11) NOT NULL DEFAULT 0, ADD COLUMN IF NOT EXISTS created_dtm DATETIME NULL DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS modify_by INT(11) NOT NULL DEFAULT 0, ADD COLUMN IF NOT EXISTS modify_dtm DATETIME NULL DEFAULT NULL;
UPDATE tbl_emp_debit_note SET created_by = update_by WHERE (created_by IS NULL OR created_by = 0) AND update_by > 0;
UPDATE tbl_emp_debit_note SET created_dtm = update_dtm WHERE (created_dtm IS NULL OR created_dtm < '2000-01-01') AND update_dtm >= '2000-01-01';
ALTER TABLE tbl_emp_debit_note_return_payment ADD COLUMN IF NOT EXISTS created_by INT(11) NOT NULL DEFAULT 0, ADD COLUMN IF NOT EXISTS created_dtm DATETIME NULL DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS modify_by INT(11) NOT NULL DEFAULT 0, ADD COLUMN IF NOT EXISTS modify_dtm DATETIME NULL DEFAULT NULL;
UPDATE tbl_emp_debit_note_return_payment SET created_by = update_by WHERE (created_by IS NULL OR created_by = 0) AND update_by > 0;
UPDATE tbl_emp_debit_note_return_payment SET created_dtm = update_dtm WHERE (created_dtm IS NULL OR created_dtm < '2000-01-01') AND update_dtm >= '2000-01-01';
ALTER TABLE tbl_emp_salary ADD COLUMN IF NOT EXISTS created_by INT(11) NOT NULL DEFAULT 0, ADD COLUMN IF NOT EXISTS created_dtm DATETIME NULL DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS modify_by INT(11) NOT NULL DEFAULT 0, ADD COLUMN IF NOT EXISTS modify_dtm DATETIME NULL DEFAULT NULL;
UPDATE tbl_emp_salary SET created_by = em_update_by WHERE (created_by IS NULL OR created_by = 0) AND em_update_by > 0;
ALTER TABLE tbl_emp_monthly_salary ADD COLUMN IF NOT EXISTS created_by INT(11) NOT NULL DEFAULT 0, ADD COLUMN IF NOT EXISTS created_dtm DATETIME NULL DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS modify_by INT(11) NOT NULL DEFAULT 0, ADD COLUMN IF NOT EXISTS modify_dtm DATETIME NULL DEFAULT NULL;
ALTER TABLE tbl_task_assign ADD COLUMN IF NOT EXISTS created_by INT(11) NOT NULL DEFAULT 0, ADD COLUMN IF NOT EXISTS created_dtm DATETIME NULL DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS modify_by INT(11) NOT NULL DEFAULT 0, ADD COLUMN IF NOT EXISTS modify_dtm DATETIME NULL DEFAULT NULL;

-- ---------- triggers
CREATE TRIGGER audit_mst_branch_bi BEFORE INSERT ON mst_branch FOR EACH ROW SET
  NEW.created_by = IF(NEW.created_by > 0, NEW.created_by, IFNULL(@bie_user_id, 0)),
  NEW.created_dtm = IF(NEW.created_dtm >= '2000-01-01', NEW.created_dtm, CONVERT_TZ(UTC_TIMESTAMP(), '+00:00', '+05:30'));
CREATE TRIGGER audit_mst_branch_bu BEFORE UPDATE ON mst_branch FOR EACH ROW SET
  NEW.created_by = IF(OLD.created_by > 0, OLD.created_by, NEW.created_by),
  NEW.created_dtm = IF(OLD.created_dtm >= '2000-01-01', OLD.created_dtm, NEW.created_dtm),
  NEW.modify_by = IF(@bie_user_id > 0, @bie_user_id, NEW.modify_by),
  NEW.modify_dtm = CONVERT_TZ(UTC_TIMESTAMP(), '+00:00', '+05:30');
CREATE TRIGGER audit_mst_brand_bi BEFORE INSERT ON mst_brand FOR EACH ROW SET
  NEW.created_by = IF(NEW.created_by > 0, NEW.created_by, IFNULL(@bie_user_id, 0)),
  NEW.created_dtm = IF(NEW.created_dtm >= '2000-01-01', NEW.created_dtm, CONVERT_TZ(UTC_TIMESTAMP(), '+00:00', '+05:30'));
CREATE TRIGGER audit_mst_brand_bu BEFORE UPDATE ON mst_brand FOR EACH ROW SET
  NEW.created_by = IF(OLD.created_by > 0, OLD.created_by, NEW.created_by),
  NEW.created_dtm = IF(OLD.created_dtm >= '2000-01-01', OLD.created_dtm, NEW.created_dtm),
  NEW.modify_by = IF(@bie_user_id > 0, @bie_user_id, NEW.modify_by),
  NEW.modify_dtm = CONVERT_TZ(UTC_TIMESTAMP(), '+00:00', '+05:30');
CREATE TRIGGER audit_mst_category_bi BEFORE INSERT ON mst_category FOR EACH ROW SET
  NEW.created_by = IF(NEW.created_by > 0, NEW.created_by, IFNULL(@bie_user_id, 0)),
  NEW.created_dtm = IF(NEW.created_dtm >= '2000-01-01', NEW.created_dtm, CONVERT_TZ(UTC_TIMESTAMP(), '+00:00', '+05:30'));
CREATE TRIGGER audit_mst_category_bu BEFORE UPDATE ON mst_category FOR EACH ROW SET
  NEW.created_by = IF(OLD.created_by > 0, OLD.created_by, NEW.created_by),
  NEW.created_dtm = IF(OLD.created_dtm >= '2000-01-01', OLD.created_dtm, NEW.created_dtm),
  NEW.modify_by = IF(@bie_user_id > 0, @bie_user_id, NEW.modify_by),
  NEW.modify_dtm = CONVERT_TZ(UTC_TIMESTAMP(), '+00:00', '+05:30');
CREATE TRIGGER audit_mst_city_bi BEFORE INSERT ON mst_city FOR EACH ROW SET
  NEW.created_by = IF(NEW.created_by > 0, NEW.created_by, IFNULL(@bie_user_id, 0)),
  NEW.created_dtm = IF(NEW.created_dtm >= '2000-01-01', NEW.created_dtm, CONVERT_TZ(UTC_TIMESTAMP(), '+00:00', '+05:30'));
CREATE TRIGGER audit_mst_city_bu BEFORE UPDATE ON mst_city FOR EACH ROW SET
  NEW.created_by = IF(OLD.created_by > 0, OLD.created_by, NEW.created_by),
  NEW.created_dtm = IF(OLD.created_dtm >= '2000-01-01', OLD.created_dtm, NEW.created_dtm),
  NEW.modify_by = IF(@bie_user_id > 0, @bie_user_id, NEW.modify_by),
  NEW.modify_dtm = CONVERT_TZ(UTC_TIMESTAMP(), '+00:00', '+05:30');
CREATE TRIGGER audit_mst_color_bi BEFORE INSERT ON mst_color FOR EACH ROW SET
  NEW.created_by = IF(NEW.created_by > 0, NEW.created_by, IFNULL(@bie_user_id, 0)),
  NEW.created_dtm = IF(NEW.created_dtm >= '2000-01-01', NEW.created_dtm, CONVERT_TZ(UTC_TIMESTAMP(), '+00:00', '+05:30'));
CREATE TRIGGER audit_mst_color_bu BEFORE UPDATE ON mst_color FOR EACH ROW SET
  NEW.created_by = IF(OLD.created_by > 0, OLD.created_by, NEW.created_by),
  NEW.created_dtm = IF(OLD.created_dtm >= '2000-01-01', OLD.created_dtm, NEW.created_dtm),
  NEW.modify_by = IF(@bie_user_id > 0, @bie_user_id, NEW.modify_by),
  NEW.modify_dtm = CONVERT_TZ(UTC_TIMESTAMP(), '+00:00', '+05:30');
CREATE TRIGGER audit_mst_customer_branch_bi BEFORE INSERT ON mst_customer_branch FOR EACH ROW SET
  NEW.created_by = IF(NEW.created_by > 0, NEW.created_by, IFNULL(@bie_user_id, 0)),
  NEW.created_dtm = IF(NEW.created_dtm >= '2000-01-01', NEW.created_dtm, CONVERT_TZ(UTC_TIMESTAMP(), '+00:00', '+05:30'));
CREATE TRIGGER audit_mst_customer_branch_bu BEFORE UPDATE ON mst_customer_branch FOR EACH ROW SET
  NEW.created_by = IF(OLD.created_by > 0, OLD.created_by, NEW.created_by),
  NEW.created_dtm = IF(OLD.created_dtm >= '2000-01-01', OLD.created_dtm, NEW.created_dtm),
  NEW.modify_by = IF(@bie_user_id > 0, @bie_user_id, NEW.modify_by),
  NEW.modify_dtm = CONVERT_TZ(UTC_TIMESTAMP(), '+00:00', '+05:30');
CREATE TRIGGER audit_mst_department_bi BEFORE INSERT ON mst_department FOR EACH ROW SET
  NEW.created_by = IF(NEW.created_by > 0, NEW.created_by, IFNULL(@bie_user_id, 0)),
  NEW.created_dtm = IF(NEW.created_dtm >= '2000-01-01', NEW.created_dtm, CONVERT_TZ(UTC_TIMESTAMP(), '+00:00', '+05:30'));
CREATE TRIGGER audit_mst_department_bu BEFORE UPDATE ON mst_department FOR EACH ROW SET
  NEW.created_by = IF(OLD.created_by > 0, OLD.created_by, NEW.created_by),
  NEW.created_dtm = IF(OLD.created_dtm >= '2000-01-01', OLD.created_dtm, NEW.created_dtm),
  NEW.modify_by = IF(@bie_user_id > 0, @bie_user_id, NEW.modify_by),
  NEW.modify_dtm = CONVERT_TZ(UTC_TIMESTAMP(), '+00:00', '+05:30');
CREATE TRIGGER audit_mst_designation_bi BEFORE INSERT ON mst_designation FOR EACH ROW SET
  NEW.created_by = IF(NEW.created_by > 0, NEW.created_by, IFNULL(@bie_user_id, 0)),
  NEW.created_dtm = IF(NEW.created_dtm >= '2000-01-01', NEW.created_dtm, CONVERT_TZ(UTC_TIMESTAMP(), '+00:00', '+05:30'));
CREATE TRIGGER audit_mst_designation_bu BEFORE UPDATE ON mst_designation FOR EACH ROW SET
  NEW.created_by = IF(OLD.created_by > 0, OLD.created_by, NEW.created_by),
  NEW.created_dtm = IF(OLD.created_dtm >= '2000-01-01', OLD.created_dtm, NEW.created_dtm),
  NEW.modify_by = IF(@bie_user_id > 0, @bie_user_id, NEW.modify_by),
  NEW.modify_dtm = CONVERT_TZ(UTC_TIMESTAMP(), '+00:00', '+05:30');
CREATE TRIGGER audit_mst_district_bi BEFORE INSERT ON mst_district FOR EACH ROW SET
  NEW.created_by = IF(NEW.created_by > 0, NEW.created_by, IFNULL(@bie_user_id, 0)),
  NEW.created_dtm = IF(NEW.created_dtm >= '2000-01-01', NEW.created_dtm, CONVERT_TZ(UTC_TIMESTAMP(), '+00:00', '+05:30'));
CREATE TRIGGER audit_mst_district_bu BEFORE UPDATE ON mst_district FOR EACH ROW SET
  NEW.created_by = IF(OLD.created_by > 0, OLD.created_by, NEW.created_by),
  NEW.created_dtm = IF(OLD.created_dtm >= '2000-01-01', OLD.created_dtm, NEW.created_dtm),
  NEW.modify_by = IF(@bie_user_id > 0, @bie_user_id, NEW.modify_by),
  NEW.modify_dtm = CONVERT_TZ(UTC_TIMESTAMP(), '+00:00', '+05:30');
CREATE TRIGGER audit_mst_division_bi BEFORE INSERT ON mst_division FOR EACH ROW SET
  NEW.created_by = IF(NEW.created_by > 0, NEW.created_by, IFNULL(@bie_user_id, 0)),
  NEW.created_dtm = IF(NEW.created_dtm >= '2000-01-01', NEW.created_dtm, CONVERT_TZ(UTC_TIMESTAMP(), '+00:00', '+05:30'));
CREATE TRIGGER audit_mst_division_bu BEFORE UPDATE ON mst_division FOR EACH ROW SET
  NEW.created_by = IF(OLD.created_by > 0, OLD.created_by, NEW.created_by),
  NEW.created_dtm = IF(OLD.created_dtm >= '2000-01-01', OLD.created_dtm, NEW.created_dtm),
  NEW.modify_by = IF(@bie_user_id > 0, @bie_user_id, NEW.modify_by),
  NEW.modify_dtm = CONVERT_TZ(UTC_TIMESTAMP(), '+00:00', '+05:30');
CREATE TRIGGER audit_mst_employee_bi BEFORE INSERT ON mst_employee FOR EACH ROW SET
  NEW.created_by = IF(NEW.created_by > 0, NEW.created_by, IFNULL(@bie_user_id, 0)),
  NEW.created_dtm = IF(NEW.created_dtm >= '2000-01-01', NEW.created_dtm, CONVERT_TZ(UTC_TIMESTAMP(), '+00:00', '+05:30'));
CREATE TRIGGER audit_mst_employee_bu BEFORE UPDATE ON mst_employee FOR EACH ROW SET
  NEW.created_by = IF(OLD.created_by > 0, OLD.created_by, NEW.created_by),
  NEW.created_dtm = IF(OLD.created_dtm >= '2000-01-01', OLD.created_dtm, NEW.created_dtm),
  NEW.modify_by = IF(@bie_user_id > 0, @bie_user_id, NEW.modify_by),
  NEW.modify_dtm = CONVERT_TZ(UTC_TIMESTAMP(), '+00:00', '+05:30');
CREATE TRIGGER audit_mst_employee_certificate_bi BEFORE INSERT ON mst_employee_certificate FOR EACH ROW SET
  NEW.created_by = IF(NEW.created_by > 0, NEW.created_by, IFNULL(@bie_user_id, 0)),
  NEW.created_dtm = IF(NEW.created_dtm >= '2000-01-01', NEW.created_dtm, CONVERT_TZ(UTC_TIMESTAMP(), '+00:00', '+05:30'));
CREATE TRIGGER audit_mst_employee_certificate_bu BEFORE UPDATE ON mst_employee_certificate FOR EACH ROW SET
  NEW.created_by = IF(OLD.created_by > 0, OLD.created_by, NEW.created_by),
  NEW.created_dtm = IF(OLD.created_dtm >= '2000-01-01', OLD.created_dtm, NEW.created_dtm),
  NEW.modify_by = IF(@bie_user_id > 0, @bie_user_id, NEW.modify_by),
  NEW.modify_dtm = CONVERT_TZ(UTC_TIMESTAMP(), '+00:00', '+05:30');
CREATE TRIGGER audit_mst_hsn_bi BEFORE INSERT ON mst_hsn FOR EACH ROW SET
  NEW.created_by = IF(NEW.created_by > 0, NEW.created_by, IFNULL(@bie_user_id, 0)),
  NEW.created_dtm = IF(NEW.created_dtm >= '2000-01-01', NEW.created_dtm, CONVERT_TZ(UTC_TIMESTAMP(), '+00:00', '+05:30'));
CREATE TRIGGER audit_mst_hsn_bu BEFORE UPDATE ON mst_hsn FOR EACH ROW SET
  NEW.created_by = IF(OLD.created_by > 0, OLD.created_by, NEW.created_by),
  NEW.created_dtm = IF(OLD.created_dtm >= '2000-01-01', OLD.created_dtm, NEW.created_dtm),
  NEW.modify_by = IF(@bie_user_id > 0, @bie_user_id, NEW.modify_by),
  NEW.modify_dtm = CONVERT_TZ(UTC_TIMESTAMP(), '+00:00', '+05:30');
CREATE TRIGGER audit_mst_labour_bi BEFORE INSERT ON mst_labour FOR EACH ROW SET
  NEW.created_by = IF(NEW.created_by > 0, NEW.created_by, IFNULL(@bie_user_id, 0)),
  NEW.created_dtm = IF(NEW.created_dtm >= '2000-01-01', NEW.created_dtm, CONVERT_TZ(UTC_TIMESTAMP(), '+00:00', '+05:30'));
CREATE TRIGGER audit_mst_labour_bu BEFORE UPDATE ON mst_labour FOR EACH ROW SET
  NEW.created_by = IF(OLD.created_by > 0, OLD.created_by, NEW.created_by),
  NEW.created_dtm = IF(OLD.created_dtm >= '2000-01-01', OLD.created_dtm, NEW.created_dtm),
  NEW.modify_by = IF(@bie_user_id > 0, @bie_user_id, NEW.modify_by),
  NEW.modify_dtm = CONVERT_TZ(UTC_TIMESTAMP(), '+00:00', '+05:30');
CREATE TRIGGER audit_mst_ledger_bi BEFORE INSERT ON mst_ledger FOR EACH ROW SET
  NEW.created_by = IF(NEW.created_by > 0, NEW.created_by, IFNULL(@bie_user_id, 0)),
  NEW.created_dtm = IF(NEW.created_dtm >= '2000-01-01', NEW.created_dtm, CONVERT_TZ(UTC_TIMESTAMP(), '+00:00', '+05:30'));
CREATE TRIGGER audit_mst_ledger_bu BEFORE UPDATE ON mst_ledger FOR EACH ROW SET
  NEW.created_by = IF(OLD.created_by > 0, OLD.created_by, NEW.created_by),
  NEW.created_dtm = IF(OLD.created_dtm >= '2000-01-01', OLD.created_dtm, NEW.created_dtm),
  NEW.modify_by = IF(@bie_user_id > 0, @bie_user_id, NEW.modify_by),
  NEW.modify_dtm = CONVERT_TZ(UTC_TIMESTAMP(), '+00:00', '+05:30');
CREATE TRIGGER audit_mst_principal_bi BEFORE INSERT ON mst_principal FOR EACH ROW SET
  NEW.created_by = IF(NEW.created_by > 0, NEW.created_by, IFNULL(@bie_user_id, 0)),
  NEW.created_dtm = IF(NEW.created_dtm >= '2000-01-01', NEW.created_dtm, CONVERT_TZ(UTC_TIMESTAMP(), '+00:00', '+05:30'));
CREATE TRIGGER audit_mst_principal_bu BEFORE UPDATE ON mst_principal FOR EACH ROW SET
  NEW.created_by = IF(OLD.created_by > 0, OLD.created_by, NEW.created_by),
  NEW.created_dtm = IF(OLD.created_dtm >= '2000-01-01', OLD.created_dtm, NEW.created_dtm),
  NEW.modify_by = IF(@bie_user_id > 0, @bie_user_id, NEW.modify_by),
  NEW.modify_dtm = CONVERT_TZ(UTC_TIMESTAMP(), '+00:00', '+05:30');
CREATE TRIGGER audit_mst_salary_setting_bi BEFORE INSERT ON mst_salary_setting FOR EACH ROW SET
  NEW.created_by = IF(NEW.created_by > 0, NEW.created_by, IFNULL(@bie_user_id, 0)),
  NEW.created_dtm = IF(NEW.created_dtm >= '2000-01-01', NEW.created_dtm, CONVERT_TZ(UTC_TIMESTAMP(), '+00:00', '+05:30'));
CREATE TRIGGER audit_mst_salary_setting_bu BEFORE UPDATE ON mst_salary_setting FOR EACH ROW SET
  NEW.created_by = IF(OLD.created_by > 0, OLD.created_by, NEW.created_by),
  NEW.created_dtm = IF(OLD.created_dtm >= '2000-01-01', OLD.created_dtm, NEW.created_dtm),
  NEW.modify_by = IF(@bie_user_id > 0, @bie_user_id, NEW.modify_by),
  NEW.modify_dtm = CONVERT_TZ(UTC_TIMESTAMP(), '+00:00', '+05:30');
CREATE TRIGGER audit_mst_shifts_bi BEFORE INSERT ON mst_shifts FOR EACH ROW SET
  NEW.created_by = IF(NEW.created_by > 0, NEW.created_by, IFNULL(@bie_user_id, 0)),
  NEW.created_dtm = IF(NEW.created_dtm >= '2000-01-01', NEW.created_dtm, CONVERT_TZ(UTC_TIMESTAMP(), '+00:00', '+05:30'));
CREATE TRIGGER audit_mst_shifts_bu BEFORE UPDATE ON mst_shifts FOR EACH ROW SET
  NEW.created_by = IF(OLD.created_by > 0, OLD.created_by, NEW.created_by),
  NEW.created_dtm = IF(OLD.created_dtm >= '2000-01-01', OLD.created_dtm, NEW.created_dtm),
  NEW.modify_by = IF(@bie_user_id > 0, @bie_user_id, NEW.modify_by),
  NEW.modify_dtm = CONVERT_TZ(UTC_TIMESTAMP(), '+00:00', '+05:30');
CREATE TRIGGER audit_mst_state_bi BEFORE INSERT ON mst_state FOR EACH ROW SET
  NEW.created_by = IF(NEW.created_by > 0, NEW.created_by, IFNULL(@bie_user_id, 0)),
  NEW.created_dtm = IF(NEW.created_dtm >= '2000-01-01', NEW.created_dtm, CONVERT_TZ(UTC_TIMESTAMP(), '+00:00', '+05:30'));
CREATE TRIGGER audit_mst_state_bu BEFORE UPDATE ON mst_state FOR EACH ROW SET
  NEW.created_by = IF(OLD.created_by > 0, OLD.created_by, NEW.created_by),
  NEW.created_dtm = IF(OLD.created_dtm >= '2000-01-01', OLD.created_dtm, NEW.created_dtm),
  NEW.modify_by = IF(@bie_user_id > 0, @bie_user_id, NEW.modify_by),
  NEW.modify_dtm = CONVERT_TZ(UTC_TIMESTAMP(), '+00:00', '+05:30');
CREATE TRIGGER audit_mst_supplier_new_bi BEFORE INSERT ON mst_supplier_new FOR EACH ROW SET
  NEW.created_by = IF(NEW.created_by > 0, NEW.created_by, IFNULL(@bie_user_id, 0)),
  NEW.created_dtm = IF(NEW.created_dtm >= '2000-01-01', NEW.created_dtm, CONVERT_TZ(UTC_TIMESTAMP(), '+00:00', '+05:30'));
CREATE TRIGGER audit_mst_supplier_new_bu BEFORE UPDATE ON mst_supplier_new FOR EACH ROW SET
  NEW.created_by = IF(OLD.created_by > 0, OLD.created_by, NEW.created_by),
  NEW.created_dtm = IF(OLD.created_dtm >= '2000-01-01', OLD.created_dtm, NEW.created_dtm),
  NEW.modify_by = IF(@bie_user_id > 0, @bie_user_id, NEW.modify_by),
  NEW.modify_dtm = CONVERT_TZ(UTC_TIMESTAMP(), '+00:00', '+05:30');
CREATE TRIGGER audit_mst_task_bi BEFORE INSERT ON mst_task FOR EACH ROW SET
  NEW.created_by = IF(NEW.created_by > 0, NEW.created_by, IFNULL(@bie_user_id, 0)),
  NEW.created_dtm = IF(NEW.created_dtm >= '2000-01-01', NEW.created_dtm, CONVERT_TZ(UTC_TIMESTAMP(), '+00:00', '+05:30'));
CREATE TRIGGER audit_mst_task_bu BEFORE UPDATE ON mst_task FOR EACH ROW SET
  NEW.created_by = IF(OLD.created_by > 0, OLD.created_by, NEW.created_by),
  NEW.created_dtm = IF(OLD.created_dtm >= '2000-01-01', OLD.created_dtm, NEW.created_dtm),
  NEW.modify_by = IF(@bie_user_id > 0, @bie_user_id, NEW.modify_by),
  NEW.modify_dtm = CONVERT_TZ(UTC_TIMESTAMP(), '+00:00', '+05:30');
CREATE TRIGGER audit_mst_uom_bi BEFORE INSERT ON mst_uom FOR EACH ROW SET
  NEW.created_by = IF(NEW.created_by > 0, NEW.created_by, IFNULL(@bie_user_id, 0)),
  NEW.created_dtm = IF(NEW.created_dtm >= '2000-01-01', NEW.created_dtm, CONVERT_TZ(UTC_TIMESTAMP(), '+00:00', '+05:30'));
CREATE TRIGGER audit_mst_uom_bu BEFORE UPDATE ON mst_uom FOR EACH ROW SET
  NEW.created_by = IF(OLD.created_by > 0, OLD.created_by, NEW.created_by),
  NEW.created_dtm = IF(OLD.created_dtm >= '2000-01-01', OLD.created_dtm, NEW.created_dtm),
  NEW.modify_by = IF(@bie_user_id > 0, @bie_user_id, NEW.modify_by),
  NEW.modify_dtm = CONVERT_TZ(UTC_TIMESTAMP(), '+00:00', '+05:30');
CREATE TRIGGER audit_tbl_user_bi BEFORE INSERT ON tbl_user FOR EACH ROW SET
  NEW.created_by = IF(NEW.created_by > 0, NEW.created_by, IFNULL(@bie_user_id, 0)),
  NEW.created_dtm = IF(NEW.created_dtm >= '2000-01-01', NEW.created_dtm, CONVERT_TZ(UTC_TIMESTAMP(), '+00:00', '+05:30'));
CREATE TRIGGER audit_tbl_user_bu BEFORE UPDATE ON tbl_user FOR EACH ROW SET
  NEW.created_by = IF(OLD.created_by > 0, OLD.created_by, NEW.created_by),
  NEW.created_dtm = IF(OLD.created_dtm >= '2000-01-01', OLD.created_dtm, NEW.created_dtm),
  NEW.modify_by = IF(@bie_user_id > 0, @bie_user_id, NEW.modify_by),
  NEW.modify_dtm = CONVERT_TZ(UTC_TIMESTAMP(), '+00:00', '+05:30');
CREATE TRIGGER audit_tbl_item_details_bi BEFORE INSERT ON tbl_item_details FOR EACH ROW SET
  NEW.created_by = IF(NEW.created_by > 0, NEW.created_by, IFNULL(@bie_user_id, 0)),
  NEW.created_dtm = IF(NEW.created_dtm >= '2000-01-01', NEW.created_dtm, CONVERT_TZ(UTC_TIMESTAMP(), '+00:00', '+05:30'));
CREATE TRIGGER audit_tbl_item_details_bu BEFORE UPDATE ON tbl_item_details FOR EACH ROW SET
  NEW.created_by = IF(OLD.created_by > 0, OLD.created_by, NEW.created_by),
  NEW.created_dtm = IF(OLD.created_dtm >= '2000-01-01', OLD.created_dtm, NEW.created_dtm),
  NEW.modify_by = IF(@bie_user_id > 0, @bie_user_id, NEW.modify_by),
  NEW.modify_dtm = CONVERT_TZ(UTC_TIMESTAMP(), '+00:00', '+05:30');
CREATE TRIGGER audit_tbl_item_group_bi BEFORE INSERT ON tbl_item_group FOR EACH ROW SET
  NEW.created_by = IF(NEW.created_by > 0, NEW.created_by, IFNULL(@bie_user_id, 0)),
  NEW.created_dtm = IF(NEW.created_dtm >= '2000-01-01', NEW.created_dtm, CONVERT_TZ(UTC_TIMESTAMP(), '+00:00', '+05:30'));
CREATE TRIGGER audit_tbl_item_group_bu BEFORE UPDATE ON tbl_item_group FOR EACH ROW SET
  NEW.created_by = IF(OLD.created_by > 0, OLD.created_by, NEW.created_by),
  NEW.created_dtm = IF(OLD.created_dtm >= '2000-01-01', OLD.created_dtm, NEW.created_dtm),
  NEW.modify_by = IF(@bie_user_id > 0, @bie_user_id, NEW.modify_by),
  NEW.modify_dtm = CONVERT_TZ(UTC_TIMESTAMP(), '+00:00', '+05:30');
CREATE TRIGGER audit_tbl_item_uom_price_bi BEFORE INSERT ON tbl_item_uom_price FOR EACH ROW SET
  NEW.created_by = IF(NEW.created_by > 0, NEW.created_by, IFNULL(@bie_user_id, 0)),
  NEW.created_dtm = IF(NEW.created_dtm >= '2000-01-01', NEW.created_dtm, CONVERT_TZ(UTC_TIMESTAMP(), '+00:00', '+05:30'));
CREATE TRIGGER audit_tbl_item_uom_price_bu BEFORE UPDATE ON tbl_item_uom_price FOR EACH ROW SET
  NEW.created_by = IF(OLD.created_by > 0, OLD.created_by, NEW.created_by),
  NEW.created_dtm = IF(OLD.created_dtm >= '2000-01-01', OLD.created_dtm, NEW.created_dtm),
  NEW.modify_by = IF(@bie_user_id > 0, @bie_user_id, NEW.modify_by),
  NEW.modify_dtm = CONVERT_TZ(UTC_TIMESTAMP(), '+00:00', '+05:30');
CREATE TRIGGER audit_tbl_supp_items_bi BEFORE INSERT ON tbl_supp_items FOR EACH ROW SET
  NEW.created_by = IF(NEW.created_by > 0, NEW.created_by, IFNULL(@bie_user_id, 0)),
  NEW.created_dtm = IF(NEW.created_dtm >= '2000-01-01', NEW.created_dtm, CONVERT_TZ(UTC_TIMESTAMP(), '+00:00', '+05:30'));
CREATE TRIGGER audit_tbl_supp_items_bu BEFORE UPDATE ON tbl_supp_items FOR EACH ROW SET
  NEW.created_by = IF(OLD.created_by > 0, OLD.created_by, NEW.created_by),
  NEW.created_dtm = IF(OLD.created_dtm >= '2000-01-01', OLD.created_dtm, NEW.created_dtm),
  NEW.modify_by = IF(@bie_user_id > 0, @bie_user_id, NEW.modify_by),
  NEW.modify_dtm = CONVERT_TZ(UTC_TIMESTAMP(), '+00:00', '+05:30');
CREATE TRIGGER audit_tbl_asset_details_bi BEFORE INSERT ON tbl_asset_details FOR EACH ROW SET
  NEW.created_by = IF(NEW.created_by > 0, NEW.created_by, IFNULL(@bie_user_id, 0)),
  NEW.created_dtm = IF(NEW.created_dtm >= '2000-01-01', NEW.created_dtm, CONVERT_TZ(UTC_TIMESTAMP(), '+00:00', '+05:30'));
CREATE TRIGGER audit_tbl_asset_details_bu BEFORE UPDATE ON tbl_asset_details FOR EACH ROW SET
  NEW.created_by = IF(OLD.created_by > 0, OLD.created_by, NEW.created_by),
  NEW.created_dtm = IF(OLD.created_dtm >= '2000-01-01', OLD.created_dtm, NEW.created_dtm),
  NEW.modify_by = IF(@bie_user_id > 0, @bie_user_id, NEW.modify_by),
  NEW.modify_dtm = CONVERT_TZ(UTC_TIMESTAMP(), '+00:00', '+05:30');
CREATE TRIGGER audit_tbl_month_master_bi BEFORE INSERT ON tbl_month_master FOR EACH ROW SET
  NEW.created_by = IF(NEW.created_by > 0, NEW.created_by, IFNULL(@bie_user_id, 0)),
  NEW.created_dtm = IF(NEW.created_dtm >= '2000-01-01', NEW.created_dtm, CONVERT_TZ(UTC_TIMESTAMP(), '+00:00', '+05:30'));
CREATE TRIGGER audit_tbl_month_master_bu BEFORE UPDATE ON tbl_month_master FOR EACH ROW SET
  NEW.created_by = IF(OLD.created_by > 0, OLD.created_by, NEW.created_by),
  NEW.created_dtm = IF(OLD.created_dtm >= '2000-01-01', OLD.created_dtm, NEW.created_dtm),
  NEW.modify_by = IF(@bie_user_id > 0, @bie_user_id, NEW.modify_by),
  NEW.modify_dtm = CONVERT_TZ(UTC_TIMESTAMP(), '+00:00', '+05:30');
CREATE TRIGGER audit_tbl_quotation_bi BEFORE INSERT ON tbl_quotation FOR EACH ROW SET
  NEW.created_by = IF(NEW.created_by > 0, NEW.created_by, IFNULL(@bie_user_id, 0)),
  NEW.created_dtm = IF(NEW.created_dtm >= '2000-01-01', NEW.created_dtm, CONVERT_TZ(UTC_TIMESTAMP(), '+00:00', '+05:30'));
CREATE TRIGGER audit_tbl_quotation_bu BEFORE UPDATE ON tbl_quotation FOR EACH ROW SET
  NEW.created_by = IF(OLD.created_by > 0, OLD.created_by, NEW.created_by),
  NEW.created_dtm = IF(OLD.created_dtm >= '2000-01-01', OLD.created_dtm, NEW.created_dtm),
  NEW.modify_by = IF(@bie_user_id > 0, @bie_user_id, NEW.modify_by),
  NEW.modify_dtm = CONVERT_TZ(UTC_TIMESTAMP(), '+00:00', '+05:30');
CREATE TRIGGER audit_tbl_sales_order_bi BEFORE INSERT ON tbl_sales_order FOR EACH ROW SET
  NEW.created_by = IF(NEW.created_by > 0, NEW.created_by, IFNULL(@bie_user_id, 0)),
  NEW.created_dtm = IF(NEW.created_dtm >= '2000-01-01', NEW.created_dtm, CONVERT_TZ(UTC_TIMESTAMP(), '+00:00', '+05:30'));
CREATE TRIGGER audit_tbl_sales_order_bu BEFORE UPDATE ON tbl_sales_order FOR EACH ROW SET
  NEW.created_by = IF(OLD.created_by > 0, OLD.created_by, NEW.created_by),
  NEW.created_dtm = IF(OLD.created_dtm >= '2000-01-01', OLD.created_dtm, NEW.created_dtm),
  NEW.modify_by = IF(@bie_user_id > 0, @bie_user_id, NEW.modify_by),
  NEW.modify_dtm = CONVERT_TZ(UTC_TIMESTAMP(), '+00:00', '+05:30');
CREATE TRIGGER audit_tbl_dc_bi BEFORE INSERT ON tbl_dc FOR EACH ROW SET
  NEW.created_by = IF(NEW.created_by > 0, NEW.created_by, IFNULL(@bie_user_id, 0)),
  NEW.created_dtm = IF(NEW.created_dtm >= '2000-01-01', NEW.created_dtm, CONVERT_TZ(UTC_TIMESTAMP(), '+00:00', '+05:30'));
CREATE TRIGGER audit_tbl_dc_bu BEFORE UPDATE ON tbl_dc FOR EACH ROW SET
  NEW.created_by = IF(OLD.created_by > 0, OLD.created_by, NEW.created_by),
  NEW.created_dtm = IF(OLD.created_dtm >= '2000-01-01', OLD.created_dtm, NEW.created_dtm),
  NEW.modify_by = IF(@bie_user_id > 0, @bie_user_id, NEW.modify_by),
  NEW.modify_dtm = CONVERT_TZ(UTC_TIMESTAMP(), '+00:00', '+05:30');
CREATE TRIGGER audit_tbl_dc_package_box_bi BEFORE INSERT ON tbl_dc_package_box FOR EACH ROW SET
  NEW.created_by = IF(NEW.created_by > 0, NEW.created_by, IFNULL(@bie_user_id, 0)),
  NEW.created_dtm = IF(NEW.created_dtm >= '2000-01-01', NEW.created_dtm, CONVERT_TZ(UTC_TIMESTAMP(), '+00:00', '+05:30'));
CREATE TRIGGER audit_tbl_dc_package_box_bu BEFORE UPDATE ON tbl_dc_package_box FOR EACH ROW SET
  NEW.created_by = IF(OLD.created_by > 0, OLD.created_by, NEW.created_by),
  NEW.created_dtm = IF(OLD.created_dtm >= '2000-01-01', OLD.created_dtm, NEW.created_dtm),
  NEW.modify_by = IF(@bie_user_id > 0, @bie_user_id, NEW.modify_by),
  NEW.modify_dtm = CONVERT_TZ(UTC_TIMESTAMP(), '+00:00', '+05:30');
CREATE TRIGGER audit_tbl_proforma_bi BEFORE INSERT ON tbl_proforma FOR EACH ROW SET
  NEW.created_by = IF(NEW.created_by > 0, NEW.created_by, IFNULL(@bie_user_id, 0)),
  NEW.created_dtm = IF(NEW.created_dtm >= '2000-01-01', NEW.created_dtm, CONVERT_TZ(UTC_TIMESTAMP(), '+00:00', '+05:30'));
CREATE TRIGGER audit_tbl_proforma_bu BEFORE UPDATE ON tbl_proforma FOR EACH ROW SET
  NEW.created_by = IF(OLD.created_by > 0, OLD.created_by, NEW.created_by),
  NEW.created_dtm = IF(OLD.created_dtm >= '2000-01-01', OLD.created_dtm, NEW.created_dtm),
  NEW.modify_by = IF(@bie_user_id > 0, @bie_user_id, NEW.modify_by),
  NEW.modify_dtm = CONVERT_TZ(UTC_TIMESTAMP(), '+00:00', '+05:30');
CREATE TRIGGER audit_tbl_invoice_bi BEFORE INSERT ON tbl_invoice FOR EACH ROW SET
  NEW.created_by = IF(NEW.created_by > 0, NEW.created_by, IFNULL(@bie_user_id, 0)),
  NEW.created_dtm = IF(NEW.created_dtm >= '2000-01-01', NEW.created_dtm, CONVERT_TZ(UTC_TIMESTAMP(), '+00:00', '+05:30'));
CREATE TRIGGER audit_tbl_invoice_bu BEFORE UPDATE ON tbl_invoice FOR EACH ROW SET
  NEW.created_by = IF(OLD.created_by > 0, OLD.created_by, NEW.created_by),
  NEW.created_dtm = IF(OLD.created_dtm >= '2000-01-01', OLD.created_dtm, NEW.created_dtm),
  NEW.modify_by = IF(@bie_user_id > 0, @bie_user_id, NEW.modify_by),
  NEW.modify_dtm = CONVERT_TZ(UTC_TIMESTAMP(), '+00:00', '+05:30');
CREATE TRIGGER audit_tbl_receipt_bi BEFORE INSERT ON tbl_receipt FOR EACH ROW SET
  NEW.created_by = IF(NEW.created_by > 0, NEW.created_by, IFNULL(@bie_user_id, 0)),
  NEW.created_dtm = IF(NEW.created_dtm >= '2000-01-01', NEW.created_dtm, CONVERT_TZ(UTC_TIMESTAMP(), '+00:00', '+05:30'));
CREATE TRIGGER audit_tbl_receipt_bu BEFORE UPDATE ON tbl_receipt FOR EACH ROW SET
  NEW.created_by = IF(OLD.created_by > 0, OLD.created_by, NEW.created_by),
  NEW.created_dtm = IF(OLD.created_dtm >= '2000-01-01', OLD.created_dtm, NEW.created_dtm),
  NEW.modify_by = IF(@bie_user_id > 0, @bie_user_id, NEW.modify_by),
  NEW.modify_dtm = CONVERT_TZ(UTC_TIMESTAMP(), '+00:00', '+05:30');
CREATE TRIGGER audit_tbl_sales_repair_bi BEFORE INSERT ON tbl_sales_repair FOR EACH ROW SET
  NEW.created_by = IF(NEW.created_by > 0, NEW.created_by, IFNULL(@bie_user_id, 0)),
  NEW.created_dtm = IF(NEW.created_dtm >= '2000-01-01', NEW.created_dtm, CONVERT_TZ(UTC_TIMESTAMP(), '+00:00', '+05:30'));
CREATE TRIGGER audit_tbl_sales_repair_bu BEFORE UPDATE ON tbl_sales_repair FOR EACH ROW SET
  NEW.created_by = IF(OLD.created_by > 0, OLD.created_by, NEW.created_by),
  NEW.created_dtm = IF(OLD.created_dtm >= '2000-01-01', OLD.created_dtm, NEW.created_dtm),
  NEW.modify_by = IF(@bie_user_id > 0, @bie_user_id, NEW.modify_by),
  NEW.modify_dtm = CONVERT_TZ(UTC_TIMESTAMP(), '+00:00', '+05:30');
CREATE TRIGGER audit_tbl_spare_mapping_bi BEFORE INSERT ON tbl_spare_mapping FOR EACH ROW SET
  NEW.created_by = IF(NEW.created_by > 0, NEW.created_by, IFNULL(@bie_user_id, 0)),
  NEW.created_dtm = IF(NEW.created_dtm >= '2000-01-01', NEW.created_dtm, CONVERT_TZ(UTC_TIMESTAMP(), '+00:00', '+05:30'));
CREATE TRIGGER audit_tbl_spare_mapping_bu BEFORE UPDATE ON tbl_spare_mapping FOR EACH ROW SET
  NEW.created_by = IF(OLD.created_by > 0, OLD.created_by, NEW.created_by),
  NEW.created_dtm = IF(OLD.created_dtm >= '2000-01-01', OLD.created_dtm, NEW.created_dtm),
  NEW.modify_by = IF(@bie_user_id > 0, @bie_user_id, NEW.modify_by),
  NEW.modify_dtm = CONVERT_TZ(UTC_TIMESTAMP(), '+00:00', '+05:30');
CREATE TRIGGER audit_tbl_store_indent_bi BEFORE INSERT ON tbl_store_indent FOR EACH ROW SET
  NEW.created_by = IF(NEW.created_by > 0, NEW.created_by, IFNULL(@bie_user_id, 0)),
  NEW.created_dtm = IF(NEW.created_dtm >= '2000-01-01', NEW.created_dtm, CONVERT_TZ(UTC_TIMESTAMP(), '+00:00', '+05:30'));
CREATE TRIGGER audit_tbl_store_indent_bu BEFORE UPDATE ON tbl_store_indent FOR EACH ROW SET
  NEW.created_by = IF(OLD.created_by > 0, OLD.created_by, NEW.created_by),
  NEW.created_dtm = IF(OLD.created_dtm >= '2000-01-01', OLD.created_dtm, NEW.created_dtm),
  NEW.modify_by = IF(@bie_user_id > 0, @bie_user_id, NEW.modify_by),
  NEW.modify_dtm = CONVERT_TZ(UTC_TIMESTAMP(), '+00:00', '+05:30');
CREATE TRIGGER audit_tbl_po_prepare_bi BEFORE INSERT ON tbl_po_prepare FOR EACH ROW SET
  NEW.created_by = IF(NEW.created_by > 0, NEW.created_by, IFNULL(@bie_user_id, 0)),
  NEW.created_dtm = IF(NEW.created_dtm >= '2000-01-01', NEW.created_dtm, CONVERT_TZ(UTC_TIMESTAMP(), '+00:00', '+05:30'));
CREATE TRIGGER audit_tbl_po_prepare_bu BEFORE UPDATE ON tbl_po_prepare FOR EACH ROW SET
  NEW.created_by = IF(OLD.created_by > 0, OLD.created_by, NEW.created_by),
  NEW.created_dtm = IF(OLD.created_dtm >= '2000-01-01', OLD.created_dtm, NEW.created_dtm),
  NEW.modify_by = IF(@bie_user_id > 0, @bie_user_id, NEW.modify_by),
  NEW.modify_dtm = CONVERT_TZ(UTC_TIMESTAMP(), '+00:00', '+05:30');
CREATE TRIGGER audit_tbl_purchase_order_bi BEFORE INSERT ON tbl_purchase_order FOR EACH ROW SET
  NEW.created_by = IF(NEW.created_by > 0, NEW.created_by, IFNULL(@bie_user_id, 0)),
  NEW.created_dtm = IF(NEW.created_dtm >= '2000-01-01', NEW.created_dtm, CONVERT_TZ(UTC_TIMESTAMP(), '+00:00', '+05:30'));
CREATE TRIGGER audit_tbl_purchase_order_bu BEFORE UPDATE ON tbl_purchase_order FOR EACH ROW SET
  NEW.created_by = IF(OLD.created_by > 0, OLD.created_by, NEW.created_by),
  NEW.created_dtm = IF(OLD.created_dtm >= '2000-01-01', OLD.created_dtm, NEW.created_dtm),
  NEW.modify_by = IF(@bie_user_id > 0, @bie_user_id, NEW.modify_by),
  NEW.modify_dtm = CONVERT_TZ(UTC_TIMESTAMP(), '+00:00', '+05:30');
CREATE TRIGGER audit_tbl_grn_bi BEFORE INSERT ON tbl_grn FOR EACH ROW SET
  NEW.created_by = IF(NEW.created_by > 0, NEW.created_by, IFNULL(@bie_user_id, 0)),
  NEW.created_dtm = IF(NEW.created_dtm >= '2000-01-01', NEW.created_dtm, CONVERT_TZ(UTC_TIMESTAMP(), '+00:00', '+05:30'));
CREATE TRIGGER audit_tbl_grn_bu BEFORE UPDATE ON tbl_grn FOR EACH ROW SET
  NEW.created_by = IF(OLD.created_by > 0, OLD.created_by, NEW.created_by),
  NEW.created_dtm = IF(OLD.created_dtm >= '2000-01-01', OLD.created_dtm, NEW.created_dtm),
  NEW.modify_by = IF(@bie_user_id > 0, @bie_user_id, NEW.modify_by),
  NEW.modify_dtm = CONVERT_TZ(UTC_TIMESTAMP(), '+00:00', '+05:30');
CREATE TRIGGER audit_tbl_grn_pay_receipt_bi BEFORE INSERT ON tbl_grn_pay_receipt FOR EACH ROW SET
  NEW.created_by = IF(NEW.created_by > 0, NEW.created_by, IFNULL(@bie_user_id, 0)),
  NEW.created_dtm = IF(NEW.created_dtm >= '2000-01-01', NEW.created_dtm, CONVERT_TZ(UTC_TIMESTAMP(), '+00:00', '+05:30'));
CREATE TRIGGER audit_tbl_grn_pay_receipt_bu BEFORE UPDATE ON tbl_grn_pay_receipt FOR EACH ROW SET
  NEW.created_by = IF(OLD.created_by > 0, OLD.created_by, NEW.created_by),
  NEW.created_dtm = IF(OLD.created_dtm >= '2000-01-01', OLD.created_dtm, NEW.created_dtm),
  NEW.modify_by = IF(@bie_user_id > 0, @bie_user_id, NEW.modify_by),
  NEW.modify_dtm = CONVERT_TZ(UTC_TIMESTAMP(), '+00:00', '+05:30');
CREATE TRIGGER audit_tbl_purchase_return_bi BEFORE INSERT ON tbl_purchase_return FOR EACH ROW SET
  NEW.created_by = IF(NEW.created_by > 0, NEW.created_by, IFNULL(@bie_user_id, 0)),
  NEW.created_dtm = IF(NEW.created_dtm >= '2000-01-01', NEW.created_dtm, CONVERT_TZ(UTC_TIMESTAMP(), '+00:00', '+05:30'));
CREATE TRIGGER audit_tbl_purchase_return_bu BEFORE UPDATE ON tbl_purchase_return FOR EACH ROW SET
  NEW.created_by = IF(OLD.created_by > 0, OLD.created_by, NEW.created_by),
  NEW.created_dtm = IF(OLD.created_dtm >= '2000-01-01', OLD.created_dtm, NEW.created_dtm),
  NEW.modify_by = IF(@bie_user_id > 0, @bie_user_id, NEW.modify_by),
  NEW.modify_dtm = CONVERT_TZ(UTC_TIMESTAMP(), '+00:00', '+05:30');
CREATE TRIGGER audit_tbl_stock_adjustment_bi BEFORE INSERT ON tbl_stock_adjustment FOR EACH ROW SET
  NEW.created_by = IF(NEW.created_by > 0, NEW.created_by, IFNULL(@bie_user_id, 0)),
  NEW.created_dtm = IF(NEW.created_dtm >= '2000-01-01', NEW.created_dtm, CONVERT_TZ(UTC_TIMESTAMP(), '+00:00', '+05:30'));
CREATE TRIGGER audit_tbl_stock_adjustment_bu BEFORE UPDATE ON tbl_stock_adjustment FOR EACH ROW SET
  NEW.created_by = IF(OLD.created_by > 0, OLD.created_by, NEW.created_by),
  NEW.created_dtm = IF(OLD.created_dtm >= '2000-01-01', OLD.created_dtm, NEW.created_dtm),
  NEW.modify_by = IF(@bie_user_id > 0, @bie_user_id, NEW.modify_by),
  NEW.modify_dtm = CONVERT_TZ(UTC_TIMESTAMP(), '+00:00', '+05:30');
CREATE TRIGGER audit_tbl_accounts_bi BEFORE INSERT ON tbl_accounts FOR EACH ROW SET
  NEW.created_by = IF(NEW.created_by > 0, NEW.created_by, IFNULL(@bie_user_id, 0)),
  NEW.created_dtm = IF(NEW.created_dtm >= '2000-01-01', NEW.created_dtm, CONVERT_TZ(UTC_TIMESTAMP(), '+00:00', '+05:30'));
CREATE TRIGGER audit_tbl_accounts_bu BEFORE UPDATE ON tbl_accounts FOR EACH ROW SET
  NEW.created_by = IF(OLD.created_by > 0, OLD.created_by, NEW.created_by),
  NEW.created_dtm = IF(OLD.created_dtm >= '2000-01-01', OLD.created_dtm, NEW.created_dtm),
  NEW.modify_by = IF(@bie_user_id > 0, @bie_user_id, NEW.modify_by),
  NEW.modify_dtm = CONVERT_TZ(UTC_TIMESTAMP(), '+00:00', '+05:30');
CREATE TRIGGER audit_tbl_attendance_bi BEFORE INSERT ON tbl_attendance FOR EACH ROW SET
  NEW.created_by = IF(NEW.created_by > 0, NEW.created_by, IFNULL(@bie_user_id, 0)),
  NEW.created_dtm = IF(NEW.created_dtm >= '2000-01-01', NEW.created_dtm, CONVERT_TZ(UTC_TIMESTAMP(), '+00:00', '+05:30'));
CREATE TRIGGER audit_tbl_attendance_bu BEFORE UPDATE ON tbl_attendance FOR EACH ROW SET
  NEW.created_by = IF(OLD.created_by > 0, OLD.created_by, NEW.created_by),
  NEW.created_dtm = IF(OLD.created_dtm >= '2000-01-01', OLD.created_dtm, NEW.created_dtm),
  NEW.modify_by = IF(@bie_user_id > 0, @bie_user_id, NEW.modify_by),
  NEW.modify_dtm = CONVERT_TZ(UTC_TIMESTAMP(), '+00:00', '+05:30');
CREATE TRIGGER audit_tbl_emp_advance_bi BEFORE INSERT ON tbl_emp_advance FOR EACH ROW SET
  NEW.created_by = IF(NEW.created_by > 0, NEW.created_by, IFNULL(@bie_user_id, 0)),
  NEW.created_dtm = IF(NEW.created_dtm >= '2000-01-01', NEW.created_dtm, CONVERT_TZ(UTC_TIMESTAMP(), '+00:00', '+05:30'));
CREATE TRIGGER audit_tbl_emp_advance_bu BEFORE UPDATE ON tbl_emp_advance FOR EACH ROW SET
  NEW.created_by = IF(OLD.created_by > 0, OLD.created_by, NEW.created_by),
  NEW.created_dtm = IF(OLD.created_dtm >= '2000-01-01', OLD.created_dtm, NEW.created_dtm),
  NEW.modify_by = IF(@bie_user_id > 0, @bie_user_id, NEW.modify_by),
  NEW.modify_dtm = CONVERT_TZ(UTC_TIMESTAMP(), '+00:00', '+05:30');
CREATE TRIGGER audit_tbl_emp_advance_return_payment_bi BEFORE INSERT ON tbl_emp_advance_return_payment FOR EACH ROW SET
  NEW.created_by = IF(NEW.created_by > 0, NEW.created_by, IFNULL(@bie_user_id, 0)),
  NEW.created_dtm = IF(NEW.created_dtm >= '2000-01-01', NEW.created_dtm, CONVERT_TZ(UTC_TIMESTAMP(), '+00:00', '+05:30'));
CREATE TRIGGER audit_tbl_emp_advance_return_payment_bu BEFORE UPDATE ON tbl_emp_advance_return_payment FOR EACH ROW SET
  NEW.created_by = IF(OLD.created_by > 0, OLD.created_by, NEW.created_by),
  NEW.created_dtm = IF(OLD.created_dtm >= '2000-01-01', OLD.created_dtm, NEW.created_dtm),
  NEW.modify_by = IF(@bie_user_id > 0, @bie_user_id, NEW.modify_by),
  NEW.modify_dtm = CONVERT_TZ(UTC_TIMESTAMP(), '+00:00', '+05:30');
CREATE TRIGGER audit_tbl_emp_debit_note_bi BEFORE INSERT ON tbl_emp_debit_note FOR EACH ROW SET
  NEW.created_by = IF(NEW.created_by > 0, NEW.created_by, IFNULL(@bie_user_id, 0)),
  NEW.created_dtm = IF(NEW.created_dtm >= '2000-01-01', NEW.created_dtm, CONVERT_TZ(UTC_TIMESTAMP(), '+00:00', '+05:30'));
CREATE TRIGGER audit_tbl_emp_debit_note_bu BEFORE UPDATE ON tbl_emp_debit_note FOR EACH ROW SET
  NEW.created_by = IF(OLD.created_by > 0, OLD.created_by, NEW.created_by),
  NEW.created_dtm = IF(OLD.created_dtm >= '2000-01-01', OLD.created_dtm, NEW.created_dtm),
  NEW.modify_by = IF(@bie_user_id > 0, @bie_user_id, NEW.modify_by),
  NEW.modify_dtm = CONVERT_TZ(UTC_TIMESTAMP(), '+00:00', '+05:30');
CREATE TRIGGER audit_tbl_emp_debit_note_return_payment_bi BEFORE INSERT ON tbl_emp_debit_note_return_payment FOR EACH ROW SET
  NEW.created_by = IF(NEW.created_by > 0, NEW.created_by, IFNULL(@bie_user_id, 0)),
  NEW.created_dtm = IF(NEW.created_dtm >= '2000-01-01', NEW.created_dtm, CONVERT_TZ(UTC_TIMESTAMP(), '+00:00', '+05:30'));
CREATE TRIGGER audit_tbl_emp_debit_note_return_payment_bu BEFORE UPDATE ON tbl_emp_debit_note_return_payment FOR EACH ROW SET
  NEW.created_by = IF(OLD.created_by > 0, OLD.created_by, NEW.created_by),
  NEW.created_dtm = IF(OLD.created_dtm >= '2000-01-01', OLD.created_dtm, NEW.created_dtm),
  NEW.modify_by = IF(@bie_user_id > 0, @bie_user_id, NEW.modify_by),
  NEW.modify_dtm = CONVERT_TZ(UTC_TIMESTAMP(), '+00:00', '+05:30');
CREATE TRIGGER audit_tbl_emp_salary_bi BEFORE INSERT ON tbl_emp_salary FOR EACH ROW SET
  NEW.created_by = IF(NEW.created_by > 0, NEW.created_by, IFNULL(@bie_user_id, 0)),
  NEW.created_dtm = IF(NEW.created_dtm >= '2000-01-01', NEW.created_dtm, CONVERT_TZ(UTC_TIMESTAMP(), '+00:00', '+05:30'));
CREATE TRIGGER audit_tbl_emp_salary_bu BEFORE UPDATE ON tbl_emp_salary FOR EACH ROW SET
  NEW.created_by = IF(OLD.created_by > 0, OLD.created_by, NEW.created_by),
  NEW.created_dtm = IF(OLD.created_dtm >= '2000-01-01', OLD.created_dtm, NEW.created_dtm),
  NEW.modify_by = IF(@bie_user_id > 0, @bie_user_id, NEW.modify_by),
  NEW.modify_dtm = CONVERT_TZ(UTC_TIMESTAMP(), '+00:00', '+05:30');
CREATE TRIGGER audit_tbl_emp_monthly_salary_bi BEFORE INSERT ON tbl_emp_monthly_salary FOR EACH ROW SET
  NEW.created_by = IF(NEW.created_by > 0, NEW.created_by, IFNULL(@bie_user_id, 0)),
  NEW.created_dtm = IF(NEW.created_dtm >= '2000-01-01', NEW.created_dtm, CONVERT_TZ(UTC_TIMESTAMP(), '+00:00', '+05:30'));
CREATE TRIGGER audit_tbl_emp_monthly_salary_bu BEFORE UPDATE ON tbl_emp_monthly_salary FOR EACH ROW SET
  NEW.created_by = IF(OLD.created_by > 0, OLD.created_by, NEW.created_by),
  NEW.created_dtm = IF(OLD.created_dtm >= '2000-01-01', OLD.created_dtm, NEW.created_dtm),
  NEW.modify_by = IF(@bie_user_id > 0, @bie_user_id, NEW.modify_by),
  NEW.modify_dtm = CONVERT_TZ(UTC_TIMESTAMP(), '+00:00', '+05:30');
CREATE TRIGGER audit_tbl_task_assign_bi BEFORE INSERT ON tbl_task_assign FOR EACH ROW SET
  NEW.created_by = IF(NEW.created_by > 0, NEW.created_by, IFNULL(@bie_user_id, 0)),
  NEW.created_dtm = IF(NEW.created_dtm >= '2000-01-01', NEW.created_dtm, CONVERT_TZ(UTC_TIMESTAMP(), '+00:00', '+05:30'));
CREATE TRIGGER audit_tbl_task_assign_bu BEFORE UPDATE ON tbl_task_assign FOR EACH ROW SET
  NEW.created_by = IF(OLD.created_by > 0, OLD.created_by, NEW.created_by),
  NEW.created_dtm = IF(OLD.created_dtm >= '2000-01-01', OLD.created_dtm, NEW.created_dtm),
  NEW.modify_by = IF(@bie_user_id > 0, @bie_user_id, NEW.modify_by),
  NEW.modify_dtm = CONVERT_TZ(UTC_TIMESTAMP(), '+00:00', '+05:30');

-- check: 108 rows named audit_..._bi / audit_..._bu
SHOW TRIGGERS WHERE `Trigger` LIKE 'audit\_%';
