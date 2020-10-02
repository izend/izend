<?php

/**
 *
 * @copyright  2016-2020 izend.org
 * @version    3
 * @link       http://www.izend.org
 */

require_once 'readarg.php';
require_once 'tokenid.php';

function analytics($lang) {
	global $base_url;

	$with_period=false;

	$available_periods=$with_period ? array('1weekago', '2weeksago', '1monthago', '3monthsago', '6monthsago', '1yearago') : false;

	$action='init';
	if (isset($_POST['analytics_draw'])) {
		$action='draw';
	}

	$url=false;
	$period=false;
	$start_date=$end_date=false;
	$trendline=false;

	$token=false;

	switch($action) {
		case 'init':
			if ($with_period) {
				$period='1weekago';
			}
			else {
				$start_date=date('Y-m-d', strtotime('today -1 month'));
				$end_date=date('Y-m-d', strtotime('today'));
			}
			break;

		case 'draw':
			if (isset($_POST['analytics_url'])) {
				$url=readarg($_POST['analytics_url']);
			}
			if ($with_period) {
				if (isset($_POST['analytics_period'])) {
					$period=readarg($_POST['analytics_period']);
				}
			}
			else {
				if (isset($_POST['analytics_startdate'])) {
					$start_date=readarg($_POST['analytics_startdate']);
				}
				if (isset($_POST['analytics_enddate'])) {
					$end_date=readarg($_POST['analytics_enddate']);
				}
			}
			if (isset($_POST['analytics_trendline'])) {
				$trendline=readarg($_POST['analytics_trendline']) == 'on' ? true : false;
			}
			if (isset($_POST['analytics_token'])) {
				$token=readarg($_POST['analytics_token']);
			}
			break;
		default:
			break;
	}

	$bad_token=false;

	$missing_url=false;
	$bad_url=false;

	$missing_period=false;
	$bad_period=false;

	switch($action) {
		case 'draw':
			if (!isset($_SESSION['analytics_token']) or $token != $_SESSION['analytics_token']) {
				$bad_token=true;
			}

			if (!$url) {
				$missing_url=true;
			}
			else {
				$r=@parse_url($url);
				if (!$r) {
					$bad_url=true;
				}
				else {
					$url = $r['path'];

					if (!$url) {
						$bad_url=true;
					}
				}
			}

			if ($with_period) {
				if (!$period) {
					$missing_period=true;
				}
				else if (!in_array($period, $available_periods)) {
					$bad_period=true;
				}
			}
			else {
				if (! ($start_date and $end_date)) {
					$missing_period=true;
				}
				else {
					$today=strtotime('today');

					if (!preg_match('#^([0-9]{4})([/-])([0-9]{2})\2([0-9]{2})$#', $start_date, $d)) {
						$bad_period=true;
					}
					else if (!checkdate($d[3], $d[4], $d[1])) {
						$bad_period=true;
					}
					else {
						$date1=mktime(0, 0, 0, $d[3], $d[4], $d[1]);
						if ($date1 > $today) {
							$bad_period=true;
						}
					}

					if ($bad_period) {
						$start_date = false;
						break;
					}

					if (!preg_match('#^([0-9]{4})([/-])([0-9]{2})\2([0-9]{2})$#', $end_date, $d)) {
						$bad_period=true;
					}
					else if (!checkdate($d[3], $d[4], $d[1])) {
						$bad_period=true;
					}
					else {
						$date2=mktime(0, 0, 0, $d[3], $d[4], $d[1]);
						if ($date2 > $today) {
							$bad_period=true;
						}
					}

					if ($bad_period) {
						$end_date = false;
						break;
					}

					if ($date1 == $date2)  {
						$bad_period=true;
					}
					else if ($date1 > $date2) {
						$date = $start_date;
						$start_date = $end_date;
						$end_date = $date;
					}
				}
			}

			break;
		default:
			break;
	}

	$visits=$average=0;
	$data=false;

	$internal_error=false;

	switch($action) {
		case 'draw':
			if ($bad_token or $missing_url or $bad_url or $missing_period or $bad_period) {
				break;
			}

			require_once 'vendor/autoload.php';

			global $googleanalyticsaccount, $googleanalyticskeyfile;

			try {
				$tmpdir=ini_get('upload_tmp_dir') ?: ROOT_DIR . DIRECTORY_SEPARATOR . 'tmp';

				$client = new Google_Client();
				$client->setClassConfig('Google_Cache_File', 'directory', $tmpdir);

				$analytics = new Google_Service_Analytics($client);

				$key = file_get_contents($googleanalyticskeyfile);

				$cred = new Google_Auth_AssertionCredentials(
						$googleanalyticsaccount,
						array(Google_Service_Analytics::ANALYTICS_READONLY),
						$key
				);

				$client->setAssertionCredentials($cred);
				if ($client->getAuth()->isAccessTokenExpired()) {
					$client->getAuth()->refreshTokenWithAssertion($cred);
				}

				$accounts = $analytics->management_accounts->listManagementAccounts();

				if (count($accounts->getItems()) > 0) {
					$items = $accounts->getItems();
					$firstAccountId = $items[0]->getId();
					$properties = $analytics->management_webproperties->listManagementWebproperties($firstAccountId);

					if (count($properties->getItems()) > 0) {
						$items = $properties->getItems();
						$firstPropertyId = $items[0]->getId();
						$profiles = $analytics->management_profiles->listManagementProfiles($firstAccountId, $firstPropertyId);

						if (count($profiles->getItems()) > 0) {
							$items = $profiles->getItems();
					        $profile_id = $items[0]->getId();
						}
					}
				}
			}
			catch (Exception $e) {
				$internal_error=true;
				break;
			}

			if (!$profile_id) {
				$internal_error=true;
				break;
			}

			if ($with_period) {
				switch ($period) {
					case '1yearago':
						$date=strtotime('today -1 year');
						break;
					case '6monthsago':
						$date=strtotime('today -6 months');
						break;
					case '3monthsago':
						$date=strtotime('today -3 months');
						break;
					case '1monthago':
						$date=strtotime('today -1 month');
						break;
					case '2weeksago':
						$date=strtotime('today -2 weeks');
						break;
					case '1weekago':
					default:
						$date=strtotime('today -1 week');
						break;
				}
				$start_date=date('Y-m-d', $date);
				$end_date=date('Y-m-d');
			}

			try {
				$r = $analytics->data_ga->get(
					'ga:' . $profile_id,
					$start_date,
					$end_date,
					'ga:uniquePageviews',
					array(
						'filters'       => 'ga:pagePath==' . $url,
						'dimensions'    => 'ga:date',
						'sort'          => 'ga:date',
					));

				$totals=$r->getTotalsForAllResults();
				$visits=$totals['ga:uniquePageviews'];
				$average=false;
				if ($visits) {
					$data=$r->getRows();
					$ndays=count($data);
					$average=round($visits/$ndays, 1);

				}
			}
			catch (Exception $e) {
				$internal_error=true;
				break;
			}

			break;

		default:
			break;
	}

	$_SESSION['analytics_token'] = $token = token_id();

	$errors = compact('missing_url', 'bad_url', 'missing_period', 'bad_period', 'internal_error');

	$output = view('analytics', $lang, compact('token', 'with_period', 'url', 'period', 'start_date', 'end_date', 'visits', 'average', 'data', 'trendline', 'errors'));

	return $output;
}
