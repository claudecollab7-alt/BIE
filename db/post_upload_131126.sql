-- 2026-11-13 extra units (multiple UOM): conversion to the main unit, and two clean-ups of the item extra-unit lists.
-- tbl_item_uom: how many of the main unit one extra unit is (1 MTR = 100 NOS -> 100), set on the Item Price screen.
-- GRN uses it to add stock in the main unit (a PO line in MTR on a NOS item was added to stock as NOS, unconverted).
-- Run once in phpMyAdmin on the bie database, after db/post_upload_111126_audit.sql; safe to run again.

CREATE TABLE IF NOT EXISTS tbl_item_uom (
  item_uom_id INT(11) NOT NULL AUTO_INCREMENT,
  item_id INT(11) NOT NULL,
  uom_id INT(11) NOT NULL,
  uom_factor DECIMAL(14,4) NOT NULL DEFAULT 0,
  created_by INT(11) NOT NULL DEFAULT 0,
  created_dtm DATETIME NULL DEFAULT NULL,
  modify_by INT(11) NOT NULL DEFAULT 0,
  modify_dtm DATETIME NULL DEFAULT NULL,
  PRIMARY KEY (item_uom_id),
  UNIQUE KEY item_uom (item_id, uom_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- created / modified by and time, as on the other tables (db/post_upload_111126_audit.sql)
DROP TRIGGER IF EXISTS audit_tbl_item_uom_bi;
DROP TRIGGER IF EXISTS audit_tbl_item_uom_bu;
CREATE TRIGGER audit_tbl_item_uom_bi BEFORE INSERT ON tbl_item_uom FOR EACH ROW SET
  NEW.created_by = IF(NEW.created_by > 0, NEW.created_by, IFNULL(@bie_user_id, 0)),
  NEW.created_dtm = IF(NEW.created_dtm >= '2000-01-01', NEW.created_dtm, CONVERT_TZ(UTC_TIMESTAMP(), '+00:00', '+05:30'));
CREATE TRIGGER audit_tbl_item_uom_bu BEFORE UPDATE ON tbl_item_uom FOR EACH ROW SET
  NEW.created_by = IF(OLD.created_by > 0, OLD.created_by, NEW.created_by),
  NEW.created_dtm = IF(OLD.created_dtm >= '2000-01-01', OLD.created_dtm, NEW.created_dtm),
  NEW.modify_by = IF(@bie_user_id > 0, @bie_user_id, NEW.modify_by),
  NEW.modify_dtm = CONVERT_TZ(UTC_TIMESTAMP(), '+00:00', '+05:30');

-- extra-unit list that is only the main unit itself, or a unit that does not exist (7 items on the 11-11 copy) -> none
UPDATE tbl_item_details i
SET i.multi_uom_id = '0'
WHERE i.multi_uom_id REGEXP '^[0-9]+$' AND i.multi_uom_id <> '0'
  AND (i.multi_uom_id = i.item_uom OR NOT EXISTS (SELECT 1 FROM mst_uom u WHERE u.uom_id = i.multi_uom_id AND u.uom_id > 0));

-- check: the table, and the trigger pair
SHOW COLUMNS FROM tbl_item_uom;
SHOW TRIGGERS WHERE `Trigger` LIKE 'audit\_tbl\_item\_uom\_%';
