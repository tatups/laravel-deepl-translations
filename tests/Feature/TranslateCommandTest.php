<?php

namespace Tests\Feature;

use BabyMarkt\DeepL\DeepL;
use BlackLabelBytes\Translations\Domain\TranslationRepository;
use BlackLabelBytes\Translations\TranslateCommand;
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
                    ['text'=>'translated huu <x>:placeholder</x>', 'idc'=>'idc'],
                    ['text'=>'translated nested value that should not override'],
                    ['text'=>'translated nested value']

                ];

                $mock->allows(['translate'=>$returnTranslations]);
            });

            return new TranslationService($repo, $mockDeepL);
        });

    }


    public function test_translation_command() {
       
        config(['deepl-translations.api_key'=>'hii']);
        config(['deepl-translations.api_address'=>'hiihuu']);
        config(['deepl-translations.api_version'=>'2']);
        config(['deepl-translations.from_language'=>'en']);
        config(['deepl-translations.to_languages'=>['fi']]);

        $data = [
            'should_not_override'=>'this_value_should_not_override',
            'key'=>'hii',
            'key2'=>'huu',
            'nested'=>[
                'should_not_override'=>'this_value_should_not_override',
                'nested_key'=>'nested value'
            ]
        ];

        $existingTranslations = [
            'should_not_override'=>$existing='i should not be overridden',
            'nested'=>[
                'should_not_override'=>$existing
            ]
        ];
       
        file_put_contents(__DIR__.'/../mock/en/test.php', "<?php return ".var_export($data, true).' ;');

        //initialize with empty
        file_put_contents(__DIR__.'/../mock/fi/test.php', "<?php return ".var_export($existingTranslations, true).' ;');


        Artisan::call('deepl-translate');

        $result = require __DIR__.'/../mock/fi/test.php';

        $expected = [
            'should_not_override'=>$existing,
            'key'=>'translated hii',
            'key2'=>'translated huu :placeholder',
            'nested'=>[
                'should_not_override'=>$existing,
                'nested_key'=>'translated nested value'
            ]
        ];
     
        $this->assertEquals($result, $expected);
    }

    public function test_env_variable_verification() {

        file_put_contents(app()->environmentFilePath(), '');

        $this->artisan('deepl-translate')
        ->expectsConfirmation('Are you sure you want to generate translations for your application?', 'yes')
        ->expectsOutput('Missing configuration options.')
        ->expectsQuestion('Please enter your DeepL api key', 'hiihuu')
        ->expectsQuestion('Please enter the DeepL api address [api-free.deepl.com]', 'test.com')
        ->expectsQuestion('Please enter translation source language [en]', 'fi')
        ->expectsQuestion('Please enter the languages you want to create translations for [fi,sv]', 'ru,sv')
        ->expectsOutput('DeepL configuration initialized. Please run the command again to generate your translations.')
        ->assertExitCode(0);

        $result = file_get_contents(app()->environmentFilePath());

        $expected = PHP_EOL.PHP_EOL.TranslateCommand::DEEPL_API_KEY.'=hiihuu'.PHP_EOL;
        $expected.= TranslateCommand::DEEPL_API_ADDRESS.'=test.com'.PHP_EOL;
        $expected.= TranslateCommand::DEEPL_FROM_LANGUAGE.'=fi'.PHP_EOL;
        $expected.= TranslateCommand::DEEPL_TO_LANGUAGES.'=ru,sv';
      
        $this->assertEquals($expected, $result);
        
    }

}