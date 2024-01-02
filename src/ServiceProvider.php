<?php

namespace BladeStyle;

use BladeStyle\Commands\StyleCacheCommand;
use BladeStyle\Commands\StyleClearCommand;
use BladeStyle\Components\StyleComponent;
use BladeStyle\Components\StylesComponent;
use BladeStyle\Engines\CompilerEngine;
use BladeStyle\Engines\EngineResolver;
use BladeStyle\Engines\MinifierEngine;
use BladeStyle\Minifier\MullieMinifier;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\File;
use Illuminate\Support\ServiceProvider as LaravelServiceProvider;

class ServiceProvider extends LaravelServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->registerPublishes();

        if (! config('style.compiled') && $this->app->runningInConsole()) {
            return;
        }

        $this->registerMinifier();
        $this->registerMinifierEngine();

        $this->registerCompiler();
        $this->registerEngineResolver();

        $this->registerStyleClearCommand();
        $this->registerStyleCacheCommand();

        $this->registerFactory();

        $this->registerBladeComponents();

        $this->app->register(ViewServiceProvider::class);
    }

    /**
     * Register blade components.
     *
     * @return void
     */
    public function registerBladeComponents()
    {
        Blade::component('style', StyleComponent::class);
        Blade::component('styles', StylesComponent::class);
    }

    /**
     * Register minifier.
     *
     * @return void
     */
    protected function registerMinifier()
    {
        $this->app->singleton('style.minifier.mullie', fn($app) => new MullieMinifier());
    }

    /**
     * Register minifier engine.
     *
     * @return void
     */
    protected function registerMinifierEngine()
    {
        $this->app->singleton('style.engine.minifier', fn($app) => new MinifierEngine($app['style.minifier.mullie']));
    }

    /**
     * Register style engine resolver.
     *
     * @return void
     */
    protected function registerEngineResolver()
    {
        $this->app->singleton('style.engine.resolver', function ($app) {
            $resolver = new EngineResolver();

            foreach (config('style.compiler') as $binding => $abstracts) {
                foreach ($abstracts as $abstract) {
                    $this->registerCompilerEngine($resolver, $binding, $abstract);
                }
            }

            return $resolver;
        });
    }

    /**
     * Register style factory.
     *
     * @return void
     */
    public function registerFactory()
    {
        $this->app->singleton('style.factory', function ($app) {
            $resolver = $app['style.engine.resolver'];

            return new Factory($resolver);
        });

        $this->app->alias('style.factory', Factory::class);
    }

    /**
     * Register style compiler.
     *
     * @return void
     */
    protected function registerCompiler()
    {
        foreach (config('style.compiler') as $compiler => $abstracts) {
            $this->app->singleton($compiler, fn() => new $compiler(
                $this->app['style.engine.minifier'],
                $this->app['files'],
                $this->app['config']['style.compiled'],
            ));
        }
    }

    /**
     * Register style compiler.
     *
     * @param  \BladeStyle\Engines\EngineResolver $resolver
     * @param  string                             $binding
     * @param  string                             $abstract
     * @return void
     */
    protected function registerCompilerEngine($resolver, $binding, $abstract)
    {
        $resolver->register($abstract, fn() => new CompilerEngine($this->app[$binding]));
    }

    /**
     * Register the command.
     *
     * @return void
     */
    protected function registerStyleClearCommand()
    {
        $this->app->singleton('command.style.clear', fn($app) => new StyleClearCommand($app['files']));

        $this->commands(['command.style.clear']);
    }

    /**
     * Register the command.
     *
     * @return void
     */
    protected function registerStyleCacheCommand()
    {
        $this->app->singleton('command.style.cache', fn($app) => new StyleCacheCommand($app['style.factory']));

        $this->commands(['command.style.cache']);
    }

    /**
     * Register publishes.
     *
     * @return void
     */
    public function registerPublishes()
    {
        $this->publishes([
            __DIR__.'/../storage/' => storage_path('framework/styles'),
        ], 'storage');

        $this->publishes([
            __DIR__.'/../config/style.php' => config_path('style.php'),
        ], 'config');

        if (! File::exists(storage_path('framework/styles'))) {
            File::copyDirectory(__DIR__.'/../storage/', storage_path('framework/styles'));
        }

        $this->mergeConfigFrom(
            __DIR__.'/../config/style.php',
            'style'
        );
    }
}
