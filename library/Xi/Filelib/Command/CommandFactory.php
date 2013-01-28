<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Command;

use Xi\Filelib\Queue\Queue;

class CommandFactory
{
    /**
     * @var Queue
     */
    private $queue;

    /**
     * @var Commander
     */
    private $commander;

    /**
     * @var array
     */
    private $commandStrategies = array();

    /**
     * @param Queue $queue
     * @param Commander $commander
     */
    public function __construct(Queue $queue, Commander $commander)
    {
        $this->queue = $queue;
        $this->commander = $commander;
        $this->commandStrategies = $commander->getCommandStrategies();
    }

    private function assertCommandExists($command)
    {
        if (!isset($this->commandStrategies[$command])) {
            throw new \InvalidArgumentException("Command '{$command}' is not supported");
        }
    }

    private function assertStrategyExists($strategy)
    {
        if (!in_array($strategy, array(EnqueueableCommand::STRATEGY_ASYNCHRONOUS, EnqueueableCommand::STRATEGY_SYNCHRONOUS))) {
            throw new \InvalidArgumentException("Invalid command strategy '{$strategy}'");
        }
    }

    public function getCommandStrategy($command)
    {
        $this->assertCommandExists($command);

        return $this->commandStrategies[$command];
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
        return $reflClass->newInstanceArgs($args);
    }

    public function executeOrQueue(EnqueueableCommand $commandObj, $commandName, array $callbacks = array())
    {
        $strategy = $this->getCommandStrategy($commandName);
        if ($strategy == EnqueueableCommand::STRATEGY_ASYNCHRONOUS) {
            $ret = $this->queue->enqueue($commandObj);
        } else {
            $ret = $commandObj->execute();
        }

        return $this->executeOrQueueHandleCallbacks($strategy, $callbacks, $ret);
    }

    private function executeOrQueueHandleCallbacks($strategy, $callbacks, $ret)
    {
        if (isset($callbacks[$strategy])) {
            return $callbacks[$strategy]($this->commander, $ret);
        }

        return $ret;
    }



}
