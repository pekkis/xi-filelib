<?php

namespace Xi\Filelib\Tests;

use Xi\Filelib\Configuration;

class ConfigurationTest extends TestCase
{
    public function setUp()
    {
        $this->dirname = ROOT_TESTS . '/data/publisher/unwritable_dir';
        chmod($this->dirname, 0444);
    }

    public function tearDown()
    {
        chmod($this->dirname, 0755);
    }

    /**
     * @test
     */
    public function classShouldExist()
    {
        $this->assertTrue(class_exists('Xi\Filelib\Configuration'));
    }

    /**
     * @test
     */
    public function storageSetterAndGetterShouldWorkAsExpected()
    {
        $configuration = new Configuration();
        $storage = $this->getMock('Xi\Filelib\Storage\Storage');

        $this->assertNull($configuration->getStorage());
        $this->assertSame($configuration, $configuration->setStorage($storage));
        $this->assertSame($storage, $configuration->getStorage());
    }


    /**
     * @test
     */
    public function publisherSetterAndGetterShouldWorkAsExpected()
    {
        $configuration = new Configuration();
        $obj = $this->getMockForAbstractClass('Xi\Filelib\Publisher\Publisher');
        $this->assertEquals(null, $configuration->getPublisher());
        $this->assertSame($configuration, $configuration->setPublisher($obj));
        $this->assertSame($obj, $configuration->getPublisher());
    }


    /**
     * @test
     */
    public function queueSetterAndGetterShouldWorkAsExpected()
    {
        $configuration = new Configuration();
        $obj = $this->getMock('Xi\Filelib\Queue\Queue');
        $this->assertEquals(null, $configuration->getQueue());
        $this->assertSame($configuration, $configuration->setQueue($obj));
        $this->assertSame($obj, $configuration->getQueue());
    }

    /**
     * @test
     */
    public function platformSetterAndGetterShouldWorkAsExpected()
    {
        $configuration = new Configuration();
        $obj = $this->getMock('Xi\Filelib\Backend\Platform\Platform');
        $this->assertEquals(null, $configuration->getPlatform());
        $this->assertSame($configuration, $configuration->setPlatform($obj));
        $this->assertSame($obj, $configuration->getPlatform());
    }

    /**
     * @test
     */
    public function backendSetterAndGetterShouldWorkAsExpected()
    {
        $configuration = new Configuration();
        $obj = $this
            ->getMockBuilder('Xi\Filelib\Backend\Backend')
            ->disableOriginalConstructor()
            ->getMock();
        $this->assertEquals(null, $configuration->getBackend());
        $this->assertSame($configuration, $configuration->setBackend($obj));
        $this->assertSame($obj, $configuration->getBackend());
    }

    /**
     * @test
     */
    public function identityMapSetterAndGetterShouldWorkAsExpected()
    {
        $configuration = new Configuration();
        $obj = $this
            ->getMockBuilder('Xi\Filelib\IdentityMap\IdentityMap')
            ->disableOriginalConstructor()
            ->getMock();
        $this->assertEquals(null, $configuration->getIdentityMap());
        $this->assertSame($configuration, $configuration->setIdentityMap($obj));
        $this->assertSame($obj, $configuration->getIdentityMap());
    }

    /**
     * @test
     */
    public function aclSetterAndGetterShouldWorkAsExpected()
    {
        $configuration = new Configuration();
        $obj = $this->getMockForAbstractClass('Xi\Filelib\Acl\Acl');
        $this->assertEquals(null, $configuration->getAcl());
        $this->assertSame($configuration, $configuration->setAcl($obj));
        $this->assertSame($obj, $configuration->getAcl());
    }

    /**
     * @test
     */
    public function tempDirShouldDefaultToSystemTempDirAnd()
    {
        $configuration = new Configuration();
        $this->assertEquals(sys_get_temp_dir(), $configuration->getTempDir());
    }

    /**
     * @test
     */
    public function setTempDirShouldObeySetter()
    {
        $configuration = new Configuration();
        $this->assertSame(
            $configuration,
            $configuration->setTempDir(ROOT_TESTS . '/data/temp')
        );
        $this->assertEquals(ROOT_TESTS . '/data/temp', $configuration->getTempDir());
    }


    /**
     * @test
     */
    public function setTempDirShouldFailWhenDirectoryDoesNotExists()
    {
        $configuration = new Configuration();

        $this->setExpectedException(
            'InvalidArgumentException',
            sprintf(
                'Temp dir "%s" is not writable or does not exist',
                ROOT_TESTS . '/nonexisting_directory'
            )
        );

        $configuration->setTempDir(ROOT_TESTS . '/nonexisting_directory');
    }

    /**
     * @test
     */
    public function setTempDirShouldFailWhenDirectoryIsNotWritable()
    {
        $dirname = ROOT_TESTS . '/data/publisher/unwritable_dir';
        $this->assertTrue(is_dir($this->dirname));
        $this->assertFalse(is_writable($this->dirname));

        $configuration = new Configuration();

        $this->setExpectedException(
            'InvalidArgumentException',
            sprintf(
                'Temp dir "%s" is not writable or does not exist',
                $dirname
            )
        );

        $configuration->setTempDir($dirname);
    }

    /**
     * @test
     */
    public function getEventDispatcherShouldDefaultToSymfonyDefaultImplementation()
    {
        $configuration = new Configuration();
        $dispatcher = $configuration->getEventDispatcher();
        $this->assertInstanceOf('Symfony\Component\EventDispatcher\EventDispatcher', $dispatcher);
    }

    /**
     * @test
     */
    public function getEventDispatcherShouldObeySetter()
    {
        $configuration = new Configuration();

        $mock = $this->getMockForAbstractClass('Symfony\Component\EventDispatcher\EventDispatcherInterface');

        $this->assertSame($configuration, $configuration->setEventDispatcher($mock));

        $dispatcher = $configuration->getEventDispatcher();

        $this->assertSame($mock, $dispatcher);
    }
}
