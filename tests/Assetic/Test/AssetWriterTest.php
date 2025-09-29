<?php

/*
 * This file is part of the Assetic package, an OpenSky project.
 *
 * (c) 2010-2014 OpenSky Project Inc
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Assetic\Test;

use Assetic\Asset\FileAsset;
use Assetic\AssetWriter;

class AssetWriterTest extends \PHPUnit_Framework_TestCase
{
    private $dir;
    /** @var AssetWriter */
    private $writer;

    protected function setUp()
    {
        $this->dir = sys_get_temp_dir().'/assetic_tests_'.random_int(11111, 99999);
        mkdir($this->dir);
        $this->writer = new AssetWriter($this->dir, [
            'locale' => ['en', 'de', 'fr'],
            'browser' => ['ie', 'firefox', 'other'],
            'gzip' => ['gzip', ''],
        ]);
    }

    protected function tearDown()
    {
        array_map('unlink', glob($this->dir.'/*'));
        rmdir($this->dir);
    }

    public function testWriteManagerAssets()
    {
        $asset = $this->getMockBuilder(\Assetic\Asset\AssetInterface::class)->getMock();
        $am = $this->getMockBuilder(\Assetic\AssetManager::class)->getMock();

        $am->expects($this->once())
            ->method('getNames')
            ->will($this->returnValue(['foo']));
        $am->expects($this->once())
            ->method('get')
            ->with('foo')
            ->will($this->returnValue($asset));
        $asset->expects($this->atLeastOnce())
            ->method('getTargetPath')
            ->will($this->returnValue('target_url'));
        $asset->expects($this->once())
            ->method('dump')
            ->will($this->returnValue('content'));
        $asset->expects($this->atLeastOnce())
            ->method('getVars')
            ->will($this->returnValue([]));
        $asset->expects($this->atLeastOnce())
            ->method('getValues')
            ->will($this->returnValue([]));

        $this->writer->writeManagerAssets($am);

        $this->assertFileExists($this->dir.'/target_url');
        $this->assertEquals('content', file_get_contents($this->dir.'/target_url'));
    }

    public function testWriteAssetWithVars()
    {
        $asset = $this->getMockBuilder(\Assetic\Asset\AssetInterface::class)->getMock();
        $asset->expects($this->atLeastOnce())
            ->method('getVars')
            ->will($this->returnValue(['locale']));

        $self = $this;
        $expectedValues = [
            ['locale' => 'en'],
            ['locale' => 'de'],
            ['locale' => 'fr'],
        ];
        $asset->expects($this->exactly(3))
            ->method('setValues')
            ->will($this->returnCallback(function ($values) use ($self, $expectedValues): void {
                static $counter = 0;
                $self->assertEquals($expectedValues[$counter++], $values);
            }));
        $asset->expects($this->exactly(3))
            ->method('getValues')
            ->will($this->returnCallback(function () use ($expectedValues) {
                static $counter = 0;

                return $expectedValues[$counter++];
            }));

        $asset->expects($this->exactly(3))
            ->method('dump')
            ->will($this->onConsecutiveCalls('en', 'de', 'fr'));

        $asset->expects($this->atLeastOnce())
            ->method('getTargetPath')
            ->will($this->returnValue('target.{locale}'));

        $this->writer->writeAsset($asset);

        $this->assertFileExists($this->dir.'/target.en');
        $this->assertFileExists($this->dir.'/target.de');
        $this->assertFileExists($this->dir.'/target.fr');
        $this->assertEquals('en', file_get_contents($this->dir.'/target.en'));
        $this->assertEquals('de', file_get_contents($this->dir.'/target.de'));
        $this->assertEquals('fr', file_get_contents($this->dir.'/target.fr'));
    }

    public function testAssetWithInputVars()
    {
        $asset = new FileAsset(__DIR__.'/Fixture/messages.{locale}.js',
            [], null, null, ['locale']);
        $asset->setTargetPath('messages.{locale}.js');

        $this->writer->writeAsset($asset);

        $this->assertFileExists($this->dir.'/messages.en.js');
        $this->assertFileExists($this->dir.'/messages.de.js');
        $this->assertFileExists($this->dir.'/messages.fr.js');
        $this->assertEquals('var messages = {"text.greeting": "Hello %name%!"};',
            file_get_contents($this->dir.'/messages.en.js'));
        $this->assertEquals('var messages = {"text.greeting": "Hallo %name%!"};',
            file_get_contents($this->dir.'/messages.de.js'));
        $this->assertEquals('var messages = {"text.greet": "All\u00f4 %name%!"};',
            file_get_contents($this->dir.'/messages.fr.js'));
    }
}
