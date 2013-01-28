<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Operator;

use Xi\Filelib\Configuration;
use Xi\Filelib\Storage\Storage;
use Xi\Filelib\Backend\Backend;
use Xi\Filelib\Publisher\Publisher;
use Xi\Filelib\Acl\Acl;
use Xi\Filelib\Queue\Queue;
use Xi\Filelib\Tool\UuidGenerator\UuidGenerator;
use Xi\Filelib\Tool\UuidGenerator\PHPUuidGenerator;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Xi\Filelib\Command\CommandFactory;
use Xi\Filelib\Command\Commander;

/**
 * Abstract convenience class for operators
 *
 * @author pekkis
 *
 */
abstract class AbstractOperator implements Commander
{
    /**
     * @var OperatorManager
     */
    protected $operatorManager;

    /**
     * @var Storage
     */
    protected $storage;

    /**
     * @var Backend
     */
    protected $backend;

    /**
     * @var Publisher
     */
    protected $publisher;

    /**
     * @var Queue
     */
    protected $queue;

    /**
     * @var Acl
     */
    protected $acl;

    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @var array
     */
    protected $commandStrategies = array();

    private $commandFactory;

    public function __construct(Configuration $configuration, OperatorManager $operatorManager)
    {
        $this->operatorManager = $operatorManager;
        $this->storage = $configuration->getStorage();
        $this->backend = $configuration->getBackend();
        $this->publisher = $configuration->getPublisher();
        $this->acl = $configuration->getAcl();
        $this->eventDispatcher = $configuration->getEventDispatcher();
        $this->commandFactory = new CommandFactory($configuration->getQueue(), $this);
    }

    /**
     * @return array
     */
    public function getCommands()
    {
        return $this->commandStrategies;
    }

    /**
     * Returns backend
     *
     * @return Backend
     */
    public function getBackend()
    {
        return $this->backend;
    }

    /**
     * Returns storage
     *
     * @return Storage
     */
    public function getStorage()
    {
        return $this->storage;
    }

    /**
     * Returns publisher
     *
     * @return Publisher
     */
    public function getPublisher()
    {
        return $this->publisher;
    }

    /**
     * Returns Acl
     *
     * @return Acl
     */
    public function getAcl()
    {
        return $this->acl;
    }

    /**
     * Returns Event dispatcher
     *
     * @return EventDispatcherInterface
     */
    public function getEventDispatcher()
    {
        return $this->eventDispatcher;
    }

    protected function getCommandFactory()
    {
        return $this->commandFactory;
    }

}
