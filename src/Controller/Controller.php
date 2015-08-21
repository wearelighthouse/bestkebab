<?php

namespace BestKebab\Controller;

if (!defined('ABSPATH')) {
    exit;
}

use BestKebab\Utility\Inflector as Inflector;

class Controller
{

    private $_postType = '';

    /**
     * @return void
     */
    public function __construct()
    {
        $this->_postType = Inflector::classify(basename(get_class($this), 'Controller'));
        $PostType = SITENAME . '\PostType\\' . $this->_postType . 'PostType';
        $this->{$this->_postType} = new $PostType();
        $this->initialise();
    }

    /**
     * @return void
     */
    public function initialise()
    {
    }

    /**
     * Display archive part for post type
     *
     * @return void
     */
    public function archive()
    {
        include 'src' . DS . 'Part' . DS . $this->{$this->_postType}->model() . DS . 'archive.php';
    }

    /**
     * Display single part for post type
     *
     * @return void
     */
    public function single()
    {
        include 'src' . DS . 'Part' . DS . $this->{$this->_postType}->model() . DS . 'single.php';
    }
}
