<?php

namespace App\PostType;

use BestKebab\PostType\PostType;

class TestsPostType extends PostType
{

    /**
     * @return void
     */
    public function initialise()
    {
        $this->addMetaBox('testBox', 'Test Box');
        $this->addField('test_field', 'text', 'Test Field', 'testBox');
        parent::initialise();
    }
}
