<?php
function GetAppId()
{
	return ['app_id' => 100067, 'user_id' => "4186604490"];
}

function GetInstance($reqClassGroup = null, $returnNotInstance = false, $reqClass = null)
{
    if (empty($reqClassGroup)) {
        error('ise');
    }

    $configPath = __DIR__.DIRECTORY_SEPARATOR.'CONFIG'.DIRECTORY_SEPARATOR.'config.php';
    if (!file_exists($configPath)) {
        error('ise');
    }

    $config = require $configPath;
    $config = (is_array($config)) ? $config : json_decode($config, true);

    $instances = $config;
    if (!in_array($reqClassGroup, array_keys($instances))) {
        error('ise');
    }

    if ($returnNotInstance == true) {
        return $returnNotInstance = $instances[$reqClassGroup];
    }

    $populate = [];
    foreach ($instances[$reqClassGroup] as $class) {
        if (class_exists("$class")) {
            $instanceClass = $class::instance();
            if ($instanceClass instanceof $class) {
                $populate[$class] = $instanceClass;
            } else {
                error('ise');
            }
        } else {
            error('ise');
        }
    }

    if (empty($populate)) {
        error('ise');
    }

    if (!empty($reqClass)) {
        return $populate[$reqClass];
    } else {
        return $populate;
    }
}

function HeadersToArray($str)
{
	$headers = [];
    $headersTmpArray = explode( "\r\n" , $str );
    for ( $i = 0 ; $i < count( $headersTmpArray ) ; ++$i )
    {
        if ( strlen( $headersTmpArray[$i] ) > 0 )
        {
            if ( strpos( $headersTmpArray[$i] , ":" ) )
            {
                $headerName = substr( $headersTmpArray[$i] , 0 , strpos( $headersTmpArray[$i] , ":" ) );
                $headerValue = substr( $headersTmpArray[$i] , strpos( $headersTmpArray[$i] , ":" )+1 );
                $headers[$headerName] = $headerValue;
            }
        }
    }
    return $headers;
}

function PathList($path)
{
	$list = [
		'get_user_info' => 'https://kiosgamer.co.id/api/auth/get_user_info',
		'player_id_login' => 'https://kiosgamer.co.id/api/auth/player_id_login',
		'get_voucher' => 'https://kiosgamer.co.id/api/shop/apps/channels?app_id=100067&packed_role_id=0&region=CO.ID&language=id',
        'get_csrf' => 'https://kiosgamer.co.id/api/preflight',
        'payment' => 'https://kiosgamer.co.id/api/shop/pay/init?language=id&region=CO.ID',
        'get_payment_pool' => 'https://kiosgamer.co.id/api/shop/pay/poll?language=id&region=CO.ID',
        'payment_gw' => 'https://airtime.codapayments.com/airtime/begin?type=3&txn_id=6340322505931095294'
	];

	return $list[$path];
}

function GetCookie($cookie)
{
	$cookie = $cookie['header']['Set-Cookie'];
	$cookie = explode(';', $cookie);
	$cookie = explode('=', $cookie[0]);
	$cookie = $cookie[1];
	$_SESSION['cookie'] = $cookie;
	$_SESSION['cookie_time'] = time() + ini_get('session.gc_maxlifetime');
	LogData("Set Cookie: ".$_SESSION['cookie']);
}

function GetCsrf($csrf)
{
    $csrf = $csrf['header']['Set-Cookie'];
    $csrf = explode(';', $csrf);
	$csrf = explode('=', $csrf[0]);
	$csrf = $csrf[1];
    $_SESSION['csrf'] = $csrf;
	LogData("Set csrf: ".$_SESSION['csrf']);
}

function error($code, $param = array())
{
    if (!is_array($param)) {
        $param = array($param);
    }

    $countLatency = microtime(true);
    $return['isSuccess'] = false;

    $debugBackTrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3);
    $getFunctionCalled = [];
    $unsetReturnBackTrace = ['file','type','object'];
    foreach ($debugBackTrace as $list) {
        foreach ($unsetReturnBackTrace as $unset) {
            unset($list[$unset]);
        }
        if (empty($list['line'])) {
            continue;
        }
        $getFunctionCalled[] = $list;
    }

    $errorList = GetInstance('error', true);
    if (!in_array($code, array_keys($errorList))) {
        $return['priority'] = $errorList['errornotdefine']['priority'];
        $return['error'] = sprintf($errorList['errornotdefine']['errorMessage'], $code);
        $return['trace'] = $getFunctionCalled;
        $return['latency'] = (microtime(true) - $countLatency);
        die(json_encode([
            'isSuccess' => false,
            'error' => $errorList['bad']['errorMessage'],
            'latency' => (microtime(true) - $countLatency)
        ]));
    }

    $return['errorCode'] = $errorList[$code]['errorCode'];

    if ($code == 'param') {
        $return['error'] = vsprintf($errorList['param']['errorMessage'], $param);
    } else {
        $return['error'] = $errorList[$code]['errorMessage'];
    }

    $return['trace'] = $getFunctionCalled;
    $return['latency'] = (microtime(true) - $countLatency);
    $return['trace'] = $debugBackTrace;

    unset($return['trace']);

    die(json_encode($return));
}

function LogData($msg)
{
    static $id, $path;

    if (!$id) {
        $id = ip2long($_SERVER['REMOTE_ADDR']);
        $id = str_pad(dechex($id), 8, '0', STR_PAD_LEFT);

        if (!isset($GLOBALS['ClientID'])) {
            $id = "0000.$id.";
        } else {
            $id = str_pad(dechex($GLOBALS['ClientID']), 4, '0', STR_PAD_LEFT).".$id.";
        }

        if (!isset($_REQUEST['sessid'])) {
            $length = empty(ini_get('session.hash_bits_per_character')) ? 6 : ini_get('session.hash_bits_per_character');
            $id .= substr(md5(time()), 0, ceil(128/$length));
        } else {
            $id .= $_REQUEST['sessid'];
        }

        $id .= date('.His');
    }

    $path = dirname(__FILE__).'/LOG/'.date('Y.m.d');
    if (!file_exists($path)) {
        mkdir($path, 0775);
    }

    $path .= '/'.date('Y.m.d').".log";
    $time = explode(' ', microtime());
    clearstatcache(true, $path);
    $f = fopen($path, 'a');

    if (!is_string($msg)) {
        $msg = json_encode($msg);
    }

    $msg = str_replace(array("\r\n","\r","\n"), array("\n","\n","\r\n\t"), $msg);
    fwrite($f, date('H:i:s').substr($time[0], 1)." $id > $msg\r\n");
    fclose($f);
}

function TransformVoucherList($typeGet = 'channel', $channel_id = null)
{
	$voucher = @file_get_contents(dirname(__FILE__).'/POOL/'.'voucherlist.json');
	$voucher = json_decode($voucher, true);

	$voucerList = [];
	$channelList = [];
	foreach ($voucher['channels'] as $channelKey => $channel) {
		if (empty($channel['items'])) continue;

		$voucerList[$channel['channel']]['channel'] = $channel['name'];
		$voucerList[$channel['channel']]['channel_id'] = $channel['channel'];
		if (!empty($channel['message'])) {
			$voucerList[$channel['channel']]['status'] = 'close';
		} else {
			$voucerList[$channel['channel']]['status'] = 'open';
		}

		$channelName = strtolower($channel['name']);
		$channelList[$channelName]['channel_id'] = $channel['channel'];

		foreach ($channel['items'] as $itemKey => $item) {
			$voucerList[$channel['channel']]['voucher'][$item['item_id']]['vid'] = $item['id'];
			$voucerList[$channel['channel']]['voucher'][$item['item_id']]['gpa'] = $item['garena_point_amount'];
			$voucerList[$channel['channel']]['voucher'][$item['item_id']]['apa'] = $item['app_point_amount'];
			$voucerList[$channel['channel']]['voucher'][$item['item_id']]['price'] = $item['currency_amount'];
		}
	}

	if (isset($channelList) && $typeGet == 'channel' || is_null($channel_id)) {
		return $channelList;
	} else {
		return $voucerList[$channel_id];
	}
}

function reformatPhoneNumber($number)
{
    $number = trim($number);
    $number = preg_replace('/^\+*62/', '0', $number);
    $number = preg_replace('/[^\d]/', '', $number);
    if ($number[0] != '0') {
        $number = "0$number";
    }
    return $number;
}
