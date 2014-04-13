<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Plugin\Image;

use Xi\Filelib\File\File;
use Xi\Filelib\File\FileRepository;
use Xi\Filelib\Plugin\Image\Adapter\ImageProcessorAdapter;
use Xi\Filelib\Plugin\VersionProvider\AbstractVersionProvider;
use Xi\Filelib\FileLibrary;

/**
 * Versions an image
 */
class VersionPlugin extends AbstractVersionProvider
{
    /**
     * @var CommandHelper
     */
    protected $commandHelper;

    /**
     * @var File extension for the version
     */
    protected $extension;

    /**
     * @var string
     */
    protected $tempDir;

    /**
     * @var string
     */
    protected $identifier;

    public function __construct(
        $identifier,
        $commandDefinitions = array(),
        $extension = null,
        ImageProcessorAdapter $adapter = null,
        $executeOptions = array()
    ) {
        parent::__construct(
            function (File $file) {
                // @todo: maybe some more complex mime type based checking
                return (bool) preg_match("/^image/", $file->getMimetype());
            }
        );
        $this->identifier = $identifier;
        $this->extension = $extension;

        $this->commandHelper = new CommandHelper(
            $commandDefinitions,
            $adapter,
            $executeOptions
        );
    }

    public function attachTo(FileLibrary $filelib)
    {
        parent::attachTo($filelib);
        $this->tempDir = $filelib->getTempDir();
    }

    /**
     * Returns ImageMagick helper
     *
     * @return CommandHelper
     */
    public function getCommandHelper()
    {
        return $this->commandHelper;
    }

    /**
     * Creates temporary version
     *
     * @param  File  $file
     * @return array
     */
    public function createTemporaryVersions(File $file)
    {
        // Todo: optimize
        $retrieved = $this->storage->retrieve($file->getResource());
        $tmp = $this->tempDir . '/' . uniqid('', true);
        $this->getCommandHelper()->execute($retrieved, $tmp);
        return array(
            $this->identifier => $tmp
        );
    }

    public function getVersions()
    {
        return array($this->identifier);
    }

    /**
     * Returns the plugins file extension
     *
     * @return string
     */
    public function getExtension()
    {
        return $this->extension;
    }

    public function getExtensionFor(File $file, $version)
    {
        // Hard coded extension (the old way)
        if ($extension = $this->getExtension()) {
            return $extension;
        }
        return parent::getExtensionFor($file, $version);
    }

    public function isSharedResourceAllowed()
    {
        return true;
    }

    public function areSharedVersionsAllowed()
    {
        return true;
    }
}
