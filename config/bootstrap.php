<?php

if (!defined('ABSPATH')) {
    exit;
}

require __DIR__ . '/paths.php';

if (file_exists(THEME . DS . 'config' . DS . 'app.php')) {
    require THEME . DS . 'config' . DS . 'app.php';
}

if (!defined('SITENAME')) {
    define('SITENAME', 'App');
}

require PLUGIN . DS . 'vendor' . DS . 'autoload.php';
