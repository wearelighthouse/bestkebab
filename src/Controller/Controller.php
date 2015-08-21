<?php

namespace BestKebab\Controller;

if (!defined('ABSPATH')) {
    exit;
}

use BestKebab\Utility\Inflector as Inflector;

use WP_POST;

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
     * @param \WP_POST $post The post object
     * @return void
     */
    public function beforeRender(WP_POST $post)
    {
        $this->{$this->_postType}->prepareEntity($post);
    }
}
