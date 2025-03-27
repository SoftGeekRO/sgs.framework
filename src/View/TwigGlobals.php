<?php

namespace SGS\View;

use SGS\Config\Config;

class TwigGlobals {
    public static function base(): array {
        return [
            'site_name' => Config::get('App.siteName'),
            'app_name' => Config::get('App.applicationName'),
            'author' => Config::get('App.author'),
            'year' => date('Y'),
            'base_url' => '/assets/',
            'debug_mode' => Config::get('debug', false),
        ];
    }
}