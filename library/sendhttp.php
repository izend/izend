<?php

/**
 *
 * @copyright  2010-2025 izend.org
 * @version    17
 * @link       http://www.izend.org
 */

function http_build_args($args) {
	$args_string = '';

	foreach ($args as $name => $value) {
		$args_string .= ($args_string ? '&' : '') . urlencode($name) . '=' . urlencode($value);
	}

	return $args_string;
}

function http_parse_url($url) {
	$purl = @parse_url($url);
	if ($purl === false) {
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
	$portnum = isset($purl['port']) ? $purl['port'] : ($scheme == 'https' ? 443 : 80);
	$path = isset($purl['path']) ? $purl['path'] : '/';
	$query = isset($purl['query']) ? $purl['query'] : false;

	return array($proto, $scheme, $host, $portnum, $path, $query);
}

function sendget($url, $args=false, $options=false, $header=false) {
	return sendhttp('GET', $url, $args, false, false, $options, $header);
}

function senddelete($url, $args=false, $options=false, $header=false) {
	return sendhttp('DELETE', $url, $args, false, false, $options, $header);
}

function sendhead($url, $args=false, $options=false, $header=false) {
	return sendhttp('HEAD', $url, $args, false, false, $options, $header);
}

function sendpost($url, $args=false, $files=false, $base64=false, $options=false, $header=false) {
	return sendhttp('POST', $url, $args, $files, $base64, $options, $header);
}

function sendput($url, $args=false, $files=false, $base64=false, $options=false, $header=false) {
	return sendhttp('PUT', $url, $args, $files, $base64, $options, $header);
}

function sendhttp($method, $url, $args, $files=false, $base64=false, $options=false, $header=false) {
	$r = http_parse_url($url);

	if (!$r) {
		return false;
	}

	list($proto, $scheme, $host, $portnum, $path, $query)=$r;

	if ($query) {
		$path .= '?' . $query;
	}

	$hostaddr=($scheme == 'http' && $portnum == 80) ? $host : $host . ':' . $portnum;

	$user_agent='iZend';

	$content_string=$content_type=false;

	$crlf="\r\n";

	switch ($method) {
		case 'POST':
		case 'PUT':
			if ($files) {
				$boundary = md5(microtime());
				$content_type = 'multipart/form-data; boundary='.$boundary;

				$content_string = '';

				if ($args && is_array($args)) {
					foreach ($args as $k => $v) {
						$content_string .= '--' . $boundary . $crlf;
						$content_string .= 'Content-Disposition: form-data; name="' . $k . '"' . $crlf . $crlf . $v . $crlf;
					}
				}
				if (is_array($files)) {
					foreach ($files as $k => $v ) {
						$data=false;
						if (isset($v['tmp_name'])) {
							$data = file_get_contents($v['tmp_name']);
						}
						else if (isset($v['data'])) {
							$data = $v['data'];
						}
						if (!$data) {
							break;
						}
						$content_string .= '--' . $boundary . $crlf;
						$content_string .= 'Content-Disposition: form-data; name="' . $k . '"; filename="' . $v['name'] . '"' . $crlf;
						$content_string .= 'Content-Type: ' . $v['type'] . $crlf;
						if ($base64) {
							$content_string .= 'Content-Transfer-Encoding: base64' . $crlf . $crlf;
							$content_string .= chunk_split(base64_encode($data)) . $crlf;
						}
						else {
							$content_string .= 'Content-Transfer-Encoding: binary' . $crlf . $crlf;
							$content_string .= $data . $crlf;
						}
					}
				}
				$content_string .= '--' . $boundary . '--' . $crlf;
			}
			else {
				if ($args) {
					if (is_array($args)) {
						$content_type = 'application/x-www-form-urlencoded';
						$content_string = http_build_args($args);
					}
					else {
						$content_string = $args;
					}
				}
			}

			$header_string="$method $path HTTP/1.1{$crlf}Host: $hostaddr{$crlf}User-Agent: $user_agent{$crlf}";

			if ($content_string) {
				$content_length = strlen($content_string);
				$header_string .= "Content-Length: $content_length{$crlf}";
				if ($content_type) {
					$header_string .= "Content-Type: $content_type{$crlf}";
				}
			}
			break;

		case 'GET':
		case 'HEAD':
		case 'DELETE':
			if ($args && is_array($args)) {
				$path .= ($query ? '&' : '?') . http_build_args($args);
			}
			$header_string="$method $path HTTP/1.1{$crlf}Host: $hostaddr{$crlf}User-Agent: $user_agent{$crlf}";
			break;

		default:
			return false;
	}

	if ($header && is_array($header)) {
		foreach ($header as $name => $value) {
			if (is_array($value)) {
				$value = implode('; ', $value);
			}
			$header_string .= "{$name}: {$value}{$crlf}";
		}
	}

	$header_string .= "Connection: close{$crlf}{$crlf}";

	return sendhttpraw($proto, $host, $portnum, $header_string, $content_string, $options);
}

function sendhttpraw($proto, $host, $portnum, $header_string, $content_string=false, $options=false) {
	$url=$proto . '://' . $host . ':' . $portnum;

	$socket = $options ? @stream_socket_client($url, $errstr, $errno, 60, STREAM_CLIENT_CONNECT, stream_context_create($options)) : @stream_socket_client($url);

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

	if (!$response) {
		return false;
	}

	$crlf="\r\n";

	list($response_headers, $response_body) = explode($crlf . $crlf, $response, 2);

	$response_header_lines = explode($crlf, $response_headers);
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
		$response_header_array[ucwords($header, '-')] = $value;
	}

	if (isset($response_header_array['Transfer-Encoding'])) {
		switch ($response_header_array['Transfer-Encoding']) {
			case 'chunked':
				$chunks = explode($crlf, $response_body);
				foreach ($chunks as $i => $s) {
					if ($i % 2 == 0) {
						unset($chunks[$i]);
					}
				}
				$response_body = implode('', $chunks);
				break;
			default:
				break;
		}
	}

	return array($response_code, $response_header_array, $response_body);
}
