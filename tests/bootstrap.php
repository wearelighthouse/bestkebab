<?php

$testsDir = '/tmp/wordpress-tests-lib';

require_once $testsDir . '/includes/functions.php';

function loadBestKebab()
{
    require dirname(dirname(__FILE__)) . '/bestkebab.php';
}
tests_add_filter('muplugins_loaded', 'loadBestKebab');

define('THEME', dirname(__FILE__) . '/test_theme');

require $testsDir . '/includes/bootstrap.php';
