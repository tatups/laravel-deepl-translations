<?php

namespace BlackLabelBytes\Translations;

use BabyMarkt\DeepL\DeepL;
use BlackLabelBytes\Translations\Domain\TranslationRepository;
use Illuminate\Translation\TranslationServiceProvider;

class DeepLTranslationServiceProvider extends \Illuminate\Support\ServiceProvider {

    protected $configName = 'deepl-translations';

    public function register() {


        $this->mergeConfig();

        if ($this->app->runningInConsole()) {
            $this->commands([
                TranslateCommand::class
            ]);

            $this->app->singleton(DeepL::class, function($app) {
               
                $apiKey = config('deepl-translations.api_key');
                $version = config('deepl-translations.api_version');
                $address = config('deepl-translations.api_address');
        
                return new DeepL($apiKey, $version, $address);
            });

            $this->app->singleton(TranslationService::class, function ($app) {
              
                $repo = new TranslationRepository(config('deepl-translations.api_chunk_size'), base_path('resources/lang'));
    
                return new TranslationService($repo, $app[DeepL::class]);
            });
        }
        
     
        

    }

    public function boot() {
        $this->publishConfig();
    }

    protected function mergeConfig()
    {
        $configPath = __DIR__ . '/../config/' . $this->configName . '.php';

        $this->mergeConfigFrom($configPath, $this->configName);
    }

    /**
     * Publish config file.
     *
     * @param void
     * @return  void
     */
    protected function publishConfig()
    {
        $configPath = __DIR__ . '/../config/' . $this->configName . '.php';

        $this->publishes([$configPath => config_path($this->configName . '.php')], 'impersonate');
    }
}