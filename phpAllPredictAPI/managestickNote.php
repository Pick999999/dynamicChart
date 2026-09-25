<?php
// managestickNote.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

// Database Configuration
/*
$host = 'localhost';
$dbname = 'thepaper_lab';
$username = 'thepaper';
$password = 'maithong';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Connection failed: ' . $e->getMessage()]);
    exit;
}
*/

require_once("../deriv/newutil2.php"); 
$dbname = 'thepaper_lab' ;
$pdo = getPDONew()  ;





// Get action
$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch($action) {
    case 'list':
        listNotes($pdo);
        break;
    case 'get':
        getNote($pdo);
        break;
    case 'insert':
        insertNote($pdo);
        break;
    case 'update':
        updateNote($pdo);
        break;
    case 'delete':
        deleteNote($pdo);
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}

// List all notes
function listNotes($pdo) {
    try {
        $stmt = $pdo->query("
            SELECT id, title, color, subjects, create_date, last_update 
            FROM sticky_notes 
            ORDER BY last_update DESC
        ");
        
        $notes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'data' => $notes
        ]);
    } catch(PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}

// Get single note
function getNote($pdo) {
    try {
        $id = $_GET['id'] ?? 0;
        
        $stmt = $pdo->prepare("
            SELECT id, title, color, subjects, create_date, last_update 
            FROM sticky_notes 
            WHERE id = ?
        ");
        
        $stmt->execute([$id]);
        $note = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($note) {
            echo json_encode([
                'success' => true,
                'data' => $note
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Note not found'
            ]);
        }
    } catch(PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}

// Insert new note
function insertNote($pdo) {
    try {
        $title = $_POST['title'] ?? '';
        $color = $_POST['color'] ?? 'yellow';
        $subjects = $_POST['subjects'] ?? '[]';
        
        if (empty($title)) {
            echo json_encode([
                'success' => false,
                'message' => 'Title is required'
            ]);
            return;
        }
        
        $stmt = $pdo->prepare("
            INSERT INTO sticky_notes (title, color, subjects, create_date, last_update) 
            VALUES (?, ?, ?, NOW(), NOW())
        ");
        
        $stmt->execute([$title, $color, $subjects]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Note created successfully',
            'id' => $pdo->lastInsertId()
        ]);
    } catch(PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}

// Update note
function updateNote($pdo) {
    try {
        $id = $_POST['id'] ?? 0;
        $title = $_POST['title'] ?? '';
        $color = $_POST['color'] ?? 'yellow';
        $subjects = $_POST['subjects'] ?? '[]';
        
        if (empty($title)) {
            echo json_encode([
                'success' => false,
                'message' => 'Title is required'
            ]);
            return;
        }
        
        $stmt = $pdo->prepare("
            UPDATE sticky_notes 
            SET title = ?, color = ?, subjects = ?, last_update = NOW() 
            WHERE id = ?
        ");
        
        $stmt->execute([$title, $color, $subjects, $id]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Note updated successfully'
        ]);
    } catch(PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}

// Delete note
function deleteNote($pdo) {
    try {
        $id = $_POST['id'] ?? 0;
        
        if (empty($id)) {
            echo json_encode([
                'success' => false,
                'message' => 'ID is required'
            ]);
            return;
        }
        
        $stmt = $pdo->prepare("DELETE FROM sticky_notes WHERE id = ?");
        $stmt->execute([$id]);
        
        if ($stmt->rowCount() > 0) {
            echo json_encode([
                'success' => true,
                'message' => 'Note deleted successfully'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Note not found'
            ]);
        }
    } catch(PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}
?>