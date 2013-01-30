<?php

namespace Xi\Filelib\Tests\Command;

use Xi\Filelib\Command\CommandDefinition;
use Xi\Filelib\Command\CommandFactory;

class CommandDefinitionTest extends \Xi\Filelib\Tests\TestCase
{
    /**
     * @test
     */
    public function classShouldExist()
    {
        $this->assertTrue(class_exists('Xi\Filelib\Command\CommandDefinition'));
    }

    /**
     * @test
     */
    public function shouldInitializeCorrectly()
    {
        $definition = new CommandDefinition(
            'tussi',
            'Lussen\Sie\Tussen',
            CommandFactory::STRATEGY_SYNCHRONOUS
        );

        $this->assertEquals('tussi', $definition->getName());
        $this->assertEquals('Lussen\Sie\Tussen', $definition->getClass());
        $this->assertEquals(CommandFactory::STRATEGY_SYNCHRONOUS, $definition->getStrategy());
    }

    /**
     * @test
     */
    public function invalidStrategyThrowsException()
    {
        $this->setExpectedException('InvalidArgumentException', "Invalid command strategy 'consummatore'");

        $definition = new CommandDefinition(
            'tussi',
            'Lussen\Sie\Tussen',
            'consummatore'
        );
    }
}
