<?php
/**
 * airbrake-craft module for Craft CMS 3.x
 *
 * @link      https://onedesigncompany.com
 * @copyright Copyright (c) 2022 One Design Company
 */


namespace onedesign\airbrake\services;


use Airbrake\Errors\Base;
use Airbrake\Notifier;
use craft\base\Component;
use Throwable;
use const CRAFT_ENVIRONMENT;

/**
 * @author    One Design Company
 * @package   airbrake-craft
 * @since     1.0.0
 */
class Airbrake extends Component
{
    /**
     * @var null|Notifier
     */
    public $notifier = null;

    /**
     * @throws \Airbrake\Exception
     */
    public function init()
    {
        parent::init();

        $settings = \onedesign\airbrake\Airbrake::$plugin->settings;

        $notifierSettings = [
            'projectId' => $settings->projectId,
            'projectKey' => $settings->projectKey,
            'environment' => defined('CRAFT_ENVIRONMENT') ? CRAFT_ENVIRONMENT : null,
            'keysBlacklist' => ['/secret/i', '/password/i'],
        ];

        if ($rootDirectory = $settings->rootDirectory) {
            $notifierSettings['rootDirectory'] = $rootDirectory;
        }

        $this->notifier = new Notifier($notifierSettings);
    }

    /**
     * Shortcut delegating sendNotice() call to notifier.
     *
     * @param array $notice Notice built by buildNotice()
     *
     * @return array|int|mixed Result of the call
     */
    public function sendNotice(array $notice)
    {
        return $this->notifier->sendNotice($notice);
    }

    /**
     * Shortcut delegating buildNotice() call to notifier.
     *
     * @param Throwable|Base $throwable Throwable to notify
     *
     * @return array Built notification
     */
    public function buildNotice($throwable): array
    {
        return $this->notifier->buildNotice($throwable);
    }

}
