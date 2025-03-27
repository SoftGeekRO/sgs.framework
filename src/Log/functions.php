<?php

// Helper function
use SGS\Log\LogManager;

if (!function_exists('logger')) {
    function logger(?string $channel = null): \SGS\Log\Logger
    {
        return LogManager::channel($channel);
    }
}