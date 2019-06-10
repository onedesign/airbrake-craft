# Airbrake plugin for Craft CMS 3.x

Log Craft errors to airbrake.io

![Screenshot](resources/img/plugin-logo.png)

## Requirements

This plugin requires Craft CMS 3.0.0-beta.23 or later.

## Installation

To install the plugin, follow these instructions.

1. Open your terminal and go to your Craft project:

        cd /path/to/project

2. Then tell Composer to load the plugin:

        composer require onedesign/airbrake

3. In the Control Panel, go to Settings → Plugins and click the “Install” button for airbrake.

## airbrake Overview

This is a simple plugin that will send Craft and php errors to Airbrake. You need to have an airbrake.io account to use this.

## Configuring airbrake

Copy the config file from `plugins/airbrake/src/config.php` to `config/airbrake.php` and then set the following environment variables.

```
# Airbrake Logging
AIRBRAKE_PROJECT_ID=""
AIRBRAKE_PROJECT_KEY=""
AIRBRAKE_ENABLED=false
```

## Using airbrake

Install the plugin and make sure your environment variables are configured properly. That's it.


* Release it

Brought to you by [One Design Company](https://onedesigncompany.com)
