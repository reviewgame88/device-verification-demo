<?php
// app/Services/DeviceDetectorService.php

namespace App\Services;

use App\Constants\DeviceType;
use Jenssegers\Agent\Agent;

class DeviceDetectorService
{
    private $agent;

    public function __construct()
    {
        $this->agent = new Agent();
    }

    /**
     * Detect device type from user agent
     */
    public function detectDeviceType(?string $user_agent = null): string
    {
        if ($user_agent) {
            $this->agent->setUserAgent($user_agent);
        }

        // Check if it's a tablet first (some tablets can be detected as mobile)
        if ($this->agent->isTablet()) {
            return DeviceType::TABLET;
        }

        // Then check if it's a mobile device
        if ($this->agent->isMobile()) {
            return DeviceType::MOBILE;
        }

        // If not tablet or mobile, assume it's a web browser
        return DeviceType::WEB;
    }

    /**
     * Get detailed device info
     */
    public function getDeviceInfo(?string $user_agent = null): array
    {
        if ($user_agent) {
            $this->agent->setUserAgent($user_agent);
        }

        return [
            'device' => $this->agent->device(),
            'platform' => $this->agent->platform(),
            'platform_version' => $this->agent->version($this->agent->platform()),
            'browser' => $this->agent->browser(),
            'browser_version' => $this->agent->version($this->agent->browser()),
            'is_robot' => $this->agent->isRobot(),
            'robot_name' => $this->agent->robot(),
            'device_type' => $this->detectDeviceType($user_agent),
        ];
    }

    /**
     * Validate if claimed device type matches detected type
     */
    public function validateDeviceType(string $claimed_type, ?string $user_agent = null): bool
    {
        $detected_type = $this->detectDeviceType($user_agent);

        // Special case: Allow web access from tablets (e.g., iPad users might prefer desktop version)
        if ($claimed_type === DeviceType::WEB && $detected_type === DeviceType::TABLET) {
            return true;
        }

        return $claimed_type === $detected_type;
    }

    /**
     * Get fingerprint of device
     */
    public function getDeviceFingerprint(array $device_info): string
    {
        $fingerprint_data = [
            $device_info['device'] ?? '',
            $device_info['platform'] ?? '',
            $device_info['platform_version'] ?? '',
            $device_info['browser'] ?? '',
            $device_info['browser_version'] ?? '',
        ];

        return hash('sha256', implode('|', array_filter($fingerprint_data)));
    }
}