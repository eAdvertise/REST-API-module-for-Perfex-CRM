<?php

defined('BASEPATH') || exit('No direct script access allowed');

$aColumns = [
	'status',
	'name',
	'scheduled_at',
	'executed_at',
	'CONCAT(rel_type, " - ", action) as type',
];

$sTable = db_prefix() . 'scheduled_webhooks';
$sIndexColumn = 'id';
$join         = ['LEFT JOIN ' . db_prefix() . 'webhooks_master ON ' . db_prefix() . 'webhooks_master.id = ' . db_prefix() . 'scheduled_webhooks.webhook_id'];

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, [], [$sTable.'.id', 'error_message']);
$output = $result['output'];
$rResult = $result['rResult'];

$label_class = ["PENDING" => "warning", "SUCCESS" => "success", "FAILED" => 'danger'];

foreach ($rResult as $aRow) {
	$row = [];
	$status = $aRow['status'];

	$row[] = '<span data-toggle = "tooltip" data-title="'.$aRow['error_message'].'" class="label label-' . ($label_class[$status] ?? "info") . ' s-status">' . $status . '</span>';

	$row[] = $aRow['name'];
	$row[] = _dt($aRow['scheduled_at']);
	$row[] = _dt($aRow['executed_at']);

	$row[] = ucwords($aRow['type']);

	$options = icon_btn(WEBHOOKS_MODULE.'/delete_scheduled_call/'.$aRow['id'], 'remove fa-solid fa-trash', 'btn-danger _delete', ['data-toggle'=>'tooltip', 'data-title'=>_l('delete')]);
	$row[] = $options;

	$output['aaData'][] = $row;
}
