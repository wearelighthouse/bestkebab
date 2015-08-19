<?php

namespace BestKebab\Utility;

if (!defined('ABSPATH')) {
    exit;
}

class ControllerContainer
{
    protected $_controllers = [];

    /**
     * @return \BestKebab\Utility\ControllerContainer
     */
    public static function instance()
    {
        static $instance = null;

        if ($instance === null) {
            $instance = new ControllerContainer();
        }

        return $instance;
    }

    /**
     * @return void
     */
    private function __construct()
    {
    }

    /**
     * @return void
     */
    private function __clone()
    {
    }

    /**
     * @return void
     */
    private function __wakeup()
    {
    }

    /**
     * @return void
     */
    public function addController($controller)
    {
        $controller = basename($controller, '.php');
        $postType = strtolower(Inflector::singularise(str_replace('Controller', '', $controller)));
        $ControllerClass = 'App\Controller\\' . $controller;
        $this->_controllers[$postType] = new $ControllerClass();
    }

    /**
     * @return \BestKebab\Controller\Controller
     */
    public function getController($postType)
    {
        return $this->_controllers[$postType];
    }
}
