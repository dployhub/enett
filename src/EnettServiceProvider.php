<?php
namespace Dploy\Enett;

use Log;
use Dploy\Enett\Enett;
use Illuminate\Support\ServiceProvider;

class EnettServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        // Route
        $path = 'enett.php';
        $this->publishes([
            __DIR__.'/Config/enett.php' => app()->basePath() . '/config' . ($path ? '/' . $path : $path),
        ], 'config');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom( __DIR__.'/Config/enett.php', 'enett');
        $config = config('enett');
        $this->app->singleton('enett', function () use ($config) {
            return new Enett($config, Log::getMonolog());
        });
    }
}
?>
