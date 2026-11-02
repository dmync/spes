<?php
require_once __DIR__ . '/../db_connect.php';
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['agent_code'])) { echo json_encode(['status'=>'error','message'=>'Unauthorized']); exit; }
$tl_code = $_SESSION['agent_code'];

try {
    // requests (promotions) for agents under this TL
    $stmtA = $pdo->prepare("SELECT agent_code FROM agents WHERE team_leader_code = ?");
    $stmtA->execute([$tl_code]);
    $agentCodes = $stmtA->fetchAll(PDO::FETCH_COLUMN);
    if (empty($agentCodes)) { echo json_encode(['status'=>'success','data'=>[]]); exit; }
    $in = implode(',', array_fill(0, count($agentCodes), '?'));
    $stmt = $pdo->prepare("SELECT id AS id, agent_code, to_role, reason, status, requested_at FROM promotions WHERE agent_code IN ($in) ORDER BY requested_at DESC");
    $stmt->execute($agentCodes);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['status'=>'success','data'=>$rows]);
} catch (Exception $e) {
    echo json_encode(['status'=>'error','message'=>$e->getMessage()]);
}
