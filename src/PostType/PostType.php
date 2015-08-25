<?php

namespace BestKebab\PostType;

if (!defined('ABSPATH')) {
    exit;
}

use BestKebab\PostType\Taxonomy\Taxonomy;
use BestKebab\Utility\Inflector;

use WP_POST;

abstract class PostType
{
    private $_model = '';
    private $_name = '';

    private $_fields = [];
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
     * Prepares an entity
     *
     * @param \WP_POST $post The post object
     * @return void
     */
    public function prepareEntity(WP_POST $post)
    {
        if (post_type_supports($this->_name, 'thumbnail') && has_post_thumbnail($post->ID)) {
            $post->thumbnail_id = get_post_thumbnail_id($post->ID);
        }

        foreach ($this->_metaBoxes as $metaBox) {
            foreach ($metaBox->prop('fields') as $field) {
                $postMeta = get_post_meta($post->ID, $field['id'], true);

                if (!empty($postMeta)) {
                    $post->{str_replace('_' . $this->_name . '_', '', $field['id'])} = $postMeta;
                }
            }
        }

        foreach ($this->_taxonomies as $taxonomy) {
            $terms = get_the_terms($post->ID, $taxonomy->name());

            if (!empty($terms)) {
                if ($taxonomy->isHierarchical()) {
                    $terms = $this->_formatTerms($terms);
                }

                $post->{Inflector::pluralize($taxonomy->name())} = $terms;
            }
        }
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
     * Formats an array of terms to be correctly associated
     * with its parent
     *
     * @param array $terms The terms array
     * @return array
     */
    private function _formatTerms(array $terms)
    {
        $formated = [];
        foreach ($terms as $term) {
            if ($term->parent === 0) {
                $children = isset($formated[$term->term_id]) ? $formated[$term->term_id]->children : [];
                $formated[$term->term_id] = $term;
                $formated[$term->term_id]->children = $children;
            } else {
                if (!isset($formated[$term->parent])) {
                    $formated[$term->parent] = new \stdClass();
                    $formated[$term->parent]->children = [$term];
                } else {
                    $formated[$term->parent]->children[] = $term;
                }
            }
        }
        return $formated;
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
