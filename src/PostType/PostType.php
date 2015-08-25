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
    private $_prefix = '';

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
        $this->_prefix = '_' . $this->_name . '_';
        
        if (!in_array($this->_name, get_post_types())) {
            $this->_registerPostType();
        }

        add_action('cmb2_init', [$this, 'initialise']);
        add_action('save_post_' . $this->_name, [$this, 'afterSave'], 10, 3);
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
        if (substr($type, 0, strlen('taxonomy_')) === 'taxonomy_') {
            $type = str_replace('taxonomy_', '', $type);

            $terms = get_terms($options['taxonomy'], [
                'hide_empty' => false
            ]);

            $options['options'] = [];

            foreach ($terms as $term) {
                $options['options'][$term->slug] = __($term->name);
            }

            unset($options['taxonomy']);
        }

        $this->_metaBoxes[$metaBoxId]->add_field([
            'id' => $this->_prefix . $id,
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
            'id' => $this->_prefix . $id . '_meta_box',
            'title' => __($title),
            'object_types' => [
                $this->_name
            ]
        ] + $options);
        $this->_metaBoxes[$id] = $metaBox;
    }

    /**
     * Runs after post type is saved
     * @param int $id The post id
     * @param \WP_POST $post The post object
     * @param bool $isUpdate If this is a create or update
     * @return void
     */
    public function afterSave($id, WP_POST $post, $isUpdate)
    {
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
     * Finds or creates unique post meta
     *
     * @param int $postId The post id
     * @param string $metaKey The meta key
     * @param mixed $metaValue The meta value
     * @return mixed The value of $metaValue
     */
    public function findOrCreatePostMeta($postId, $metaKey, $metaValue)
    {
        $postMeta = get_post_meta($postId, $metaKey, true);

        if ($postMeta) {
            return $postMeta;
        }

        add_post_meta($postId, $this->prefix() . $metaKey, $metaValue, true);
        return $metaValue;
    }

    /**
     * @return void
     */
    abstract public function initialise();

    /**
     * Prepares an entity
     *
     * @param \WP_POST $post The post object
     * @return void
     */
    public function prepareEntity(WP_POST $post)
    {
        $post->prefix = $this->_prefix;

        if (post_type_supports($this->_name, 'thumbnail') && has_post_thumbnail($post->ID)) {
            $post->thumbnail_id = get_post_thumbnail_id($post->ID);
        }

        foreach ($this->_metaBoxes as $metaBox) {
            foreach ($metaBox->prop('fields') as $field) {
                $postMeta = get_post_meta($post->ID, $field['id'], true);

                if (!empty($postMeta)) {
                    $post->{str_replace($this->_prefix, '', $field['id'])} = $postMeta;
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
