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
        return base_path(
            str_replace('\\', '/', trim(config('modules.path'), '/'))
        );
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

            $fullModulePath = "$modulesPath/" . $modulePath->implode('/');

            throw_unless(
                is_dir($fullModulePath),
                \RuntimeException::class,
                'Module `' . $modulePath->last() . '` not found!'
            );

            // Get module namespace.
            $moduleNamespace = "$modulesNamespace\\" . $modulePath->implode('\\');

            // Initialize module routes.
            $this->bootModule(
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
    protected function bootModule($path, $namespace, $params)
    {
        $prefix = $params['prefix'] ?? $this->guessPrefixName($namespace);

        // Routes
        $routes = data_get($params, 'routes', []);
        $routesPath = $path . '/Routes';

        foreach ($routes as $route) {
            Route::namespace($namespace . '\\Controllers')
                ->group("$routesPath/$route.php");
        }

        // Views
        $this->loadViewsFrom("$path/Views", $prefix);

        // Migrations
        $this->loadMigrationsFrom("$path/Migrations");

        // Translations
        $this->loadTranslationsFrom("$path/Translations", $prefix);
        $this->loadJsonTranslationsFrom("$path/Translations");

        return true;
    }

    /**
     * Guess prefix name.
     *
     * For example:
     * App\Modules\Front\BlogPost -> blog-post
     *
     * @param $namespace
     * @return string
     */
    protected function guessPrefixName($namespace)
    {
        return \Str::snake(
            class_basename($namespace), '-'
        );
    }
}
