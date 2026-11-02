<?php
require_once __DIR__ . '/../db_connect.php';
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['agent_code'])) { echo json_encode(['status'=>'error','message'=>'Unauthorized']); exit; }
$tl_code = $_SESSION['agent_code'];

try {
    $stmt = $pdo->prepare("
        SELECT a.agent_code, a.name, a.role,
          (SELECT COUNT(*) FROM invoices i WHERE i.agent_code = a.agent_code AND MONTH(i.created_at)=MONTH(CURRENT_DATE()) AND YEAR(i.created_at)=YEAR(CURRENT_DATE())) AS month_policies,
          (SELECT COALESCE(SUM(i.premium_amount),0) FROM invoices i WHERE i.agent_code = a.agent_code AND MONTH(i.created_at)=MONTH(CURRENT_DATE()) AND YEAR(i.created_at)=YEAR(CURRENT_DATE())) AS month_premium
        FROM agents a
        WHERE a.team_leader_code = ?
        ORDER BY a.name ASC
    ");
    $stmt->execute([$tl_code]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['status'=>'success','data'=>$rows]);
} catch (Exception $e) {
    echo json_encode(['status'=>'error','message'=>$e->getMessage()]);
}
