<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Tests\Operator;

use Xi\Filelib\Operator\FileOperator;
use Xi\Filelib\File\File;
use Xi\Filelib\File\Resource;
use Xi\Filelib\Folder\Folder;
use Xi\Filelib\File\Upload\FileUpload;
use Xi\Filelib\Command\EnqueueableCommand;
use Xi\Filelib\Backend\Finder\FileFinder;
use ArrayIterator;
use Xi\Filelib\Configuration;

class FileOperatorTest extends \Xi\Filelib\Tests\TestCase
{
    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * @var FileOperator
     */
    private $op;

    private $backend;

    public function setUp()
    {
        $this->configuration = $this->getConfigurationWithMockedObjects();

        $this->op = new FileOperator($this->configuration);
        $this->backend = $this->configuration->getBackend();
    }

    /**
     * @test
     */
    public function classShouldExist()
    {
        $this->assertTrue(class_exists('Xi\Filelib\Operator\FileOperator'));
    }

    /**
     * @test
     */
    public function strategiesShouldDefaultToSynchronous()
    {
        $this->assertEquals(
            EnqueueableCommand::STRATEGY_SYNCHRONOUS,
            $this->op->getCommandStrategy(FileOperator::COMMAND_UPLOAD)
        );

        $this->assertEquals(
            EnqueueableCommand::STRATEGY_SYNCHRONOUS,
            $this->op->getCommandStrategy(FileOperator::COMMAND_AFTERUPLOAD)
        );

        $this->assertEquals(
            EnqueueableCommand::STRATEGY_SYNCHRONOUS,
            $this->op->getCommandStrategy(FileOperator::COMMAND_UPDATE)
        );

        $this->assertEquals(
            EnqueueableCommand::STRATEGY_SYNCHRONOUS,
            $this->op->getCommandStrategy(FileOperator::COMMAND_DELETE)
        );

        $this->assertEquals(
            EnqueueableCommand::STRATEGY_SYNCHRONOUS,
            $this->op->getCommandStrategy(FileOperator::COMMAND_PUBLISH)
        );

        $this->assertEquals(
            EnqueueableCommand::STRATEGY_SYNCHRONOUS,
            $this->op->getCommandStrategy(FileOperator::COMMAND_UNPUBLISH)
        );

        $this->assertEquals(
            EnqueueableCommand::STRATEGY_SYNCHRONOUS,
            $this->op->getCommandStrategy(FileOperator::COMMAND_COPY)
        );
    }

    /**
     * @test
     */
    public function settingAndGettingCommandStrategiesShouldWork()
    {
        $this->assertEquals(
            EnqueueableCommand::STRATEGY_SYNCHRONOUS,
            $this->op->getCommandStrategy(FileOperator::COMMAND_UPLOAD)
        );

        $this->assertSame(
            $this->op,
            $this->op->setCommandStrategy(FileOperator::COMMAND_UPLOAD, EnqueueableCommand::STRATEGY_ASYNCHRONOUS)
        );

        $this->assertEquals(
            EnqueueableCommand::STRATEGY_ASYNCHRONOUS,
            $this->op->getCommandStrategy(FileOperator::COMMAND_UPLOAD)
        );
    }

    /**
     * @test
     */
    public function uploadShouldExecuteCommandsWhenSynchronousStrategyIsUsed()
    {
        $folder = $this->getMock('Xi\Filelib\Folder\Folder');

        $upload = new FileUpload(ROOT_TESTS . '/data/self-lussing-manatee.jpg');
        $profile = 'versioned';

        $op = $this->getOpMock(array('createCommand', 'getQueue'));
        $op->expects($this->never())->method('getQueue');

        $uploadCommand = $this->getMock('Xi\Filelib\EnqueueableCommand');
        $uploadCommand
            ->expects($this->once())
            ->method('execute');

        $op
            ->expects($this->at(0))
            ->method('createCommand')
            ->with($this->equalTo('Xi\Filelib\File\Command\UploadFileCommand'))
            ->will($this->returnValue($uploadCommand));

        $op->upload($upload, $folder, $profile);
    }

    /**
     * @test
     */
    public function uploadShouldQueueUploadCommandWhenAynchronousStrategyIsUsed()
    {
        $folder = $this->getMock('Xi\Filelib\Folder\Folder');
        $upload = new FileUpload(ROOT_TESTS . '/data/self-lussing-manatee.jpg');
        $profile = 'versioned';

        $op = $this->getOpMock(array('createCommand'));

        $uploadCommand = $this->getMock('Xi\Filelib\EnqueueableCommand');
        $uploadCommand
            ->expects($this->never())
            ->method('execute');

        $queue = $this->configuration->getQueue();
        $queue
            ->expects($this->once())
            ->method('enqueue')
            ->with($uploadCommand);

        $op
            ->expects($this->at(0))
            ->method('createCommand')
            ->with($this->equalTo('Xi\Filelib\File\Command\UploadFileCommand'))
            ->will($this->returnValue($uploadCommand));

        $op->setCommandStrategy(FileOperator::COMMAND_UPLOAD, EnqueueableCommand::STRATEGY_ASYNCHRONOUS);

        $op->upload($upload, $folder, $profile);
    }

    /**
     * @test
     */
    public function addProfileShouldAddProfile()
    {
        $this->assertEquals(array(), $this->op->getProfiles());

        $profile = $this->getMockedFileProfile('xooxer');
        $profile2 = $this->getMockedFileProfile('lusser');

        $eventDispatcher = $this->configuration->getEventDispatcher();
        $eventDispatcher
            ->expects($this->exactly(2))
            ->method('addSubscriber')
            ->with($this->isInstanceOf('Xi\Filelib\File\FileProfile'));

        $eventDispatcher
            ->expects($this->exactly(2))
            ->method('dispatch')
            ->with(
                $this->equalTo('xi_filelib.profile.add'),
                $this->isInstanceOf('Xi\Filelib\Event\FileProfileEvent')
            );

        $this->op->addProfile($profile);
        $this->assertCount(1, $this->op->getProfiles());

        $this->op->addProfile($profile2);
        $this->assertCount(2, $this->op->getProfiles());

        $this->assertSame($profile, $this->op->getProfile('xooxer'));
        $this->assertSame($profile2, $this->op->getProfile('lusser'));
    }

    /**
     * @test
     * @expectedException InvalidArgumentException
     */
    public function addProfileShouldFailWhenProfileAlreadyExists()
    {
        $profile = $this->getMockedFileProfile('xooxer');
        $profile2 = $this->getMockedFileProfile('xooxer');

        $this->op->addProfile($profile);
        $this->op->addProfile($profile2);
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function getProfileShouldFailWhenProfileDoesNotExist()
    {
       $prof = $this->op->getProfile('xooxer');
    }

    /**
     * @test
     */
    public function findShouldReturnFalseIfFileIsNotFound()
    {
        $id = 1;

        $this->backend
            ->expects($this->once())
            ->method('findById')
            ->with($id, 'Xi\Filelib\File\File')
            ->will($this->returnValue(false));

        $file = $this->op->find($id);
        $this->assertEquals(false, $file);
    }

    /**
     * @test
     */
    public function findShouldReturnFileInstanceIfFileIsFound()
    {
        $id = 1;
        $file = new File();

        $this->backend
            ->expects($this->once())
            ->method('findById')
            ->with($this->equalTo($id))
            ->will($this->returnValue($file));

        $ret = $this->op->find($id);
        $this->assertSame($file, $ret);
    }

    /**
     * @test
     */
    public function findByFilenameShouldReturnFalseIfFileIsNotFound()
    {
        $folder = Folder::create(array('id' => 6));
        $finder = new FileFinder(
            array(
                'folder_id' => 6,
                'name' => 'lussname',
            )
        );

        $this->backend
            ->expects($this->once())
            ->method('findByFinder')->with(
                $this->equalTo($finder)
            )
            ->will($this->returnValue(new ArrayIterator(array())));

        $ret = $this->op->findByFilename($folder, 'lussname');
        $this->assertFalse($ret);
    }

    /**
     * @test
     */
    public function findByFilenameShouldReturnFileInstanceIfFileIsFound()
    {
        $folder = Folder::create(array('id' => 6));
        $file = new File();
        $finder = new FileFinder(
            array(
                'folder_id' => 6,
                'name' => 'lussname',
            )
        );

        $this->backend
            ->expects($this->once())
            ->method('findByFinder')->with(
                $this->equalTo($finder)
            )
            ->will($this->returnValue(new ArrayIterator(array($file))));

        $ret = $this->op->findByFilename($folder, 'lussname');
        $this->assertSame($file, $ret);
    }

      /**
     * @test
     */
    public function findAllShouldReturnEmptyIfNoFilesAreFound()
    {
        $finder = new FileFinder();
        $this->backend
            ->expects($this->once())
            ->method('findByFinder')->with(
                $this->equalTo($finder)
            )
            ->will($this->returnValue(new ArrayIterator(array())));
        $files = $this->op->findAll();
        $this->assertCount(0, $files);

    }

    /**
     * @test
     */
    public function findAllShouldReturnAnArrayOfFileInstancesIfFilesAreFound()
    {
        $finder = new FileFinder();
        $iter = new ArrayIterator(array(
            new File(),
            new File(),
            new File(),
        ));
        $this->backend
            ->expects($this->once())
            ->method('findByFinder')->with(
                $this->equalTo($finder)
            )
            ->will($this->returnValue($iter));

        $files = $this->op->findAll();
        $this->assertSame($iter, $files);
    }

    /**
     * @test
     */
    public function prepareUploadShouldReturnFileUpload()
    {
        $upload = $this->op->prepareUpload(ROOT_TESTS . '/data/self-lussing-manatee.jpg');
        $this->assertInstanceOf('Xi\Filelib\File\Upload\FileUpload', $upload);
    }

    /**
     * @test
     */
    public function typeResolverShouldDefaultToStupid()
    {
        $this->assertInstanceOf(
            'Xi\Filelib\Tool\TypeResolver\StupidTypeResolver',
            $this->op->getTypeResolver()
        );
    }

    /**
     *  @test
     */
    public function typeResolverShouldRespectSetter()
    {
        $typeResolver = $this->getMock('Xi\Filelib\Tool\TypeResolver\TypeResolver');
        $this->assertSame($this->op, $this->op->setTypeResolver($typeResolver));
        $this->assertSame($typeResolver, $this->op->getTypeResolver());
    }

    /**
     * @test
     */
    public function getTypeShouldDelegateToTypeResolver()
    {
        $typeResolver = $this->getMock('Xi\Filelib\Tool\TypeResolver\TypeResolver');
        $typeResolver
            ->expects($this->once())
            ->method('resolveType')
            ->with($this->equalTo('application/lus'))
            ->will($this->returnValue('losoface'));

        $file = File::create(array(
            'name' => 'larvador.lus',
            'resource' => Resource::create(array('mimetype' => 'application/lus'))
        ));

        $this->op->setTypeResolver($typeResolver);
        $ret = $this->op->getType($file);
        $this->assertEquals('losoface', $ret);
    }

    /**
     * @test
     */
    public function hasVersionShouldDelegateToProfile()
    {
        $op = $this->getOpMock(array('getProfile'));

        $file = File::create(array('profile' => 'meisterlus'));

        $profile = $this->getMockedFileProfile();
        $profile
            ->expects($this->once())
            ->method('fileHasVersion')
            ->with($this->equalTo($file), $this->equalTo('kloo'))
            ->will($this->returnValue(false));

        $op
            ->expects($this->any())
            ->method('getProfile')
            ->with($this->equalTo('meisterlus'))
            ->will($this->returnValue($profile));

        $hasVersion = $op->hasVersion($file, 'kloo');
        $this->assertFalse($hasVersion);
    }

    /**
     * @test
     */
    public function getVersionProviderShouldDelegateToProfile()
    {
        $op = $this->getOpMock(array('getProfile'));
        $file = File::create(array('profile' => 'meisterlus'));

        $profile = $this->getMockedFileProfile();
        $profile
            ->expects($this->once())
            ->method('getVersionProvider')
            ->with($this->equalTo($file), $this->equalTo('kloo'))
            ->will($this->returnValue('lus'));

        $op
            ->expects($this->any())
            ->method('getProfile')
            ->with($this->equalTo('meisterlus'))
            ->will($this->returnValue($profile));

        $vp = $op->getVersionProvider($file, 'kloo');
        $this->assertEquals('lus', $vp);
    }

    /**
     * @test
     */
    public function addProfileShouldDelegateToProfile()
    {
        $op = $this->getOpMock(array('getProfile'));

        $plugin = $this->getMockedPlugin();
        $plugin
            ->expects($this->atLeastOnce())
            ->method('getProfiles')
            ->will($this->returnValue(array('lussi', 'tussi', 'jussi')));

        $profile1 = $this->getMockedFileProfile();
        $profile1
            ->expects($this->once())
            ->method('addPlugin')
            ->with($this->equalTo($plugin));

        $profile2 = $this->getMockedFileProfile();
        $profile2
            ->expects($this->once())
            ->method('addPlugin')
            ->with($this->equalTo($plugin));

        $profile3 = $this->getMockedFileProfile();
        $profile3
            ->expects($this->once())
            ->method('addPlugin')
            ->with($this->equalTo($plugin));

        $op
            ->expects($this->exactly(3))
            ->method('getProfile')
            ->with($this->logicalOr($this->equalTo('lussi'), $this->equalTo('tussi'), $this->equalTo('jussi')))
            ->will(
                $this->returnValueMap(
                    array(
                        array('tussi', $profile1),
                        array('lussi', $profile2),
                        array('jussi', $profile3),
                    )
                )
            );

        $op->addPlugin($plugin);
    }

    /**
     * @test
     * @expectedException InvalidArgumentException
     */
    public function settingInvalidstrategyShouldThrowException()
    {
        $this->op->setCommandStrategy(FileOperator::COMMAND_UPLOAD, 'tussenhof');
    }

    public function provideCommandMethods()
    {
        return array(
            array(
                'Xi\Filelib\File\Command\CopyFileCommand',
                'copy',
                FileOperator::COMMAND_COPY,
                EnqueueableCommand::STRATEGY_ASYNCHRONOUS,
                true,
                true
            ),
            array(
                'Xi\Filelib\File\Command\CopyFileCommand',
                'copy',
                FileOperator::COMMAND_COPY,
                EnqueueableCommand::STRATEGY_SYNCHRONOUS,
                false,
                true
            ),
            array(
                'Xi\Filelib\File\Command\DeleteFileCommand',
                'delete',
                FileOperator::COMMAND_DELETE,
                EnqueueableCommand::STRATEGY_ASYNCHRONOUS,
                true,
                false
            ),
            array(
                'Xi\Filelib\File\Command\DeleteFileCommand',
                'delete',
                FileOperator::COMMAND_DELETE,
                EnqueueableCommand::STRATEGY_SYNCHRONOUS,
                false,
                false
            ),
            array(
                'Xi\Filelib\File\Command\PublishFileCommand',
                'publish',
                FileOperator::COMMAND_PUBLISH,
                EnqueueableCommand::STRATEGY_ASYNCHRONOUS,
                true,
                false
            ),
            array(
                'Xi\Filelib\File\Command\PublishFileCommand',
                'publish',
                FileOperator::COMMAND_PUBLISH,
                EnqueueableCommand::STRATEGY_SYNCHRONOUS,
                false,
                false
            ),
            array(
                'Xi\Filelib\File\Command\UnpublishFileCommand',
                'unpublish',
                FileOperator::COMMAND_UNPUBLISH,
                EnqueueableCommand::STRATEGY_ASYNCHRONOUS,
                true,
                false
            ),
            array(
                'Xi\Filelib\File\Command\UnpublishFileCommand',
                'unpublish',
                FileOperator::COMMAND_UNPUBLISH,
                EnqueueableCommand::STRATEGY_SYNCHRONOUS,
                false,
                false
            ),
            array(
                'Xi\Filelib\File\Command\UpdateFileCommand',
                'update',
                FileOperator::COMMAND_UPDATE,
                EnqueueableCommand::STRATEGY_ASYNCHRONOUS,
                true,
                false
            ),
            array(
                'Xi\Filelib\File\Command\UpdateFileCommand',
                'update',
                FileOperator::COMMAND_UPDATE,
                EnqueueableCommand::STRATEGY_SYNCHRONOUS,
                false,
                false
            ),
        );

    }

    /**
     * @test
     * @dataProvider provideCommandMethods
     */
    public function commandMethodsShouldExecuteOrEnqeueDependingOnStrategy(
        $commandClass,
        $operatorMethod,
        $commandName,
        $strategy,
        $queueExpected,
        $fileAndFolder
    ) {

        $op = $this->getOpMock(array('createCommand'));

        $queue = $this->configuration->getQueue();

        $command = $this->getMockBuilder($commandClass)
                      ->disableOriginalConstructor()
                      ->setMethods(array('execute'))
                      ->getMock();

        if ($queueExpected) {

            $queue
                ->expects($this->once())
                ->method('enqueue')
                ->with($this->isInstanceOf($commandClass));

            $command->expects($this->never())->method('execute');
        } else {

            $queue
                ->expects($this->never())
                ->method('enqueue');
            $command
                ->expects($this->once())
                ->method('execute');

        }

        $file = $this->getMock('Xi\Filelib\File\File');
        $folder = $this->getMock('Xi\Filelib\Folder\Folder');

        $op
            ->expects($this->once())
            ->method('createCommand')
            ->with($this->equalTo($commandClass))
            ->will($this->returnValue($command));

        $op->setCommandStrategy($commandName, $strategy);

        if ($fileAndFolder) {
          $op->$operatorMethod($file, $folder);
        } else {
          $op->$operatorMethod($file);
        }

    }

    /**
     * @param array $methods
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getOpMock($methods = array())
    {
        return $this
            ->getMockBuilder('Xi\Filelib\Operator\FileOperator')
            ->setConstructorArgs(array($this->configuration))
            ->setMethods($methods)
            ->getMock();
    }
}
