<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Operator;

use Xi\Filelib\FilelibException;
use Xi\Filelib\Folder\Folder;
use Xi\Filelib\Backend\Finder\FolderFinder;
use Xi\Filelib\Backend\Finder\FileFinder;
use Xi\Filelib\Command\CommandDefinition;
use Xi\Filelib\Command\CommandFactory;
use ArrayIterator;

/**
 * Operates on folders
 *
 * @author pekkis
 *
 */
class FolderOperator extends AbstractOperator
{
    const COMMAND_CREATE = 'create';
    const COMMAND_DELETE = 'delete';
    const COMMAND_UPDATE = 'update';
    const COMMAND_CREATE_BY_URL = 'create_by_url';

    /**
     * @return array
     */
    public function getCommandDefinitions()
    {
        return array(
            new CommandDefinition(
                self::COMMAND_CREATE,
                'Xi\Filelib\Folder\Command\CreateFolderCommand',
                CommandFactory::STRATEGY_SYNCHRONOUS
            ),
            new CommandDefinition(
                self::COMMAND_DELETE,
                'Xi\Filelib\Folder\Command\CreateFolderCommand',
                CommandFactory::STRATEGY_SYNCHRONOUS
            ),
            new CommandDefinition(
                self::COMMAND_UPDATE,
                'Xi\Filelib\Folder\Command\CreateFolderCommand',
                CommandFactory::STRATEGY_SYNCHRONOUS
            ),
            new CommandDefinition(
                self::COMMAND_CREATE_BY_URL,
                'Xi\Filelib\Folder\Command\CreateByUrlFolderCommand',
                CommandFactory::STRATEGY_SYNCHRONOUS
            )
        );
    }

    /**
     * Returns directory route for folder
     *
     * @param  Folder $folder
     * @return string
     */
    public function buildRoute(Folder $folder)
    {
        $rarr = array();

        array_unshift($rarr, $folder->getName());
        $imposter = clone $folder;
        while ($imposter = $this->findParentFolder($imposter)) {

            if ($imposter->getParentId()) {
                array_unshift($rarr, $imposter->getName());
            }
        }

        return implode('/', $rarr);
    }

    /**
     * Creates a folder
     *
     * @param Folder $folder
     * @return mixed
     */
    public function create(Folder $folder)
    {
        return $this
            ->getCommandFactory()
            ->createCommand(self::COMMAND_CREATE, array($this->operatorManager))
            ->execute();
    }

    /**
     * Deletes a folder
     *
     * @param Folder $folder Folder
     * @return mixed
     */
    public function delete(Folder $folder)
    {
        return $this
            ->getCommandFactory()
            ->createCommand(self::COMMAND_DELETE, array($this->operatorManager))
            ->execute();
    }

    /**
     * Updates a folder
     *
     * @param Folder $folder Folder
     */
    public function update(Folder $folder)
    {
        return $this
            ->getCommandFactory()
            ->createCommand(self::COMMAND_UPDATE, array($this->operatorManager))
            ->execute();
    }

    /**
     * Finds folder by url
     *
     * @param string $url
     * @return mixed
     */
    public function createByUrl($url)
    {
        return $this
            ->getCommandFactory()
            ->createCommand(self::COMMAND_CREATE_BY_URL, array($this->operatorManager))
            ->execute();
    }

    /**
     * Finds the root folder
     *
     * @return Folder
     */
    public function findRoot()
    {
        $folder = $this->getBackend()->findByFinder(
            new FolderFinder(array('parent_id' => null))
        )->current();

        if (!$folder) {
            throw new FilelibException('Could not locate root folder', 500);
        }

        return $folder;
    }

    /**
     * Finds a folder
     *
     * @param  mixed  $id Folder id
     * @return Folder
     */
    public function find($id)
    {
        $folder = $this->getBackend()->findById($id, 'Xi\Filelib\Folder\Folder');

        return $folder;
    }

    public function findByUrl($url)
    {
        $folder = $this->getBackend()->findByFinder(
            new FolderFinder(array('url' => $url))
        )->current();

        return $folder;
    }

    /**
     * Finds subfolders
     *
     * @param  Folder        $folder
     * @return ArrayIterator
     */
    public function findSubFolders(Folder $folder)
    {
        $folders = $this->getBackend()->findByFinder(
            new FolderFinder(array('parent_id' => $folder->getId()))
        );

        return $folders;
    }

    /**
     * Finds parent folder
     *
     * @param  Folder       $folder
     * @return Folder|false
     */
    public function findParentFolder(Folder $folder)
    {
        if (!$parentId = $folder->getParentId()) {
            return false;
        }

        $parent = $this->getBackend()->findById($folder->getParentId(), 'Xi\Filelib\Folder\Folder');

        return $parent;
    }

    /**
     * @param  Folder        $folder Folder
     * @return ArrayIterator Collection of file items
     */
    public function findFiles(Folder $folder)
    {
        $files = $this->getBackend()->findByFinder(
            new FileFinder(array('folder_id' => $folder->getId()))
        );

        return $files;
    }
}
