<?php

namespace BlackLabelBytes\Translations;

use BabyMarkt\DeepL\DeepL;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;

class TranslateCommand extends Command
{
    public const DEEPL_API_KEY = 'DEEPL_API_KEY';
    public const DEEPL_API_ADDRESS = 'DEEPL_API_ADDRESS';
    public const DEEPL_API_VERSION = 'DEEPL_API_VERSION';
    public const DEEPL_API_CHUNK_SIZE = 'DEEPL_API_CHUNK_SIZE';
    public const DEEPL_TO_LANGUAGES = 'DEEPL_TO_LANGUAGES';
    public const DEEPL_FROM_LANGUAGE = 'DEEPL_FROM_LANGUAGE';
    public const DEEPL_TRANSLATIONS_FOLDER= 'DEEPL_TRANSLATIONS_FOLDER';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'deepl-translate {--filenames=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Translate your language files';

    public function __construct()
    {
        parent::__construct();
    }
    
    public function handle()
    {
        $service = app()->make(TranslationService::class);
        $fromLanguage = config('deepl-translations.from_language');
        $toLanguages = config('deepl-translations.to_languages');

        $confirmed =$this->confirm('Are you sure you want to generate translations for your application?');
   
        if(!$confirmed) {
            return;
        }

        $envValid = $this->verifyEnv();

        if(!$envValid) {
            return;
        }

        $this->info('Proceeding to generate translation files from '.$fromLanguage.
        ' to '.implode(',', $toLanguages));

        $filenames = $this->option('filenames') ?  explode(',', $this->option('filenames')) : null;

        foreach($toLanguages as $toLanguage) {
            
            $this->info("Translating from $fromLanguage to $toLanguage...");
            $service->translate($fromLanguage, $toLanguage, $filenames);
        }
        
        return 0;
    }

    protected function verifyEnv() {

        $envCheck = [
            $apiKeyMissing = empty(config('deepl-translations.api_key')),
            $apiAddressMissing = empty(config('deepl-translations.api_address')),
            $apiFromLanguageMissing = empty(config('deepl-translations.from_language')),
            $apiToLanguagesMissing =  empty(config('deepl-translations.to_languages')),
            $missingFolder = !is_dir(base_path(config('deepl-translations.translations_folder')))
            
        ];
        $missingEnvs = !empty(array_filter($envCheck));


        $first = true;
        if($missingEnvs) {
            $this->info('Missing configuration options.');
            $this->info('Please proceed to enter the missing options below. We will append them to your .env file');
            $this->line('--------------------------');
        }
        if($apiKeyMissing) {
            $value = $this->ask('Please enter your DeepL api key');
            $this->writeNewEnvironmentFileWith(self::DEEPL_API_KEY, $value, $first);
            $first = false;
        }
        if($apiAddressMissing) {
            $value = $this->ask('Please enter the DeepL api address [api-free.deepl.com]', 'api-free.deepl.com');
            $this->writeNewEnvironmentFileWith(self::DEEPL_API_ADDRESS, $value, $first);
            $first = false;

        }
        if($apiFromLanguageMissing) {
            $value = $this->ask('Please enter translation source language [en]', 'en');
            $this->writeNewEnvironmentFileWith(self::DEEPL_FROM_LANGUAGE, $value, $first);
            $first = false;

        }   
        if($apiToLanguagesMissing) {
            $value = $this->ask('Please enter the languages you want to create translations for [fi,sv]', 'fi,sv');
            $this->writeNewEnvironmentFileWith(self::DEEPL_TO_LANGUAGES, $value, $first);
            $first = false;
        }    

        if($missingFolder) {
            $value = $this->ask('Please enter the path to the translations folder');
            $this->writeNewEnvironmentFileWith(self::DEEPL_TRANSLATIONS_FOLDER, $value, $first);
            $first = false;
        }  

        if($missingEnvs) {
            $this->info('DeepL configuration initialized. Please run the command again to generate your translations.');
            $this->info('Future updates to the configuration should be done to you .env file');

            return false;
        }
        return true;

    }

   /**
     * Write a new environment file with the given key.
     *
     * @param  string  $key
     * @param  string  $value
     * @return void
     */
    protected function writeNewEnvironmentFileWith(string $key, string $value, bool $first)
    {
        $contents = file_get_contents($this->laravel->environmentFilePath());

  
    

        if(str_contains($contents,  $key)) {
            file_put_contents($this->laravel->environmentFilePath(), preg_replace(
                $this->keyReplacementPattern($key),
                "$key=$value",
                $contents
            ));
        }
        else {
            $newContent = $contents.PHP_EOL."$key=$value";
            $newContent = $first ? PHP_EOL.$newContent : $newContent;
            file_put_contents($this->laravel->environmentFilePath(), $newContent);
        }
    }

    /**
     * Get a regex pattern that will match env key
     *
     * @return string
     */
    protected function keyReplacementPattern($key)
    {
        $escaped = preg_quote('='.env($key), '/');

        return "/^$key{$escaped}/m";
    }
}
