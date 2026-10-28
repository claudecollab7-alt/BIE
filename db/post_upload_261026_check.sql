-- 2026-10-28 check for db/post_upload_261026.sql - read only, run it ON ITS OWN after that file (in the same database).
-- Lists the indexes now on the 22 tables: each table should show the index(es) named in post_upload_261026.sql.
-- Kept apart because a query on information_schema in the same batch as CREATE INDEX makes phpMyAdmin send the
-- whole batch to information_schema (#1044 Access denied).

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
