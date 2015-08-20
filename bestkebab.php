<?php
/*
 * Plugin Name: Best Kebab
 * Description: Bringing order to miscellaneous meat
 */

if (!defined('ABSPATH')) {
    exit;
}

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

/**
 * Autoloading for theme files
 */
function loadClass($class)
{
    if (stripos($class, SITENAME) !== false && stripos($class, SITENAME) === 0) {
        $parts = explode('\\', $class);
        $file = array_pop($parts) . '.php';

        array_shift($parts);
        $path = 'src' . DS;

        foreach ($parts as $part) {
            $path .= $part . DS;
        }

        require $path . $file;
    }
}

set_include_path(get_include_path() . ':' . get_stylesheet_directory());
spl_autoload_register(__NAMESPACE__ . '\loadClass');

/**
 * Best Kebab init
 */
function bestKebabInit()
{
    if (!file_exists(get_stylesheet_directory() . DS . 'src' . DS . 'Controller')) {
        return;
    }

    $directory = new DirectoryIterator(get_stylesheet_directory() . DS . 'src' . DS . 'Controller');

    foreach ($directory as $controller) {
        if (!$controller->isDot()) {
            BestKebab\Utility\ControllerContainer::instance()->addController($controller->getFileName());
        }
    }
}

add_action('init', 'bestKebabInit');

/**
 * The Best Kebab function
 */
function bestKebab()
{
    global $post, $wp_query;

    if (!isset($post)) {
        $postType = 'post';
    } else {
        $postType = $post->post_type;
    }

    if ($postType === 'page' || isset($wp_query->query_vars[$postType])) {
        BestKebab\Utility\ControllerContainer::instance()->getController($postType)->single();
    } else {
        BestKebab\Utility\ControllerContainer::instance()->getController($postType)->archive();
    }
}
