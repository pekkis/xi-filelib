<?php

namespace Xi\Filelib\Tests;

use Xi\Filelib\Command\AsynchronousExecutionStrategy;

class AynchronousExecutionStrategyTest extends \Xi\Filelib\Tests\TestCase
{
    /**
     * @test
     */
    public function shouldExist()
    {
        $this->assertTrue(class_exists('Xi\Filelib\Command\AsynchronousExecutionStrategy'));
        $this->assertContains(
            'Xi\Filelib\Command\ExecutionStrategy',
            class_implements('Xi\Filelib\Command\AsynchronousExecutionStrategy')
        );
    }

    /**
     * @test
     */
    public function executeShouldExecuteCommand()
    {
        $command = $this->getMockedCommand();
        $command
            ->expects($this->never())
            ->method('execute');

        $queue = $this->getMockedQueue();
        $queue
            ->expects($this->once())
            ->method('enqueue')
            ->with($command)
            ->will($this->returnValue('queue vadis?'));


        $strategy = new ASynchronousExecutionStrategy($queue);
        $ret = $strategy->execute($command);

        $this->assertSame('queue vadis?', $ret);
    }
}
