<?php
// ============================================================
//  setup.php — Rental Monitoring System Installer
//  Run once: http://localhost/rental_system/setup.php
//  DELETE this file after setup is complete!
// ============================================================

session_start();
$step    = (int)($_GET['step'] ?? 1);
$message = '';
$error   = '';

// ── STEP 2: Test connection ──────────────────────────────────
if ($step === 2 && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $host = trim($_POST['host'] ?? 'localhost');
    $user = trim($_POST['db_user'] ?? 'root');
    $pass = $_POST['db_pass'] ?? '';
    $name = trim($_POST['db_name'] ?? 'rental_monitoring');

    try {
        $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
        $_SESSION['setup'] = compact('host','user','pass','name');
        header('Location: setup.php?step=3');
        exit;
    } catch (PDOException $e) {
        $error = 'Connection failed: ' . $e->getMessage();
        $step  = 1;
    }
}

// ── STEP 3: Create DB, tables, seed ─────────────────────────
if ($step === 3 && isset($_SESSION['setup'])) {
    ['host'=>$host,'user'=>$user,'pass'=>$pass,'name'=>$name] = $_SESSION['setup'];
    try {
        $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);

        // Create database
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `$name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $pdo->exec("USE `$name`");

        // Create tables
        $pdo->exec("CREATE TABLE IF NOT EXISTS users (
            id         INT AUTO_INCREMENT PRIMARY KEY,
            first_name VARCHAR(80)  NOT NULL,
            last_name  VARCHAR(80)  NOT NULL,
            email      VARCHAR(150) NOT NULL UNIQUE,
            password   VARCHAR(255) NOT NULL,
            role       ENUM('admin','tenant') DEFAULT 'tenant',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB");

        $pdo->exec("CREATE TABLE IF NOT EXISTS rentals (
            id          INT AUTO_INCREMENT PRIMARY KEY,
            unit        VARCHAR(100) NOT NULL,
            type        ENUM('Apartment','House','Condo','Studio','Commercial') NOT NULL,
            address     TEXT NOT NULL,
            tenant      VARCHAR(120) DEFAULT NULL,
            contact     VARCHAR(30)  DEFAULT NULL,
            rent        DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            due_day     TINYINT DEFAULT NULL,
            lease_start DATE DEFAULT NULL,
            lease_end   DATE DEFAULT NULL,
            status      ENUM('Active','Vacant','Overdue','Maintenance') NOT NULL DEFAULT 'Vacant',
            notes       TEXT DEFAULT NULL,
            created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB");

        $pdo->exec("CREATE TABLE IF NOT EXISTS payments (
            id        INT AUTO_INCREMENT PRIMARY KEY,
            rental_id INT NOT NULL,
            amount    DECIMAL(10,2) NOT NULL,
            paid_date DATE NOT NULL,
            method    ENUM('Cash','GCash','Bank Transfer','Check') DEFAULT 'Cash',
            note      TEXT DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (rental_id) REFERENCES rentals(id) ON DELETE CASCADE
        ) ENGINE=InnoDB");

        // Admin account from form, or use default
        $adminFirst = trim($_POST['admin_first'] ?? 'Admin');
        $adminLast  = trim($_POST['admin_last']  ?? 'User');
        $adminEmail = trim($_POST['admin_email'] ?? 'admin@rental.com');
        $adminPass  = $_POST['admin_pass'] ?? 'Admin@1234';
        if (strlen($adminPass) < 8) $adminPass = 'Admin@1234';
        $hash = password_hash($adminPass, PASSWORD_BCRYPT);

        // Check if admin already exists
        $existing = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $existing->execute([$adminEmail]);
        if (!$existing->fetch()) {
            $pdo->prepare("INSERT INTO users (first_name, last_name, email, password, role) VALUES (?,?,?,?,'admin')")
                ->execute([$adminFirst, $adminLast, $adminEmail, $hash]);
        }

        // Seed sample rentals only if table is empty
        $count = $pdo->query("SELECT COUNT(*) FROM rentals")->fetchColumn();
        if ($count == 0) {
            $pdo->exec("INSERT INTO rentals (unit,type,address,tenant,contact,rent,due_day,lease_start,lease_end,status,notes) VALUES
                ('Unit 4B','Apartment','123 Mabini St, Quezon City','Maria Santos','09171234567',8500,15,'2024-01-15','2025-01-15','Active','2-bedroom unit. Includes parking.'),
                ('Unit 1A','Studio','45 Rizal Ave, Manila','Jose Reyes','09282345678',5500,1,'2024-03-01','2025-03-01','Active',''),
                ('Lot 7','House','89 Bonifacio Drive, Makati','Ana Cruz','09393456789',18000,10,'2023-06-10','2025-06-10','Overdue','3 months overdue. Follow up needed.'),
                ('Unit 2C','Condo','56 Ayala Blvd, Makati',NULL,NULL,12000,5,NULL,NULL,'Vacant','Freshly renovated.'),
                ('Store 3','Commercial','Cubao Commercial Complex, QC','Pedro Lim','09504567890',25000,20,'2024-05-20','2026-05-20','Active','Commercial space for retail.'),
                ('Unit 6D','Apartment','12 Luna St, Pasig',NULL,NULL,7000,1,NULL,NULL,'Maintenance','Plumbing repairs ongoing.')
            ");
        }

        // Write config.php
        $configContent = "<?php
define('DB_HOST', " . var_export($host, true) . ");
define('DB_NAME', " . var_export($name, true) . ");
define('DB_USER', " . var_export($user, true) . ");
define('DB_PASS', " . var_export($pass, true) . ");
define('DB_CHARSET', 'utf8mb4');

function getDB(): PDO {
    static \$pdo = null;
    if (\$pdo === null) {
        \$dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
        \$options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            \$pdo = new PDO(\$dsn, DB_USER, DB_PASS, \$options);
        } catch (PDOException \$e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Database error: ' . \$e->getMessage()]);
            exit;
        }
    }
    return \$pdo;
}

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if (\$_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

session_start();

function isLoggedIn(): bool {
    return isset(\$_SESSION['user_id']);
}

function requireAuth(): void {
    if (!isLoggedIn()) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Unauthorized. Please log in.']);
        exit;
    }
}
";
        file_put_contents(__DIR__ . '/api/config.php', $configContent);
        $_SESSION['setup_done'] = [
            'email' => $adminEmail,
            'pass'  => $adminPass,
            'db'    => $name,
        ];
        header('Location: setup.php?step=4');
        exit;
    } catch (PDOException $e) {
        $error = 'Setup failed: ' . $e->getMessage();
        $step  = 3;
    }
}
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Rental Monitoring System — Setup</title>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600&family=Syne:wght@700&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{
  --teal-50:#E1F5EE;--teal-200:#5DCAA5;--teal-400:#1D9E75;
  --teal-600:#0F6E56;--teal-800:#085041;
  --white:#fff;--gray-50:#F8FAF9;--gray-100:#EEF1F0;
  --gray-200:#DDE4E0;--gray-300:#C2CEC9;--gray-500:#6B807A;
  --gray-700:#38504A;--gray-900:#162220;
  --red:#DC2626;--red-bg:#FEE2E2;--green:#059669;--green-bg:#D1FAE5;
}
body{font-family:'DM Sans',sans-serif;background:var(--gray-50);min-height:100vh;
  display:flex;align-items:center;justify-content:center;padding:24px;
  background-image:radial-gradient(circle at 10% 20%,var(--teal-50) 0%,transparent 40%),
                   radial-gradient(circle at 90% 80%,var(--teal-50) 0%,transparent 40%);}
.card{background:var(--white);border-radius:20px;box-shadow:0 8px 48px rgba(15,110,86,.13);
  width:100%;max-width:540px;overflow:hidden;border:1px solid var(--teal-50);}
.card-top{background:var(--teal-800);padding:32px 36px;color:white;}
.brand{display:flex;align-items:center;gap:12px;margin-bottom:20px;}
.brand-icon{width:44px;height:44px;background:var(--teal-400);border-radius:10px;
  display:flex;align-items:center;justify-content:center;}
.brand-icon svg{width:22px;height:22px;fill:white;}
.brand h1{font-family:'Syne',sans-serif;font-size:16px;font-weight:700;line-height:1.2;}
.brand p{font-size:11px;opacity:.5;letter-spacing:.4px;text-transform:uppercase;}
.card-top h2{font-family:'Syne',sans-serif;font-size:20px;font-weight:700;margin-bottom:6px;}
.card-top p{font-size:13.5px;opacity:.7;line-height:1.6;}

/* STEPS BAR */
.steps{display:flex;align-items:center;gap:0;margin-top:24px;}
.step-dot{display:flex;align-items:center;gap:8px;}
.dot{width:28px;height:28px;border-radius:50%;border:2px solid rgba(255,255,255,.3);
  display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:600;
  color:rgba(255,255,255,.5);flex-shrink:0;transition:all .3s;}
.dot.done{background:var(--teal-400);border-color:var(--teal-400);color:white;}
.dot.active{background:white;border-color:white;color:var(--teal-800);}
.step-label{font-size:11.5px;color:rgba(255,255,255,.5);white-space:nowrap;}
.step-label.active{color:white;font-weight:500;}
.step-line{flex:1;height:1px;background:rgba(255,255,255,.2);margin:0 10px;}

.card-body{padding:32px 36px;}
.alert{padding:12px 16px;border-radius:8px;font-size:13.5px;margin-bottom:20px;display:flex;align-items:flex-start;gap:10px;}
.alert svg{width:16px;height:16px;flex-shrink:0;margin-top:1px;}
.alert-error{background:var(--red-bg);color:var(--red);border:1px solid #FECACA;}
.alert-error svg{stroke:var(--red);fill:none;stroke-width:2;stroke-linecap:round;}
.alert-success{background:var(--green-bg);color:var(--green);border:1px solid #A7F3D0;}
.field{margin-bottom:18px;}
.field label{display:block;font-size:12.5px;font-weight:500;color:var(--gray-700);margin-bottom:7px;}
.field small{display:block;font-size:11.5px;color:var(--gray-500);margin-top:4px;}
.field input{width:100%;padding:10px 14px;border:1.5px solid var(--gray-200);border-radius:8px;
  font-family:'DM Sans',sans-serif;font-size:13.5px;color:var(--gray-900);
  background:var(--gray-50);outline:none;transition:all .18s;}
.field input:focus{border-color:var(--teal-200);background:white;box-shadow:0 0 0 3px rgba(29,158,117,.10);}
.field input::placeholder{color:var(--gray-300);}
.divider{height:1px;background:var(--gray-100);margin:22px 0;}
.section-label{font-size:11px;font-weight:600;letter-spacing:.6px;text-transform:uppercase;
  color:var(--gray-400);margin-bottom:14px;}
.btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;
  padding:11px 22px;border-radius:8px;font-family:'DM Sans',sans-serif;
  font-size:14px;font-weight:600;cursor:pointer;border:none;transition:all .18s;width:100%;}
.btn-primary{background:var(--teal-400);color:white;box-shadow:0 4px 14px rgba(29,158,117,.25);}
.btn-primary:hover{background:var(--teal-600);transform:translateY(-1px);}
.btn svg{width:16px;height:16px;stroke:currentColor;fill:none;stroke-width:2;stroke-linecap:round;stroke-linejoin:round;}

/* CHECKLIST */
.checklist{list-style:none;display:flex;flex-direction:column;gap:10px;margin-bottom:24px;}
.checklist li{display:flex;align-items:center;gap:10px;font-size:13.5px;color:var(--gray-700);}
.check-icon{width:22px;height:22px;border-radius:50%;background:var(--green-bg);
  display:flex;align-items:center;justify-content:center;flex-shrink:0;}
.check-icon svg{width:12px;height:12px;stroke:var(--green);fill:none;stroke-width:2.5;stroke-linecap:round;stroke-linejoin:round;}

/* CREDENTIALS BOX */
.creds{background:var(--teal-50);border:1px solid var(--teal-100);border-radius:10px;padding:18px 20px;margin-bottom:22px;}
.creds p{font-size:12px;color:var(--teal-600);font-weight:600;text-transform:uppercase;letter-spacing:.5px;margin-bottom:10px;}
.cred-row{display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;}
.cred-row:last-child{margin-bottom:0;}
.cred-row span{font-size:13px;color:var(--gray-500);}
.cred-row strong{font-size:13px;color:var(--teal-800);font-weight:600;}

/* WARNING BOX */
.warn-box{background:#FEF3C7;border:1px solid #FDE68A;border-radius:8px;
  padding:12px 16px;font-size:13px;color:#92400E;display:flex;align-items:flex-start;gap:8px;margin-bottom:20px;}
.warn-box svg{width:15px;height:15px;stroke:#D97706;fill:none;stroke-width:2;stroke-linecap:round;flex-shrink:0;margin-top:1px;}

.req-check{display:flex;align-items:center;gap:8px;font-size:13px;padding:8px 0;border-bottom:1px solid var(--gray-100);}
.req-check:last-child{border-bottom:none;}
.req-ok{color:var(--green);font-weight:500;}
.req-fail{color:var(--red);font-weight:500;}
.req-icon{width:20px;height:20px;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0;}
.req-icon.ok{background:var(--green-bg);}
.req-icon.fail{background:var(--red-bg);}
.req-icon svg{width:10px;height:10px;stroke-width:2.5;stroke-linecap:round;stroke-linejoin:round;fill:none;}
.req-icon.ok svg{stroke:var(--green);}
.req-icon.fail svg{stroke:var(--red);}
</style>
</head>
<body>
<div class="card">
  <div class="card-top">
    <div class="brand">
      <div class="brand-icon"><svg viewBox="0 0 24 24"><path d="M3 9.5L12 3l9 6.5V20a1 1 0 01-1 1H4a1 1 0 01-1-1V9.5z"/><path d="M9 21V12h6v9" fill="rgba(255,255,255,0.3)"/></svg></div>
      <div><h1>Rental Monitoring System</h1><p>Setup Installer</p></div>
    </div>

    <?php
    $titles = [1=>'System Check & Database Info', 2=>'', 3=>'Installing…', 4=>'Setup Complete!'];
    $subs   = [1=>'Configure your database connection below.', 2=>'', 3=>'Creating database and tables…', 4=>'Your system is ready to use.'];
    ?>
    <h2><?= $titles[$step] ?></h2>
    <p><?= $subs[$step] ?></p>

    <div class="steps">
      <div class="step-dot">
        <div class="dot <?= $step >= 1 ? ($step > 1 ? 'done' : 'active') : '' ?>">
          <?= $step > 1 ? '✓' : '1' ?>
        </div>
        <span class="step-label <?= $step === 1 ? 'active' : '' ?>">Database</span>
      </div>
      <div class="step-line"></div>
      <div class="step-dot">
        <div class="dot <?= $step >= 3 ? ($step > 3 ? 'done' : 'active') : '' ?>">
          <?= $step > 3 ? '✓' : '2' ?>
        </div>
        <span class="step-label <?= $step === 3 ? 'active' : '' ?>">Install</span>
      </div>
      <div class="step-line"></div>
      <div class="step-dot">
        <div class="dot <?= $step >= 4 ? 'done' : '' ?>">
          <?= $step >= 4 ? '✓' : '3' ?>
        </div>
        <span class="step-label <?= $step === 4 ? 'active' : '' ?>">Done</span>
      </div>
    </div>
  </div>

  <div class="card-body">
    <?php if ($error): ?>
    <div class="alert alert-error">
      <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
      <?= htmlspecialchars($error) ?>
    </div>
    <?php endif; ?>

    <?php if ($step === 1): ?>
    <!-- STEP 1: REQUIREMENTS + DB FORM -->
    <div class="section-label">System Requirements</div>
    <?php
      $phpOk  = version_compare(PHP_VERSION, '7.4', '>=');
      $pdoOk  = extension_loaded('pdo_mysql');
      $sessOk = session_status() !== PHP_SESSION_DISABLED;
    ?>
    <div style="margin-bottom:22px;">
      <div class="req-check">
        <div class="req-icon <?= $phpOk?'ok':'fail' ?>"><svg viewBox="0 0 24 24"><?= $phpOk?'<polyline points="20 6 9 17 4 12"/>':'<line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>' ?></svg></div>
        <span class="<?= $phpOk?'req-ok':'req-fail' ?>">PHP <?= PHP_VERSION ?> <?= $phpOk?'✓ (8.0+ recommended)':'✗ Requires PHP 7.4+' ?></span>
      </div>
      <div class="req-check">
        <div class="req-icon <?= $pdoOk?'ok':'fail' ?>"><svg viewBox="0 0 24 24"><?= $pdoOk?'<polyline points="20 6 9 17 4 12"/>':'<line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>' ?></svg></div>
        <span class="<?= $pdoOk?'req-ok':'req-fail' ?>">PDO MySQL <?= $pdoOk?'✓ Enabled':'✗ Not found — enable pdo_mysql in php.ini' ?></span>
      </div>
      <div class="req-check">
        <div class="req-icon <?= $sessOk?'ok':'fail' ?>"><svg viewBox="0 0 24 24"><?= $sessOk?'<polyline points="20 6 9 17 4 12"/>':'<line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>' ?></svg></div>
        <span class="<?= $sessOk?'req-ok':'req-fail' ?>">PHP Sessions <?= $sessOk?'✓ Available':'✗ Disabled' ?></span>
      </div>
    </div>

    <div class="divider"></div>
    <div class="section-label">Database Configuration</div>

    <form method="POST" action="setup.php?step=2">
      <div class="field">
        <label>Database Host</label>
        <input type="text" name="host" value="localhost" required>
        <small>Usually <strong>localhost</strong> for XAMPP</small>
      </div>
      <div class="field">
        <label>MySQL Username</label>
        <input type="text" name="db_user" value="root" required>
        <small>Default XAMPP username is <strong>root</strong></small>
      </div>
      <div class="field">
        <label>MySQL Password</label>
        <input type="password" name="db_pass" placeholder="Leave blank for XAMPP default">
        <small>Leave empty if using default XAMPP (no password)</small>
      </div>
      <div class="field">
        <label>Database Name</label>
        <input type="text" name="db_name" value="rental_monitoring" required>
        <small>Will be created automatically if it doesn't exist</small>
      </div>

      <div class="divider"></div>
      <div class="section-label">Admin Account</div>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
        <div class="field" style="margin-bottom:0">
          <label>First Name</label>
          <input type="text" name="admin_first" value="Admin" required>
        </div>
        <div class="field" style="margin-bottom:0">
          <label>Last Name</label>
          <input type="text" name="admin_last" value="User" required>
        </div>
      </div>
      <div class="field" style="margin-top:14px">
        <label>Admin Email</label>
        <input type="email" name="admin_email" value="admin@rental.com" required>
      </div>
      <div class="field">
        <label>Admin Password</label>
        <input type="password" name="admin_pass" placeholder="Min. 8 characters" minlength="8">
        <small>Leave blank to use default: <strong>Admin@1234</strong></small>
      </div>
      <button type="submit" class="btn btn-primary">
        <svg viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
        Test Connection & Continue
      </button>
    </form>

    <?php elseif ($step === 3 && isset($_SESSION['setup'])): ?>
    <!-- STEP 3: RUN INSTALL -->
    <ul class="checklist">
      <li><div class="check-icon"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></div>Connection verified</li>
      <li><div class="check-icon"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></div>Ready to create database & tables</li>
      <li><div class="check-icon"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></div>Admin account will be created</li>
      <li><div class="check-icon"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></div>Sample rental data will be seeded</li>
      <li><div class="check-icon"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></div>config.php will be auto-generated</li>
    </ul>
    <form method="POST" action="setup.php?step=3">
      <input type="hidden" name="admin_first" value="<?= htmlspecialchars($_SESSION['setup']['user'] ?? 'Admin') ?>">
      <input type="hidden" name="admin_last"  value="User">
      <input type="hidden" name="admin_email" value="admin@rental.com">
      <input type="hidden" name="admin_pass"  value="">
      <button type="submit" class="btn btn-primary">
        <svg viewBox="0 0 24 24"><polyline points="16 16 12 12 8 16"/><line x1="12" y1="12" x2="12" y2="21"/><path d="M20.39 18.39A5 5 0 0018 9h-1.26A8 8 0 103 16.3"/></svg>
        Run Installation
      </button>
    </form>

    <?php elseif ($step === 4): ?>
    <!-- STEP 4: DONE -->
    <?php $done = $_SESSION['setup_done'] ?? []; ?>
    <ul class="checklist">
      <li><div class="check-icon"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></div>Database created successfully</li>
      <li><div class="check-icon"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></div>Tables: users, rentals, payments</li>
      <li><div class="check-icon"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></div>Admin account configured</li>
      <li><div class="check-icon"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></div>Sample data seeded (6 rental units)</li>
      <li><div class="check-icon"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></div>api/config.php generated</li>
    </ul>

    <div class="creds">
      <p>Your Login Credentials</p>
      <div class="cred-row"><span>Email</span><strong><?= htmlspecialchars($done['email'] ?? 'admin@rental.com') ?></strong></div>
      <div class="cred-row"><span>Password</span><strong><?= htmlspecialchars($done['pass'] ?? 'Admin@1234') ?></strong></div>
      <div class="cred-row"><span>Database</span><strong><?= htmlspecialchars($done['db'] ?? 'rental_monitoring') ?></strong></div>
    </div>

    <div class="warn-box">
      <svg viewBox="0 0 24 24"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
      <span><strong>Security:</strong> Delete <code>setup.php</code> from your server after logging in!</span>
    </div>

    <a href="index.html" class="btn btn-primary" style="text-decoration:none;display:flex;">
      <svg viewBox="0 0 24 24"><path d="M15 3h4a2 2 0 012 2v14a2 2 0 01-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
      Go to Login Page
    </a>
    <?php endif; ?>
  </div>
</div>
</body>
</html>
