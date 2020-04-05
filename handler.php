<?php
require (__DIR__.'/setting.php');
function array2csv($array){
	if (count($array) == 0) return null;
	ob_start();
	$df = fopen("php://output", 'w');
	fputs($df, chr(0xEF) . chr(0xBB) . chr(0xBF));
	foreach ($array as $row) { fputcsv($df, $row, ';', '"'); }
	fclose($df);
	return ob_get_clean();
}

function download_send_headers($filename) {
	// disable caching
	$now = gmdate("D, d M Y H:i:s");
	header("Expires: Tue, 03 Jul 2001 06:00:00 GMT");
	header("Cache-Control: max-age=0, no-cache, must-revalidate, proxy-revalidate");
	header("Last-Modified: {$now} GMT");
	// force download  
	header("Content-Type: application/force-download");
	header("Content-Type: application/octet-stream");
	header("Content-Type: application/download");
	// disposition / encoding on response body
	header("Content-Disposition: attachment;filename={$filename}");
	header("Content-Transfer-Encoding: binary");
}

function formatArray ($arr) {
	$data = array(
		array('Тип сделки','Ответственный','Дата создания сделки','Сделка', 'Стадия сделки','Дата закрытия сделки','Дата создания задачи','Задача','Исполнитель задачи','Стадия задачи',
			'Норма времени','Списанное время','Сумма задачи', 'Сумма сделки','Поступившие деньги','Мотивация ответственного','Мотивация исполнителя'));
	foreach ($arr as $k => $value) {
		if (end(array_keys($arr)) == $k) {
			$tmp[] = array('', '', '', $value['dealscount'], '', '', '', '', '', '',$value['normacount'], $value['factcount'], $value['opportunityT'], $value['opportunitycount'],
				$value['comeincount'], $value['motivresponcount'], $value['motivworkercount']);

		} else {
			if ($value['task']) {
				for ($i = 0, $s = count($value['task']); $i < $s; $i++) {
					if (isset($value['task'][$i]['id']) && !isset($value['deal'])) continue;
					$tmp[] = array($value['deal'][0]['type'],$value['deal'][0]['responsible'],$value['deal'][0]['dateCreateD'],current($value['deal'][0]['deal']),$value['deal'][0]['stage'],$value['deal'][0]['dateCloseD'],$value['task'][$i]['dateCreate'],current($value['task'][$i]['title']),$value['task'][$i]['responsible'],$value['task'][$i]['status'],$value['task'][$i]['norma'],$value['task'][$i]['fact'],$value['task'][$i]['opportunity'],$value['deal'][0]['opportunity'],$value['deal'][0]['comein'],$value['task'][$i]['motivrespon'],$value['task'][$i]['motiveworker'],);
				}
			} else {
				$tmp[] = array($value['deal'][0]['type'],$value['deal'][0]['responsible'],$value['deal'][0]['dateCreateD'],current($value['deal'][0]['deal']),$value['deal'][0]['stage'],$value['deal'][0]['dateCloseD'],'','','','','','', '',$value['deal'][0]['opportunity'],$value['deal'][0]['comein'],$value['deal'][0]['motivrespon'],'');
			}
		}
	}
	$data = array_merge($data, $tmp);
	return $data;
}

download_send_headers("data_export_" . date("Y-m-d") . ".csv");
echo array2csv(formatArray($appsConfig));
die();