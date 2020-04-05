<?php
require (__DIR__.'/libs/crest/CRestPlus.php');
require (__DIR__.'/libs/debugger/Debugger.php');
define ('CLIENT', __DIR__.'/libs/crest/settings.json');
$countArr = array('dealscount' => 0, 'opportunitycount' => 0, 'comeincount' => 0, 'comeactscount' => 0, 'motivresponcount' => 0, 'motivworkercount' => 0, 'normacount' => 0, 'factcount' => 0);
#=========================================== settings ==========================================#
### Установка приложения ###
if (isset($_REQUEST['PLACEMENT']) && !file_exists(CLIENT)) CRestPlus::installApp();
### работа с менеджерами ###
$users = CRestPlus::callBatchList ('user.get', array('filter' => array('ACTIVE' => 'Y', 'USER_TYPE' => 'employee', 'UF_DEPARTMENT' => '7')));
foreach ($users['result']['result'] as $user) {
	foreach ($user as $u) { $resp[] = $u['ID']; }
}
### стадии портала ###
$stageId = CRest::call('crm.dealcategory.stage.list', array('id' => '9'));
foreach ($stageId['result'] as $value) {
	$arrStages[$value['STATUS_ID']] = $value['NAME'];
}
$stage = array_keys($arrStages);
### фильтр ###
$dealFilter = array('CLOSEDATE'  => '1900-01-01', 'CATEGORY_ID' => '9', '>OPPORTUNITY' => '1', '!TITLE' => '%не был проставлен отдел%');
$taskFilter = array('CREATED_BY' => $resp, '>Uf_AUTO_434771699909' => '1', 'CLOSED_DATE' => '1900-01-01', '!TITLE' => '%не был проставлен отдел%');
$dealSelect = array('ASSIGNED_BY_ID', 'STAGE_ID', 'DATE_CREATE', 'TITLE', 'CLOSEDATE', 'OPPORTUNITY', 'UF_CRM_1556287663');
$taskSelect = array('UF_CRM_TASK', 'CREATED_DATE', 'TITLE', 'RESPONSIBLE_ID', 'STATUS', 'TIME_ESTIMATE', 'TIME_SPENT_IN_LOGS', 'CLOSED_DATE', 'UF_AUTO_434771699909');
if (isset($_POST['send'])) {
	$dateStart    = $_POST['dateCloseStart'] ?: date('Y-m-d');
	$dateEnd      = $_POST['dateCloseEnd'] ?: date('Y-m-d');
	$responsible  = $_POST['responsible'] ?: $resp;
	$dealTag      = ($_POST['dateType'] == 'begin') ? 'DATE_CREATE'  : 'CLOSEDATE';
	$taskTag      = ($_POST['dateType'] == 'begin') ? 'CREATED_DATE' : 'CLOSED_DATE';
	$stage        = $_POST['stage'] ?: array_keys($arrStages);

	$dealFilter   = array('>='.$dealTag => $dateStart, '<='.$dealTag => $dateEnd, 'ASSIGNED_BY_ID' => $responsible, 'STAGE_ID' => $stage, '!TITLE' => '%не был проставлен отдел%');
	$taskFilter   = array('>='.$taskTag => $dateStart, '<='.$taskTag => $dateEnd, 'RESPONSIBLE_ID' => $responsible, '!CLOSED_DATE' => '', '!TITLE' => '%не был проставлен отдел%');
}

### задачи ###
$tasks = CRestPlus::callBatchList ('tasks.task.list', array('filter' => $taskFilter, 'select' => $taskSelect));
if (isset($tasks['result']['result'])) {
	foreach ($tasks['result']['result'] as $key => $task) {
		foreach ($task['tasks'] as $t) {
			if (!empty($t['ufCrmTask'][0])) {
				if ($t['ufCrmTask'][0][0] == 'D') $tmp = explode('_', $t['ufCrmTask'][0])[1];
				else $tmp = '';
			}

			$dealId[] = $tmp;
			if (!empty($tmp)) {
				### задачи со сделками ###
				$mainArr[$tmp]['task'][] = array(
					'id'           => $tmp,
					'opportunity'  => $t['ufAuto434771699909'],
					'dateCreate'   => explode('T', $t['createdDate'])[0],
					'title'        => array($t['id'] => $t['title']),
					'responsible'  => $t['responsible']['name'],
					'status'       => $t['status'],
					'norma'        => round(($t['timeEstimate'] / 3600), 2) ?: '0',
					'fact'         => round(($t['timeSpentInLogs'] / 3600), 2) ?: '0',
					'motiveworker' => (empty($t['ufAuto434771699909'])) ? round((($t['timeEstimate'] / 3600) * 170), 2) : round(($t['ufAuto434771699909'] * 0.1), 2),
				);
				$tmpCountArr[$tmp][] = (empty($t['ufAuto434771699909'])) ? round((($t['timeEstimate'] / 3600) * 170), 2) : round(($t['ufAuto434771699909'] * 0.1), 2);

			} else {
				### задачи без сделок ###
				$mainArr['none']['task'][] = array(
					'opportunity'  => $t['ufAuto434771699909'],
					'dateCreate'   => explode('T', $t['createdDate'])[0],
					'title'        => array($t['id'] => $t['title']),
					'responsible'  => $t['responsible']['name'],
					'status'       => $t['status'],
					'norma'        => round(($t['timeEstimate'] / 3600), 2) ?: '0',
					'fact'         => round(($t['timeSpentInLogs'] / 3600), 2) ?: '0',
					'motivrespon'  => round((($t['timeEstimate'] / 3600) * 100), 2),
					'motiveworker' => (empty($t['ufAuto434771699909'])) ? round((($t['timeEstimate'] / 3600) * 170), 2) : round(($t['ufAuto434771699909'] * 0.1), 2)
				);
				$countArr['motivworkercount'] += (empty($t['ufAuto434771699909'])) ? round((($t['timeEstimate'] / 3600) * 170), 2) : round(($t['ufAuto434771699909'] * 0.1), 2);
				$countArr['motivresponcount'] += round((($t['timeEstimate'] / 3600) * 100), 2);
				$countArr['opportunityT'] += $t['ufAuto434771699909'];
				$countArr['normacount']   += round(($t['timeEstimate'] / 3600), 2) ?: '0';
				$countArr['factcount']    += round(($t['timeSpentInLogs'] / 3600), 2) ?: '0';
			}
			$opportunityT[$tmp] = $t['ufAuto434771699909'];
			$normacount[$tmp]   = round(($t['timeEstimate'] / 3600), 2) ?: '0';
			$factcount[$tmp]    = round(($t['timeSpentInLogs'] / 3600), 2) ?: '0';
			$tmp = '';
		}
	}

	### сделки к задачам ###
	$tmpDealFilterTask = array('ID' => $dealId, 'STAGE_ID' => $stage, '!TITLE' => '%не был проставлен отдел%');
	$dealsT = CRestPlus::callBatchList ('crm.deal.list', array('filter' => $tmpDealFilterTask, 'select' => $dealSelect));
	if (isset($dealsT['result']['result'])) {
		foreach ($dealsT['result']['result'] as $deal) {
			foreach ($deal as $d) {
				$manager = getManager ($users, $d['ASSIGNED_BY_ID']);
				$firstIds[] = $d['ID'];
				### счета по сделкам ###
				// $invoice = CRestPlus::call ('crm.invoice.list', array('filter' => array('UF_DEAL_ID' => $d['ID']), 'select' => array('PRICE', 'DATE_PAYED')));
				// if (isset($invoice['result'][0]['PRICE']) && isset($invoice['result'][0]['DATE_PAYED'])) {
				// 	$countArr['comeincount'] += $invoice['result'][0]['PRICE'];
				// 	$countArr['motivresponcount'] += round(($invoice['result'][0]['PRICE'] * 0.09), 2);
				// 	for ($i = 0, $s = count($mainArr[$d['ID']]['task']); $i < $s; $i++) {
				// 		$mainArr[$d['ID']]['task'][$i]['motivrespon'] = round(($invoice['result'][0]['PRICE'] * 0.09), 2);
				// 	}
				// }

				if (is_array($d['UF_CRM_1556287663'])) $type = current($d['UF_CRM_1556287663']);
				else $type = $d['UF_CRM_1556287663'];
				$mainArr[$d['ID']]['deal'][] = array(
					'type'        => ($type == '6234') ? 'Лицензия' : 'Услуга',
					'stage'       => isset($arrStages[$d['STAGE_ID']]) ? $arrStages[$d['STAGE_ID']] : null,
					'responsible' => $manager,
					'dateCreateD' => explode('T', $d['DATE_CREATE'])[0],
					'deal'        => array($d['ID'] => $d['TITLE']),
					'dateCloseD'  => explode('T', $d['CLOSEDATE'])[0],
					'opportunity' => round($d['OPPORTUNITY'], 2) ?: '0.00',
					// 'comein'      => (isset($invoice['result'][0]['PRICE']) && isset($invoice['result'][0]['DATE_PAYED'])) ? round($invoice['result'][0]['PRICE'], 2) : '0',
				);
				$countArr['dealscount']       += 1;
				$countArr['opportunitycount'] += $d['OPPORTUNITY'];
				for ($i = 0, $s = count($tmpCountArr[$d['ID']]); $i < $s; $i++) {
					$countArr['motivworkercount'] += $tmpCountArr[$d['ID']][$i];
					$countArr['opportunityT']     += $opportunityT[$d['ID']];
					$countArr['normacount']       += $normacount[$d['ID']];
					$countArr['factcount']        += $factcount[$d['ID']];
				}
			}
		}
		$invoice = CRestPlus::callBatchList ('crm.invoice.list', array('filter' => array('UF_DEAL_ID' => $firstIds), 'select' => array('PRICE', 'DATE_PAYED', 'UF_DEAL_ID')));
		foreach ($invoice['result']['result'] as $value) {
			foreach ($value as $v) {
				if (!isset($mainArr[$v['UF_DEAL_ID']]['deal'])) continue;
				if (isset($v['PRICE']) && !empty($v['DATE_PAYED'])) {
					$countArr['comeincount'] += $v['PRICE'];
					$countArr['motivresponcount'] += round(($v['PRICE'] * 0.09), 2);
					$mainArr[$v['UF_DEAL_ID']]['deal'][0]['comein'] = round($v['PRICE'], 2);
					for ($i = 0, $s = count($mainArr[$v['UF_DEAL_ID']]['task']); $i < $s; $i++) {
						$mainArr[$v['UF_DEAL_ID']]['task'][$i]['motivrespon'] = round(($v['PRICE'] * 0.09), 2);
					}
				}
			}
		}
	}
}

### сделки без задач ###
if (!isset($dealId)) $dealId = '';
$tmpDealFilter = array_merge(array('!ID' => $dealId), $dealFilter);
$deals = CRestPlus::callBatchList ('crm.deal.list', array('filter' => $tmpDealFilter, 'select' => $dealSelect));
if (isset($deals['result']['result'])) {
	foreach ($deals['result']['result'] as $deal) {
		foreach ($deal as $d) {
			$manager = getManager ($users, $d['ASSIGNED_BY_ID']);
			$secondIds[] = $d['ID'];
			### счета по сделкам ###
			// $invoice = CRestPlus::call ('crm.invoice.list', array('filter' => array('UF_DEAL_ID' => $d['ID']), 'select' => array('PRICE', 'DATE_PAYED')));
			// if (isset($invoice['result'][0]['PRICE']) && isset($invoice['result'][0]['DATE_PAYED'])) {
			// 	$countArr['comeincount'] += $invoice['result'][0]['PRICE'];
			// 	$countArr['motivresponcount'] += round(($invoice['result'][0]['PRICE'] * 0.09), 2);
			// }

			if (is_array($d['UF_CRM_1556287663'])) $type = current($d['UF_CRM_1556287663']);
			else $type = $d['UF_CRM_1556287663'];
			$mainArr[$d['ID']]['deal'][] = array(
				'type'        => ($type == '6234') ? 'Лицензия' : 'Услуга',
				'stage'       => 'Сделка успешна',
				'responsible' => $manager,
				'dateCreateD' => explode('T', $d['DATE_CREATE'])[0],
				'deal'        => array($d['ID'] => $d['TITLE']),
				'dateCloseD'  => explode('T', $d['CLOSEDATE'])[0],
				'opportunity' => round($d['OPPORTUNITY'], 2) ?: '0.00',
				// 'comein'      => (isset($invoice['result'][0]['PRICE']) && isset($invoice['result'][0]['DATE_PAYED'])) ? round($invoice['result'][0]['PRICE'], 2) : '0',
				// 'motivrespon' => (isset($invoice['result'][0]['PRICE']) && isset($invoice['result'][0]['DATE_PAYED'])) ? round(($invoice['result'][0]['PRICE'] * 0.09), 2) : '0'
			);
			$countArr['dealscount']       += 1;
			$countArr['opportunitycount'] += $d['OPPORTUNITY'];
		}
	}
	$invoicetwo = CRestPlus::callBatchList ('crm.invoice.list', array('filter' => array('UF_DEAL_ID' => $secondIds), 'select' => array('PRICE', 'DATE_PAYED', 'UF_DEAL_ID')));
	foreach ($invoicetwo['result']['result'] as $value) {
		foreach ($value as $v) {
			if (isset($v['PRICE']) && !empty($v['DATE_PAYED'])) {
				$countArr['comeincount'] += $v['PRICE'];
				$countArr['motivresponcount'] += round(($v['PRICE'] * 0.09), 2);
				$mainArr[$v['UF_DEAL_ID']]['deal'][0]['comein'] = round($v['PRICE'], 2);
				$mainArr[$v['UF_DEAL_ID']]['deal'][0]['motivrespon'] = round(($v['PRICE'] * 0.09), 2);
			}
		}
	}
}

if (isset($_POST['sort']) && $_POST['sort'] != 'none') {
	usort($mainArr, function($a, $b) {
		if ($a['deal'][0][$_POST['sort']] > $b['deal'][0][$_POST['sort']]) return 1; // type, responsible, dateCreateD
		else { return 0; }
	});
}
require_once (__DIR__.'/view/index.php');
if (isset($mainArr)) {
	$mainArr = array_merge($mainArr, array($countArr));
	Debugger::saveParams ($mainArr, 'setting.php');
}
################################### functions ######################################
function getManager ($users, $d) {
	foreach ($users['result']['result'][0] as $user) {
		if ($d == $user['ID']) $manager = $user['LAST_NAME'].' '.$user['NAME'];
	}
	return $manager;
}