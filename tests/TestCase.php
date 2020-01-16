<?php

namespace Pebble\Translatable\Tests;

use Illuminate\Database\Schema\Blueprint;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected $fake;

    public function setUp(): void
    {
        parent::setUp();
        $this->setUpDatabase($this->app);
    }

    /**
     * @param \Illuminate\Foundation\Application $app
     *
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            \Pebble\Translatable\TranslatableServiceProvider::class,
        ];
    }

    /**
     * Set up the database.
     *
     * @param \Illuminate\Foundation\Application $app
     */
    protected function setUpDatabase($app)
    {
        include_once __DIR__.'/../database/migrations/create_translations_tables.php.stub';
        (new \CreateTranslationsTables())->up();

        // Fake model migration
        $this->app['db']->connection()->getSchemaBuilder()->create('fakes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('greeting')->nullable();
            $table->timestamps();
        });

        // Create fake model to test
        $this->fake = new Fake;
        $this->fake->save();
    }
}
