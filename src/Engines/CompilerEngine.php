<?php

namespace BladeStyle\Engines;

use BladeStyle\Compiler\Compiler;
use BladeStyle\Contracts\StyleEngine;
use Illuminate\Support\Facades\File;

class CompilerEngine implements StyleEngine
{
    /**
     * Style Compiler.
     *
     * @var \Illuminate\View\Compilers\CompilerInterface
     */
    protected $compiler;

    /**
     * Create a new Blade view engine instance.
     *
     * @return void
     */
    public function __construct(Compiler $compiler)
    {
        $this->compiler = $compiler;
    }

    /**
     * Get compiled style from the given path.
     *
     * @param  string $path
     * @return void
     */
    public function get(string $path)
    {
        if ($this->compiler->isExpired($path)) {
            $this->compiler->compile($path);
        }

        return File::get($this->compiler->getCompiledPath($path));
    }

    /**
     * Get compiler.
     *
     * @return \BladeStyle\Compiler\Compiler
     */
    public function getCompiler()
    {
        return $this->compiler;
    }
}
