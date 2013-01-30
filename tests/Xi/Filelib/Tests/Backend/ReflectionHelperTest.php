<?php

namespace Xi\Tests\Filelib\Backend;

use Xi\Filelib\Backend\ReflectionHelper;

class ReflectionHelperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ReflectionHelper
     */
    private $helper;

    public function setUp()
    {
        $this->helper = new ReflectionHelper();
    }

    /**
     * @test
     */
    public function returnsAccessibleProperty()
    {
        $reflProp = $this->helper->getAccessibleProperty(__CLASS__, "helper");
        $this->assertInstanceOf("ReflectionProperty", $reflProp);

        $reflProp2 = $this->helper->getAccessibleProperty(__CLASS__, "helper");
        $this->assertSame($reflProp, $reflProp2);
    }
}
