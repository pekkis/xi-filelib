<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib;

use Xi\Filelib\Folder\FolderOperator;
use Xi\Filelib\File\FileOperator;
use Xi\Filelib\Plugin\Plugin;
use Xi\Filelib\File\FileProfile;
use Xi\Filelib\Event\PluginEvent;

/**
 * File library
 *
 * @author pekkis
 * @todo Refactor to contain common methods (getFile etc)
 *
 */
class FileLibrary
{
    /**
     * @var FileOperator
     */
    private $fileOperator;

    /**
     * @var FolderOperator
     */
    private $folderOperator;

    /**
     * @var Configuration
     */
    private $configuration;


    public function __construct(Configuration $configuration)
    {
        $this->configuration = $configuration;
        $this->fileOperator = new FileOperator($configuration);
        $this->folderOperator = new FolderOperator($configuration);

        // Muna or kana?
        $this->fileOperator->injectFolderOperator($this->folderOperator);
        $this->folderOperator->injectFileOperator($this->fileOperator);
    }

    /**
     * @return Configuration
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }


    /**
     * Returns file operator
     *
     * @return FileOperator
     */
    public function getFileOperator()
    {
        return $this->fileOperator;
    }

    /**
     * Returns folder operator
     *
     * @return FolderOperator
     */
    public function getFolderOperator()
    {
        return $this->folderOperator;
    }


    /**
     * Adds a file profile
     *
     * @param FileProfile $profile
     */
    public function addProfile(FileProfile $profile)
    {
        $this->getFileOperator()->addProfile($profile);
    }

    /**
     * Returns all file profiles
     *
     * @return array
     */
    public function getProfiles()
    {
        return $this->getFileOperator()->getProfiles();
    }

    /**
     * Adds a plugin
     *
     * @param  Plugin      $plugin
     * @param  integer     $priority
     * @return FileLibrary
     *
     * @TODO: Priority is not used.
     * @TODO: refactor to plugin manager VERY SOON SO NO NILLITYS ABOUT METHOD CHAINING THANX PUPPE!
     */
    public function addPlugin(Plugin $plugin, $priority = 1000)
    {
        $this->getConfiguration()->getEventDispatcher()->addSubscriber($plugin);

        $event = new PluginEvent($plugin);
        $this->getConfiguration()->getEventDispatcher()->dispatch('xi_filelib.plugin.add', $event);

        // @todo: is this necessary? Investigate!
        $plugin->init();

        return $this;
    }
}
