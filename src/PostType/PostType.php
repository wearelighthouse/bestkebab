<?php

namespace BestKebab\PostType;

if (!defined('ABSPATH')) {
    exit;
}

use BestKebab\PostType\Taxonomy\Taxonomy;
use BestKebab\Utility\Inflector;

abstract class PostType
{
    private $_model = '';
    private $_name = '';

    private $_metaBoxes = [];
    private $_taxonomies = [];

    /**
     * @return void
     */
    public function __construct()
    {
        $this->_model = Inflector::batch(basename(get_class($this), 'PostType'), [
            'classify',
            'singularize'
        ]);
        $this->_name = strtolower($this->_model);
        
        if (!in_array($this->_name, get_post_types())) {
            $this->_registerPostType();
        }

        add_action('cmb2_init', [$this, 'initialise']);
    }

    /**
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        if (!isset($this->{'_' . $name})) {
            trigger_error('Call to undefined method ' . get_class($this) . '::' . $name . '()', E_USER_ERROR);
        }
        
        return $this->{'_' . $name};
    }

    /**
     * @return void
     */
    abstract public function initialise();

    /**
     * Adds a field to a meta box
     *
     * @param string $id The id for this field
     * @param string $type The type of the field
     * @param string $name The name of the field
     * @param string $metaBoxId The id of the meta box to add a field to
     * @param array $options Any extra options
     * @return void
     */
    public function addField($id, $type, $name, $metaBoxId, $options = [])
    {
        $this->_metaBoxes[$metaBoxId]->add_field([
            'id' => '_' . $this->_name . '_' . $id,
            'name' => __($name),
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
        $metaBox = new_cmb2_box([
            'id' => '_' . $this->_name . '_' . $id . '_meta_box',
            'title' => __($title),
            'object_types' => [
                $this->_name
            ]
        ] + $options);
        $this->_metaBoxes[$id] = $metaBox;
    }

    /**
     * Adds support to this post type
     *
     * @param string|array $support The support to add
     * @return void
     */
    public function addSupport($support)
    {
        add_post_type_support($this->_name, $support);
    }

    /**
     * Adds a taxonomy to this post type
     *
     * @param string $name The undercored name of the taxonomy
     * @param string $type Either "category" or "tag"
     * @return void
     */
    public function addTaxonomy($name, $type, $options = [])
    {
        $taxonomy = new Taxonomy($name, $type, $this->_name, $options);
        $this->_taxonomies[$name] = $taxonomy;
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
        $plural = Inflector::pluralize($this->_model);
        register_post_type($this->_name, [
            'labels' => [
                'name' => __($plural),
                'singular_name' => __($this->_model),
                'all_items' => __('All ' . $plural),
                'add_new' => __('Add New'),
                'add_new_item' => __('Add New ' . $this->_model),
                'edit_item' => __('Edit ' . $this->_model),
                'new_item' => __('New ' . $this->_model),
                'view_item' => __('View ' . $this->_model),
                'search_items' => __('Search ' . $plural),
                'not_found' => __('Nothing found in the Database.'),
                'not_found_in_trash' => __('Nothing found in Trash'),
                'parent_item_colon' => __('Parent ' . $this->_model . ':')
            ],
            'public' => true,
            'publicly_queryable' => true,
            'exclude_from_search' => false,
            'show_ui' => true,
            'query_var' => true,
            'menu_position' => 4,
            'rewrite' => [
                'slug' => strtolower($plural),
                'with_front' => false
            ],
            'has_archive' => true,
            'capability_type' => 'post',
            'hierarchical' => false
        ]);
        flush_rewrite_rules();
    }
}
