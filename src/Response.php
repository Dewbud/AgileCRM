<?php

namespace Dewbud\AgileCRM;

use GuzzleHttp\Psr7\Response as GuzzleResponse;

class Response extends Fluent
{
    /** @var \GuzzleHttp\Psr7\Response */
    public $guzzle_response;

    public function __construct(GuzzleResponse $response)
    {
        $this->guzzle_response = $response;

        if ($response->getStatusCode() === 200) {
            parent::__construct(\json_decode($response->getBody()));
        } else {
            parent::__construct();
        }

        if (array_key_exists('properties', $this->attributes)) {

            // Map response properties to Fluent properties
            $props = [];

            foreach ($this->attributes['properties'] as $key => $field) {
                $props[$field->name] = $field->value;
            }

            $this->attributes['properties'] = (object) $props;
        }
    }
}
