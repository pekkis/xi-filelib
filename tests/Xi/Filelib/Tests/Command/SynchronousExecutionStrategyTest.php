<?php

namespace Xi\Filelib\Tests;

use Xi\Filelib\Command\SynchronousExecutionStrategy;

class SynchronousExecutionStrategyTest extends \Xi\Filelib\Tests\TestCase
{
    /**
     * @test
     */
    public function shouldExist()
    {
        $this->assertTrue(class_exists('Xi\Filelib\Command\SynchronousExecutionStrategy'));
        $this->assertContains(
            'Xi\Filelib\Command\ExecutionStrategy',
            class_implements('Xi\Filelib\Command\SynchronousExecutionStrategy')
        );
    }

    /**
     * @test
     */
    public function executeShouldExecuteCommand()
    {
        $command = $this->getMockedCommand();
        $command
            ->expects($this->once())
            ->method('execute')
            ->will($this->returnValue('executore'));

        $strategy = new SynchronousExecutionStrategy();
        $ret = $strategy->execute($command);

        $this->assertSame('executore', $ret);
    }
}
