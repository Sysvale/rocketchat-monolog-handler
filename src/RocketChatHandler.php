<?php

namespace Sysvale\Logging;

use GuzzleHttp\Client;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;

class RocketChatHandler extends AbstractProcessingHandler
{
    /**
     * @var \GuzzleHttp\Client;
     */
    private $client;

    /**
     * @var array
     */
    private $webhooks;

    /**
     * @var string
     */
    private $username;

    /**
     * Colors for a given log level.
     *
     * @var array
     */
    protected $levelColors = [
        Logger::DEBUG => "#9E9E9E",
        Logger::INFO => "#4CAF50",
        Logger::NOTICE => "#607D8B",
        Logger::WARNING => "#FFEB3B",
        Logger::ERROR => "#F44336",
        Logger::CRITICAL => "#F44336",
        Logger::ALERT => "#F44336",
        Logger::EMERGENCY => "#F44336",
    ];

    /**
     * RocketChatHandler constructor.
     *
     * @param array $webhooks
     * @param string $username
     * @param int $level
     * @param bool $bubble
     */
    public function __construct(
        array  $webhooks,
        string $username = null,
        int    $level = Logger::DEBUG,
        bool   $bubble = true
    ) {
        parent::__construct($level, $bubble);
        $this->client = new Client();
        $this->webhooks = $webhooks;
        $this->username = $username;
    }

    /**
     * @param array $record
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function write(array $record): void
    {
        $level = $record['level'] ?? $this->level;

        $content = [
            "text" => $record['message'],
            "attachments" => [
                [
                    "title" => $record['level_name'] ?? '',
                    "text" => !empty($record['context']['exception'])
                        ? $record['context']['exception']->__toString()
                        : '',
                    "color" => $this->levelColors[$level],
                ],
            ],
        ];

        foreach ($this->webhooks as $webhook) {
            $this->client->request('POST', $webhook, [
                'json' => $content,
            ]);
        }
    }
}
