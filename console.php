#!/usr/bin/env php
<?php
// application.php

require __DIR__.'/vendor/autoload.php';

use Dotenv\Dotenv;
use Symfony\Component\Console\Application;

// load .env file
$dotenv = new Dotenv(__DIR__);
$dotenv->load();

$application = new Application();

$application->add(new commands\GetCountriesCommand());

$application->run();
