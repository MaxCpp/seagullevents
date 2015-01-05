<?php

if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {

	require_once('./classes/class.seagullevents.php');
	require_once($_SERVER['DOCUMENT_ROOT'].'/manager/includes/config.inc.php');

	$connect = db_connect($database_server, $database_user, $database_password);
	$db = str_replace('`', '', $dbase);
	$db = db_select($db, $connect);

	if (!$db) {
		echo 'Невозможно установить соединение c базой данных "'.$dbase.'" на "'.$database_server.'"';
		exit();
	}

//	Other AJAX-requests
	$se = new CSeagullEvents($msg);
	$response = array();

	switch ($_REQUEST['cmd']) {
		case 'saveEvent':
			if (isset($_POST)) {
				$r = $se->saveEvent($_POST);

				if (!$msg->keep)
					$msg->setError('Мероприятие не сохранен. Попробуйте еще раз.');
			}
		break;

		case 'saveConfig':
			if ($se->config->saveForm($_POST['config'], $se->nameModule)) {
				$se->config->getVariables($se->nameModule);

				if ($se->config->multilang->active) {
					$se->install_multilang();
				}
				$msg->setOk('Настройки сохранены');
			}
			else
				$msg->setError('Ошибка при сохранении');
		break;

		case 'setPublished':
			$r = run_sql('UPDATE '.$se->tables['events']->table.' SET `published`=\''.$_POST['val'].'\' WHERE `id`='.$_POST['itemID']);	// запись прочитана
			$_POST['val'] ? $msg->setOk('Мероприятия №'.$_POST['itemID'].' опубликован') : $msg->setOk('Мероприятия №'.$_POST['itemID'].' скрыт');
		break;

		case 'filter':
			$response['tbody'] = $se->tables['events']->renderTableBody(NULL, "`date_published`=".date2int($_POST['date']));
//			$response['links'] = $se->tables['events']->renderPaginatorLinks($_REQUEST['pageID']);
			$msg->setInfo('Следующая страница');
		break;

		case 'getPaginatorPage':
			$response['tbody'] = $se->tables['events']->renderTableBody($_REQUEST['pageID']);
			$response['links'] = $se->tables['events']->renderPaginatorLinks($_REQUEST['pageID']);
			$msg->setInfo('Следующая страница');
		break;

		case 'addvariable':
			if (($_POST['type']=='S' or $_POST['type']=='R') and strpos($_POST['value'], '|')) {

				$arr = explode("\n", $_POST['value']);
				$_POST['value'] = array();
				foreach ($arr as $option) {
					$arr_option = explode('|', $option);

					$item['title'] = $arr_option[0];
					$item['name'] = $arr_option[1];
					$item['val'] = $arr_option[2];
					$_POST['value'][] = $item;
				}
//				ea($_POST['value']);
			}

			if (empty($_POST['title'])) {
				$msg->setHighlight('title');
				$msg->setError('Введите title');
			}

			if (empty($_POST['name'])) {
				$msg->setHighlight('name');
				$msg->setError('Введите name');
			}

			if (empty($_POST['value'])) {
				$msg->setHighlight('value');
				$msg->setError('Введите value');
			}

			if (empty($_POST['namemodule'])) {
				$msg->setHighlight('namemodule');
				$msg->setError('Введите namemodule');
			}

			if (empty($_POST['fieldset'])) {
				$msg->setHighlight('fieldset');
				$msg->setError('Введите fieldset');
			}

			if (!$msg->keep) {
				switch ($se->config->setVariable($_POST['name'], $_POST['value'], $_POST['namemodule'], $_POST['fieldset'], $_POST['type'], $_POST['title'])) {
					case 1: $msg->setOk('Переменная добавлена'); break;
					case 2: $msg->setInfo('Переменная обновлена'); break;
					case 0: $msg->setError('Ошибка при сохранении'); break;
				}
			}
		break;
	}
	$response = array_merge($response, $msg->get());
	echo json_encode($response);
}
?>