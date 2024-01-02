<?php

namespace BladeStyle\Engines;

use Illuminate\View\Engines\CompilerEngine;

// use Facade\Ignition\Views\Engines\CompilerEngine;

class StyleViewCompilerEngine extends CompilerEngine
{
    use Concerns\ManagesViewCompilerEngine;
}
