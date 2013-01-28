<?php

namespace Xi\Filelib\Tests\Operator;

use Xi\Filelib\Operator\AbstractOperator;

class AbstractOperatorTest extends \Xi\Filelib\Tests\TestCase
{
    private $op;
    private $configuration;

    public function setUp()
    {
        $this->configuration = $this->getConfigurationWithMockedObjects();
        $this->op = $this->getMockedOperator();
    }

    /**
     * @test
     */
    public function classShouldExist()
    {
        $this->assertTrue(class_exists('Xi\Filelib\Operator\AbstractOperator'));
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    public function getMockedOperator($methods = array())
    {

        $op = $this
            ->getMockBuilder('Xi\Filelib\Operator\AbstractOperator')
            ->setMethods($methods)
            ->setConstructorArgs(array($this->configuration))
            ->getMockForAbstractClass();

        return $op;
    }


    /**
     * @test
     */
    public function gettersShouldWork()
    {
        $configuration = $this->configuration;
        $op = $this->getMockedOperator();

        $this->assertSame($configuration->getBackend(), $op->getBackend());
        $this->assertSame($configuration->getAcl(), $op->getAcl());
        $this->assertSame($configuration->getStorage(), $op->getStorage());
        $this->assertSame($configuration->getPublisher(), $op->getPublisher());
        $this->assertSame($configuration->getEventDispatcher(), $op->getEventDispatcher());
    }

    /**
     *
     * @test
     */
    public function generateUuidShouldGenerateUuid()
    {
        $op = $this->getMockedOperator();
        $uuid = $op->generateUuid();
        $this->assertRegExp("/^\w{8}-\w{4}-\w{4}-\w{4}-\w{12}$/", $uuid);
    }

}
