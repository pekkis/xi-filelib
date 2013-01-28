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
use Xi\Filelib\Command\Commander;
use Xi\Filelib\Command\Commandeerable;

/**
 * Abstract convenience class for operators
 *
 * @author pekkis
 *
 */
abstract class AbstractOperator implements Commandeerable
{
    /**
     * @var OperatorManager
     */
    protected $operatorManager;

    /**
     *
     * @var UuidGenerator
     */
    protected $uuidGenerator;

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

    protected $commandStrategies = array();

    public function __construct(Configuration $configuration, OperatorManager $operatorManager)
    {
        $this->operatorManager = $operatorManager;
        $this->storage = $configuration->getStorage();
        $this->backend = $configuration->getBackend();
        $this->publisher = $configuration->getPublisher();
        $this->acl = $configuration->getAcl();
        $this->eventDispatcher = $configuration->getEventDispatcher();
        $this->commander = new Commander($configuration->getQueue(), $this);
    }

    public function getCommandStrategies()
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
     * @return UuidGenerator
     */
    protected function getUuidGenerator()
    {
        if (!$this->uuidGenerator) {
            $this->uuidGenerator = new PHPUuidGenerator();
        }

        return $this->uuidGenerator;
    }
}
