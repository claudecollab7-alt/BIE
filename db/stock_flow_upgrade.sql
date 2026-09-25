-- ============================================================================
--  BIE - Stock Flow ledger upgrade + Stock Adjustment module
-- ----------------------------------------------------------------------------
--  Run this ONCE against the BIE database before deploying the PHP changes.
--
--  NOT re-runnable as a whole: the ALTER TABLE statements in section 1 will
--  error with "Duplicate column name" / "Duplicate key name" on a second run.
--  That is harmless - it means section 1 is already applied. Sections 2 and 3
--  (CREATE TABLE IF NOT EXISTS, guarded INSERTs) are safe to repeat.
-- ============================================================================

-- ---------------------------------------------------------------------------
-- 1. tbl_stock_flow : new audit columns
-- ---------------------------------------------------------------------------
--    trans_dir      'I' = stock went IN (+), 'O' = stock went OUT (-)
--                   trans_qty stays a POSITIVE magnitude for every row, the
--                   direction is carried here so reports can render the sign.
--    trans_remarks  free text reason (mandatory for ADJ rows)
--    stock_field    the tbl_item_stock column that was actually changed
--                   (ho_stock / kl_stock / rjpm_stock ...) - makes the ledger
--                   self-describing when new branches are added later.

ALTER TABLE `tbl_stock_flow`
  ADD COLUMN `trans_dir` CHAR(1) NOT NULL DEFAULT '' COMMENT 'I = IN (+), O = OUT (-)' AFTER `trans_type`,
  ADD COLUMN `trans_remarks` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'Reason / narration' AFTER `pend_qty`,
  ADD COLUMN `stock_field` VARCHAR(30) NOT NULL DEFAULT '' COMMENT 'tbl_item_stock column updated' AFTER `branch_id`;

-- Quantities must match tbl_item_stock which is decimal(10,3).
-- int -> decimal is a widening change, existing values are preserved exactly.
ALTER TABLE `tbl_stock_flow`
  MODIFY `before_qty` DECIMAL(12,3) NOT NULL,
  MODIFY `trans_qty`  DECIMAL(12,3) NOT NULL COMMENT 'always positive, see trans_dir',
  MODIFY `rcvd_qty`   DECIMAL(12,3) NOT NULL,
  MODIFY `after_qty`  DECIMAL(12,3) NOT NULL,
  MODIFY `reje_qty`   DECIMAL(12,3) NOT NULL,
  MODIFY `pend_qty`   DECIMAL(12,3) NOT NULL,
  MODIFY `trans_type` VARCHAR(10) NOT NULL COMMENT 'GRN,INV,ADJ,OPEN,RET';

-- Reports always filter on item + branch + date, so index them.
ALTER TABLE `tbl_stock_flow`
  ADD INDEX `idx_sf_item_branch_date` (`item_id`, `branch_id`, `trans_date`),
  ADD INDEX `idx_sf_trans` (`trans_type`, `trans_id`);

-- Backfill trans_dir for the rows that already exist.
-- Historically GRN added stock and INV removed it; trans_qty was stored
-- positive in both cases, so the type alone tells us the direction.
UPDATE `tbl_stock_flow` SET `trans_dir` = 'I' WHERE `trans_dir` = '' AND `trans_type` = 'GRN';
UPDATE `tbl_stock_flow` SET `trans_dir` = 'O' WHERE `trans_dir` = '' AND `trans_type` IN ('INV','SALE');

-- Backfill stock_field from the branch master where it is resolvable.
UPDATE `tbl_stock_flow` sf
  JOIN `mst_branch` b ON b.`branch_id` = sf.`branch_id`
   SET sf.`stock_field` = b.`branch_stock_field`
 WHERE sf.`stock_field` = '';


-- ---------------------------------------------------------------------------
-- 2. tbl_stock_adjustment : header for every manual stock correction
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `tbl_stock_adjustment` (
  `adj_id`      INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `adj_refno`   VARCHAR(25)      NOT NULL COMMENT 'ADJ/<branch>/<yy-yy>/<slno>',
  `adj_slno`    INT(10) UNSIGNED NOT NULL DEFAULT 0,
  `adj_finyr`   VARCHAR(10)      NOT NULL DEFAULT '',
  `adj_date`    DATE             NOT NULL,
  `branch_id`   TINYINT(4)       NOT NULL,
  `stock_field` VARCHAR(30)      NOT NULL COMMENT 'tbl_item_stock column updated',
  `item_id`     INT(10) UNSIGNED NOT NULL,
  `adj_type`    CHAR(1)          NOT NULL COMMENT 'I = Increase, D = Decrease',
  `adj_qty`     DECIMAL(12,3)    NOT NULL COMMENT 'always positive magnitude',
  `before_qty`  DECIMAL(12,3)    NOT NULL,
  `after_qty`   DECIMAL(12,3)    NOT NULL,
  `item_price`  DECIMAL(12,2)    NOT NULL DEFAULT 0.00,
  `adj_reason`  VARCHAR(255)     NOT NULL COMMENT 'mandatory',
  `adj_status`  TINYINT(1)       NOT NULL DEFAULT 1 COMMENT '1 = active, 0 = deleted',
  `created_by`  INT(10) UNSIGNED NOT NULL,
  `created_dtm` DATETIME         NOT NULL,
  PRIMARY KEY (`adj_id`),
  KEY `idx_adj_branch_date` (`branch_id`, `adj_date`),
  KEY `idx_adj_item` (`item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- ---------------------------------------------------------------------------
-- 3. Menu entry - "Stock Adjustment" under Stores (mm_id 20)
-- ---------------------------------------------------------------------------
INSERT INTO `mst_sub_menu` (`sm_id`, `mm_id`, `sm_index`, `sm_name`, `sm_url`, `sm_query`, `sm_show`, `sm_class`, `sm_default`)
SELECT 147, 20, 7, 'Stock Adjustment', 'stock_adjustment.php', NULL, 1, '', NULL
  FROM DUAL
 WHERE NOT EXISTS (SELECT 1 FROM `mst_sub_menu` WHERE `sm_url` = 'stock_adjustment.php');

-- Non-admin users need an explicit right row before the menu shows up.
-- Grant it to every user who can already reach the Stores menu (mm_id 20).
INSERT INTO `tbl_user_rights` (`usr_id`, `mm_id`, `sm_id`)
SELECT DISTINCT ur.`usr_id`, 20, 147
  FROM `tbl_user_rights` ur
 WHERE ur.`mm_id` = 20
   AND NOT EXISTS (SELECT 1 FROM `tbl_user_rights` x
                    WHERE x.`usr_id` = ur.`usr_id` AND x.`sm_id` = 147);


-- ---------------------------------------------------------------------------
-- 4. Menu entry - "Item Stock List" under Item Masters (mm_id 3)
-- ---------------------------------------------------------------------------
INSERT INTO `mst_sub_menu` (`sm_id`, `mm_id`, `sm_index`, `sm_name`, `sm_url`, `sm_query`, `sm_show`, `sm_class`, `sm_default`)
SELECT 148, 3, 11, 'Item Stock List', 'lst_item_stock.php', NULL, 1, '', NULL
  FROM DUAL
 WHERE NOT EXISTS (SELECT 1 FROM `mst_sub_menu` WHERE `sm_url` = 'lst_item_stock.php');

-- Grant it to every user who can already reach the Item Masters menu (mm_id 3).
INSERT INTO `tbl_user_rights` (`usr_id`, `mm_id`, `sm_id`)
SELECT DISTINCT ur.`usr_id`, 3, 148
  FROM `tbl_user_rights` ur
 WHERE ur.`mm_id` = 3
   AND NOT EXISTS (SELECT 1 FROM `tbl_user_rights` x
                    WHERE x.`usr_id` = ur.`usr_id` AND x.`sm_id` = 148);
