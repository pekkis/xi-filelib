<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Authorization;

use Xi\Filelib\Event\FileEvent;
use Xi\Filelib\Event\FolderEvent;
use Xi\Filelib\FileLibrary;
use Xi\Filelib\Identifiable;
use Xi\Filelib\Plugin\BasePlugin;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Xi\Filelib\Events as CoreEvents;
use Xi\Filelib\Publisher\Events as PublisherEvents;
use Xi\Filelib\Authorization\AccessDeniedException;
use Xi\Filelib\Event\IdentifiableEvent;
use Xi\Filelib\Renderer\Events as RendererEvents;

class AuthorizationPlugin extends BasePlugin
{
    /**
     * @var AuthorizationAdapter
     */
    private $adapter;

    /**
     * @var array
     */
    protected static $subscribedEvents = array(
        CoreEvents::FOLDER_BEFORE_WRITE_TO => 'checkFolderWrite',
        CoreEvents::FOLDER_BEFORE_DELETE => 'checkFolderWrite',
        CoreEvents::FOLDER_BEFORE_UPDATE => 'checkFolderWrite',
        CoreEvents::FILE_BEFORE_DELETE => 'checkFileWrite',
        CoreEvents::FILE_BEFORE_UPDATE => 'checkFileWrite',
        PublisherEvents::FILE_BEFORE_PUBLISH => 'checkFileAnonymousRead',
        RendererEvents::RENDERER_BEFORE_RENDER => 'checkFileRead',
    );

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    public function __construct(AuthorizationAdapter $adapter)
    {
        $this->adapter = $adapter;
    }

    public function attachTo(FileLibrary $filelib)
    {
        $this->eventDispatcher = $filelib->getEventDispatcher();
        $this->adapter->attachTo($filelib);
    }

    public function checkFileAnonymousRead(FileEvent $event)
    {
        $file = $event->getFile();
        if (!$this->adapter->isFileReadableByAnonymous($file)) {
            $this->dispatchDenyEvent($file);
            throw $this->createAccessDeniedException($file, 'anonymous read');
        }
    }

    public function checkFileWrite(FileEvent $event)
    {
        $file = $event->getFile();
        if (!$this->adapter->isFileWritable($file)) {
            $this->dispatchDenyEvent($file);
            throw $this->createAccessDeniedException($file, 'write');
        }
    }

    public function checkFileRead(FileEvent $event)
    {
        $file = $event->getFile();
        if (!$this->adapter->isFileReadable($file)) {
            $this->dispatchDenyEvent($file);
            throw $this->createAccessDeniedException($file, 'read');
        }
    }

    public function checkFolderRead(FolderEvent $event)
    {
        $folder = $event->getFolder();
        if (!$this->adapter->isFolderReadable($folder)) {
            $this->dispatchDenyEvent($folder);
            throw $this->createAccessDeniedException($folder, 'read');
        }
    }

    public function checkFolderWrite(FolderEvent $event)
    {
        $folder = $event->getFolder();
        if (!$this->adapter->isFolderWritable($folder)) {
            $this->dispatchDenyEvent($folder);
            throw $this->createAccessDeniedException($folder, 'write');
        }
    }

    /**
     * @param Identifiable $identifiable
     * @param string $permission
     * @return AccessDeniedException
     */
    private function createAccessDeniedException(Identifiable $identifiable, $permission)
    {
        $implodedClass = explode('\\', get_class($identifiable));
        $msg = sprintf(
            "%s access to %s #%s was denied",
            ucfirst($permission),
            lcfirst(array_pop($implodedClass)),
            $identifiable->getId()
        );

        return new AccessDeniedException($msg);
    }

    /**
     * @param Identifiable $identifiable
     */
    public function dispatchDenyEvent(Identifiable $identifiable)
    {
        $event = new IdentifiableEvent($identifiable);
        $this->eventDispatcher->dispatch(Events::BEFORE_DENY_ACCESS, $event);
    }
}
