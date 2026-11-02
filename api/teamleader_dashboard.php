<?php
require_once __DIR__ . '/../db_connect.php';
session_start();
header('Content-Type: application/json');

$tl_code = $_GET['tl'] ?? ($_SESSION['agent_code'] ?? null);
if (!$tl_code) { echo json_encode(['status'=>'error','message'=>'Missing TL code']); exit; }

try {
    // monthly summary for TL
    $stmt = $pdo->prepare("
      SELECT
        (SELECT COUNT(*) FROM invoices i WHERE i.agent_code IN (SELECT agent_code FROM agents WHERE team_leader_code = ?) AND MONTH(i.created_at)=MONTH(CURRENT_DATE()) AND YEAR(i.created_at)=YEAR(CURRENT_DATE())) AS policies,
        (SELECT COALESCE(SUM(i.premium_amount),0) FROM invoices i WHERE i.agent_code IN (SELECT agent_code FROM agents WHERE team_leader_code = ?) AND MONTH(i.created_at)=MONTH(CURRENT_DATE()) AND YEAR(i.created_at)=YEAR(CURRENT_DATE())) AS premium
    ");
    $stmt->execute([$tl_code,$tl_code]);
    $summary = $stmt->fetch(PDO::FETCH_ASSOC);

    // top agents
    $stmt2 = $pdo->prepare("
      SELECT a.agent_code, a.name,
        (SELECT COUNT(*) FROM invoices i WHERE i.agent_code = a.agent_code AND MONTH(i.created_at)=MONTH(CURRENT_DATE()) AND YEAR(i.created_at)=YEAR(CURRENT_DATE())) AS policies,
        (SELECT COALESCE(SUM(i.premium_amount),0) FROM invoices i WHERE i.agent_code = a.agent_code AND MONTH(i.created_at)=MONTH(CURRENT_DATE()) AND YEAR(i.created_at)=YEAR(CURRENT_DATE())) AS premium
      FROM agents a
      WHERE a.team_leader_code = ?
      ORDER BY policies DESC
    ");
    $stmt2->execute([$tl_code]);
    $top_agents = $stmt2->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['status'=>'success','data'=>['summary'=>$summary,'top_agents'=>$top_agents]]);
} catch (Exception $e) {
    echo json_encode(['status'=>'error','message'=>$e->getMessage()]);
}
