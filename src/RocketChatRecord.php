<?php

namespace Sysvale\Logging;

use Monolog\Formatter\FormatterInterface;
use Monolog\Formatter\NormalizerFormatter;
use Monolog\Logger;
use Monolog\Utils;

/**
 * Rocket.Chat record utility helping to log to Rocket.Chat webhooks.
 *
 * @author Esron Silva <esron.sulva@sysvale.com>
 * @see    https://docs.rocket.chat/guides/administrator-guides/integrations
 */
class RocketChatRecord
{
    /**
     * Name that will appear in Rocket.Chat
     * @var string|null
     */
    private $username;

    /**
     * Emoji that will appear as the user
     * @var string|null
     */
    private $emoji;

    /**
     * @var FormatterInterface
     */
    private $formatter;

    /**
     * @var NormalizerFormatter
     */
    private $normalizerFormatter;

    /**
     * Colors for a given log level.
     *
     * @var array
     */
    private $levelColors = [
        Logger::DEBUG     => "#9E9E9E",
        Logger::INFO      => "#4CAF50",
        Logger::NOTICE    => "#607D8B",
        Logger::WARNING   => "#FFEB3B",
        Logger::ERROR     => "#F44336",
        Logger::CRITICAL  => "#F44336",
        Logger::ALERT     => "#F44336",
        Logger::EMERGENCY => "#F44336",
    ];

    public function __construct(
        string $username = null,
        string $emoji = null,
        FormatterInterface $formatter = null
    ) {
        $this->username = $username;
        $this->emoji = $emoji;
        $this->formatter = $formatter;

        $this->normalizerFormatter = new NormalizerFormatter();
    }

    public function getRocketChatData(array $record)
    {
        $dataArray = array();
        $attachment = array(
            'fields' => array(),
        );

        if ($this->username) {
            $dataArray['username'] = $this->username;
        }

        if ($this->emoji) {
            $dataArray['emoji'] = $this->emoji;
        }

        if ($this->formatter) {
            $attachment['text'] = $this->formatter->format($record);
        } else {
            $attachment['text'] = $record['message'];
        }

        foreach (array('extra', 'context') as $key) {
            if (empty($record[$key])) {
                continue;
            }

            $attachment['fields'] = array_merge(
                $attachment['fields'],
                $this->generateAttachmentFields($record[$key])
            );
        }

        $attachment['title'] = $record['level_name'];
        $attachment['color'] = $this->levelColors[$record['level']];
        $dataArray['attachments'] = array($attachment);

        return $dataArray;
    }

    /**
     * Stringifies an array of key/value pairs to be used in attachment fields
     *
     * @param array $fields
     *
     * @return string
     */
    public function stringify($fields)
    {
        $normalized = $this->normalizerFormatter->format($fields);
        $prettyPrintFlag = defined('JSON_PRETTY_PRINT') ? JSON_PRETTY_PRINT : 128;
        $flags = 0;
        if (PHP_VERSION_ID >= 50400) {
            $flags = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE;
        }

        $hasSecondDimension = count(array_filter($normalized, 'is_array'));
        $hasNonNumericKeys = !count(array_filter(array_keys($normalized), 'is_numeric'));

        return $hasSecondDimension || $hasNonNumericKeys
            ? Utils::jsonEncode($normalized, $prettyPrintFlag | $flags)
            : Utils::jsonEncode($normalized, $flags);
    }

    /**
     * Generates attachment field
     *
     * @param string       $title
     * @param string|array $value
     *
     * @return array
     */
    private function generateAttachmentField($title, $value)
    {
        $value = is_array($value)
            ? sprintf('```%s```', $this->stringify($value))
            : $value;

        return array(
            'title' => ucfirst($title),
            'value' => $value,
            'short' => false
        );
    }

    /**
     * Generates a collection of attachment fields from array
     *
     * @param array $data
     *
     * @return array
     */
    private function generateAttachmentFields(array $data)
    {
        $fields = array();
        foreach ($this->normalizerFormatter->format($data) as $key => $value) {
            $fields[] = $this->generateAttachmentField($key, $value);
        }

        return $fields;
    }
}
