<?php

/**
 *
 * @copyright  2016 izend.org
 * @version    1
 * @link       http://www.izend.org
 */

require_once 'readarg.php';
require_once 'tokenid.php';

function analytics($lang) {
	global $base_url;

	$available_periods=array('1weekago', '2weeksago', '1monthago', '3monthsago', '6monthsago', '1yearago');

	$action='init';
	if (isset($_POST['analytics_draw'])) {
		$action='draw';
	}

	$url=$period=false;
	$trendline=false;

	$token=false;

	switch($action) {
		case 'init':
			$period='1weekago';
			break;

		case 'draw':
			if (isset($_POST['analytics_url'])) {
				$url=readarg($_POST['analytics_url']);
			}
			if (isset($_POST['analytics_period'])) {
				$period=readarg($_POST['analytics_period']);
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

			if (!$period) {
				$missing_period=true;
			}
			else if (!in_array($period, $available_periods)) {
				$bad_period=true;
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
				$client = new Google_Client();
				$client->setClassConfig('Google_Cache_File', 'directory', ini_get('upload_tmp_dir'));

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

			try {
				$r = $analytics->data_ga->get(
					'ga:' . $profile_id,
					date('Y-m-d', $date),
					'today',
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

	$output = view('analytics', $lang, compact('token', 'url', 'period', 'visits', 'average', 'data', 'trendline', 'errors'));

	return $output;
}
