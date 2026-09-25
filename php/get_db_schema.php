<?php
/**
 * get_db_schema.php
 * API Endpoint to retrieve database schema (tables, columns, primary keys, foreign keys)
 * from MySQL database `dynamic_chart` formatted specifically for GoJS Entity Relationship Diagram.
 */

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, no-store, must-revalidate');

require_once __DIR__ . '/db.php';

try {
    $pdo = getDbConnection();
    $dbName = DB_NAME;

    // 1. Fetch all Base Tables
    $stmtTables = $pdo->prepare("
        SELECT 
            TABLE_NAME, 
            TABLE_ROWS, 
            DATA_LENGTH, 
            CREATE_TIME,
            TABLE_COMMENT
        FROM information_schema.TABLES 
        WHERE TABLE_SCHEMA = :dbname 
          AND TABLE_TYPE = 'BASE TABLE'
        ORDER BY TABLE_NAME ASC
    ");
    $stmtTables->execute([':dbname' => $dbName]);
    $rawTables = $stmtTables->fetchAll(PDO::FETCH_ASSOC);

    // 2. Fetch all Columns
    $stmtColumns = $pdo->prepare("
        SELECT 
            TABLE_NAME, 
            COLUMN_NAME, 
            ORDINAL_POSITION, 
            COLUMN_DEFAULT, 
            IS_NULLABLE, 
            DATA_TYPE, 
            COLUMN_TYPE, 
            COLUMN_KEY, 
            EXTRA, 
            COLUMN_COMMENT
        FROM information_schema.COLUMNS 
        WHERE TABLE_SCHEMA = :dbname 
        ORDER BY TABLE_NAME ASC, ORDINAL_POSITION ASC
    ");
    $stmtColumns->execute([':dbname' => $dbName]);
    $rawColumns = $stmtColumns->fetchAll(PDO::FETCH_ASSOC);

    // 3. Fetch Explicit Foreign Keys
    $stmtFKs = $pdo->prepare("
        SELECT 
            k.TABLE_NAME, 
            k.COLUMN_NAME, 
            k.CONSTRAINT_NAME, 
            k.REFERENCED_TABLE_NAME, 
            k.REFERENCED_COLUMN_NAME
        FROM information_schema.KEY_COLUMN_USAGE k
        WHERE k.TABLE_SCHEMA = :dbname 
          AND k.REFERENCED_TABLE_NAME IS NOT NULL
        ORDER BY k.TABLE_NAME ASC, k.COLUMN_NAME ASC
    ");
    $stmtFKs->execute([':dbname' => $dbName]);
    $rawFKs = $stmtFKs->fetchAll(PDO::FETCH_ASSOC);

    // Build lookup for explicit FKs: "table.column" => [refTable, refCol]
    $explicitFkMap = [];
    $links = [];
    $existingLinkKeys = [];

    foreach ($rawFKs as $fk) {
        $key = strtolower($fk['TABLE_NAME'] . '.' . $fk['COLUMN_NAME']);
        $explicitFkMap[$key] = [
            'refTable' => $fk['REFERENCED_TABLE_NAME'],
            'refCol'   => $fk['REFERENCED_COLUMN_NAME'],
            'constraint' => $fk['CONSTRAINT_NAME']
        ];

        $linkKey = $fk['TABLE_NAME'] . '->' . $fk['REFERENCED_TABLE_NAME'] . ':' . $fk['COLUMN_NAME'];
        if (!isset($existingLinkKeys[$linkKey])) {
            $existingLinkKeys[$linkKey] = true;
            $links[] = [
                'from'        => $fk['TABLE_NAME'],
                'to'          => $fk['REFERENCED_TABLE_NAME'],
                'fromCol'     => $fk['COLUMN_NAME'],
                'toCol'       => $fk['REFERENCED_COLUMN_NAME'],
                'text'        => '0..N',
                'toText'      => '1',
                'isExplicit'  => true,
                'constraint'  => $fk['CONSTRAINT_NAME']
            ];
        }
    }

    // Group columns by table
    $tableColumns = [];
    $tablePKs = [];
    foreach ($rawColumns as $col) {
        $t = $col['TABLE_NAME'];
        if (!isset($tableColumns[$t])) {
            $tableColumns[$t] = [];
            $tablePKs[$t] = [];
        }
        $tableColumns[$t][] = $col;
        if (strtoupper($col['COLUMN_KEY']) === 'PRI') {
            $tablePKs[$t][] = $col['COLUMN_NAME'];
        }
    }

    // List of table names for inferred relationships
    $allTableNames = array_map(function($r) { return $r['TABLE_NAME']; }, $rawTables);
    $tableNamesLowerMap = [];
    foreach ($allTableNames as $tn) {
        $tableNamesLowerMap[strtolower($tn)] = $tn;
    }

    // 4. Inferred / Logical Relationship Discovery
    foreach ($tableColumns as $tableName => $cols) {
        foreach ($cols as $col) {
            $colName = $col['COLUMN_NAME'];
            $colLower = strtolower($colName);
            $fkKey = strtolower($tableName . '.' . $colName);

            // Skip if already an explicit foreign key
            if (isset($explicitFkMap[$fkKey])) {
                continue;
            }

            // Skip primary key of the same table
            if (strtoupper($col['COLUMN_KEY']) === 'PRI') {
                continue;
            }

            $matchedTargetTable = null;
            $matchedTargetCol = null;

            // Pattern A: <target_table>_id, e.g. user_id -> users.id, trade_id -> tradehead.id, head_id -> headarticle.id
            if (preg_match('/^(.+)_id$/i', $colName, $m)) {
                $candidate = strtolower($m[1]);
                if (isset($tableNamesLowerMap[$candidate])) {
                    $matchedTargetTable = $tableNamesLowerMap[$candidate];
                } elseif (isset($tableNamesLowerMap[$candidate . 's'])) { // plural
                    $matchedTargetTable = $tableNamesLowerMap[$candidate . 's'];
                } elseif (isset($tableNamesLowerMap[$candidate . 'article'])) { // head -> headarticle
                    $matchedTargetTable = $tableNamesLowerMap[$candidate . 'article'];
                }
                $matchedTargetCol = 'id';
            }
            // Pattern B: <TargetTable>ID (PascalCase), e.g. VPSMasterID -> vpsmaster.id, DerivAccountID -> derivaccount.id
            elseif (preg_match('/^(.+)id$/i', $colName, $m)) {
                $candidate = strtolower($m[1]);
                if (isset($tableNamesLowerMap[$candidate])) {
                    $matchedTargetTable = $tableNamesLowerMap[$candidate];
                    $matchedTargetCol = 'id';
                }
            }
            // Pattern C: vps_id -> vpsmaster.id
            elseif ($colLower === 'vps_id' && isset($tableNamesLowerMap['vpsmaster'])) {
                $matchedTargetTable = $tableNamesLowerMap['vpsmaster'];
                $matchedTargetCol = 'id';
            }

            if ($matchedTargetTable && $matchedTargetTable !== $tableName) {
                $linkKey = $tableName . '->' . $matchedTargetTable . ':' . $colName;
                if (!isset($existingLinkKeys[$linkKey])) {
                    $existingLinkKeys[$linkKey] = true;
                    $links[] = [
                        'from'        => $tableName,
                        'to'          => $matchedTargetTable,
                        'fromCol'     => $colName,
                        'toCol'       => $matchedTargetCol ?: 'id',
                        'text'        => '0..N',
                        'toText'      => '1',
                        'isExplicit'  => false,
                        'constraint'  => 'Inferred (' . $colName . ')'
                    ];
                }
            }
        }
    }

    // 5. Build GoJS nodeDataArray
    $nodeDataArray = [];
    $totalColumnsCount = 0;

    foreach ($rawTables as $table) {
        $tableName = $table['TABLE_NAME'];
        $cols = $tableColumns[$tableName] ?? [];
        $totalColumnsCount += count($cols);

        $items = [];
        $inheritedItems = [];

        foreach ($cols as $col) {
            $isPri = strtoupper($col['COLUMN_KEY']) === 'PRI';
            $isUni = strtoupper($col['COLUMN_KEY']) === 'UNI';
            $isFk = isset($explicitFkMap[strtolower($tableName . '.' . $col['COLUMN_NAME'])]);
            $dataType = strtolower($col['DATA_TYPE']);
            $colType = $col['COLUMN_TYPE'];

            // Determine figure and color style matching GoJS ER sample
            $figure = 'Circle';
            $color = 'blue';

            if ($isPri) {
                $figure = 'Decision'; // Diamond
                $color = 'purple';
            } elseif ($isFk) {
                $figure = 'TriangleRight';
                $color = 'red';
            } elseif (in_array($dataType, ['int', 'tinyint', 'smallint', 'mediumint', 'bigint', 'decimal', 'float', 'double'])) {
                $figure = 'Circle';
                $color = 'green';
            } elseif (in_array($dataType, ['datetime', 'date', 'timestamp', 'time', 'year'])) {
                $figure = 'Hexagon';
                $color = 'yellow';
            } elseif (in_array($dataType, ['varchar', 'char', 'text', 'mediumtext', 'longtext', 'tinytext'])) {
                $figure = 'RoundedRectangle';
                $color = 'blue';
            }

            $itemData = [
                'name'         => $col['COLUMN_NAME'] . ': ' . $colType,
                'colName'      => $col['COLUMN_NAME'],
                'colType'      => $colType,
                'dataType'     => $dataType,
                'iskey'        => $isPri || $isUni,
                'isPk'         => $isPri,
                'isFk'         => $isFk,
                'nullable'     => strtoupper($col['IS_NULLABLE']) === 'YES',
                'defaultValue' => $col['COLUMN_DEFAULT'],
                'extra'        => $col['EXTRA'],
                'comment'      => $col['COLUMN_COMMENT'],
                'figure'       => $figure,
                'color'        => $color
            ];

            // If foreign key, also add to inheritedItems or keep in items
            if ($isFk) {
                $inheritedItems[] = $itemData;
            } else {
                $items[] = $itemData;
            }
        }

        // Exact row count if table is reasonably sized
        $approxRows = (int)$table['TABLE_ROWS'];
        $exactRows = $approxRows;
        if ($approxRows < 50000) {
            try {
                $countStmt = $pdo->query("SELECT COUNT(*) FROM `{$tableName}`");
                $exactRows = (int)$countStmt->fetchColumn();
            } catch (Exception $e) {
                $exactRows = $approxRows;
            }
        }

        $nodeDataArray[] = [
            'key'            => $tableName,
            'tableName'      => $tableName,
            'rowCount'       => $exactRows,
            'comment'        => $table['TABLE_COMMENT'] ?: '',
            'columnCount'    => count($cols),
            'items'          => $items,
            'inheritedItems' => $inheritedItems
        ];
    }

    echo json_encode([
        'status'         => 'success',
        'database'       => $dbName,
        'summary'        => [
            'totalTables'    => count($rawTables),
            'totalColumns'   => $totalColumnsCount,
            'totalRelations' => count($links),
            'explicitRelations' => count(array_filter($links, function($l) { return $l['isExplicit']; })),
            'inferredRelations' => count(array_filter($links, function($l) { return !$l['isExplicit']; })),
            'timestamp'      => date('Y-m-d H:i:s')
        ],
        'nodeDataArray'  => $nodeDataArray,
        'linkDataArray'  => $links
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status'  => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
