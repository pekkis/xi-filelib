<?php

namespace Xi\Filelib\Tests\Command;

use Xi\Filelib\Command\EnqueueableCommand;
use Xi\Filelib\Command\CommandFactory;
use Xi\Filelib\Command\Commander;
use InvalidArgumentException;

class CommandFactoryTest extends \Xi\Filelib\Tests\TestCase
{
    private $commander;

    /**
     * @var CommandFactory
     */
    private $commandFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $queue;

    public function setUp()
    {
        $this->commander = $this->getMock('Xi\Filelib\Command\Commander');
        $this->commander
            ->expects($this->any())
            ->method('getCommands')
            ->will(
                $this->returnValue(
                    array(
                        'tussi' => array(
                            'Manatee\Tussi',
                            EnqueueableCommand::STRATEGY_ASYNCHRONOUS,
                         ),
                        'lussi' => array(
                            'Manatee\Lussi',
                            EnqueueableCommand::STRATEGY_SYNCHRONOUS
                        ),
                    )
                )
            );

        $this->queue = $this->getMockedQueue();
        $this->commandFactory = new CommandFactory($this->queue, $this->commander);
    }

    /**
     * @test
     */
    public function classShouldExist()
    {
        $this->assertTrue(class_exists('Xi\Filelib\Command\CommandFactory'));
    }

    /**
     * @test
     */
    public function shouldInitializeProperly()
    {
        $this->assertAttributeEquals(
            array(
                'tussi' => array(
                    'Manatee\Tussi',
                    EnqueueableCommand::STRATEGY_ASYNCHRONOUS,
                ),
                'lussi' => array(
                    'Manatee\Lussi',
                    EnqueueableCommand::STRATEGY_SYNCHRONOUS
                ),
            ),
            'commands',
            $this->commandFactory
        );
    }

    /**
     * @test
     * @expectedException InvalidArgumentException
     */
    public function gettingInvalidCommandShouldThrowException()
    {
        $this->commandFactory->getCommandStrategy('lussenhof');
    }

    /**
     * @test
     * @expectedException InvalidArgumentException
     */
    public function settingInvalidCommandShouldThrowException()
    {
        $this->commandFactory->setCommandStrategy('lussenhof', EnqueueableCommand::STRATEGY_ASYNCHRONOUS);
    }

    /**
     * @test
     * @expectedException InvalidArgumentException
     */
    public function settingInvalidStrategyShouldThrowException()
    {
        $this->commandFactory->setCommandStrategy('tussi', 'aaaaaaaaargh olen täynnä ilmaa!');
    }

    /**
     * @test
     */
    public function settingStrategyShouldWork()
    {
        $this->assertEquals(
            EnqueueableCommand::STRATEGY_ASYNCHRONOUS,
            $this->commandFactory->getCommandStrategy('tussi')
        );

        $this->assertSame(
            $this->commandFactory,
            $this->commandFactory->setCommandStrategy('tussi', EnqueueableCommand::STRATEGY_SYNCHRONOUS)
        );

        $this->assertEquals(
            EnqueueableCommand::STRATEGY_SYNCHRONOUS,
            $this->commandFactory->getCommandStrategy('tussi')
        );
    }


    /**
     * @test
     */
    public function executeOrQueueShouldEnqueueWithAsynchronousStrategy()
    {
        $command = $this->getMock('Xi\Filelib\Command\EnqueueableCommand');

        $this->queue
            ->expects($this->once())
            ->method('enqueue')
            ->with($command)
            ->will($this->returnValue('tussi-id'));

        $ret = $this->commandFactory->executeOrQueue($command, 'tussi', array());
        $this->assertEquals('tussi-id', $ret);
    }

    /**
     * @test
     */
    public function executeOrQueueShouldExecuteWithSynchronousStrategy()
    {
        $command = $this->getMock('Xi\Filelib\Command\EnqueueableCommand');
        $command
            ->expects($this->once())
            ->method('execute')
            ->will($this->returnValue('executed!!!'));


        $this->queue
            ->expects($this->never())
            ->method('enqueue');

        $ret = $this->commandFactory->executeOrQueue($command, 'lussi', array());
        $this->assertEquals('executed!!!', $ret);
    }

    /**
     * @return array
     */
    public function provideCallbackStrategies()
    {
        return array(
            array('asynchronous', 'tussi'),
            array('synchronous', 'lussi'),
        );
    }

    /**
     * @test
     * @dataProvider provideCallbackStrategies
     */
    public function executeOrQueueShouldUtilizeCallbacks($expectedValue, $commandType)
    {
        $self = $this;

        $callbacks = array(
            EnqueueableCommand::STRATEGY_ASYNCHRONOUS => function (Commander $co, $ret) use ($self) {
                $this->assertEquals('queuedValue', $ret);
                $this->assertSame($self->commander, $co);
                return 'asynchronous';
            },
            EnqueueableCommand::STRATEGY_SYNCHRONOUS => function (Commander $co, $ret) use ($self) {
                $this->assertEquals('originalValue', $ret);
                $this->assertSame($self->commander, $co);
                return 'synchronous';
            }
        );

        $command = $this->getMock('Xi\Filelib\Command\EnqueueableCommand');
        $command
            ->expects($this->any())
            ->method('execute')
            ->will($this->returnValue('originalValue'));

        $this->queue
            ->expects($this->any())
            ->method('enqueue')
            ->will($this->returnValue('queuedValue'));

        $ret = $this->commandFactory->executeOrQueue($command, $commandType, $callbacks);
        $this->assertEquals($expectedValue, $ret);

    }

    /**
     * @test
     */
    public function generateUuidShouldGenerateUuid()
    {
        $uuid = $this->commandFactory->generateUuid();
        $this->assertRegExp("/^\w{8}-\w{4}-\w{4}-\w{4}-\w{12}$/", $uuid);
    }

    /**
     * @test
     */
    public function createCommandCreatesCommandObject()
    {
        $mockClass = $this->getMockClass('Xi\Filelib\Command\Command');

        $command = $this->commandFactory->createCommand(
            $mockClass
        );

        $this->assertInstanceOf($mockClass, $command);
    }
}
