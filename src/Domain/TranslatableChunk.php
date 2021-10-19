<?php

namespace BlackLabelBytes\Translations\Domain;

use Illuminate\Support\Collection;

class TranslatableChunk 
{
    protected $translatables;
    protected $translationStrings;

    /**
     * @param Collection|TranslationString[] $translatables translation string in source language
     */
    public function __construct(Collection $translatables)
    {
        $this->translatables = $translatables;
    }

    public function getTranslatableStrings(): Collection 
    {
        return $this->translatables->map->getValue()->values();
    }

    public function getKeyedTranslatables() {
        return $this->translatables;
    }


    /**
     * @param Collection|string[] $resultValues result strings of this chunk
     * @return Collection|TranslationString[] translation results
     */
    public function getTranslationStrings(Collection $resultValues) 
    {
        return  $this->translatables->keys()->combine($resultValues)->map(function($item, $key) {
            $parts = explode('.', $key);
            $filename = $parts[0].'.php';
            
            $translationKey =  implode('.', array_slice($parts, 1));
            $item = str_replace(['<x>', '</x>'], '', $item);
       
            return new TranslationString($filename, $translationKey, $item);
        })->values();
    }


}