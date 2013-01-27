<?php

namespace Xi\Filelib\Tests\File\Command;

use Xi\Filelib\FileLibrary;
use Xi\Filelib\File\FileOperator;
use Xi\Filelib\File\File;
use Xi\Filelib\File\Command\UnPublishFileCommand;

class UnPublishFileCommandTest extends \Xi\Filelib\Tests\TestCase
{

    /**
     * @test
     */
    public function classShouldExist()
    {
        $this->assertTrue(class_exists('Xi\Filelib\File\Command\UnpublishFileCommand'));
        $this->assertContains('Xi\Filelib\File\Command\FileCommand', class_implements('Xi\Filelib\File\Command\UnPublishFileCommand'));
    }

    /**
     * @test
     */
    public function commandShouldSerializeAndUnserializeProperly()
    {
        $configuration = $this->getConfigurationWithMockedObjects();
        $op = $this->getConfiguredMockedFileOperator($configuration, array('getAcl'));

        $file = File::create(array('id' => 1, 'profile' => 'versioned'));

        $command = new UnPublishFileCommand($op, $file);
        $serialized = serialize($command);
        $command2 = unserialize($serialized);

        $this->assertAttributeEquals(null, 'fileOperator', $command2);
        $this->assertAttributeEquals($file, 'file', $command2);
        $this->assertAttributeNotEmpty('uuid', $command2);
    }

    /**
     * @test
     */
    public function unpublishShouldDelegateCorrectly()
    {
        $file = File::create(array('id' => 1, 'profile' => 'lussen'));

        $configuration = $this->getConfigurationWithMockedObjects();
        $op = $this->getConfiguredMockedFileOperator($configuration, array('getAcl'));

        $dispatcher = $configuration->getEventDispatcher();
        $dispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with(
                $this->equalTo('xi_filelib.file.unpublish'),
                $this->isInstanceOf('Xi\Filelib\Event\FileEvent')
            );

        $publisher = $configuration->getPublisher();
        $publisher
            ->expects($this->once())
            ->method('unpublish');

        $command = new UnpublishFileCommand($op, $file);
        $command->execute();
    }
}
