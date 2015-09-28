<?php

namespace BestKebab\Test\TestCase;

use BestKebab\Utility\ControllerContainer;

use WP_UnitTestCase;

class BestKebabTest extends WP_UnitTestCase
{

    /**
     * Tests controllers are correctly loaded
     */
    public function testBestKebabInit()
    {
        $controllers = ControllerContainer::instance()->controllers();
        $this->assertEquals(1, count($controllers));
        $this->assertEquals('App\Controller\TestsController', get_class(current($controllers)));
    }
}
