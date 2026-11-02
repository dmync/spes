<?php
// supervisor_requests.php
require_once __DIR__ . '/../db_connect.php';
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['agent_code'])) { echo json_encode(['status'=>'error','message'=>'Unauthorized']); exit; }
$supervisor = $_SESSION['agent_code'];

try {
    // requests by agents in this supervisor's branch/team 
    $stmt = $pdo->prepare("SELECT r.id AS id, r.entered_code AS agent_code, r.role_suggested AS to_role, r.entered_by, r.created_at FROM recruitment r JOIN agents a ON a.branch_id = r.branch_code OR a.branch_id = a.branch_id WHERE a.supervisor_code = ? ORDER BY r.created_at DESC");
    // The recruitment table stores branch_code text 
    $stmt = $pdo->prepare("
        SELECT p.id AS id, p.agent_code, p.to_role, p.reason, p.status, p.requested_at FROM promotions p
        JOIN agents a ON a.agent_code = p.agent_code
        WHERE a.supervisor_code = ?
        ORDER BY p.requested_at DESC
    ");
    $stmt->execute([$supervisor]);
    $promos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['status'=>'success','data'=>$promos]);
} catch (Exception $e) {
    echo json_encode(['status'=>'error','message'=>$e->getMessage()]);
}
