-- 2026-10-22 GRN payment: balance and status worked out again from the payments made
-- Run once in phpMyAdmin on the bie database after uploading the 22-10-26 files. Safe to run again.
-- Also links the GRNs of old Multiple GRN bills (step 1).
-- Editing a bill amount kept the old balance and set the status back to Pending (1) even when the GRN was
-- part paid or fully paid. Only GRNs with a bill amount are touched (single and multiple bills, not the GRNs
-- that ride on another GRN's bill). Status: 1 = nothing paid, 2 = part paid, 3 = fully paid.

-- 1. GRNs on another GRN's bill (Multiple GRN) that were never linked: they were only linked when on the same PO,
--    so they stayed "Not Yet" in the GRN payment list though their bill was entered and paid on the main GRN.
--    Only GRNs with no bill of their own are linked (same supplier and branch as the main GRN).
SELECT m.grn_id AS main_grn, m.grn_ref_code AS main_ref, s.grn_id, s.grn_ref_code
FROM tbl_grn s JOIN tbl_grn m ON m.pay_type = 'M' AND m.grn_status = 2 AND FIND_IN_SET(s.grn_id, m.grn_ids)
WHERE s.pay_type = 'S' AND s.grn_status = 2 AND s.grn_pay_finished_status = 0 AND s.grn_pay_amount = 0
  AND s.supp_id = m.supp_id AND s.branch_id = m.branch_id AND s.grn_id <> m.grn_id;

UPDATE tbl_grn s JOIN tbl_grn m ON m.pay_type = 'M' AND m.grn_status = 2 AND FIND_IN_SET(s.grn_id, m.grn_ids)
SET s.pay_type = 'N', s.grn_ids = m.grn_id, s.grn_bill_copy = m.grn_bill_copy, s.grn_bal_value = 0
WHERE s.pay_type = 'S' AND s.grn_status = 2 AND s.grn_pay_finished_status = 0 AND s.grn_pay_amount = 0
  AND s.supp_id = m.supp_id AND s.branch_id = m.branch_id AND s.grn_id <> m.grn_id;

-- for review only (not changed): GRNs listed on a main GRN's bill that also got a bill of their own
SELECT m.grn_ref_code AS main_ref, s.grn_ref_code, s.grn_pay_amount AS own_bill_amount, s.grn_pay_finished_status AS own_status
FROM tbl_grn s JOIN tbl_grn m ON m.pay_type = 'M' AND m.grn_status = 2 AND FIND_IN_SET(s.grn_id, m.grn_ids)
WHERE s.pay_type = 'S' AND s.grn_pay_amount > 0;

-- 2. status and balance
-- before: GRNs whose status or balance does not match their payments
SELECT g.grn_id, g.grn_ref_code, g.grn_pay_amount, IFNULL(p.paid, 0) AS paid, g.grn_bal_value, g.grn_pay_finished_status AS status_now,
       CASE WHEN IFNULL(p.paid, 0) <= 0 THEN 1 WHEN p.paid >= g.grn_pay_amount THEN 3 ELSE 2 END AS status_right
FROM tbl_grn g LEFT JOIN (SELECT grn_id, SUM(pay_amount) AS paid FROM tbl_grn_pay_receipt GROUP BY grn_id) p ON p.grn_id = g.grn_id
WHERE g.grn_status = 2 AND g.pay_type IN ('S', 'M') AND g.grn_pay_amount > 0
  AND (g.grn_pay_finished_status <> CASE WHEN IFNULL(p.paid, 0) <= 0 THEN 1 WHEN p.paid >= g.grn_pay_amount THEN 3 ELSE 2 END
       OR g.grn_bal_value <> GREATEST(g.grn_pay_amount - IFNULL(p.paid, 0), 0));

UPDATE tbl_grn g LEFT JOIN (SELECT grn_id, SUM(pay_amount) AS paid FROM tbl_grn_pay_receipt GROUP BY grn_id) p ON p.grn_id = g.grn_id
SET g.grn_pay_finished_status = CASE WHEN IFNULL(p.paid, 0) <= 0 THEN 1 WHEN p.paid >= g.grn_pay_amount THEN 3 ELSE 2 END,
    g.grn_bal_value = GREATEST(g.grn_pay_amount - IFNULL(p.paid, 0), 0),
    g.grn_bill_status = 1
WHERE g.grn_status = 2 AND g.pay_type IN ('S', 'M') AND g.grn_pay_amount > 0
  AND (g.grn_pay_finished_status <> CASE WHEN IFNULL(p.paid, 0) <= 0 THEN 1 WHEN p.paid >= g.grn_pay_amount THEN 3 ELSE 2 END
       OR g.grn_bal_value <> GREATEST(g.grn_pay_amount - IFNULL(p.paid, 0), 0)
       OR g.grn_bill_status <> 1);
