<?php

namespace Sarvarov\LaravelModules;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class ModuleServiceProdiver extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     * @throws \Throwable
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/config.php' => config_path('modules.php'),
        ]);

        $this->bootModules();
    }

    /**
     * Returns module list.
     *
     * @return array
     */
    protected function getModules()
    {
        return config('modules.list', []);
    }

    /**
     * Returns modules path.
     *
     * @return string
     */
    protected function getModulesPath()
    {
        return rtrim(base_path(config('modules.path')), '/');
    }

    /**
     * Returns module folder namespace.
     *
     * @return string
     */
    protected function getNamespace()
    {
        return trim(config('modules.namespace'), '\\');
    }

    /**
     * Returns module path.
     *
     * @param int|string $key
     * @param string|array $params
     * @return Collection
     */
    protected function getModulePath($key, $params)
    {
        $rawModulePath = is_array($params) ? $key : $params;

        return collect(
            explode('.', str_replace('/', '.', $rawModulePath))
        );
    }

    /**
     * Booting modules.
     *
     * @throws \Throwable
     */
    protected function bootModules()
    {
        $modules = $this->getModules();
        $modulesPath = $this->getModulesPath();
        $modulesNamespace = $this->getNamespace();

        foreach ($modules as $moduleKey => $moduleParams) {
            // Get module path.
            $modulePath = $this->getModulePath($moduleKey, $moduleParams);

            $fullModulePath = "$modulesPath\\" . $modulePath->implode('\\');

            throw_unless(
                is_dir($fullModulePath),
                \RuntimeException::class,
                'Module `' . $modulePath->last() . '` not found!'
            );

            // Get module namespace.
            $moduleNamespace = "$modulesNamespace\\" . $modulePath->implode('\\');

            // Initialize module routes.
            $this->bootModuleRoutes(
                $fullModulePath, $moduleNamespace, $moduleParams
            );
        }
    }

    /**
     * Booting all module routes.
     *
     * @param string $path
     * @param string $namespace
     * @param array|string $params
     * @return false
     */
    protected function bootModuleRoutes($path, $namespace, $params)
    {
        if (! $routes = data_get($params, 'routes', [])) {
            return false;
        }

        $routesPath = $path . '\\Routes';

        foreach ($routes as $route) {
            Route::namespace($namespace . '\\Controllers')
                ->prefix(
                    $params['prefix'] ?? $this->guessPrefixName($namespace)
                )
                ->group("$routesPath\\$route.php");
        }

        return true;
    }

    /**
     * Guess prefix name.
     *
     * For example:
     * App\Modules\Front\BlogPost -> blog-posts
     *
     * @param $namespace
     * @return string
     */
    protected function guessPrefixName($namespace)
    {
        return \Str::snake(
            \Str::plural(class_basename($namespace)), '-'
        );
    }
}
