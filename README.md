# laravel-modules
Lightweight assistant for implementing modular architecture in Laravel.

```
app
- Modules
-- Frontend
--- Controllers
--- Migrations
--- Translations
--- Views
--- Routes
--- Models
```

## Installation
1. `composer require sarvarov/laravel-modules`
2. php artisan `vendor:publish --provider="Sarvarov\LaravelModules\ModuleServiceProvider"`.
3. In `config/modules.php` add your created modules in `list` parameter, for example:
```
'list' => [
    'Frontend/Page' => [
        'prefix' => 'pages',
        'routes' => ['web'],
    ],
],
```
In example above it will load all routes from `app/Modules/Frontend/Page/Routes` folder. `\Route::get('/', 'PageController@index');` will route to `app/Modules/Frontend/Page/Controllers/PageController`.
