<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Tests\Plugin\Image;

use Imagick;
use Xi\Filelib\FileLibrary;
use Xi\Filelib\Plugin\Image\VersionPlugin;
use Xi\Filelib\File\File;
use Xi\Filelib\File\FileRepository;
use Xi\Filelib\Storage\Storage;
use Xi\Filelib\Publisher\Publisher;
use Xi\Filelib\Resource\Resource;
use Xi\Filelib\Events;

/**
 * @group plugin
 */
class VersionPluginTest extends TestCase
{
    /**
     * @var VersionPlugin
     */
    private $plugin;

    /**
     * @var Storage
     */
    private $storage;

    public function setUp()
    {
        if (!class_exists('Imagick')) {
            return $this->markTestSkipped('Imagick required');
        }

        parent::setUp();

        $this->storage = $this->getMockedStorage();

        $this->plugin = new VersionPlugin(
            array(
                'xooxer' => array(
                    array(),
                    null,
                ),
                'tooxer' => array(
                    array(),
                    null
                )
            )
        );
    }

    /**
     * @test
     */
    public function classExtendsBasePlugin()
    {
        $this->assertArrayHasKey(
            'Xi\Filelib\Plugin\BasePlugin',
            class_parents($this->plugin)
        );
    }

    /**
     * @test
     */
    public function pluginShouldProvideForImage()
    {
        $filelib = new FileLibrary(
            $this->getMockedStorageAdapter(),
            $this->getMockedBackendAdapter()
        );
        $filelib->addPlugin($this->plugin);

        $this->assertFalse(
            $this->plugin->isApplicableTo(
                File::create(array('resource' => Resource::create(array('mimetype' => 'video/avi'))))
            )
        );

        $this->assertTrue(
            $this->plugin->isApplicableTo(
                File::create(array('resource' => Resource::create(array('mimetype' => 'image/png'))))
            )
        );
    }

    /**
     * @test
     */
    public function pluginShouldAllowSharedResource()
    {
        $this->assertTrue($this->plugin->isSharedResourceAllowed());
    }

    /**
     * @test
     */
    public function pluginShouldAllowSharedVersions()
    {
        $this->assertTrue($this->plugin->areSharedVersionsAllowed());
    }

    /**
     * @test
     */
    public function createProvidedVersionsShouldCreateVersions()
    {
        $retrievedPath = ROOT_TESTS . '/data/self-lussing-manatee.jpg';

        $file = File::create(array('id' => 1, 'resource' => Resource::create()));

        $this->storage
            ->expects($this->once())
            ->method('retrieve')
            ->with($this->isInstanceOf('Xi\Filelib\Resource\Resource'))
            ->will($this->returnValue($retrievedPath));

        $pm = $this->getMockedProfileManager(array('xooxer'));
        $filelib = $this->getMockedFilelib(
            null, array(
                'storage' => $this->storage,
                'pm' => $pm
            )
        );
        $filelib->expects($this->any())->method('getTempDir')->will($this->returnValue(ROOT_TESTS . '/data/temp'));

        $this->plugin->attachTo($filelib);
        $ret = $this->plugin->createTemporaryVersions($file);
        $this->assertInternalType('array', $ret);

        foreach ($ret as $version => $tmp) {
            $this->assertRegExp('#^' . ROOT_TESTS . '/data/temp#', $tmp);
        }
    }

    /**
     * @test
     */
    public function getSubscribedEventsShouldReturnCorrectEvents()
    {
        $events = VersionPlugin::getSubscribedEvents();
        $this->assertArrayHasKey(Events::PROFILE_AFTER_ADD, $events);
        $this->assertArrayHasKey(Events::FILE_AFTER_AFTERUPLOAD, $events);
        $this->assertArrayHasKey(Events::FILE_AFTER_DELETE, $events);
        $this->assertArrayHasKey(Events::RESOURCE_AFTER_DELETE, $events);
    }

    /**
     * @test
     */
    public function getVersionsShouldReturnArrayOfOneContainingIdentifier()
    {
         $this->assertEquals(array('xooxer', 'tooxer'), $this->plugin->getProvidedVersions());
    }

    /**
     * @test
     */
    public function getExtensionShouldUsePreDefinedMimeType()
    {
        $plugin = new VersionPlugin(
            array(
                'xooxoo' => array(
                    array(),
                    'application/rpki-ghostbusters'
                )
            )
        );
        $ret = $plugin->getExtension($this->getMockedFile(), 'xooxoo');
        $this->assertSame('gbr', $ret);
    }

    /**
     * @test
     */
    public function getExtensionShouldDelegateToParentToAutodetectExtension()
    {
        $storage = $this->getMockedStorage();

        $filelib = $this->getMockedFilelib(
            null,
            array(
                'storage' => $storage
            )
        );

        $plugin = new VersionPlugin(
            array(
                'xooxoo' => array(
                    array(),
                    null
                )
            )
        );
        $plugin->attachTo($filelib);

        $resource = $this->getMockedResource();
        $file = File::create(array('resource' => $resource));

        $storage->expects($this->once())
            ->method('retrieveVersion')
            ->with($resource, 'xooxoo')
            ->will($this->returnValue(ROOT_TESTS . '/data/self-lussing-manatee.jpg'));

        $ret = $plugin->getExtension($file, 'xooxoo');

        $this->assertSame('jpg', $ret);
    }



    /**
     * @test
     */
    public function injectsTempDirFromFilelib()
    {
        $filelib = $this->getMockedFilelib();
        $filelib->expects($this->any())->method('getTempDir')->will($this->returnValue('lussutushovi'));
    }
}
