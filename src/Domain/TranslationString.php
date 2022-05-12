<?php

namespace BlackLabelBytes\Translations\Domain;

use Illuminate\Support\Arr;

class TranslationString 
{

    protected $filename;
    protected $key;
    protected $value;

    public function __construct(string $filename, string $key, string $value)
    {
        $this->filename = $filename;
        $this->key = $key;
        $this->value = $value;
    }

    public function getValue() {
        return $this->value;
    }

    public function getKey() {
        return $this->key;
    }

    public function getFullTranslationKey() {
        $baseFilename = explode('.', $this->getFilename())[0];

        return $baseFilename.'.'.$this->getKey();
    }

    public function getFilename() {
        return $this->filename;
    }

    //Returns the representation of this translation as if it was in a .php translation array (omits filename)
    public function toArray() {
        $arr = [];

        Arr::set($arr, $this->key, $this->value);

        return $arr;
    }

    public function existsFor(string $locale) {

        return app('translator')->hasForLocale($this->getFullTranslationKey(), $locale);
    }



    public static function makeFromKeyWithFilename(string $key, string $value) {
        list($filename, $key) = explode('.', $key, 2);

        return new self($filename.'.php', $key, $value);
    }

    
}