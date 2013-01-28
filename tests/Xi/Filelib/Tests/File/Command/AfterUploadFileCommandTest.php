<?php

namespace Xi\Filelib\Tests\File\Command;

use Xi\Filelib\FileLibrary;
use Xi\Filelib\Operator\FileOperator;
use Xi\Filelib\File\File;
use Xi\Filelib\File\Command\AfterUploadFileCommand;
use Xi\Filelib\File\Command\PublishFileCommand;

class AfterUploadFileCommandTest extends \Xi\Filelib\Tests\TestCase
{

    /**
     * @test
     */
    public function classShouldExist()
    {
        $this->assertTrue(class_exists('Xi\Filelib\File\Command\AfterUploadFileCommand'));
        $this->assertContains(
            'Xi\Filelib\File\Command\FileCommand',
            class_implements('Xi\Filelib\File\Command\AfterUploadFileCommand')
        );
    }

    public function provideDataForUploadTest()
    {
        return array(
            array(false, false),
            array(true, true),
        );
    }

    /**
     * @test
     * @dataProvider provideDataForUploadTest
     */
    public function commandShouldUploadAndDelegateCorrectly($expectedCallToPublish, $readableByAnonymous)
    {
        $configuration = $this->getConfigurationWithMockedObjects();
        $op = $this->getConfiguredMockedFileOperator(
            $configuration,
            array('publish', 'getInstance', 'createCommand', 'getProfile')
        );

        $dispatcher = $configuration->getEventDispatcher();

        if ($expectedCallToPublish) {

            $publishCommand = $this
                ->getMockBuilder('Xi\Filelib\File\Command\PublishFileCommand')
                ->disableOriginalConstructor()
                ->getMock();

            $publishCommand
                ->expects($this->once())
                ->method('execute');

            $op
                ->expects($this->once())
                ->method('createCommand')
                ->with($this->equalTo('Xi\Filelib\File\Command\PublishFileCommand'))
                ->will($this->returnValue($publishCommand));
        }

        $fileitem = $this->getMock('Xi\Filelib\File\File');

        $backend = $configuration->getBackend();
        $backend
            ->expects($this->once())
            ->method('updateFile')
            ->with($this->isInstanceOf('Xi\Filelib\File\File'));

        $fileitem
            ->expects($this->any())
            ->method('getProfile')
            ->will($this->returnValue('versioned'));

        $fileitem
            ->expects($this->once())
            ->method('setLink')
            ->with($this->equalTo('maximuslincitus'));

        $fileitem
            ->expects($this->once())
            ->method('setStatus')
            ->with($this->equalTo(File::STATUS_COMPLETED));

        $dispatcher
            ->expects($this->at(0))
            ->method('dispatch')
            ->with(
                $this->equalTo('xi_filelib.file.after_upload'),
                $this->isInstanceOf('Xi\Filelib\Event\FileEvent')
        );

        $profile = $this->getMockedFileProfile();

        $linker = $this->getMockedLinker();

        $linker
            ->expects($this->any())
            ->method('getLink')
            ->will($this->returnValue('maximuslincitus'));

        $profile
            ->expects($this->any())
            ->method('getLinker')
            ->will($this->returnValue($linker));

        $acl = $configuration->getAcl();
        $acl
            ->expects($this->atLeastOnce())
            ->method('isFileReadableByAnonymous')
            ->with($this->isInstanceOf('Xi\Filelib\File\File'))
            ->will($this->returnValue($readableByAnonymous));

        $op
            ->expects($this->any())
            ->method('getAcl')
            ->will($this->returnValue($acl));

        $op
            ->expects($this->any())
            ->method('getBackend')
            ->will($this->returnValue($backend));

        $op
            ->expects($this->atLeastOnce())
            ->method('getProfile')
            ->with($this->equalTo('versioned'))
            ->will($this->returnValue($profile));

        $command = new AfterUploadFileCommand($op, $fileitem);
        $ret = $command->execute();

        $this->assertInstanceOf('Xi\Filelib\File\File', $ret);
    }

    /**
     * @test
     */
    public function commandShouldSerializeAndUnserializeProperly()
    {
        $configuration = $this->getConfigurationWithMockedObjects();
        $op = $this->getConfiguredMockedFileOperator($configuration, array('getAcl'));

        $file = File::create(array('id' => 1, 'profile' => 'versioned'));

        $command = new AfterUploadFileCommand($op, $file);
        $serialized = serialize($command);
        $command2 = unserialize($serialized);

        $this->assertAttributeEquals(null, 'fileOperator', $command2);
        $this->assertAttributeEquals($file, 'file', $command2);
        $this->assertAttributeNotEmpty('uuid', $command2);
    }
}
