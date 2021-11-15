<?php

namespace BlackLabelBytes\Translations;

use BabyMarkt\DeepL\DeepL;
use BlackLabelBytes\Translations\Domain\TranslationRepository;

class TranslationService
{
    /** @var TranslationRepository $repo */
    protected $repo;

    /** @var DeepL $translationClient */
    protected $translationClient;


    public function __construct(TranslationRepository $repo, DeepL $translationClient)
    {
        $this->repo = $repo;
        $this->translationClient = $translationClient;
    }

    /**
     *
     * @param string $fromLanguage
     * @param string $toLanguage
     * @return void
     */
    public function translate(string $fromLanguage, string $toLanguage) {

        $translatableChunks = $this->repo->getTranslatables($fromLanguage);

        $localeResults = collect();

        foreach($translatableChunks as $chunk) {
          
            $translatables = $chunk->getTranslatableStringsWithoutTranslation($toLanguage)->toArray();
            if(count($translatables) > 0) {
                $results = collect($this->translationClient->translate($translatables, $fromLanguage, $toLanguage));
            
                $values = $results->map(function($item) {
                    return $item['text'];
                });
            
                $localeResults = $localeResults->merge($chunk->getTranslationStrings($values, $toLanguage));
            }
        }
        $this->repo->storeTranslationStrings($localeResults, $toLanguage);
        
    }
}