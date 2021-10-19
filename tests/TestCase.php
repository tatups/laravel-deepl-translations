<?php

namespace Tests;

use BlackLabelBytes\Translations\DeepLTranslationServiceProvider;

class TestCase extends \Orchestra\Testbench\TestCase
{


    public function setUp(): void
    {
        parent::setUp();
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