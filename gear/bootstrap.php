<?php
	ob_start();

	ob_implicit_flush(0);
	mb_internal_encoding('UTF-8');
	session_start();
	
	include(dir::classes('autoload.php'));
	
	autoload::register();
	autoload::addDir(dir::model());

	include(dir::functions('time.php'));
	include(dir::functions('html.php'));
	include(dir::functions('url.php'));
	include(dir::functions('validate.php'));

	new config();
	
	$DB = config::get('DB');
    
	$pdo = new PDO('mysql:host=' . $DB['host'] . ';dbname=' . $DB['database'] . ';charset=utf8', $DB['user'], $DB['password'], [
		PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
		PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING
	]);
	
	$db = new FluentPDO($pdo);
	
	function db() {
		global $db;
		return $db;
	}
	
	unset($DB, $pdo);
	
	lang::setDefault();
	lang::setLang(config::get('lang'));

	date_default_timezone_set(config::get('timezone', 'Europe/Berlin'));
	
	new userLogin();

	cache::setCache(config::get('cache'));

	$system = ob_get_contents();

	ob_end_clean();

	config::add('system', $system);
	
	ob_start();
	
	new application($env);

	$content = ob_get_contents();

	ob_end_clean();
	
	if(ajax::is()) {
		
		echo ajax::getReturn();
		
		exit();
			
	}
	
	config::add('content', $content);
	
?>