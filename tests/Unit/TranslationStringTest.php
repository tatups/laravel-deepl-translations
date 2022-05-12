<?php

namespace Tests\Unit;

use BlackLabelBytes\Translations\Domain\TranslatableChunk;
use BlackLabelBytes\Translations\TranslationRepository;
use BlackLabelBytes\Translations\Domain\TranslationString;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Translation\FileLoader;
use Illuminate\Translation\Translator;
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


    public function test_translation_string_exists_for_locale() {

        $data = [
            'key'=>'hii',
        ];

        file_put_contents(__DIR__.'/../mock/en/test.php', "<?php return ".var_export($data, true).' ;');

        app()->bind('translator', function() {

            $loader = new FileLoader(app()->make(Filesystem::class), __DIR__."/../mock");
            return new Translator($loader, 'en');
        });

       $t = app('translator');

        $key = 'test.key';
        $result = TranslationString::makeFromKeyWithFilename($key, 'value');

        $this->assertTrue($result->existsFor('en'));
        $this->assertFalse($result->existsFor('fi'));
        $this->assertFalse($result->existsFor('sv'));

    }


}