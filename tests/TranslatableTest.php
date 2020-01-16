<?php

namespace Pebble\Translatable\Tests;

class TranslatableTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        config(['app.locale' => 'en']);
    }

    public function testSetTranslationString()
    {
        $tableNames = config('pebble-translatable.table_names');

        $this->fake
            ->setTranslation('greeting', 'es', 'Hola')
            ->setTranslation('greeting', 'en', 'Hello')
            ->save();

        $this->assertDatabaseHas($tableNames['translations'], [
            'id' => 1,
            'field' => 'greeting',
            'locale' => 'es',
            'content' => json_encode('Hola'),
        ]);

        $this->assertDatabaseHas($tableNames['translations'], [
            'id' => 2,
            'field' => 'greeting',
            'locale' => 'en',
            'content' => json_encode('Hello'),
        ]);
    }

    public function testGetTranslationString()
    {
        $this->fake
            ->setTranslation('greeting', 'es', 'Hola')
            ->setTranslation('greeting', 'en', 'Hello')
            ->save();

        $response = $this->fake->getTranslation('greeting', 'es');

        $this->assertEquals('Hola', $response);

        $response = $this->fake->getTranslation('greeting', 'en');

        $this->assertEquals('Hello', $response);
    }

    public function testGetTranslations()
    {
        $this->fake
            ->setTranslation('greeting', 'es', 'Hola')
            ->setTranslation('greeting', 'en', 'Hello')
            ->save();

        $response = $this->fake->getTranslations('greeting')->all();

        $this->assertEquals('Hola', $response['es']);
        $this->assertEquals('Hello', $response['en']);
    }

    public function testGetTranslatedLocales()
    {
        $this->fake
            ->setTranslation('greeting', 'es', 'Hola')
            ->setTranslation('greeting', 'en', 'Hello')
            ->save();

        $response = $this->fake->getTranslatedLocales('greeting')->all();

        $this->assertEquals('es', $response[0]);
        $this->assertEquals('en', $response[1]);
    }

    public function testGetTranslatableAttributes()
    {
        $this->fake
            ->setTranslation('greeting', 'es', 'Hola')
            ->setTranslation('greeting', 'en', 'Hello')
            ->save();

        $response = $this->fake->getTranslatableAttributes();

        $this->assertCount(1, $response);
        $this->assertEquals('greeting', $response[0]);
    }

    public function testIsTranslatableAttribute()
    {
        $this->fake
            ->setTranslation('greeting', 'es', 'Hola')
            ->setTranslation('greeting', 'en', 'Hello')
            ->save();

        $response = $this->fake->isTranslatableAttribute('greeting');
        $this->assertTrue($response);

        $response = $this->fake->isTranslatableAttribute('saludo');
        $this->assertFalse($response);
    }

    public function testSetTranslatableFieldUsingMutator()
    {
        app()->setLocale('en');
        $this->fake->greeting = 'Hello';

        app()->setLocale('es');
        $this->fake->greeting = 'Hola';

        $response = $this->fake->getTranslation('greeting', 'en');
        $this->assertEquals('Hello', $response);

        $response = $this->fake->getTranslation('greeting', 'es');
        $this->assertEquals('Hola', $response);
    }

    public function testCreateFactoryEloquent()
    {
        $fake = Fake::create([
            'greeting' => [
                'es' => 'Hola',
                'en' => 'Hello',
            ],
        ]);

        $response = $fake->getTranslation('greeting', 'en');
        $this->assertEquals('Hello', $response);

        $response = $fake->getTranslation('greeting', 'es');
        $this->assertEquals('Hola', $response);
    }

    public function testCreateWithNewEloquent()
    {
        $fake = new Fake;

        $fake
            ->setTranslation('greeting', 'en', 'Hello')
            ->save();

        $response = $fake->getTranslation('greeting', 'en');
        $this->assertEquals('Hello', $response);

        $fake
            ->setTranslation('greeting', 'es', 'Hola')
            ->save();

        $response = $fake->getTranslation('greeting', 'es');
        $this->assertEquals('Hola', $response);
    }
}
