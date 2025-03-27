<?php

use SGS\Cache\Cache;
use SGS\Config\Config;
use SGS\Error\ErrorHandler;
use SGS\Error\ErrorTrap;
use SGS\Error\ExceptionTrap;
//use SGS\Log\Log;
use SGS\Log\Archive\LogArchiver;
use SGS\Log\LogManager;
use SGS\Signal\Signal;

require __DIR__ . DIRECTORY_SEPARATOR . 'paths.php';

/**
 * Load global functions.
 */
require SRC . DS . 'functions.php';

try {
    // Load configuration
    Config::load(CONFIG);
} catch (\Exception $e) {
    exit($e->getMessage() . "\n");
}

/*
 * Set the default server timezone. Using UTC makes time calculations / conversions easier.
 * Check https://php.net/manual/en/timezones.php for list of valid timezone strings.
 */
date_default_timezone_set(Config::get('App.defaultTimezone'));

/*
 * Configure the mbstring extension to use the correct encoding.
 */
mb_internal_encoding(Config::get('App.encoding'));

/*
 * Set the default locale. This controls how dates, number and currency is
 * formatted and sets the default language to use for translations.
 */
ini_set('intl.default_locale', Config::get('App.defaultLocale'));

// Initialize logging system
LogManager::init(Config::get('logs'));

// Register archiver
if (Config::get('logs.archive.enabled')) {
    register_shutdown_function(function() {
        $archiver = new LogArchiver(
            Config::get('logs.storage.logs'),
            Config::get('logs.storage.archives'),
            Config::get('logs.archive.max_age_days', 30)
        );
        $archiver->archive();
    });
}

/*
 * Register application error and exception handlers.
 */
(new ErrorTrap(Config::get('Error')))->register();
(new ExceptionTrap(Config::get('Error')))->register();


//try {
//    // Register the ErrorHandler
//    ErrorHandler::register();
//} catch (\Exception $e) {
//    exit($e->getMessage() . "\n");
//}
