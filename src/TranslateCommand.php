<?php

namespace BlackLabelBytes\Translations;

use BabyMarkt\DeepL\DeepL;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;

class TranslateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'deepl-translate';

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

        $service->translate(config('deepl-translations.from_language'), config('deepl-translations.to_languages'));
        
        
        return Command::SUCCESS;
    }
}
