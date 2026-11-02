<?php
require_once __DIR__ . '/../db_connect.php';
session_start();
header('Content-Type: application/json');

$agent_code = $_POST['agent_code'] ?? null;
$name = $_POST['name'] ?? null;
$role = $_POST['role'] ?? 'agent';
$branch_code = $_POST['branch_code'] ?? null;

if (!$agent_code || !$name) { echo json_encode(['status'=>'error','message'=>'Missing fields']); exit; }

try {
    // map branch_code to branch_id if possible
    $branch_id = null;
    if ($branch_code) {
        $stmt = $pdo->prepare("SELECT branch_id FROM branches WHERE branch_code = ? LIMIT 1");
        $stmt->execute([$branch_code]); $branch_id = $stmt->fetchColumn() ?: null;
    }
    $stmt = $pdo->prepare("INSERT INTO agents (agent_code, name, role, branch_id, status, created_at) VALUES (:ac, :name, :role, :bid, 'active', NOW())");
    $stmt->execute([':ac'=>$agent_code,':name'=>$name,':role'=>$role,':bid'=>$branch_id]);
    echo json_encode(['status'=>'success']);
} catch (Exception $e) {
    // if duplicate key, return success to avoid breaking idempotency
    if (strpos($e->getMessage(),'Duplicate') !== false) {
        echo json_encode(['status'=>'success','message'=>'Already exists']);
    } else {
        echo json_encode(['status'=>'error','message'=>$e->getMessage()]);
    }
}
