<?php

namespace Rupalipshinde\Template;

use Illuminate\Support\ServiceProvider;

class TemplateServiceProvider extends ServiceProvider {

    /**
     * Publishes configuration file.
     *
     * @return  void
     */
    public function boot() {
//        $this->registerMigrations();

        $this->publishes([
            __DIR__ . '/../database/migrations' => database_path('migrations'),
                ], 'template-migrations');

        $this->publishes([
            __DIR__ . '/config/template.php' => config_path('template.php'),
                ], 'template');

//        $this->publishes([
//            __DIR__ . '/../database/migrations/' => database_path('migrations')
//        ]);
//        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations/');
    }

    /**
     * Make config publishment optional by merging the config from the package.
     *
     * @return  void
     */
    public function register() {
        $this->mergeConfigFrom(
                __DIR__ . '/config/template.php', 'template'
        );

//        $this->loadRoutesFrom(__DIR__ . '/routes.php');
    }

    /**
     * Register Template migration files.
     *
     * @return void
     */
    protected function registerMigrations() {
        return $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }

}
