<?php

namespace Tests\Unit;

use BlackLabelBytes\Translations\Domain\TranslatableChunk;
use BlackLabelBytes\Translations\TranslationRepository;
use BlackLabelBytes\Translations\Domain\TranslationString;
use Tests\TestCase;

class TranslatableChunkTest extends TestCase
{


    public function test_chunk_getTranslationStrings_removes_xml_ignore_tags() {

        $chunk = new TranslatableChunk(collect([
            new TranslationString('test', 'hii', 'hii :placeholder :placeholder2'),
        ]));

        $result = $chunk->getTranslationStrings(collect(['translated hii <x>:placeholder</x> <x>:placeholder2</x>']));

        $this->assertEquals($result->first()->getValue(),  'translated hii :placeholder :placeholder2');
    }


}