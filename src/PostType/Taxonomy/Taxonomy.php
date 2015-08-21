<?php

namespace BestKebab\PostType\Taxonomy;

if (!defined('ABSPATH')) {
    exit;
}

use BestKebab\Utility\Inflector as Inflector;

class Taxonomy
{
    private $_name = '';
    private $_plural = '';
    private $_postType = '';
    private $_singular = '';

    private $_isHierarchical;

    /**
     * @return void
     */
    public function __construct($name, $type, $postType, array $options)
    {
        $this->_name = $name;
        $this->_postType = $postType;
        $this->_singular = Inflector::humanize($this->_name);
        $this->_plural = Inflector::pluralize($this->_singular);
        
        $this->_isHierarchical = $type === 'category';

        if (!in_array($this->_name, get_taxonomies())) {
            $this->_registerTaxonomy($options);
        }
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
    private function _registerTaxonomy(array $options)
    {
        $defaults = [
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
            'hierarchical' => $this->_isHierarchical,
            'query_var' => true,
            'rewrite' => [
                'slug' => Inflector::slugify($this->_plural)
            ]
        ];
        register_taxonomy($this->_name, [$this->_postType], wp_parse_args($options, $defaults));
        flush_rewrite_rules();
    }
}
