<?php

namespace BladeStyle\Compiler;

use BladeStyle\Engines\MinifierEngine;
use Illuminate\Filesystem\Filesystem;
use Illuminate\View\Compilers\CompilerInterface;
use Illuminate\View\Compilers\Compiler as ViewCompiler;

abstract class Compiler extends ViewCompiler implements CompilerInterface
{
    /**
     * Minifier engine.
     *
     * @var \BladeStyle\Engines\MinifierEngine
     */
    protected $engine;

    /**
     * Compile style string.
     *
     * @param string|null $string
     * @return string
     */
    abstract public function compileString($string);

    /**
     * Create a new compiler instance.
     *
     * @param  \BladeStyle\Engines\MinifierEngine $engine
     * @param  \Illuminate\Filesystem\Filesystem  $files
     * @param  string  $cachePath
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(MinifierEngine $engine, Filesystem $files, $cachePath)
    {
        parent::__construct($files, $cachePath);

        $this->engine = $engine;
    }

    /**
     * Get the path to the compiled version of a script.
     *
     * @param  string  $path
     * @return string
     */
    public function getCompiledPath($path)
    {
        return $this->cachePath . '/' . sha1($path) . '.css';
    }

    /**
     * Compile the style at the given path.
     *
     * @param  string  $path
     * @return void
     */
    public function compile($path)
    {
        if (!$raw = $this->getRaw($path)) {
            return;
        }

        $css = $this->compileString($raw);

        if (config('style.minify')) {
            $css = $this->engine->minify($css);
        }

        $this->files->put(
            $this->getCompiledPath($path),
            $css
        );
    }

    /**
     * Get raw style from path.
     *
     * @param string $path
     * @return string|null
     */
    public function getRaw($path)
    {
        return $this->getStyleFromString(
            $this->files->get($path)
        );
    }

    /**
     * Get style from string.
     *
     * @param string|null $string
     * @return string
     */
    protected function getStyleFromString(string $string)
    {
        preg_match('/<x-style [^>]*>(.|\n)*?<\/x-style>/', $string, $matches);

        if (empty($matches)) {
            preg_match('/<x-style>(.|\n)*?<\/x-style>/', $string, $matches);
        }

        if (empty($matches)) {
            return;
        }

        return preg_replace('/<[^>]*>/', '', $matches[0]);
    }
}
