<?php
class Api
{
	public function SendRequest($method = '', $headers= [], $path = '', $content = [])
	{
		$url = PathList($path);
		$url = parse_url($url);
		if (!isset($url['scheme'])) {
			throw new \Exception("Error Processing ".__FUNCTION__, 1);
			return false;
		}

		$urlPath = $url['path'];

		if (isset($url['query'])) {
			$url['path'] .= "?$url[query]";
		}

		$host = ($url['scheme']=='https'?'https':'http')."://$url[host]$url[path]";

		$headerRequest = [
			"$method $urlPath HTTP/1.1",
			'Host: '.$url['host'],
			'Origin: '."$url[scheme]://$url[host]",
			'Connection: keep-alive',
			'Accept-Encoding: gzip, deflate, br',
			'Accept-Language: en-US,en;q=0.9',
			'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/94.0.4606.71 Safari/537.36',
			'sec-ch-ua: Chromium;v=94, Google Chrome;v=94, ;Not A Brand;v=99',
			'sec-ch-ua-mobile: ?0',
			'sec-ch-ua-platform: Windows',
			'sec-fetch-dest: empty',
			'sec-fetch-mode: cors',
			'sec-fetch-site: same-origin',
			'sec-fetch-user: ?1'
		];

		if(isset($headers)) {
			foreach ($headers as $header) {
				array_push($headerRequest, $header);
			}
		}

		if ($path != 'get_user_info' && $path != 'payment' && isset($_SESSION['cookie'])) {
			$cookie = 'Cookie: source=pc; b.vnpopup.1=1; _ga=GA1.3.2056344255.1633951252; _gid=GA1.3.563373177.1633951252; _gat=1; session_key='.$_SESSION['cookie'];
			array_push($headerRequest, $cookie);
		}

		if ($path == 'payment' && isset($_SESSION['csrf'])) {
			$cookie = 'Cookie: source=pc; b.vnpopup.1=1; _ga=GA1.3.2056344255.1633951252; _gid=GA1.3.563373177.1633951252; _gat=1; session_key='.$_SESSION['cookie']."; __csrf__=".$_SESSION['csrf'];
			$csrf = 'x-csrf-token: '.$_SESSION['csrf'];
			array_push($headerRequest, $cookie);
			array_push($headerRequest, $csrf);
		}

		if($method == 'POST') {
			array_push($headerRequest, "Content-length: ".strlen(json_encode($content)));
		}

		LogData("Request API $path : ".json_encode($content));
		LogData("Request Header $path : ".json_encode($headerRequest));

		try {
			$curl = curl_init($host);
			if ($curl) {
				curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);

				if ($method == 'POST') {
					curl_setopt($curl, CURLOPT_POST, 1);
					curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($content));
				}
				curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, mt_rand(10,30));
				curl_setopt($curl, CURLOPT_TIMEOUT, mt_rand(10,30));
				curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
				curl_setopt($curl, CURLOPT_AUTOREFERER, 1);
				curl_setopt($curl, CURLOPT_HTTPHEADER, $headerRequest);
				curl_setopt($curl, CURLOPT_HEADER, 1);

				$response = curl_exec($curl);

				if (curl_errno($curl)) {
					$response['isSuccess'] = false;
					$response['message'] = curl_error($curl);
					curl_close($curl);
					return $response;
				}

				$headerSize = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
				$headerStr = substr($response,0,$headerSize);
				$bodyStr = substr($response,$headerSize);

				$headerResponse = headersToArray($headerStr);

				curl_close($curl);

				$return = [];
				if (trim($headerResponse['Content-Encoding']) == 'gzip') {
					$return = [
					'body' => json_decode(gzinflate(substr($bodyStr, 10)), true),
					'header' => $headerResponse
					];
				} else {
					$return = [
					'body' => 'view',
					'header' => $headerResponse
					];
				}

				LogData("Response API $path : ".base64_encode(json_encode($return)));

				return $return;
			}
		} catch (\Exception $e) {
			throw new \Exception("Error Processing Request ".$e->getMessage(), 1);
			return false;
		}
	}
}
