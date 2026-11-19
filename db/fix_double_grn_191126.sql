-- 2026-11-19 take out the stock added twice by the double GRNs (see docs/CHANGELOG.md, 2026-11-19).
-- Entering the bill no. on a saved GRN set its lines to 0 (stock stayed in), the PO reopened and a second GRN added
-- the same goods again:
--   GRN/0413/BIE/HO/2425 + GRN/0420/BIE/HO/2425  (PO 417)   20 units
--   GRN/1055/BIE/HO/2526 + GRN/1056/BIE/HO/2526  (PO 999)   27 units
--   GRN/0258/BIE/HO/2627 + GRN/0260/BIE/HO/2627  (PO 1316)  32 units
-- The second GRN stays as the real receipt. For each item the stock added by the zeroed GRN is taken out as a stock
-- adjustment (Decrease), the same way the Stock Adjustment screen saves one: a tbl_stock_adjustment row with the next
-- ADJ number, the HO stock reduced, and a tbl_stock_flow row. Like that screen it never takes stock below 0 - where
-- the stock now is lower than the extra, the rest shows in the last column of the result: count those items.
-- Run once in phpMyAdmin on the bie database. Safe to run again: items already corrected are skipped.

SET @bie_user_id = 1;
SET @now = CONVERT_TZ(UTC_TIMESTAMP(), '+00:00', '+05:30');
SET @finyr = (SELECT finyr FROM mst_finyear WHERE finyr_active = 1 LIMIT 1);

START TRANSACTION;

DROP TEMPORARY TABLE IF EXISTS fix_dbl;
CREATE TEMPORARY TABLE fix_dbl AS
SELECT z.grn_id, z.grn_ref_code AS zeroed_grn, s.grn_ref_code AS second_grn, p.po_refno,
       f.item_id, SUM(f.trans_qty) AS extra_qty
FROM tbl_grn z
JOIN tbl_grn s ON s.po_id = z.po_id
              AND s.grn_id = CASE z.grn_id WHEN 534 THEN 541 WHEN 1232 THEN 1233 WHEN 1606 THEN 1608 END
JOIN tbl_purchase_order p ON p.po_id = z.po_id
JOIN tbl_stock_flow f ON f.trans_type = 'GRN' AND f.trans_dir = 'I' AND f.trans_id = z.grn_id AND f.stock_field = 'ho_stock'
WHERE z.grn_id IN (534, 1232, 1606)
  AND (SELECT IFNULL(SUM(d.grn_accepted_qty), 0) FROM tbl_grn_details d WHERE d.grn_id = z.grn_id) = 0
GROUP BY z.grn_id, z.grn_ref_code, s.grn_ref_code, p.po_refno, f.item_id;

-- already corrected (this script run before)
DELETE x FROM fix_dbl x
JOIN tbl_stock_adjustment a ON a.item_id = x.item_id AND a.adj_reason LIKE CONCAT('Double GRN: ', x.zeroed_grn, ' %');

ALTER TABLE fix_dbl ADD before_qty DECIMAL(12,3) NOT NULL DEFAULT 0, ADD cut_qty DECIMAL(12,3) NOT NULL DEFAULT 0,
                    ADD item_price DECIMAL(12,2) NOT NULL DEFAULT 0, ADD adj_slno INT NOT NULL DEFAULT 0,
                    ADD adj_refno VARCHAR(25) NOT NULL DEFAULT '';

UPDATE fix_dbl x
SET x.before_qty = IFNULL((SELECT st.ho_stock FROM tbl_item_stock st WHERE st.item_id = x.item_id ORDER BY st.stock_id LIMIT 1), 0),
    x.item_price = IFNULL((SELECT i.item_cost_price FROM tbl_item_details i WHERE i.item_id = x.item_id), 0);
UPDATE fix_dbl SET cut_qty = LEAST(extra_qty, GREATEST(before_qty, 0));

-- next ADJ numbers for HO in the active year
SET @n = (SELECT IFNULL(MAX(adj_slno), 0) FROM tbl_stock_adjustment WHERE adj_finyr = @finyr AND branch_id = 1);
UPDATE fix_dbl SET adj_slno = (@n := @n + 1), adj_refno = CONCAT('ADJ/', LPAD(@n, 4, '0'), '/BIE/HO/', @finyr)
WHERE cut_qty > 0 ORDER BY grn_id, item_id;

INSERT INTO tbl_stock_adjustment
    (adj_refno, adj_slno, adj_finyr, adj_date, branch_id, stock_field, item_id, adj_type,
     adj_qty, before_qty, after_qty, item_price, adj_reason, adj_status, created_by, created_dtm)
SELECT adj_refno, adj_slno, @finyr, DATE(@now), 1, 'ho_stock', item_id, 'D',
       cut_qty, before_qty, before_qty - cut_qty, item_price,
       CONCAT('Double GRN: ', zeroed_grn, ' and ', second_grn, ' are the same goods (', po_refno, ') - extra ', extra_qty + 0),
       1, 1, @now
FROM fix_dbl WHERE cut_qty > 0;

UPDATE tbl_item_stock st JOIN fix_dbl x ON x.item_id = st.item_id
SET st.ho_stock = st.ho_stock - x.cut_qty
WHERE x.cut_qty > 0;

INSERT INTO tbl_stock_flow
    (trans_type, trans_dir, trans_id, branch_id, stock_field, trans_date, item_id, item_price,
     before_qty, rcvd_qty, trans_qty, reje_qty, pend_qty, trans_remarks, after_qty, modify_by, modify_date_time, stock_status)
SELECT 'ADJ', 'O', a.adj_id, 1, 'ho_stock', DATE(@now), x.item_id, x.item_price,
       x.before_qty, 0, x.cut_qty, 0, 0, a.adj_reason, x.before_qty - x.cut_qty, 1, @now, 0
FROM fix_dbl x
JOIN tbl_stock_adjustment a ON a.adj_refno = x.adj_refno AND a.item_id = x.item_id
WHERE x.cut_qty > 0;

COMMIT;

-- result: what was taken out, and what could not be (stock already lower) - count those items
SELECT x.zeroed_grn, x.second_grn, i.item_purchase_code AS item, x.extra_qty AS extra,
       x.before_qty AS stock_before, x.cut_qty AS taken_out, x.before_qty - x.cut_qty AS stock_now,
       x.extra_qty - x.cut_qty AS not_taken_out_count_these, x.adj_refno
FROM fix_dbl x JOIN tbl_item_details i ON i.item_id = x.item_id
ORDER BY x.grn_id, i.item_purchase_code;

DROP TEMPORARY TABLE IF EXISTS fix_dbl;
