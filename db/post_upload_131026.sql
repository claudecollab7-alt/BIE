-- 2026-10-13 Task Settings - who creates, verifies, approves and views, kept in tables instead of code
-- Run once in phpMyAdmin on the bie database, together with the 13-10-26 files. Safe to run again.
--
-- Roles: C creator / editor, V verifier, A approver (also rejects / cancels), W viewer (sees every branch)
-- branch_id 0 = all branches. Admin users (type A) can do everything and need no rows.
-- The starting rows below give every user exactly the access the code gave them before.
-- tbl_task_user is no longer read; it is left as it is and can be dropped later.

CREATE TABLE IF NOT EXISTS mst_task (
  task_id SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
  task_code VARCHAR(30) NOT NULL,
  task_name VARCHAR(100) NOT NULL,
  task_module VARCHAR(50) NOT NULL DEFAULT '',
  task_roles VARCHAR(4) NOT NULL DEFAULT 'CVAW',
  task_note VARCHAR(500) NOT NULL DEFAULT '',
  task_status TINYINT NOT NULL DEFAULT 1,
  created_by SMALLINT NOT NULL DEFAULT 0,
  created_dtm DATETIME NULL,
  modify_by SMALLINT NOT NULL DEFAULT 0,
  modify_dtm DATETIME NULL,
  PRIMARY KEY (task_id),
  UNIQUE KEY task_code (task_code)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS tbl_task_assign (
  assign_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  task_id SMALLINT UNSIGNED NOT NULL,
  task_role CHAR(1) NOT NULL,
  usr_id SMALLINT UNSIGNED NOT NULL,
  branch_id SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  created_by SMALLINT NOT NULL DEFAULT 0,
  created_dtm DATETIME NULL,
  PRIMARY KEY (assign_id),
  UNIQUE KEY task_role_user_branch (task_id, task_role, usr_id, branch_id),
  KEY usr_id (usr_id)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- starting rows only go in on the first run, so later edits in Task Settings are not undone
SET @fresh = (SELECT COUNT(*) = 0 FROM tbl_task_assign);
-- second batch (sales order approve / payment confirm open to all, cancel split off) - once, also where the first batch already ran
SET @fix2 = (SELECT COUNT(*) = 0 FROM mst_task WHERE task_code = 'SO_CANCEL');

INSERT IGNORE INTO mst_task (task_code, task_name, task_module, task_roles, task_note, task_status, created_by, created_dtm) VALUES
('PURCHASE_ORDER', 'Purchase Order', 'Purchase', 'CAW', 'Creator: New PO button and edit links on the PO list. Approver: approve / reject on PO print, dashboard PO in approval. Viewer: PO list shows every branch.', 1, 1, NOW()),
('SUPPLIER', 'Supplier', 'Purchase', 'CAW', 'Creator: item mapping link and delete of any supplier. Approver: approve supplier, dashboard supplier in approval. Viewer: supplier list shows every branch.', 1, 1, NOW()),
('PO_PREPARE', 'Prepare Purchase Order', 'Purchase', 'W', 'Viewer: prepare PO list shows every branch.', 1, 1, NOW()),
('PURCHASE_RETURN', 'Purchase Return', 'Purchase', 'AW', 'Approver: approve / reject purchase return. Viewer: purchase return list shows every branch.', 1, 1, NOW()),
('QUOTATION', 'Quotation', 'Sales', 'AW', 'Approver: approve / reject on quotation print (for the branch given), dashboard quotation in approval. Viewer: quotation list shows every branch.', 1, 1, NOW()),
('SALES_ORDER', 'Sales Order', 'Sales', 'AW', 'Approver: approve / reject on sales order print, dashboard sales order in approval. Viewer: sales order list shows every branch.', 1, 1, NOW()),
('SO_CANCEL', 'Sales Order Cancel', 'Sales', 'A', 'Approver: cancel link on the sales order list.', 1, 1, NOW()),
('CREDIT_SALES', 'Credit Sales Approval', 'Sales', 'A', 'Approver: approve credit sales orders, dashboard credit sales order in approval.', 1, 1, NOW()),
('DC', 'Delivery Challan', 'Sales', 'AW', 'Approver: approve / reject on DC print (for the branch given). Viewer: DC list shows every branch.', 1, 1, NOW()),
('INVOICE', 'Invoice', 'Sales', 'W', 'Viewer: invoice list shows every branch.', 1, 1, NOW()),
('SALES_RECEIPT', 'Sales Order in Accounts', 'Accounts', 'VW', 'Verifier: confirm payment and mark as credit sales, dashboard payment to confirm. Viewer: sales order in accounts list shows every branch.', 1, 1, NOW()),
('GRN', 'GRN', 'Stores', 'AW', 'Approver: cancel GRN. Viewer: GRN list shows every branch.', 1, 1, NOW()),
('GRN_PAYMENT', 'GRN Payment', 'Accounts', 'AW', 'Approver: edit paid amount on GRN payment, dashboard GRN payment pending. Viewer: GRN payment list shows every branch.', 1, 1, NOW()),
('STORE_INDENT', 'Store Indent', 'Stores', 'AW', 'Approver: shown as the send-to-purchase approver on new indents. Viewer: store indent list shows every branch.', 1, 1, NOW()),
('STOCK_LOCATION', 'Stock Location', 'Stores', 'C', 'Creator: update location on the sales order stock list.', 1, 1, NOW()),
('ITEM', 'Item', 'Item Masters', 'CA', 'Creator: edit, price history, branch-wise and delete links on the item list. Approver: approve new branch items, dashboard new item in approval.', 1, 1, NOW()),
('SALES_REPAIR', 'Sales Repair', 'Service', 'VA', 'Verifier: send for approval on sales repair print. Approver: approve.', 1, 1, NOW()),
('EMPLOYEE', 'Employee / Staff / Labour', 'HR Management', 'W', 'Viewer: employee, staff, labour, work details, salary setting, advance and debit note lists show every branch.', 1, 1, NOW()),
('SALARY', 'Salary Issue', 'Attendance and Salary', 'A', 'Approver: issue salary on the PF, without PF and OT salary reports.', 1, 1, NOW());

-- head office users (branch 1) saw every branch on the lists
INSERT IGNORE INTO tbl_task_assign (task_id, task_role, usr_id, branch_id, created_by, created_dtm)
SELECT t.task_id, 'W', u.usr_id, 0, 1, NOW()
FROM mst_task t JOIN tbl_user u ON u.branch_id = 1 AND u.usr_type <> 'A'
WHERE @fresh = 1 AND t.task_code IN ('PURCHASE_ORDER','SUPPLIER','PO_PREPARE','PURCHASE_RETURN','QUOTATION','SALES_ORDER','DC','INVOICE','SALES_RECEIPT','GRN','GRN_PAYMENT','STORE_INDENT','EMPLOYEE');

-- head office users had the New PO button / PO edit links, supplier item mapping and delete, and GRN cancel
INSERT IGNORE INTO tbl_task_assign (task_id, task_role, usr_id, branch_id, created_by, created_dtm)
SELECT t.task_id, x.task_role, u.usr_id, 0, 1, NOW()
FROM (SELECT 'PURCHASE_ORDER' AS task_code, 'C' AS task_role UNION ALL SELECT 'SUPPLIER', 'C' UNION ALL SELECT 'GRN', 'A') x
JOIN mst_task t ON t.task_code = x.task_code
JOIN tbl_user u ON u.branch_id = 1 AND u.usr_type <> 'A'
WHERE @fresh = 1;

-- quotation and DC approve / reject were open to every user of the document's own branch
INSERT IGNORE INTO tbl_task_assign (task_id, task_role, usr_id, branch_id, created_by, created_dtm)
SELECT t.task_id, 'A', u.usr_id, u.branch_id, 1, NOW()
FROM mst_task t JOIN tbl_user u ON u.usr_type <> 'A' AND u.branch_id > 0
WHERE @fresh = 1 AND t.task_code IN ('QUOTATION','DC');

-- confirm payment / mark credit: holders of the Sales Order in Accounts menu
INSERT IGNORE INTO tbl_task_assign (task_id, task_role, usr_id, branch_id, created_by, created_dtm)
SELECT DISTINCT t.task_id, 'V', u.usr_id, 0, 1, NOW()
FROM mst_task t
JOIN mst_sub_menu s ON TRIM(s.sm_url) = 'lst_sales_receipt.php'
JOIN tbl_user_rights r ON r.sm_id = s.sm_id
JOIN tbl_user u ON u.usr_id = r.usr_id AND u.usr_type <> 'A'
WHERE @fresh = 1 AND t.task_code = 'SALES_RECEIPT';

-- users listed in the old tbl_task_user rows (1 PO approval, 2 supplier approval, 3 the other dashboard tiles)
INSERT IGNORE INTO tbl_task_assign (task_id, task_role, usr_id, branch_id, created_by, created_dtm)
SELECT t.task_id, 'A', u.usr_id, 0, 1, NOW()
FROM tbl_task_user o
JOIN (SELECT 1 AS old_id, 'PURCHASE_ORDER' AS task_code UNION ALL SELECT 2, 'SUPPLIER' UNION ALL SELECT 3, 'CREDIT_SALES'
      UNION ALL SELECT 3, 'ITEM' UNION ALL SELECT 3, 'GRN_PAYMENT' UNION ALL SELECT 3, 'QUOTATION') m ON m.old_id = o.task_id
JOIN mst_task t ON t.task_code = m.task_code
JOIN tbl_user u ON FIND_IN_SET(u.usr_id, REPLACE(o.user_id, ' ', '')) AND u.usr_type <> 'A'
WHERE @fresh = 1;

-- update location was coded for user 9 as well as admin
INSERT IGNORE INTO tbl_task_assign (task_id, task_role, usr_id, branch_id, created_by, created_dtm)
SELECT t.task_id, 'C', u.usr_id, 0, 1, NOW()
FROM mst_task t JOIN tbl_user u ON u.usr_id = 9 AND u.usr_type <> 'A'
WHERE @fresh = 1 AND t.task_code = 'STOCK_LOCATION';

-- second batch: the sales order approve / reject buttons and the payment screen had no user check, and live
-- data shows Kerala, Sales, Stores, Accounts approving and Purchase, Rajapalayam confirming - so every user:
-- head office users (they see every branch's orders) for all branches, the others for their own branch
INSERT IGNORE INTO tbl_task_assign (task_id, task_role, usr_id, branch_id, created_by, created_dtm)
SELECT t.task_id, x.task_role, u.usr_id, IF(u.branch_id = 1, 0, u.branch_id), 1, NOW()
FROM (SELECT 'SALES_ORDER' AS task_code, 'A' AS task_role UNION ALL SELECT 'SALES_RECEIPT', 'V') x
JOIN mst_task t ON t.task_code = x.task_code
JOIN tbl_user u ON u.usr_type <> 'A' AND u.branch_id > 0
WHERE @fix2 = 1;

UPDATE mst_task SET task_note = 'Approver: approve / reject on sales order print, dashboard sales order in approval. Viewer: sales order list shows every branch.'
WHERE @fix2 = 1 AND task_code = 'SALES_ORDER';
UPDATE mst_task SET task_note = 'Verifier: confirm payment and mark as credit sales, dashboard payment to confirm. Viewer: sales order in accounts list shows every branch.'
WHERE @fix2 = 1 AND task_code = 'SALES_RECEIPT';

-- menu: Settings > Task Settings (admin sees every menu)
INSERT INTO mst_sub_menu (mm_id, sm_index, sm_name, sm_url, sm_show, sm_class)
SELECT 1, 5, 'Task Settings', 'mst_task.php', 1, ''
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM mst_sub_menu WHERE sm_url = 'mst_task.php');

-- check: tasks and starting rows per task / role
SELECT t.task_code, a.task_role, GROUP_CONCAT(CONCAT(a.usr_id, IF(a.branch_id = 0, '', CONCAT('@', a.branch_id))) ORDER BY a.usr_id) AS users
FROM mst_task t LEFT JOIN tbl_task_assign a ON a.task_id = t.task_id
GROUP BY t.task_id, a.task_role ORDER BY t.task_id, a.task_role;
