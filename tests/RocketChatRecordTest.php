<?php

namespace Tests;

use Monolog\Formatter\FormatterInterface;
use Monolog\Level;
use Monolog\Logger;
use Monolog\LogRecord;
use Monolog\Test\MonologTestCase;
use Sysvale\Logging\RocketChatRecord;

class RocketChatRecordTest extends MonologTestCase
{
    public function testWithUsernameAndEmojiAndFormatter(): void
    {
        $formatter = $this->getFormatter();

        $rocketChatRecord = new RocketChatRecord(
            'username',
            'emoji',
            $formatter
        );

        $record = $this->getRecord(
            level: Level::Debug,
            message: 'this is a test',
        );


        $expected = [
            'username' => 'username',
            'emoji' => 'emoji',
            'attachments' => [
                [
                    'fields' => [],
                    'text' => $record,
                    'title' => Level::Debug->getName(),
                    'color' => '#9E9E9E',
                ],
            ],
        ];

        $this->assertEquals($expected, $rocketChatRecord->getRocketChatData($record));
    }

    public function testWithoutUsernameAndEmojiAndFormatter(): void
    {
        $rocketChatRecord = new RocketChatRecord();

        $record = $this->getRecord(
            level: Level::Debug,
            message: 'this is a test',
        );

        $expected = [
            'attachments' => [
                [
                    'fields' => [],
                    'text' => 'this is a test',
                    'title' => Level::Debug->getName(),
                    'color' => '#9E9E9E',
                ],
            ],
        ];

        $this->assertEquals($expected, $rocketChatRecord->getRocketChatData($record));
    }

    public function testWithoutUsernameAndEmojiAndFormatterAndRecordHasExtraAndContent(): void
    {
        $rocketChatRecord = new RocketChatRecord();

        $record = $this->getRecord(
            level: Level::Debug,
            message: 'this is a test',
            extra: ['extra#1' => 'read all about it'],
        );

        $expected = [
            'attachments' => [
                [
                    'fields' => [
                        [
                            'title' => 'Extra#1',
                            'value' => 'read all about it',
                            'short' => false,
                        ],

                    ],
                    'text' => 'this is a test',
                    'title' => Level::Debug->getName(),
                    'color' => '#9E9E9E',
                ],
            ],
        ];

        $this->assertEquals($expected, $rocketChatRecord->getRocketChatData($record));
    }

    public function testWithoutUsernameAndEmojiAndFormatterAndRecordHasExtraAndContentAndAreArrays(): void
    {
        $rocketChatRecord = new RocketChatRecord();

        $record = $this->getRecord(
            level: Level::Debug,
            message: 'this is a test',
            extra: [
                'extra#1' => [
                    'read all about it',
                    'or not',
                ]
            ],
        );

        $expected = [
            'attachments' => [
                [
                    'fields' => [
                        [
                            'title' => 'Extra#1',
                            'value' => '```["read all about it","or not"]```',
                            'short' => false,
                        ],

                    ],
                    'text' => 'this is a test',
                    'title' => Level::Debug->getName(),
                    'color' => '#9E9E9E',
                ],
            ],
        ];

        $this->assertEquals($expected, $rocketChatRecord->getRocketChatData($record));
    }

    public function testWithoutUsernameAndEmojiAndFormatterAndRecordHasExtraAndContentAndAreArraysWithNonNumericKeys(): void
    {
        $rocketChatRecord = new RocketChatRecord();

        $record = $this->getRecord(
            level: Level::Debug,
            message: 'this is a test',
            extra: [
                'extra#1' => [
                    'a' => 'read all about it',
                    'b' => 'or not',
                ]
            ],
        );

        $expected = [
            'attachments' => [
                [
                    'fields' => [
                        [
                            'title' => 'Extra#1',
                            'value' => '```{
    "a": "read all about it",
    "b": "or not"
}```',
                            'short' => false,
                        ],

                    ],
                    'text' => 'this is a test',
                    'title' => Level::Debug->getName(),
                    'color' => '#9E9E9E',
                ],
            ],
        ];

        $this->assertEquals($expected, $rocketChatRecord->getRocketChatData($record));
    }

    private function getFormatter(): FormatterInterface
    {
        return new class implements FormatterInterface {

            public function format(LogRecord $record)
            {
                return $record;
            }

            public function formatBatch(array $records)
            {
                return $records;
            }
        };
    }
}
