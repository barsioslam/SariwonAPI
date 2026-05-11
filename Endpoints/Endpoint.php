<?php

namespace SariwonAPI\Endpoints;

use \Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class Endpoint {

    public function __construct(
        public string $method,
        public string $url,
        public string $description = '',
        public string $access = 'all'
    ) {}
    
}