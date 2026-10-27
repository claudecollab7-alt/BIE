-- 2026-10-26 indexes on the link columns pages look rows up by - each of these was a full scan of the table every time
-- (e.g. the quotation branch report sums quotation details 1,400 times per run, each one reading all 6,200 detail rows).
-- Found by opening every menu page with the query log on and EXPLAIN on each query, plus the columns the code filters on.
-- Only adds indexes - no data changes. Safe to run again (IF NOT EXISTS). Run once in phpMyAdmin on the bie database.

-- detail rows by their document
CREATE INDEX IF NOT EXISTS quo_id ON tbl_quotation_details (quo_id);
CREATE INDEX IF NOT EXISTS quo_id ON tbl_quo_pack_details (quo_id);
CREATE INDEX IF NOT EXISTS inv_id ON tbl_invoice_pack_details (inv_id);
CREATE INDEX IF NOT EXISTS so_id ON tbl_sales_order_pack_dts (so_id);
CREATE INDEX IF NOT EXISTS si_id ON tbl_store_indent_details (si_id);
CREATE INDEX IF NOT EXISTS po_prepare_id ON tbl_po_prepare_dets (po_prepare_id);
CREATE INDEX IF NOT EXISTS si_id ON tbl_po_prepare_dets (si_id);
CREATE INDEX IF NOT EXISTS si_id ON tbl_po_prepare (si_id);
CREATE INDEX IF NOT EXISTS po_id ON tbl_po_print_details (po_id);
CREATE INDEX IF NOT EXISTS dc_id ON tbl_package_box_details (dc_id);
CREATE INDEX IF NOT EXISTS item_group_id ON tbl_item_group_details (item_group_id);
CREATE INDEX IF NOT EXISTS emp_id ON tbl_attendance (emp_id);

-- documents by customer / supplier and by the document they came from
CREATE INDEX IF NOT EXISTS supp_id ON tbl_quotation (supp_id);
CREATE INDEX IF NOT EXISTS supp_id ON tbl_purchase_order (supp_id);
CREATE INDEX IF NOT EXISTS supp_id ON tbl_grn (supp_id);
CREATE INDEX IF NOT EXISTS supp_id ON tbl_invoice (supp_id);
CREATE INDEX IF NOT EXISTS quo_id ON tbl_sales_order (quo_id);
CREATE INDEX IF NOT EXISTS so_id ON tbl_receipt (so_id);
CREATE INDEX IF NOT EXISTS dc_inv_id ON tbl_dc (dc_inv_id);

-- item history / item lookups across documents
CREATE INDEX IF NOT EXISTS item_id ON tbl_quotation_details (item_id);
CREATE INDEX IF NOT EXISTS item_id ON tbl_sales_order_details (item_id);
CREATE INDEX IF NOT EXISTS item_id ON tbl_purchase_order_details (item_id);
CREATE INDEX IF NOT EXISTS item_id ON tbl_invoice_details (item_id);
CREATE INDEX IF NOT EXISTS item_id ON tbl_spare_mapping (item_id);

-- check: the indexes now on these tables
SELECT table_name, index_name, GROUP_CONCAT(column_name ORDER BY seq_in_index) AS columns
FROM information_schema.statistics
WHERE table_schema = DATABASE() AND index_name <> 'PRIMARY'
  AND table_name IN ('tbl_quotation_details', 'tbl_quo_pack_details', 'tbl_invoice_pack_details', 'tbl_sales_order_pack_dts',
                     'tbl_store_indent_details', 'tbl_po_prepare_dets', 'tbl_po_prepare', 'tbl_po_print_details', 'tbl_package_box_details',
                     'tbl_item_group_details', 'tbl_attendance', 'tbl_quotation', 'tbl_purchase_order', 'tbl_grn', 'tbl_invoice',
                     'tbl_sales_order', 'tbl_receipt', 'tbl_dc', 'tbl_sales_order_details', 'tbl_purchase_order_details',
                     'tbl_invoice_details', 'tbl_spare_mapping')
GROUP BY table_name, index_name
ORDER BY table_name, index_name;
