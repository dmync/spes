<?php
require_once __DIR__ . '/../db_connect.php';
session_start();
header('Content-Type: application/json');

try {
    $stmt = $pdo->prepare("SELECT * FROM recruitment ORDER BY created_at DESC LIMIT 500");
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['status'=>'success','data'=>$rows]);
} catch (Exception $e) {
    echo json_encode(['status'=>'error','message'=>$e->getMessage()]);
}
