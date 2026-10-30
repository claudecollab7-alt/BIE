-- 2026-10-30 items can be sold in part qty (0.2, 0.5, 1.25) on the invoice.
-- tbl_invoice_details.inv_qty was a whole number (int) - a 0.5 typed on the invoice was saved as 1 or 0.
-- Now decimal with 3 places, the same as the stock columns (tbl_item_stock *_stock) and tbl_stock_flow.
-- Existing quantities keep their value (2 becomes 2.000). Run once in phpMyAdmin on the bie database; safe to run again.

ALTER TABLE tbl_invoice_details MODIFY inv_qty DECIMAL(12,3) NOT NULL;

-- check: Type should read decimal(12,3)
SHOW COLUMNS FROM tbl_invoice_details LIKE 'inv_qty';
