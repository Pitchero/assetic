<?php

/*
 * This file is part of the Assetic package, an OpenSky project.
 *
 * (c) 2010-2014 OpenSky Project Inc
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Assetic\Filter\Sass;

use Assetic\Asset\AssetInterface;
use Assetic\Exception\FilterException;
use Assetic\Util\FilesystemUtils;

/**
 * Loads SASS files.
 *
 * @link http://sass-lang.com/
 * @author Kris Wallsmith <kris.wallsmith@gmail.com>
 */
class SassFilter extends BaseSassFilter
{
    const STYLE_NESTED     = 'nested';
    const STYLE_EXPANDED   = 'expanded';
    const STYLE_COMPACT    = 'compact';
    const STYLE_COMPRESSED = 'compressed';

    private $sassPath;
    private $rubyPath;
    private $unixNewlines;
    private $scss;
    private $style;
    private $precision;
    private $quiet;
    private $debugInfo;
    private $lineNumbers;
    private $sourceMap;
    private $cacheLocation;
    private $noCache;
    private $compass;

    public function __construct($sassPath = '/usr/bin/sass', $rubyPath = null)
    {
        $this->sassPath = $sassPath;
        $this->rubyPath = $rubyPath;
        $this->cacheLocation = FilesystemUtils::getTemporaryDirectory();
    }

    public function setUnixNewlines($unixNewlines)
    {
        $this->unixNewlines = $unixNewlines;
    }

    public function setScss($scss)
    {
        $this->scss = $scss;
    }

    public function setStyle($style)
    {
        $this->style = $style;
    }

    public function setPrecision($precision)
    {
        $this->precision = $precision;
    }

    public function setQuiet($quiet)
    {
        $this->quiet = $quiet;
    }

    public function setDebugInfo($debugInfo)
    {
        $this->debugInfo = $debugInfo;
    }

    public function setLineNumbers($lineNumbers)
    {
        $this->lineNumbers = $lineNumbers;
    }

    public function setSourceMap($sourceMap)
    {
        $this->sourceMap = $sourceMap;
    }

    public function setCacheLocation($cacheLocation)
    {
        $this->cacheLocation = $cacheLocation;
    }

    public function setNoCache($noCache)
    {
        $this->noCache = $noCache;
    }

    public function setCompass($compass)
    {
        $this->compass = $compass;
    }

    public function filterLoad(AssetInterface $asset)
    {
        $sassProcessArgs = array($this->sassPath);
        if (null !== $this->rubyPath) {
            $sassProcessArgs = array_merge(explode(' ', $this->rubyPath), $sassProcessArgs);
        }

        if ($dir = $asset->getSourceDirectory()) {
            $sassProcessArgs[] = '--load-path';
            $sassProcessArgs[] = $dir;
        }

        if ($this->unixNewlines) {
            $sassProcessArgs[] = '--unix-newlines';
        }

        if (true === $this->scss || (null === $this->scss && 'scss' == pathinfo($asset->getSourcePath(), PATHINFO_EXTENSION))) {
            $sassProcessArgs[] = '--scss';
        }

        if ($this->style) {
            $sassProcessArgs[] = '--style';
            $sassProcessArgs[] = $this->style;
        }

        if ($this->precision) {
            $sassProcessArgs[] = '--precision';
            $sassProcessArgs[] = $this->precision;
        }

        if ($this->quiet) {
            $sassProcessArgs[] = '--quiet';
        }

        if ($this->debugInfo) {
            $sassProcessArgs[] = '--debug-info';
        }

        if ($this->lineNumbers) {
            $sassProcessArgs[] = '--line-numbers';
        }

        if ($this->sourceMap) {
            $sassProcessArgs[] = '--sourcemap';
        }

        foreach ($this->loadPaths as $loadPath) {
            $sassProcessArgs[] = '--load-path';
            $sassProcessArgs[] = $loadPath;
        }

        if ($this->cacheLocation) {
            $sassProcessArgs[] = '--cache-location';
            $sassProcessArgs[] = $this->cacheLocation;
        }

        if ($this->noCache) {
            $sassProcessArgs[] = '--no-cache';
        }

        if ($this->compass) {
            $sassProcessArgs[] = '--compass';
        }

        // input
        $input = FilesystemUtils::createTemporaryFile('sass');
        $sassProcessArgs[] = $input;
        file_put_contents($input, $asset->getContent());

        $proc = $this->createProcess($sassProcessArgs);
        $code = $proc->run();
        unlink($input);

        if (0 !== $code) {
            throw FilterException::fromProcess($proc)->setInput($asset->getContent());
        }

        $asset->setContent($proc->getOutput());
    }

    public function filterDump(AssetInterface $asset)
    {
    }
}
