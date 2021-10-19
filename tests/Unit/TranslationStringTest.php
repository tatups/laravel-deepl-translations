<?php

namespace Tests\Unit;

use BlackLabelBytes\Translations\Domain\TranslatableChunk;
use BlackLabelBytes\Translations\TranslationRepository;
use BlackLabelBytes\Translations\Domain\TranslationString;
use Tests\TestCase;

class TranslationStringTest extends TestCase
{


    public function test_translation_string_make_from_key_with_filename() {

        $key = 'hii.huu';
        $result = TranslationString::makeFromKeyWithFilename($key, 'value');

        $this->assertEquals('hii.php', $result->getFilename());
        $this->assertEquals('huu', $result->getKey());
        $this->assertEquals('value', $result->getValue());
    }


}