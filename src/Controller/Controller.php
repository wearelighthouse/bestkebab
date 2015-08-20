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
        $this->_postType = Inflector::postTypify(get_class($this));
        $PostType = SITENAME . '\PostType\\' . $this->_postType;

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
        include 'App/Part/' . $this->{$this->_postType}->name() . '/archive.php';
    }

    /**
     * Display single part for post type
     *
     * @return void
     */
    public function single()
    {
        include 'App/Part/' . $this->{$this->_postType}->name() . '/single.php';
    }
}
