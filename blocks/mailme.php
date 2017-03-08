<?php

/**
 *
 * @copyright  2010-2017 izend.org
 * @version    9
 * @link       http://www.izend.org
 */

require_once 'readarg.php';
require_once 'strflat.php';
require_once 'validatemail.php';
require_once 'ismailinjected.php';
require_once 'isfilenameallowed.php';
require_once 'validatefilename.php';
require_once 'tokenid.php';

function mailme($lang, $to=false, $with_appointment=false, $with_attachment=false, $with_captcha=true, $with_home=true) {
	$maxfilesize=2000000;

	$action='init';
	if (isset($_POST['mailme_send'])) {
		$action='send';
	}

	$mail=$subject=$message=$date=$hour=$minute=$code=$token=false;

	$file=$filename=$filesize=$fileok=false;

	if (isset($_SESSION['user']['mail'])) {
		$mail=$_SESSION['user']['mail'];
	}

	switch($action) {
		case 'send':
			if (isset($_POST['mailme_mail'])) {
				$mail=strtolower(strflat(readarg($_POST['mailme_mail'])));
			}
			if (isset($_POST['mailme_subject'])) {
				$subject=readarg($_POST['mailme_subject']);
			}
			if (isset($_POST['mailme_message'])) {
				$message=readarg($_POST['mailme_message']);
			}
			if ($with_appointment) {
				if (isset($_POST['mailme_date'])) {
					$date=readarg($_POST['mailme_date']);
				}
				if (isset($_POST['mailme_hour'])) {
					$hour=readarg($_POST['mailme_hour']);
				}
				if (isset($_POST['mailme_minute'])) {
					$minute=readarg($_POST['mailme_minute']);
				}
			}
			if ($with_attachment) {
				if (isset($_FILES['mailme_file'])) {
					if (isset($_FILES['mailme_file']['tmp_name'])) {
						$file=$_FILES['mailme_file']['tmp_name'];
					}
					if (isset($_FILES['mailme_file']['name'])) {
						$filename=$_FILES['mailme_file']['name'];
					}
					if (isset($_FILES['upload_file']['size'])) {
						$filesize=$_FILES['upload_file']['size'];
					}
					if (isset($_FILES['mailme_file']['error'])) {
						$fileok=$_FILES['mailme_file']['error'];
					}
				}
			}
			if (isset($_POST['mailme_code'])) {
				$code=readarg($_POST['mailme_code']);
			}
			if (isset($_POST['mailme_token'])) {
				$token=readarg($_POST['mailme_token']);
			}
			break;
		default:
			break;
	}

	$missing_code=false;
	$bad_code=false;

	$bad_token=false;

	$missing_mail=false;
	$bad_mail=false;

	$missing_subject=false;
	$bad_subject=false;

	$missing_message=false;

	$bad_appointment=false;

	$bad_attachment=false;

	$email_sent=false;
	$home_page=false;

	$internal_error=false;

	switch($action) {
		case 'send':
			if (!isset($_SESSION['mailme_token']) or $token != $_SESSION['mailme_token']) {
				$bad_token=true;
			}

			if ($with_captcha) {
				if (!$code) {
					$missing_code=true;
					break;
				}
				$captcha=isset($_SESSION['captcha']['mailme']) ? $_SESSION['captcha']['mailme'] : false;
				if (!$captcha or $captcha != strtoupper($code)) {
					$bad_code=true;
					break;
				}
			}

			if (!$mail) {
				$missing_mail=true;
			}
			else if (!validate_mail($mail)) {
				$bad_mail=true;
			}
			if (!$subject) {
				$missing_subject=true;
			}
			else if (is_mail_injected($subject)) {
				$bad_subject=true;
			}
			if (!$message) {
				$missing_message=true;
			}

			if ($with_appointment) {
				if ($date) {
					if (!preg_match('#^([0-9]{4})([/-])([0-9]{2})\2([0-9]{2})$#', $date, $d)) {
						$bad_appointment=true;
					}
					else if (!checkdate($d[3], $d[4], $d[1])) {
						$bad_appointment=true;
					}
					else if (mktime(0, 0, 0, $d[3], $d[4], $d[1]) <= mktime(0, 0, 0, date("m"), date("d"), date("y"))) {
						$bad_appointment=true;
					}
				}
				if (is_numeric($hour) and is_numeric($minute)) {
					if ($hour < 0 or $hour > 23 or $minute < 0 or $minute > 59) {
						$bad_appointment=true;
					}
				}
			}

			if ($with_attachment) {
				switch ($fileok) {
					case UPLOAD_ERR_NO_FILE:
						break;
					case UPLOAD_ERR_OK:
						if (!is_uploaded_file($file)) {
							$bad_attachment=true;
						}
						else if (!validate_filename($filename) or !is_filename_allowed($filename)) {
							$bad_attachment=true;
						}
						else if ($maxfilesize and $filesize > $maxfilesize) {
							$bad_attachment=true;
						}
						break;
					default:
						$bad_attachment=true;
						break;
				}
			}

			break;
		default:
			break;
	}

	switch($action) {
		case 'send':
			if ($bad_token or $missing_code or $bad_code or $missing_mail or $bad_mail or $missing_subject or $bad_subject or $missing_message or $bad_appointment or $bad_attachment) {
				break;
			}

			if ($date) {
				$f=translate('email:appointment', $lang);
				$s=sprintf($f ? $f : "%s %02d:%02d", $date, $hour, $minute);
				$message .= "\n\n$s";
			}

			if ($file) {
				require_once 'emailmefile.php';

				$r = emailmefile($subject, $message, $file, $filename, $mail, $to);
			}
			else {
				require_once 'emailme.php';

				$r = emailme($subject, $message, $mail, $to);
			}

			if (!$r) {
				$internal_error=true;
				break;
			}

			$subject=$message=$date=$hour=$minute=false;

			if ($with_home) {
				global $home_action;

				$home_page=url($home_action, $lang);
			}

			$email_sent=true;

			break;
		default:
			break;
	}

	$_SESSION['mailme_token'] = $token = token_id();

	$errors = compact('missing_code', 'bad_code', 'missing_mail', 'bad_mail', 'missing_subject', 'bad_subject', 'missing_message', 'bad_appointment', 'bad_attachment', 'internal_error');
	$infos = compact('email_sent', 'home_page');

	$output = view('mailme', $lang, compact('token', 'with_captcha', 'with_appointment', 'with_attachment', 'maxfilesize', 'mail', 'subject', 'message', 'date', 'hour', 'minute', 'errors', 'infos'));

	return $output;
}

