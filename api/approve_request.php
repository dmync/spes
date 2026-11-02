<?php
require_once __DIR__ . '/../db_connect.php';
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['agent_code'])) { echo json_encode(['status'=>'error','message'=>'Unauthorized']); exit; }

$id = $_POST['id'] ?? null;
if (!$id) { echo json_encode(['status'=>'error','message'=>'Missing id']); exit; }

try {
    // For your schema, requests are promotions; mark them approved (or use promotions table)
    $stmt = $pdo->prepare("UPDATE promotions SET status='approved', approved_by = ?, decided_at = NOW() WHERE id = ?");
    $stmt->execute([$_SESSION['agent_code'],$id]);
    echo json_encode(['status'=>'success']);
} catch (Exception $e) {
    echo json_encode(['status'=>'error','message'=>$e->getMessage()]);
}
