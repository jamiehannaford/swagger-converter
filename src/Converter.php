<?php

namespace SwaggerConverter;

class Converter
{
    public function parse($file)
    {
        if (!file_exists($file) || !is_readable($file)) {
            throw new \InvalidArgumentException(sprintf(
                '%s either does not exist or cannot be read', $file
            ));
        }

        $json = json_decode(file_get_contents($file), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \InvalidArgumentException(sprintf(
                'JSON decode error: %s', json_last_error_msg()
            ));
        }
    }
}