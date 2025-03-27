<?php declare(strict_types=1);

namespace SGS\Log\Processors;

use Monolog\Processor\ProcessorInterface;

class EnvironmentProcessor implements ProcessorInterface {
    private array $envVars;
    private bool $includeServer;

    public function __construct(array $envVars = [], bool $includeServer = false) {
        $this->envVars = $envVars;
        $this->includeServer = $includeServer;
    }

    /**
     * @param array $record
     * @return array
     */
    public function __invoke($record) {
        $extra = [
            'env' => $this->getEnvironmentData(),
            'php' => [
                'version' => PHP_VERSION,
                'sapi' => PHP_SAPI,
                'memory_limit' => ini_get('memory_limit')
            ]
        ];

        if ($this->includeServer) {
            $extra['server'] = $this->getServerData();
        }

        $record->extra = array_merge($record->extra, $extra);
        return $record;
    }

    private function getEnvironmentData(): array {
        $data = [];
        foreach ($this->envVars as $var) {
            if (isset($_ENV[$var])) {
                $data[$var] = $_ENV[$var];
            }
        }
        return $data;
    }

    private function getServerData(): array {
        $safeServerVars = [
            'SERVER_NAME',
            'SERVER_ADDR',
            'SERVER_PORT',
            'REMOTE_ADDR',
            'REQUEST_METHOD',
            'REQUEST_TIME'
        ];

        $data = [];
        foreach ($safeServerVars as $var) {
            if (isset($_SERVER[$var])) {
                $data[$var] = $_SERVER[$var];
            }
        }
        return $data;
    }
}