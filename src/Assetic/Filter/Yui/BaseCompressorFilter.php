<?php

/*
 * This file is part of the Assetic package, an OpenSky project.
 *
 * (c) 2010-2014 OpenSky Project Inc
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Assetic\Filter\Yui;

use Assetic\Asset\AssetInterface;
use Assetic\Exception\FilterException;
use Assetic\Filter\BaseProcessFilter;
use Assetic\Util\FilesystemUtils;

/**
 * Base YUI compressor filter.
 *
 * @link http://developer.yahoo.com/yui/compressor/
 * @author Kris Wallsmith <kris.wallsmith@gmail.com>
 */
abstract class BaseCompressorFilter extends BaseProcessFilter
{
    private $jarPath;
    private $javaPath;
    private $charset;
    private $lineBreak;
    private $stackSize;

    public function __construct($jarPath, $javaPath = '/usr/bin/java')
    {
        $this->jarPath = $jarPath;
        $this->javaPath = $javaPath;
    }

    public function setCharset($charset)
    {
        $this->charset = $charset;
    }

    public function setLineBreak($lineBreak)
    {
        $this->lineBreak = $lineBreak;
    }

    public function setStackSize($stackSize)
    {
        $this->stackSize = $stackSize;
    }

    public function filterLoad(AssetInterface $asset)
    {
    }

    /**
     * Compresses a string.
     *
     * @param string $content The content to compress
     * @param string $type    The type of content, either "js" or "css"
     * @param array  $options An indexed array of additional options
     *
     * @return string The compressed content
     */
    protected function compress($content, $type, $options = [])
    {
        $args = [$this->javaPath];

        if (null !== $this->stackSize) {
            $args[] = '-Xss'.$this->stackSize;
        }

        $args[] = '-jar';
        $args[] = $this->jarPath;

        foreach ($options as $option) {
            $args[] = $option;
        }

        if (null !== $this->charset) {
            $args[] = '--charset';
            $args[] = $this->charset;
        }

        if (null !== $this->lineBreak) {
            $args[] = '--line-break';
            $args[] = $this->lineBreak;
        }

        // input and output files
        $tempDir = FilesystemUtils::getTemporaryDirectory();
        $input = tempnam($tempDir, 'assetic_yui_input');
        $output = tempnam($tempDir, 'assetic_yui_output');
        file_put_contents($input, $content);

        $args[] = '-o';
        $args[] = $output;
        $args[] = '--type';
        $args[] = $type;
        $args[] = $input;

        $proc = $this->createProcess($args);
        $code = $proc->run();
        unlink($input);

        if (0 !== $code) {
            if (file_exists($output)) {
                unlink($output);
            }

            throw FilterException::fromProcess($proc)->setInput($content);
        }

        if (!file_exists($output)) {
            throw new \RuntimeException('Error creating output file.');
        }

        $retval = file_get_contents($output);
        unlink($output);

        return $retval;
    }
}
