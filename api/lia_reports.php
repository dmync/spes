<?php
require_once __DIR__ . '/../db_connect.php';
session_start();
header('Content-Type: application/json');

$agent = $_GET['agent'] ?? ($_SESSION['agent_code'] ?? null);
if (!$agent) { echo json_encode(['status'=>'error','message'=>'Missing agent']); exit; }

try {
    $labels=[]; $policies=[];
    for ($i=5;$i>=0;$i--) {
        $d = new DateTime("first day of -$i month");
        $labels[] = $d->format('M Y');
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM invoices WHERE agent_code = ? AND MONTH(created_at)=? AND YEAR(created_at)=?");
        $stmt->execute([$agent,$d->format('n'),$d->format('Y')]);
        $policies[] = (int)$stmt->fetchColumn();
    }
    $stmtS = $pdo->prepare("SELECT COUNT(*) as total_policies, COALESCE(SUM(premium_amount),0) as total_premium FROM invoices WHERE agent_code = ?");
    $stmtS->execute([$agent]); $summary = $stmtS->fetch(PDO::FETCH_ASSOC);

    echo json_encode(['status'=>'success','data'=>['trends'=>['labels'=>$labels,'policies'=>$policies],'summary'=>$summary]]);
} catch (Exception $e) {
    echo json_encode(['status'=>'error','message'=>$e->getMessage()]);
}
