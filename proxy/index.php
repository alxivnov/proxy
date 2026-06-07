<?php
	function array_key_value($key, $array, $default = NULL) {
		return array_key_exists($key, $array)
			? $array[$key]
			: $default;
	}

	function stdout(...$lines) {
		$last = count($lines) - 1;

		foreach ($lines as $i => $line) {
			file_put_contents('php://stdout', print_r($line, true));

			if ($i < $last) {
				file_put_contents('php://stdout', ' ');
			}
		}

		file_put_contents('php://stdout', PHP_EOL);
	}

	function curl($url, $header, $body, $headerfunction, $log) {
		$ch = curl_init();

		curl_setopt_array($ch, array(
			CURLOPT_URL						=> $url,			// the url to fetch
			CURLOPT_HTTPHEADER				=> $header,			// HTTP header fields to set
			CURLOPT_HEADERFUNCTION			=> $headerfunction,	// the header data which must be written by the callback
			CURLOPT_HTTP_TRANSFER_DECODING	=> false,			// cURL does chunked transfer decoding by default unless this option is set to 0
		));

		if (!is_null($body)) {
			curl_setopt_array($ch, array(
				CURLOPT_POST				=> true,			// HTTP POST request
				CURLOPT_POSTFIELDS			=> $body,			// the full data to post in a HTTP POST operation
			));
		}

		if ($log) {
			curl_setopt_array($ch, array(
				CURLOPT_STDERR				=> $log,			// alternative location to output errors
				CURLOPT_VERBOSE				=> true				// output verbose information
			));
		}

		$result = curl_exec($ch);
		// $info = curl_getinfo($ch);
		curl_close($ch);

		return $result;
	}

	function headerFunction($curlHandle, $headerData) {
		$header = $headerData;
		if (stripos($headerData, 'Location: ') === 0) {
			// $header = 'X-'.$headerData;
			$header = 'Location: ?url='.substr($headerData, 10);
		}
		// if ($i === 0) {
		// 	// Disable HTTP/2 for Safari + php -S compatibility
		// 	// if (strpos($header, 'HTTP/2') === 0 || strpos($header, 'HTTP/3') === 0) {
		// 	// 	$header = 'HTTP/1.1'.substr($header, 6);
		// 	// }
		// 	header($header);
		// } elseif ($header) {
		// 	// Disable attachment for viewing in browser
		// 	// if (stripos($header, 'Content-Disposition: attachment') === false
		// 	// Disable chunking for resending
		// 	// || stripos($header, 'Transfer-Encoding: chunked') === false) {
		// 	// 	continue;
		// 	// }
		// 	header($header);
		// }

		// stdout($header);

		header($header);

		return strlen($headerData);
	}
	try {
		// stdout(curl_version());
		// stdout($_SERVER);

		$method = $_SERVER['REQUEST_METHOD'];
		$api = array_key_value('api', $_GET);
		$url = array_key_value('url', $_GET);
		$host = array_key_value('host', $_GET, parse_url($url, PHP_URL_HOST));
		$refer = array_key_value('refer', $_GET);
		$log = array_key_exists('log', $_GET);

		if ($url) {
			$header = array(
				// 'Acccept: */*'
			);
			foreach ($_SERVER as $name => $value) {
				if ($name === 'HTTP_HOST' && $host) {
					array_push($header, 'Host: '.$host);
				} else if ($name === 'HTTP_REFERER') {
					if ($refer) {
						array_push($header, 'Referer: '.$refer);
					}
				} else if (strpos($name, 'HTTP_') === 0) {
					array_push($header, str_replace('_', '-', ucwords(strtolower(substr($name, 5)), '_')).': '.$value);
				}
			}

			// stdout('req:', $method, $url, $header);

			if ($log !== false) {
				$log = fopen('php://temp/maxmemory:1048576', 'w+');
			}
			$res = curl(
				$url,
				$header,
				$method === 'POST'
					? file_get_contents('php://input')
					: NULL,
				'headerFunction',
				$log
			);

			// stdout('res:', $res);

			if ($res === false) {
				header('HTTP/1.1 400 Bad Request');
				header('Content-Type: text/plain');

				if ($log !== false) {
					rewind($log);
					while (!feof($log)) {
						$err = fread($log, 8192);
						print($err);

						// stdout('err:', $err);
					}
				}
			}

			if ($log !== false) {
				fclose($log);
			}
		} else {
			header('HTTP/1.1 204 No Content');
		}
	} catch (Exception $ex) {
		header('HTTP/1.1 500 Internal Server Error');
		header('Content-Type: text/plain');

		print($ex);
	}
?>
