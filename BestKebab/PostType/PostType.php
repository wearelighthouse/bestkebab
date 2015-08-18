<?php

namespace BestKebab\PostType;

if (!defined('ABSPATH')) {
    exit;
}

use BestKebab\Utility\Inflector as Inflector;

class PostType
{
    protected $_class = '';
    protected $_name = '';
    protected $_plural = '';

    /**
     * @return void
     */
    public function __construct()
    {
        $this->_class = Inflector::classify(get_class($this));
        $this->_name = strtolower($this->_class);
        $this->_plural = ucfirst(Inflector::pluralise($this->_class));
        
        if (!in_array($this->_name, get_post_types())) {
            $this->_registerPostType();
        }
    }

    /**
     * Apply args array to the global WP_Query object
     *
     * @param array $args The arguments array for WP_Query
     * @return void
     */
    public function query(array $args)
    {
        global $wp_query;
        $wp_query = new \WP_Query($args);
    }

    /**
     * @return void
     */
    private function _registerPostType()
    {
        register_post_type($this->_name, [
            'labels' => [
                'name' => __($this->_plural),
                'singular_name' => __($this->_class),
                'all_items' => __('All ' . $this->_plural),
                'add_new' => __('Add New'),
                'add_new_item' => __('Add New ' . $this->_class),
                'edit' => __('Edit'),
                'edit_item' => __('Edit ' . $this->_class),
                'new_item' => __('New ' . $this->_class),
                'view_item' => __('View ' . $this->_class),
                'search_items' => __('Search ' . $this->_plural),
                'not_found' => __('Nothing found in the Database.'),
                'not_found_in_trash' => __('Nothing found in Trash')
            ],
            'public' => true,
            'publicly_queryable' => true,
            'exclude_from_search' => false,
            'show_ui' => true,
            'query_var' => true,
            'menu_position' => 4,
            'rewrite' => [
                'slug' => strtolower($this->_plural),
                'with_front' => false
            ],
            'has_archive' => true,
            'capability_type' => 'post',
            'hierarchical' => false,
            'supports' => [
                'title',
                'editor',
                'thumbnail'
            ]
        ]);

        flush_rewrite_rules();
    }
}
