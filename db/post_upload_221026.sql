-- 2026-10-22 Purchase Order Report menu, and indexes for the sales order / purchase order reports
-- Run once in phpMyAdmin on the bie database before uploading the 22-10-26 files. Safe to run again.

-- menu: Report > Purchase Order Report (admin sees every menu); given to everyone who has the Sales Order Report
INSERT INTO mst_sub_menu (mm_id, sm_index, sm_name, sm_url, sm_show, sm_class)
SELECT 22, 9, 'Purchase Order Report', 'rpt_purchase_order.php', 1, ''
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM mst_sub_menu WHERE sm_url = 'rpt_purchase_order.php');

INSERT INTO tbl_user_rights (usr_id, mm_id, sm_id)
SELECT DISTINCT r.usr_id, s.mm_id, s.sm_id
FROM tbl_user_rights r
JOIN mst_sub_menu h ON h.sm_id = r.sm_id AND h.sm_url = 'rpt_sales_order.php'
JOIN mst_sub_menu s ON s.sm_url = 'rpt_purchase_order.php'
WHERE NOT EXISTS (SELECT 1 FROM tbl_user_rights x WHERE x.usr_id = r.usr_id AND x.sm_id = s.sm_id);

-- indexes on the link columns - the reports and the GRN / DC / invoice screens look rows up by them and scanned whole tables
CREATE INDEX IF NOT EXISTS po_id ON tbl_purchase_order_details (po_id);
CREATE INDEX IF NOT EXISTS po_id ON tbl_grn (po_id);
CREATE INDEX IF NOT EXISTS grn_item ON tbl_grn_details (grn_id, grn_item_id);
CREATE INDEX IF NOT EXISTS so_id ON tbl_sales_order_details (so_id);
CREATE INDEX IF NOT EXISTS so_id ON tbl_dc (so_id);
CREATE INDEX IF NOT EXISTS dc_item ON tbl_dc_details (dc_id, dc_item_id);
CREATE INDEX IF NOT EXISTS so_id ON tbl_invoice (so_id);
CREATE INDEX IF NOT EXISTS inv_id ON tbl_invoice_details (inv_id);
CREATE INDEX IF NOT EXISTS grn_id ON tbl_grn_pay_receipt (grn_id);
