-- ============================================================
--  RENTAL MONITORING SYSTEM — Full Database v3
--  Changes vs v2:
--    • users.tenant_id  — links login account to tenants row
--    • payments.status  — added 'Overdue' enum value
--    • payments UNIQUE  — (rental_id, month_for) prevents dupes
--    • payments.updated_at column added
--  phpMyAdmin: Import > Choose File > Go
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE DATABASE IF NOT EXISTS rental_monitoring
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE rental_monitoring;

-- ── USERS ────────────────────────────────────────────────────
DROP TABLE IF EXISTS users;
CREATE TABLE users (
  id          INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  first_name  VARCHAR(80)     NOT NULL,
  last_name   VARCHAR(80)     NOT NULL,
  email       VARCHAR(150)    NOT NULL,
  password    VARCHAR(255)    NOT NULL,
  role        ENUM('admin','tenant') NOT NULL DEFAULT 'tenant',
  tenant_id   INT UNSIGNED        NULL DEFAULT NULL
                COMMENT 'Links this login account to a tenants record',
  created_at  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_users_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── TENANTS ──────────────────────────────────────────────────
DROP TABLE IF EXISTS tenants;
CREATE TABLE tenants (
  id          INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  first_name  VARCHAR(80)     NOT NULL,
  last_name   VARCHAR(80)     NOT NULL,
  email       VARCHAR(150)        NULL DEFAULT NULL,
  contact     VARCHAR(30)         NULL DEFAULT NULL,
  address     TEXT                NULL DEFAULT NULL,
  id_type     VARCHAR(60)         NULL DEFAULT NULL,
  id_number   VARCHAR(80)         NULL DEFAULT NULL,
  notes       TEXT                NULL DEFAULT NULL,
  created_at  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── RENTALS ──────────────────────────────────────────────────
DROP TABLE IF EXISTS rentals;
CREATE TABLE rentals (
  id          INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  unit        VARCHAR(100)    NOT NULL,
  type        ENUM('Apartment','House','Condo','Studio','Commercial') NOT NULL,
  address     TEXT            NOT NULL,
  tenant_id   INT UNSIGNED        NULL DEFAULT NULL,
  rent        DECIMAL(10,2)   NOT NULL DEFAULT 0.00,
  due_day     TINYINT UNSIGNED    NULL DEFAULT NULL
                COMMENT 'Day of month rent is due (1-31)',
  lease_start DATE                NULL DEFAULT NULL,
  lease_end   DATE                NULL DEFAULT NULL,
  status      ENUM('Active','Vacant','Overdue','Maintenance') NOT NULL DEFAULT 'Vacant',
  notes       TEXT                NULL DEFAULT NULL,
  created_at  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_rentals_tenant (tenant_id),
  KEY idx_rentals_status (status),
  CONSTRAINT fk_rentals_tenant
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── PAYMENTS ─────────────────────────────────────────────────
DROP TABLE IF EXISTS payments;
CREATE TABLE payments (
  id          INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  rental_id   INT UNSIGNED    NOT NULL,
  tenant_id   INT UNSIGNED        NULL DEFAULT NULL,
  amount      DECIMAL(10,2)   NOT NULL DEFAULT 0.00,
  paid_date   DATE            NOT NULL,
  month_for   CHAR(7)             NULL DEFAULT NULL
                COMMENT 'YYYY-MM — which month this covers',
  method      ENUM('Cash','GCash','Bank Transfer','Check','Other') NOT NULL DEFAULT 'Cash',
  status      ENUM('Paid','Partial','Pending','Overdue')          NOT NULL DEFAULT 'Paid',
  note        TEXT                NULL DEFAULT NULL,
  created_at  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_payment_rental_month (rental_id, month_for)
    COMMENT 'Prevents duplicate month entries per rental',
  KEY idx_payments_tenant  (tenant_id),
  KEY idx_payments_month   (month_for),
  KEY idx_payments_status  (status),
  CONSTRAINT fk_payments_rental
    FOREIGN KEY (rental_id) REFERENCES rentals(id) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_payments_tenant
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ═══════════════════════════════════════════════════════════
--  SEED DATA
-- ═══════════════════════════════════════════════════════════

-- Admin account  (password: password)
INSERT INTO users (first_name,last_name,email,password,role,tenant_id) VALUES
('Admin','User','admin@rental.com',
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
 'admin', NULL);

-- Tenants
INSERT INTO tenants (id,first_name,last_name,email,contact,address,id_type,id_number,notes) VALUES
(1,'Maria','Santos',  'maria.santos@email.com', '09171234567','45 Mabini St, Quezon City',    'PhilSys ID',    'PSN-001-2021','Reliable payer. Long-term tenant.'),
(2,'Jose', 'Reyes',   'jose.reyes@email.com',   '09282345678','12 Rizal Ave, Manila',          'Driver License','DL-PH-88821', 'Works at BGC. Quiet tenant.'),
(3,'Ana',  'Cruz',    'ana.cruz@email.com',      '09393456789','89 Bonifacio Drive, Makati',   'Passport',      'P-PH1234567', '3 months overdue. Formal notice sent.'),
(4,'Pedro','Lim',     'pedro.lim@email.com',     '09504567890','Cubao Commercial Complex, QC', 'PhilSys ID',    'PSN-002-2022','Commercial tenant.'),
(5,'Rosa', 'Dela Cruz','rosa.dc@email.com',      '09615678901','3 Burgos St, Pasig',           'Driver License','DL-PH-77743', 'New tenant. Move-in March 2025.');

-- Rental units
INSERT INTO rentals (id,unit,type,address,tenant_id,rent,due_day,lease_start,lease_end,status,notes) VALUES
(1,'Unit 4B','Apartment','123 Mabolo St, Cebu City',     1, 8500,15,'2024-01-15','2026-01-15','Active',      '2-bedroom. Includes parking.'),
(2,'Unit 1A','Studio',   '123 Mabolo St, Cebu City',     2, 5500, 1,'2024-03-01','2026-03-01','Active',      'Ground floor studio.'),
(3,'Lot 7',  'House',    '89 Bonifacio Drive, Cebu City',3,18000,10,'2023-06-10','2025-06-10','Overdue',     '3 months overdue. Notice sent.'),
(4,'Unit 2C','Condo',    '56 Ayala Blvd, Cebu City', NULL,12000, 5,NULL,NULL,                'Vacant',      'Freshly renovated. Available now.'),
(5,'Store 3','Commercial','Cubao Complex, Cebu City',    4,25000,20,'2024-05-20','2026-05-20','Active',      'Ground floor commercial space.'),
(6,'Unit 6D','Apartment','12 Luna St, Cebu City',    NULL, 7000, 1,NULL,NULL,                'Maintenance', 'Plumbing repairs ongoing.'),
(7,'Unit 3F','Apartment','78 Katipunan Ave, Cebu City',  5, 9000, 1,'2025-03-01','2026-03-01','Active',      'Top floor. Great view.');

-- Tenant portal login accounts  (password: password)
-- tenant_id column links each user to their tenants record
INSERT INTO users (first_name,last_name,email,password,role,tenant_id) VALUES
('Maria','Santos',  'maria.santos@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','tenant',1),
('Jose', 'Reyes',   'jose.reyes@email.com',   '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','tenant',2),
('Ana',  'Cruz',    'ana.cruz@email.com',      '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','tenant',3),
('Pedro','Lim',     'pedro.lim@email.com',     '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','tenant',4),
('Rosa', 'Dela Cruz','rosa.dc@email.com',      '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','tenant',5);

-- Payments
INSERT INTO payments (rental_id,tenant_id,amount,paid_date,month_for,method,status,note) VALUES
-- Unit 4B - Maria (all paid)
(1,1, 8500,'2025-01-15','2025-01','GCash',        'Paid',   'January rent'),
(1,1, 8500,'2025-02-14','2025-02','GCash',        'Paid',   'February rent'),
(1,1, 8500,'2025-03-15','2025-03','Cash',         'Paid',   'March rent'),
(1,1, 8500,'2025-04-15','2025-04','GCash',        'Paid',   'April rent'),
-- Unit 1A - Jose (all paid)
(2,2, 5500,'2025-01-01','2025-01','Bank Transfer','Paid',   'January rent'),
(2,2, 5500,'2025-02-01','2025-02','Bank Transfer','Paid',   'February rent'),
(2,2, 5500,'2025-03-03','2025-03','Bank Transfer','Paid',   'March - 2 days late'),
(2,2, 5500,'2025-04-01','2025-04','Bank Transfer','Paid',   'April rent'),
-- Store 3 - Pedro (all paid)
(5,4,25000,'2025-01-20','2025-01','Check',        'Paid',   'January'),
(5,4,25000,'2025-02-20','2025-02','Check',        'Paid',   'February'),
(5,4,25000,'2025-03-20','2025-03','Check',        'Paid',   'March'),
(5,4,25000,'2025-04-20','2025-04','Check',        'Paid',   'April'),
-- Unit 3F - Rosa (new tenant, 2 months)
(7,5, 9000,'2025-03-01','2025-03','GCash',        'Paid',   'March'),
(7,5, 9000,'2025-04-01','2025-04','GCash',        'Paid',   'April'),
-- Lot 7 - Ana (overdue)
(3,3,18000,'2025-01-10','2025-01','Cash',         'Paid',   'January rent'),
(3,3, 9000,'2025-02-20','2025-02','Cash',         'Partial','Partial payment — short 9000'),
(3,3,    0,'2025-03-10','2025-03','Cash',         'Pending','No payment received'),
(3,3,    0,'2025-04-10','2025-04','Cash',         'Pending','No payment received');

SET FOREIGN_KEY_CHECKS = 1;

-- ═══════════════════════════════════════════════════════════
--  QUICK REFERENCE
--  Admin login:       admin@rental.com   / password
--  Tenant logins:     maria.santos@...   / password
--                     jose.reyes@...     / password
--                     ana.cruz@...       / password  (overdue)
--                     pedro.lim@...      / password
--                     rosa.dc@...        / password
--
--  Import:  mysql -u root -p < database.sql
--           or phpMyAdmin > Import > Choose File > Go
-- ═══════════════════════════════════════════════════════════