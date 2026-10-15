-- 2026-10-15 Revoke - undo a wrong approve / verify, kept per task in Task Settings (role R, Revoker)
-- Run once in phpMyAdmin on the bie database, after post_upload_131026.sql and before uploading the 15-10-26 files. Safe to run again.
--
-- No user rows are added: only admin users can revoke until someone is given the Revoker role in Task Settings.
-- Every revoke is written to tbl_revoke_log with the old values and the reason.

-- room for the new role letter
ALTER TABLE mst_task MODIFY task_roles VARCHAR(10) NOT NULL DEFAULT 'CVAW';

-- Revoker on the tasks that have a revoke button - kept in C V A R W order
UPDATE mst_task SET
  task_roles = IF(task_roles LIKE '%W', CONCAT(LEFT(task_roles, CHAR_LENGTH(task_roles) - 1), 'RW'), CONCAT(task_roles, 'R')),
  task_note = CONCAT(task_note, CASE task_code
    WHEN 'PURCHASE_ORDER' THEN ' Revoker: revoke an approved PO on PO print - back to draft (not once a GRN is made).'
    WHEN 'SUPPLIER' THEN ' Revoker: revoke supplier approval on the approve screen - back to not approved (not once used in a PO / GRN / return).'
    WHEN 'PURCHASE_RETURN' THEN ' Revoker: revoke an approved purchase return on its view page - back to draft.'
    WHEN 'QUOTATION' THEN ' Revoker: revoke an approved quotation on quotation print - back to draft (not once a sales order / invoice / proforma is made).'
    WHEN 'SALES_ORDER' THEN ' Revoker: revoke an approved sales order on its print - back to in approval (not once a DC / invoice is made).'
    WHEN 'CREDIT_SALES' THEN ' Revoker: revoke a credit approval on the receipt screen - back to credit requested (sales order not yet approved).'
    WHEN 'DC' THEN ' Revoker: revoke an approved DC on DC print - back to draft (not once invoiced).'
    WHEN 'SALES_RECEIPT' THEN ' Revoker: revoke a payment confirm / mark as credit - back to payment pending (sales order and credit not yet approved).'
    WHEN 'SALES_REPAIR' THEN ' Revoker: revoke approve (back to in approval) or send for approval (back to draft) on sales repair print.'
    WHEN 'SALARY' THEN ' Revoker: revoke an issued salary on the attendance report - salary and its accounts entry removed, can be issued again.'
    ELSE '' END)
WHERE task_code IN ('PURCHASE_ORDER','SUPPLIER','PURCHASE_RETURN','QUOTATION','SALES_ORDER','CREDIT_SALES','DC','SALES_RECEIPT','SALES_REPAIR','SALARY')
  AND task_roles NOT LIKE '%R%';

CREATE TABLE IF NOT EXISTS tbl_revoke_log (
  revoke_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  revoke_doc VARCHAR(20) NOT NULL,
  task_code VARCHAR(30) NOT NULL,
  doc_id INT UNSIGNED NOT NULL,
  doc_ref VARCHAR(100) NOT NULL DEFAULT '',
  branch_id SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  old_values TEXT NULL,
  revoke_reason VARCHAR(250) NOT NULL DEFAULT '',
  revoked_by SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  revoked_dtm DATETIME NULL,
  PRIMARY KEY (revoke_id),
  KEY revoke_doc (revoke_doc, doc_id)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
