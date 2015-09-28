<?php

namespace BestKebab\Test\TestCase\PostType;

use WP_UnitTestCase;

class PostTypeTest extends WP_UnitTestCase
{

    private $_postType;

    /**
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $this->_postType = ControllerContainer::instance()->getController('test')->Tests;
    }
}
