<?php

namespace Tests\Feature;

use BlackLabelBytes\Translations\Domain\TranslationRepository;
use BlackLabelBytes\Translations\Domain\TranslationString;
use Tests\TestCase;

class TranslationRepositoryTest extends TestCase
{


    public function test_get_translatables_respects_max_chunk_size() {

        $data = [
            'chunk_1_val_1'=>'hii',
            'chunk_1_val_2'=>'huu',
            'chunk_2_val_1'=>'value',
        ];
        file_put_contents(__DIR__.'/../mock/en/test.php', "<?php return ".var_export($data, true).' ;');


        $repo = new TranslationRepository(2, __DIR__.'/../mock');
        $chunked = $repo->getTranslatables('en');
       
        $this->assertEquals(2, $chunked->count());

        $firstChunk = $chunked->get(0)->getKeyedTranslatables();
        $secondChunk = $chunked->get(1)->getKeyedTranslatables();

        $this->assertEquals(2, $firstChunk->count());
        $this->assertEquals('hii', $firstChunk->get('test.chunk_1_val_1')->getValue());
        $this->assertEquals('huu', $firstChunk->get('test.chunk_1_val_2')->getValue());

        $this->assertEquals(1, $secondChunk->count());
        $this->assertEquals('value', $secondChunk->get('test.chunk_2_val_1')->getValue());
    }

    public function test_get_translatables_results_equals_translation_keys() {

        $data = [
            'key'=>'value',
            'placeholders_containing_key'=>'value :placeholder1 :placeholder2',
            'nested'=>[
                'nested_key'=>'nested value',
                'more_nesting'=>[
                    'key'=>'value'
                ],
            ]
        ];
       
        file_put_contents(__DIR__.'/../mock/en/test.php', "<?php return ".var_export($data, true).' ;');
        
        $repo = new TranslationRepository(500, __DIR__.'/../mock');
        $results = $repo->getTranslatables('en')->first()->getKeyedTranslatables();

        
        $this->assertEquals(4, $results->count());
     
        $this->assertEquals('value', $results->get('test.key')->getValue());
        $this->assertEquals('value <x>:placeholder1</x> <x>:placeholder2</x>', $results->get('test.placeholders_containing_key')->getValue());
        $this->assertEquals('nested value', $results->get('test.nested.nested_key')->getValue());

        $this->assertEquals('value', $results->get('test.nested.more_nesting.key')->getValue());
    }

    public function test_store_translation_results() {

        $data = [
            'key'=>'existing_value',
        ];
       
        file_put_contents(__DIR__.'/../mock/fi/test.php', "<?php return ".var_export($data, true).' ;');
        
        $repo = new TranslationRepository(500, __DIR__.'/../mock');
        
        $translationResults = collect([
            new TranslationString('test.php', 'key', 'should not override'),
            new TranslationString('test.php', 'key2', 'finnish translation result'),
            new TranslationString('test.php', 'nested.key', 'finnish translation result with :placeholder'),
            new TranslationString('test.php', 'nested.key2', 'finnish translation result 2')

        ]);
        
        $repo->storeTranslationStrings($translationResults, 'fi');

        $result = require __DIR__.'/../mock/fi/test.php';

        $excected = [
            'key'=>'existing_value',
            'key2'=>'finnish translation result',
            'nested'=>[
                'key'=>'finnish translation result with :placeholder',
                'key2'=>'finnish translation result 2'

            ]

        ];
        $this->assertEquals($excected, $result);
    }


}