<?php

declare(strict_types=1);

namespace Tests;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use Monolog\Formatter\FormatterInterface;
use Monolog\Level;
use Monolog\Logger;
use Monolog\Test\MonologTestCase;
use ReflectionObject;
use Sysvale\Logging\RocketChatHandler;

class RocketChatHandlerTest extends MonologTestCase
{
    public function testHandleWithoutWebhooks(): void
    {
        $rocketChatHandler = new RocketChatHandler(
            [],
            'username',
            'emoji',
            Level::Debug,
        );

        $rocketChatHandler->setFormatter($this->getFormatter());

        $record = $this->getRecord(
            level: Level::Debug,
        );


        $this->assertFalse($rocketChatHandler->handle($record));
    }

    public function testHandleWithWebhooks(): void
    {
        $rocketChatHandler = new RocketChatHandler(
            ['/test'],
            'username',
            'emoji',
            Level::Debug
        );

        $client = $this->createMock(ClientInterface::class);
        $reflectionObject = new ReflectionObject($rocketChatHandler);
        $reflectionProperty = $reflectionObject->getProperty('client');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($rocketChatHandler, $client);

        $rocketChatHandler->setFormatter($this->getFormatter());

        $record = $this->getRecord(
            level: Level::Debug,
        );

        $this->assertFalse($rocketChatHandler->handle($record));
    }

    private function getFormatter(): FormatterInterface
    {
        return $this->createMock(FormatterInterface::class);
    }
}
