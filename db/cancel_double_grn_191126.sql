-- 2026-11-19 mark the three zeroed GRNs of the double entries as Cancelled (run after db/fix_double_grn_191126.sql).
-- GRN/0258 (= GRN/0260), GRN/1055 (= GRN/1056) and GRN/0413 (= GRN/0420) show 0 qty since the bill no. entry; the
-- second GRN is the real receipt and the extra stock is already taken out by fix_double_grn_191126.sql.
-- Same as the GRN cancel (modal_grn_reject_dets.php): status 3, cancel flag, by, time, reason - the GRN list shows it
-- as Cancelled with the reason, and it drops out of the GRN payment lists, the GRN Payment Pending tile and the PO
-- report. Header only: no stock or line change. Kept, not deleted, so the GRN numbers and the stock history stay whole.
-- Only a GRN still at 0 qty, with no bill amount and not cancelled yet is changed; safe to run again.

SET @bie_user_id = 1;
SET @now = CONVERT_TZ(UTC_TIMESTAMP(), '+00:00', '+05:30');

UPDATE tbl_grn z
JOIN tbl_grn s ON s.grn_id = CASE z.grn_id WHEN 534 THEN 541 WHEN 1232 THEN 1233 WHEN 1606 THEN 1608 END
SET z.grn_status = 3, z.grn_approve_status = 0, z.grn_cancel_status = 1, z.grn_cancel_by = 1, z.grn_cancel_date_time = @now,
    z.grn_cancel_reason = CONCAT('Duplicate of ', s.grn_ref_code, ' (same goods',
                                 IF(IFNULL(s.grn_refno, '') <> '', CONCAT(', bill ', s.grn_refno), ''),
                                 ') - qty went to 0 on the bill no. entry; extra stock taken out by stock adjustment')
WHERE z.grn_id IN (534, 1232, 1606)
  AND z.grn_cancel_status = 0
  AND z.grn_pay_amount = 0
  AND (SELECT IFNULL(SUM(d.grn_accepted_qty), 0) FROM tbl_grn_details d WHERE d.grn_id = z.grn_id) = 0;

SELECT grn_ref_code, grn_status, grn_cancel_status, grn_cancel_date_time, grn_cancel_reason
FROM tbl_grn WHERE grn_id IN (534, 1232, 1606) ORDER BY grn_id;
