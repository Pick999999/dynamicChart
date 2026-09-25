<?php
/**
 * configDB.php
 * ไฟล์ตั้งค่าการเชื่อมต่อฐานข้อมูล MySQL (Database Configuration)
 * 
 * เมื่อนำโปรเจกต์ไปติดตั้งบน Web Hosting / Server จริง
 * สามารถเข้ามาแก้ไขค่า Host, User, Password, Database Name ได้ที่ไฟล์นี้ไฟล์เดียว
 */

// ----------------------------------------------------
// ⚙️ MySQL Database Settings (ตั้งค่าฐานข้อมูลที่นี่)
// ----------------------------------------------------
if (!defined('DB_HOST')) define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');     // Host / Server IP (เช่น localhost หรือ 127.0.0.1)
if (!defined('DB_PORT')) define('DB_PORT', getenv('DB_PORT') ?: 3306);            // Port (ปกติคือ 3306)
if (!defined('DB_NAME')) define('DB_NAME', getenv('DB_NAME') ?: 'dynamic_chart'); // ชื่อฐานข้อมูล
if (!defined('DB_USER')) define('DB_USER', getenv('DB_USER') ?: 'root');          // ชื่อผู้ใช้งาน Database
if (!defined('DB_PASS')) define('DB_PASS', getenv('DB_PASS') !== false ? getenv('DB_PASS') : ''); // รหัสผ่าน Database
if (!defined('DB_CHARSET')) define('DB_CHARSET', 'utf8mb4');                      // ชุดภาษา

// คืนค่า Array สำหรับการเรียกใช้งานแบบดึง Config
return [
    'host'     => DB_HOST,
    'port'     => DB_PORT,
    'dbname'   => DB_NAME,
    'user'     => DB_USER,
    'password' => DB_PASS,
    'charset'  => DB_CHARSET,
];
