<?php

namespace Tests\Feature;

use BabyMarkt\DeepL\DeepL;
use BlackLabelBytes\Translations\Domain\TranslationRepository;
use BlackLabelBytes\Translations\TranslationService;
use Tests\TestCase;
use Illuminate\Support\Facades\Artisan;
use Mockery\MockInterface;

class TranslateCommandTest extends TestCase
{

    public function setUp():void {

        parent::setUp();

        app()->bind(TranslationService::class, function() { // not a service provider but the target of service provider
            $repo = new TranslationRepository(50, __DIR__.'/../mock');
            $mockDeepL = $this->partialMock (DeepL::class, function (MockInterface $mock) {

                $returnTranslations = 
                [
                    ['text'=>'should not override'], 
                    ['text'=>'translated hii'], 
                    ['text'=>'translated huu <x>:placeholder</x>', 'idc'=>'idc']
                ];

                $mock->allows(['translate'=>$returnTranslations]);
            });

            return new TranslationService($repo, $mockDeepL);
        });

    }


    public function test_translation_command() {
       
        config(['deepl-translations.to_languages'=>['fi']]);

        $data = [
            'should_not_override'=>'should_not_override',
            'key'=>'hii',
            'key2'=>'huu'
        ];

        $existingTranslations = [
            'should_not_override'=>$existing='i should not be overridden'
        ];
       
        file_put_contents(__DIR__.'/../mock/en/test.php', "<?php return ".var_export($data, true).' ;');

        //initialize with empty
        file_put_contents(__DIR__.'/../mock/fi/test.php', "<?php return ".var_export($existingTranslations, true).' ;');


        Artisan::call('deepl-translate');

        $result = require __DIR__.'/../mock/fi/test.php';

        $expected = [
            'should_not_override'=>$existing,
            'key'=>'translated hii',
            'key2'=>'translated huu :placeholder'
        ];
    
        $this->assertEquals($result, $expected);
    }

}