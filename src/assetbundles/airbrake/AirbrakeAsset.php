<?php
/**
 * airbrake plugin for Craft CMS 3.x
 *
 * Log Craft errors to airbrake.io
 *
 * @link      https://onedesigncompany.com
 * @copyright Copyright (c) 2019 One Design Company
 */

namespace onedesign\airbrake\assetbundles\airbrake;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

/**
 * @author    One Design Company
 * @package   Airbrake
 * @since     0.0.1
 */
class AirbrakeAsset extends AssetBundle
{
    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->sourcePath = "@onedesign/airbrake/assetbundles/airbrake/dist";

        $this->depends = [
            CpAsset::class,
        ];

        $this->js = [
            'js/Airbrake.js',
        ];

        $this->css = [
            'css/Airbrake.css',
        ];

        parent::init();
    }
}
