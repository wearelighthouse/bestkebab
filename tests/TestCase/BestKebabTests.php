<?php

namespace BestKebab\Test\TestCase;

use BestKebab\Utility\ControllerContainer;

use WP_UnitTestCase;

class BestKebabTests extends WP_UnitTestCase
{

    public function testBestKebabInit()
    {
        $this->assertEquals(count(ControllerContainer::instance()->controllers()), 1);
        $this->assertEquals(get_class(current(ControllerContainer::instance()->controllers())), 'App\Controller\TestsController');
    }
}
