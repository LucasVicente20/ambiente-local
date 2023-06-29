<?php

run();

function run()
{
	login();
	clearLog();
	printFilesContent();
}

function printFilesContent()
{
	$log = readFileContents('/tmp/logPortalCompras.log');
	
	$phpIni = '';
	$phpIniUbuntu = '';
	if (isset($_GET['ini']) && $_GET['ini'] == 't') {
		$phpIni = readFileContents('/etc/php.ini');
		$phpIniUbuntu = readFileContents('/etc/php5/apache2/php.ini');
	}
	
	$listPhpPlugins = '';
	$listPhpPluginsUbuntu = '';
	if (isset($_GET['plugins']) && $_GET['plugins'] == 't') {
		$listPhpPlugins = readFilesDir('/etc/php.d');
		$listPhpPluginsUbuntu = readFilesDir('/etc/php5/apache2/conf.d');
	}

	$filesContent = array(
		'LOG PORTAL COMPRAS' => $log,
		'PHP.INI' => $phpIni . $phpIniUbuntu,
		'PLUGINS INSTALADOS' => $listPhpPlugins . $listPhpPluginsUbuntu
	);

	echo '<pre>';

	foreach ($filesContent as $key => $value) {
		if (!empty($value)) {
			echo '<br/>';
			echo '================================================ ' . $key . ' ================================================';
			echo '<br/><br/>';
			echo $value;
		}
	}
}

function readFileContents($name)
{
	$content = '';
	
	if (file_exists($name)) {
		$content = file_get_contents($name);
	}
	
	return $content;
}

function readFilesDir($dir)
{
	$dh = opendir($dir);

	$files = array();
	if ($dh !== false) {
		while (false !== ($filename = readdir($dh))) {
			if ($filename != '.' && $filename != '..') {		
				$files[] = $filename;
			}
		}
	}
	
	return implode('<br/>', $files);
}

function clearLog()
{
	if (isset($_GET['clearLog']) && $_GET['clearLog'] == 't') {
		$logFile = "/tmp/logPortalCompras.log";
	
		if (file_exists($logFile)) {
			$handle = fopen($logFile, 'wb') or exit("Falha ao abrir arquivo de log de erros");
			fclose($handle);
		}
	}
}

function auth($user, $pass)
{
	$userLog = 'passarinho';
	$secretPass = 'cr1T0RigoP1t@';
	
	if ($user == $userLog && $pass == $secretPass) {
		return true;
	}
	
	return false;
}

function login()
{
	if (!isset($_SERVER['PHP_AUTH_USER'])) {
		header('WWW-Authenticate: Basic realm="Log Portal Compras"');
		header('HTTP/1.0 401 Unauthorized');
		exit;
	}
	
	if (!auth($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW'])) {
		exit;
	}
}