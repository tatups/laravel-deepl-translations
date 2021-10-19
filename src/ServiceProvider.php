<?php

namespace BlackLabelBytes\Translations;

use Illuminate\Support\ServiceProvider as SupportServiceProvider;

class ServiceProvider extends SupportServiceProvider {


    public function boot()
    {
    if ($this->app->runningInConsole()) {
        $this->commands([
            TranslateCommand::class
        ]);
    }

    $this->publishes([
        __DIR__.'/../config/deepl-translations.php' => config_path('deepl-translations.php'),
    ]);


    }
}