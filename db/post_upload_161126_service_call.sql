-- 2026-11-16 Service call register - the same flow as Benzear (register a call with its complaints, complete each
-- complaint, send for approval / approve from the print, report). Calls are made in BIE or transferred from Benzear.
-- Transfer: Benzear writes the call and its open complaints straight into these tables (same server) with
-- src_system = 'BENZEAR' and bie_received = 0; BIE then gives it a BIE number, branch, customer and master ids
-- from the names (the two databases have different state / district / city / principal ids). Benzear reads the
-- status back from here. See docs/service_call_transfer.md.
-- Run once in phpMyAdmin on the bie database, after db/post_upload_131126.sql; safe to run again.

CREATE TABLE IF NOT EXISTS tbl_service_call_register (
  sc_id INT(11) NOT NULL AUTO_INCREMENT,
  sc_slno INT(11) NOT NULL DEFAULT 0,
  sc_no VARCHAR(50) NOT NULL DEFAULT '',
  sc_finyr VARCHAR(5) NOT NULL DEFAULT '',
  state_id INT(11) NOT NULL DEFAULT 0,
  district_id INT(11) NOT NULL DEFAULT 0,
  city_id INT(11) NOT NULL DEFAULT 0,
  enq_pincode VARCHAR(10) DEFAULT '',
  sc_date DATE NOT NULL,
  customer_name VARCHAR(150) NOT NULL DEFAULT '',
  supp_id INT(11) NOT NULL DEFAULT 0,
  sc_ref_no VARCHAR(50) NOT NULL DEFAULT '',
  customer_address TEXT DEFAULT NULL,
  enq_add1 VARCHAR(100) DEFAULT '',
  enq_add2 VARCHAR(100) DEFAULT '',
  contact_person VARCHAR(100) DEFAULT '',
  prefix VARCHAR(10) DEFAULT '',
  designation VARCHAR(50) DEFAULT '',
  mobile_no VARCHAR(20) DEFAULT '',
  fax_no VARCHAR(20) DEFAULT '',
  email VARCHAR(100) DEFAULT '',
  enq_gst VARCHAR(15) DEFAULT '',
  enq_pan VARCHAR(10) DEFAULT '',
  enq_website VARCHAR(100) DEFAULT '',
  tel_no VARCHAR(20) DEFAULT '',
  principal_id INT(11) NOT NULL DEFAULT 0,
  service_charge_type TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1 Under Warranty, 2 Pre Installation / Visit, 3 AMC Visit, 4 Paid Service, 5 Paid Installation, 6 Installation Visit',
  invoice_id INT(11) NOT NULL DEFAULT 0,
  received_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  nature_of_complaint TEXT DEFAULT NULL,
  branch_id INT(11) NOT NULL DEFAULT 0,
  sc_status TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1 Pending, 2 Completed, 3 Approved',
  sc_verify_status TINYINT(1) NOT NULL DEFAULT 0,
  sc_verify_by INT(11) NOT NULL DEFAULT 0,
  sc_verify_date_time DATETIME DEFAULT NULL,
  sc_approve_status TINYINT(1) NOT NULL DEFAULT 0,
  sc_approve_by INT(11) NOT NULL DEFAULT 0,
  sc_approve_date_time DATETIME DEFAULT NULL,
  -- transfer from Benzear
  src_system VARCHAR(20) NOT NULL DEFAULT '' COMMENT 'blank = made in BIE, BENZEAR = transferred',
  src_sc_id INT(11) NOT NULL DEFAULT 0,
  src_sc_no VARCHAR(50) NOT NULL DEFAULT '',
  src_state_name VARCHAR(100) NOT NULL DEFAULT '',
  src_district_name VARCHAR(100) NOT NULL DEFAULT '',
  src_city_name VARCHAR(100) NOT NULL DEFAULT '',
  src_principal_name VARCHAR(100) NOT NULL DEFAULT '',
  src_transfer_by VARCHAR(100) NOT NULL DEFAULT '',
  src_transfer_dtm DATETIME DEFAULT NULL,
  bie_received TINYINT(1) NOT NULL DEFAULT 1 COMMENT '0 = transferred, BIE number / customer not given yet',
  created_by INT(11) NOT NULL DEFAULT 0,
  created_dtm DATETIME DEFAULT NULL,
  modify_by INT(11) NOT NULL DEFAULT 0,
  modify_dtm DATETIME DEFAULT NULL,
  PRIMARY KEY (sc_id),
  KEY src (src_system, src_sc_id),
  KEY sc_date (sc_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS tbl_sc_complaints (
  complaint_id INT(11) NOT NULL AUTO_INCREMENT,
  sc_id INT(11) NOT NULL DEFAULT 0,
  complaint_desc TEXT DEFAULT NULL,
  complaint_status TINYINT(1) NOT NULL DEFAULT 0 COMMENT '0 Open, 1 Completed',
  action_taken TINYINT(1) DEFAULT 1 COMMENT '1 Over Phone, 2 On Site Visit',
  action_remarks TEXT DEFAULT NULL,
  chargable DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  chart_no VARCHAR(100) NOT NULL DEFAULT '',
  total_received_amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  engineer_expenses DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  no_of_visits SMALLINT(5) NOT NULL DEFAULT 0,
  solving_date DATE DEFAULT NULL,
  service_engineer_id INT(11) NOT NULL DEFAULT 0,
  customer_comments TEXT DEFAULT NULL,
  sc_review VARCHAR(255) DEFAULT '',
  complete_by INT(11) NOT NULL DEFAULT 0,
  complete_date_time DATETIME DEFAULT NULL,
  src_complaint_id INT(11) NOT NULL DEFAULT 0 COMMENT 'complaint id in Benzear when transferred',
  created_by INT(11) NOT NULL DEFAULT 0,
  created_dtm DATETIME DEFAULT NULL,
  modify_by INT(11) NOT NULL DEFAULT 0,
  modify_dtm DATETIME DEFAULT NULL,
  PRIMARY KEY (complaint_id),
  KEY sc_id (sc_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- spare parts used on an on-site visit (recorded only, stock does not move - as in Benzear)
CREATE TABLE IF NOT EXISTS tbl_sc_complaint_details (
  auto_id INT(11) NOT NULL AUTO_INCREMENT,
  complaint_id INT(11) NOT NULL DEFAULT 0,
  item_id INT(11) NOT NULL DEFAULT 0,
  sc_qty DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  sc_unit VARCHAR(20) DEFAULT '',
  sc_avl_qty DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  PRIMARY KEY (auto_id),
  KEY complaint_id (complaint_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- created / modified by and time, as on the other tables (db/post_upload_111126_audit.sql)
DROP TRIGGER IF EXISTS audit_tbl_service_call_register_bi;
DROP TRIGGER IF EXISTS audit_tbl_service_call_register_bu;
DROP TRIGGER IF EXISTS audit_tbl_sc_complaints_bi;
DROP TRIGGER IF EXISTS audit_tbl_sc_complaints_bu;
CREATE TRIGGER audit_tbl_service_call_register_bi BEFORE INSERT ON tbl_service_call_register FOR EACH ROW SET
  NEW.created_by = IF(NEW.created_by > 0, NEW.created_by, IFNULL(@bie_user_id, 0)),
  NEW.created_dtm = IF(NEW.created_dtm >= '2000-01-01', NEW.created_dtm, CONVERT_TZ(UTC_TIMESTAMP(), '+00:00', '+05:30'));
CREATE TRIGGER audit_tbl_service_call_register_bu BEFORE UPDATE ON tbl_service_call_register FOR EACH ROW SET
  NEW.created_by = IF(OLD.created_by > 0, OLD.created_by, NEW.created_by),
  NEW.created_dtm = IF(OLD.created_dtm >= '2000-01-01', OLD.created_dtm, NEW.created_dtm),
  NEW.modify_by = IF(@bie_user_id > 0, @bie_user_id, NEW.modify_by),
  NEW.modify_dtm = CONVERT_TZ(UTC_TIMESTAMP(), '+00:00', '+05:30');
CREATE TRIGGER audit_tbl_sc_complaints_bi BEFORE INSERT ON tbl_sc_complaints FOR EACH ROW SET
  NEW.created_by = IF(NEW.created_by > 0, NEW.created_by, IFNULL(@bie_user_id, 0)),
  NEW.created_dtm = IF(NEW.created_dtm >= '2000-01-01', NEW.created_dtm, CONVERT_TZ(UTC_TIMESTAMP(), '+00:00', '+05:30'));
CREATE TRIGGER audit_tbl_sc_complaints_bu BEFORE UPDATE ON tbl_sc_complaints FOR EACH ROW SET
  NEW.created_by = IF(OLD.created_by > 0, OLD.created_by, NEW.created_by),
  NEW.created_dtm = IF(OLD.created_dtm >= '2000-01-01', OLD.created_dtm, NEW.created_dtm),
  NEW.modify_by = IF(@bie_user_id > 0, @bie_user_id, NEW.modify_by),
  NEW.modify_dtm = CONVERT_TZ(UTC_TIMESTAMP(), '+00:00', '+05:30');

-- Task Settings: who may send for approval (V), approve (A) and revoke (R) a service call
INSERT INTO mst_task (task_code, task_name, task_module, task_roles, task_note, task_status)
SELECT 'SERVICE_CALL', 'Service Call', 'Service', 'VAR', 'Service call register - send for approval / approve from the print', 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM mst_task WHERE task_code = 'SERVICE_CALL');

-- menu: Service > Service Call Register, Report > Service Call Report (admins see them; give other users in User Rights)
INSERT INTO mst_sub_menu (mm_id, sm_index, sm_name, sm_url, sm_show, sm_class)
SELECT 25, 3, 'Service Call Register', 'service_call_list.php', 1, '' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM mst_sub_menu WHERE sm_url = 'service_call_list.php');
INSERT INTO mst_sub_menu (mm_id, sm_index, sm_name, sm_url, sm_show, sm_class)
SELECT 22, 12, 'Service Call Report', 'service_call_rept.php', 1, '' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM mst_sub_menu WHERE sm_url = 'service_call_rept.php');

-- check
SHOW COLUMNS FROM tbl_service_call_register LIKE 'src%';
SELECT sm_id, sm_name, sm_url FROM mst_sub_menu WHERE sm_url LIKE 'service_call%';
