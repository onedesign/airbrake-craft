<?php
/**
 * airbrake plugin for Craft CMS 3.x
 *
 * Log Craft errors to airbrake.io
 *
 * @link      https://onedesigncompany.com
 * @copyright Copyright (c) 2019 One Design Company
 */

namespace onedesign\airbrake;

use Airbrake\Instance;
use Airbrake\Notifier;
use Craft;
use craft\base\Plugin;
use craft\events\PluginEvent;
use craft\services\Plugins;
use onedesign\airbrake\models\Settings;
use onedesign\airbrake\targets\AirbrakeTarget;
use yii\base\Event;

/**
 * Class Airbrake
 *
 * @author    One Design Company
 * @package   Airbrake
 * @since     0.0.1
 *
 */
class Airbrake extends Plugin
{
    // Static Properties
    // =========================================================================

    /**
     * @var Airbrake
     */
    public static $plugin;

    // Public Properties
    // =========================================================================

    /**
     * @var string
     */
    public $schemaVersion = '1.0.0';

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        self::$plugin = $this;

        if ($this->settings->enabled) {
            $airbrakeTarget = new AirbrakeTarget();
            $airbrakeTarget->setLevels($this->settings->levels);
            $airbrakeTarget->setExcludedTypes($this->settings->excludedTypes);
            $airbrakeTarget->setExcludedCategories($this->settings->excludedCategories);

            $settings = [
                'projectId' => $this->settings->projectId,
                'projectKey' => $this->settings->projectKey,
                'environment' => defined('CRAFT_ENVIRONMENT') ? CRAFT_ENVIRONMENT : null,
                'keysBlacklist' => ['/secret/i', '/password/i'],
            ];

            if ($this->settings->rootDirectory) {
                $settings['rootDirectory'] = $this->settings->rootDirectory;
            }

            $notifier = new Notifier($settings);

            // Adds the Craft user's info to the notice
            $notifier->addFilter(function($notice) {
                $user = Craft::$app->getUser()->getIdentity();
                if ($user) {
                    $notice['context']['user']['email'] = $user->email;
                    $notice['context']['user']['name'] = "{$user->firstName} {$user->lastName}";
                }

                return $notice;
            });

            Instance::set($notifier);

            Craft::getLogger()->dispatcher->targets[] = $airbrakeTarget;
        }

        Craft::info(
            Craft::t(
                'airbrake',
                '{name} plugin loaded',
                ['name' => $this->name]
            ),
            __METHOD__
        );
    }

    // Protected Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    protected function createSettingsModel()
    {
        return new Settings();
    }

    /**
     * @inheritdoc
     */
    protected function settingsHtml(): string
    {
        return Craft::$app->view->renderTemplate(
            'airbrake/settings',
            [
                'settings' => $this->getSettings()
            ]
        );
    }
}
