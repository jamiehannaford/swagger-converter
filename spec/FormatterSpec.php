<?php

namespace spec\SwaggerConverter;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class FormatterSpec extends ObjectBehavior
{
    function it_sanitizes_text()
    {
        $text = <<<EOT
 <p> Lorem ipsum dolor sit amet,
        consectetur adipiscing elit,
        ut labore et dolore magna aliqua. </p>
  Ut enim ad minim veniam, quis nostrud.
EOT;

        $this->sanitizeText($text)->shouldReturn(
            'Lorem ipsum dolor sit amet, consectetur adipiscing elit, ut labore et dolore magna aliqua. '.
            'Ut enim ad minim veniam, quis nostrud.'
        );
    }

    function it_formats_body_location_as_json()
    {
        $this->formatLocation('body')->shouldReturn('json');
    }

    function it_formats_path_location_as_uri()
    {
        $this->formatLocation('path')->shouldReturn('uri');
    }

    function it_formats_other_location_as_themselves()
    {
        $this->formatLocation('query')->shouldReturn('query');
        $this->formatLocation('header')->shouldReturn('header');
    }

    function it_formats_to_camel_case()
    {
        $this->toCamelCase('something_in_snake_case')->shouldReturn('somethingInSnakeCase');
    }

    function it_formats_path()
    {
        $this->formatPath('/v2/{tenant_id}/servers/{server_id}')->shouldReturn('servers/{serverId}');
    }
}