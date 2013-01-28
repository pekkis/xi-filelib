<?php

namespace Xi\Filelib\Tests;

use Xi\Filelib\FileLibrary;
use Xi\Filelib\Configuration;

class FileLibraryTest extends TestCase
{
    /**
     * @var FileLibrary
     */
    private $filelib;

    /**
     * @var Configuration
     */
    private $configuration;

    public function setUp()
    {
        $this->configuration = $this->getConfigurationWithMockedObjects();
        $this->filelib = new FileLibrary($this->configuration);
    }


    /**
     * @test
     */
    public function classShouldExist()
    {
        $this->assertTrue(class_exists('Xi\Filelib\FileLibrary'));
    }

    /**
     * @test
     */
    public function getConfigurationShouldReturnConfiguration()
    {
        $this->assertSame($this->configuration, $this->filelib->getConfiguration());
    }

    /**
     * @test
     */
    public function getFileOperatorShouldReturnInjectedFileOperator()
    {
        $this->assertInstanceOf('Xi\Filelib\Operator\FileOperator', $this->filelib->getFileOperator());

        $this->assertInstanceOf(
            'Xi\Filelib\Operator\FolderOperator',
            $this->filelib->getFileOperator()->getFolderOperator()
        );
    }

    /**
     * @test
     */
    public function getFolderOperatorShouldReturnInjectedFolderOperator()
    {
        $this->assertInstanceOf('Xi\Filelib\Operator\FolderOperator', $this->filelib->getFolderOperator());

        $this->assertInstanceOf(
            'Xi\Filelib\Operator\FileOperator',
            $this->filelib->getFolderOperator()->getFileOperator()
        );
    }

    /**
     * @test
     */
    public function getProfilesShouldDelegateToFileOperator()
    {
        $fop = $this->getMockedFileOperator();
        $fop->expects($this->once())->method('getProfiles');

        $filelib = $this->getFilelibMock(array('getFileOperator'));
        $filelib
            ->expects($this->any())
            ->method('getFileOperator')
            ->will($this->returnValue($fop));

        $filelib->getProfiles();
    }

    /**
     * @test
     */
    public function addProfileShouldDelegateToFileOperator()
    {
        $profile = $this->getMockedFileProfile();

        $fop = $this->getMockedFileOperator();
        $fop
            ->expects($this->once())
            ->method('addProfile')
            ->with($this->equalTo($profile));

        $filelib = $this->getFilelibMock(array('getFileOperator'));
        $filelib
            ->expects($this->any())
            ->method('getFileOperator')
            ->will($this->returnValue($fop));

        $filelib->addProfile($profile);
    }

    /**
     * @test
     */
    public function addPluginShouldFirePluginAddEvent()
    {
        $plugin = $this->getMockForAbstractClass('Xi\Filelib\Plugin\Plugin');
        $plugin->expects($this->once())->method('init');

        $eventDispatcher = $this->configuration->getEventDispatcher();

        $eventDispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with(
                $this->equalTo('xi_filelib.plugin.add'),
                $this->isInstanceOf('Xi\Filelib\Event\PluginEvent')
            );

        $this->filelib->addPlugin($plugin);
    }

    /**
     * @test
     */
    public function addPluginShouldAddPluginAsSubscriber()
    {
        $plugin = $this->getMockedPlugin();

        $eventDispatcher = $this->configuration->getEventDispatcher();
        $eventDispatcher
            ->expects($this->once())
            ->method('addSubscriber')
            ->with($this->equalTo($plugin));

        $this->filelib->addPlugin($plugin);
    }


    protected function getFilelibMock($methods = array())
    {
        return $this
            ->getMockBuilder('Xi\Filelib\FileLibrary')
            ->setMethods($methods)
            ->setConstructorArgs(array($this->configuration))
            ->getMock();
    }
}
