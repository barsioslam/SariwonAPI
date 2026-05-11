<?php

namespace SariwonAPI\Endpoints;

use \ReflectionClass;
use \ReflectionMethod;
use SariwonAPI\Endpoints\Endpoint;

abstract class AbstractEndpoint {

    public static function getEndpoints(): array {

        $reflection = new ReflectionClass(static::class);
        $endpoints = [];

        foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            
            // on ignore les méthodes héritées de la classe mère
            if ($method->getDeclaringClass()->getName() !== $reflection->getName()) {
                continue;
            }

            $attributes = $method->getAttributes(Endpoint::class);

            foreach ($attributes as $attribute) {

                /** @var Endpoint $instance */
                $instance = $attribute->newInstance();

                $endpoints[] = [
                    'base' => $reflection->getShortName(),
                    'access' => $instance->access,
                    'method' => strtolower($instance->method),
                    'url' => $instance->url,
                    'description' => $instance->description,
                    'handler' => $method->getName(),
                ];

            }

        }

        return $endpoints;

    }

}