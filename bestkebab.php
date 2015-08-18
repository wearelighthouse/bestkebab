<?php
/*
 * Plugin Name: Best Kebab
 * Description: Bringing order to miscellaneous meat
 */

if (!defined('ABSPATH')) {
    exit;
}

define('PLUGINPATH', plugin_dir_path(__FILE__));

require_once 'config/bootstrap.php';

/**
 * CakePHP's pr function
 */
function pr($var)
{
    echo '<pre>';
        print_r($var);
    echo '</pre>';
}
