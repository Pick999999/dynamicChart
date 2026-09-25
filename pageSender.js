/**
 * ============================================================================
 * Page Sender Utility (pageSender.js)
 * ============================================================================
 * ดึงข้อมูลโครงสร้าง HTML ทั้งหมดของหน้าปัจจุบัน บีบอัดเป็นไฟล์ Gzip (.gz)
 * และส่งไปที่ Server ปลายทางพร้อมข้อมูลชื่อไฟล์, URL และไฟล์ Zip ผ่าน FormData
 */
document.addEventListener('DOMContentLoaded', async () => {
    // 1. เตรียมข้อมูลหน้าเว็บ (Metadata)
    const fullUrl = window.location.href; // URL หรือ Path ทั้งหมด (เช่น file:///D:/Rust/derivAuth/testauth.html)
    const pathName = window.location.pathname; // Path ของไฟล์ (เช่น /D:/Rust/derivAuth/testauth.html)
    const fileName = pathName.substring(pathName.lastIndexOf('/') + 1) || 'index.html'; // ชื่อไฟล์ (เช่น testauth.html)

    // ตรวจสอบและระบุที่อยู่โฟลเดอร์เครื่องต้นทาง (Physical Path) กรณีรันบน Local Server
    let targetPath = pathName;
    if (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') {
        targetPath = 'D:\\Rust\\newAPI_Graph\\' + fileName;
    }

    // 2. ดึงข้อมูลโครงสร้าง HTML ทั้งหมดของหน้าเว็บ (รวม Doctype และ Comment ที่อยู่นอก <html>)
    const htmlContent = Array.from(document.childNodes)
        .map(node => {
            if (node.nodeType === Node.ELEMENT_NODE) return node.outerHTML;
            if (node.nodeType === Node.TEXT_NODE) return node.nodeValue;
            if (node.nodeType === Node.COMMENT_NODE) return `<!--${node.nodeValue}-->`;
            if (node.nodeType === Node.DOCUMENT_TYPE_NODE) {
                let doctype = `<!DOCTYPE ${node.name}`;
                if (node.publicId) doctype += ` PUBLIC "${node.publicId}"`;
                if (node.systemId) doctype += ` "${node.systemId}"`;
                doctype += '>';
                return doctype;
            }
            return '';
        })
        .join('');

    // 3. ฟังก์ชันภายในสำหรับบีบอัดข้อความเป็น Gzip (ใช้ Native Browser API)
    async function compressToGzip(text) {
        const stream = new Blob([text]).stream();
        const compressedStream = stream.pipeThrough(new CompressionStream('gzip'));
        const response = new Response(compressedStream);
        return await response.blob();
    }

    // ฟังก์ชันสำหรับโหลด Script ไลบรารีภายนอกแบบ Dynamic
    function loadScript(src) {
        return new Promise((resolve, reject) => {
            const script = document.createElement('script');
            script.src = src;
            script.onload = resolve;
            script.onerror = reject;
            document.head.appendChild(script);
        });
    }

    try {
        // 4. โหลด html2canvas จาก CDN
        await loadScript('https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js');

        // 5. ทำการแคปเจอร์หน้าจอ (ใช้ document.body หรือ element อื่นที่ต้องการ)
        const canvas = await html2canvas(document.body, {
            useCORS: true,       // รองรับการดึงภาพภายนอกแบบข้าม Domain
            allowTaint: true,
            logging: false       // ปิด log ของ html2canvas ใน console
        });

        // แปลงภาพ Canvas เป็น Blob (PNG)
        const screenshotBlob = await new Promise((resolve) => canvas.toBlob(resolve, 'image/png'));

        // ดึงรายการไฟล์ JavaScript ทั้งหมดในหน้าปัจจุบัน
        const scriptTags = Array.from(document.querySelectorAll('script'));
        const jsFiles = scriptTags
            .map(script => script.getAttribute('src'))
            .filter(src => src)
            .map(src => {
                let cleanSrc = src.split('?')[0];
                return cleanSrc.substring(cleanSrc.lastIndexOf('/') + 1);
            })
            .filter(name => name.endsWith('.js'));
        const uniqueJsFiles = [...new Set(jsFiles)];
        const fileUsedVal = uniqueJsFiles.join(', ');
        const fileTypeVal = fileName.substring(fileName.lastIndexOf('.') + 1) || 'html';

        // 6. ทำการบีบอัดข้อมูล HTML เป็น Gzip
        const gzipBlob = await compressToGzip(htmlContent);

        // 7. บรรจุข้อมูลใส่ FormData เพื่อส่งแบบ Multipart
        const formData = new FormData();
        formData.append('fileName', fileName);
        formData.append('path', targetPath);
        formData.append('url', fullUrl);
        formData.append('fileType', fileTypeVal);
        formData.append('fileUsed', fileUsedVal);
        formData.append('zippedHtml', gzipBlob, fileName + '.gz'); // แนบไฟล์ zip ที่บีบอัดแล้ว

        if (screenshotBlob) {
            // แนบไฟล์รูปภาพหน้าจอไปด้วยในชื่อฟิลด์ 'screenshot'
            formData.append('screenshot', screenshotBlob, fileName + '.png');
        }

        // 8. ระบุ Host URL ปลายทางที่ต้องการส่งข้อมูลไปเก็บ
        const hostUrl = 'https://lovetoshopmall.com/Derivtrade2026/ajaxphp/savepagetolib.php'; // *** เปลี่ยนเป็น URL เซิร์ฟเวอร์จริงของคุณ ***

        // 9. ทำการส่ง AJAX (Fetch POST) ข้อมูลทั้งหมดไปยังเซิร์ฟเวอร์
        const response = await fetch(hostUrl, {
            method: 'POST',
            body: formData // เบราว์เซอร์จะตั้งค่า Header 'multipart/form-data' ให้อัตโนมัติ
        });

        if (response.ok) {
            console.log(`[PageSender] ส่งหน้าเว็บ ${fileName} และ Screenshot สำเร็จ`);

            // 10. ส่งไฟล์ JS ที่ใช้ในหน้านี้ขึ้นไปเก็บเพิ่มเติมในฐานข้อมูล
            const localJsSrcs = scriptTags
                .map(script => script.getAttribute('src'))
                .filter(src => src && !src.startsWith('http') && src !== 'pageSender.js'); // คัดเฉพาะสคริปต์โลคอล (ไม่ใช่ CDN) และไม่ใช่ pageSender.js เอง

            for (const jsSrc of [...new Set(localJsSrcs)]) {
                try {
                    const absoluteUrl = new URL(jsSrc, window.location.href).href;

                    // Fetch ดึงเนื้อหาของไฟล์สคริปต์ JS
                    const jsResponse = await fetch(absoluteUrl);
                    if (!jsResponse.ok) throw new Error(`Fetch failed with status: ${jsResponse.status}`);
                    const jsContent = await jsResponse.text();

                    const jsFileName = jsSrc.split('?')[0].substring(jsSrc.lastIndexOf('/') + 1) || jsSrc;
                    const jsGzipBlob = await compressToGzip(jsContent);

                    const jsFormData = new FormData();
                    jsFormData.append('fileName', jsFileName);

                    let jsTargetPath = new URL(absoluteUrl).pathname;
                    if (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') {
                        jsTargetPath = 'D:\\Rust\\newAPI_Graph\\' + jsFileName;
                    }

                    jsFormData.append('path', jsTargetPath);
                    jsFormData.append('url', absoluteUrl);
                    jsFormData.append('fileType', 'js');
                    jsFormData.append('fileUsed', ''); // ไฟล์ JS ไม่เก็บข้อมูลไฟล์พึ่งพิงซ้อน
                    jsFormData.append('zippedHtml', jsGzipBlob, jsFileName + '.gz');

                    const jsRes = await fetch(hostUrl, {
                        method: 'POST',
                        body: jsFormData
                    });

                    if (jsRes.ok) {
                        console.log(`[PageSender] ส่งไฟล์สคริปต์ ${jsFileName} สำเร็จ`);
                    } else {
                        console.warn(`[PageSender] ส่งไฟล์สคริปต์ ${jsFileName} ล้มเหลวด้วยสถานะ: ${jsRes.status}`);
                    }
                } catch (jsErr) {
                    console.error(`[PageSender] เกิดข้อผิดพลาดในการดึงหรือส่งไฟล์สคริปต์ ${jsSrc}:`, jsErr);
                }
            }
        } else {
            console.warn(`[PageSender] การส่งข้อมูลล้มเหลวด้วยสถานะ: ${response.status}`);
        }
    } catch (error) {
        console.error('[PageSender] เกิดข้อผิดพลาดในการประมวลผลหรือส่งข้อมูล:', error);
    }
});
