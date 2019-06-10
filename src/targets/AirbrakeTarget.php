<?php

namespace onedesign\airbrake\targets;

use yii\log\Target;
use yii\log\Logger;
use yii\base\InvalidConfigException;
use Airbrake;
use Exception;

class StringError extends Exception {}
class ArrayError extends Exception {}

/**
 * Docs https://www.yiiframework.com/doc/api/2.0/yii-log-target
 */
class AirbrakeTarget extends Target {
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
  public function setExcludedTypes(Array $types = []) {
    $this->excludedTypes = $types;
  }

  // Sets the excluded categories
  public function setExcludedCategories(Array $categories = []) {
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
  public function export() {
    foreach($this->messages as $message) {
      $level = $message[1];
      if (!isset($this->_levels[$level])) {
        continue;
      }

      // Filters out context message cause it just results in a duplicate in airbrake
      if ($message[0] == $this->getContextMessage()) {
        continue;
      }

      if ($this->categoryIsExcluded($message[2])) {
        continue;
      }

      if ($this->typeIsExcluded($message[0])) {
        continue;
      }

      // The Airbrake library expects a throwable class
      if (is_string($message[0])) {
        $error = new StringError($message[0]);
      } elseif (is_array($message[0])) {
        $error = new ArrayError(implode("\n", $message[0]));
      } else {
        $error = $message[0];
      }

      if ($this->_debug == true) {
        error_log($level . ': ' . get_class($error) . ': ' . $error->getMessage() ."\n", 3, '/tmp/airbrake.log');
      } else {
        Airbrake\Instance::notify($error);
      }
    }
  }

  /**
   * Whether an object should be filtered based
   * on type.
   *
   * @param mixed $obj
   * @return boolean
   */
  private function typeIsExcluded($obj) {
    foreach($this->excludedTypes as $type) {
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
  private function categoryIsExcluded($categoryToCheck) {
    foreach($this->excludedCategories as $category) {
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
  public function setLevels($levels) {
    static $levelMap = [
      'error' => Logger::LEVEL_ERROR,
      'warning' => Logger::LEVEL_WARNING,
      'info' => Logger::LEVEL_INFO,
      'trace' => Logger::LEVEL_TRACE,
      'profile' => Logger::LEVEL_PROFILE,
    ];
    if (is_array($levels)) {
      $intrestingLevels = [];

      foreach ($levels as $level) {
        if (!isset($this->_levels[$level]) && !isset($levelMap[$level])) {
          throw new InvalidConfigException("Unrecognized level: $level");
        }
        if (isset($levelMap[$level])) {
          $intrestingLevels[$levelMap[$level]] = $this->_levels[$levelMap[$level]];
        }
        if (isset($this->_levels[$level])) {
          $intrestingLevels[$level] = $this->_levels[$level];
        }
      }

      $this->_levels = $intrestingLevels;
    } else {
      throw new InvalidConfigException("Incorrect $levels value");
    }
  }
}
