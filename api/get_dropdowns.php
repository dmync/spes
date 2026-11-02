<?php
require_once __DIR__ . '/../db_connect.php';
session_start();
header('Content-Type: application/json');

try {
    // branches
    $branches = $pdo->query("SELECT branch_code, branch_name FROM branches ORDER BY branch_name")->fetchAll(PDO::FETCH_ASSOC);
    // supervisors
    $stmt = $pdo->prepare("SELECT agent_code, name FROM agents WHERE role='supervisor' ORDER BY name");
    $stmt->execute(); $supervisors = $stmt->fetchAll(PDO::FETCH_ASSOC);
    // team leaders
    $stmt = $pdo->prepare("SELECT agent_code, name FROM agents WHERE role='team_leader' ORDER BY name");
    $stmt->execute(); $team_leaders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    // agents and lias
    $stmt = $pdo->prepare("SELECT agent_code, name FROM agents WHERE role IN ('agent','lia') ORDER BY name");
    $stmt->execute(); $agents = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['status'=>'success','branches'=>$branches,'supervisors'=>$supervisors,'team_leaders'=>$team_leaders,'agents'=>$agents]);
} catch (Exception $e) {
    echo json_encode(['status'=>'error','message'=>$e->getMessage()]);
}
