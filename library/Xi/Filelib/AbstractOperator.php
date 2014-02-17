<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib;

use Xi\Filelib\FileLibrary;
use Xi\Filelib\Storage\Storage;
use Xi\Filelib\Backend\Backend;
use Xi\Filelib\InvalidArgumentException;
use Pekkis\Queue\Queue;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Rhumsaa\Uuid\Uuid;
use Xi\Filelib\Command\Command;
use Xi\Filelib\Command\ExecutionStrategy\ExecutionStrategy;
use Xi\Filelib\Command\ExecutionStrategy\AsynchronousExecutionStrategy;
use Xi\Filelib\Command\ExecutionStrategy\SynchronousExecutionStrategy;

/**
 * Abstract convenience class for operators
 *
 * @author pekkis
 *
 */
abstract class AbstractOperator
{
    /**
     * Filelib reference
     *
     * @var FileLibrary
     */
    protected $filelib;

    /**
     * Commands and their default strategies
     *
     * @var array
     */
    protected $commandStrategies = array();

    public function __construct(FileLibrary $filelib)
    {
        $this->filelib = $filelib;
    }

    /**
     * Returns backend
     *
     * @return Backend
     */
    public function getBackend()
    {
        return $this->getFilelib()->getBackend();
    }

    /**
     * Returns storage
     *
     * @return Storage
     */
    public function getStorage()
    {
        return $this->getFilelib()->getStorage();
    }

    /**
     * Returns filelib
     *
     * @return FileLibrary
     */
    public function getFilelib()
    {
        return $this->filelib;
    }

    /**
     * Returns Event dispatcher
     *
     * @return EventDispatcherInterface
     */
    public function getEventDispatcher()
    {
        return $this->getFilelib()->getEventDispatcher();
    }

    /**
     * Returns queue
     *
     * @return Queue
     */
    public function getQueue()
    {
        return $this->getFilelib()->getQueue();
    }

    private function assertCommandExists($command)
    {
        if (!isset($this->commandStrategies[$command])) {
            throw new InvalidArgumentException("Command '{$command}' is not supported");
        }
    }

    private function assertStrategyExists($strategy)
    {
        if (!in_array(
            $strategy,
            array(
                ExecutionStrategy::STRATEGY_ASYNCHRONOUS,
                ExecutionStrategy::STRATEGY_SYNCHRONOUS
            )
        )) {
            throw new InvalidArgumentException("Invalid execution strategy '{$strategy}'");
        }
    }

    public function getCommandStrategy($command)
    {
        $this->assertCommandExists($command);
        return $this->commandStrategies[$command];
    }

    /**
     * @param $command
     * @return ExecutionStrategy
     */
    private function createCommandStrategy($command)
    {
        if ($this->getCommandStrategy($command) == ExecutionStrategy::STRATEGY_ASYNCHRONOUS) {
            return new AsynchronousExecutionStrategy($this->getQueue());
        } else {
            return new SynchronousExecutionStrategy();
        }
    }


    public function setCommandStrategy($command, $strategy)
    {
        $this->assertCommandExists($command);
        $this->assertStrategyExists($strategy);
        $this->commandStrategies[$command] = $strategy;

        return $this;
    }

    public function createCommand($commandClass, array $args = array())
    {
        $reflClass = new \ReflectionClass($commandClass);
        $ret = $reflClass->newInstanceArgs($args);
        $ret->attachTo($this->filelib);

        return $ret;
    }

    public function executeOrQueue(Command $commandObj, $commandName)
    {
        return $this->createCommandStrategy($commandName)->execute($commandObj);
    }
}
