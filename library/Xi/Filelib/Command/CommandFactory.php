<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Command;

use Xi\Filelib\Queue\Queue;
use Xi\Filelib\Tool\UuidGenerator\PHPUuidGenerator;

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
    private $commands = array();

    /**
     *
     * @var PhpUuidGenerator
     */
    protected $uuidGenerator;

    /**
     * @param Queue $queue
     * @param Commander $commander
     */
    public function __construct(Queue $queue, Commander $commander)
    {
        $this->queue = $queue;
        $this->commander = $commander;
        $this->commands = $commander->getCommands();
    }

    /**
     * @param string $command
     * @return string
     */
    public function getCommandStrategy($command)
    {
        $this->assertCommandExists($command);

        return $this->commands[$command][1];
    }

    /**
     * @param string $command
     * @param string $strategy
     * @return CommandFactory
     */
    public function setCommandStrategy($command, $strategy)
    {
        $this->assertCommandExists($command);
        $this->assertStrategyExists($strategy);
        $this->commands[$command][1] = $strategy;

        return $this;
    }

    /**
     * @param string $commandClass
     * @param array $args
     * @return Command
     */
    public function createCommand($commandClass, array $args = array())
    {
        $reflClass = new \ReflectionClass($commandClass);
        return $reflClass->newInstanceArgs($args);
    }

    /**
     * @param EnqueueableCommand $commandObj
     * @param string $commandName
     * @param array $callbacks
     * @return mixed
     */
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

    /**
     * Generates UUID
     *
     * @return string
     */
    public function generateUuid()
    {
        return $this->getUuidGenerator()->v4();
    }

    /**
     * @return PhpUuidGenerator
     */
    protected function getUuidGenerator()
    {
        if (!$this->uuidGenerator) {
            $this->uuidGenerator = new PHPUuidGenerator();
        }

        return $this->uuidGenerator;
    }


    private function executeOrQueueHandleCallbacks($strategy, $callbacks, $ret)
    {
        if (isset($callbacks[$strategy])) {
            return $callbacks[$strategy]($this->commander, $ret);
        }

        return $ret;
    }

    private function assertCommandExists($command)
    {
        if (!isset($this->commands[$command])) {
            throw new \InvalidArgumentException("Command '{$command}' is not supported");
        }
    }

    private function assertStrategyExists($strategy)
    {
        if (!in_array($strategy, array(EnqueueableCommand::STRATEGY_ASYNCHRONOUS, EnqueueableCommand::STRATEGY_SYNCHRONOUS))) {
            throw new \InvalidArgumentException("Invalid command strategy '{$strategy}'");
        }
    }



}
