<?php

namespace Xi\Filelib\Tests\Command;

class CommandeerableTest extends \Xi\Filelib\Tests\TestCase
{
    /**
     * @test
     */
    public function interfaceShouldExist()
    {
        $this->assertTrue(interface_exists('Xi\Filelib\Command\Commander'));
    }
}
