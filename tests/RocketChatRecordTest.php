<?php

namespace Tests;

use Monolog\Formatter\FormatterInterface;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use Sysvale\Logging\RocketChatRecord;

class RocketChatRecordTest extends TestCase
{
    public function testWithUsernameAndEmojiAndFormatter(): void
    {
        $formatter = $this->getFormatter();

        $rocketChatRecord = new RocketChatRecord(
            'username',
            'emoji',
            $formatter
        );

        $record = [
            'level_name' => 'debug',
            'level' => Logger::DEBUG,
        ];

        $expected = [
            'username' => 'username',
            'emoji' => 'emoji',
            'attachments' => [
                [
                    'fields' => [],
                    'text' => [
                        'level_name' => 'debug',
                        'level' => Logger::DEBUG,
                    ],
                    'title' => 'debug',
                    'color' => '#9E9E9E',
                ],
            ],
        ];

        $this->assertEquals($expected , $rocketChatRecord->getRocketChatData($record));
    }

    public function testWithoutUsernameAndEmojiAndFormatter(): void
    {
        $rocketChatRecord = new RocketChatRecord();

        $record = [
            'level_name' => 'debug',
            'level' => Logger::DEBUG,
            'message' => 'this is a test',
        ];

        $expected = [
            'attachments' => [
                [
                    'fields' => [],
                    'text' => 'this is a test',
                    'title' => 'debug',
                    'color' => '#9E9E9E',
                ],
            ],
        ];

        $this->assertEquals($expected , $rocketChatRecord->getRocketChatData($record));
    }

    public function testWithoutUsernameAndEmojiAndFormatterAndRecordHasExtraAndContent(): void
    {
        $rocketChatRecord = new RocketChatRecord();

        $record = [
            'level_name' => 'debug',
            'level' => Logger::DEBUG,
            'message' => 'this is a test',
            'extra' => ['extra#1' => 'read all about it'],
            'content' => ['some content here'],
        ];

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
                    'title' => 'debug',
                    'color' => '#9E9E9E',
                ],
            ],
        ];

        $this->assertEquals($expected, $rocketChatRecord->getRocketChatData($record));
    }

    public function testWithoutUsernameAndEmojiAndFormatterAndRecordHasExtraAndContentAndAreArrays(): void
    {
        $rocketChatRecord = new RocketChatRecord();

        $record = [
            'level_name' => 'debug',
            'level' => Logger::DEBUG,
            'message' => 'this is a test',
            'extra' => [
                'extra#1' => [
                    'read all about it',
                    'or not',
                ]
            ],
            'content' => [
                [
                    'some content here',
                    'another here',
                ],
            ],
        ];

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
                    'title' => 'debug',
                    'color' => '#9E9E9E',
                ],
            ],
        ];

        $this->assertEquals($expected , $rocketChatRecord->getRocketChatData($record));
    }

    public function testWithoutUsernameAndEmojiAndFormatterAndRecordHasExtraAndContentAndAreArraysWithNonNumericKeys(): void
    {
        $rocketChatRecord = new RocketChatRecord();

        $record = [
            'level_name' => 'debug',
            'level' => Logger::DEBUG,
            'message' => 'this is a test',
            'extra' => [
                'extra#1' => [
                    'a' => 'read all about it',
                    'b' => 'or not',
                ]
            ],
            'content' => [
                [
                    'some content here',
                    'another here',
                ],
            ],
        ];

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
                    'title' => 'debug',
                    'color' => '#9E9E9E',
                ],
            ],
        ];

        $this->assertEquals($expected , $rocketChatRecord->getRocketChatData($record));
    }

    private function getFormatter(): FormatterInterface
    {
        return new class implements FormatterInterface {

            public function format(array $record)
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
