<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title>Отчет по сделкам и задачам (мотивация)</title>
		<link rel="stylesheet" type="text/css" href="view/css/style.css">
		<script src="//api.bitrix24.com/api/v1/"></script>
		<script>
			BX24.resizeWindow(frames.innerWidth,window.innerHeight);
		</script>
	</head>
	<body>

		<!-- фильтр -->
		<form method='post'>
			<div class='responsible'>
				<label>Ответственный:</label>
				<select name='responsible'>
					<option value=''>Все сотрудники</option>
					<?php foreach($users['result']['result'] as $user): ?>
						<?php foreach($user as $u): ?>
							<?php if($u['ID'] == '1549') continue; ?>
							<?php $selectuser = ($_POST['responsible'] == $u['ID']) ? 'selected' : '';?>
							<option <?=$selectuser;?> value="<?=$u['ID'];?>"><?=$u['LAST_NAME'].' '.$u['NAME'];?></option>
						<?php endforeach; ?>
					<?php endforeach; ?>
				</select>
			</div>

			<div class='stage'>
				<label>Стадия сделки:</label>
				<select name='stage'>
					<option value="">Все стадии</option>
					<?php foreach ($arrStages as $key => $stade): ?>
						<?php $selectst = ($_POST['stage'] == $key) ? 'selected' : '';?>
						<option <?=$selectst;?> value="<?=$key;?>"><?=$stade;?></option>
					<?php endforeach; ?>
				</select>
			</div>

			<div class='dateClose'>
				<label>Настройка временного периода:</label>
				<input type="date" value="<?=$_POST['dateCloseStart'];?>" name="dateCloseStart">
				<input type="date" value="<?=$_POST['dateCloseEnd'];?>" name="dateCloseEnd">
				
				<select name='dateType' value="<?=$_POST['dateType'];?>">
					<option value='close'>По дате закрытия</option>
					<option value='begin'>По дате открытия</option>
				</select>
			</div>

			<div class='sort'>
				<label>Настройка сортировки:</label>
				<select name='sort'>
					<option value='none'>Не задано</option>
					<option value='type'>По типу сделки</option>
					<option value='responsible'>По ответственному</option>
					<option value='dateCreateD'>По дате создания сделки</option>
				</select>
			</div>

			<div class='btn'>
				<input type="submit" name="send" value='Отправить'>
				<a href='handler.php'>Excel</a>
			</div>
		</form>

		<!-- таблица -->
		<table>
			<thead>
				<tr>
					<td>Тип сделки</td>
					<td>Ответственный</td>
					<td>Дата создания</td>
					<td>Сделка</td>
					<td>Стадия сделки</td>
					<td>Дата закрытия сделки</td>
					<td>Дата создания задачи</td>
					<td>Задача</td>
					<td>Исполнитель задачи</td>
					<td>Стадия задачи</td>
					<td>Норма времени</td>
					<td>Списанное время</td>
					<td>Сумма задачи</td>
					<td>Сумма сделки</td>
					<td>Поступившие деньги</td>
					<td>Мотивация ответственного</td>
					<td>Мотивация исполнителя</td>
				</tr>
			</thead>

			<tbody>
				<?php if(isset($mainArr)): ?>
					<?php foreach($mainArr as $k => $arr): ?>
							<?php if(isset($arr['task']) && count($arr['task']) > 0): ?>
								<?php for ($i = 0, $s = count($arr['task']); $i < $s; $i++): ?>
									<?php if(!empty($arr['task'][$i]['id']) && !isset($arr['deal'])) continue; ?>
									<tr>
										<td><?=isset($arr['deal'][0]['type']) ? $arr['deal'][0]['type'] : '-';?></td>
										<td><?=isset($arr['deal'][0]['responsible']) ? $arr['deal'][0]['responsible'] : '-';?></td>
										<td><?=isset($arr['deal'][0]['dateCreateD']) ? $arr['deal'][0]['dateCreateD'] : '-';?></td>
										<td>
											<a target='_blank' href="https://shurup.bitrix24.ru/crm/deal/details/<?=key($arr['deal'][0]['deal']);?>/">
												<?=isset($arr['deal'][0]['deal']) ? current($arr['deal'][0]['deal']) : '-';?>
											</a>
										</td>
										<td><?=isset($arr['deal'][0]['stage']) ? $arr['deal'][0]['stage'] : '-';?></td>
										<td><?=isset($arr['deal'][0]['dateCloseD']) ? $arr['deal'][0]['dateCloseD'] : '-';?></td>

										<td><?=isset($arr['task'][$i]['dateCreate']) ? $arr['task'][$i]['dateCreate'] : '-';?></td>
										<td>
											<a target='_blank' href="https://shurup.bitrix24.ru/company/personal/user/2870/tasks/task/view/<?=key($arr['task'][$i]['title']);?>/">
												<?=current($arr['task'][$i]['title']) ?: '-';?>
											</a>
										</td>
										<td><?=isset($arr['task'][$i]['responsible']) ? $arr['task'][$i]['responsible'] : '-';?></td>
										<td><?=isset($arr['task'][$i]['status']) ? $arr['task'][$i]['status'] : '-';?></td>
										<td><?=isset($arr['task'][$i]['norma']) ? $arr['task'][$i]['norma'] : '-';?></td>
										<td><?=isset($arr['task'][$i]['fact']) ? $arr['task'][$i]['fact'] : '-';?></td>
										<td><?=isset($arr['task'][$i]['opportunity']) ? $arr['task'][$i]['opportunity'] : '-';?></td>

										<td><?=isset($arr['deal'][0]['opportunity']) ? $arr['deal'][0]['opportunity'] : '-';?></td>
										<td><?=isset($arr['deal'][0]['comein']) ? $arr['deal'][0]['comein'] : '-';?></td>
										<td><?=isset($arr['task'][$i]['motivrespon']) ? $arr['task'][$i]['motivrespon'] : '-';?></td>
										<td><?=isset($arr['task'][$i]['motiveworker']) ? $arr['task'][$i]['motiveworker'] : '-';?></td>
									</tr>
								<?php endfor; ?>
							<?php else: ?>
								<tr>
									<td><?=isset($arr['deal'][0]['type']) ? $arr['deal'][0]['type'] : '-';?></td>
									<td><?=isset($arr['deal'][0]['responsible']) ? $arr['deal'][0]['responsible'] : '-';?></td>
									<td><?=isset($arr['deal'][0]['dateCreateD']) ? $arr['deal'][0]['dateCreateD'] : '-';?></td>
									<td>
										<a target='_blank' href="https://shurup.bitrix24.ru/crm/deal/details/<?=key($arr['deal'][0]['deal']);?>/">
											<?=isset($arr['deal'][0]['deal']) ? current($arr['deal'][0]['deal']) : '-';?>
										</a>
									</td>
									<td><?=isset($arr['deal'][0]['stage']) ? $arr['deal'][0]['stage'] : '-';?></td>
									<td><?=isset($arr['deal'][0]['dateCloseD']) ? $arr['deal'][0]['dateCloseD'] : '-';?></td>

									<td>-</td>
									<td>-</td>
									<td>-</td>
									<td>-</td>
									<td>-</td>
									<td>-</td>
									<td>-</td>

									<td><?=isset($arr['deal'][0]['opportunity']) ? $arr['deal'][0]['opportunity'] : '-';?></td>
									<td><?=isset($arr['deal'][0]['comein']) ? $arr['deal'][0]['comein'] : '-';?></td>
									<td><?=isset($arr['deal'][0]['motivrespon']) ? $arr['deal'][0]['motivrespon'] : '-';?></td>
									<td><?=isset($arr['deal'][0]['motiveworker']) ? $arr['deal'][0]['motiveworker'] : '-';?></td>
								</tr>
							<?php endif; ?>
					<?php endforeach; ?>
				<?php endif; ?>
			</tbody>

			<tfoot>
				<?php if(isset($countArr)): ?>
					<tr>
						<td>-</td>
						<td>-</td>
						<td>-</td>
						<td><?=$countArr['dealscount'];?></td>
						<td>-</td>
						<td>-</td>
						<td>-</td>
						<td>-</td>
						<td>-</td>
						<td>-</td>
						<td><?=round($countArr['normacount'], 2) ?: '0';?></td>
						<td><?=round($countArr['factcount'], 2) ?: '0';?></td>
						<td><?=$countArr['opportunityT'] ?: '0';?></td>
						<td><?=$countArr['opportunitycount'] ?: '0';?></td>
						<td><?=$countArr['comeincount'] ?: '0';?></td>
						<td><?=$countArr['motivresponcount'] ?: '0';?></td>
						<td><?=$countArr['motivworkercount'] ?: '0';?></td>
					</tr>
				<?php endif; ?>
			</tfoot>
		</table>
	</body>
</html>