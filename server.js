/**
 * server.js — Simple Web Server & Save Config API for DynamicChart
 * ================================================================
 * ใช้ Pure Node.js (Built-in modules เท่านั้น): http, fs, path
 * ไม่ต้องติดตั้ง npm packages ใดๆ เพิ่มเติม
 *
 * วิธีใช้งาน:
 *   node server.js
 *
 * แล้วเปิดเบราว์เซอร์ไปที่: http://localhost:3000
 */

const http = require('http');
const fs = require('fs');
const path = require('path');

const PORT = process.env.PORT || 3000;
const CONFIG_DIR = path.join(__dirname, 'pageconfig');

// MIME types mapping สำหรับ static files
const MIME_TYPES = {
    '.html': 'text/html; charset=UTF-8',
    '.js':   'text/javascript; charset=UTF-8',
    '.css':  'text/css; charset=UTF-8',
    '.json': 'application/json; charset=UTF-8',
    '.png':  'image/png',
    '.jpg':  'image/jpeg',
    '.svg':  'image/svg+xml',
    '.ico':  'image/x-icon',
};

const server = http.createServer((req, res) => {
    // Enable CORS for flexibility
    res.setHeader('Access-Control-Allow-Origin', '*');
    res.setHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
    res.setHeader('Access-Control-Allow-Headers', 'Content-Type');

    if (req.method === 'OPTIONS') {
        res.writeHead(204);
        res.end();
        return;
    }

    const url = new URL(req.url, `http://${req.headers.host}`);

    // API: บันทึกไฟล์ Config ลงในโฟลเดอร์ pageconfig
    if (req.method === 'POST' && url.pathname === '/api/save-config') {
        let body = '';
        req.on('data', chunk => { body += chunk; });
        req.on('end', () => {
            try {
                const payload = JSON.parse(body);
                
                // ตรวจสอบ/สร้างโฟลเดอร์ pageconfig ถ้ายังไม่มี
                if (!fs.existsSync(CONFIG_DIR)) {
                    fs.mkdirSync(CONFIG_DIR, { recursive: true });
                }

                // ตั้งชื่อไฟล์ (ถ้าส่ง filename มาจะใช้ชื่อนั้น ถ้าไม่มีจะสร้างจาก timestamp)
                let filename = payload.filename;
                if (!filename) {
                    const now = new Date();
                    const timestamp = now.toISOString().replace(/[:.]/g, '-').slice(0, 19);
                    filename = `config_${timestamp}.json`;
                } else if (!filename.endsWith('.json')) {
                    filename += '.json';
                }

                // ล้างค่า custom property ที่ไม่จำเป็นก่อนเซฟ (เช่น filename)
                const configToSave = payload.config || payload;
                if (configToSave.filename) delete configToSave.filename;

                const filePath = path.join(CONFIG_DIR, filename);
                fs.writeFileSync(filePath, JSON.stringify(configToSave, null, 2), 'utf-8');

                console.log(`[Server] Saved config to: ${filePath}`);

                res.writeHead(200, { 'Content-Type': 'application/json; charset=UTF-8' });
                res.end(JSON.stringify({
                    success: true,
                    message: `บันทึกไฟล์ ${filename} เรียบร้อยแล้ว!`,
                    filename: filename,
                    path: `pageconfig/${filename}`
                }));
            } catch (err) {
                console.error('[Server] Save config error:', err);
                res.writeHead(400, { 'Content-Type': 'application/json; charset=UTF-8' });
                res.end(JSON.stringify({ success: false, error: err.message }));
            }
        });
        return;
    }

    // API: ดึงรายชื่อไฟล์ Config ที่บันทึกไว้ใน pageconfig
    if (req.method === 'GET' && url.pathname === '/api/list-configs') {
        try {
            if (!fs.existsSync(CONFIG_DIR)) {
                fs.mkdirSync(CONFIG_DIR, { recursive: true });
            }
            const files = fs.readdirSync(CONFIG_DIR).filter(file => file.endsWith('.json'));
            res.writeHead(200, { 'Content-Type': 'application/json; charset=UTF-8' });
            res.end(JSON.stringify({ success: true, files }));
        } catch (err) {
            res.writeHead(500, { 'Content-Type': 'application/json; charset=UTF-8' });
            res.end(JSON.stringify({ success: false, error: err.message }));
        }
        return;
    }

    // Serve Static Files
    let reqPath = decodeURIComponent(url.pathname);
    if (reqPath === '/') reqPath = '/index.html';

    const safePath = path.normalize(reqPath).replace(/^(\.\.[\/\\])+/, '');
    const filePath = path.join(__dirname, safePath);

    fs.stat(filePath, (err, stats) => {
        if (err || !stats.isFile()) {
            res.writeHead(404, { 'Content-Type': 'text/plain; charset=UTF-8' });
            res.end('404 Not Found');
            return;
        }

        const ext = path.extname(filePath).toLowerCase();
        const contentType = MIME_TYPES[ext] || 'application/octet-stream';

        res.writeHead(200, { 'Content-Type': contentType });
        fs.createReadStream(filePath).pipe(res);
    });
});

server.listen(PORT, () => {
    console.log(`===================================================`);
    console.log(` DynamicChart Web Server is running!`);
    console.log(` Access URL : http://localhost:${PORT}`);
    console.log(` Config Dir : ${CONFIG_DIR}`);
    console.log(`===================================================`);
});
