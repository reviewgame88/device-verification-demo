<?php

namespace App\Constants;

class ApiErrorCode
{
    // Authentication & Authorization Errors (1xxx)
    const UNAUTHORIZED = '1000';
    const INVALID_CREDENTIALS = '1001';
    const TOKEN_EXPIRED = '1002';
    const TOKEN_INVALID = '1003';
    const TOKEN_BLACKLISTED = '1004';
    const INSUFFICIENT_PERMISSIONS = '1005';

    // Device Verification Errors (2xxx)
    const DEVICE_TYPE_REQUIRED = '2000';
    const DEVICE_TYPE_INVALID = '2001';
    const DEVICE_LIMIT_REACHED = '2002';
    const DEVICE_TYPE_EXISTS = '2003';
    const DEVICE_NOT_FOUND = '2004';
    const DEVICE_VERIFICATION_FAILED = '2005';

    // Validation Errors (3xxx)
    const VALIDATION_ERROR = '3000';
    const INVALID_PARAMETERS = '3001';
    const MISSING_REQUIRED_FIELD = '3002';
    const INVALID_FORMAT = '3003';

    // Resource Errors (4xxx)
    const RESOURCE_NOT_FOUND = '4000';
    const RESOURCE_ALREADY_EXISTS = '4001';
    const RESOURCE_CONFLICT = '4002';

    // Server Errors (5xxx)
    const SERVER_ERROR = '5000';
    const SERVICE_UNAVAILABLE = '5001';
    const DATABASE_ERROR = '5002';
    const CACHE_ERROR = '5003';

    private static $messages = [
        // Authentication & Authorization
        self::UNAUTHORIZED => 'Unauthorized access',
        self::INVALID_CREDENTIALS => 'Invalid credentials provided',
        self::TOKEN_EXPIRED => 'Authentication token has expired',
        self::TOKEN_INVALID => 'Invalid authentication token',
        self::TOKEN_BLACKLISTED => 'Token has been blacklisted',
        self::INSUFFICIENT_PERMISSIONS => 'Insufficient permissions for this action',

        // Device Verification
        self::DEVICE_TYPE_REQUIRED => 'Device type header is required',
        self::DEVICE_TYPE_INVALID => 'Invalid device type provided',
        self::DEVICE_LIMIT_REACHED => 'Maximum device limit reached',
        self::DEVICE_TYPE_EXISTS => 'Device type already registered',
        self::DEVICE_NOT_FOUND => 'Device not found',
        self::DEVICE_VERIFICATION_FAILED => 'Device verification failed',

        // Validation
        self::VALIDATION_ERROR => 'Validation error occurred',
        self::INVALID_PARAMETERS => 'Invalid parameters provided',
        self::MISSING_REQUIRED_FIELD => 'Required field is missing',
        self::INVALID_FORMAT => 'Invalid data format',

        // Resource
        self::RESOURCE_NOT_FOUND => 'Requested resource not found',
        self::RESOURCE_ALREADY_EXISTS => 'Resource already exists',
        self::RESOURCE_CONFLICT => 'Resource conflict occurred',

        // Server
        self::SERVER_ERROR => 'Internal server error occurred',
        self::SERVICE_UNAVAILABLE => 'Service temporarily unavailable',
        self::DATABASE_ERROR => 'Database error occurred',
        self::CACHE_ERROR => 'Cache error occurred',
    ];

    private static $statusCodes = [
        // Authentication & Authorization
        self::UNAUTHORIZED => 401,
        self::INVALID_CREDENTIALS => 401,
        self::TOKEN_EXPIRED => 401,
        self::TOKEN_INVALID => 401,
        self::TOKEN_BLACKLISTED => 401,
        self::INSUFFICIENT_PERMISSIONS => 403,

        // Device Verification
        self::DEVICE_TYPE_REQUIRED => 400,
        self::DEVICE_TYPE_INVALID => 400,
        self::DEVICE_LIMIT_REACHED => 403,
        self::DEVICE_TYPE_EXISTS => 403,
        self::DEVICE_NOT_FOUND => 404,
        self::DEVICE_VERIFICATION_FAILED => 400,

        // Validation
        self::VALIDATION_ERROR => 400,
        self::INVALID_PARAMETERS => 400,
        self::MISSING_REQUIRED_FIELD => 400,
        self::INVALID_FORMAT => 400,

        // Resource
        self::RESOURCE_NOT_FOUND => 404,
        self::RESOURCE_ALREADY_EXISTS => 409,
        self::RESOURCE_CONFLICT => 409,

        // Server
        self::SERVER_ERROR => 500,
        self::SERVICE_UNAVAILABLE => 503,
        self::DATABASE_ERROR => 500,
        self::CACHE_ERROR => 500,
    ];

    public static function getMessage(string $code): string
    {
        return self::$messages[$code] ?? 'Unknown error occurred';
    }

    public static function getStatusCode(string $code): int
    {
        return self::$statusCodes[$code] ?? 500;
    }
}