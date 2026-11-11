-- 2026-11-11 PO/0269/BIE/KL/2627 (po_id 1333): PUT-B-12-8-B, 20 MTR, went out at 0 - there was no MTR price.
-- First run db/post_upload_111126.sql, then set the MTR price for Kerala on the Item Price screen of PUT-B-12-8-B,
-- then run this once: the PO line and its prepare line take that price. Nothing changes if the price is not set yet.
-- Only for this PO while it is not approved (po_approve_status 0).

UPDATE tbl_purchase_order_details d
JOIN tbl_purchase_order p ON p.po_id = d.po_id AND p.po_approve_status = 0
JOIN tbl_item_uom_price u ON u.item_id = d.item_id AND u.uom_id = 12 AND u.branch_id = 2 AND u.cost_price > 0
SET d.item_price = u.item_price,
    d.cost_price = u.cost_price,
    d.discount_per = u.item_discount,
    d.po_value = ROUND(u.cost_price * d.po_qty, 2),
    d.tax_value = ROUND(ROUND(u.cost_price * d.po_qty, 2) * d.vat / 100, 2),
    d.net_value = ROUND(u.cost_price * d.po_qty, 2) + ROUND(ROUND(u.cost_price * d.po_qty, 2) * d.vat / 100, 2)
WHERE d.po_id = 1333 AND d.item_id = 4469;

UPDATE tbl_purchase_order p
SET p.po_value = (SELECT SUM(net_value) FROM tbl_purchase_order_details WHERE po_id = 1333)
WHERE p.po_id = 1333 AND p.po_approve_status = 0;

UPDATE tbl_po_prepare_dets pd
JOIN tbl_item_uom_price u ON u.item_id = pd.item_id AND u.uom_id = 12 AND u.branch_id = 2 AND u.cost_price > 0
SET pd.uom_code = 'MTR',
    pd.unit_price = u.cost_price,
    pd.item_discount = u.item_discount,
    pd.gst = 18,
    pd.gst_id = 132,
    pd.price = ROUND(u.cost_price * pd.si_qty, 2),
    pd.net_amt = ROUND(ROUND(u.cost_price * pd.si_qty, 2) * 1.18, 2)
WHERE pd.po_prepare_id = 1268 AND pd.item_id = 4469;

-- check: cost_price and net_value should no longer be 0
SELECT po_id, po_qty, po_unit, item_price, cost_price, po_value, tax_value, net_value FROM tbl_purchase_order_details WHERE po_id = 1333;
SELECT po_refno, po_value FROM tbl_purchase_order WHERE po_id = 1333;
