<?php

/**
 *
 * @copyright  2016-2023 izend.org
 * @version    5
 * @link       http://www.izend.org
 */

require_once 'readarg.php';
require_once 'tokenid.php';

require_once 'vendor/autoload.php';

use Google\Analytics\Data\V1beta\BetaAnalyticsDataClient;
use Google\Analytics\Data\V1beta\DateRange;
use Google\Analytics\Data\V1beta\Dimension;
use Google\Analytics\Data\V1beta\Metric;
use Google\Analytics\Data\V1beta\Filter;
use Google\Analytics\Data\V1beta\Filter\StringFilter;
use Google\Analytics\Data\V1beta\FilterExpression;

function analytics($lang) {
	global $googlecredentials;
	global $googleanalyticspropertyid;

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

			try {
				$client = new BetaAnalyticsDataClient(['credentials' => $googlecredentials]);
			}
			catch (Exception $e) {
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
				$daterange = new DateRange([
					'start_date' => $start_date,
					'end_date' => $end_date,
					'name' => 'period'
				]);

				$filter = new Filter([
					'field_name' => 'pagePath',
					'string_filter' => new StringFilter([ 'match_type' => 1, 'value' => $url ])
				]);

				$response = $client->runReport([
					'property' => "properties/$googleanalyticspropertyid",
					'dateRanges' => [ $daterange ],
					'metrics' => [ new Metric(['name' => 'activeUsers']) ],
					'dimensions' => [ new Dimension(['name' => 'pagePath']), new Dimension(['name' => 'date']) ],
					'dimensionFilter' => new FilterExpression([ 'filter' => $filter ])
				]);

				if (count($response->getRows()) > 0) {
					$gdata=array();

					foreach ($response->getRows() as $r) {
						$d=$r->getDimensionValues()[1]->getValue();
						$n=$r->getMetricValues()[0]->getValue();
						$gdata[strtotime($d)]=(int)$n;
						$visits+=$n;
					}

					$ndays=date_diff(new DateTime($start_date), new DateTime($end_date))->days;

					if ($visits) {
						$average=round($visits/$ndays, 1);
					}

					$data=array();

					for ($date=strtotime($start_date), $d = 0; $d < $ndays; $d++, $date=strtotime('+ 1 day', $date)) {
						$data[$date] = 0;
					}

					$data = array_replace($data, $gdata);
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
