<?php

/*
 * Use the DS to separate the directories in other defines
 */
if (!defined('DS')) {
    define('DS', DIRECTORY_SEPARATOR);
}

/**
 * The full path to the directory which holds "src", WITHOUT a trailing DS.
 * If the framework is in his own folder, you can use dirname(__DIR__)
 */
define('ROOT', dirname(__DIR__, 3));

/**
 * When the framework is in SRC folder
 */
define('SRC', dirname(__DIR__));

/**
 * Define the SRC folder of the framework
 */
const CORE = SRC . DS . 'Core' . DS;

/*
 * The actual directory name for the application directory. Normally
 * named 'app'.
 */
const APP_DIR = 'app';

/*
 * Path to the application's directory.
 */
const APP = ROOT . DS . APP_DIR . DS;

/*
 * Path to the application config directory.
 */
const CONFIG = ROOT . DS . APP_DIR . DS. 'config' . DS;

define('WWW_ROOT', '');
/*
 * Path to the temporary files' directory.
 */
const TMP = ROOT . DS . 'tmp' . DS;

/*
 * Path to the logs' directory.
 */
const LOGS = ROOT . DS . 'logs' . DS;

/*
 * Path to the cache files directory. It can be shared between hosts in a multi-server setup.
 */
const CACHE = TMP . 'cache' . DS;

/*
 * Path to the webroot directory.
 */
const WEBROOT = ROOT . DS . 'webroot' . DS;
