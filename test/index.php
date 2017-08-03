<?php
declare(strict_types=1);
use Rx\{Scheduler};

// Load the class library.
$rootPath = dirname(__DIR__);
require_once "$rootPath/vendor/autoload.php";

// Initialize the application.
ini_set('xdebug.max_nesting_level', '1024');
Scheduler::setDefaultFactory([Scheduler::class, 'getImmediate']);
