<?php

namespace BestKebab\PostType;

if (!defined('ABSPATH')) {
    exit;
}

use BestKebab\Utility\Inflector as Inflector;

abstract class PostType
{
    protected $_class = '';
    protected $_name = '';
    protected $_plural = '';

    protected $_metaBoxes = [];

    /**
     * @return void
     */
    public function __construct()
    {
        $this->_class = Inflector::classify(get_class($this));
        $this->_name = strtolower($this->_class);
        $this->_plural = Inflector::batch($this->_class, ['humanize', 'pluralize']);
        
        if (!in_array($this->_name, get_post_types())) {
            $this->_registerPostType();
        }

        add_action('cmb2_init', [$this, 'initialise']);
    }

    /**
     * @return void
     */
    abstract public function initialise();

    /**
     * Adds a field to a meta box
     *
     * @param string $metaBoxTitle The title of the meta box
     * @return void
     */
    public function addField($id, $type, $name, $metaBoxId, $options = [])
    {
        $prefix = '_' . $this->_name . '_';
        $this->_metaBoxes[$metaBoxId]->add_field([
            'name' => __($name),
            'id' => $prefix . $id,
            'type' => $type
        ] + $options);
    }

    /**
     * Adds a meta box to the meta boxes array
     *
     * @param string $id A unique identifier for the meta box
     * @param string $title The title of the meta box
     * @param array $options The extra options array
     * @return void
     */
    public function addMetaBox($id, $title, $options = [])
    {
        $metaBox = \new_cmb2_box([
            'id' => $id,
            'title' => __($title),
            'object_types' => [
                $this->_name
            ]
        ] + $options);
        $this->_metaBoxes[$id] = $metaBox;
    }

    /**
     * Apply options to the global WP_Query object
     *
     * @param array $options The options array for WP_Query
     * @return void
     */
    public function query(array $options)
    {
        global $wp_query;
        $wp_query = new \WP_Query($options);
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
                'edit_item' => __('Edit ' . $this->_class),
                'new_item' => __('New ' . $this->_class),
                'view_item' => __('View ' . $this->_class),
                'search_items' => __('Search ' . $this->_plural),
                'not_found' => __('Nothing found in the Database.'),
                'not_found_in_trash' => __('Nothing found in Trash'),
                'parent_item_colon' => __('Parent ' . $this->_class . ':')
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
