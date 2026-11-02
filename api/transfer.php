<?php
require_once __DIR__ . '/../db_connect.php';
session_start();
header('Content-Type: application/json');

$agent_code = $_POST['agent_code'] ?? null;
$supervisor_code = $_POST['supervisor_code'] ?? null;
$team_leader_code = $_POST['team_leader_code'] ?? null;
$branch_code = $_POST['branch_code'] ?? null;

if (!$agent_code) { echo json_encode(['status'=>'error','message'=>'Missing agent']); exit; }

try {
    $fields = []; $params = [':ac'=>$agent_code];
    if ($supervisor_code !== null && $supervisor_code !== '') { $fields[] = "supervisor_code = :sup"; $params[':sup'] = $supervisor_code; }
    if ($team_leader_code !== null && $team_leader_code !== '') { $fields[] = "team_leader_code = :tl"; $params[':tl'] = $team_leader_code; }
    if ($branch_code !== null && $branch_code !== '') {
        // map branch_code to branch_id
        $stmtB = $pdo->prepare("SELECT branch_id FROM branches WHERE branch_code = ? LIMIT 1");
        $stmtB->execute([$branch_code]); $bid = $stmtB->fetchColumn();
        if ($bid) { $fields[] = "branch_id = :bid"; $params[':bid'] = $bid; }
    }
    if (count($fields) === 0) { echo json_encode(['status'=>'error','message'=>'Nothing to change']); exit; }

    $sql = "UPDATE agents SET " . implode(', ', $fields) . " WHERE agent_code = :ac";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    // log transfer in activity_log
    $log = $pdo->prepare("INSERT INTO activity_log (agent_code, action, details, created_at) VALUES (:ac,'transfer',:details,NOW())");
    $log->execute([':ac'=>$agent_code,':details'=>json_encode($params)]);

    echo json_encode(['status'=>'success']);
} catch (Exception $e) {
    echo json_encode(['status'=>'error','message'=>$e->getMessage()]);
}
