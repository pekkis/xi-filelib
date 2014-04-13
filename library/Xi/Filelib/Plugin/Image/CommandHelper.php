<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Plugin\Image;

use Xi\Filelib\Plugin\Image\Adapter\ImageProcessorAdapter;
use Xi\Filelib\Plugin\Image\Adapter\ImagickImageProcessorAdapter;
use Xi\Filelib\Plugin\Image\Command\Command;

/**
 * ImageMagick helper
 *
 * @author pekkis
 */
class CommandHelper
{
    protected $commands = array();

    /**
     * @var ImageProcessorAdapter
     */
    protected $adapter;

    /**
     * @var array
     */
    protected $executeOptions = array();

    public function __construct(
        $commandDefinitions = array(),
        ImageProcessorAdapter $adapter = null,
        $executeOptions = array()
    ) {
        foreach ($commandDefinitions as $key => $definition) {
            $this->addCommand($this->createCommandFromDefinition($key, $definition));
        }

        if (!$adapter) {
            $adapter = new ImagickImageProcessorAdapter();
        }

        $this->adapter = $adapter;
        $this->executeOptions = $executeOptions;
    }

    /**
     * @param Command $command
     */
    public function addCommand(Command $command)
    {
        $this->commands[] = $command;
    }

    /**
     * @return Command[]
     */
    public function getCommands()
    {
        return $this->commands;
    }

    /**
     * @param $key
     * @return Command
     */
    public function getCommand($key)
    {
        return $this->commands[$key];
    }

    public function setCommand($key, $command)
    {
        $this->commands[$key] = $command;
    }

    public function getExecuteOptions()
    {
        return $this->executeOptions;
    }

    public function execute($source, $target)
    {
        $this->adapter->execute($source, $target, $this);
    }

    /**
     * @param mixed $key
     * @param mixed $definition
     */
    private function createCommandFromDefinition($key, $definition)
    {
        if ($definition instanceof Command) {
            return $definition;
        }

        $commandClass = (is_numeric($key)) ? 'Xi\Filelib\Plugin\Image\Command\ExecuteMethodCommand' : $key;
        $reflClass = new \ReflectionClass($commandClass);
        $command = $reflClass->newInstanceArgs($definition);

        return $command;
    }
}
