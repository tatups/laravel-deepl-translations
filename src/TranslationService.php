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

    public function translate(string $fromLanguage, array $toLanguages) {

        $translatableChunks = $this->repo->getTranslatables($fromLanguage);
        
        foreach ($toLanguages as $locale) {

            $localeResults = collect();

            foreach($translatableChunks as $chunk) {

                $translatables = $chunk->getTranslatableStrings()->toArray();
               
                $results = collect($this->translationClient->translate($translatables, $fromLanguage, $locale));
             
                $values = $results->map(function($item) {
                    return $item['text'];
                });
           
                $localeResults = $localeResults->merge($chunk->getTranslationStrings($values));
         
            }
            $this->repo->storeTranslationStrings($localeResults, $locale);
        }
    }
}