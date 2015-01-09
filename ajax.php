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
			if ($se->config->saveForm($_POST['config'], CSeagullEvents::nameModule)) {
				$se->config->getVariables(CSeagullEvents::nameModule);

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
			$se->config->addVariable($_POST);
		break;
	}
	$response = array_merge($response, $msg->get());
	echo json_encode($response);
}
?>
