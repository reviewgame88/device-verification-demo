<?php

namespace App\Http\Middleware;

use Closure;
use App\Services\DeviceVerificationService;
use App\Traits\ApiResponse;
use App\Constants\ApiErrorCode;
use Illuminate\Support\Facades\Redis;
use App\Constants\DeviceType;
use App\Services\DeviceDetectorService;
use Illuminate\Support\Facades\Cache;

class DeviceVerificationMiddleware
{

    use ApiResponse;
    
    private $deviceService;

    public function __construct(
        DeviceVerificationService $deviceService
    )
    {
        $this->deviceService = $deviceService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle($request, Closure $next)
    {
        if ($request->routeIs('api.login')) {
            return $next($request);
        }

        if (!$request->user()) {
            return $this->errorResponse(ApiErrorCode::UNAUTHORIZED);
        }
        
        $device_type = $request->header('X-Device-Type');
        
        if (!$device_type || !DeviceType::isValid($device_type)) {
            return $this->errorResponse(ApiErrorCode::DEVICE_TYPE_INVALID);
        }

        $device_info = [
            'user_agent' => $request->userAgent(),
            'ip' => $request->ip(),
            'type' => $device_type
        ];

        $user_id = $request->user()->id;
        $device_id = $this->deviceService->generateDeviceId($device_info);
        
        // Thêm thông tin thiết bị để sử dụng ( nếu cần )
        $request->attributes->add([
            'device_info' => $device_info,
            'device_id' => $device_id
        ]);
        
        // sử dụng redis để giảm tải
        $redis_key = "user_devices:{$user_id}";
        $device_data = Redis::hget($redis_key, $device_id);

        if ($device_data) {
            $device_data = json_decode($device_data, true);
            $device_data['last_access'] = time();
            Redis::hset($redis_key, $device_id, json_encode($device_data));
            
            return $next($request);
        }

        $verification_result = $this->deviceService->verifyDevice($user_id, $device_id, $device_type);

        if (!$verification_result['success']) {
            $error_code = ApiErrorCode::DEVICE_VERIFICATION_FAILED;
            
            if (isset($verification_result['error_code'])) {
                $error_code = $verification_result['error_code'];
            }

            return $this->errorResponse($error_code);
        }
        
        return $next($request);
    }
}
