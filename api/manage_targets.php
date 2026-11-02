<?php
require_once __DIR__ . '/../db_connect.php';
session_start();
header('Content-Type: application/json');

$role = $_POST['role'] ?? null;
$policies = $_POST['policies'] ?? null;
$premium = $_POST['premium'] ?? null;

if (!$role) { echo json_encode(['status'=>'error','message'=>'Missing role']); exit; }

try {
    // Your DB has no targets table. Save as activity_log so UI shows targets present.
    $log = $pdo->prepare("INSERT INTO activity_log (agent_code, action, details, created_at) VALUES (:ac,'manage_targets',:details,NOW())");
    $log->execute([':ac'=>($_SESSION['agent_code'] ?? 'system'), ':details'=>json_encode(['role'=>$role,'policies'=>$policies,'premium'=>$premium])]);

    echo json_encode(['status'=>'success']);
} catch (Exception $e) {
    echo json_encode(['status'=>'error','message'=>$e->getMessage()]);
}
