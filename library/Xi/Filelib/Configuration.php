<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Xi\Filelib\Backend\Backend;
use Xi\Filelib\Storage\Storage;
use Xi\Filelib\Publisher\Publisher;
use Xi\Filelib\Acl\Acl;
use Xi\Filelib\Queue\Queue;
use Xi\Filelib\IdentityMap\IdentityMap;
use Xi\Filelib\Backend\Platform\Platform;
use Xi\Filelib\Exception\InvalidArgumentException;

class Configuration
{
    /**
     * @var string
     */
    private $tempDir;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var Backend
     */
    private $backend;

    /**
     * @var Storage
     */
    private $storage;

    /**
     * @var Publisher
     */
    private $publisher;

    /**
     * @var Acl
     */
    private $acl;

    /**
     * @var Queue
     */
    private $queue;

    /**
     * @var IdentityMap
     */
    private $identityMap;

    /**
     * @var Platform
     */
    private $platform;

    /**
     * @param EventDispatcherInterface $eventDispatcher
     * @return Configuration
     */
    public function setEventDispatcher(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
        return $this;
    }

    /**
     * @return EventDispatcherInterface
     */
    public function getEventDispatcher()
    {
        if (!$this->eventDispatcher) {
            $this->eventDispatcher = new EventDispatcher();
        }
        return $this->eventDispatcher;
    }



    /**
     * Sets temporary directory
     *
     * @param string $tempDir
     * @return Configuration
     */
    public function setTempDir($tempDir)
    {
        if (!is_dir($tempDir) || !is_writable($tempDir)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Temp dir "%s" is not writable or does not exist',
                    $tempDir
                )
            );
        }
        $this->tempDir = $tempDir;
        return $this;
    }

    /**
     * Returns temporary directory
     *
     * @return string
     */
    public function getTempDir()
    {
        if (!$this->tempDir) {
            $this->setTempDir(sys_get_temp_dir());
        }

        return $this->tempDir;
    }

    /**
     * Sets storage
     *
     * @param Storage $storage
     * @return Configuration
     */
    public function setStorage(Storage $storage)
    {
        $this->storage = $storage;

        return $this;
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
     * Sets publisher
     *
     * @param Publisher $publisher
     * @return Configuration
     */
    public function setPublisher(Publisher $publisher)
    {
        $this->publisher = $publisher;
        return $this;
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
     * Sets backend
     *
     * @param Backend $backend
     * @return Configuration
     */
    public function setBackend(Backend $backend)
    {
        $this->backend = $backend;
        return $this;
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
     * Sets acl handler
     *
     * @param Acl $acl
     * @return Configuration
     */
    public function setAcl(Acl $acl)
    {
        $this->acl = $acl;
        return $this;
    }

    /**
     * Returns acl handler
     *
     * @return Acl
     */
    public function getAcl()
    {
        return $this->acl;
    }

    /**
     * Sets queue
     *
     * @param Queue $queue
     * @return Configuration
     */
    public function setQueue(Queue $queue)
    {
        $this->queue = $queue;
        return $this;
    }

    /**
     * Returns queue
     *
     * @return Queue
     */
    public function getQueue()
    {
        return $this->queue;
    }

    /**
     * Sets identity map
     *
     * @param IdentityMap $identityMap
     * @return Configuration
     */
    public function setIdentityMap(IdentityMap $identityMap)
    {
        $this->identityMap = $identityMap;
        return $this;
    }

    /**
     * Returns identity map
     *
     * @return IdentityMap
     */
    public function getIdentityMap()
    {
        return $this->identityMap;
    }

    /**
     * Sets platform
     *
     * @param Platform $platform
     * @return Configuration
     */
    public function setPlatform(Platform $platform)
    {
        $this->platform = $platform;
        return $this;
    }

    /**
     * Returns platform
     *
     * @return Platform
     */
    public function getPlatform()
    {
        return $this->platform;
    }
}
