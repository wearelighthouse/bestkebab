<?php

namespace BestKebab\Controller;

if (!defined('ABSPATH')) {
    exit;
}

use BestKebab\Utility\Inflector as Inflector;

class Controller
{
    protected $_postType = '';

    /**
     * @return void
     */
    public function __construct()
    {
        $postType = $this->_postType = Inflector::postTypify(get_class($this));
        
        $PostType = 'App\PostType\\' . $this->_postType;
        $this->$postType = new $PostType();
    }

    /**
     * Display archive part for post type
     *
     * @return void
     */
    public function archive()
    {
        include 'App/Part/' . $this->_postType . '/archive.php';
    }

    /**
     * Display single part for post type
     *
     * @return void
     */
    public function single()
    {
        include 'App/Part/' . $this->_postType . '/single.php';
    }
}
