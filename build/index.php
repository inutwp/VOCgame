<?php
ini_set('date.timezone', 'Asia/Jakarta');
ini_set('display_errors', true);
ini_set('log_errors', true);
ini_set('html_errors', false);
ini_set('error_reporting', E_ALL);
ini_set('error_log', __FILE__.'.log');

ini_set('session.use_cookies', false);
ini_set('session.use_only_cookies', true);
ini_set('session.save_path', 'SESS');
ini_set('session.hash_bits_per_character', 5);
ini_set('session.gc_maxlifetime', 1800);

ini_set('always_populate_raw_post_data', '-1');
header('Content-type: application/json');

require_once('helper.php');

spl_autoload_extensions(".php");
spl_autoload_register();

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$requestMethod = $_SERVER["REQUEST_METHOD"];
$uriParts = explode('/', $uri);

if ($uri !== '/' && file_exists(__DIR__.DIRECTORY_SEPARATOR.$uri)) {
	error('bad');
}

$routes = [
    '/' => [
        'method' => 'GET',
        'expression' => '/^\/?$/',
        'controller_method' => 'Home'
    ],
	'test' => [
        'method' => 'GET',
        'expression' => '/^\/(test)?$/',
        'controller_method' => 'Test'
    ],
	'api' => [
        'method' => 'GET',
        'expression' => '/^\/(api)?$/',
        'controller_method' => 'Index'
    ],
	'api.login' => [
        'method' => 'GET',
        'expression' => '/^\/(api)[\/]+(login)?$/',
        'controller_method' => 'CreateSession'
    ],
	'api.get.voucher' => [
        'method' => 'POST',
        'expression' => '/^\/(api)[\/]+(voucher)?$/',
        'controller_method' => 'GetVoucher'
    ],
];

$routeFound = null;
foreach ($routes as $route) {
    if ($route['method'] == $requestMethod && preg_match($route['expression'], $uri)) {
        $routeFound = $route;
        break;
    }
}

if (!$routeFound) {
    header("HTTP/1.1 404 Not Found");
    error('bad');
}

$response = call_user_func_array($routeFound['controller_method'],[]);

if (!is_string($response)) {
	header("HTTP/1.1 200 Ok");
	die(json_encode($response));
} else {
	die($response);
}

register_shutdown_function('ErrorHandler');
set_error_handler('ErrorHandler', E_ALL);

function ErrorHandler ($errno = null, $errstr = null, $errfile = null, $errline = null)
{
	static $last, $error = [];
	if ($errno & 1792) $last = $errno;
	$timeout = ini_get('max_execution_time');

	if ($errno !== null)
	{
		$time = explode(' ',microtime());
		$time[2] = date('H:i:s').substr($time[0],1);
		$error[] = "$time[2] At $errfile line $errline: $errstr";
		if ($timeout && $time[1] - $_SERVER['REQUEST_TIME'] > $timeout)
			{$error[] = "$time[2] At $errfile line $errline: script timed out";exit;}
		return false;
	}
	if (empty($error)) return;

	$header = array();
	$header[] = "Content-type: text/html";
	$header[] = "From: Kiosgame API Scraper <kiosgame@localhost>";
	$header = implode("\r\n",$header);

	$subject = "Kiosgame Scraper Error Notification";
	$message = "<pre>\r\n".implode("\r\n",$error)."\r\n</pre>";
	mail("logloganku@gmail.com",$subject,$message,$header);
}

function Home() {
	$loc = $_SERVER['HTTP_HOST'];
	header("location: http://$loc/api", true, 301);
	die();
}

function Index() {
	return ['isSuccess' => true, 'message' => 'API VOCGame v1.0'];
}

function Test() {
	header('Content-Type: text/html; charset=UTF-8');
	die(phpinfo());
}

function CreateSession()
{
	try {
		session_start();
		$login = DoLogin();
		if ($login) {
			$session = [];
			$session['isSuccess'] = true;
			$session['sessid'] = session_id();
			return $session;
		}
		error('auth');
	} catch (\Throwable $th) {
		throw new \Exception("Error Processing Request ".$th->getMessage(), 1);
		error('auth');
	}
}

function DoLogin()
{
	$header = [
		'Referer: https://kiosgamer.co.id/app',
		'Accept: application/json',
		'Content-Type: application/json',
	];
	$getSessionCookie = (new api())->SendRequest('GET', $header, 'get_user_info');
	GetCookie($getSessionCookie);

	$loginAction = (new api())->SendRequest('POST', $header, 'player_id_login', [
		'app_id' => GetAppId()['app_id'],
		'login_id' => GetAppId()['user_id']
	]);

	if (isset($loginAction['body']['error'])) {
		return false;
	}
	
	CollectVoucher();
	return true;
}

function CollectVoucher($forceUpdate = false)
{
	$recheckVoucherList = 10800;
	$timeFile = 0;
	$path = dirname(__FILE__).'/POOL/'.'voucherlist.json';
	if (!file_exists($path)) {
		$timeFile = 0;
	} else {
		$timeFile = filemtime($path);
	}

	if ((time() - $timeFile) > $recheckVoucherList || $forceUpdate) {
		$header = [
			'Referer: https://kiosgamer.co.id/app'
		];

		$getVoucher = (new api())->SendRequest('GET', $header, 'get_voucher');
		if (!isset($getVoucher['body']['error'])) {
			$fo = fopen($path, 'w+');
			flock($fo, LOCK_EX);
			fwrite($fo, json_encode($getVoucher['body']));
			flock($fo, LOCK_UN);
			fclose($fo);
		}
	}
}

function GetVoucher()
{
	extract($_REQUEST);

	if (empty($channel_id)) {
		return TransformVoucherList('channel');
	} else {
		return TransformVoucherList('voucher', $channel_id);
	}
}

function TopUp()
{
	extract($_REQUEST);

	if (empty($customer_phone)) {
		error('param', 'customer_phone');
	}

	if (empty($channel_id)) {
		error('param', 'channel_id');
	}

	if (empty($item_id)) {
		error('param', 'item_id');
	}

	$phoneNumber = reformatPhoneNumber($customer_phone);

	$header = [
		'Referer: https://kiosgamer.co.id/app/100067/buy/0'
	];

	$getCsrf = (new api())->SendRequest('POST', $header, 'get_csrf');
	GetCsrf($getCsrf);

	$payment = (new api())->SendRequest('POST', $header, 'payment', [
		'app_id' => GetAppId()['app_id'],
		'channel_data' => (object) [],
		'channel_id' => $channel_id,
		'item_id' => $item_id,
		'packed_role_id' => 0,
		'service' => 'pc'
	]);

	$item['struct'] = [
		'channel_name' => TransformVoucherList('voucher', $channel_id)['channel'],
		'channel_id' => $channel_id,
		'item_id' => $item_id,
		'voucher_price' => TransformVoucherList('voucher', $channel_id)['voucher'][$item_id]['price'],
		'display_id' => "",
		'display_id_url' => ""
	];

	if (!is_null($payment['body']['result']) && $payment['body']['result'] == 'success') {
		$_SESSION['display_id'] = $payment['body']['display_id'];
		$_SESSION['display_id_url'] = $payment['body']['init']['url'];
		$_SESSION['customer_phone_number'] = $phoneNumber;
		$item['struct']['display_id'] = $payment['body']['display_id'];
		$item['struct']['display_id_url'] = $payment['body']['init']['url'];
	}

	$_SESSION['struct'] = $item;

	return $item;
}

function CommitPayment()
{
	$payment = (new api())->SendRequest('POST', $header, 'get_payment_pool', ['display_id' => '383444081702102188']);
	$header = [
		':authority: airtime.codapayments.com',
		':method: GET',
		':path: /airtime/track-gaclient-info?TxnId=6340309099381095459',
		':scheme: https',
		'accept: text/plain, */*; q=0.01',
		'referer: https://airtime.codapayments.com/airtime/epc-checkout?type=3&txn_id=6340309099381095459',
		'x-requested-with: XMLHttpRequest',
		'cookie: JSESSIONID=561C7AFA638EE5B06533E56A5A0EEC9A; language=in_ID; land_url="https://kiosgamer.co.id/Codapay/pending/?TxnId=6340309099381095459&OrderId=11062842827530843388"; browser_type=desktop-web; amfID=; language=in_ID; _gcl_au=1.1.717356190.1634022008; _ga=GA1.2.1312979816.1634022009; _gid=GA1.2.1831172393.1634022009; browser_type=desktop-web; _gat_UA-38419864-3=1'
	];
	$payment = (new api())->SendRequest('GET', $header, 'payment_gw');
}

function CheckPayment()
{
	$header = ['Referer: https://kiosgamer.co.id/app/100067/buy/0'];
	$checkPayment = SendRequest('POST', $header, 'get_payment_pool', ['display_id' => $_SESSION['display_id']]);
	// https://kiosgamer.co.id/Codapay/pending/?TxnId=6345697039231975097&OrderId=16119305472622712473
	return $checkPayment;
}
