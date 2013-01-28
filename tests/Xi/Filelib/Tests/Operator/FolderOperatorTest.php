<?php

namespace Xi\Filelib\Tests\Operator;

use Xi\Filelib\FileLibrary;
use Xi\Filelib\Operator\FolderOperator;
use Xi\Filelib\Folder\Folder;
use Xi\Filelib\File\File;
use Xi\Filelib\Command\EnqueueableCommand;
use Xi\Filelib\Backend\Finder\FolderFinder;
use Xi\Filelib\Backend\Finder\FileFinder;
use ArrayIterator;
use Xi\Filelib\Configuration;

class FolderOperatorTest extends \Xi\Filelib\Tests\TestCase
{
    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * @var FolderOperator
     */
    private $op;

    private $backend;

    private $operatorManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $commandFactory;

    public function setUp()
    {
        $this->configuration = $this->getConfigurationWithMockedObjects();
        $this->operatorManager = $this->getMockedOperatorManager();

        $this->op = $this
            ->getMockBuilder('Xi\Filelib\Operator\FolderOperator')
            ->setConstructorArgs(array($this->configuration, $this->operatorManager))
            ->setMethods(array('getCommandFactory'))
            ->getMock();

        $this->commandFactory = $this->getMockedCommandFactory();

        $this->op
            ->expects($this->any())
            ->method('getCommandFactory')
            ->will($this->returnValue($this->commandFactory));

        $this->backend = $this->configuration->getBackend();
    }

    /**
     * @test
     */
    public function classShouldExist()
    {
        $this->assertTrue(class_exists('Xi\Filelib\Operator\FolderOperator'));
    }

    /**
     * @test
     */
    public function shouldContainCorrectCommandsAndStrategies()
    {
        $this->assertEquals(
            array(
                FolderOperator::COMMAND_CREATE => array(
                    'Xi\Filelib\Folder\Command\CreateFolderCommand',
                    EnqueueableCommand::STRATEGY_SYNCHRONOUS,
                ),
                FolderOperator::COMMAND_DELETE => array(
                    'Xi\Filelib\Folder\Command\DeleteFolderCommand',
                    EnqueueableCommand::STRATEGY_SYNCHRONOUS,
                ),
                FolderOperator::COMMAND_UPDATE => array(
                    'Xi\Filelib\Folder\Command\UpdateFolderCommand',
                    EnqueueableCommand::STRATEGY_SYNCHRONOUS,
                ),
                FolderOperator::COMMAND_CREATE_BY_URL => array(
                    'Xi\Filelib\Folder\Command\CreateByUrlFolderCommand',
                    EnqueueableCommand::STRATEGY_SYNCHRONOUS,
                ),
            ),
            $this->op->getCommands()
        );
    }

    /**
     * @test
     * @dataProvider provideCommandMethods
     */
    public function commandMethodsShouldExecuteOrEnqeueDependingOnStrategy($commandClass, $operatorMethod, $commandName, $strategy, $queueExpected)
    {
        $op = $this->getOpMock(array('createCommand'));

        $queue = $this->configuration->getQueue();

        $command = $this
            ->getMockBuilder($commandClass)
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

        $folder = $this->getMock('Xi\Filelib\Folder\Folder');

        $op
            ->expects($this->once())
            ->method('createCommand')
            ->with($this->equalTo($commandClass))
            ->will($this->returnValue($command));

        $op->setCommandStrategy($commandName, $strategy);
        $op->$operatorMethod($folder);
    }

    /**
     * @test
     */
    public function findShouldReturnFalseIfFileIsNotFound()
    {
        $id = 1;

        $filelib = new FileLibrary();
        $op = new FolderOperator($filelib);

        $backend = $this->getBackendMock();
        $filelib->setBackend($backend);

        $backend
            ->expects($this->once())
            ->method('findById')
            ->with($id, 'Xi\Filelib\Folder\Folder')
            ->will($this->returnValue(false));

        $folder = $op->find($id);
        $this->assertFalse($folder);
    }

    /**
     * @test
     */
    public function findShouldReturnFolderInstanceIfFileIsFound()
    {
        $id = 1;

        $filelib = new FileLibrary();
        $op = new FolderOperator($filelib);

        $backend = $this->getBackendMock();
        $filelib->setBackend($backend);

        $folder = new Folder();

        $backend
            ->expects($this->once())
            ->method('findById')
            ->with($id, 'Xi\Filelib\Folder\Folder')
            ->will($this->returnValue($folder));

        $ret = $op->find($id);
        $this->assertSame($folder, $ret);
    }

    /**
     * @test
     */
    public function findFilesShouldReturnEmptyArrayIteratorWhenNoFilesAreFound()
    {
        $filelib = new FileLibrary();
        $op = new FolderOperator($filelib);

        $finder = new FileFinder(
            array(
                'folder_id' => 500,
            )
        );

        $folders = new ArrayIterator(array());

        $backend = $this->getBackendMock();
        $filelib->setBackend($backend);

        $backend
            ->expects($this->once())
            ->method('findByFinder')->with(
                $this->equalTo($finder)
            )
            ->will($this->returnValue($folders));

        $folder = Folder::create(array('id' => 500, 'parent_id' => 499));
        $files = $op->findFiles($folder);

        $this->assertInstanceOf('ArrayIterator', $files);
        $this->assertCount(0, $files);
    }

    /**
     * @test
     */
    public function findFilesShouldReturnNonEmptyArrayIteratorWhenFilesAreFound()
    {
        $filelib = new FileLibrary();
        $op = new FolderOperator($filelib);

        $finder = new FileFinder(
            array(
                'folder_id' => 500,
            )
        );

        $files = new ArrayIterator(
            array(
                new File(),
                new File(),
                new File(),
            )
        );

        $backend = $this->getBackendMock();
        $filelib->setBackend($backend);

        $backend
            ->expects($this->once())
            ->method('findByFinder')->with(
            $this->equalTo($finder)
        )
            ->will($this->returnValue($files));

        $folder = Folder::create(array('id' => 500, 'parent_id' => 499));
        $files = $op->findFiles($folder);

        $this->assertInstanceOf('ArrayIterator', $files);
        $this->assertCount(3, $files);

    }

    /**
     * @test
     */
    public function findParentFolderShouldReturnFalseWhenParentIdIsNull()
    {
        $id = null;

        $filelib = new FileLibrary();
        $op = new FolderOperator($filelib);

        $backend = $this->getBackendMock();
        $filelib->setBackend($backend);

        $backend->expects($this->never())->method('findById');

        $folder = Folder::create(array('parent_id' => $id));
        $parent = $op->findParentFolder($folder);
        $this->assertFalse($parent);
    }

    /**
     * @test
     */
    public function findParentFolderShouldReturnFalseWhenParentIsNotFound()
    {
        $id = 5;

        $filelib = new FileLibrary();
        $op = new FolderOperator($filelib);

        $backend = $this->getBackendMock();
        $filelib->setBackend($backend);

        $backend
            ->expects($this->once())
            ->method('findById')
            ->with(5, 'Xi\Filelib\Folder\Folder')
            ->will($this->returnValue(false));

        $filelib->setBackend($backend);

        $folder = Folder::create(array('parent_id' => $id));

        $parent = $op->findParentFolder($folder);
        $this->assertFalse($parent);
    }

    /**
     * @test
     */
    public function findParentFolderShouldReturnFolderWhenParentIsFound()
    {
        $id = 5;

        $filelib = new FileLibrary();
        $op = new FolderOperator($filelib);

        $backend = $this->getBackendMock();
        $filelib->setBackend($backend);

        $parentFolder = new Folder();

        $backend
            ->expects($this->once())
            ->method('findById')
            ->with(5, 'Xi\Filelib\Folder\Folder')
            ->will($this->returnValue($parentFolder));

        $filelib->setBackend($backend);

        $folder = Folder::create(array('parent_id' => $id));

        $ret = $op->findParentFolder($folder);
        $this->assertSame($parentFolder, $ret);
    }

    /**
     * @test
     */
    public function findSubFoldersShouldReturnEmptyArrayIteratorWhenNoSubFoldersAreFound()
    {
        $filelib = new FileLibrary();
        $op = new FolderOperator($filelib);

        $finder = new FolderFinder(
            array(
                'parent_id' => 500,
            )
        );

        $folders = new ArrayIterator(array());

        $backend = $this->getBackendMock();
        $filelib->setBackend($backend);

        $backend
            ->expects($this->once())
            ->method('findByFinder')->with(
            $this->equalTo($finder)
        )
            ->will($this->returnValue($folders));

        $folder = Folder::create(array('id' => 500, 'parent_id' => 499));
        $files = $op->findSubFolders($folder);

        $this->assertInstanceOf('ArrayIterator', $files);
        $this->assertCount(0, $files);
    }

    /**
     * @test
     */
    public function findSubFoldersShouldReturnNonEmptyArrayIteratorWhenSubFoldersAreFound()
    {
        $filelib = new FileLibrary();
        $op = new FolderOperator($filelib);

        $finder = new FolderFinder(
            array(
                'parent_id' => 500,
            )
        );

        $folders = new ArrayIterator(
            array(
                new Folder(),
                new Folder(),
                new Folder(),
            )
        );

        $backend = $this->getBackendMock();
        $filelib->setBackend($backend);

        $backend
            ->expects($this->once())
            ->method('findByFinder')->with(
            $this->equalTo($finder)
        )
            ->will($this->returnValue($folders));

        $folder = Folder::create(array('id' => 500, 'parent_id' => 499));
        $files = $op->findSubFolders($folder);

        $this->assertInstanceOf('ArrayIterator', $files);
        $this->assertCount(3, $files);
    }

    /**
     * @test
     */
    public function findByUrlShouldReturnFolderWhenFolderIsFound()
    {
        $filelib = new FileLibrary();
        $op = new FolderOperator($filelib);

        $finder = new FolderFinder(
            array(
                'url' => 'lussen/tussi',
            )
        );

        $folders = new ArrayIterator(
            array(
                new Folder(),
            )
        );

        $backend = $this->getBackendMock();
        $filelib->setBackend($backend);

        $backend
            ->expects($this->once())
            ->method('findByFinder')->with(
            $this->equalTo($finder)
        )
            ->will($this->returnValue($folders));

        $folder = Folder::create(array('id' => 500, 'parent_id' => 499));

        $id = 'lussen/tussi';

        $folder = $op->findByUrl($id);
        $this->assertInstanceOf('Xi\Filelib\Folder\Folder', $folder);
    }

    /**
     * @test
     * @expectedException Xi\Filelib\FilelibException
     */
    public function findRootShouldFailWhenRootFolderIsNotFound()
    {
        $filelib = new FileLibrary();
        $op = new FolderOperator($filelib);

        $finder = new FolderFinder(
            array(
                'parent_id' => null,
            )
        );

        $folders = new ArrayIterator(
            array(
            )
        );

        $backend = $this->getBackendMock();
        $filelib->setBackend($backend);

        $backend
            ->expects($this->once())
            ->method('findByFinder')->with(
                $this->equalTo($finder)
            )
            ->will($this->returnValue($folders));

        $folder = $op->findRoot();
    }

    /**
     * @test
     */
    public function findRootShouldReturnFolderWhenRootFolderIsFound()
    {
        $filelib = new FileLibrary();
        $op = new FolderOperator($filelib);

        $finder = new FolderFinder(
            array(
                'parent_id' => null,
            )
        );

        $folders = new ArrayIterator(
            array(
                new Folder(),
            )
        );

        $backend = $this->getBackendMock();
        $filelib->setBackend($backend);

        $backend
            ->expects($this->once())
            ->method('findByFinder')->with(
            $this->equalTo($finder)
        )
            ->will($this->returnValue($folders));

        $folder = $op->findRoot();
        $this->assertInstanceOf('Xi\Filelib\Folder\Folder', $folder);
    }

    public function provideDataForBuildRouteTest()
    {
        return array(
            array('lussutus/bansku/tohtori vesala/lamantiini/kaskas/losoboesk', 10),
            array('lussutus/bansku/tohtori vesala/lamantiini/kaskas', 9),
            array('lussutus/bansku/tohtori vesala', 4),
            array('lussutus/bansku/tohtori vesala/lamantiini/klaus kulju', 8),
            array('lussutus/bansku/tohtori vesala/lamantiini/puppe', 6),
        );
    }

    /**
     * @test
     * @dataProvider provideDataForBuildRouteTest
     */
    public function buildRouteShouldBuildBeautifulRoute($expected, $folderId)
    {
        $filelib = new FileLibrary();
        $op = new FolderOperator($filelib);

        // $op->expects($this->exactly(4))->method('buildRoute')->with($this->isInstanceOf('Xi\Filelib\Folder\Folder'));

        $backend = $this->getBackendMock();
        $backend->expects($this->any())
                ->method('findById')
                ->with($this->isType('int'), 'Xi\Filelib\Folder\Folder')
                ->will($this->returnCallback(function($folderId, $class) {

                    $farr = array(
                        1 => Folder::create(array('parent_id' => null, 'name' => 'root')),
                        2 => Folder::create(array('parent_id' => 1, 'name' => 'lussutus')),
                        3 => Folder::create(array('parent_id' => 2, 'name' => 'bansku')),
                        4 => Folder::create(array('parent_id' => 3, 'name' => 'tohtori vesala')),
                        5 => Folder::create(array('parent_id' => 4, 'name' => 'lamantiini')),
                        6 => Folder::create(array('parent_id' => 5, 'name' => 'puppe')),
                        7 => Folder::create(array('parent_id' => 6, 'name' => 'nilkki')),
                        8 => Folder::create(array('parent_id' => 5, 'name' => 'klaus kulju')),
                        9 => Folder::create(array('parent_id' => 5, 'name' => 'kaskas')),
                        10 => Folder::create(array('parent_id' => 9, 'name' => 'losoboesk'))
                    );

                    if (isset($farr[$folderId])) {
                        return $farr[$folderId];
                    }

                    return false;
                }
            )
        );

        $filelib->setBackend($backend);

        $folder = $op->find($folderId);

        $route = $op->buildRoute($folder);

        $this->assertEquals($expected, $route);

    }

    /**
     * @param array $methods
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getOpMock($methods = array())
    {
        return $this
            ->getMockBuilder('Xi\Filelib\Operator\FolderOperator')
            ->setConstructorArgs(array($this->configuration))
            ->setMethods($methods)
            ->getMock();
    }


}
