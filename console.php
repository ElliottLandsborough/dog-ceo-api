#!/usr/bin/env php
<?php
// application.php

require __DIR__.'/vendor/autoload.php';

use Symfony\Component\Console\Application;
use Dotenv\Dotenv;

// load .env file
$dotenv = new Dotenv(__DIR__);
$dotenv->load();

$application = new Application();

$application->add(new commands\GetCountriesCommand());

$application->run();
