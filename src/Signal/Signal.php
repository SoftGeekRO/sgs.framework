<?php

namespace SGS\Signal;

class Signal {
    private static ?self $instance = null;
    private array $listeners = [];

    /**
     * Private constructor to prevent direct instantiation.
     */
    private function __construct() {}

    /**
     * Get the singleton instance of the Signal class.
     *
     * @return Signal
     */
    public static function getInstance(): self {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Subscribe to a signal.
     *
     * @param string $signal The signal name.
     * @param callable $callback The callback to execute when the signal is published.
     */
    public function subscribe(string $signal, callable $callback): void {
        if (!isset($this->listeners[$signal])) {
            $this->listeners[$signal] = [];
        }
        $this->listeners[$signal][] = $callback;
    }

    /**
     * Publish a signal.
     *
     * @param string $signal The signal name.
     * @param mixed|null $message The message to pass to subscribers.
     */
    public function publish(string $signal, mixed $message = null): void {
        if (isset($this->listeners[$signal])) {
            foreach ($this->listeners[$signal] as $callback) {
                call_user_func($callback, $message);
            }
        }
    }

    public function getListeners(): array {
        return $this->listeners;
    }
}