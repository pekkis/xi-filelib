<?php

namespace Xi\Filelib\Tests\File\Command;

use Xi\Filelib\FileLibrary;
use Xi\Filelib\File\FileOperator;
use Xi\Filelib\File\File;
use Xi\Filelib\File\Resource;
use Xi\Filelib\File\Command\DeleteFileCommand;

class DeleteFileCommandTest extends \Xi\Filelib\Tests\TestCase
{

    /**
     * @test
     */
    public function classShouldExist()
    {
        $this->assertTrue(class_exists('Xi\Filelib\File\Command\DeleteFileCommand'));
        $this->assertContains('Xi\Filelib\File\Command\FileCommand', class_implements('Xi\Filelib\File\Command\DeleteFileCommand'));
    }

    /**
     * @test
     */
    public function commandShouldSerializeAndUnserializeProperly()
    {
        $op = $this->getMockedFileOperator();
        $op->expects($this->any())->method('generateUuid')->will($this->returnValue('xooxer'));

        $file = File::create(array('id' => 1, 'profile' => 'versioned'));

        $command = new DeleteFileCommand($op, $file);

        $serialized = serialize($command);

        $command2 = unserialize($serialized);

        $this->assertAttributeEquals(null, 'fileOperator', $command2);
        $this->assertAttributeEquals($file, 'file', $command2);
        $this->assertAttributeNotEmpty('uuid', $command2);
    }

    /**
     * @return array
     */
    public function provideForDeleteDelegation()
    {
        return array(
            array(false),
            array(true),
        );
    }

    /**
     * @test
     * @dataProvider provideForDeleteDelegation
     */
    public function deleteShouldDelegateCorrectly($exclusiveResource)
    {
        $filelib = $this->getMockedFilelib();
        $ed = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');
        $filelib->expects($this->any())->method('getEventDispatcher')->will($this->returnValue($ed));

        $ed
            ->expects($this->once())
            ->method('dispatch')
            ->with(
                $this->equalTo('xi_filelib.file.delete'),
                $this->isInstanceOf('Xi\Filelib\Event\FileEvent')
            );

        $op = $this->getMockBuilder('Xi\Filelib\File\FileOperator')
                   ->setConstructorArgs(array($filelib))
                   ->setMethods(array('getProfile', 'createCommand'))
                   ->getMock();

        $profile = $this->getMockedFileProfile();

        $file = File::create(array('id' => 1, 'profile' => 'lussen', 'resource' => Resource::create(array('exclusive' => $exclusiveResource))));

        $backend = $this
            ->getMockBuilder('Xi\Filelib\Backend\Backend')
            ->disableOriginalConstructor()
            ->getMock();
        $backend->expects($this->once())->method('deleteFile')->with($this->equalTo($file));

        $storage = $this->getMockForAbstractClass('Xi\Filelib\Storage\Storage');

        if ($exclusiveResource) {
            $storage->expects($this->once())->method('delete')->with($this->isInstanceOf('Xi\Filelib\File\Resource'));
            $backend->expects($this->once())->method('deleteResource')->with($this->isInstanceOf('Xi\Filelib\File\Resource'));
        } else {
            $storage->expects($this->never())->method('delete');
            $backend->expects($this->never())->method('deleteResource');
        }

        $filelib->expects($this->any())->method('getBackend')->will($this->returnValue($backend));
        $filelib->expects($this->any())->method('getStorage')->will($this->returnValue($storage));


        $op->expects($this->any())->method('getProfile')->with($this->equalTo('lussen'))->will($this->returnValue($profile));

        $command = new DeleteFileCommand($op, $file);
        $command->execute();

    }

}
