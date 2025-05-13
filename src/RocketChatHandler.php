<?php

namespace Sysvale\Logging;

use GuzzleHttp\Client;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Level;
use Monolog\Logger;
use Monolog\LogRecord;
use Sysvale\Logging\RocketChatRecord;

class RocketChatHandler extends AbstractProcessingHandler
{
    /**
     * @var \GuzzleHttp\Client;
     */
    private $client;

    /**
     * Name that will appear in Rocket.Chat
     * @var string|null
     */
    private $username;

    /**
     * @var array
     */
    private $webhooks;

    /**
     * Instance of the SlackRecord util class preparing data for Slack API.
     * @var RocketChatRecord
     */
    private $rocketChatRecord;

    /**
     * RocketChatHandler constructor.
     *
     * @param array $webhooks
     * @param string $username
     * @param string $emoji
     * @param int $level
     * @param bool $bubble
     */
    public function __construct(
        array  $webhooks,
        ?string $username,
        ?string $emoji,
        $level = Level::Error,
        bool   $bubble = true
    ) {
        parent::__construct($level, $bubble);

        $this->webhooks = $webhooks;
        $this->username = $username;

        $this->client = new Client();

        $this->rocketChatRecord = new RocketChatRecord(
            $username,
            $emoji,
            $this->formatter
        );
    }

    /**
     * @param array $record
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function write(LogRecord $record): void
    {
        $content = $this->rocketChatRecord->getRocketChatData($record);

        foreach ($this->webhooks as $webhook) {
            $this->client->request('POST', $webhook, [
                'json' => $content,
            ]);
        }
    }
}
