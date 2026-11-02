<?php
session_start();
header('Content-Type: application/json');
require_once '../db_connect.php';

$agent_code = $_POST['agent_code'] ?? '';
$password = $_POST['password'] ?? '';

if (!$agent_code || !$password) { echo json_encode(['status'=>'error','message'=>'Missing credentials']); exit; }

try {
    $stmt = $pdo->prepare("SELECT l.password, a.name, a.role FROM logins l JOIN agents a ON l.agent_code = a.agent_code WHERE l.agent_code = :code LIMIT 1");
    $stmt->execute([':code'=>$agent_code]);
    $row = $stmt->fetch();
    if (!$row || !password_verify($password, $row['password'])) {
        echo json_encode(['status'=>'error','message'=>'Invalid agent code or password']); exit;
    }
    $_SESSION['agent_code'] = $agent_code;
    $_SESSION['role'] = $row['role'];
    $_SESSION['name'] = $row['name'];
    echo json_encode(['status'=>'success','role'=>$row['role'],'name'=>$row['name']]);
} catch (Exception $e) {
    echo json_encode(['status'=>'error','message'=>$e->getMessage()]);
}
