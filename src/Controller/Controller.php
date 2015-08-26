<?php

namespace BestKebab\Controller;

if (!defined('ABSPATH')) {
    exit;
}

use BestKebab\Network\Request;
use BestKebab\Utility\Inflector;

use WP_POST;
use WP_Query;

class Controller
{

    private $_postType = '';
    protected $request = null;

    /**
     * @return void
     */
    public function __construct()
    {
        $this->_postType = Inflector::classify(basename(get_class($this), 'Controller'));
        $PostType = SITENAME . '\PostType\\' . $this->_postType . 'PostType';
        $this->{$this->_postType} = new $PostType();

        $this->request = new Request();
        $this->initialise();

        add_action('the_post', [$this, 'beforeRender'], 10, 1);
        add_action('pre_get_posts', function (WP_Query $query) {
            if (isset($query->query['post_type']) && $query->query['post_type'] === $this->{$this->_postType}->name()) {
                $this->beforeFilter($query);
            }
        }, 10, 1);
    }

    /**
     * @return void
     */
    public function initialise()
    {
    }

    /**
     * This is called after the global WP_Query object is setup for
     * this controller
     *
     * @param \WP_Query $query The query object
     * @return void
     */
    public function beforeFilter(WP_Query $query)
    {
        $this->request->wpQuery = $query;

        if ($query->is_archive) {
            $this->archive();
        } elseif ($query->is_single) {
            $this->single();
        }
    }

    /**
     * This is called before each iteration of "The Loop"
     *
     * @param \WP_POST $post The post object
     * @return void
     */
    public function beforeRender(WP_POST $post)
    {
        $this->{$this->_postType}->prepareEntity($post);
    }

    /**
     * @return void
     */
    public function archive()
    {
    }

    /**
     * @return void
     */
    public function single()
    {
    }
}
