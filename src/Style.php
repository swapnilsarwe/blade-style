<?php

namespace BladeStyle;

use BladeStyle\Engines\CompilerEngine;

class Style
{
    /**
     * Style engine.
     *
     * @var \BladeStyle\Engines\CompilerEngine
     */
    protected $engine;

    /**
     * Create new style instance.
     *
     * @param string                             $path
     * @param \BladeStyle\Engines\CompilerEngine $compiler
     */
    public function __construct(/**
     * Path where style is located.
     */
    protected $path, CompilerEngine $engine)
    {
        $this->engine = $engine;
    }

    /**
     * Render style.
     *
     * @return string
     */
    public function render()
    {
        return $this->engine->get($this->path);
    }

    /**
     * Get compiler.
     *
     * @return \BladeStyle\Compiler\Compiler
     */
    public function getCompiler()
    {
        return $this->engine->getCompiler();
    }
}
