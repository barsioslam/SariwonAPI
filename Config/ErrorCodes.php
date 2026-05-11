<?php

class ErrorCodes {

    // GENERAL CODES
    const INVALID_REQUEST = 400;
    const UNAUTHORIZED = 401;
    const FORBIDDEN = 403;
    const NOT_FOUND = 404;
    const INTERNAL_SERVER_ERROR = 500;
    
    // API CODES
    const UNKNOWN_ERROR = 1;
    const LOCKED_ACCESS = 1001;
    const ENDPOINT_NOT_FOUND = 1002;
    const INVALID_ENDPOINT_METHOD = 1003;
    const MISSING_PARAMETERS = 1004;
    const INVALID_PARAMETERS = 1005;

    public static function getMessage($code) {
        switch ($code) {
            case self::INVALID_REQUEST:
                return 'Invalid request.';
            case self::UNAUTHORIZED:
                return 'Unauthorized access.';
            case self::FORBIDDEN:
                return 'Forbidden.';
            case self::NOT_FOUND:
                return 'Resource not found.';
            case self::INTERNAL_SERVER_ERROR:
                return 'Internal server error.';
            case self::LOCKED_ACCESS:
                return 'Locked Access.';
            case self::ENDPOINT_NOT_FOUND:
                return 'Endpoint not found.';
            case self::INVALID_ENDPOINT_METHOD:
                return 'Invalid endpoint method.';
            case self::MISSING_PARAMETERS:
                return 'Missing parameters.';
            case self::INVALID_PARAMETERS:
                return 'Invalid parameters.';
            default:
                return 'Unknown error.';
        }
    }

}