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
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    public function getMockedOperatorManager()
    {
        $manager = $this
            ->getMockBuilder('Xi\Filelib\Operator\OperatorManager')
            ->disableOriginalConstructor()
            ->getMock();

        return $manager;
    }


    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    public function getMockedCommandFactory()
    {
        $commandFactory = $this
            ->getMockBuilder('Xi\Filelib\Command\CommandFactory')
            ->disableOriginalConstructor()
            ->getMock();

        return $commandFactory;
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

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    public function getMockedStorage()
    {
        return $this->getMock('Xi\Filelib\Storage\Storage');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    public function getMockedLinker()
    {
        return $this->getMock('Xi\Filelib\Linker\Linker');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    public function getMockedAcl()
    {
        return $this->getMock('Xi\Filelib\Acl\Acl');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    public function getMockedQueue()
    {
        return $this->getMock('Xi\Filelib\Queue\Queue');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    public function getMockedEventDispatcher()
    {
        return $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    public function getMockedPublisher()
    {
        return $this->getMock('Xi\Filelib\Publisher\Publisher');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    public function getMockedPlatform()
    {
        return $this->getMock('Xi\Filelib\Backend\Platform\Platform');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    public function getMockedBackend()
    {
        $backend = $this
            ->getMockBuilder('Xi\Filelib\Backend\Backend')
            ->disableOriginalConstructor()
            ->getMock();

        return $backend;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
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

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    public function getMockedPlugin()
    {
        $plugin = $this->getMock('Xi\Filelib\Plugin\Plugin');
        return $plugin;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    public function getMockedCommand()
    {
        return $this->getMock('Xi\Filelib\Command\Command');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    public function getMockedFolder()
    {
        return $this->getMock('Xi\Filelib\Folder\Folder');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    public function getMockedFile()
    {
        return $this->getMock('Xi\Filelib\File\File');
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
