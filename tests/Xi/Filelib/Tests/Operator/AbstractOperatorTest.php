<?php

namespace Xi\Filelib\Tests;

use Xi\Filelib\EnqueueableCommand;
use Xi\Filelib\AbstractOperator;
use InvalidArgumentException;

class AbstractOperatorTest extends TestCase
{
    private $op;
    private $configuration;

    public function setUp()
    {
        $this->configuration = $this->getConfigurationWithMockedObjects();
        $this->op = $this->getMockedOperator();
    }

    /**
     * @test
     */
    public function classShouldExist()
    {
        $this->assertTrue(class_exists('Xi\Filelib\AbstractOperator'));
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    public function getMockedOperator($methods = array())
    {

        $op = $this
            ->getMockBuilder('Xi\Filelib\AbstractOperator')
            ->setMethods($methods)
            ->setConstructorArgs(array($this->configuration))
            ->getMockForAbstractClass();

        return $op;
    }


    /**
     * @test
     */
    public function gettersShouldWork()
    {
        $configuration = $this->configuration;
        $op = $this->getMockedOperator();

        $this->assertSame($configuration->getBackend(), $op->getBackend());
        $this->assertSame($configuration->getAcl(), $op->getAcl());
        $this->assertSame($configuration->getStorage(), $op->getStorage());
        $this->assertSame($configuration->getPublisher(), $op->getPublisher());
        $this->assertSame($configuration->getEventDispatcher(), $op->getEventDispatcher());
        $this->assertSame($configuration->getQueue(), $op->getQueue());
    }

    /**
     * @test
     * @expectedException InvalidArgumentException
     */
    public function gettingInvalidCommandShouldThrowException()
    {
        $op = $this->getMockedOperator();
        $op->getCommandStrategy('lussenhof');
    }

    /**
     * @test
     * @expectedException InvalidArgumentException
     */
    public function settingInvalidCommandShouldThrowException()
    {
        $op = $this->getMockedOperator();
        $op->setCommandStrategy('lussenhof', EnqueueableCommand::STRATEGY_ASYNCHRONOUS);
    }

    /**
     *
     * @test
     */
    public function generateUuidShouldGenerateUuid()
    {
        $op = $this->getMockedOperator();
        $uuid = $op->generateUuid();
        $this->assertRegExp("/^\w{8}-\w{4}-\w{4}-\w{4}-\w{12}$/", $uuid);
    }

    /**
     * @test
     */
    public function executeOrQueueShouldEnqueueWithAsynchronousStrategy()
    {
        $op = $this->getMockedOperator(array('getCommandStrategy'));

        $command = $this->getMockBuilder('Xi\Filelib\EnqueueableCommand')
                        ->disableOriginalConstructor()
                        ->getMock();

        $queue = $this->configuration->getQueue();

        $op->expects($this->once())->method('getCommandStrategy')
           ->with($this->equalTo('tussi'))
           ->will($this->returnValue(EnqueueableCommand::STRATEGY_ASYNCHRONOUS));

        $queue->expects($this->once())->method('enqueue')
              ->with($this->isInstanceOf('Xi\Filelib\Command'))
              ->will($this->returnValue('tussi-id'));

        $ret = $op->executeOrQueue($command, 'tussi', array());

        $this->assertEquals('tussi-id', $ret);

    }

    /**
     * @test
     */
    public function executeOrQueueShouldExecuteWithSynchronousStrategy()
    {

        $op = $this->getMockedOperator(array('getCommandStrategy'));

        $command = $this->getMockBuilder('Xi\Filelib\EnqueueableCommand')
                        ->disableOriginalConstructor()
                        ->getMock();

        $queue = $this->configuration->getQueue();

        $op->expects($this->once())->method('getCommandStrategy')
           ->with($this->equalTo('tussi'))
           ->will($this->returnValue(EnqueueableCommand::STRATEGY_SYNCHRONOUS));

        $queue->expects($this->never())->method('enqueue');

        $command->expects($this->once())->method('execute')
                ->will($this->returnValue('executed!!!'));

        $ret = $op->executeOrQueue($command, 'tussi', array());

        $this->assertEquals('executed!!!', $ret);

    }

    public function provideCallbackStrategies()
    {
        return array(
            array('asynchronous', EnqueueableCommand::STRATEGY_ASYNCHRONOUS),
            array('synchronous', EnqueueableCommand::STRATEGY_SYNCHRONOUS),
        );
    }

    /**
     * @test
     * @dataProvider provideCallbackStrategies
     */
    public function executeOrQueueShouldUtilizeCallbacks($expectedValue, $strategy)
    {
        $callbacks = array(
            EnqueueableCommand::STRATEGY_ASYNCHRONOUS => function(AbstractOperator $op, $ret) {
                return 'asynchronous';
            },
            EnqueueableCommand::STRATEGY_SYNCHRONOUS => function(AbstractOperator $op, $ret) {
                return 'synchronous';
            }
        );

        $op = $this->getMockedOperator(array('getCommandStrategy'));

        $command = $this->getMockBuilder('Xi\Filelib\EnqueueableCommand')
            ->disableOriginalConstructor()
            ->getMock();

        $queue = $this->configuration->getQueue();

        $op->expects($this->once())->method('getCommandStrategy')
            ->with($this->equalTo('tussi'))
            ->will($this->returnValue($strategy));

        $command->expects($this->any())->method('execute')
            ->will($this->returnValue('originalValue'));

        $queue->expects($this->any())->method('enqueue')
            ->will($this->returnValue('originalValue'));

        $ret = $op->executeOrQueue($command, 'tussi', $callbacks);

        $this->assertEquals($expectedValue, $ret);

    }

    /**
     * @test
     */
    public function createCommandCreatesCommandObject()
    {
        $mockClass = $this
            ->getMockClass(
                'Xi\Filelib\File\Command\AbstractFileCommand',
                array('execute', 'serialize', 'unserialize')
            );

        $op = $this->getMockedOperator();

        $fileop = $this->getMockBuilder('Xi\Filelib\File\FileOperator')->disableOriginalConstructor()->getMock();

        $command = $op->createCommand(
            $mockClass,
            array($fileop)
        );

        $this->assertInstanceOf($mockClass, $command);
    }

}
