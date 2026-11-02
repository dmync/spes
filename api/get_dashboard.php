<?php
require_once __DIR__ . '/../db_connect.php';
session_start();
header('Content-Type: application/json');

$role = $_SESSION['role'] ?? ($_GET['role'] ?? 'manager');

try {
    if ($role === 'manager') {
        // Manager overview: metrics + trends + distribution (basic)
        $totalPolicies = (int)$pdo->query("SELECT COUNT(*) FROM invoices")->fetchColumn();
        $monthPremium = (float)$pdo->query("SELECT COALESCE(SUM(premium_amount),0) FROM invoices WHERE MONTH(created_at)=MONTH(CURRENT_DATE()) AND YEAR(created_at)=YEAR(CURRENT_DATE())")->fetchColumn();
        $activeAgents = (int)$pdo->query("SELECT COUNT(*) FROM agents WHERE status='active'")->fetchColumn();
        $pendingPromotions = (int)$pdo->query("SELECT COUNT(*) FROM promotions WHERE status='pending'")->fetchColumn();

        $metrics = [
            ['title'=>'Total Policies','value_display'=>$totalPolicies,'percent'=>50],
            ['title'=>'Monthly Premium (LKR)','value_display'=>number_format($monthPremium,2),'percent'=>60],
            ['title'=>'Active Agents','value_display'=>$activeAgents,'percent'=>70],
            ['title'=>'Pending Promotions','value_display'=>$pendingPromotions,'percent'=>10],
        ];

        $labels = []; $policies = [];
        for ($i=5;$i>=0;$i--) {
            $d = new DateTime("first day of -$i month");
            $labels[] = $d->format('M Y');
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM invoices WHERE MONTH(created_at)=? AND YEAR(created_at)=?");
            $stmt->execute([$d->format('n'), $d->format('Y')]);
            $policies[] = (int)$stmt->fetchColumn();
        }

        $distribution = ['labels'=>['Agent','TL','Supervisor','LIA'],'values'=>[60,20,10,10]];

        echo json_encode(['status'=>'success','data'=>['metrics'=>$metrics,'trends'=>['labels'=>$labels,'policies'=>$policies],'distribution'=>$distribution]]);
        exit;
    }

    // default small payload for other roles (front-end calls role-specific endpoints for detailed data)
    $metrics = [
        ['title'=>'Policies this month','value_display'=>0,'percent'=>0],
        ['title'=>'Premium this month','value_display'=>'0.00','percent'=>0]
    ];
    echo json_encode(['status'=>'success','data'=>['metrics'=>$metrics]]);
} catch (Exception $e) {
    echo json_encode(['status'=>'error','message'=>$e->getMessage()]);
}
