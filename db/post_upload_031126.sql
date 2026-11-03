-- 2026-11-03 part qty (0.2, 0.5, 1.25) through the whole sale: quotation -> sales order -> DC (and packing) -> proforma.
-- These quantities were whole numbers (int): a 0.5 on a quotation was saved as 1 and a 0.2 as 0, and the sales order /
-- DC / proforma made from it carried the rounded qty. Now decimal with 3 places, as the invoice qty
-- (db/post_upload_301026.sql) and the stock columns. Existing quantities keep their value (2 becomes 2.000).
-- Run once in phpMyAdmin on the bie database, after db/post_upload_301026.sql; safe to run again.

ALTER TABLE tbl_quotation_details MODIFY quo_qty DECIMAL(12,3) NOT NULL;

ALTER TABLE tbl_sales_order_details MODIFY so_qty DECIMAL(12,3) NOT NULL,
                                    MODIFY so_avl_qty DECIMAL(12,3) NOT NULL;
ALTER TABLE tbl_sales_order MODIFY so_qty DECIMAL(12,3) NOT NULL;

ALTER TABLE tbl_dc_details MODIFY dc_qty DECIMAL(12,3) NOT NULL,
                           MODIFY dc_dispatch_qty DECIMAL(12,3) NOT NULL,
                           MODIFY bal_qty DECIMAL(12,3) NOT NULL;
ALTER TABLE tbl_package_box_details MODIFY total_qty DECIMAL(12,3) NOT NULL,
                                    MODIFY dispatch_qty DECIMAL(12,3) NOT NULL;

ALTER TABLE tbl_proforma_details MODIFY pro_qty DECIMAL(12,3) NOT NULL;

-- check: each Type should read decimal(12,3)
SHOW COLUMNS FROM tbl_quotation_details LIKE 'quo_qty';
SHOW COLUMNS FROM tbl_sales_order_details WHERE Field IN ('so_qty', 'so_avl_qty');
SHOW COLUMNS FROM tbl_sales_order LIKE 'so_qty';
SHOW COLUMNS FROM tbl_dc_details WHERE Field IN ('dc_qty', 'dc_dispatch_qty', 'bal_qty');
SHOW COLUMNS FROM tbl_package_box_details WHERE Field IN ('total_qty', 'dispatch_qty');
SHOW COLUMNS FROM tbl_proforma_details LIKE 'pro_qty';
