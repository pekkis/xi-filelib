<?php

namespace Xi\Filelib\Tests\Publisher\Adapter\Filesystem;

use Xi\Filelib\File\File;
use Xi\Filelib\Publisher\Adapter\Filesystem\BaseFilesystemPublisherAdapter;
use Xi\Filelib\Tests\TestCase;

class BaseFilesystemPublisherAdapterTest extends TestCase
{

    public function setUp()
    {
        parent::setUp();
        chmod(ROOT_TESTS . '/data/publisher/unwritable_dir', 0444);

    }

    public function tearDown()
    {
        parent::tearDown();
        chmod(ROOT_TESTS . '/data/publisher/unwritable_dir', 0755);

    }

    /**
     * @test
     */
    public function shouldInitializeCorrectly()
    {
        $dirPerm = "777";
        $filePerm = "666";

        $publicRoot = ROOT_TESTS . '/data/publisher/public';
        $baseUrl = 'http://dr-kobros.com/files';

        $publisher = $this->getMockedAdapter($publicRoot, $filePerm, $dirPerm, $baseUrl);

        $this->assertEquals(0777, $publisher->getDirectoryPermission());
        $this->assertEquals(0666, $publisher->getFilePermission());
        $this->assertEquals($publicRoot, $publisher->getPublicRoot());
        $this->assertEquals($baseUrl, $publisher->getBaseUrl());
    }

    /**
     * @test
     */
    public function shouldThrowupIfRootDirNotWritable()
    {
        $this->setExpectedException('Xi\Filelib\InvalidArgumentException');

        $invalidRoot = ROOT_TESTS . '/data/illusive_directory';

        $publisher = $this->getMockedAdapter($invalidRoot);
    }

    /**
     * @test
     */
    public function shouldInitializeCorrectlyWithDefaults()
    {
        $publicRoot = ROOT_TESTS . '/data/publisher/public';

        $publisher = $this->getMockedAdapter($publicRoot);

        $this->assertEquals(0700, $publisher->getDirectoryPermission());
        $this->assertEquals(0600, $publisher->getFilePermission());
        $this->assertEquals($publicRoot, $publisher->getPublicRoot());
        $this->assertEquals('', $publisher->getBaseUrl());
    }

    /**
     * @test
     */
    public function getUrlShouldReturnCorrectUrlVersion()
    {
        $linker = $this->getMockedLinker();
        $linker
            ->expects($this->once())
            ->method('getLink')
            ->will(
                $this->returnCallback(
                    function ($file, $version, $extension) {
                        return 'tussin/lussun/tussi-' . $version . '.jpg';
                    }
                )
            );

        $file = $this->getMockedFile();
        $file->expects($this->any())->method('getId')->will($this->returnValue(1));

        $publisher = $this->getMockedAdapter(
            ROOT_TESTS . '/data/publisher/public', "777", "666", 'http://diktaattoriporssi.com'
        );

        $versionProvider = $this->getMockedVersionProvider();

        $this->assertEquals(
            'http://diktaattoriporssi.com/tussin/lussun/tussi-xooxer.jpg',
            $publisher->getUrl($file, 'xooxer', $versionProvider, $linker)
        );
    }

    /**
     * @param $publicRoot
     * @param int $filePermission
     * @param int $directoryPermission
     * @param string $baseUrl
     * @return BaseFilesystemPublisherAdapter
     */
    public function getMockedAdapter($publicRoot, $filePermission = "600", $directoryPermission = "700", $baseUrl = '')
    {
        $adapter = $this
            ->getMockBuilder('Xi\Filelib\Publisher\Adapter\Filesystem\BaseFilesystemPublisherAdapter')
            ->setMethods(
                array(
                    'publish',
                    'unpublish',
                    'getLinkerForFile',
                    'attachTo'
                )
            )
            ->setConstructorArgs(array($publicRoot, $filePermission, $directoryPermission, $baseUrl))
            ->getMock();

        return $adapter;

    }

}
