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
    protected $description = 'Translate your english language files into sv,fi';


    protected DeepL $deepl;
    
    protected string $basePath;

    protected string $fromLanguage;

    protected array $toLanguages;
    
    public function __construct()
    {
        parent::__construct();

        $apiKey = config('deepl-translations.api_key');
        $version = config('deepl-translations.api_version');
        $address = config('deepl-translations.api_address');

        $this->deepl = new DeepL($apiKey, $version, $address);
        $this->basePath = base_path('resources/lang');
        $this->fromLanguage = config('deepl-translations.from_language');
        $this->toLanguages = config('deepl-translations.to_languages');
    }
    
    protected function handle()
    {
    
        foreach($this->toLanguages as $locale) {
            $resultFiles = $this->translateLocale($locale);
            $toPath = $this->basePath.DIRECTORY_SEPARATOR.$locale;
            if(!is_dir($toPath)) {
                mkdir($toPath);
            }

            foreach($resultFiles as $filename => $translations) {

                $filePath = $toPath.DIRECTORY_SEPARATOR.$filename;

                $existing = file_exists($filePath) ? Arr::dot(require $filePath) : [];

                //Merge not overwriting existing values
                $new = $translations->merge($existing)->toArray();
                //To the final format
                $new = $this->reverseDot($new);
                //String formatting
                $new = $this->varExport($new);
                

                file_put_contents($toPath.DIRECTORY_SEPARATOR.$filename, '<?php return ' . $new . ';');
                
            }
        }
        return Command::SUCCESS;


    }

    protected function translateLocale(string $locale): Collection {

        $sourcePath = $this->basePath.DIRECTORY_SEPARATOR.$this->fromLanguage;
        $files = scandir($sourcePath);

        $files = array_filter($files, function($value) {
            return str_contains($value, '.php');
        });

        $results = collect([]);
        $regex = '~(:\w+)~';
       
        foreach($files as $file) {
            $translations = require $sourcePath.DIRECTORY_SEPARATOR.$file;

            //Flatten to dot notation and remove non string values (empty arrays) from translations
            $dotted = collect(Arr::dot($translations))->filter(function($i) {
                return is_string($i);
            });

            //Add the <x></x> xml tags to translation :placeholders to notify deepl that we dont want to translate these
            $dotted = $dotted->map(function($value)use($regex){
                $matches = [];
                preg_match_all($regex, $value, $matches);
            
                foreach($matches[0] as $match) {
                    $value = str_replace($match, "<x>$match</x>", $value);
                }
                return $value;

            });
       
        
            $values = $dotted->values();
            $result = collect($deepl->translate($values, 'en', $locale, tagHandling: 'xml', ignoreTags: ['x']));

            //Get the translation texts
            $result = $result->map(fn($val) => $val['text']);

            //Remove the <x></x> tags
            $result = $result->map(fn($val) => str_replace('<x>', '', $val));
            $result = $result->map(fn($val) => str_replace('</x>', '', $val));

            $results->put($file, $dotted->keys()->combine($result));
        }
        return $results;
    }

    protected function reverseDot(array $dotted) {

        $result = [];
        foreach($dotted as $dotKey => $dotValue) {

            Arr::set($result, $dotKey, $dotValue);
        }
         
        return $result;
    }

    //https://gist.github.com/Bogdaan/ffa287f77568fcbb4cffa0082e954022 thanks mr.Bogdaan
    //Replaces array() with [] and adds 4 spaces
    protected function varExport(array $expression) {
        $export = var_export($expression, true);
        $export = preg_replace("/^([ ]*)(.*)/m", '$1$1$2', $export);
        $array = preg_split("/\r\n|\n|\r/", $export);
        $array = preg_replace(["/\s*array\s\($/", "/\)(,)?$/", "/\s=>\s$/"], [null, ']$1', ' => ['], $array);
        $export = join(PHP_EOL, array_filter(["["] + $array));
        return $export;
    }
}
