<?php

/*
 * This file is part of the Assetic package, an OpenSky project.
 *
 * (c) 2010-2014 OpenSky Project Inc
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Assetic\Test\Filter;

use Assetic\Asset\StringAsset;
use Assetic\Filter\CssRewriteFilter;

class CssRewriteFilterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider provideUrls
     */
    public function testUrls($format, $sourcePath, $targetPath, $inputUrl, $expectedUrl)
    {
        $asset = new StringAsset(sprintf($format, $inputUrl), [], null, $sourcePath);
        $asset->setTargetPath($targetPath);
        $asset->load();

        $filter = new CssRewriteFilter();
        $filter->filterLoad($asset);
        $filter->filterDump($asset);

        $this->assertEquals(sprintf($format, $expectedUrl), $asset->getContent(), '->filterDump() rewrites relative urls');
    }

    public function provideUrls()
    {
        return [
            // url variants
            ['body { background: url(%s); }', 'css/body.css', 'css/build/main.css', '../images/bg.gif', '../../images/bg.gif'],
            ['body { background: url("%s"); }', 'css/body.css', 'css/build/main.css', '../images/bg.gif', '../../images/bg.gif'],
            ['body { background: url(\'%s\'); }', 'css/body.css', 'css/build/main.css', '../images/bg.gif', '../../images/bg.gif'],

            //url with data:
            ['body { background: url(\'%s\'); }', 'css/body.css', 'css/build/main.css', 'data:image/png;base64,abcdef=', 'data:image/png;base64,abcdef='],
            ['body { background: url(\'%s\'); }', 'css/body.css', 'css/build/main.css', '../images/bg-data:.gif', '../../images/bg-data:.gif'],

            // @import variants
            ['@import "%s";', 'css/imports.css', 'css/build/main.css', 'import.css', '../import.css'],
            ['@import url(%s);', 'css/imports.css', 'css/build/main.css', 'import.css', '../import.css'],
            ['@import url("%s");', 'css/imports.css', 'css/build/main.css', 'import.css', '../import.css'],
            ['@import url(\'%s\');', 'css/imports.css', 'css/build/main.css', 'import.css', '../import.css'],

            // path diffs
            ['body { background: url(%s); }', 'css/body/bg.css', 'css/build/main.css', '../../images/bg.gif', '../../images/bg.gif'],
            ['body { background: url(%s); }', 'css/body.css', 'main.css', '../images/bg.gif', 'images/bg.gif'],
            ['body { background: url(%s); }', 'body.css', 'css/main.css', 'images/bg.gif', '../images/bg.gif'],
            ['body { background: url(%s); }', 'source/css/body.css', 'output/build/main.css', '../images/bg.gif', '../../source/images/bg.gif'],
            ['body { background: url(%s); }', 'css/body.css', 'css/build/main.css', '//example.com/images/bg.gif', '//example.com/images/bg.gif'],

            // url diffs
            ['body { background: url(%s); }', 'css/body.css', 'css/build/main.css', 'http://foo.com/bar.gif', 'http://foo.com/bar.gif'],
            ['body { background: url(%s); }', 'css/body.css', 'css/build/main.css', '/images/foo.gif', '/images/foo.gif'],
            ['body { background: url(%s); }', 'css/body.css', 'css/build/main.css', 'http://foo.com/images/foo.gif', 'http://foo.com/images/foo.gif'],

            // IE AlphaImageLoader filter
            ['.fix { filter: progid:DXImageTransform.Microsoft.AlphaImageLoader(src=\'%s\'); }', 'css/ie.css', 'css/build/ie.css', '../images/fix.png', '../../images/fix.png'],
        ];
    }

    /**
     * @dataProvider provideMultipleUrls
     */
    public function testMultipleUrls($format, $sourcePath, $targetPath, $inputUrl1, $inputUrl2, $expectedUrl1, $expectedUrl2)
    {
        $asset = new StringAsset(sprintf($format, $inputUrl1, $inputUrl2), [], null, $sourcePath);
        $asset->setTargetPath($targetPath);
        $asset->load();

        $filter = new CssRewriteFilter();
        $filter->filterLoad($asset);
        $filter->filterDump($asset);

        $this->assertEquals(sprintf($format, $expectedUrl1, $expectedUrl2), $asset->getContent(), '->filterDump() rewrites relative urls');
    }

    public function provideMultipleUrls()
    {
        return [
            // multiple url
            ['body { background: url(%s); background: url(%s); }', 'css/body.css', 'css/build/main.css', '../images/bg.gif', '../images/bg2.gif', '../../images/bg.gif', '../../images/bg2.gif'],
            ["body { background: url(%s);\nbackground: url(%s); }", 'css/body.css', 'css/build/main.css', '../images/bg.gif', '../images/bg2.gif', '../../images/bg.gif', '../../images/bg2.gif'],

            // multiple import
            ['@import "%s"; @import "%s";', 'css/imports.css', 'css/build/main.css', 'import.css', 'import2.css', '../import.css', '../import2.css'],
            ["@import \"%s\";\n@import \"%s\";", 'css/imports.css', 'css/build/main.css', 'import.css', 'import2.css', '../import.css', '../import2.css'],

            // mixed urls and imports
            ['@import "%s"; body { background: url(%s); }', 'css/body.css', 'css/build/main.css', 'import.css', '../images/bg2.gif', '../import.css', '../../images/bg2.gif'],
            ["@import \"%s\";\nbody { background: url(%s); }", 'css/body.css', 'css/build/main.css', 'import.css', '../images/bg2.gif', '../import.css', '../../images/bg2.gif'],
        ];
    }

    public function testNoTargetPath()
    {
        $content = 'body { background: url(foo.gif); }';

        $asset = new StringAsset($content);
        $asset->load();

        $filter = new CssRewriteFilter();
        $filter->filterDump($asset);

        $this->assertEquals($content, $asset->getContent(), '->filterDump() urls are not changed without urls');
    }

    public function testExternalSource()
    {
        $asset = new StringAsset('body { background: url(../images/bg.gif); }', [], 'http://www.example.com', 'css/main.css');
        $asset->setTargetPath('css/packed/main.css');
        $asset->load();

        $filter = new CssRewriteFilter();
        $filter->filterDump($asset);

        $this->assertContains('http://www.example.com/images/bg.gif', $asset->getContent(), '->filterDump() rewrites references in external stylesheets');
    }

    public function testEmptySrcAttributeSelector()
    {
        $asset = new StringAsset('img[src=""] { border: red; }', [], 'http://www.example.com', 'css/main.css');
        $asset->setTargetPath('css/packed/main.css');
        $asset->load();

        $filter = new CssRewriteFilter();
        $filter->filterDump($asset);

        // no error is thrown
    }

    public function testEmptyUrl()
    {
        $asset = new StringAsset('body { background: url(); }', [], 'http://www.example.com', 'css/main.css');
        $asset->setTargetPath('css/packed/main.css');
        $asset->load();

        $filter = new CssRewriteFilter();
        $filter->filterDump($asset);

        // no error is thrown
    }
}
