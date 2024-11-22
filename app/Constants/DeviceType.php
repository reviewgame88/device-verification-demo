<?php
// app/Constants/DeviceType.php

namespace App\Constants;

class DeviceType
{
    public const WEB = 'web';
    public const MOBILE = 'mobile';
    public const TABLET = 'tablet';

    private static $readableNames = [
        self::WEB => 'Web Browser',
        self::MOBILE => 'Mobile Device',
        self::TABLET => 'Tablet Device'
    ];

    /**
     * Get all supported device types
     */
    public static function all(): array
    {
        return [
            self::WEB,
            self::MOBILE,
            self::TABLET
        ];
    }

    /**
     * Check if device type is valid
     */
    public static function isValid(string $type): bool
    {
        return in_array($type, self::all());
    }

    /**
     * Get validation rules for device type
     */
    public static function validationRules(): string
    {
        return 'required|in:' . implode(',', self::all());
    }

    /**
     * Get human readable name for device type
     */
    public static function getReadableName(string $type): string
    {
        return self::$readableNames[$type] ?? 'Unknown Device';
    }
}