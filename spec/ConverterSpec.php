<?php

namespace spec\SwaggerConverter;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ConverterSpec extends ObjectBehavior
{
    private $jsonFile;

    function let()
    {
        $this->jsonFile = __DIR__ . '/test-compute.json';
    }

    function it_converts_any_file()
    {
        $this->parse($this->jsonFile);
    }

    function it_throws_exception_if_inputted_file_cannot_be_read()
    {
        $this->shouldThrow('InvalidArgumentException')->duringParse('blah');
    }

    function it_throws_exception_if_inputted_file_is_not_valid_json()
    {
        $file = '/tmp/foo.json';
        file_put_contents($file, '{{{"');

        $this->shouldThrow('InvalidArgumentException')->duringParse($file);

        unlink($file);
    }
}