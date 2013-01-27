<?php

namespace Xi\Filelib\Tests;

use Xi\Filelib\Configuration;

class TestCase extends \PHPUnit_Framework_TestCase
{
    public function getMockedFilelib()
    {
        $filelib = $this
            ->getMockBuilder('Xi\Filelib\FileLibrary')
            ->disableOriginalConstructor()
            ->getMock();

        return $filelib;
    }

    public function getMockedFileOperator()
    {
        $fileop = $this
            ->getMockBuilder('Xi\Filelib\File\FileOperator')
            ->disableOriginalConstructor()
            ->getMock();

        return $fileop;
    }

    public function getMockedStorage()
    {
        return $this->getMock('Xi\Filelib\Storage\Storage');
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

    public function getMockedFileProfile()
    {
        return $this->getMockBuilder('Xi\Filelib\File\FileProfile')
            ->disableOriginalConstructor()
            ->getMock();
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
