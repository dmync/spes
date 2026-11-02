<?php
require_once __DIR__ . '/../db_connect.php';
session_start();
header('Content-Type: application/json');

$agent_code = $_POST['agent_code'] ?? null;
$reason = $_POST['reason'] ?? null;

if (!$agent_code) { echo json_encode(['status'=>'error','message'=>'Missing agent']); exit; }

try {
    $stmt = $pdo->prepare("UPDATE agents SET status='resigned' WHERE agent_code = ?");
    $stmt->execute([$agent_code]);

    $log = $pdo->prepare("INSERT INTO activity_log (agent_code, action, details, created_at) VALUES (:ac,'resign',:details,NOW())");
    $log->execute([':ac'=>$agent_code,':details'=>$reason]);

    echo json_encode(['status'=>'success']);
} catch (Exception $e) {
    echo json_encode(['status'=>'error','message'=>$e->getMessage()]);
}
