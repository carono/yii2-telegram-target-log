<?php

namespace carono\yii2log;

use Closure;
use Yii;
use yii\helpers\VarDumper;
use yii\log\Logger;
use yii\log\Target;

class TelegramTarget extends Target
{
    public int|string|array $chatId;
    public Closure $sendMessage;

    public function export()
    {
        $text = implode("\n", array_map([$this, 'formatMessage'], $this->messages));
        $truncText = mb_substr($text, 0, 4096);
        foreach ((array)$this->chatId as $chatId) {
            call_user_func_array($this->sendMessage, [$truncText, $chatId]);
        }
    }

    /**
     * Formats a log message for display as a string.
     *
     * @param array $message the log message to be formatted.
     * The message structure follows that in [[Logger::messages]].
     * @return string the formatted message
     */
    public function formatMessage($message)
    {
        [$text, $level, $category, $timestamp] = $message;
        $level = Logger::getLevelName($level);
        if (!is_string($text)) {
            // exceptions may not be serializable if in the call stack somewhere is a Closure
            if ($text instanceof \Throwable) {
                $text = (string)$text;
            } else {
                $text = VarDumper::export($text);
            }
        }
        $text = substr($text, 0, strpos($text, '#1') ?: strlen($text));
        $app = Yii::getAlias('@app');
        return "$app\n[$level][$category]\n\n$text";
    }
}