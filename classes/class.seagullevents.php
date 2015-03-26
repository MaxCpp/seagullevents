<?php
/*
	Class SeagullEvents	0.0.8
	Update 0.0.8: 2015-03-20
		- add range date;
	Update 0.0.7: 2015-01-10
		- add calendarList();
	Update 0.0.6: 2014-11-28
		- add calendar for year;
	Update 0.0.5: 2014-11-28
		- add calendar (only one month);
	Update 0.0.4: 2014-04-21
	Update 0.0.3: 2013-02-27
	Update 0.0.2: 2013-02-27
	Date start: 2012-12-08
*/
require_once($_SERVER['DOCUMENT_ROOT'].'/assets/modules/seagulllibrary/class.seagullmodule.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/assets/modules/seagulltags/classes/class.seagulltags.php');

class CSeagullEvents extends CSeagullModule {
	var $modx = null;
	var $ph = array('ver'=>'0.0.7');
	var $tables = array();
	var $langs = array('ru'=>'Русская версия', 'ua'=>'Украинская версия', 'en'=>'Английская версия');
	var $nameMonth = array(1=>'Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь', 'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь');
	var $nameDay = array(1=>'Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб', 'Вс');
	var $nameDayFull = array(1=>'Понедельник', 'Вторник', 'Среда', 'Четверг', 'Пятница', 'Суббота', 'Воскресенье');
	var $lang_default = 'ru';
	var $lang_cur = 'ru';
	var $lang_cols = array();
	static $url = 'meropriyatiya/';
	static $tableEvents = 'seagull_events';
	static $GUID = '0ec522a9b70811ee6c993822551a1c0a';
	const nameModule = 'seagullevents';

	function __construct() { //----------------------------------------------
		$args = func_get_args();
		if (isset($args[0])) {
			$this->msg = $args[0];
		}
		if (isset($args[1])) {
			$this->modx = $args[1];
		}

		$this->config = new CConfig($this->msg);
		$this->config->getVariables(self::nameModule);

		$this->ph['title'] = 'Управление мероприятиями';
		$this->ph['nameModule'] = self::nameModule;

		$this->lang = array(
			'title_add' => 'Добавление мероприятия',
			'title_edit' => 'Редактирование мероприятия',
			'title_install' => 'Установка модуля',
			'dont_deleted' => 'Мероприятие не удалено',
			'error_when_editing' => 'Ошибка при редактировании мероприятия',
			'item' => 'Мероприятие',
			'items' => 'мероприятия',
			'saved' => 'Мероприятие сохранено'
		);
//-------------------------------------------------
		$columns = array();
		$columns['id'] = array(
					'title'=>'ID',
					'form_hidden'=>true,
					'form_dontEdit'=>true,
					'table_theadParam'=>'style="width:30px"'
					);

		$columns['published'] =	array(
					'title'=>'Опубликован',
					'form_fieldType'=>'checkbox',
					'table_td_content'=>array('published'=>array(0=>'<div class="b-unpublished" title="Скрыт"></div>', 1=>'<div class="b-published" title="Опубликован"></div>')),
					'table_theadParam'=>'style="width:20px"',
					'table_title_hidden'=>true
					);

		$columns['special'] =	array(
					'title'=>'Опубликовать на главной',
					'form_fieldType'=>'checkbox',
					'table_td_content'=>array('special'=>array(0=>'<div class="b-unpublished" title="Скрыт"></div>', 1=>'<div class="b-published" title="Опубликован на главной"></div>')),
					'table_theadParam'=>'style="width:20px"',
					'table_title_hidden'=>true
					);

		$columns['type'] = array(
					'title'=>'Тип новости',
					'form_fieldType'=>'radio',
					'values'=>array('news'=>'новость','announcement'=>'анонс'),
					'value_default'=>'news',
					'table_hidden'=>true
					);

		$columns['date_begin'] = array(
					'title'=>'Дата начала',
					'form_fieldType'=>'date',
					'form_fieldParam'=>'class="b-datepicker"',
					'form_mysql_mask'=>'FROM_UNIXTIME(`date_begin`, "%d.%m.%Y") `date_begin`',
					'table_mysql_mask'=>'FROM_UNIXTIME(`date_begin`, "%d.%m.%Y") `date_begin`',
					'table_theadParam'=>'style="width:85px"'
					);

		$columns['date_end'] = array(
					'title'=>'Дата окончания',
					'form_fieldType'=>'date',
					'form_fieldParam'=>'class="b-datepicker"',
					'form_mysql_mask'=>'FROM_UNIXTIME(`date_end`, "%d.%m.%Y") `date_end`',
					'table_mysql_mask'=>'FROM_UNIXTIME(`date_end`, "%d.%m.%Y") `date_end`',
					'table_theadParam'=>'style="width:85px"'
					);

		$columns['time_begin'] = array(
					'title'=>'Время начала',
					'form_fieldType'=>'time',
					'form_fieldParam'=>'class="b-timepicker" style="width:50px"',
					'form_mysql_mask'=>'DATE_FORMAT(`time_begin`, "%H:%i") `time_begin`',
					'table_theadParam'=>'style="width:55px"'
					);

		$columns['time_end'] = array(
					'title'=>'Время конца',
					'form_fieldType'=>'time',
					'form_fieldParam'=>'class="b-timepicker" style="width:50px"',
					'form_mysql_mask'=>'DATE_FORMAT(`time_end`, "%H:%i") `time_end`',
					'table_theadParam'=>'style="width:55px"'
					);

		$columns['title'] =	array(
					'title'=>'Заголовок',
					'multilang'=>true,
					'form_fieldType'=>'input',
					'form_fieldParam'=>'style="width:50%;min-width:400px;"'
					);

		$columns['alias'] =	array(
					'title'=>'Псевдоним (URL)',
					'multilang'=>true,
					'form_fieldType'=>'input',
					'form_fieldParam'=>'style="width:50%;min-width:400px;"',
					'form_hidden'=>($this->config->identifyByURL==='alias' ? false : true),
					'table_hidden'=>true
					);

		if ($this->config->multilang->active) {
			$columns['ru'] = array(
						'non-exist'=>true,
						'title'=>'RU',
						'table_td_content'=>array('content_ru'=>array(0=>'<div class="b-false" title="Русская версия - ПУСТА"></div>', 1=>'<div class="b-true" title="Русская версия - ЗАПОЛНЕНА"></div>'))
						);

			$columns['ua'] = array(
						'non-exist'=>true,
						'title'=>'UA',
						'table_td_content'=>array('content_ua'=>array(0=>'<div class="b-false" title="Украинская версия - ПУСТА"></div>', 1=>'<div class="b-true" title="Украинская версия - ЗАПОЛНЕНА"></div>'))
						);

			$columns['en'] = array(
						'non-exist'=>true,
						'title'=>'EN',
						'table_td_content'=>array('content_en'=>array(0=>'<div class="b-false" title="Английская версия - ПУСТА"></div>', 1=>'<div class="b-true" title="Английская версия - ЗАПОЛНЕНА"></div>'))
						);
		}

		$columns['description'] = array(
					'title'=>'Описание',
					'multilang'=>true,
					'form_fieldType'=>'textarea',
					'form_fieldParam'=>'style="width:50%;min-width:400px;"',
					'table_hidden'=>true
					);

		$columns['content'] = array(
//					'title'=>'Содержимое',
					'multilang'=>true,
					'form_fieldType'=>'textarea',
					'form_fieldParam'=>'class="ckeditor" style="width:80%"',
					'table_hidden'=>true
					);

		if ($this->config->allow_tags) {
			$columns['tags'] = array(
						'title'=>'Тэги',
						'form_fieldType'=>'input',
						'form_fieldParam'=>'style="width:50%;min-width:300px;"',
						'table_theadParam'=>'style="width:20%"',
						'form_callback'=>function($row) {
							$output = CSeagullTags::renderTags($row['id'], 'seagullevents');
							$output = empty($output) ? $output : $output.', ';
							return $output;
						},
						'table_callback'=>function($row) {
							return CSeagullTags::renderTags($row['id'], 'seagullevents');
						}
					);
		}


		$columns['author'] = array(
					'title'=>'Автор',
					'form_fieldType'=>'input',
					'form_dontEdit'=>true,
					'values'=>'',
					'table_theadParam'=>'style="width:100px"'
					);

		$columns['date_update'] = array(
					'title'=>'Обновление',
					'form_hidden'=>true,
					'form_fieldType'=>'date',
					'form_mysql_mask'=>'FROM_UNIXTIME(`date_update`, "%d.%m.%Y %h:%i") `date_update`',
					'values'=>'unix_timestamp(now())',
					'table_mysql_mask'=>'FROM_UNIXTIME(`date_update`, "%d.%m.%Y %h:%i") `date_update`',
					'table_theadParam'=>'style="width:102px"'
					);

		$columns['date_published'] = array(
					'title'=>'Дата публикации',
					'form_fieldType'=>'date',
					'form_fieldParam'=>'class="b-datepicker"',
					'form_mysql_mask'=>'FROM_UNIXTIME(`date_published`, "%d.%m.%Y") `date_published`',
					'table_mysql_mask'=>'FROM_UNIXTIME(`date_published`, "%d.%m.%Y") `date_published`',
					'table_theadParam'=>'style="width:85px"'
					);


		if ($this->config->multilang->active) {
			foreach ($this->langs as $lang=>$text) {
				if ($this->lang_default === $lang)
					$select_i18n .= 'IF(`content`!=\'\',1,0) as `content_'.$lang.'`,';
				else
					$select_i18n .= 'IF(`content_'.$lang.'`!=\'\',1,0) as `content_'.$lang.'`,';
			}
		}
		$this->tables['events'] = new CEditTable(self::$tableEvents, $columns);
		$this->tables['events']->setConfig('table_mysql_select', '`id`, `published`, `special`, `title`, '.$select_i18n.' FROM_UNIXTIME(`date_begin`, "%d.%m.%Y") `date_begin`, FROM_UNIXTIME(`date_end`, "%d.%m.%Y") `date_end`, DATE_FORMAT(`time_begin`, "%H:%i") `time_begin`, DATE_FORMAT(`time_end`, "%H:%i") `time_end`, FROM_UNIXTIME(`date_published`, "%d.%m.%Y") `date_published`, FROM_UNIXTIME(`date_update`, "%d.%m.%Y %H:%i") `date_update`');
		$this->tables['events']->setConfig('table_param', 'id="t-events" class="b-table tpaginator" cellpadding="0" cellspacing="0"');
		$this->tables['events']->setConfig('tr_param', array('id'=>' id="row%id%" class="row-edit"'));
		$this->tables['events']->setConfig('multilang', $this->config->multilang->active);
		$this->tables['events']->setConfig('sort_direct', 'DESC');
		$this->tables['events']->setConfig('label_begin', '<label style="width:110px; display:inline-block;">');
		$this->tables['events']->setConfig('paginatorRowsByPage', $this->config->backend->rowsByPage);
		$this->tables['events']->setConfig('paginatorAdvLinks', $this->config->backend->advLinks);

	}

	function handlePost() { //-------------------------------------------
		switch($_POST['cmd']) {
			case 'install':
				$this->install();
				$this->ph['title'] = $this->lang['title_install'];
				$this->file_tpl = 'install';
				$this->ph['msg'] = $this->msg->renderAll();
				return 1;
			break;

			case 'addEvent':
				$this->ph['render_form'] = $this->tables['events']->renderForm();
				$this->ph['event_id'] = $_POST['itemID'];
				$this->ph['title'] = $this->lang['title_add'];
				$this->file_tpl = 'edit';
			break;

			case 'editEvent':
				$this->ph['render_form'] = $this->tables['events']->renderForm($_POST['itemID']);
				$this->ph['event_id'] = $_POST['itemID'];
				$this->ph['title'] = $this->lang['title_edit'].' №'.$_POST['itemID'];

//				MULTI LANG
				if ($this->config->multilang->active) {
					foreach ($this->langs as $lang=>$text) {
						if ($this->lang_default !== $lang)
							$this->ph['render_form_i18n'] .= '<div class="b-tabs__page" id="lang_'.$lang.'">'.$this->tables['events']->renderFormLang($_POST['itemID'], $lang).'</div>';
					}
					$this->file_tpl = 'edit_multilang';
					$this->ph['lang_default'] = $this->lang_default;
				}
				else $this->file_tpl = 'edit';
			break;

			case 'delEvent':
				if ($this->tables['events']->del($_POST['itemID']))
					$this->msg->setOk($this->lang['item'].' №'.$_POST['itemID'].' удалено');
				else
					$this->msg->setError($this->lang['dont_deleted']);

				$this->tables['events']->setConfig('table_caption', 'Новости');
				$this->ph['event_list'] = $this->tables['events']->renderTable(1, "`type`='news'");
				$this->tables['events']->setConfig('table_caption', 'Аннонсы');
				$this->ph['announcement_list'] = $this->tables['events']->renderTable(1, "`type`='announcement'");
				$this->file_tpl = 'main';
			break;

			case 'config':
				$this->ph['config'] = $this->config->renderForm(self::nameModule);
				$this->file_tpl = 'config';
			break;

			default:
				$this->tables['events']->setConfig('table_caption', 'Новости');
				$this->ph['event_list'] = $this->tables['events']->renderTable(1, "`type`='news'");
				$this->tables['events']->setConfig('table_caption', 'Аннонсы');
				$this->ph['announcement_list'] = $this->tables['events']->renderTable(1, "`type`='announcement'");
				$this->file_tpl = 'main';
			break;
		}
		$this->ph['msgType'] = 'msg_'.$this->msg->getType();
		$this->ph['msg'] = $this->msg->render();
//		$this->modx->getLoginUserName($this->modx->getLoginUserID())
	}

	function saveEvent($aData) { //----------------------------------------------

		if (isset($aData['title']) and empty($aData['title']))
			$this->msg->setError('Введите <strong>Заголовок статьи</strong>');

		if (isset($aData['date_begin']) and empty($aData['date_begin']))
			$this->msg->setError('Введите <strong>Дату события</strong>');

		$aData['alias'] = empty($aData['alias']) ? translit2URL($aData['title']) : $aData['alias'];

		if (!$this->msg->keep) {
			if (isset($aData['itemID']) and !empty($aData['itemID'])) {

//	----------- TAGS -----------------------------
				if ($this->config->allow_tags) {
					$this->tags = new CSeagullTags($this->msg);
					$aData['tags'] = $this->tags->saveTags($aData['tags'], $aData['itemID'], self::nameModule);
				}
//	----------------------------------------------
				$aData['date_begin'] = empty($aData['date_begin']) ? date('d.m.Y', time()) : $aData['date_begin'];
				$aData['date_end'] = empty($aData['date_end']) ? '' : $aData['date_end'];

				$aData['date_update'] = $aData['date_published'] = time();
				$eventID = $this->tables['events']->updateRow($aData['itemID'], $aData);
				if ($eventID) {
					$this->msg->setOk($this->lang['saved']);
					$this->msg->setVar('itemID', $aData['itemID']);
				}
				else
					$this->msg->setError($this->lang['error_when_editing']);
			}
			else {
				$aData['date_begin'] = empty($aData['date_begin']) ? date('d.m.Y', time()) : $aData['date_begin'];
				$aData['date_end'] = empty($aData['date_end']) ? '' : $aData['date_end'];

				$aData['date_update'] = $aData['date_published'] = time();
				$eventID = $this->tables['events']->saveForm($aData['itemID'], $aData);

				if ($eventID) {
					if (!empty($aData['tags'])) {
						$arr = array();

						if ($this->config->allow_tags) {
							$this->tags = new CSeagullTags($this->msg);
							$arr['tags'] = $this->tags->saveTags($aData['tags'], $eventID, self::nameModule, 1);
						}

						$this->tables['events']->updateRow($eventID, $arr);
					}
					$this->msg->setOk($this->lang['saved']);
					$this->msg->setVar('itemID', $eventID);
					$this->msg->setReload();
				}
				else
					$this->msg->setError($this->lang['error_when_editing']);
			}
		}

//		MULTI LANGUAGE -------------------------------------
		if ($this->config->multilang->active and isset($aData['itemID']) and !empty($aData['itemID'])) {
			foreach ($this->langs as $lang=>$text) {
				if ($this->lang_default !== $lang)
					$aData['alias_'.$lang] = empty($aData['alias_'.$lang]) ? translit2URL($aData['title_'.$lang]) : $aData['alias_'.$lang];
			}
			$this->tables['events']->saveRowLang($aData['itemID'], $aData, $this->langs, $this->lang_default);
		}

	}

	function getEvent($param) { //----------------------------------------------

		if (is_array($param)) {

			$date = mktime(0, 0, 0, $param[2], $param[3], $param[1]);
//echo 'SELECT `id`, `title`, `content`, `author`, `alias`, FROM_UNIXTIME(`date_begin`, "%d.%m.%Y") `date`, FROM_UNIXTIME(`date_begin`, "%Y-%m-%d") `url` FROM '.$this->tables['events']->table.' WHERE '.$date.'<`date_begin` AND `date_begin`<'.($date+86400);
			$event = retr_sql('SELECT `id`, `title`, `content`, `author`, `alias`, FROM_UNIXTIME(`date_begin`, "%d.%m.%Y") `date`, FROM_UNIXTIME(`date_begin`, "%Y-%m-%d") `url` FROM '.$this->tables['events']->table.' WHERE '.$date.'<`date_begin` AND `date_begin`<'.($date+86400));
			if ($event)
				return $event;
		}
		elseif (is_numeric($param)) {
			$event = retr_sql('SELECT `id`, `title`, `content`, `author`, `alias`, FROM_UNIXTIME(`date_begin`, "%d.%m.%Y") `date`, FROM_UNIXTIME(`date_begin`, "%Y-%m-%d") `url` FROM '.$this->tables['events']->table." WHERE `id`='$param'");
			if ($event)
				return $event;
		}
		elseif (is_string($param)) {
			$event = retr_sql('SELECT `id`, `title`, `content`, `author`, `alias`, FROM_UNIXTIME(`date_begin`, "%d.%m.%Y") `date`, FROM_UNIXTIME(`date_begin`, "%Y-%m-%d") `url` FROM '.$this->tables['events']->table." WHERE `alias`='$param'");
			if ($event)
				return $event;
		}
		return 0;
	}

	function handleSnippet($view=NULL, $count=NULL, $type=NULL, $lang=NULL, $year=NULL, $special=NULL) { //------------------------------------------------------

		$this->type = isset($type) ? $type : 'both';
		$this->special = isset($special) ? $special : 0;
		$year = isset($year) ? (($year == 'current') ? date('Y') : $year) : date('Y');

		switch ($view) {
			case 'small':
			case 'medium':
			case 'large':
				return $this->renderEvents($view, $count, $type, $lang);
			break;

			case 'calendar':
				return $this->renderCalendar($year);
			break;

			case 'calendarMonth':
				return $this->renderCalendar($year, date('n'));
			break;

			case 'calendarList':
				return $this->renderCalendarList($year);
			break;

			default:
				if ($_GET['event']) {
					return $this->renderEvent($_GET['event']);
				}
				elseif ($_GET['year']) {
					return $this->renderEvents($view, $count, $type, $lang, $_GET);
				}
				else {
					return $this->renderEvents($view, $count, 'announcement', $lang);
				}
			break;
		}
	}

	function renderEvent($event, $view='medium') { //----------------------------------------------
		$output = '';

/*		if (array_key_exists($alias, $this->modx->documentListing)) {
			$this->documentIdentifier= $this->modx->documentListing[$alias];
		}
*/
//		Отображение одной статьи
		if (isset($event)) {
			switch ($this->config->identifyByURL) {
				case 'id': {
					if (is_numeric($event)) {
						$event = $this->getEvent($event);
					}
				} break;

				case 'date': {
					if (preg_match('/(\d{4})/(\d{2})/(\d{2})/', $event, $date)) {
						$event = $this->getEvent($date);
					}
				} break;

				case 'alias': {
					if (is_string($event)) {
						$event = $this->getEvent($event);
					}
				} break;
			}
//			ea($event);
			$this->modx->regClientStartupHTMLBlock('<title>'.$event['title'].'</title>');
//			echo $this->modx->documentContent;
//			$this->modx->documentContent = $event['content'];
//			$this->modx->documentObject['pagetitle'] = $event['title'];
//			$event['content'] = str_replace('<hr />', '', $event['content']);
//			$event['title'] = $this->modx->documentObject['pagetitle'].': '.$event['title'];
			$output = $this->parseTemplate('frontend/event', $event);
		}

//		Список мероприятий внизу читаемого мероприятия
		if ($this->config->countAdvEvents) {
			$output .= '<p class="lastevent">Последние '.$this->lang['items'].'<span class="lastevent__line"></span></p>';
		}
//		$doc = $this->modx->getDocument($this->modx->documentIdentifier);
//		ea($doc);
		$url = $this->modx->makeURL($this->modx->documentIdentifier);

		if ($count) {
			switch ($view) {
				case 'medium':
					$arr = sql2table('SELECT `title`, `description`, FROM_UNIXTIME(`date_begin`, "%d.%m.%Y") `date`, FROM_UNIXTIME(`date_begin`, "%Y-%m-%d") `url` FROM '.$this->tables['events']->table." WHERE `type`='$type' ORDER BY `date_begin` DESC LIMIT $count");
					$output .= '<div class="event-medium-list">';
					foreach ($arr as $item) {
						$output .= '<div class="event-medium"><span class="event-medium__date">'.$item['date'].'</span><a href="'.self::$url.$item['url'].'" class="event-medium__title">'.$item['title'].'</a><div class="event-medium__desc">'.$item['description'].'</div></div>';
					}
					$output .= '</div>';
				break;

				case 'large':
					$arr = sql2table('SELECT `title`, FROM_UNIXTIME(`date_begin`, "%d.%m.%Y") `date`, `content` FROM '.$this->tables['events']->table." WHERE `type`='$type' ORDER BY `date_begin` DESC LIMIT $count");
					$output .= '<ul class="event-list">';
					foreach ($arr as $item) {
						$output .= '<li class="event"><span class="event__date">'.$item['date'].'</span><h3 class="event__title">'.$item['title'].'</h3><div class="event__desc">'.$item['description'].'</div></li>';
					}
					$output .= '</ul>';
				break;
			}
		}

/*		if ($this->config->allow_comment) {
			require_once(SITE_ROOT.'/assets/modules/seagullcomments/classes/class.seagullcomments.php');

			$this->comment = new CSeagullComments($this->msg, $this->modx);
			$output .= $this->comment->renderComments($event['id'], self::nameModule);
			$output .= $this->comment->renderForm($event['id'], self::nameModule);
		}*/
		return $output;
	}

	function renderEvents($view=NULL, $count=NULL, $type=NULL, $lang=NULL, $date=NULL) { //----------------------------------------------
		$count = isset($count) ? $count : 10;
		$view = isset($view) ? $view : 'medium';
		// $type = isset($type) ? $type : 'news';
		$lang = isset($lang) ? $lang : $this->lang_default;
		$output = '';

		switch ($this->config->identifyByURL) {
			case 'id': {
				$url = '`id` as `url`';
			} break;

			case 'date': {
				$url = 'FROM_UNIXTIME(`date_begin`, "%Y/%m/%d") `url`';
			} break;

			case 'alias': {
				$url = (!$this->config->multilang->active and $this->lang_default === $lang) ? '`alias` as `url`' : '`alias_'.$lang.'` as `url`';
			} break;
		}

		// $url = $this->modx->makeURL($this->modx->documentIdentifier);
		$tpl = $this->getTpl('frontend/event_'.$view.'_row');
		$tpl_without_date = $this->getTpl('frontend/event_'.$view.'_row_last');

		if ($this->config->multilang->active and $this->lang_default !== $lang) {
			$select = '`alias_'.$lang.'` as `alias`, `title_'.$lang.'` as `title`, `description_'.$lang.'` as `description`, `content_'.$lang.'` as `content`, '.$url;
			$table_i18n = ','.$this->tables['events']->table_i18n;
			$where = '`id`=`event_id` AND';
		}
		else {
			$select = '`title`, `description`, `content`, '.$url;
			$table_i18n = $where = '';
		}
		$where .= is_null($type) ? '' : "`type`='$type' AND ";
		$where .= $this->special ? "`special`='1' AND " : '';

		if (isset($date)) {
			$date_begin = mktime(0,0,0, $date['month'], $date['day'], $date['year']);
			$date_end = $date_begin+86400;
			$arr = sql2table('SELECT '.$select.', `id`, `tags`, `type`, FROM_UNIXTIME(`date_begin`, "%d.%m.%Y") `date_begin`, FROM_UNIXTIME(`date_end`, "%d.%m.%Y") `date_end` FROM '.$this->tables['events']->table.$table_i18n." WHERE $where `published`='1' AND `date_begin`>=$date_begin AND `date_begin`<=$date_end ORDER BY `date_begin` DESC LIMIT $count");
			// echo 'SELECT '.$select.', `id`, `tags`, `type`, FROM_UNIXTIME(`date_begin`, "%d.%m.%Y") `date` FROM '.$this->tables['events']->table.$table_i18n." WHERE $where `published`='1' AND `date_begin`>=$date_begin AND `date_begin`<=$date_end ORDER BY `date_begin` DESC LIMIT $count";
		} else
			$arr = sql2table('SELECT '.$select.', `id`, `tags`, `type`, FROM_UNIXTIME(`date_begin`, "%d.%m.%Y") `date_begin`, FROM_UNIXTIME(`date_end`, "%d.%m.%Y") `date_end` FROM '.$this->tables['events']->table.$table_i18n." WHERE $where `published`='1' ORDER BY `date_begin` DESC LIMIT $count");
		// echo 'SELECT '.$select.', `tags`, FROM_UNIXTIME(`date_begin`, "%d.%m.%Y") `date` FROM '.$this->tables['events']->table.$table_i18n." WHERE $where `type`='$type' AND `published`='1' ORDER BY `date_begin` DESC LIMIT $count";
		// ea($arr);
		if ($arr) {
			if ($this->config->allow_tags) {
				$this->tags = new CSeagullTags($this->msg);
			}

			foreach ($arr as $item) {
				$item['url'] = '/'.self::$url.$item['url'];
				$item['content'] = substr($item['content'], 0, strpos($item['content'], '<hr />'));
				// $item['date'] = date('j', $item['date_begin']).' '.date('m', $item['date_begin']).' '.date('Y', $item['date_begin']);
				// if ($item['date_end'])
				// 	$item['date'] .= ' - '.date('j', $item['date_end']).' '.date('m', $item['date_end']).' '.date('Y', $item['date_end']);
				$item['date'] = date2format($item['date_begin'], 'd m');
				// $item['date'] = $item['date_begin'];
				if ($item['date_end'])
					$item['date'] .= ' - '.date2format($item['date_end'], 'd m');

				$item['date'] .= date2format($item['date_begin'], ' Y');

	// ----------- TAGS -----------------------------
				if ($this->config->allow_tags) {
					$item['tags'] = $this->tags->renderTags($item['id'], self::nameModule, 'link');
					$item['tags'] = $item['tags'] ? '<span class="event__tags">'.$item['tags'].'</span>' : '';
				}

				// if ($item['type'] === 'news')
				// 	$output .= $this->parseContent($tpl_without_date, $item);
				// else
				$output .= $this->parseContent($tpl, $item);
			}
		}
		else
			$output = '<p style="text-align:center">Ближайшие даты тренингов уточняются</p>';

		return $output;
	}

	public static function m_renderEventsByTag($strIDs, $tempmodx) { //----------------------------------------------
		$output = '';

		if ($strIDs) {
			$arr = sql2table('SELECT `id`, `title`, FROM_UNIXTIME(`date_begin`, "%d.%m.%Y") `date` '.$url.' FROM '.self::$tableEvents." WHERE `id` IN ($strIDs) ORDER BY `date_begin` DESC");

			if ($arr) {
				$module = retr_sql('SELECT `id`, `name` FROM `'.$tempmodx->dbConfig['table_prefix'].'site_modules` WHERE `GUID`=\''.self::$GUID.'\'');
				$output['nameModule'] = $module['name'];
				foreach ($arr as $item) {
					$output['list'] .= '<li><a href="/manager/index.php?a=112&id='.$module['id'].'" onclick="postFormModule(this, \'editEvent\', '.$item['id'].'); return false;">['.$item['id'].']&nbsp;&nbsp;'.$item['title'].'</a></li>';
				}

			}
		}
		return $output;
	}

	function renderEventsByTag($strIDs) { //----------------------------------------------
		$output = '';

		if ($strIDs) {
			switch ($this->config->identifyByURL) {
				case 'id': {
					$url = ', `id` as `url`';
				} break;

				case 'date': {
					$url = ', FROM_UNIXTIME(`date_begin`, "%Y-%m-%d") `url`';
				} break;

				case 'alias': {
					$url = ', `alias` as `url`';
				} break;
			}

			$arr = sql2table('SELECT `id`, `title`, FROM_UNIXTIME(`date_begin`, "%d.%m.%Y") `date` '.$url.' FROM '.self::$tableEvents." WHERE `id` IN ($strIDs) ORDER BY `date_begin` DESC");

			if ($arr) {
				$module = retr_sql('SELECT `id`, `name` FROM `'.$this->modx->dbConfig['table_prefix'].'site_modules` WHERE `GUID`=\''.self::$GUID.'\'');
				$output['nameModule'] = $module['name'];
				foreach ($arr as $item) {
					$output['list'] .= '<li><a href="/'.self::$url.$item['url'].'">'.$item['title'].'</a></li>';
				}

			}
		}
		return $output;
	}

	function renderCalendar($year = NULL, $month = NULL, $neighbors = true) {

		$output = '';
		// Рендер календаря на год
		if (is_null($month)) {

			for ($i = 1; $i <= 12; $i++) {
				$output .= $this->renderCalendarMonth($year, $i, $neighbors);
			}
			$output = '<div class="calendar calendar-year" data-year="'.$year.'"><h2>'.$year.'</h2>'.$output.'</div>';
			$calendarView = 0;
		}
		else {
			$output = '<div class="calendar" data-year="'.$year.'">'.$this->renderCalendarMonth($year, $month, $neighbors).'</div>';
			$calendarView = $month;
		}

		if ($this->calendarData) {
			$this->calendarData = json_encode($this->calendarData);
			$output .= '<script type="text/javascript">calendarData['.$calendarView.']='.$this->calendarData.';</script>';
		}

		return $output;
	}

	function renderCalendarMonth($year = NULL, $month = NULL, $neighbors = false) { //-------------------------------------------------ion renderCalendar($month = NULL, $year = NULL, $type = NULL) { //-------------------------------------------------

		$begin_month = mktime(0, 0, 0, $month, 1, $year);
		$dayofmonth = date('t', $begin_month); // Вычисляем число дней в текущем месяце
		$end_month = mktime(23, 59, 59, $month, $dayofmonth, $year);
		$today_date = date('d');
		$cur_month = (date('m') == $month) ? true : false;

		// if ($neighbors) {
		// 	$backDays = date('w', $begin_month) - 1;
		// 	$begin_month = mktime(0, 0, 0, $month, 1-$backDays, $year);
		// }

		$where = ($this->type === 'both') ? '' : ' AND `type`=\''.$this->type.'\'';
		$where .= ' AND ((`date_begin`>'.$begin_month.' AND `date_begin`<'.$end_month.') OR (`date_end`>'.$begin_month.' AND `date_end`<'.$end_month.'))';

		// $arr = sql2array('SELECT `date_begin`, FROM_UNIXTIME(`date_begin`, "%d") `day`, FROM_UNIXTIME(`date_begin`, "%Y/%m/%d") `url` FROM '.$this->tables['events']->table." WHERE `published`='1' $where GROUP BY `date_begin` ORDER BY `date_begin` DESC", 'day');
		$aEvents = sql2table('SELECT `id`, `date_begin`, `date_end`, `title`, FROM_UNIXTIME(`date_begin`, "%e") `day_begin`, FROM_UNIXTIME(`date_end`, "%e") `day_end`, DATE_FORMAT(`time_begin`, "%H:%i") `time_begin`, DATE_FORMAT(`time_end`, "%H:%i") `time_end`, FROM_UNIXTIME(`date_begin`, "%Y/%m/%d") `url` FROM '.$this->tables['events']->table." WHERE `published`='1' $where ORDER BY `date_begin` DESC");
					// echo 'SELECT `id`, `date_begin`, `title`, FROM_UNIXTIME(`date_begin`, "%d") `day`, FROM_UNIXTIME(`date_begin`, "%Y/%m/%d") `url` FROM '.$this->tables['events']->table." WHERE `published`='1' $where ORDER BY `date_begin` DESC";
// ea($aEvents);
		$arr = array();
		$aDaysEvents = array();

		for ($i = $begin_month; $i <= $end_month; $i = $i + 86400) {
			foreach ($aEvents as $key => $value) {
				if ($value['date_begin'] == $i or ($value['date_begin'] < $i and $i <= $value['date_end'])) {
					$day = date('j', $i);
					if (!array_key_exists($day, $aDaysEvents)) {
						$aDaysEvents[$day] = array(
							'date_begin' => $value['date_begin'],
							'day_begin' => $value['day_begin'],
							'url' => $value['url'],
							'events' => array(
								$value['id'] => array(
									'title' => $value['title'],
									'time' => $value['time_begin'] ? $value['time_begin'].'-'.$value['time_end'] : ''
								)
							)
						);
					} else {
						$aDaysEvents[$day]['events'][$value['id']] = array(
							'title' => $value['title'],
							'time' => $value['time_begin'] ? $value['time_begin'].'-'.$value['time_end'] : ''
						);
					}
				}
			}
		}
// ea($aDaysEvents);
// ea($arr);
		$num = 0;

		if ($neighbors) {
			// $back = getdate($begin_month);

			// ea($back);

			// $day_count = $back['mday']; // Счётчик для дней месяца

			// for ($i = 0; $i < 7; $i++) {
			// 	// Вычисляем номер дня недели для числа
			// 	$dayofweek = date('w', mktime(0, 0, 0, $month, 1-$backDays, $year));
			// 	// Приводим к числа к формату 1 - понедельник, ..., 6 - суббота
			// 	$dayofweek = $dayofweek - 1;
			// 	if ($dayofweek == -1) $dayofweek = 6;
			// 	echo $i,' ',$dayofweek,'<br>';

			// 	if ($dayofweek == $i) {
			// 		// Если дни недели совпадают, заполняем массив $week числами месяца
			// 		$week[$num][$i] = array_key_exists($day_count, $arr) ? '<a href="/'.$this->lang_cur.'/'.self::$url.$arr[$day_count]['url'].'">'.$day_count.'</a>' : $day_count;
			// 		$backDays--;
			// 		$day_count++;
			// 	}
			// 	else {
			// 		$week[$num][$i] = '';
			// 	}
			// }
		}
		$day_count = 1; // Счётчик для дней месяца

		// 1. Первая неделя
		// $num = 0;
		for ($i = 0; $i < 7; $i++) {
			// Вычисляем номер дня недели для числа
			$dayofweek = date('w', mktime(0, 0, 0, $month, $day_count, $year));
			// $dayofweek = date('w', mktime(0, 0, 0, $month, $day_count-$backDays, $year));
			// Приводим к числа к формату 1 - понедельник, ..., 6 - суббота
			$dayofweek = $dayofweek - 1;
			if ($dayofweek == -1) $dayofweek = 6;

			if ($dayofweek == $i) {
				// Если дни недели совпадают, заполняем массив $week числами месяца
				$week[$num][$i] = $day_count;
					$day_count++;
				// if ($backDays === 0)
				// 	$day_count++;
				// else
				// 	$backDays--;
			}
			else {
				$week[$num][$i] = '';
			}
		}

		// 2. Последующие недели месяца
		while (true) {
			$num++;
			for ($i = 0; $i < 7; $i++) {
				$week[$num][$i] = $day_count;
				$day_count++;
				// Если достигли конца месяца - выходим из цикла
				if($day_count > $dayofmonth) break;
			}
			// Если достигли конца месяца - выходим из цикла
			if($day_count > $dayofmonth) break;
		}
//ea($week);
// ea($aEvents);
		// 3. Выводим содержимое массива $week в виде календаря. Выводим таблицу
		$date = current($aDaysEvents);
		$js = '';
		$output = '';

		$output = '<div class="calendar-month" data-year="'.$year.'" data-month="'.$month.'"><table class="events-calendar" border="0"><tr><td colspan="7" class="calendar-month__title">'.$this->nameMonth[$month].' '.$year.'</td></tr><tr><td>пн</td><td>вт</td><td>ср</td><td>чт</td><td>пт</td><td>сб</td><td>вс</td></tr>';
		for ($i = 0; $i < count($week); $i++) {
			$output .= '<tr>';
			for ($j = 0; $j < 7; $j++) {
				if (!empty($week[$i][$j])) {
					// Если имеем дело с субботой и воскресенья подсвечиваем их
					$weekend = ($j == 5 || $j == 6) ? 'weekend' : '';
					$active = array_key_exists($week[$i][$j], $aDaysEvents) ? ' active' : '';
					$today = ($cur_month and $week[$i][$j] == $today_date) ? ' today' : '';

					$day = $active ? '<a href="/'.($this->config->multilang->active ? $this->lang_cur.'/' : '').self::$url.$aDaysEvents[$week[$i][$j]]['url'].'">'.$week[$i][$j].'</a>' : $week[$i][$j];

					$output .= '<td class="'.$weekend.$active.$today.'">'.$day.'</td>';
				}
				else {
					$output .= '<td>&nbsp;</td>';
				}

				$str = '';

				if ($aDaysEvents[$week[$i][$j]]['events']) {
					foreach ($aDaysEvents[$week[$i][$j]]['events'] as $key => $value) {
						$str .= '<div class="event" data-id="'.$key.'">'.($value['time'] ? '<div class="event__time">'.$value['time'].'</div>' : '').'<div class="event__title">'.$value['title'].'</div></div>';
					}

					$this->calendarData[$year][$month][$week[$i][$j]] = '<div class="calendar-popup__date">'.$week[$i][$j].' '.$this->nameMonth[$month].' ('.$this->nameDay[$j+1].')</div>'.$str;
				}
			}
			$output .= '</tr>';
		}
		$output .= '</table></div>';

		return $output;
	}

	function renderCalendarList($yaer=NULL) { //----------------------------------------------

		$year = isset($year) ? $year : date('Y');
		$count = isset($count) ? $count : 10;
		$output = '';

		switch ($this->config->identifyByURL) {
			case 'id': {
				$url = '`id` as `url`';
			} break;

			case 'date': {
				$url = 'FROM_UNIXTIME(`date_begin`, "%Y/%m/%d") `url`';
			} break;

			case 'alias': {
				$url = (!$this->config->multilang->active and $this->lang_default === $lang) ? '`alias` as `url`' : '`alias_'.$lang.'` as `url`';
			} break;
		}

//		$url = $this->modx->makeURL($this->modx->documentIdentifier);
		// $tpl = $this->getTpl('frontend/event_'.$view.'_row');
		$tpl_year = $this->getTpl('frontend/calendar-list_year');
		$tpl_month = $this->getTpl('frontend/calendar-list_month');
		$tpl_event = $this->getTpl('frontend/calendar-list_event');

		if ($this->config->multilang->active and $this->lang_default !== $lang) {
			$select = '`alias_'.$lang.'` as `alias`, `title_'.$lang.'` as `title`, `description_'.$lang.'` as `description`, `content_'.$lang.'` as `content`';
			$table_i18n = ','.$this->tables['events']->table_i18n;
			$where = '`id`=`event_id` AND';
		}
		else {
			$select = '`title`, `description`, `content`, '.$url;
			$table_i18n = $where = '';
		}
		$where .= ($this->type === 'both') ? '' : '`type`=\''.$this->type.'\' AND ';

		$begin_published = mktime(0, 0, 0, 1, 1, $year);
		$end_published = mktime(23, 59, 59, 12, 31, $year);

		$where .= '`date_begin`>'.$begin_published.' AND `date_begin`<'.$end_published.' AND ';


		if (isset($date)) {
			$date_begin = mktime(0,0,0, $date['month'], $date['day'], $date['year']);
			$date_end = $date_begin + 86400;
			$arr = sql2table('SELECT '.$select.', `id`, `tags`, `type`, FROM_UNIXTIME(`date_begin`, "%d.%m.%Y") `date` FROM '.$this->tables['events']->table.$table_i18n." WHERE $where `published`='1' AND `date_begin`>=$date_begin AND `date_begin`<=$date_end ORDER BY `date_begin` DESC LIMIT $count");
			// echo 'SELECT '.$select.', `id`, `tags`, `type`, FROM_UNIXTIME(`date_begin`, "%d.%m.%Y") `date` FROM '.$this->tables['events']->table.$table_i18n." WHERE $where `published`='1' AND `date_begin`>=$date_begin AND `date_begin`<=$date_end ORDER BY `date_begin` DESC LIMIT $count";
		} else {
			$arr = sql2table('SELECT '.$select.', `id`, `tags`, `type`, FROM_UNIXTIME(`date_begin`, "%d.%m.%Y") `date`, FROM_UNIXTIME(`date_begin`, "%Y") `year`, FROM_UNIXTIME(`date_begin`, "%c") `month`, FROM_UNIXTIME(`date_begin`, "%e") `day` FROM '.$this->tables['events']->table.$table_i18n." WHERE $where `published`='1' ORDER BY `year`, `month`, `date_begin` LIMIT $count");
		}

		if ($arr) {
			if ($this->config->allow_tags) {
				$this->tags = new CSeagullTags($this->msg);
			}

			$current_year = 0;
			$current_month = 0;
			$events = '';

			$c = count($arr)-1;

			foreach ($arr as $key => $item) {
				$item['url'] = '/'.self::$url.$item['url'];
				$item['content'] = substr($item['content'], 0, strpos($item['content'], '<hr />'));
//				$item['date'] = date2month($item['date']);

//	----------- TAGS -----------------------------
				if ($this->config->allow_tags) {
					$item['tags'] = $this->tags->renderTags($item['id'], self::nameModule, 'link');
					$item['tags'] = $item['tags'] ? '<span class="event__tags">'.$item['tags'].'</span>' : '';
				}

				if ($current_year !== $item['year']) {
					$current_year = $item['year'];
					if (!empty($events)) {
						$output .= '<div class="calendar-list-events">'.$events.'</div>';
						$events = '';
					}
					$output .= $this->parseContent($tpl_year, $item);
				}

				if ($current_month !== $item['month']) {
					$current_month = $item['month'];
					$item['month'] = $this->nameMonth[$item['month']];
					if (!empty($events)) {
						$output .= '<div class="calendar-list-events">'.$events.'</div>';
						$events = '';
					}
					$output .= $this->parseContent($tpl_month, $item);
				} else {
					$item['month'] = $this->nameMonth[$item['month']];
				}

				$events .= $this->parseContent($tpl_event, $item);

				if ($c == $key) {
					if (!empty($events)) {
						$output .= '<div class="calendar-list-events">'.$events.'</div>';
						$events = '';
					}
				}
			}
		}
		else
			$output = '<p style="text-align:center">Ближайшие даты тренингов уточняются</p>';

		return $output;
	}

	function getEventByDay($date) {

	}

	function install() { //----------------------------------------------
		global $dbase;

		$r = true;
		$this->config->install();
		$this->config->addModule(self::nameModule);

		$r &= (boolean)$this->config->setVariable('allow_comment', '0', self::nameModule, NULL, 'C', 'Включить комментарии');
		$r &= (boolean)$this->config->setVariable('allow_tags', '0', self::nameModule, NULL, 'C', 'Включить тэги');
		$r &= (boolean)$this->config->setVariable('countAdvEvents', '10', self::nameModule, NULL, 'N', 'Кол-во статей в дополнительном списке внизу статьи');
		$arr = array(
			array('name'=>'id','title'=>'ID статьи','val'=>1),
			array('name'=>'date','title'=>'Дате публикации статьи','val'=>0),
			array('name'=>'alias','title'=>'Псевдониму статьи (транслитерация заголовка)','val'=>0)
		);
		$r &= (boolean)$this->config->setVariable('identifyByURL', $arr, self::nameModule, NULL, 'S', 'URL статьи строиться по', NULL, 'Будьте внимательны при смене этого поля, так как оно влияет на SEO');

		$r &= (boolean)$this->config->setVariable('frontend', NULL, self::nameModule, NULL, 'FIELDSET', 'Постраничная навигация на сайте');
		$r &= (boolean)$this->config->setVariable('active', 1, self::nameModule, 'frontend', 'C', 'Включить');
		$r &= (boolean)$this->config->setVariable('rowsByPage', '15', self::nameModule, 'frontend', 'N', 'Кол-во записей на странице', '50px');
		$r &= (boolean)$this->config->setVariable('advLinks', '2', self::nameModule, 'frontend', 'N', 'Кол-во ссылок на соседние страницы', '50px');

		$r &= (boolean)$this->config->setVariable('backend', NULL, self::nameModule, NULL, 'FIELDSET', 'Настройки для админки');
		$r &= (boolean)$this->config->setVariable('rowsByPage', '15', self::nameModule, 'backend', 'N', 'Кол-во статей на странице', '50px');
		$r &= (boolean)$this->config->setVariable('advLinks', '2', self::nameModule, 'backend', 'N', 'Общее кол-во выводимых ссылок', '50px');

		$r &= (boolean)$this->config->setVariable('multilang', NULL, self::nameModule, NULL, 'FIELDSET', 'Мультиязычность');
		$r &= (boolean)$this->config->setVariable('active', '0', self::nameModule, 'multilang', 'C', 'Включить');

		$r ? $this->msg->setOk('Переменные установлены') : $this->msg->setError('Ошибка при установки переменных');

		$r = retr_sql("SHOW TABLE STATUS FROM ".$dbase." LIKE '".$this->tables['events']->tablename."'");
		if (!$r) {
			$r = run_sql('CREATE TABLE '.$this->tables['events']->table." (
				  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
				  `published` ENUM('1','0') NOT NULL DEFAULT '1',
				  `alias` VARCHAR(255) DEFAULT NULL,
				  `title` VARCHAR(255) NOT NULL,
				  `description` TEXT NOT NULL,
				  `content` TEXT NOT NULL,
				  `type` ENUM('news','announcement') NOT NULL DEFAULT 'news',
				  `author` varchar(128) NOT NULL,
				  `date_begin` int(10) unsigned DEFAULT NULL,
				  `date_end` int(10) DEFAULT NULL,
				  `time_begin` time DEFAULT NULL,
				  `time_end` time DEFAULT NULL,
  				  `date_published` INT(10) UNSIGNED,
				  `date_update` INT(10) UNSIGNED,
				  `tags` VARCHAR(255) NOT NULL,
				PRIMARY KEY (`id`)
				) ENGINE=MYISAM DEFAULT CHARSET=utf8");
			if ($r)
				$this->msg->setOk('Таблица "'.$this->tables['events']->tablename.'" создана');
		} else
			$this->msg->setWarning('Таблица "'.$this->tables['events']->tablename.'" уже создана');

		if (!$this->msg->keep) {
//			$this->msg->setReload();
			return 1;
		}
		return 0;
	}

	function install_multilang() { //----------------------------------------------
		global $dbase;

		$r = retr_sql('SHOW TABLE STATUS FROM '.$dbase." LIKE '".$this->tables['events']->tablename_i18n."'");
		if (!$r) {
			$fields = '';
			foreach ($this->langs as $lang=>$item) {
				if ($this->lang_default !== $lang)
					$fields .= '`alias_'.$lang.'` VARCHAR(255) DEFAULT NULL,
								`title_'.$lang.'` VARCHAR(255) NOT NULL,
								`description_'.$lang.'` TEXT NOT NULL,
								`content_'.$lang.'` TEXT NOT NULL,';
			}

			$r = run_sql('CREATE TABLE `'.$this->tables['events']->tablename_i18n."` (
				  `event_id` INT(10) UNSIGNED NOT NULL,$fields
				PRIMARY KEY (`event_id`)
				) ENGINE=MYISAM DEFAULT CHARSET=utf8");
			if ($r)
				$this->msg->setOk('Таблица "'.$this->tables['events']->tablename_i18n.'" создана');
		} else
			$this->msg->setWarning('Таблица "'.$this->tables['events']->tablename_i18n.'" уже создана');

		if (!$this->msg->keep) {
			return 1;
		}
		return 0;
	}
}
?>
