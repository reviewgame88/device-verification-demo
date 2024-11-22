<?php

namespace App\Services;

use App\Models\UserDevice;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Constants\DeviceType;
use App\Constants\ApiErrorCode;
use App\Exceptions\DeviceNotFoundException;
use App\Helpers\DateTimeHelper;
use Exception;

class DeviceVerificationService 
{
    private const REDIS_TTL = 3600; // 1 hour
    private const TOTAL_DEVICE_LIMIT = 3; // Tối đa 3 thiết bị truy cập
    private const REDIS_KEY_PREFIX = 'user_devices:';
    /**
     * Tạo mã định danh thiết bị (unique)
     */
    public function generateDeviceId(array $device_info): string 
    {
        $data = [
            $device_info['user_agent'],
            $device_info['ip'],
            $device_info['type'],
            // Add more device fingerprinting data here
        ];
        
        return hash('sha256', implode('|', $data));
    }

    /**
     * Xác thực và lưu trữ thông tin
     */
    public function verifyDevice(string $user_id, string $device_id, string $device_type): array
    {
        return DB::transaction(function() use ($user_id, $device_id, $device_type) {
            try {
                if (!DeviceType::isValid($device_type)) {
                    return $this->errorResult(ApiErrorCode::DEVICE_TYPE_INVALID);
                }

                // Check Redis first
                $redis_key = $this->getRedisKey($user_id);
                $device_data = Redis::hget($redis_key, $device_id);
                
                if ($device_data) {
                    $this->updateLastAccess($user_id, $device_id);
                    return $this->successResult('Device verified');
                }

                $active_devices = $this->getActiveDevices($user_id);

                $existing_device = $active_devices->firstWhere('device_id', $device_id);
                if ($existing_device) {
                    return $this->handleExistingDevice($user_id, $existing_device);
                }

                $validation_result = $this->validateDeviceLimits($active_devices, $device_type);
                if (!$validation_result['success']) {
                    return $validation_result;
                }

                return $this->registerNewDevice($user_id, $device_id, $device_type);

            } catch (\Exception $e) {
                $this->cleanupRedis($user_id, $device_id);
                Log::error('Device verification failed', [
                    'user_id' => $user_id,
                    'device_id' => $device_id,
                    'error' => $e->getMessage()
                ]);
                throw $e;
            }
        });
    }

    /**
     * Update last access time
     */
    private function updateLastAccess(string $user_id, string $device_id): void
    {
        UserDevice::where('user_id', $user_id)
            ->where('device_id', $device_id)
            ->update(['last_access_at' => now()]);

        $redis_key = $this->getRedisKey($user_id);
        $device_data = Redis::hget($redis_key, $device_id);
        if ($device_data) {
            $device_data = json_decode($device_data, true);
            $device_data['last_access_at'] = now()->timestamp;
            Redis::hset($redis_key, $device_id, json_encode($device_data));
        }
    }

    /**
     * Store device data in Redis
     */
    private function storeInRedis(string $user_id, array $device_data): void
    {
        try {
            $redis_key = $this->getRedisKey($user_id);
            $redisData = [
                'id' => $device_data['device_id'],
                'type' => $device_data['device_type'],
                'device_info' => $device_data['device_info'],
                'registered_at' => DateTimeHelper::convertToTimestamp($device_data['registered_at']),
                'last_access_at' => DateTimeHelper::convertToTimestamp($device_data['last_access_at']),
                'is_active' => $device_data['is_active']
            ];

            Redis::hset($redis_key, $device_data['device_id'], json_encode($redisData));
            Redis::expire($redis_key, self::REDIS_TTL);
            
        } catch (Exception $e) {
            Log::error('Failed to store device in Redis', [
                'user_id' => $user_id,
                'device_id' => $device_data['device_id'],
                'error' => $e->getMessage()
            ]);
            throw $e; // Re-throw để xử lý ở transaction
        }
    }

    /**
     * Cleanup Redis in case of error
     */
    private function cleanupRedis(string $user_id, string $device_id): void
    {
        try {
            $redis_key = $this->getRedisKey($user_id);
            Redis::hdel($redis_key, $device_id);
        } catch (Exception $e) {
            Log::error('Failed to cleanup Redis', [
                'user_id' => $user_id,
                'device_id' => $device_id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get Redis key for user devices
     */
    private function getRedisKey(string $user_id): string
    {
        return self::REDIS_KEY_PREFIX . $user_id;
    }

    public function removeDevice($userId, $deviceId)
    {
        try {
            DB::beginTransaction();

            // Update database
            $device = UserDevice::where('user_id', $userId)
                ->where('device_id', $deviceId)
                ->first();

            if (!$device) {
                throw new DeviceNotFoundException();
            }

            $device->update(['is_active' => false]);

            // Clear Redis cache
            $this->clearDeviceCache($userId, $deviceId);

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function clearDeviceCache($userId, $deviceId)
    {
        // Clear from Redis hash
        Redis::hdel("user_devices:{$userId}", $deviceId);
        
        // Clear any other related cache
        $pattern = "device:*:{$userId}:{$deviceId}";
        foreach (Redis::keys($pattern) as $key) {
            Redis::del($key);
        }
    }

    /**
     * Get all active devices for a user
     */
    private function getActiveDevices(string $user_id): \Illuminate\Database\Eloquent\Collection
    {
        return UserDevice::where('user_id', $user_id)
            ->where('is_active', true)
            ->get();
    }

    /**
     * Handle existing device case
     */
    private function handleExistingDevice(string $user_id, UserDevice $device): array
    {
        $this->storeInRedis($user_id, $device->toArray());
        $this->updateLastAccess($user_id, $device->device_id);
        return $this->successResult('Device verified and cache restored');
    }

    /**
     * Validate device limits
     */
    private function validateDeviceLimits(\Illuminate\Database\Eloquent\Collection $active_devices, string $device_type): array
    {
        // Check device type limit
        if ($active_devices->where('device_type', $device_type)->count() > 0) {
            return $this->errorResult(ApiErrorCode::DEVICE_TYPE_EXISTS);
        }

        // Check total device limit
        if ($active_devices->count() >= self::TOTAL_DEVICE_LIMIT) {
            return $this->errorResult(ApiErrorCode::DEVICE_LIMIT_REACHED);
        }

        return $this->successResult();
    }

    /**
     * Register new device
     */
    private function registerNewDevice(string $user_id, string $device_id, string $device_type): array
    {
        $device_data = [
            'user_id' => $user_id,
            'device_id' => $device_id,
            'device_type' => $device_type,
            'device_info' => request()->attributes->get('device_info'),
            'registered_at' => now(),
            'last_access_at' => now(),
            'is_active' => true
        ];

        $this->storeInRedis($user_id, $device_data);
        UserDevice::create($device_data);

        return $this->successResult('Device registered successfully');
    }

    /**
     * Create success result
     */
    private function successResult(string $message = ''): array
    {
        return [
            'success' => true,
            'message' => $message
        ];
    }

    /**
     * Create error result
     */
    private function errorResult(string $error_code): array
    {
        return [
            'success' => false,
            'error_code' => $error_code
        ];
    }
}