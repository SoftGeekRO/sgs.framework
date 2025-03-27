<?php


use Monolog\Level;
use SGS\Log\Processors\{
    EnvironmentProcessor,
    MemoryUsageProcessor,
    UidProcessor,
    UserProcessor,
    WebProcessor
};

return [
    'debug' => false, // default value for debug is false
    'App' => [
        'baseUrl' => '',
        'encoding' => env('APP_ENCODING', 'UTF-8'),
        'defaultLocale' => env('APP_DEFAULT_LOCALE', 'en_US'),
        'defaultTimezone' => env('APP_DEFAULT_TIMEZONE', 'UTC'),
        'webroot' => 'webroot',
        'wwwRoot' => WWW_ROOT,
        'absolutePath' => false,
        'paths' => [
            'templates' => [
                SRC . DS . 'templates' . DS,
                APP .  'templates' . DS
            ]
        ]
    ],
    'assets' => [
        'imageBaseUrl' => 'img/',
        'cssBaseUrl' => 'css/',
        'jsBaseUrl' => 'js/',
        'fontsBaseUrl' => 'fonts/',
        'default' => 'assets/'
    ],
    'middleware' => [],
    'logs' => [
        'default' => [
            'channel' => 'app',
            'format' => 'text',
        ],
        'storage' => [
            'logs' => LOGS,
            'archives' => LOGS . DS. 'archives',
        ],
        'channels' => [
            'app' => [
                'handlers' => [
                    'rotating' => [
                        'type' => 'rotating',
                        'path' => LOGS. DS . 'app.log',
                        'level' => Level::Debug,
                        'max_files' => 7,
                        'formatter' => 'json',
                    ],
                    'stdout' => [
                        'type' => 'stream',
                        'stream' => 'php://stdout',
                        'level' => Level::Info,
                        'formatter' => 'json',
                    ]
                ]
            ],
            'error' => [
                'handlers' => [
                    'rotating' => [
                        'type' => 'rotating',
                        'path' => LOGS. DS . 'error.log',
                        'level' => Level::Error,
                        'max_files' => 30,
                        'formatter' => 'json',
                    ],
                    'syslog' => [
                        'type' => 'syslog',
                        'ident' => 'sgs-error',
                        'facility' => LOG_LOCAL0,
                        'level' => Level::Critical,
                        'formatter' => 'syslog',
                    ]
                ]
            ]
        ],
        'processors' => [
            WebProcessor::class,
            [
                'class' => UserProcessor::class,
                'args' => ['session_key' => 'user']
            ],
            [
                'class' => EnvironmentProcessor::class,
                'args' => [
                    'envVars' => ['APP_ENV', 'APP_DEBUG'], // Environment variables to include
                    'includeServer' => true                 // Whether to include server data
                ]
            ],
            [
                'class' => MemoryUsageProcessor::class,
                'args' => [
                    'realUsage' => true,    // Get real memory usage
                    'includePeak' => true   // Include peak memory usage
                ]
            ],
            UidProcessor::class,
        ],
        'archive' => [
            'enabled' => true,
            'max_age_days' => 30,
        ]
    ],
    'cache' => [
        'views' => [
            'enabled' => false,
            'path' => CACHE . DS . 'views',
        ]

    ],

    /*
     * Configure the Error and Exception handlers used by your application.
     *
     * Options:
     *
     * - `errorLevel` - int - The level of errors you are interested in capturing.
     * - `trace` - boolean - Whether backtraces should be included in
     *   logged errors/exceptions.
     * - `log` - boolean - Whether you want exceptions logged.
     * - `ignoredDeprecationPaths` - array - A list of glob-compatible file paths that deprecations
     *   should be ignored in. Use this to ignore deprecations for plugins or parts of
     *   your application that still emit deprecations.
     */
    'Error' => [
        'errorLevel' => E_ALL,
        'skipLog' => [],
        'log' => true,
        'trace' => true,
        'ignoredDeprecationPaths' => [],
    ],
];