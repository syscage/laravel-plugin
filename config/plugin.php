<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Plugins Path
    |--------------------------------------------------------------------------
    |
    | The absolute path to the directory that contains every installed
    | plugin. Each subdirectory of this path is expected to contain a
    | "plugin.json" manifest describing the plugin.
    |
    */

    'plugins_path' => base_path('plugins'),

    /*
    |--------------------------------------------------------------------------
    | Plugin Root Namespace
    |--------------------------------------------------------------------------
    |
    | The root PHP namespace under which generated plugin namespaces are
    | nested, e.g. "Plugins\MyPlugin". Generators use this value to build
    | the default namespace written into a new plugin's "plugin.json".
    |
    */

    'namespace_root' => 'Plugins',

    /*
    |--------------------------------------------------------------------------
    | Manifest Filename
    |--------------------------------------------------------------------------
    |
    | The filename every plugin must contain at its root directory to be
    | discoverable by the framework.
    |
    */

    'manifest_file' => 'plugin.json',

    /*
    |--------------------------------------------------------------------------
    | Database Table
    |--------------------------------------------------------------------------
    |
    | The table used to persist installed plugin state (installation time,
    | enabled/disabled timestamps, and the plugin's UUID).
    |
    */

    'table' => 'plugins',

    /*
    |--------------------------------------------------------------------------
    | Plugin-Generated Table Prefix
    |--------------------------------------------------------------------------
    |
    | The default prefix applied to every table name a plugin generator
    | creates (migrations made via "make:model-plugin"/"make:migration-plugin"
    | and the "$table" property written into generated models). Prevents
    | table-name collisions between plugins and the host application. An
    | individual plugin may override this via the "table_prefix" field in
    | its own "plugin.json".
    |
    */

    'table_prefix' => 'plugin_',

    /*
    |--------------------------------------------------------------------------
    | Cache Paths
    |--------------------------------------------------------------------------
    |
    | Locations of the various compiled caches produced by the framework:
    | the discovered plugin manifest cache, the merged sidebar cache, and
    | the generated frontend manifest consumed by React/Vue/Inertia apps.
    |
    */

    'cache' => [
        'plugins' => base_path('bootstrap/cache/plugins.php'),
        'sidebar' => base_path('bootstrap/cache/sidebar.php'),
        'frontend' => base_path('bootstrap/cache/plugins.ts'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Stub Path
    |--------------------------------------------------------------------------
    |
    | The directory containing the stub templates used by the plugin and
    | plugin-resource generators. Publish this directory to override any
    | stub with `php artisan vendor:publish --tag=plugin-stubs`.
    |
    */

    'stubs' => __DIR__ . '/../stubs/plugin',

    /*
    |--------------------------------------------------------------------------
    | Frontend Framework
    |--------------------------------------------------------------------------
    |
    | The frontend stack used by the host application. One of: "blade",
    | "react", "vue", "inertia-react", "inertia-vue". When left as "auto",
    | the framework detects it from the host's package.json / composer.json.
    |
    */

    'frontend' => 'auto',

    /*
    |--------------------------------------------------------------------------
    | Public Asset Path
    |--------------------------------------------------------------------------
    |
    | The directory, under the host application's public directory, that
    | each plugin's own "src/public" assets are linked (or copied) into by
    | the Plugin Asset Manager. Assets become reachable at
    | "/plugins/{alias}/...".
    |
    */

    'public_path' => public_path('plugins'),

];
