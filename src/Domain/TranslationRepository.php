<?php

namespace BlackLabelBytes\Translations\Domain;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class TranslationRepository
{
    /** @var string $basePath */
    protected $basePath;

    /** @var int $chunkSize */
    protected $chunkSize;

    public function __construct(int $chunkSize, string $basePath=null)
    {
        $this->basePath = $basePath ?? base_path('resources/lang');
        $this->chunkSize = $chunkSize;
    }


    /**
     *
     * @return Collection|TranslatableChunk[]
     */
    public function getTranslatables(string $locale) {

        $sourcePath = $this->basePath.DIRECTORY_SEPARATOR.$locale;
        $files = collect(scandir($sourcePath));

        $files = $files->filter(function($value) {
            return str_contains($value, '.php');
        });

        $translatables = collect();
        $regex = '~(:\w+)~';

        foreach($files as $file) {
            $translations = require $sourcePath.DIRECTORY_SEPARATOR.$file;
            
            //Flatten to dot notation and remove non string values (empty arrays) from translations
            $dotted = collect(Arr::dot($translations))->filter(function($i) {
                return is_string($i);
            });

            $dotted = $dotted->keyBy((function($i, $k)use($file){
                return explode('.php', $file)[0].'.'.$k;
            }));
         
            //Add the <x></x> xml tags to translation :placeholders to notify deepl that we dont want to translate these
            $dotted = $dotted->map(function($value)use($regex){
                $matches = [];
                preg_match_all($regex, $value, $matches);
            
                foreach($matches[0] as $match) {
                    $value = str_replace($match, "<x>$match</x>", $value);
                }
                return $value;

            });

            $translatables = $translatables->merge($dotted);
        }

        $translatables = $translatables->map(function($item, $key) {
            return TranslationString::makeFromKeyWithFilename($key, $item);
        });

        $chunked = $translatables->chunk($this->chunkSize);
        
        return $chunked->mapInto(TranslatableChunk::class);
    }




    /**
     * Store the translation strings 
     *
     * @param Collection|TranslationString[] $chunkedResults
     * @return void
     */
    public function storeTranslationStrings(Collection $results, string $locale) {


        $toPath = $this->basePath.DIRECTORY_SEPARATOR.$locale;
        if(!is_dir($toPath)) {
            mkdir($toPath);
        }
        
        $newFiles = $results->groupBy->getFilename()->map(function($group) {
         
            return $group->reduce(function($carry, $item) {
                return $carry->put($item->getKey(), $item->getValue());
            }, collect());
        });
            
        foreach($newFiles as $filename => $translations) {

            $filePath = $toPath.DIRECTORY_SEPARATOR.$filename;

            $existing = file_exists($filePath) ? Arr::dot(require $filePath) : [];
        
            //Merge not overwriting existing values
            $merged = $translations->merge($existing);
            
            $new = [];
            //Convert from dot keyed to multidimensional array
            foreach($merged as $key => $value) {
                Arr::set($new, $key, $value);
            }
            //To the final format
            $new = $this->varExport($new);
               
            file_put_contents($toPath.DIRECTORY_SEPARATOR.$filename, '<?php return ' . $new . ';');
            
        }
        
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