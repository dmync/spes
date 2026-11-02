<?php
require_once __DIR__ . '/../db_connect.php';
session_start();
header('Content-Type: application/json');

try {
    $branches = $pdo->query("SELECT branch_id, branch_code, branch_name FROM branches ORDER BY branch_name")->fetchAll(PDO::FETCH_ASSOC);
    $out = [];
    foreach ($branches as $b) {
        $branch_id = $b['branch_id'];
        // managers in this branch
        $stmtM = $pdo->prepare("SELECT agent_code, name FROM agents WHERE branch_id = ? AND role = 'manager' ORDER BY name");
        $stmtM->execute([$branch_id]); $managers = $stmtM->fetchAll(PDO::FETCH_ASSOC);
        foreach ($managers as &$m) {
            $stmtS = $pdo->prepare("SELECT agent_code, name FROM agents WHERE branch_id = ? AND role='supervisor' ORDER BY name");
            $stmtS->execute([$branch_id]); $supervisors = $stmtS->fetchAll(PDO::FETCH_ASSOC);
            foreach ($supervisors as &$s) {
                // TLs under supervisor by supervisor_code
                $stmtTL = $pdo->prepare("SELECT agent_code, name FROM agents WHERE role='team_leader' AND supervisor_code = ? ORDER BY name");
                $stmtTL->execute([$s['agent_code']]); $tls = $stmtTL->fetchAll(PDO::FETCH_ASSOC);
                $s['team_leaders'] = $tls;
            }
            $m['supervisors'] = $supervisors;
        }
        $out[] = ['branch_code'=>$b['branch_code'],'branch_name'=>$b['branch_name'],'managers'=>$managers];
    }
    echo json_encode(['status'=>'success','data'=>$out]);
} catch (Exception $e) {
    echo json_encode(['status'=>'error','message'=>$e->getMessage()]);
}
