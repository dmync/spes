<?php
require_once __DIR__ . '/../db_connect.php';
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['agent_code'])) { echo json_encode(['status'=>'error','message'=>'Unauthorized']); exit; }
$supervisor = $_SESSION['agent_code'];

try {
    $stmt = $pdo->prepare("
      SELECT tl.agent_code, tl.name,
        (SELECT COUNT(*) FROM agents a WHERE a.team_leader_code = tl.agent_code) AS agent_count,
        (SELECT COUNT(*) FROM invoices i WHERE i.agent_code IN (SELECT a.agent_code FROM agents a WHERE a.team_leader_code = tl.agent_code) AND MONTH(i.created_at)=MONTH(CURRENT_DATE()) AND YEAR(i.created_at)=YEAR(CURRENT_DATE())) AS month_policies
      FROM agents tl
      WHERE tl.role = 'team_leader' AND tl.supervisor_code = ?
      ORDER BY tl.name
    ");
    $stmt->execute([$supervisor]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['status'=>'success','data'=>$rows]);
} catch (Exception $e) {
    echo json_encode(['status'=>'error','message'=>$e->getMessage()]);
}
