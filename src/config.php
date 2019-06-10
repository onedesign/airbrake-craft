<?php
/**
 * airbrake plugin for Craft CMS 3.x
 *
 * Log Craft errors to airbrake.io
 *
 * @link      https://onedesigncompany.com
 * @copyright Copyright (c) 2019 One Design Company
 */

/**
 * airbrake config.php
 *
 * This file exists only as a template for the airbrake settings.
 * It does nothing on its own.
 *
 * Don't edit this file, instead copy it to 'craft/config' as 'airbrake.php'
 * and make your changes there to override default settings.
 *
 * Once copied to 'craft/config', this file will be multi-environment aware as
 * well, so you can have different settings groups for each environment, just as
 * you do for 'general.php'
 */
return [

    // Global on/off switch
    'enabled' => filter_var(getenv('AIRBRAKE_ENABLED'), FILTER_VALIDATE_BOOLEAN),

    // Which log levels are sent to Airbrake
    // https://www.yiiframework.com/doc/api/2.0/yii-log-target#setLevels()-detail
    'levels' => [
        'error',
        // 'warning',
        // 'info',
        // 'trace',
        // 'profile'
    ],

    // Filter out stuff we don't care to see
    // You can add any \namespace\classname here
    'excludedTypes' => [
        '\yii\web\ForbiddenHttpException',
        '\yii\web\NotFoundHttpException',
    ],

    'excludedCategories' => [],

    'projectId' => getenv('AIRBRAKE_PROJECT_ID'),
    'projectKey' => getenv('AIRBRAKE_PROJECT_KEY'),

    'rootDirectory' => realpath(dirname(__DIR__) . '/../../'),
];
