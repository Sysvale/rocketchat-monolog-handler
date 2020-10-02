<?php

declare(strict_types=1);

namespace Tests;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use Monolog\Formatter\FormatterInterface;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use ReflectionObject;
use Sysvale\Logging\RocketChatHandler;

class RocketChatHandlerTest extends TestCase
{
    public function testHandleWithoutWebhooks(): void
    {
        $rocketChatHandler = new RocketChatHandler(
            [],
            'username',
            'emoji',
            Logger::DEBUG
        );

        $rocketChatHandler->setFormatter($this->getFormatter());

        $record = [
            'level' => Logger::DEBUG,
            'level_name' => 'debug',
            'message' => 'test',
        ];


        $this->assertFalse($rocketChatHandler->handle($record));
    }

    public function testHandleWithWebhooks(): void
    {
        $rocketChatHandler = new RocketChatHandler(
            ['/test'],
            'username',
            'emoji',
            Logger::DEBUG
        );

        $client = $this->createMock(ClientInterface::class);
        $reflectionObject = new ReflectionObject($rocketChatHandler);
        $reflectionProperty = $reflectionObject->getProperty('client');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($rocketChatHandler, $client);

        $rocketChatHandler->setFormatter($this->getFormatter());

        $record = [
            'level' => Logger::DEBUG,
            'level_name' => 'debug',
            'message' => 'test',
        ];


        $this->assertFalse($rocketChatHandler->handle($record));
    }

    private function getFormatter(): FormatterInterface
    {
        return $this->createMock(FormatterInterface::class);
    }
}
