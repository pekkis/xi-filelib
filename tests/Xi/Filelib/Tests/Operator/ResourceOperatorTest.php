<?php

namespace Xi\Filelib\Tests\Operator;

class ResourceOperatorTest extends \Xi\Filelib\Tests\TestCase
{
    /**
     * @test
     */
    public function classShouldExist()
    {
        $this->assertTrue(class_exists('Xi\Filelib\Operator\ResourceOperator'));
    }
}
