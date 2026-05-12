<?php

namespace SariwonAPI\Config;

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

    // AUTH CODES
    const AUTH_INVALID_CREDENTIALS = 2001;
    const AUTH_EMAIL_TAKEN = 2002;
    const AUTH_USERNAME_TAKEN = 2003;
    const AUTH_NOT_AUTHENTICATED = 2004;

    // CHARACTER CODES
    const CHAR_NOT_FOUND = 3001;
    const CHAR_FORBIDDEN = 3002;

    // SERVER CODES
    const SERVER_NOT_FOUND      = 4001;
    const SERVER_FORBIDDEN      = 4002;
    const SERVER_INVITE_INVALID = 4003;
    const SERVER_INVITE_TAKEN   = 4004;

    public static function getMessage(int $code): string {
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
            case self::AUTH_INVALID_CREDENTIALS:
                return 'Invalid email or password.';
            case self::AUTH_EMAIL_TAKEN:
                return 'This email is already in use.';
            case self::AUTH_USERNAME_TAKEN:
                return 'This username is already taken.';
            case self::AUTH_NOT_AUTHENTICATED:
                return 'Authentication required.';
            case self::CHAR_NOT_FOUND:
                return 'Character not found.';
            case self::CHAR_FORBIDDEN:
                return 'You do not own this character.';
            case self::SERVER_NOT_FOUND:
                return 'Server not found.';
            case self::SERVER_FORBIDDEN:
                return 'You do not have access to this server.';
            case self::SERVER_INVITE_INVALID:
                return 'Invalid invite code.';
            case self::SERVER_INVITE_TAKEN:
                return 'This invite code is already in use.';
            default:
                return 'Unknown error.';
        }
    }

}