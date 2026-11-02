<?php
require_once __DIR__ . '/../db_connect.php';
session_start();
header('Content-Type: application/json');

try {
    $labels=[]; $policies=[];
    for ($i=5;$i>=0;$i--) {
        $d = new DateTime("first day of -$i month");
        $labels[] = $d->format('M Y');
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM invoices WHERE MONTH(created_at)=? AND YEAR(created_at)=?");
        $stmt->execute([$d->format('n'),$d->format('Y')]);
        $policies[] = (int)$stmt->fetchColumn();
    }
    $summary = ['total_policies'=> (int)$pdo->query("SELECT COUNT(*) FROM invoices")->fetchColumn()];
    $donut = ['labels'=>['Agents','TLs','Sup','LIA'], 'values'=>[60,20,10,10]];

    echo json_encode(['status'=>'success','data'=>['trends'=>['labels'=>$labels,'policies'=>$policies],'summary'=>$summary,'donut'=>$donut]]);
} catch (Exception $e) {
    echo json_encode(['status'=>'error','message'=>$e->getMessage()]);
}
