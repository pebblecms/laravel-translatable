<?php

namespace Pebble\Translatable\Traits;

use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Pebble\Translatable\Exceptions\AttributeIsNotTranslatable;

trait Translatable
{
    protected $tempTranslations = [];

    public static function create(array $attributes = [])
    {
        $translatables = [];

        foreach ($attributes as $field => $values) {
            if (in_array($field, self::$translatable)) {
                $translatables[$field] = $values;
                unset($attributes[$field]);
            }
        }

        $model = static::query()->create($attributes);

        foreach ($translatables as $field => $values) {
            foreach ($values as $locale => $value) {
                $model->setTranslation($field, $locale, $value);
            }
        }

        $model->save();

        return $model;
    }

    public function getAttributeValue($field)
    {
        if (! $this->isTranslatableAttribute($field)) {
            return parent::getAttributeValue($field);
        }

        return $this->getTranslation($field, $this->getLocale());
    }

    public function setAttribute($field, $value)
    {
        if (! $this->isTranslatableAttribute($field) || is_array($value)) {
            return parent::setAttribute($field, $value);
        }

        return $this->setTranslation($field, $this->getLocale(), $value);
    }

    public function isTranslatableAttribute(string $field): bool
    {
        return in_array($field, $this->getTranslatableAttributes());
    }

    protected function getLocale(): string
    {
        return Config::get('app.locale');
    }

    public function getTranslatableAttributes(): array
    {
        return is_array(self::$translatable) ? self::$translatable : [];
    }

    public function getTranslation(string $field, string $locale, bool $useFallbackLocale = true): ?string
    {
        $locale = $this->normalizeLocale($field, $locale, $useFallbackLocale);

        $translations = $this->getTranslations($field)->all();

        $translation = $translations[$locale] ?? '';

        if ($this->hasGetMutator($field)) {
            return $this->mutateAttribute($field, $translation);
        }

        return $translation;
    }

    public function setTranslation(string $field, string $locale, $content): self
    {
        $this->guardAgainstNonTranslatableAttribute($field);

        $translation = $this->translations()
            ->where('field', $field)
            ->where('locale', $locale)
            ->first();

        if (! empty($translation)) {
            $translation->content = $content;
            $translation->save();
        } else if(!empty ($this->id)) {
            $this->translations()->create([
                'field' => $field,
                'locale' => $locale,
                'content' => $content,
            ]);
        } else {
            $this->tempTranslations[$field] = [
                'locale' => $locale,
                'content' => $content
            ];
        }

        return $this;
    }

    protected function guardAgainstNonTranslatableAttribute(string $key)
    {
        if (! $this->isTranslatableAttribute($key)) {
            throw AttributeIsNotTranslatable::make($key, $this);
        }
    }

    public function translations(): MorphToMany
    {
        return $this->morphToMany(
            config('pebble-translatable.models.translation'),
            'translatable',
            config('pebble-translatable.table_names.translatables'),
            config('pebble-translatable.column_names.model_morph_key'),
            'translation_id');
    }

    protected function normalizeLocale(string $field, string $locale, bool $useFallbackLocale): string
    {
        if (in_array($locale, $this->getTranslatedLocales($field)->all())) {
            return $locale;
        }
        if (! $useFallbackLocale) {
            return $locale;
        }
        if (! is_null($fallbackLocale = Config::get('app.fallback_locale'))) {
            return $fallbackLocale;
        }

        return $locale;
    }

    /**
     * Returns a collection with all locales [ locales ].
     *
     * @param string $field
     * @return Collection
     */
    public function getTranslatedLocales(string $field): Collection
    {
        return $this->getTranslations($field)->keys();
    }

    /**
     * Returns a collection with [ locale => content ].
     *
     * @param string|null $field
     * @return Collection
     */
    public function getTranslations(string $field = null): Collection
    {
        return $this->translations()
            ->where('field', $field)
            ->select('locale', 'content')
            ->get()
            ->keyBy('locale')
            ->pluck('content', 'locale');
    }

    /**
     * Override default Eloquent save method to add dynamically stored tempTranslations array.
     *
     * @param  array $options
     * @return bool
     */
    public function save(array $options = [])
    {
        $response = $this->originalSave($options);

        if($response === true) {
            foreach($this->tempTranslations as $field => $data) {
                $this->setTranslation($field, $data['locale'] ?? null, $data['content'] ?? null);
            }
        }

        return $response;
    }

    /**
     * Save the model to the database.
     *
     * @param  array  $options
     * @return bool
     */
    public function originalSave(array $options = [])
    {
        $query = $this->newModelQuery();

        // If the "saving" event returns false we'll bail out of the save and return
        // false, indicating that the save failed. This provides a chance for any
        // listeners to cancel save operations if validations fail or whatever.
        if ($this->fireModelEvent('saving') === false) {
            return false;
        }

        // If the model already exists in the database we can just update our record
        // that is already in this database using the current IDs in this "where"
        // clause to only update this model. Otherwise, we'll just insert them.
        if ($this->exists) {
            $saved = $this->isDirty() ?
                $this->performUpdate($query) : true;
        }

        // If the model is brand new, we'll insert it into our database and set the
        // ID attribute on the model to the value of the newly inserted row's ID
        // which is typically an auto-increment value managed by the database.
        else {
            $saved = $this->performInsert($query);

            if (! $this->getConnectionName() &&
                $connection = $query->getConnection()) {
                $this->setConnection($connection->getName());
            }
        }

        // If the model is successfully saved, we need to do a few more things once
        // that is done. We will call the "saved" method here to run any actions
        // we need to happen after a model gets successfully saved right here.
        if ($saved) {
            $this->finishSave($options);
        }

        return $saved;
    }
}
