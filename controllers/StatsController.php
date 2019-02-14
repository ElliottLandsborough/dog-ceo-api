<?php

namespace controllers;

use Locale;
use models\Cache;
use models\Statistic;
use stdClass;
use Symfony\Component\HttpFoundation\Response;

class StatsController
{
    private $stats;
    private $conn;
    private $statsObject;

    public function __construct()
    {
    }

    // recursively convert array to object
    private function array_to_object($array)
    {
        $obj = new stdClass();
        foreach ($array as $k => $v) {
            if (strlen($k)) {
                if (is_array($v)) {
                    $obj->{$k} = $this->array_to_object($v); //RECURSION
                } else {
                    $obj->{$k} = $v;
                }
            }
        }

        return $obj;
    }

    public function countryPercentages()
    {
        $raw = $this->stats->getCountryCount();

        $total = 0;

        // get the total
        foreach ($raw as $stat) {
            $total += $stat['count'];
        }

        $percentages = [];

        // calculate the %s
        foreach ($raw as $stat) {
            $country = 'n/a';
            if ($stat['country']) {
                $country = $stat['country'];
                $country = Locale::getDisplayRegion('-'.$stat['country'], 'en');
            }
            $percentages[$country] = round(intval($stat['count']) / $total * 100, 2);
        }

        arsort($percentages);

        return $percentages;
    }

    public function userAgentPercentages()
    {
        $raw = $this->stats->getUserAgentCount();

        $total = 0;

        // get the total
        foreach ($raw as $stat) {
            $total += $stat['count'];
        }

        $percentages = [];

        // calculate the %s
        foreach ($raw as $stat) {
            $userAgent = 'n/a';
            if ($stat['user-agent']) {
                $userAgent = $stat['user-agent'];
            }
            $percentages[$userAgent] = round(intval($stat['count']) / $total * 100, 2);
        }

        arsort($percentages);

        return $percentages;
    }

    public function referrerPercentages()
    {
        $raw = $this->stats->getReferrerCount();

        $total = 0;

        // get the total
        foreach ($raw as $stat) {
            $total += $stat['count'];
        }

        $percentages = [];

        // calculate the %s
        foreach ($raw as $stat) {
            $referrer = $stat['referrer'] ?: 'Direct access';
            if (strlen($referrer) && !strpos($referrer, 'localhost') && !strpos($referrer, '127.0.0.1')) {
                $percentages[$referrer] = round(intval($stat['count']) / $total * 100, 2);
            }
        }

        arsort($percentages);

        return $percentages;
    }

    // generate the stats
    private function generateStats()
    {
        $endpoints = $this->getUniqueEndpoints();

        // get the stuff from the db and organise it
        $stats = [];

        $totalHits = 0;

        foreach ($endpoints as $endpointResult) {
            // ignore stats page
            if ($endpointResult->route !== '/stats') {
                $daysAndHits = $this->getDaysAndHits($endpointResult->route);

                $hitCount = 0;

                foreach ($daysAndHits as $daysResult) {
                    // route specifics
                    $day = $daysResult->date;

                    $hits = $daysResult->hits;

                    // today won't have a full set of stats yet, so lets compensate
                    if (date('Y-m-d') == $day) {
                        $dateTimeStart = $day.' 00:00:00';
                        //$dateTimeFinish = $day . ' 23:59:59';
                        $dateTime = date('Y-m-d H:i:s');
                        $secondsInDay = 24 * 60 * 60;
                        $secondsSinceDayStart = strtotime($dateTime) - strtotime($dateTimeStart);
                        //$secondsuntilDayEnd = $secondsInDay - $secondsSinceDayStart;
                        $hitsSinceDayStart = $hits;
                        $ratio = $secondsInDay / $secondsSinceDayStart;
                        $hits = $ratio * $hits;
                    }

                    $hitCount += $hits;

                    //$stats['routes'][$endpointResult->route]['dayHits'][] = ['day' => $day, 'hits' => $hits];
                }

                // globals
                $totalHits += $hitCount;

                // route specifics
                $dayCount = count((array) $daysAndHits);
                $firstDay = $daysAndHits->{0}->date;
                $averagePerDay = $hitCount / $dayCount;
                $projectedYearly = $averagePerDay * 365;
                $projectedMonthly = $projectedYearly / 12;

                //$stats['routes'][$endpointResult->route]['dayCount'] = round($dayCount);
                //$stats['routes'][$endpointResult->route]['firstDay'] = $firstDay;
                $stats['routes'][$endpointResult->route]['hitCount'] = round($hitCount);
                $stats['routes'][$endpointResult->route]['averagePerDay'] = round($averagePerDay);
                $stats['routes'][$endpointResult->route]['projectedMonthly'] = round($projectedMonthly);
                $stats['routes'][$endpointResult->route]['projectedYearly'] = round($projectedYearly);
            }
        }

        // global stuff
        $dayCount = count((array) $this->getUniqueDays());
        $averagePerDay = $totalHits / $dayCount;
        $projectedYearly = $averagePerDay * 365;
        $projectedMonthly = $projectedYearly / 12;
        $projectedPerMinute = $averagePerDay / 24 / 60;
        $projectedPerSecond = $projectedPerMinute / 60;

        $stats['global']['totalHits'] = round($totalHits);
        //$stats['global']['firstDay'] = $this->getFirstDate();
        //$stats['global']['lastDay'] = $this->getLastDate();
        $stats['global']['dayCont'] = round($dayCount);
        $stats['global']['averagePerDay'] = round($averagePerDay);
        $stats['global']['projectedYearly'] = round($projectedYearly);
        $stats['global']['projectedMonthly'] = round($projectedMonthly);
        $stats['global']['projectedPerMinute'] = round($projectedPerMinute, 2);
        $stats['global']['projectedPerSecond'] = round($projectedPerSecond, 2);

        if ($_SERVER['SERVER_NAME'] !== 'dog.ceo') {
            $stats['global']['countryCount'] = $this->countryPercentages();
            $stats['global']['userAgentCount'] = $this->userAgentPercentages();
            $stats['global']['referrerCount'] = $this->referrerPercentages();
        }

        return $this->array_to_object($stats);
    }

    // lazy - manually generate the html for now
    public function statsPage()
    {
        // don't do this in construct!
        $stats = new Statistic();
        $this->stats = $stats;
        $this->cache = new Cache();
        if ($_SERVER['SERVER_NAME'] == 'dog.ceo') {
            // only get the stats every i minutes (keep this high on production e.g 6 hours)
            $this->statsObject = $this->cache->storeAndReturn('generateStats', 360, function () {
                return $this->generateStats();
            });
        } else {
            $this->statsObject = $this->generateStats();
        }

        $stats = $this->statsObject;

        $string = null;

        $string .= '<h1>Stats</h1>'.PHP_EOL;

        //$string .= '<h2>Global</h2>'.PHP_EOL;

        $object = $stats->global;

        $projectedPerSecond = $stats->global->projectedPerSecond;
        $projectedPerMinute = $stats->global->projectedPerMinute;

        $string .= "<p>Roughly <b>$projectedPerMinute requests per minute</b> ($projectedPerSecond per second).</p>".PHP_EOL;

        $string .= '<ul>'.PHP_EOL;
        $string .= '<li>'.PHP_EOL;
        $string .= 'Total Hits: '.$object->totalHits.PHP_EOL;
        $string .= '</li>'.PHP_EOL;
        $string .= '<li>'.PHP_EOL;
        $string .= 'Daily Average: '.$object->averagePerDay.PHP_EOL;
        $string .= '</li>'.PHP_EOL;
        $string .= '<li>'.PHP_EOL;
        $string .= 'Projected Monthly: '.$object->projectedMonthly.PHP_EOL;
        $string .= '</li>'.PHP_EOL;
        $string .= '<li>'.PHP_EOL;
        $string .= 'Projected Yearly: '.$object->projectedYearly.PHP_EOL;
        $string .= '</li>'.PHP_EOL;
        $string .= '</ul>'.PHP_EOL;

        if ($_SERVER['SERVER_NAME'] !== 'dog.ceo') {
            $string .= '<h2>Countries</h2>'.PHP_EOL;

            $string .= '<ul>'.PHP_EOL;
            foreach ($object->countryCount as $countryName => $percentage) {
                $string .= '<li>'.PHP_EOL;
                $string .= '<span style="display:inline-block;min-width:50px;">'.$percentage.'%</span> '.$countryName.PHP_EOL;
                $string .= '</li>'.PHP_EOL;
            }
            $string .= '</ul>'.PHP_EOL;

            $string .= '<h2>User Agents</h2>'.PHP_EOL;
            $string .= '<ul>'.PHP_EOL;
            foreach ($object->userAgentCount as $userAgent => $percentage) {
                $string .= '<li>'.PHP_EOL;
                $string .= '<span style="display:inline-block;min-width:50px;">'.$percentage.'%</span> '.$userAgent.PHP_EOL;
                $string .= '</li>'.PHP_EOL;
            }
            $string .= '</ul>'.PHP_EOL;

            $string .= '<h2>Referrers</h2>'.PHP_EOL;
            $string .= '<ul>'.PHP_EOL;
            foreach ($object->referrerCount as $referrer => $percentage) {
                $string .= '<li>'.PHP_EOL;
                $string .= '<span style="display:inline-block;min-width:50px;">'.$percentage.'%</span> <a href="'.$referrer.'">'.$referrer.'</a>'.PHP_EOL;
                $string .= '</li>'.PHP_EOL;
            }
            $string .= '</ul>'.PHP_EOL;
        }

        /*
        $string .= '<h2>Routes</h2>'.PHP_EOL;

        $object = $stats->routes;

        $string .= '<pre>'.PHP_EOL;
        $string .= print_r($object, true);
        $string .= '</pre>'.PHP_EOL;
        */

        $response = new Response(
            $string,
            Response::HTTP_OK,
            ['content-type' => 'text/html']
        );

        return $response;
    }

    // get a list of all endpoints in the db
    private function getUniqueEndpoints()
    {
        $sql = 'SELECT DISTINCT route FROM daily;';

        $result = $this->stats->query($sql);

        return $this->array_to_object($result->fetch_all(MYSQLI_ASSOC));
    }

    // get a list of all endpoints in the db
    private function getUniqueDays()
    {
        // get todays date
        //$dateString = date('Y-m-d');
        //$sql = "SELECT DISTINCT date FROM daily WHERE date !== '$dateString' ORDER BY date ASC;";
        //$result = $this->stats->query($sql);

        $sql = 'SELECT DISTINCT date FROM daily ORDER BY date ASC;';

        $result = $this->stats->query($sql);

        return $this->array_to_object($result->fetch_all(MYSQLI_ASSOC));
    }

    // get the first date in the array
    private function getFirstDate()
    {
        $days = $this->getUniqueDays()->{0}->date;

        return $days;
    }

    private function getLastDate()
    {
        $days = $this->getUniqueDays();
        $dayCount = count((array) $days);
        $day = $days->{$dayCount - 1}->date;

        return $days;
    }

    // get all the days and hits for a route
    private function getDaysAndHits($route)
    {
        $sql = "SELECT date, hits FROM daily WHERE route = '$route' GROUP BY date, hits ORDER BY date ASC;";

        $result = $this->stats->query($sql);

        return $this->array_to_object($result->fetch_all(MYSQLI_ASSOC));
    }
}
