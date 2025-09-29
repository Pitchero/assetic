<?php

/*
 * This file is part of the Assetic package, an OpenSky project.
 *
 * (c) 2010-2014 OpenSky Project Inc
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Assetic\Test\Extension\Twig;

use Assetic\Factory\AssetFactory;
use Assetic\Extension\Twig\AsseticExtension;
use Assetic\Extension\Twig\TwigFormulaLoader;
use Twig\Environment;
use Twig\Loader\ArrayLoader;

class TwigFormulaLoaderTest extends \PHPUnit_Framework_TestCase
{
    private $am;
    private $fm;
    /**
     * @var TwigFormulaLoader
     */
    private $loader;

    protected function setUp()
    {
        $this->am = $this->getMockBuilder(\Assetic\AssetManager::class)->getMock();
        $this->fm = $this->getMockBuilder(\Assetic\FilterManager::class)->getMock();

        $factory = new AssetFactory(__DIR__.'/templates');
        $factory->setAssetManager($this->am);
        $factory->setFilterManager($this->fm);

        $twig = new Environment(new ArrayLoader([]));
        $twig->addExtension(new AsseticExtension($factory, [
            'some_func' => [
                'filter' => 'some_filter',
                'options' => ['output' => 'css/*.css'],
            ],
        ]));

        $this->loader = new TwigFormulaLoader($twig);
    }

    protected function tearDown()
    {
        $this->am = null;
        $this->fm = null;
    }

    public function testMixture()
    {
        $asset = $this->getMockBuilder(\Assetic\Asset\AssetInterface::class)->getMock();

        $expected = [
            'mixture' => [
                ['foo', 'foo/*', '@foo'],
                [],
                [
                    'output'  => 'packed/mixture',
                    'name'    => 'mixture',
                    'debug'   => false,
                    'combine' => null,
                    'vars'    => [],
                ],
            ],
        ];

        $resource = $this->getMockBuilder(\Assetic\Factory\Resource\ResourceInterface::class)->getMock();
        $resource->expects($this->once())
            ->method('getContent')
            ->will($this->returnValue(file_get_contents(__DIR__.'/templates/mixture.twig')));
        $this->am->expects($this->any())
            ->method('get')
            ->with('foo')
            ->will($this->returnValue($asset));

        $formulae = $this->loader->load($resource);
        $this->assertEquals($expected, $formulae);
    }

    public function testFunction()
    {
        $expected = [
            'my_asset' => [
                ['path/to/asset'],
                ['some_filter'],
                ['output' => 'css/*.css', 'name' => 'my_asset'],
            ],
        ];

        $resource = $this->getMockBuilder(\Assetic\Factory\Resource\ResourceInterface::class)->getMock();
        $resource->expects($this->once())
            ->method('getContent')
            ->will($this->returnValue(file_get_contents(__DIR__.'/templates/function.twig')));

        $formulae = $this->loader->load($resource);
        $this->assertEquals($expected, $formulae);
    }

    public function testUnclosedTag()
    {
        $resource = $this->getMockBuilder(\Assetic\Factory\Resource\ResourceInterface::class)->getMock();
        $resource->expects($this->once())
            ->method('getContent')
            ->will($this->returnValue(file_get_contents(__DIR__.'/templates/unclosed_tag.twig')));

        $formulae = $this->loader->load($resource);
        $this->assertEquals([], $formulae);
    }

    public function testEmbeddedTemplate()
    {
        $expected = [
            'image' => [
                ['images/foo.png'],
                [],
                [
                    'name'    => 'image',
                    'debug'   => true,
                    'vars'    => [],
                    'output'  => 'images/foo.png',
                    'combine' => false,
                ],
            ],
        ];

        $resource = $this->getMockBuilder(\Assetic\Factory\Resource\ResourceInterface::class)->getMock();
        $resource->expects($this->once())
            ->method('getContent')
            ->will($this->returnValue(file_get_contents(__DIR__.'/templates/embed.twig')));

        $formulae = $this->loader->load($resource);
        $this->assertEquals($expected, $formulae);
    }
}
