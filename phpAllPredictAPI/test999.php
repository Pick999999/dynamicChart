<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI News Summarizer</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { background-color: #0f172a; color: #f8fafc; font-family: 'Inter', sans-serif; }
        .gradient-text { background: linear-gradient(to right, #38bdf8, #818cf8); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen p-4">

    <div class="w-full max-w-2xl bg-slate-800 p-8 rounded-2xl shadow-2xl border border-slate-700">
        <h1 class="text-3xl font-bold mb-6 text-center gradient-text">สรุปข่าวด้วย AI</h1>
        
        <div class="space-y-4">
            <input type="password" id="apiKey" placeholder="วาง Gemini API Key ของคุณที่นี่"  
                class="w-full p-3 rounded-lg bg-slate-900 border border-slate-600 focus:outline-none focus:border-blue-500"
				value='AIzaSyB00xnfMZ-1ubCdxgSi0_hGQBUwFBQ2Z80'
				
				>
            
            <div class="flex gap-2">
                <input type="text" id="newsUrl" placeholder="วางลิงก์ข่าวที่ต้องการสรุป..." 
                    class="flex-1 p-3 rounded-lg bg-slate-900 border border-slate-600 focus:outline-none focus:border-blue-500 text-white">
                <button onclick="summarizeNews()" id="btnText"
                    class="bg-blue-600 hover:bg-blue-500 px-6 py-3 rounded-lg font-semibold transition">สรุปเลย</button>
            </div>
        </div>

        <div id="resultArea" class="mt-8 hidden animate-fade-in">
            <h2 class="text-xl font-semibold mb-3 text-blue-400">สรุปใจความสำคัญ:</h2>
            <div id="summaryText" class="bg-slate-900 p-5 rounded-xl border border-slate-700 leading-relaxed text-slate-300">
                </div>
        </div>

        <div id="loader" class="mt-8 text-center hidden">
            <p class="animate-pulse text-slate-400">กำลังอ่านข่าวและใช้ AI สรุปใจความ...</p>
        </div>
    </div>

    <script>
        async function summarizeNews() {
    const apiKey = document.getElementById('apiKey').value;
    const newsUrl = document.getElementById('newsUrl').value;
    const resultArea = document.getElementById('resultArea');
    const summaryText = document.getElementById('summaryText');
    const loader = document.getElementById('loader');

    if (!apiKey || !newsUrl) {
        alert("กรุณาใส่ทั้ง API Key และ URL ข่าวครับ");
        return;
    }

    loader.classList.remove('hidden');
    resultArea.classList.add('hidden');

    try {
        // ใช้ AllOrigins Proxy เพื่อแก้ปัญหา CORS (ความปลอดภัยเบราว์เซอร์)
        const proxyUrl = `https://api.allorigins.win/get?url=${encodeURIComponent(newsUrl)}`;
        const response = await fetch(proxyUrl);
        const dataJson = await response.json();
        
        // ดึงเฉพาะ Text ออกมา (แบบหยาบๆ เพื่อส่งให้ AI)
        const rawHtml = dataJson.contents;
        const plainText = rawHtml.replace(/<[^>]*>?/gm, ' ').substring(0, 10000); 

        // ส่งไปให้ Gemini สรุป
        const geminiUrl = `https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=${apiKey}`;
        
        const geminiResponse = await fetch(geminiUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                contents: [{
                    parts: [{ text: `คุณคือผู้เชี่ยวชาญด้านการสรุปข่าว ช่วยสรุปเนื้อหาจากเว็บไซต์นี้เป็นภาษาไทย โดยแบ่งเป็นหัวข้อหลักที่สำคัญ: ${plainText}` }]
                }]
            })
        });

        const geminiData = await geminiResponse.json();
        
        if (geminiData.error) {
            throw new Error(geminiData.error.message);
        }

        const aiResult = geminiData.candidates[0].content.parts[0].text;
        summaryText.innerText = aiResult;
        resultArea.classList.remove('hidden');
        
    } catch (error) {
        console.error(error);
        alert("ขออภัย! ไม่สามารถเข้าถึงเว็บนี้ได้ หรือ API Key ไม่ถูกต้อง โปรดลอง URL อื่นหรือเช็ค Key อีกครั้ง");
    } finally {
        loader.classList.add('hidden');
    }
}
    </script>
</body>
</html>