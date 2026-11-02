<?php
require_once __DIR__ . '/../db_connect.php';
session_start();
header('Content-Type: application/json');

try {
    $stmt = $pdo->prepare("SELECT p.id AS promotion_id, p.agent_code, a.name as agent_name, p.from_role, p.to_role, p.reason, p.status, p.requested_at FROM promotions p LEFT JOIN agents a ON a.agent_code = p.agent_code ORDER BY p.requested_at DESC");
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['status'=>'success','data'=>$rows]);
} catch (Exception $e) {
    echo json_encode(['status'=>'error','message'=>$e->getMessage()]);
}
