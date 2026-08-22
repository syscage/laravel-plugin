# Syscage Laravel Plugin

[![License](https://img.shields.io/packagist/l/syscage/laravel-plugin.svg)](LICENSE.md)
[![PHP Version](https://img.shields.io/badge/php-%5E8.2-777bb4.svg)](composer.json)
[![Laravel](https://img.shields.io/badge/laravel-12%20%7C%2013-ff2d20.svg)](composer.json)

A WordPress-style plugin framework for Laravel 12+ applications. `syscage/laravel-plugin` turns a `plugins/` directory in your host application into a set of independently discoverable, installable, and toggleable Laravel packages — each with its own routes, views, translations, migrations, config, assets, console commands, and dashboard widgets — without ever running `composer dump-autoload`.

This package does not manage your application's modules or its own business logic; it manages the plugins that live inside `plugins/` in your host application.

## Installation

```bash
composer require syscage/laravel-plugin
php artisan vendor:publish --tag=plugin-config
php artisan vendor:publish --tag=plugin-migrations
php artisan migrate
```

## Creating a plugin

```bash
php artisan make:plugin BlogPost --description="Blog integration"
php artisan plugin:install blog-post
```

`make:plugin` scaffolds the full plugin directory structure:

```
plugins/blog-post/
├── src/
│   ├── app/
│   │   ├── Http/{Controllers,Middleware,Requests}/
│   │   ├── Models/
│   │   └── Providers/BlogPluginPluginServiceProvider.php
│   ├── config/config.php
│   ├── database/{migrations,factories,seeders}/
│   ├── lang/
│   ├── public/
│   ├── resources/{css,js,views}/
│   ├── routes/{web,api,console}.php
│   └── Plugin.php
├── composer.json
├── plugin.json
└── README.md
```

`plugin.json` is the plugin's manifest — name, alias, version, its service provider's class name, dependencies, permissions, and sidebar contributions. `src/Plugin.php` extends `Syscage\Plugin\AbstractPlugin` and exposes every path a manager or command needs (`$plugin->viewPath()`, `$plugin->migrationPath()`, ...) plus lifecycle hooks (`install()`, `uninstall()`, `enable()`, `disable()`, `update()`) you can override. The plugin's own service provider extends `Syscage\Plugin\PluginServiceProviderBase`, which auto-wires routes, views, translations, migrations, config, assets, and console commands for you.

## Plugin lifecycle

```bash
php artisan plugin:list                    # every discovered plugin and its state
php artisan plugin:info blog-post        # detailed info about one plugin
php artisan plugin:install blog-post     # creates the DB record, assigns a UUID
php artisan plugin:enable blog-post
php artisan plugin:disable blog-post
php artisan plugin:update blog-post      # syncs the DB to the current manifest version
php artisan plugin:uninstall blog-post
php artisan plugin:delete blog-post      # permanently deletes it: DB record, caches, assets, and files on disk
php artisan plugin:doctor                  # health-check every plugin's providers/dependencies
php artisan plugin:publish blog-post     # (re-)publish a plugin's public assets
```

`requires`/`conflicts` in `plugin.json` are validated (including semver version constraints) before install/enable, and a plugin's loading order is resolved topologically by its dependencies, then by `priority`. Unlike `plugin:uninstall` (which only removes the database record and runs the plugin's `uninstall()` hook), `plugin:delete` removes the plugin from everywhere — it's the one to reach for when you want a plugin fully gone, and it's resilient enough to also clean up a plugin whose directory was already removed by hand, leaving only an orphaned database record and stale caches behind.

### Customizing the plugin record model

Installed-plugin state (UUID, install/enable timestamps, priority, ...) is persisted through `Syscage\Plugin\Models\PluginRecord`, an ordinary Eloquent model. Extend it with your own model — to add relationships, casts, or move it under your app's own namespace — and point the `model` key in `config/plugin.php` at your subclass:

```php
// app/Models//Plugin.php
namespace App\Models\Dashboard\System;

use Syscage\Plugin\Models\PluginRecord;

class Plugin extends PluginRecord
{
    //
}
```

```php
// config/plugin.php
'model' => \App\Models\Dashboard\System\Plugin::class,
```

Every plugin command and manager resolves the record through this config value, so they'll return instances of your subclass instead of the base `PluginRecord`.

## Caching

```bash
php artisan plugin:discover   # rebuild the discovery cache from disk
php artisan plugin:cache      # rebuild discovery + sidebar + frontend caches
php artisan plugin:clear      # remove all compiled caches
```

Generating a new plugin (`make:plugin`) automatically invalidates the discovery cache, so the next Artisan process picks it up without a manual `plugin:discover`.

## Friendly generator aliases

Every plugin-scoped counterpart of Laravel's own generators and database commands is available, redirecting output into the target plugin instead of the host application:

```bash
php artisan make:model-plugin blog-post Post -mfs
php artisan make:controller-plugin blog-post PostController --resource
php artisan migrate-plugin blog-post
php artisan db:seed-plugin blog-post
php artisan route:list-plugin blog-post
php artisan test-plugin blog-post
```

Every alias accepts `{plugin}` as its first argument (the plugin's alias or manifest name) followed by exactly the same arguments and options as the Laravel command it wraps. The full list:

**Generators** — each writes into the target plugin's `src/` tree under its own namespace instead of the host app's `app/`:

| Alias | Wraps |
| --- | --- |
| `make:model-plugin` | `make:model` (its `-m`/`-f`/`-s`/`-c`/`-p`/`-R`/`-a` flags cascade into the matching `*-plugin` generator below) |
| `make:controller-plugin` | `make:controller` |
| `make:middleware-plugin` | `make:middleware` |
| `make:request-plugin` | `make:request` |
| `make:provider-plugin` | `make:provider` |
| `make:policy-plugin` | `make:policy` |
| `make:observer-plugin` | `make:observer` |
| `make:event-plugin` | `make:event` |
| `make:listener-plugin` | `make:listener` |
| `make:mail-plugin` | `make:mail` |
| `make:notification-plugin` | `make:notification` |
| `make:job-plugin` | `make:job` |
| `make:rule-plugin` | `make:rule` |
| `make:resource-plugin` | `make:resource` |
| `make:cast-plugin` | `make:cast` |
| `make:enum-plugin` | `make:enum` |
| `make:factory-plugin` | `make:factory` |
| `make:seeder-plugin` | `make:seeder` |
| `make:migration-plugin` | `make:migration` (`--create`/`--table` are prefixed with the plugin's table prefix) |
| `make:test-plugin` | `make:test` |

**Migrations** — scoped to the target plugin's own `src/database/migrations` directory:

| Alias | Wraps |
| --- | --- |
| `migrate-plugin` | `migrate` |
| `migrate:fresh-plugin` | `migrate:fresh` |
| `migrate:rollback-plugin` | `migrate:rollback` |
| `migrate:refresh-plugin` | `migrate:refresh` |
| `migrate:reset-plugin` | `migrate:reset` |
| `migrate:status-plugin` | `migrate:status` |

**Database**:

| Alias | Wraps |
| --- | --- |
| `db:seed-plugin` | `db:seed` (`--class` defaults to the plugin's own `Database\Seeders\DatabaseSeeder`) |
| `db:wipe-plugin` | `db:wipe` |

**Framework/cache/queue/testing**:

| Alias | Wraps |
| --- | --- |
| `config:cache-plugin` | `config:cache` |
| `route:cache-plugin` | `route:cache` |
| `view:cache-plugin` | `view:cache` |
| `event:cache-plugin` | `event:cache` |
| `route:list-plugin` | `route:list` (filtered to only that plugin's routes) |
| `vendor:publish-plugin` | `vendor:publish` |
| `optimize-plugin` | `optimize` |
| `queue:work-plugin` | `queue:work` |
| `test-plugin` | `test` (runs only that plugin's own `tests/` directory) |

> `config:cache-plugin`, `route:cache-plugin`, `view:cache-plugin`, `event:cache-plugin`, and `optimize-plugin` still require `{plugin}` for command-family consistency, but rebuild the shared, application-wide cache — they don't scope it to one plugin.

`db:seed-plugin` relies on that `DatabaseSeeder` existing; `make:plugin` scaffolds an empty one for you at `src/database/seeders/DatabaseSeeder.php`. Plugin seeders are never run implicitly by the host app's own `php artisan db:seed` — call them explicitly from your host `DatabaseSeeder::run()` (e.g. `$this->call(\Plugins\BlogPost\Database\Seeders\DatabaseSeeder::class);`) if you want them included.

## Frontend integration

For React/Vue/Inertia hosts (auto-detected from `package.json`, or set explicitly via `plugin.frontend` in the published config), `plugin:cache` generates `bootstrap/cache/plugins.ts` containing every plugin's page component imports (from `resources/js/Pages`), routes, and sidebar entries — read by your frontend build instead of editing `app.tsx`/`app.ts` directly.

## Architecture

Every framework responsibility is its own single-purpose class behind an interface, resolved via the container — see `src/Contracts/`. Plugin Discovery, the Plugin Registry, the Dependency Resolver, the Plugin Manager/Loader/Lifecycle, and each resource manager (Route, View, Translation, Migration, Config, Asset, Command) can each be swapped independently. The package reuses Laravel's own building blocks throughout — its Router, `Translator`, `Migrator`, config repository, filesystem, and generator commands — rather than duplicating them.

## Testing

```bash
composer install
vendor/bin/phpunit
```

## Documentation

Full documentation lives in [`doc.syscage.com/laravel-plugin`](https://doc.syscage.com/laravel-plugin):
