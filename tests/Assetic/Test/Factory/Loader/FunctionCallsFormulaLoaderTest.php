<?php

/*
 * This file is part of the Assetic package, an OpenSky project.
 *
 * (c) 2010-2014 OpenSky Project Inc
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Assetic\Test\Factory\Loader;

use Assetic\Factory\AssetFactory;
use Assetic\Factory\Loader\FunctionCallsFormulaLoader;
use Assetic\Factory\Resource\FileResource;

class FunctionCallsFormulaLoaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider getJavascriptInputs
     */
    public function testInput($function, $inputs, $name, $expected)
    {
        $resource = $this->getMockBuilder(\Assetic\Factory\Resource\ResourceInterface::class)->getMock();
        $factory = $this->getMockBuilder(\Assetic\Factory\AssetFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $resource->expects($this->once())
            ->method('getContent')
            ->will($this->returnValue('<?php '.$function.'('.$inputs.') ?>'));
        $factory->expects($this->once())
            ->method('generateAssetName')
            ->will($this->returnValue($name));

        $loader = new FunctionCallsFormulaLoader($factory);
        $formulae = $loader->load($resource);

        $this->assertEquals($expected, $formulae);
    }

    public function getJavascriptInputs()
    {
        return [
            ['assetic_javascripts', '"js/core.js"',        'asdf', ['asdf' => [['js/core.js'], [], ['debug' => false, 'output' => 'js/*.js', 'name' => 'asdf']]]],
            ['assetic_javascripts', "'js/core.js'",        'asdf', ['asdf' => [['js/core.js'], [], ['debug' => false, 'output' => 'js/*.js', 'name' => 'asdf']]]],
            ['assetic_javascripts', "array('js/core.js')", 'asdf', ['asdf' => [['js/core.js'], [], ['debug' => false, 'output' => 'js/*.js', 'name' => 'asdf']]]],
            ['assetic_javascripts', 'array("js/core.js")', 'asdf', ['asdf' => [['js/core.js'], [], ['debug' => false, 'output' => 'js/*.js', 'name' => 'asdf']]]],
            ['assetic_image',       '"images/logo.gif"',   'asdf', ['asdf' => [['images/logo.gif'], [], ['debug' => false, 'output' => 'images/*', 'name' => 'asdf']]]],
        ];
    }

    public function testComplexFormula()
    {
        $factory  = new AssetFactory(__DIR__.'/templates', true);
        $loader   = new FunctionCallsFormulaLoader($factory);
        $resource = new FileResource(__DIR__.'/templates/debug.php');
        $formulae = $loader->load($resource);

        $this->assertEquals([
            'test123' => [
                ['foo.css', 'bar.css'],
                ['?foo', 'bar'],
                ['name' => 'test123', 'output' => 'css/packed.css', 'debug' => true],
            ],
        ], $formulae);
    }
}
