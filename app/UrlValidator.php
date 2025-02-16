<?php

namespace Hexlet\Code;

use Valitron\Validator;

class UrlValidator
{
    public static function validate(array $data): array
    {
        $validator = new Validator($data);
        $validator->rule('required', 'name')->message('URL не должен быть пустым');
        $validator->rule('url', 'name')->message('Некорректный URL');
        $validator->rule('lengthMax', 'name', 255)->message('Некорректный URL');
        $validator->validate();

        return $validator->errors();
    }

    public static function normalize(string $url): ?string
    {
        $parsedUrl = parse_url($url);
        if (!isset($parsedUrl['scheme'], $parsedUrl['host'])) {
            return null; // Некорректный URL
        }

        return "{$parsedUrl['scheme']}://{$parsedUrl['host']}";
    }
}
