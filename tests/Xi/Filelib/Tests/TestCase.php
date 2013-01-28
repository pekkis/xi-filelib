<?php

namespace Xi\Filelib\Tests;

use Xi\Filelib\Configuration;

class TestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    public function getMockedFilelib()
    {
        $filelib = $this
            ->getMockBuilder('Xi\Filelib\FileLibrary')
            ->disableOriginalConstructor()
            ->getMock();

        return $filelib;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    public function getMockedFileOperator()
    {
        $fileop = $this
            ->getMockBuilder('Xi\Filelib\Operator\FileOperator')
            ->disableOriginalConstructor()
            ->getMock();

        return $fileop;
    }

    /**
     * @param array $methods
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    public function getConfiguredMockedFileOperator(Configuration $configuration, $methods = array())
    {
        $fileop = $this
            ->getMockBuilder('Xi\Filelib\Operator\FileOperator')
            ->setConstructorArgs(array($configuration))
            ->setMethods($methods)
            ->getMock();

        return $fileop;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    public function getMockedFolderOperator()
    {
        $folderop = $this
            ->getMockBuilder('Xi\Filelib\Operator\FolderOperator')
            ->disableOriginalConstructor()
            ->getMock();

        return $folderop;
    }

    public function getMockedStorage()
    {
        return $this->getMock('Xi\Filelib\Storage\Storage');
    }

    public function getMockedLinker()
    {
        return $this->getMock('Xi\Filelib\Linker\Linker');
    }

    public function getMockedAcl()
    {
        return $this->getMock('Xi\Filelib\Acl\Acl');
    }

    public function getMockedQueue()
    {
        return $this->getMock('Xi\Filelib\Queue\Queue');
    }

    public function getMockedEventDispatcher()
    {
        return $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');
    }

    public function getMockedPublisher()
    {
        return $this->getMock('Xi\Filelib\Publisher\Publisher');
    }

    public function getMockedPlatform()
    {
        return $this->getMock('Xi\Filelib\Backend\Platform\Platform');
    }

    public function getMockedBackend()
    {
        $backend = $this
            ->getMockBuilder('Xi\Filelib\Backend\Backend')
            ->disableOriginalConstructor()
            ->getMock();

        return $backend;
    }

    public function getMockedFileProfile($name = null)
    {
        $profile = $this
            ->getMockBuilder('Xi\Filelib\File\FileProfile')
            ->disableOriginalConstructor()
            ->getMock();

        if ($name) {
            $profile
                ->expects($this->any())
                ->method('getIdentifier')
                ->will($this->returnValue($name));
        }

        return $profile;
    }

    public function getMockedPlugin()
    {
        $plugin = $this->getMock('Xi\Filelib\Plugin\Plugin');
        return $plugin;
    }

    /**
     * @return Configuration
     */
    public function getConfigurationWithMockedObjects()
    {
        $configuration = new Configuration();
        $configuration->setAcl($this->getMockedAcl());
        $configuration->setPublisher($this->getMockedPublisher());
        $configuration->setEventDispatcher($this->getMockedEventDispatcher());
        $configuration->setStorage($this->getMockedStorage());
        $configuration->setQueue($this->getMockedQueue());
        $configuration->setBackend($this->getMockedBackend());
        $configuration->setPlatform($this->getMockedPlatform());

        return $configuration;
    }
}
