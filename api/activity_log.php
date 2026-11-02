<?php
require_once __DIR__ . '/../db_connect.php';
session_start();
header('Content-Type: application/json');

$agent_code = $_POST['agent_code'] ?? ($_SESSION['agent_code'] ?? null);
$action = $_POST['action'] ?? null;
$details = $_POST['details'] ?? null;

if (!$action) { echo json_encode(['status'=>'error','message'=>'Missing action']); exit; }

try {
    $stmt = $pdo->prepare("INSERT INTO activity_log (agent_code, action, details, created_at) VALUES (:ac,:action,:details,NOW())");
    $stmt->execute([':ac'=>$agent_code,':action'=>$action,':details'=>is_string($details)?$details:json_encode($details)]);
    echo json_encode(['status'=>'success']);
} catch (Exception $e) {
    echo json_encode(['status'=>'error','message'=>$e->getMessage()]);
}
