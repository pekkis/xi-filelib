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
use Xi\Filelib\Plugin\VersionProvider\LazyVersionProvider;
use Xi\Filelib\FileLibrary;
use Xi\Filelib\Plugin\VersionProvider\Version;

/**
 * Versions an image
 */
class VersionPlugin extends LazyVersionProvider
{
    /**
     * @var string
     */
    protected $tempDir;

    /**
     * @var VersionPluginVersion[]
     */
    protected $versions;


    /**
     * @param string $identifier
     * @param array $commandDefinitions
     * @param string $mimeType
     */
    public function __construct(
        $versionDefinitions = array()
    ) {
        parent::__construct(
            function (File $file) {
                // @todo: maybe some more complex mime type based checking
                return (bool) preg_match("/^image/", $file->getMimetype());
            }
        );

        foreach ($versionDefinitions as $key => $definition) {
            $this->versions[$key] = new VersionPluginVersion(
                $key,
                $definition[0],
                isset($definition[1]) ? $definition[1] : null
            );
        }

    }

    public function attachTo(FileLibrary $filelib)
    {
        parent::attachTo($filelib);
        $this->tempDir = $filelib->getTempDir();
    }

    /**
     * Creates temporary version
     *
     * @param  File  $file
     * @return array
     */
    public function createTemporaryVersion(File $file, $version)
    {
        $version = Version::get($version)->getVersion();

        $retrieved = $this->storage->retrieve(
            $file->getResource()
        );

        $version = $this->versions[$version];

        $img = $version->getHelper()->createImagick($retrieved);
        $version->getHelper()->execute($img);
        $tmp = $this->tempDir . '/' . uniqid('', true);

        $img->writeImage($tmp);

        return $tmp;
    }

    /**
     * @param File $file
     * @return array
     */
    public function createAllTemporaryVersions(File $file)
    {
        $ret = array();
        foreach ($this->getProvidedVersions() as $version) {
            $ret[] = $this->createTemporaryVersion($file, $version);
        }

        return $ret;
    }

    /**
     * @return array
     */
    public function getProvidedVersions()
    {
        $ret = array();
        foreach ($this->versions as $version) {
            $ret[] = $version->getIdentifier();
        }

        return $ret;
    }

    /**
     * @param File $file
     * @param string $version
     * @return string
     */
    public function getExtension(File $file, $version)
    {
        if ($mimeType = $this->versions[$version]->getMimeType()) {
            return $this->getExtensionFromMimeType($mimeType);
        }
        return parent::getExtension($file, $version);
    }

    public function isSharedResourceAllowed()
    {
        return true;
    }

    public function areSharedVersionsAllowed()
    {
        return true;
    }

    public function isValidVersion(Version $version)
    {
        return in_array(
            $version->toString(),
            $this->getProvidedVersions()
        );
    }

}
