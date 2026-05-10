<?php
require_once __DIR__ . '/../../Models/Application.php';
require_once __DIR__ . '/../../Models/AuditModel.php';

$appModel = new Application($db);
$auditModel = new AuditModel($db);

$app_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

$candidate = $appModel->getCandidateForReport($app_id);
$logs = $auditModel->getLogsByApplication($app_id);
$totalViolations = count($logs);

include __DIR__ . '/../../../templates/recruiter/view_report_form.php';

