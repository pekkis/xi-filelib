<?php

namespace Xi\Filelib\Tests\Command;

use Xi\Filelib\Command\EnqueueableCommand;
use Xi\Filelib\Command\CommandFactory;
use Xi\Filelib\Command\Commander;
use InvalidArgumentException;
use Xi\Filelib\Command\CommandDefinition;

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

    /**
     * @var CommandDefinition
     */
    private $tussi;

    /**
     * @var CommandDefinition
     */
    private $lussi;

    public function setUp()
    {
        $this->tussi = new CommandDefinition('tussi', 'ManateeTussi', CommandFactory::STRATEGY_ASYNCHRONOUS);
        $this->lussi = new CommandDefinition('lussi', 'ManateeLussi', CommandFactory::STRATEGY_SYNCHRONOUS);

        $this->commander = $this->getMock('Xi\Filelib\Command\Commander');
        $this->commander
            ->expects($this->any())
            ->method('getCommandDefinitions')
            ->will(
                $this->returnValue(
                    array(
                        $this->tussi,
                        $this->lussi
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
        $this->assertAttributeSame(
            array(
                'tussi' => $this->tussi,
                'lussi' => $this->lussi,
            ),
            'commandDefinitions',
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
        $this->commandFactory->setCommandStrategy('lussenhof', CommandFactory::STRATEGY_ASYNCHRONOUS);
    }

    /**
     * @test
     */
    public function settingStrategyShouldWork()
    {
        $this->assertEquals(
            CommandFactory::STRATEGY_ASYNCHRONOUS,
            $this->commandFactory->getCommandStrategy('tussi')
        );

        $this->assertSame(
            $this->commandFactory,
            $this->commandFactory->setCommandStrategy('tussi', CommandFactory::STRATEGY_SYNCHRONOUS)
        );

        $this->assertEquals(
            CommandFactory::STRATEGY_SYNCHRONOUS,
            $this->commandFactory->getCommandStrategy('tussi')
        );
    }

    /**
     * @test
     */
    public function createCommandCreatesCommandObject()
    {
        if (!class_exists('ManateeLussi')) {
            $mockClass = $this->getMockClass(
                'Xi\Filelib\Command\Command',
                array(),
                array(),
                'ManateeLussi'
            );
        }

        if (!class_exists('ManateeTussi')) {
            $mockClass = $this->getMockClass(
                'Xi\Filelib\Command\Command',
                array(),
                array(),
                'ManateeTussi'
            );
        }


        $executable = $this->commandFactory->createCommand(
            'tussi'
        );
        $this->assertInstanceOf('Xi\Filelib\Command\Executable', $executable);

        $this->assertAttributeInstanceOf(
            'Xi\Filelib\Command\AsynchronousExecutionStrategy',
            'strategy',
            $executable
        );

        $executable = $this->commandFactory->createCommand(
            'lussi'
        );
        $this->assertInstanceOf('Xi\Filelib\Command\Executable', $executable);

        $this->assertAttributeInstanceOf(
            'Xi\Filelib\Command\SynchronousExecutionStrategy',
            'strategy',
            $executable
        );

    }
}
