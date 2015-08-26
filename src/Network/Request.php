<?php

namespace BestKebab\Network;

if (!defined('ABSPATH')) {
    exit;
}

use WP_Query;

class Request
{

    public $data = [];
    public $query = [];
    public $wpQuery = null;

    /**
     * @return void
     */
    public function __construct(WP_Query $wpQuery)
    {
        $this->data = $_POST;
        $this->query = $_GET;
        $this->wpQuery = $wpQuery;
    }
}
