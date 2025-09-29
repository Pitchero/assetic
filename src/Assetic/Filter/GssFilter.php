<?php

/*
 * This file is part of the Assetic package, an OpenSky project.
 *
 * (c) 2010-2014 OpenSky Project Inc
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Assetic\Filter;

use Assetic\Asset\AssetInterface;
use Assetic\Exception\FilterException;
use Assetic\Util\FilesystemUtils;

/**
 * Filter for the Google Closure Stylesheets Compiler JAR.
 *
 * @link http://code.google.com/p/closure-stylesheets/
 * @author Matthias Krauser <matthias@krauser.eu>
 */
class GssFilter extends BaseProcessFilter
{
    private $jarPath;
    private $javaPath;
    private $allowUnrecognizedFunctions;
    private $allowedNonStandardFunctions;
    private $copyrightNotice;
    private $define;
    private $gssFunctionMapProvider;
    private $inputOrientation;
    private $outputOrientation;
    private $prettyPrint;

    public function __construct($jarPath, $javaPath = '/usr/bin/java')
    {
        $this->jarPath = $jarPath;
        $this->javaPath = $javaPath;
    }

    public function setAllowUnrecognizedFunctions($allowUnrecognizedFunctions)
    {
        $this->allowUnrecognizedFunctions = $allowUnrecognizedFunctions;
    }

    public function setAllowedNonStandardFunctions($allowNonStandardFunctions)
    {
        $this->allowedNonStandardFunctions = $allowNonStandardFunctions;
    }

    public function setCopyrightNotice($copyrightNotice)
    {
        $this->copyrightNotice = $copyrightNotice;
    }

    public function setDefine($define)
    {
        $this->define = $define;
    }

    public function setGssFunctionMapProvider($gssFunctionMapProvider)
    {
        $this->gssFunctionMapProvider = $gssFunctionMapProvider;
    }

    public function setInputOrientation($inputOrientation)
    {
        $this->inputOrientation = $inputOrientation;
    }

    public function setOutputOrientation($outputOrientation)
    {
        $this->outputOrientation = $outputOrientation;
    }

    public function setPrettyPrint($prettyPrint)
    {
        $this->prettyPrint = $prettyPrint;
    }

    public function filterLoad(AssetInterface $asset)
    {
        $cleanup = [];

        $args = [
            $this->javaPath,
            '-jar',
            $this->jarPath,
        ];

        if (null !== $this->allowUnrecognizedFunctions) {
            $args[] = '--allow-unrecognized-functions';
        }

        if (null !== $this->allowedNonStandardFunctions) {
            $args[] = '--allowed_non_standard_functions';
            $args[] = $this->allowedNonStandardFunctions;
        }

        if (null !== $this->copyrightNotice) {
            $args[] = '--copyright-notice';
            $args[] = $this->copyrightNotice;
        }

        if (null !== $this->define) {
            $args[] = '--define';
            $args[] = $this->define;
        }

        if (null !== $this->gssFunctionMapProvider) {
            $args[] = '--gss-function-map-provider';
            $args[] = $this->gssFunctionMapProvider;
        }

        if (null !== $this->inputOrientation) {
            $args[] = '--input-orientation';
            $args[] = $this->inputOrientation;
        }

        if (null !== $this->outputOrientation) {
            $args[] = '--output-orientation';
            $args[] = $this->outputOrientation;
        }

        if (null !== $this->prettyPrint) {
            $args[] = '--pretty-print';
        }

        $cleanup[] = $input = FilesystemUtils::createTemporaryFile('gss');
        $args[] = $input;
        file_put_contents($input, $asset->getContent());

        $proc = $this->createProcess($args);
        $code = $proc->run();
        array_map('unlink', $cleanup);

        if (0 !== $code) {
            throw FilterException::fromProcess($proc)->setInput($asset->getContent());
        }

        $asset->setContent($proc->getOutput());
    }

    public function filterDump(AssetInterface $asset)
    {
    }
}
