<?php

namespace Xi\Filelib\Tests;

class AbstractCommandTest extends \Xi\Filelib\Tests\TestCase
{

    /**
     * @test
     */
    public function interfaceShouldExist()
    {
        $this->assertTrue(class_exists('Xi\Filelib\Command\AbstractCommand'));
        $this->assertContains('Xi\Filelib\Command\Command', class_implements('Xi\Filelib\Command\AbstractCommand'));
    }

    /**
     * @test
     */
    public function classShouldInitializeCorrectly()
    {
        $uuid = 'tussen-hof';

        $command = $this->getMockBuilder('Xi\Filelib\Command\AbstractCommand')
                        ->setMethods(array('execute'))
                        ->setConstructorArgs(array($uuid))
                        ->getMockForAbstractClass();

        $this->assertEquals($uuid, $command->getEnqueueReturnValue());
        $this->assertEquals($uuid, $command->getUuid());
    }
}
