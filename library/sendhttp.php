<?php

/**
 *
 * @copyright  2010-2014 izend.org
 * @version    5
 * @link       http://www.izend.org
 */

function http_build_args($args) {
	$args_string = '';

	foreach ($args as $name => $value) {
		$args_string .= ($args_string ? '&' : '') . urlencode($name) . '=' . urlencode($value);
	}

	return $args_string;
}

function sendget($url, $args=false) {
	return sendhttp('GET', $url, $args);
}

function sendpost($url, $args=false, $files=false, $base64=false ) {
	return sendhttp('POST', $url, $args, $files, $base64);
}

function sendhttp($method, $url, $args, $files=false, $base64=false) {
	$purl = parse_url($url);
	if ($purl === false)
	{
		return false;
	}

	$scheme = isset($purl['scheme']) ? $purl['scheme'] : 'http';
	switch($scheme) {
		case 'https':
			$proto = 'ssl';
			break;
		case 'http':
			$proto = 'tcp';
			break;
		default:
			return false;
	}
	$host = isset($purl['host']) ? $purl['host'] : 'localhost';
	$portnum = isset($purl['portnum']) ? $purl['portnum'] : $scheme == 'https' ? 443 : 80;
	$path = isset($purl['path']) ? $purl['path'] : '/';

	$user_agent = 'iZend';

	$header_string = $content_string = '';

	switch ($method) {
		case 'POST':
			if ($files && is_array($files)) {
				$boundary = md5(microtime());
				$content_type = 'multipart/form-data; boundary='.$boundary;

				$content_string = '';

				if ($args && is_array($args)) {
					foreach ($args as $k => $v) {
						$content_string .= '--' . $boundary . "\r\n";
						$content_string .= 'Content-Disposition: form-data; name="' . $k . '"' . "\r\n\r\n" . $v . "\r\n";
					}
				}
				foreach ($files as $k => $v ) {
					if (isset($v['tmp_name'])) {
						$data = file_get_contents($v['tmp_name']);
						if (get_magic_quotes_runtime()) {
							$data = stripslashes($data);
						}
					}
					else if (isset($v['data'])) {
						$data = $v['data'];
					}
					if (!$data) {
						break;
					}
					$content_string .= '--' . $boundary . "\r\n";
					$content_string .= 'Content-Disposition: form-data; name="' . $k . '"; filename="' . $v['name'] . '"' . "\r\n";
					$content_string .= 'Content-Type: ' . $v['type'] . "\r\n";
					if ($base64) {
						$content_string .= 'Content-Transfer-Encoding: base64' . "\r\n\r\n";
						$content_string .= chunk_split(base64_encode($data)) . "\r\n";
					}
					else {
						$content_string .= 'Content-Transfer-Encoding: binary' . "\r\n\r\n";
						$content_string .= $data . "\r\n";
					}
				}
				$content_string .= '--' . $boundary . '--'. "\r\n";
			}
			else {
				$content_type = 'application/x-www-form-urlencoded';
				if ($args && is_array($args)) {
					$content_string = http_build_args($args);
				}
			}

			$content_length = strlen($content_string);
			$header_string="POST $path HTTP/1.0\r\nHost: $host\r\nUser-Agent: $user_agent\r\nContent-Type: $content_type\r\nContent-Length: $content_length\r\nConnection: close\r\n\r\n";
			break;

		case 'GET':
			if ($args && is_array($args)) {
				$path .= '?'.http_build_args($args);
			}
			$header_string="GET $path HTTP/1.0\r\nHost: $host\r\nUser-Agent: $user_agent\r\nConnection: close\r\n\r\n";
			break;

		default:
			return false;
	}

	$socket = @fsockopen($proto.'://'.$host, $portnum);
	if ($socket === false) {
		return false;
	}

	if (fwrite($socket, $header_string) === false) {
		return false;
	}
	if ($content_string) {
		$content_len = strlen($content_string);
		for ($written = 0; $written < $content_len; $written += $w) {
			$w = fwrite($socket, $written == 0 ? $content_string : substr($content_string, $written));
			if ($w === false) {
				return false;
			}
		}
    }

	$response = '';
	while (!feof($socket)) {
		$response .= fread($socket, 8192);
	}
	fclose($socket);

	list($response_headers, $response_body) = explode("\r\n\r\n", $response, 2);

	$response_header_lines = explode("\r\n", $response_headers);
	$http_response_line = array_shift($response_header_lines);

	if (preg_match('@^HTTP/[0-9]\.[0-9] ([0-9]{3})@', $http_response_line, $r)) {
		$response_code = $r[1];
	}
	else {
		$response_code = 0;
	}

	$response_header_array = array();
	foreach ($response_header_lines as $header_line) {
		list($header, $value) = explode(': ', $header_line, 2);
		$response_header_array[$header] = $value;
	}

	return array($response_code, $response_header_array, $response_body);
}

