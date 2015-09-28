<?php

namespace BestKebab\Test\TestCase\Controller;

use BestKebab\Utility\ControllerContainer;

use WP_UnitTestCase;

class ControllerTest extends WP_UnitTestCase
{

    private $_controller;

    /**
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $this->_controller = ControllerContainer::instance()->getController('test');
    }

    /**
     * Tests the correct post type is automatically loaded
     * when the controller is instantiated
     */
    public function testAutoLoadPostType()
    {
        $this->assertEquals('App\PostType\TestsPostType', get_class($this->_controller->Tests));
    }

    /**
     * Tests that beforeFilter is run correctly
     */
    public function testBeforeFilter()
    {
        global $wp_query;
        
        $wp_query->set('post_type', 'test');
        $wp_query->parse_query_vars();
        
        do_action('pre_get_posts', $wp_query);
        $this->assertEquals($wp_query, $this->_controller->request->wpQuery);

        wp_reset_query();
        $wp_query->set('post_type', 'post');
        $wp_query->parse_query_vars();

        do_action('pre_get_posts', $wp_query);
        $this->assertNotEquals($wp_query, $this->_controller->request->wpQuery);
    }

    /**
     * Tests beforeRender is run correctly
     */
    // public function testBeforeRender()
    // {
    //     $postId = $this->factory->post->create([
    //         'post_type' => 'test'
    //     ]);

    //     $this
    //         ->_controller
    //         ->Tests
    //             ->addPostMeta($postId, 'test_field', 'Test Value');
    // }
}
