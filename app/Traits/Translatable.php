<?php

namespace App\Traits;

use App\Models\Translation;

trait Translatable
{
    /**
     * MorphMany relationship to Translation model.
     */
    public function translations()
    {
        return $this->morphMany(Translation::class, 'translatable');
    }

    /**
     * Get translated value for a field and locale.
     * Fallback to model's default value if not found.
     */
    public function getTranslation($field, $locale)
    {
        // Normalize locale to first two chars (e.g. 'ar-SA' -> 'ar')
        $normLocale = substr($locale, 0, 2);
        
        if ($normLocale === 'en' || !in_array($normLocale, ['en', 'ar'])) {
            return $this->{$field};
        }

        $translation = $this->translations()
            ->where('locale', $normLocale)
            ->where('field', $field)
            ->first();

        if ($translation) {
            $casts = $this->getCasts();
            // If the field is cast as an array/json in the model, decode the translation value
            if (isset($casts[$field]) && ($casts[$field] === 'array' || $casts[$field] === 'json')) {
                return json_decode($translation->value, true);
            }
            return $translation->value;
        }

        return $this->{$field};
    }

    /**
     * Set a translation for a field and locale.
     */
    public function setTranslation($field, $locale, $value)
    {
        return $this->translations()->updateOrCreate(
            ['locale' => $locale, 'field' => $field],
            ['value' => $value]
        );
    }

    /**
     * Returns the model with all translatable fields translated for the given locale.
     */
    public function translated($locale = 'en')
    {
        if ($locale === 'en') {
            return $this;
        }

        foreach ($this->translatable as $field) {
            $this->{$field} = $this->getTranslation($field, $locale);
        }

        return $this;
    }
}
