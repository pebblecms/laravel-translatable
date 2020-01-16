<?php

namespace Pebble\Translatable\Exceptions;

use Exception;
use Illuminate\Database\Eloquent\Model;

class AttributeIsNotTranslatable extends Exception
{
    public static function make(string $field, Model $model)
    {
        $translatableAttributes = implode(', ', $model->getTranslatableAttributes());

        return new static("Cannot translate attribute `{$field}` as it's not one of the translatable attributes: `$translatableAttributes`");
    }
}
