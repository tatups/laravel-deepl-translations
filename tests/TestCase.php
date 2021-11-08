<?php

namespace Tests;

use BlackLabelBytes\Translations\DeepLTranslationServiceProvider;

class TestCase extends \Orchestra\Testbench\TestCase
{


    public function setUp(): void
    {
        parent::setUp();
    }

    protected function tearDown(): void
    {
        $this->tearDownTheTestEnvironment();

        if(file_exists(__DIR__."/mock/en/test.php")) {
            unlink(__DIR__."/mock/en/test.php");
        }
        if(file_exists(__DIR__."/mock/fi/test.php")) {
            unlink(__DIR__."/mock/fi/test.php");
        }

    }  
    protected function getPackageProviders($app)
    {
        return [
            DeepLTranslationServiceProvider::class,
        ];
    }
  
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }

}