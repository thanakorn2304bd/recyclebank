-- =========================================================
-- Recycle Bank Schema (ตามเอกสาร ตาราง 3.2 - 3.12)
-- MySQL 8+
-- =========================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ลบตาราง (เรียงจากลูก -> แม่)
DROP TABLE IF EXISTS log_activity;
DROP TABLE IF EXISTS transaction_detail;
DROP TABLE IF EXISTS `transaction`;
DROP TABLE IF EXISTS material_price;
DROP TABLE IF EXISTS material;
DROP TABLE IF EXISTS material_category;
DROP TABLE IF EXISTS user_account;
DROP TABLE IF EXISTS member;
DROP TABLE IF EXISTS household;
DROP TABLE IF EXISTS staff;
DROP TABLE IF EXISTS community;

SET FOREIGN_KEY_CHECKS = 1;

-- =========================================================
-- 1) community (ตาราง 3.4)
-- =========================================================
CREATE TABLE community (
  community_id   CHAR(2)      NOT NULL,
  community_name VARCHAR(100) NOT NULL,
  PRIMARY KEY (community_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =========================================================
-- 2) staff (ตาราง 3.5)
-- =========================================================
CREATE TABLE staff (
  staff_id  INT(11)      NOT NULL AUTO_INCREMENT,
  full_name VARCHAR(100) NOT NULL,
  phone     VARCHAR(20)  NULL,
  position  VARCHAR(50)  NULL,
  PRIMARY KEY (staff_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =========================================================
-- 3) household (ตาราง 3.2)
-- หมายเหตุ: created_by จะผูก FK ทีหลัง เพราะอ้าง user_account
-- =========================================================
CREATE TABLE household (
  household_id        INT(11)       NOT NULL AUTO_INCREMENT,
  account_no          CHAR(10)       NOT NULL,
  house_no            VARCHAR(20)    NOT NULL,
  village_no          VARCHAR(10)    NULL,
  community_id        CHAR(2)        NOT NULL,
  phone               VARCHAR(20)    NULL,
  contact_person      VARCHAR(100)   NOT NULL,
  register_date       DATE           NOT NULL,
  active_status       ENUM('pending','active','inactive') NOT NULL,
  accumulated_months  INT(2)         NOT NULL,
  total_balance       DECIMAL(10,2)  NOT NULL,
  created_by          INT(11)        NULL,
  PRIMARY KEY (household_id),
  UNIQUE KEY uq_household_account_no (account_no),
  KEY idx_household_community_id (community_id),
  KEY idx_household_created_by (created_by),
  CONSTRAINT fk_household_community
    FOREIGN KEY (community_id) REFERENCES community(community_id)
    ON UPDATE RESTRICT ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =========================================================
-- 4) member (ตาราง 3.3)
-- =========================================================
CREATE TABLE member (
  member_id    INT(11)      NOT NULL AUTO_INCREMENT,
  household_id INT(11)      NOT NULL,
  full_name    VARCHAR(100) NOT NULL,
  id_card      VARCHAR(13)  NOT NULL,
  is_head      BOOLEAN      NOT NULL,
  relation     VARCHAR(50)  NOT NULL,
  PRIMARY KEY (member_id),
  KEY idx_member_household_id (household_id),
  CONSTRAINT fk_member_household
    FOREIGN KEY (household_id) REFERENCES household(household_id)
    ON UPDATE RESTRICT ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =========================================================
-- 5) user_account (ตาราง 3.12)
-- หมายเหตุ: household_id จะผูก FK ทีหลัง (วนกับ household.created_by)
-- =========================================================
CREATE TABLE user_account (
  user_id      INT(11) NOT NULL AUTO_INCREMENT,
  username     VARCHAR(50)  NOT NULL,
  password     VARCHAR(255) NOT NULL,
  role         ENUM('member','staff','admin') NOT NULL,
  household_id INT(11) NULL,
  staff_id     INT(11) NULL,
  created_at   DATETIME NOT NULL,
  last_login   DATETIME NULL,
  is_active    BOOLEAN  NOT NULL,
  PRIMARY KEY (user_id),
  UNIQUE KEY uq_user_account_username (username),
  KEY idx_user_account_household_id (household_id),
  KEY idx_user_account_staff_id (staff_id),
  CONSTRAINT fk_user_account_staff
    FOREIGN KEY (staff_id) REFERENCES staff(staff_id)
    ON UPDATE RESTRICT ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ผูก FK ที่วนกัน (household.created_by -> user_account.user_id)
ALTER TABLE household
  ADD CONSTRAINT fk_household_created_by
  FOREIGN KEY (created_by) REFERENCES user_account(user_id)
  ON UPDATE RESTRICT ON DELETE SET NULL;

-- ผูก FK (user_account.household_id -> household.household_id)
ALTER TABLE user_account
  ADD CONSTRAINT fk_user_account_household
  FOREIGN KEY (household_id) REFERENCES household(household_id)
  ON UPDATE RESTRICT ON DELETE SET NULL;

-- =========================================================
-- 6) material_category (ตาราง 3.7)
-- =========================================================
CREATE TABLE material_category (
  category_id   INT(11)      NOT NULL AUTO_INCREMENT,
  category_name VARCHAR(100) NOT NULL,
  PRIMARY KEY (category_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =========================================================
-- 7) material (ตาราง 3.6)
-- =========================================================
CREATE TABLE material (
  material_id    INT(11)      NOT NULL AUTO_INCREMENT,
  category_id    INT(11)      NOT NULL,
  material_name  VARCHAR(100) NOT NULL,
  unit           VARCHAR(20)  NOT NULL,
  description    VARCHAR(255) NOT NULL,
  is_active      BOOLEAN      NOT NULL,
  PRIMARY KEY (material_id),
  KEY idx_material_category_id (category_id),
  CONSTRAINT fk_material_category
    FOREIGN KEY (category_id) REFERENCES material_category(category_id)
    ON UPDATE RESTRICT ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =========================================================
-- 8) material_price (ตาราง 3.8)
-- =========================================================
CREATE TABLE material_price (
  price_id        INT(11)      NOT NULL AUTO_INCREMENT,
  material_id     INT(11)      NOT NULL,
  price           DECIMAL(10,2) NOT NULL,
  effective_date  DATE         NOT NULL,
  expired_date    DATE         NULL,
  created_by      INT(11)      NOT NULL,
  created_at      DATETIME     NOT NULL,
  PRIMARY KEY (price_id),
  KEY idx_material_price_material_id (material_id),
  KEY idx_material_price_created_by (created_by),
  CONSTRAINT fk_material_price_material
    FOREIGN KEY (material_id) REFERENCES material(material_id)
    ON UPDATE RESTRICT ON DELETE RESTRICT,
  CONSTRAINT fk_material_price_created_by
    FOREIGN KEY (created_by) REFERENCES user_account(user_id)
    ON UPDATE RESTRICT ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =========================================================
-- 9) transaction (ตาราง 3.9)
-- (transaction เป็นคำสงวนในบางระบบ เลยครอบด้วย backticks)
-- =========================================================
CREATE TABLE `transaction` (
  transaction_id   INT(11)       NOT NULL AUTO_INCREMENT,
  household_id     INT(11)       NOT NULL,
  transaction_date DATE          NOT NULL,
  transaction_type ENUM('deposit','withdraw') NOT NULL,
  total_weight     DECIMAL(10,2) NOT NULL,
  total_amount     DECIMAL(10,2) NOT NULL,
  recorded_by      INT(11)       NOT NULL,
  PRIMARY KEY (transaction_id),
  KEY idx_transaction_household_id (household_id),
  KEY idx_transaction_recorded_by (recorded_by),
  CONSTRAINT fk_transaction_household
    FOREIGN KEY (household_id) REFERENCES household(household_id)
    ON UPDATE RESTRICT ON DELETE RESTRICT,
  CONSTRAINT fk_transaction_recorded_by
    FOREIGN KEY (recorded_by) REFERENCES user_account(user_id)
    ON UPDATE RESTRICT ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =========================================================
-- 10) transaction_detail (ตาราง 3.10)
-- =========================================================
CREATE TABLE transaction_detail (
  detail_id      INT(11)       NOT NULL AUTO_INCREMENT,
  transaction_id INT(11)       NOT NULL,
  material_id    INT(11)       NOT NULL,
  weight         DECIMAL(10,2) NOT NULL,
  price_per_unit DECIMAL(10,2) NOT NULL,
  amount         DECIMAL(10,2) NOT NULL,
  PRIMARY KEY (detail_id),
  KEY idx_transaction_detail_transaction_id (transaction_id),
  KEY idx_transaction_detail_material_id (material_id),
  CONSTRAINT fk_transaction_detail_transaction
    FOREIGN KEY (transaction_id) REFERENCES `transaction`(transaction_id)
    ON UPDATE RESTRICT ON DELETE CASCADE,
  CONSTRAINT fk_transaction_detail_material
    FOREIGN KEY (material_id) REFERENCES material(material_id)
    ON UPDATE RESTRICT ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =========================================================
-- 11) log_activity (ตาราง 3.11)
-- =========================================================
CREATE TABLE log_activity (
  log_id     INT(11)      NOT NULL AUTO_INCREMENT,
  user_id    INT(11)      NOT NULL,
  action     VARCHAR(255) NOT NULL,
  `timestamp` DATETIME    NOT NULL,
  module     VARCHAR(50)  NOT NULL,
  PRIMARY KEY (log_id),
  KEY idx_log_activity_user_id (user_id),
  CONSTRAINT fk_log_activity_user
    FOREIGN KEY (user_id) REFERENCES user_account(user_id)
    ON UPDATE RESTRICT ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `household`
  ADD COLUMN `created_at` DATETIME NULL DEFAULT CURRENT_TIMESTAMP,
  ADD COLUMN `updated_at` DATETIME NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

ALTER TABLE `member`
  ADD COLUMN `created_at` DATETIME NULL DEFAULT CURRENT_TIMESTAMP,
  ADD COLUMN `updated_at` DATETIME NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

ALTER TABLE `material`
  ADD COLUMN `created_at` DATETIME NULL DEFAULT CURRENT_TIMESTAMP,
  ADD COLUMN `updated_at` DATETIME NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

ALTER TABLE `material_category`
  ADD COLUMN `created_at` DATETIME NULL DEFAULT CURRENT_TIMESTAMP,
  ADD COLUMN `updated_at` DATETIME NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

-- =========================================================
-- ADD INDEXES (PERFORMANCE & FK)
-- =========================================================

-- household
ALTER TABLE household
  ADD INDEX idx_household_community_id (community_id),
  ADD INDEX idx_household_created_by (created_by);

-- member
ALTER TABLE member
  ADD INDEX idx_member_household_id (household_id),
  ADD INDEX idx_member_id_card (id_card);

-- user_account
ALTER TABLE user_account
  ADD INDEX idx_user_account_household_id (household_id),
  ADD INDEX idx_user_account_staff_id (staff_id);

-- material
ALTER TABLE material
  ADD INDEX idx_material_category_id (category_id),
  ADD INDEX idx_material_name (material_name);

-- material_price
ALTER TABLE material_price
  ADD INDEX idx_material_price_material_id (material_id),
  ADD INDEX idx_material_price_created_by (created_by),
  ADD INDEX idx_material_price_effective (material_id, effective_date, expired_date);

-- transaction
ALTER TABLE `transaction`
  ADD INDEX idx_transaction_household_id (household_id),
  ADD INDEX idx_transaction_recorded_by (recorded_by),
  ADD INDEX idx_transaction_date (transaction_date),
  ADD INDEX idx_transaction_household_date (household_id, transaction_date);

-- transaction_detail
ALTER TABLE transaction_detail
  ADD INDEX idx_transaction_detail_transaction_id (transaction_id),
  ADD INDEX idx_transaction_detail_material_id (material_id);

-- log_activity
ALTER TABLE log_activity
  ADD INDEX idx_log_activity_user_id (user_id),
  ADD INDEX idx_log_activity_timestamp (`timestamp`),
  ADD INDEX idx_log_activity_user_time (user_id, `timestamp`),
  ADD INDEX idx_log_activity_module (module);
