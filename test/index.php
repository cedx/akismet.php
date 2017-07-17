<?php
declare(strict_types=1);
use Rx\{Scheduler};

// Load the class library.
$rootPath = dirname(__DIR__);
require_once "$rootPath/vendor/autoload.php";

// Initialize the application.
Scheduler::setDefaultFactory([Scheduler::class, 'getImmediate']);
