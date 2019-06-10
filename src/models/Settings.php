<?php
/**
 * airbrake plugin for Craft CMS 3.x
 *
 * Log Craft errors to airbrake.io
 *
 * @link      https://onedesigncompany.com
 * @copyright Copyright (c) 2019 One Design Company
 */

namespace onedesign\airbrake\models;

use onedesign\airbrake\Airbrake;

use Craft;
use craft\base\Model;

/**
 * @author    One Design Company
 * @package   Airbrake
 * @since     0.0.1
 */
class Settings extends Model
{
    // Public Properties
    // =========================================================================

    /**
     * @var string
     */
    public $enabled = false;

    /**
     * @var array
     */
    public $levels = [
        'error',
        // 'warning',
        // 'info',
        // 'trace',
        // 'profile'
    ];

    /**
     * @var string
     */
    public $projectId;

    /**
     * @var string
     */
    public $projectKey;

    /**
     * Excluded error types. These are class names.
     *
     * @var array
     */
    public $excludedTypes = [];

    /**
     * Excluded error categiries.
     *
     * @var array
     */
    public $excludedCategories = [];

    /**
     * The root directory of the site
     *
     * @var string
     */
    public $rootDirectory;

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['enabled', 'boolean'],
            ['projectId', 'string'],
            ['projectKey', 'string'],
        ];
    }
}
