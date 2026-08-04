# Syscage Plugin

[![Latest Version](https://img.shields.io/badge/dynamic/json?url=https%3A%2F%2Fraw.githubusercontent.com%2Fsyscage%2Flaravel-plugin%2Fmain%2Fcomposer.json%26label%3Dversion%26query%3D%24.version)](composer.json)
[![License](https://img.shields.io/packagist/l/syscage/laravel-plugin.svg)](LICENSE.md)
[![PHP Version](https://img.shields.io/badge/php-%5E8.2-777bb4.svg)](composer.json)
[![Laravel](https://img.shields.io/badge/laravel-12%20%7C%2013-ff2d20.svg)](composer.json)

A WordPress-style plugin framework for Laravel 12+ applications. `syscage/laravel-plugin` turns a `plugins/` directory in your host application into a set of independently discoverable, installable, and toggleable Laravel packages — each with its own routes, views, translations, migrations, config, assets, and console commands — without ever running `composer dump-autoload`.

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
php artisan plugin:doctor                  # health-check every plugin's providers/dependencies
php artisan plugin:publish blog-post     # (re-)publish a plugin's public assets
```

`requires`/`conflicts` in `plugin.json` are validated (including semver version constraints) before install/enable, and a plugin's loading order is resolved topologically by its dependencies, then by `priority`.

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

See `prompt.md` for the full list — every `make:*`, `migrate*`, `db:*`, `route:*`, `config:cache`, `view:cache`, `event:cache`, `vendor:publish`, `optimize`, and `queue:work` command has a `*-plugin` alias supporting the same arguments and options as the original.

## Frontend integration

For React/Vue/Inertia hosts (auto-detected from `package.json`, or set explicitly via `plugin.frontend` in the published config), `plugin:cache` generates `bootstrap/cache/plugins.ts` containing every plugin's page component imports (from `resources/js/Pages`), routes, and sidebar entries — read by your frontend build instead of editing `app.tsx`/`app.ts` directly.

## Architecture

Every framework responsibility is its own single-purpose class behind an interface, resolved via the container — see `src/Contracts/`. Plugin Discovery, the Plugin Registry, the Dependency Resolver, the Plugin Manager/Loader/Lifecycle, and each resource manager (Route, View, Translation, Migration, Config, Asset, Command) can each be swapped independently. The package reuses Laravel's own building blocks throughout — its Router, `Translator`, `Migrator`, config repository, filesystem, and generator commands — rather than duplicating them.

## Testing

```bash
composer install
vendor/bin/phpunit
```
