<?php

namespace Xi\Filelib\Tests\Operator;

use Configuration;
use Xi\Filelib\Operator\OperatorManager;

class OperatorManagerTest extends \Xi\Filelib\Tests\TestCase
{
    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * @test
     */
    public function classShouldExist()
    {
        $this->assertTrue(class_exists('Xi\Filelib\Operator\OperatorManager'));
    }

    /**
     * @test
     */
    public function operatorGettersShouldReturnOperators()
    {
        $manager = new OperatorManager($this->getConfigurationWithMockedObjects());
        $this->assertInstanceOf('Xi\Filelib\Operator\FileOperator', $manager->getFileOperator());
        $this->assertInstanceOf('Xi\Filelib\Operator\FolderOperator', $manager->getFolderOperator());
        $this->assertInstanceOf('Xi\Filelib\Operator\ResourceOperator', $manager->getResourceOperator());
    }
}
