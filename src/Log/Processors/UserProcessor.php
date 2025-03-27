<?php

namespace SGS\Log\Processors;

use Monolog\Processor\ProcessorInterface;

class UserProcessor implements ProcessorInterface {
    private string $sessionKey;

    public function __construct(string $session_key = 'user') {
        $this->sessionKey = $session_key;
    }

    public function __invoke($record) {
        if (session_status() === PHP_SESSION_ACTIVE && isset($_SESSION[$this->sessionKey])) {

            $user = $_SESSION[$this->sessionKey] ?? false;
            if ($user) {
                $record['extra']['user'] = [
                    'id' => $user['id'] ?? null,
                    'username' => $user['username'] ?? null,
                    'email' => $user['email'] ?? null,
                ];
            }
        }
        return $record;
    }
}