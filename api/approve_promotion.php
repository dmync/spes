<?php
require_once __DIR__ . '/../db_connect.php';
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['agent_code'])) { echo json_encode(['status'=>'error','message'=>'Unauthorized']); exit; }

$promotion_id = $_POST['promotion_id'] ?? null;
$action = $_POST['action'] ?? 'approve';
if (!$promotion_id) { echo json_encode(['status'=>'error','message'=>'Missing promotion_id']); exit; }

try {
    if ($action === 'approve') {
        // approve promotion and update agent role (if we decide to apply)
        $stmt = $pdo->prepare("SELECT agent_code, to_role FROM promotions WHERE id = ? LIMIT 1");
        $stmt->execute([$promotion_id]); $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $pdo->prepare("UPDATE promotions SET status='approved', approved_by = ?, decided_at = NOW() WHERE id = ?")->execute([$_SESSION['agent_code'],$promotion_id]);
            // apply promotion to agents table (role change) — this matches assignment behavior
            $pdo->prepare("UPDATE agents SET promoted_from = role, role = ? WHERE agent_code = ?")->execute([$row['to_role'],$row['agent_code']]);
            echo json_encode(['status'=>'success']);
            exit;
        } else {
            echo json_encode(['status'=>'error','message'=>'Promotion not found']); exit;
        }
    } else {
        $pdo->prepare("UPDATE promotions SET status='rejected', approved_by = ?, decided_at = NOW() WHERE id = ?")->execute([$_SESSION['agent_code'],$promotion_id]);
        echo json_encode(['status'=>'success']); exit;
    }
} catch (Exception $e) {
    echo json_encode(['status'=>'error','message'=>$e->getMessage()]);
}
