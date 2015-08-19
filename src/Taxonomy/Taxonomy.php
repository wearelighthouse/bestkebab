<?php

namespace BestKebab\Taxonomy;

if (!defined('ABSPATH')) {
    exit;
}

use BestKebab\Utility\Inflector as Inflector;

abstract class Taxonomy
{
    protected $_name = '';
    protected $_plural = '';
    protected $_postType = '';
    protected $_singular = '';

    /**
     * @return void
     */
    public function __construct($name, $postType)
    {
        $this->_name = $name;
        $this->_postType = $postType;
        $this->_singular = Inflector::humanize($this->_name);
        $this->_plural = Inflector::pluralize($this->_singular);
        
        if (!in_array($this->_name, get_taxonomies())) {
            $this->_registerTaxonomy();
        }
    }

    /**
     * @return void
     */
    private function _registerPostType()
    {
        register_taxonomy($this->_name, [$this->_postType], [
            'labels' => [
                'name' => __($this->_plural),
                'singular_name' => __($this->_singular),
                'all_items' => __('All ' . $this->_plural),
                'edit_item' => __('Edit ' . $this->_singular),
                'view_item' => __('View ' . $this->_singular),
                'update_item' => __('Update ' . $this->_singular),
                'add_new_item' => __('Add New ' . $this->_singular),
                'new_item_name' => __('New ' . $this->_singular . ' Name'),
                'parent_item' => __('Parent ' . $this->_singular),
                'parent_item_colon' => __('Parent ' . $this->_singular . ':'),
                'search_items' => __('Search ' . $this->_plural),
                'popular_items' => __('Popular' . $this->_plural),
                'separate_items_with_commas' => __('Separate ' . $this->_plural . ' with commas'),
                'add_or_remove_items' => __('Add or remove ' . $this->_plural),
                'choose_from_most_used' => __('Choose from the most used ' . $this->_plural),
                'not_found' => __('No ' . $this->_plural . ' found')
            ],
            'public' => true,
            'show_admin_column' => true,
            'hierarchical' => $this->_isHierarchical,
            'query_var' => true,
            'rewrite' => [
                'slug' => Inflector::slugify($this->_plural)
            ],
        ]);
        flush_rewrite_rules();
    }
}
