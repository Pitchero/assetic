<?php

/*
 * This file is part of the Assetic package, an OpenSky project.
 *
 * (c) 2010-2014 OpenSky Project Inc
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Assetic\Test\Util;

use Assetic\Util\VarUtils;

class VarUtilsTest extends \PHPUnit_Framework_TestCase
{
    public function testResolve()
    {
        $template = '{foo}bar';
        $vars = ['foo'];
        $values = ['foo' => 'foo'];

        $this->assertEquals('foobar', VarUtils::resolve($template, $vars, $values));
    }

    /**
     * @dataProvider getCombinationTests
     */
    public function testGetCombinations($vars, $expected)
    {
        $actual = VarUtils::getCombinations(
            $vars,
            [
                'locale'  => ['en', 'de', 'fr'],
                'browser' => ['ie', 'firefox', 'other'],
                'gzip'    => ['gzip', ''],
            ]
        );

        $this->assertEquals($expected, $actual);
    }

    public function getCombinationTests()
    {
        $tests = [];

        // no variables
        $tests[] = [
            [],
            [[]],
        ];

        // one variables
        $tests[] = [
            ['locale'],
            [
                ['locale' => 'en'],
                ['locale' => 'de'],
                ['locale' => 'fr'],
            ],
        ];

        // two variables
        $tests[] = [
            ['locale', 'browser'],
            [
                ['locale' => 'en', 'browser' => 'ie'],
                ['locale' => 'de', 'browser' => 'ie'],
                ['locale' => 'fr', 'browser' => 'ie'],
                ['locale' => 'en', 'browser' => 'firefox'],
                ['locale' => 'de', 'browser' => 'firefox'],
                ['locale' => 'fr', 'browser' => 'firefox'],
                ['locale' => 'en', 'browser' => 'other'],
                ['locale' => 'de', 'browser' => 'other'],
                ['locale' => 'fr', 'browser' => 'other'],
            ],
        ];

        // three variables
        $tests[] = [
            ['locale', 'browser', 'gzip'],
            [
                ['locale' => 'en', 'browser' => 'ie', 'gzip' => 'gzip'],
                ['locale' => 'de', 'browser' => 'ie', 'gzip' => 'gzip'],
                ['locale' => 'fr', 'browser' => 'ie', 'gzip' => 'gzip'],
                ['locale' => 'en', 'browser' => 'firefox', 'gzip' => 'gzip'],
                ['locale' => 'de', 'browser' => 'firefox', 'gzip' => 'gzip'],
                ['locale' => 'fr', 'browser' => 'firefox', 'gzip' => 'gzip'],
                ['locale' => 'en', 'browser' => 'other', 'gzip' => 'gzip'],
                ['locale' => 'de', 'browser' => 'other', 'gzip' => 'gzip'],
                ['locale' => 'fr', 'browser' => 'other', 'gzip' => 'gzip'],
                ['locale' => 'en', 'browser' => 'ie', 'gzip' => ''],
                ['locale' => 'de', 'browser' => 'ie', 'gzip' => ''],
                ['locale' => 'fr', 'browser' => 'ie', 'gzip' => ''],
                ['locale' => 'en', 'browser' => 'firefox', 'gzip' => ''],
                ['locale' => 'de', 'browser' => 'firefox', 'gzip' => ''],
                ['locale' => 'fr', 'browser' => 'firefox', 'gzip' => ''],
                ['locale' => 'en', 'browser' => 'other', 'gzip' => ''],
                ['locale' => 'de', 'browser' => 'other', 'gzip' => ''],
                ['locale' => 'fr', 'browser' => 'other', 'gzip' => ''],
            ],
        ];

        return $tests;
    }
}
