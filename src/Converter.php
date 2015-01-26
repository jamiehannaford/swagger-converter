<?php

namespace SwaggerConverter;

use Symfony\Component\Yaml\Yaml;

class Converter
{
    private $params;
    private $operations;

    private $ignoredParams = [
        'tenant_id'            => true,
        'X-Compute-Request-ID' => true,
    ];

    private $formatter;

    public function __construct(array $ignoredParams = [])
    {
        foreach ($ignoredParams as $ignoredParam) {
            $this->ignoredParams[$ignoredParam] = true;
        }

        $this->formatter = new Formatter();
    }

    private function validFile($file)
    {
        return file_exists($file) && is_readable($file);
    }

    public function convert($inputFile, $outputFile = null)
    {
        if (!$this->validFile($inputFile)) {
            throw new \InvalidArgumentException(sprintf(
                '%s either does not exist or cannot be read', $inputFile
            ));
        }

        $json = json_decode(file_get_contents($inputFile), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \InvalidArgumentException(sprintf(
                'JSON decode error: %s', json_last_error_msg()
            ));
        }

        $this->params = $this->operations = [];

        foreach ($json['paths'] as $path => $operations) {
            $path = $this->formatter->formatPath($path);

            if (isset($operations['parameters'])) {
                $this->stockParams($operations['parameters']);
                unset($operations['parameters']);
            }

            foreach ($operations as $verb => $operation) {
                $params = $this->params;
                if (!in_array($verb, ['put', 'post', 'patch'])) {
                    foreach ($this->params as $name => $param) {
                        if (in_array($param['location'], ['uri', 'query'])) {
                            $params[$name] = $param;
                        }
                    }
                }

                $response = $this->parseResponse($operation['responses']);

                $this->operations[ucfirst($operation['operationId'])] = [
                    'method'      => strtoupper($verb),
                    'path'        => $path,
                    'description' => $this->formatter->sanitizeText($operation['description']),
                    'params'      => $params,
                    'response'    => $response,
                ];
            }
        }

        return $this->output($outputFile);
    }

    private function output($outputFile = null)
    {
        $data = Yaml::dump($this->operations, 10, 3);

        if ($outputFile) {
            file_put_contents($outputFile . '.yml', $data);
        } else {
            return $data;
        }
    }

    private function parseResponse($data)
    {
        foreach ($data as $status => $response) {
            if ($status > 204) {
                continue;
            }
            if (isset($response['examples']) && isset($response['examples']['application/json'])) {
                $jsonData = json_decode($response['examples']['application/json']);
                return $this->descendResponse($jsonData);
            }
        }
    }

    private function descendResponse($data, $parentKey = null)
    {
        $things = [];
        if (is_object($data)) {
            $props = [];
            foreach ($data as $key => $val) {
                $_key = $parentKey ? $parentKey . '.' . $key : $key;
                $resp = $this->descendResponse($val, $_key);
                $props[$this->formatter->toCamelCase($key)] = $resp;
            }
            $things += [
                'type' => 'object',
                'properties' => $props,
            ];
        } elseif (is_array($data) && isset($data[0])) {
            $resp = $this->descendResponse($data[0], $parentKey);
            $things += [
                'type' => 'array',
                'items' => $resp,
            ];
        } else {
            return [
                'type' => gettype($data),
                'path' => $parentKey
            ];
        }
        return $things;
    }

    private function stockParams($params)
    {
        foreach ($params as $param) {
            if (isset($this->ignoredParams[$param['name']])) {
                continue;
            }

            $name = $this->formatter->toCamelCase($param['name']);

            $this->params[$name] = [
                'required'    => $param['required'] == true,
                'location'    => $this->formatter->formatLocation($param['in']),
                'type'        => isset($param['type']) ? $param['type'] : '',
                'description' => $this->formatter->sanitizeText($param['description']),
            ];

            if ($this->formatter->formatLocation($param['in']) != 'uri' && $name != $param['name']) {
                $this->params[$name]['path'] = $param['name'];
            }
        }
    }
}