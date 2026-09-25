<?php
/**
 * PHP File Search Tool
 * ค้นหาคำในไฟล์ PHP ทั้งหมดใน folder และ subfolder
 */

// ตั้งค่า
$searchDirectory = __DIR__; // โฟลเดอร์ที่จะค้นหา (ใช้โฟลเดอร์ปัจจุบัน)
$searchKeyword = ''; // คำที่ต้องการค้นหา

// รับค่าจากฟอร์ม
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $searchDirectory = $_POST['directory'] ?? __DIR__;
    $searchKeyword = $_POST['keyword'] ?? '';
}

/**
 * ฟังก์ชันแปลง path ของไฟล์เป็น URL ที่เปิดได้
 */
function getFileUrl($filePath) {
    $documentRoot = $_SERVER['DOCUMENT_ROOT'];
    $scriptName = dirname($_SERVER['SCRIPT_NAME']);
    
    // ลบ document root ออกจาก path
    $relativePath = str_replace($documentRoot, '', $filePath);
    
    // แปลง backslash เป็น forward slash สำหรับ Windows
    $relativePath = str_replace('\\', '/', $relativePath);
    
    // สร้าง URL
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'];
    
    return $protocol . $host . $relativePath;
}

/**
 * ฟังก์ชันค้นหาไฟล์ PHP ใน folder และ subfolder
 */
function searchPHPFiles($dir, $keyword) {
    $results = [];
    
    if (!is_dir($dir)) {
        return $results;
    }
    
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );
    
    foreach ($iterator as $file) {
        if ($file->isFile() && strtolower($file->getExtension()) === 'php') {
            $filePath = $file->getPathname();
            $content = file_get_contents($filePath);
            
            // ค้นหาคำในเนื้อหาไฟล์
            if (stripos($content, $keyword) !== false) {
                // หาบรรทัดที่มีคำค้นหา
                $lines = explode("\n", $content);
                $matchedLines = [];
                
                foreach ($lines as $lineNum => $line) {
                    if (stripos($line, $keyword) !== false) {
                        $matchedLines[] = [
                            'number' => $lineNum + 1,
                            'content' => htmlspecialchars(trim($line))
                        ];
                    }
                }
                
                $results[] = [
                    'file' => $filePath,
                    'url' => getFileUrl($filePath),
                    'matches' => $matchedLines,
                    'total' => count($matchedLines)
                ];
            }
        }
    }
    
    return $results;
}

?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ค้นหาคำในไฟล์ PHP</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .header h1 {
            font-size: 2em;
            margin-bottom: 10px;
        }
        
        .search-form {
            padding: 30px;
            background: #f8f9fa;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }
        
        .form-group input {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: transform 0.2s;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        
        .results {
            padding: 30px;
        }
        
        .result-item {
            background: #fff;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            margin-bottom: 20px;
            overflow: hidden;
            transition: box-shadow 0.3s;
        }
        
        .result-item:hover {
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .result-header {
            background: #f8f9fa;
            padding: 15px 20px;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .file-path {
            font-weight: 600;
            color: #667eea;
            word-break: break-all;
        }
        
        .open-link {
            display: inline-block;
            background: #28a745;
            color: white;
            padding: 6px 15px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 14px;
            margin-left: 10px;
            transition: background 0.3s;
        }
        
        .open-link:hover {
            background: #218838;
        }
        
        .match-count {
            display: inline-block;
            background: #667eea;
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            margin-left: 10px;
        }
        
        .result-body {
            padding: 20px;
        }
        
        .line-item {
            background: #f8f9fa;
            padding: 10px 15px;
            margin-bottom: 10px;
            border-left: 3px solid #667eea;
            border-radius: 4px;
        }
        
        .line-number {
            display: inline-block;
            background: #667eea;
            color: white;
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 12px;
            margin-right: 10px;
            font-weight: 600;
        }
        
        .line-content {
            color: #333;
            font-family: 'Courier New', monospace;
            font-size: 14px;
        }
        
        .no-results {
            text-align: center;
            padding: 40px;
            color: #666;
        }
        
        .stats {
            background: #e8f4f8;
            padding: 15px 20px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: 600;
            color: #333;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔍 ค้นหาคำในไฟล์ PHP</h1>
            <p>ค้นหาคำที่ต้องการในไฟล์ PHP ทั้งหมด รวมถึง subfolder</p>
        </div>
        
        <div class="search-form">
            <form method="POST">
                <div class="form-group">
                    <label for="directory">📁 โฟลเดอร์ที่ต้องการค้นหา:</label>
                    <input type="text" id="directory" name="directory" 
                           value="<?php echo htmlspecialchars($searchDirectory); ?>" 
                           placeholder="<?php echo __DIR__; ?>">
                </div>
                
                <div class="form-group">
                    <label for="keyword">🔎 คำที่ต้องการค้นหา:</label>
                    <input type="text" id="keyword" name="keyword" 
                           value="<?php echo htmlspecialchars($searchKeyword); ?>" 
                           placeholder="ใส่คำที่ต้องการค้นหา..." required>
                </div>
                
                <button type="submit" class="btn">ค้นหา</button>
            </form>
        </div>
        
        <?php if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($searchKeyword)): ?>
            <div class="results">
                <?php
                $results = searchPHPFiles($searchDirectory, $searchKeyword);
                
                if (!empty($results)):
                    $totalFiles = count($results);
                    $totalMatches = array_sum(array_column($results, 'total'));
                ?>
                    <div class="stats">
                        ✅ พบ <strong><?php echo $totalMatches; ?></strong> ตำแหน่งใน <strong><?php echo $totalFiles; ?></strong> ไฟล์
                    </div>
                    
                    <?php foreach ($results as $result): ?>
                        <div class="result-item">
                            <div class="result-header">
                                <span class="file-path">📄 <?php echo htmlspecialchars($result['file']); ?></span>
                                <a href="<?php echo htmlspecialchars($result['url']); ?>" 
                                   target="_blank" 
                                   class="open-link">🚀 เปิดไฟล์</a>
                                <span class="match-count"><?php echo $result['total']; ?> ตำแหน่ง</span>
                            </div>
                            <div class="result-body">
                                <?php foreach ($result['matches'] as $match): ?>
                                    <div class="line-item">
                                        <span class="line-number">บรรทัด <?php echo $match['number']; ?></span>
                                        <span class="line-content"><?php echo $match['content']; ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    
                <?php else: ?>
                    <div class="no-results">
                        <h2>😔 ไม่พบผลลัพธ์</h2>
                        <p>ไม่พบคำว่า "<strong><?php echo htmlspecialchars($searchKeyword); ?></strong>" ในไฟล์ PHP ใดๆ</p>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>