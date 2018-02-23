<?php

namespace controllers;

use \stdClass;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use models\Statistic;

class StatsController
{
    private $stats;
    private $conn;

    public function __construct()
    {
        $stats = new Statistic;
        $this->stats = $stats;
    }

    private function array_to_object($array)
    {
        $obj = new stdClass;
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

    // lazy - manually generate the html for now
    public function statsPage()
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
                $stats['routes'][$endpointResult->route]['hitCount'] = $hitCount;
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
         
        $stats['global']['totalHits'] = $totalHits;
        //$stats['global']['firstDay'] = $this->getFirstDate();
        //$stats['global']['lastDay'] = $this->getLastDate();
        $stats['global']['dayCont'] = $dayCount;
        $stats['global']['averagePerDay'] = round($averagePerDay);
        $stats['global']['projectedYearly'] = round($projectedYearly);
        $stats['global']['projectedMonthly'] = round($projectedMonthly);

        $stats = $this->array_to_object($stats);

        $string = null;

        $string .= '<h1>Stats</h1>'.PHP_EOL;

        $string .= '<h2>Global</h2>'.PHP_EOL;

        $object = $stats->global;

        $string .= '<ul>'.PHP_EOL;
        $string .= '<li>'.PHP_EOL;
        $string .= 'Total Hits: ' . $object->totalHits .PHP_EOL;
        $string .= '</li>'.PHP_EOL;
        $string .= '<li>'.PHP_EOL;
        $string .= 'Daily Average: ' . $object->averagePerDay .PHP_EOL;
        $string .= '</li>'.PHP_EOL;
        $string .= '<li>'.PHP_EOL;
        $string .= 'Projected Monthly: ' . $object->projectedMonthly .PHP_EOL;
        $string .= '</li>'.PHP_EOL;
        $string .= '<li>'.PHP_EOL;
        $string .= 'Projected Yearly: ' . $object->projectedYearly .PHP_EOL;
        $string .= '</li>'.PHP_EOL;
        $string .= '</ul>'.PHP_EOL;

        $string .= '<h2>Routes</h2>'.PHP_EOL;

        $object = $stats->routes;

        $string .= '<pre>'.PHP_EOL;
        $string .= print_r($object, true);
        $string .= '</pre>'.PHP_EOL;

        $response = new Response(
            $string,
            Response::HTTP_OK,
            array('content-type' => 'text/html')
        );

        return $response;
    }

    // get a list of all endpoints in the db
    private function getUniqueEndpoints()
    {
        $sql = "SELECT DISTINCT route FROM daily;";

        $result = $this->stats->query($sql);

        return $this->array_to_object($result->fetch_all(MYSQLI_ASSOC));
    }

    // get a list of all endpoints in the db
    private function getUniqueDays()
    {
        $sql = "SELECT DISTINCT date FROM daily ORDER BY date ASC;";

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
        $sql = "SELECT date, hits FROM daily WHERE route = '$route' ORDER BY date ASC";

        $result = $this->stats->query($sql);

        return $this->array_to_object($result->fetch_all(MYSQLI_ASSOC));
    }
}
