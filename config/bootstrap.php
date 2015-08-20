<?php

if (!defined('ABSPATH')) {
    exit;
}

if (file_exists(get_stylesheet_directory() . '/config/app.php')) {
    require get_stylesheet_directory() . '/config/app.php';
} else {
    if (!defined('SITENAME')) {
        define('SITENAME', 'App');
    }
}

require __DIR__ . '/paths.php';
require PLUGIN . DS . 'vendor' . DS . 'autoload.php';
