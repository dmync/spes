<?php
require_once __DIR__ . '/../db_connect.php';
session_start();
header('Content-Type: application/json');

$agent_code = $_POST['agent_code'] ?? null;
$branch_code = $_POST['branch_code'] ?? null;
$policy_number = $_POST['policy_number'] ?? null;
$premium_amount = $_POST['premium_amount'] ?? null;
$payment_frequency = $_POST['payment_frequency'] ?? null;
$invoice_date = $_POST['invoice_date'] ?? null;
$agent_signature = isset($_POST['agent_signature']) ? (int) $_POST['agent_signature'] : 0;
$cash_handed_date = $_POST['cash_handed_date'] ?? null;

if (!$agent_code || !$policy_number || $premium_amount === null) { echo json_encode(['status'=>'error','message'=>'Missing fields']); exit; }

try {
    $stmt = $pdo->prepare("INSERT INTO invoices (branch_code, supervisor_code, team_leader_code, agent_code, invoice_date, policy_number, premium_amount, payment_frequency, agent_signature, cash_handed_date, created_at) VALUES (:bc, :sup, :tl, :ac, :invdate, :pol, :prem, :freq, :sig, :chd, NOW())");
    $stmt->execute([
        ':bc'=>$branch_code,
        ':sup'=>$_POST['supervisor_code'] ?? null,
        ':tl'=>$_POST['team_leader_code'] ?? null,
        ':ac'=>$agent_code,
        ':invdate'=>$invoice_date,
        ':pol'=>$policy_number,
        ':prem'=>$premium_amount,
        ':freq'=>$payment_frequency,
        ':sig'=>$agent_signature,
        ':chd'=>$cash_handed_date
    ]);

    $stmtAgent = $pdo->prepare("SELECT agent_code FROM agents WHERE agent_code = ? LIMIT 1");
    $stmtAgent->execute([$agent_code]);
    if (!$stmtAgent->fetchColumn()) {
        $stmtRec = $pdo->prepare("INSERT INTO recruitment (entered_code, branch_code, role_suggested, entered_by, created_at) VALUES (:ec,:bc,:role,:by,NOW())");
        $stmtRec->execute([':ec'=>$agent_code,':bc'=>$branch_code,':role'=>'agent',':by'=>($_SESSION['agent_code'] ?? 'system')]);
    }

    $log = $pdo->prepare("INSERT INTO activity_log (agent_code, action, details, created_at) VALUES (:ac,'save_invoice',:details,NOW())");
    $log->execute([':ac'=>$agent_code,':details'=>json_encode(['policy'=>$policy_number,'premium'=>$premium_amount])]);

    echo json_encode(['status'=>'success']);
} catch (Exception $e) {
    echo json_encode(['status'=>'error','message'=>$e->getMessage()]);
}
