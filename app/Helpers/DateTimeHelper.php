<?php

namespace App\Helpers;

use DateTime;
use Carbon\Carbon;
use InvalidArgumentException;

class DateTimeHelper
{
    /**
     * Convert any timestamp format to Unix timestamp
     *
     * @param mixed $value DateTime|Carbon|string|int|null
     * @return int Unix timestamp
     * @throws InvalidArgumentException
     */
    public static function convertToTimestamp($value): int
    {
        if (is_null($value)) {
            return time();
        }

        if (is_numeric($value)) {
            return (int) $value;
        }

        if ($value instanceof DateTime || $value instanceof Carbon) {
            return $value->timestamp;
        }

        if (is_string($value)) {
            $timestamp = strtotime($value);
            if ($timestamp === false) {
                throw new InvalidArgumentException("Unable to parse date string: {$value}");
            }
            return $timestamp;
        }

        throw new InvalidArgumentException('Invalid timestamp format: ' . gettype($value));
    }

    /**
     * Format timestamp to specific format
     *
     * @param int $timestamp
     * @param string $format
     * @return string
     */
    public static function formatTimestamp(int $timestamp, string $format = 'Y-m-d H:i:s'): string
    {
        return date($format, $timestamp);
    }

    /**
     * Check if timestamp is valid
     *
     * @param mixed $timestamp
     * @return bool
     */
    public static function isValidTimestamp($timestamp): bool
    {
        if (!is_numeric($timestamp)) {
            return false;
        }

        try {
            new DateTime('@' . $timestamp);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}