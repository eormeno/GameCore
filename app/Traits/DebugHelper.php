<?php

namespace App\Traits;

trait DebugHelper
{
    private const BASIC_INFO = ['source', 'data', 'is_signal', 'rendered'];

    public function log(string $message, string $type = 'log')
    {
        $events = session('events', []);
        $events[] = [
            'name' => 'log',
            'data' => ['message' => $message, 'time' => date('H:i:s'), 'type' => $type],
        ];
        session(['events' => $events]);
		// if the running environment is testing, print the message to the console
		if (env('APP_ENV') === 'testing') {
			echo 'log: ' . $message . PHP_EOL;
		}
    }

    public function info(string $message)
    {
        $this->log($message, 'info');
    }

    public function warn(string $message)
    {
        $this->log($message, 'warn');
    }

    public function error(string $message)
    {
        $this->log($message, 'error');
    }

    public function success(string $message)
    {
        $this->log($message, 'success');
    }

    public function logBacktrace()
    {
        $callers = debug_backtrace();
        array_shift($callers);
        $stack = [];
        foreach ($callers as $i => $caller) {
            if (!isset($caller['file'])) {
                continue;
            }
            if (strpos($caller['file'], 'vendor') !== false) {
                continue;
            }
            $fileName = $caller['file'];
            $fileName = str_replace(base_path(), '', $fileName);
            $stack[] = $fileName . ' (' . $caller['line'] . ')';
        }
        $this->log(json_encode($stack, JSON_PRETTY_PRINT));
    }

    public function logEvent(array $eventInfo, bool $excludeRefresh = true, array $exclude = self::BASIC_INFO)
    {
        if ($excludeRefresh && $eventInfo['event'] === 'refresh') {
            return;
        }
        foreach ($exclude as $key) {
            unset($eventInfo[$key]);
        }
        $this->log(json_encode($eventInfo));
    }

    public function logEventRendered(array $eventInfo)
    {
        $rendered = $eventInfo['rendered'] ?? [];
        $strRenderedLog = 'empty';
        if (!empty($rendered)) {
            $strRenderedLog = implode(",", $rendered);
        }
        $this->log("Client rendered: $strRenderedLog");
    }

}
