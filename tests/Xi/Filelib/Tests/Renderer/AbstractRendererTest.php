<?php

namespace Xi\Filelib\Tests\Renderer;

use Xi\Filelib\File\File;
use Xi\Filelib\Configuration;

class AbstractRendererTest extends \Xi\Filelib\Tests\TestCase
{

    protected $renderer;

    protected $configuration;

    protected $publisher;

    protected $fiop;

    public function setUp()
    {
        $this->fiop = $this
            ->getMockBuilder('Xi\Filelib\File\FileOperator')
            ->disableOriginalConstructor()
            ->getMock();

        $this->configuration = $this->getConfigurationWithMockedObjects();
        $this->publisher = $this->configuration->getPublisher();

        $this->renderer = $this
            ->getMockBuilder('Xi\Filelib\Renderer\AbstractRenderer')
            ->setConstructorArgs(
                array(
                    $this->configuration,
                    $this->fiop,
                )
            )
            ->setMethods(array('render'))
            ->getMock();
    }

    /**
     * @test
     * @todo This should be a protected method (refuctor away later)
     */
    public function mergeOptionsShouldReturnSanitizedResult()
    {

        $expected = array(
            'version' => 'original',
            'download' => false,
            'track' => false,
        );

        $options = array();

        $this->assertEquals($expected, $this->renderer->mergeOptions($options));

        $expected = array(
            'version' => 'orignaluss',
            'download' => false,
            'impossible' => 'impossibru',
            'track' => true,
        );

        $options = array(
            'version' => 'orignaluss',
            'impossible' => 'impossibru',
            'track' => true,
        );

        $this->assertEquals($expected, $this->renderer->mergeOptions($options));

    }

    /**
     * @test
     */
    public function getUrlShouldDelegateToPublisherWhenUsingOriginalVersion()
    {
        $file = File::create(array('id' => 1));

        $this->publisher
            ->expects($this->once())
            ->method('getUrl')
            ->with($this->equalTo($file));

        $this->renderer->getUrl($file, array('version' => 'original'));
    }

    /**
     * @test
     */
    public function getUrlShouldDelegateToPublisherWhenUsingNonOriginalVersion()
    {
        $file = File::create(array('id' => 1));

        $vp = $this->getMockForAbstractClass('Xi\Filelib\Plugin\VersionProvider\VersionProvider');

        $this->fiop->expects($this->once())->method('getVersionProvider')
            ->with($this->equalTo($file), $this->equalTo('lussen'))
            ->will($this->returnValue($vp));

        $this->publisher->expects($this->once())->method('getUrlVersion')->with($this->equalTo($file), $this->equalTo('lussen'), $this->equalTo($vp));

        $this->renderer->getUrl($file, array('version' => 'lussen'));

    }

}
