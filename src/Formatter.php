<?php

namespace SwaggerConverter;

class Formatter
{
    public function sanitizeText($string)
    {
        return trim(preg_replace('#([\s\n\t]+){2}#', ' ', strip_tags($string)));
    }

    public function formatLocation($string)
    {
        switch ($string) {
            case 'body':
                return 'json';
            case 'path':
                return 'uri';
        }

        return $string;
    }

    public function toCamelCase($string)
    {
        return preg_replace_callback('#[_|-](\w)#', function($char) {
            return ucfirst($char[1]);
        }, $string);
    }

    public function formatPath($string)
    {
        $trimmed = preg_replace('#\/v[\d]{1}\/\{tenant_id\}#', '', $string);

        $path = '';

        foreach (explode('/', $trimmed) as $bit) {
            $path .= '/' . $this->toCamelCase($bit);
        }

        return trim($path, '/');
    }
}
