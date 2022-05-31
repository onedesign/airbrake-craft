<?php

namespace onedesign\airbrake\targets;

use Airbrake;
use onedesign\airbrake\Airbrake as AirbrakeService;
use yii\base\InvalidConfigException;
use yii\log\Logger;
use yii\log\Target;

/**
 * Docs https://www.yiiframework.com/doc/api/2.0/yii-log-target
 */
class AirbrakeTarget extends Target
{
    /**
     * Set this to true to send log messages to /tmp/airbrake.log instead of
     * sending them to airbrake.
     *
     * NOTE: Only use this in development
     *
     * @var boolean
     */
    private $_debug = false;

    public $logVars = [
        '_GET',
        '_POST',
        '_FILES',
        '_COOKIE',
        '_SESSION',
        '_SERVER',
    ];

    /**
     * @var array
     */
    private $_levels = [
        Logger::LEVEL_ERROR => 'error',
        Logger::LEVEL_WARNING => 'warning',
        Logger::LEVEL_INFO => 'info',
        Logger::LEVEL_TRACE => 'trace',
        Logger::LEVEL_PROFILE => 'profile',
        Logger::LEVEL_PROFILE_BEGIN => 'profile',
        Logger::LEVEL_PROFILE_END => 'profile',
    ];

    // empty means all categories
    public $categories = [];

    // these are categories we're not interested in
    public $except = [];

    // These are types of errors that should be filtered out
    // e.g. [yii\web\ForbiddenHttpException]
    protected $excludedTypes = [];

    // Categories that are excluded
    protected $excludedCategories = [];

    // Sets the excluded types
    public function setExcludedTypes(array $types = [])
    {
        $this->excludedTypes = $types;
    }

    // Sets the excluded categories
    public function setExcludedCategories(array $categories = [])
    {
        $this->excludedCategories = $categories;
    }

    /**
     * Message structure:
     * [
     *   [0] => message (mixed, can be a string or some complex data, such as an exception object)
     *   [1] => level (integer)
     *   [2] => category (string)
     *   [3] => timestamp (float, obtained by microtime(true))
     *   [4] => traces (array, debug backtrace, contains the application code call stacks)
     *   [5] => memory usage in bytes (int, obtained by memory_get_usage())
     * ]
     * See more https://www.yiiframework.com/doc/api/2.0/yii-log-logger#$messages-detail
     *
     * @return void
     */
    public function export()
    {
        foreach ($this->messages as [$content, $level, $category, $timestamp, $traces]) {
            if (!isset($this->_levels[$level])) {
                continue;
            }

            // Filters out context message cause it just results in a duplicate in airbrake
            if ($content == $this->getContextMessage()) {
                continue;
            }

            if ($this->categoryIsExcluded($category)) {
                continue;
            }

            if ($this->typeIsExcluded($content)) {
                continue;
            }

            if ($content instanceof \Throwable) {
                $airbrakeNotice = AirbrakeService::$plugin->airbrake->buildNotice($content);
            } else {
                $customError = $this->buildCustomError($content, $level, $traces);
                AirbrakeService::$plugin->airbrake->buildNotice($customError);
            }

            $airbrakeNotice['context']['severity'] = Logger::getLevelName($level);
            $airbrakeNotice['context']['category'] = $category;
            $airbrakeNotice['context']['timestamp'] = date('Y-m-d H:i:s', $timestamp);

            if ($airbrakeNotice !== null) {
                AirbrakeService::$plugin->airbrake->sendNotice($airbrakeNotice);
            }
        }
    }

    /**
     * @param $content
     * @param $level
     * @param array $traces
     * @return Airbrake\Errors\Error|Airbrake\Errors\Notice|Airbrake\Errors\Warning
     */
    protected function buildCustomError($content, $level, array $traces)
    {
        switch ($level) {
            case Logger::LEVEL_ERROR:
                return new Airbrake\Errors\Error($content, $traces);
            case Logger::LEVEL_WARNING:
                return new Airbrake\Errors\Warning($content, $traces);
            case Logger::LEVEL_INFO:
            case Logger::LEVEL_PROFILE:
            case Logger::LEVEL_TRACE:
            default:
                return new Airbrake\Errors\Notice($content, $traces);
        }
    }

    /**
     * Whether an object should be filtered based
     * on type.
     *
     * @param mixed $obj
     * @return boolean
     */
    private function typeIsExcluded($obj): bool
    {
        foreach ($this->excludedTypes as $type) {
            if ($obj instanceof $type) {
                return true;
            }
        }
        return false;
    }

    /**
     * Whether an object should be filtered based
     * on its logging category
     *
     * @param mixed string
     * @return boolean
     */
    private function categoryIsExcluded($categoryToCheck): bool
    {
        foreach ($this->excludedCategories as $category) {
            if ($category === $categoryToCheck) {
                return true;
            }
        }
        return false;
    }

    /**
     * Sets the message levels that this target is interested in.
     *
     * The parameter can be an array.
     * Valid level names include:
     *  'error',
     *  'warning',
     *  'info',
     *  'trace'
     *  'profile'
     *
     * For example,
     *
     * ```php
     * ['error', 'warning', Logger::LEVEL_PROFILE, Logger::LEVEL_TRACE]
     * ```
     *
     * @param array $levels message levels that this target is interested in.
     * @throws InvalidConfigException if $levels value is not correct.
     */
    public function setLevels($levels)
    {
        static $levelMap = [
            'error' => Logger::LEVEL_ERROR,
            'warning' => Logger::LEVEL_WARNING,
            'info' => Logger::LEVEL_INFO,
            'trace' => Logger::LEVEL_TRACE,
            'profile' => Logger::LEVEL_PROFILE,
        ];
        if (is_array($levels)) {
            $interestingLevels = [];

            foreach ($levels as $level) {
                if (!isset($this->_levels[$level]) && !isset($levelMap[$level])) {
                    throw new InvalidConfigException("Unrecognized level: $level");
                }
                if (isset($levelMap[$level])) {
                    $interestingLevels[$levelMap[$level]] = $this->_levels[$levelMap[$level]];
                }
                if (isset($this->_levels[$level])) {
                    $interestingLevels[$level] = $this->_levels[$level];
                }
            }

            $this->_levels = $interestingLevels;
        } else {
            throw new InvalidConfigException("Incorrect $levels value");
        }
    }
}
