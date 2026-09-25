@echo off
:: ปรับสีหน้าต่าง CMD (พื้นหลังสีดำ ตัวหนังสือสีเขียวเพื่อให้อ่านง่าย)
color 0A
title SQLite Database Initializer (PHP)

echo ===================================================
echo   กำลังเริ่มต้นการทำงาน SQLite database ผ่าน PHP...
echo ===================================================
echo.

:: ตรวจสอบว่ามีคำสั่ง php ในระบบหรือไม่
where php >nul 2>nul
if %errorlevel% neq 0 (
    color 0C
    echo [ERROR] ไม่พบคำสั่ง 'php' ในระบบเครื่องของคุณ!
    echo กรุณาตรวจสอบว่า:
    echo 1. ได้ติดตั้ง PHP ในเครื่องเรียบร้อยแล้ว
    echo 2. ได้เพิ่ม Path ของโฟลเดอร์ PHP ใน Environment Variables (System Path) แล้ว
    echo.
    goto end
)

:: รันคำสั่ง php เพื่อเริ่มต้นการสร้าง database และ tables
php init_db.php

:end
echo.
echo กดปุ่มใดๆ เพื่อปิดหน้าต่างนี้...
pause >nul
