# laravel-modules
Lightweight assistant for implementing modular architecture in Laravel.

## Installation
1. `composer require sarvarov/laravel-modules`
2. php artisan `vendor:publish --provider="Sarvarov\LaravelModules\ModuleServiceProvider"`.
3. In `config/modules.php` add your created modules in `list` parameter, for example:
```
'list' => [
    'Front/Post' => [
       'prefix' => 'posts',
   ],
],
```
In example above it will load all routes from `app/Modules/Front/Post/Routes` folder.
If you have `web.php` in that folder with `\Route::get('/', 'PostController@index');` it will route to `app/Modules/Front/Post/Controllers/PostController`.
