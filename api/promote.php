<?php
require_once __DIR__ . '/../db_connect.php';
session_start();
header('Content-Type: application/json');

$agent_code = $_POST['agent_code'] ?? null;
$to_role = $_POST['to_role'] ?? null;
$reason = $_POST['reason'] ?? null;

if (!$agent_code || !$to_role) { echo json_encode(['status'=>'error','message'=>'Missing fields']); exit; }

try {
    $stmt = $pdo->prepare("INSERT INTO promotions (agent_code, from_role, to_role, reason, status, requested_at) VALUES (:ac, (SELECT role FROM agents WHERE agent_code = :ac LIMIT 1), :to, :reason, 'pending', NOW())");
    $stmt->execute([':ac'=>$agent_code, ':to'=>$to_role, ':reason'=>$reason]);
    echo json_encode(['status'=>'success']);
} catch (Exception $e) {
    echo json_encode(['status'=>'error','message'=>$e->getMessage()]);
}
