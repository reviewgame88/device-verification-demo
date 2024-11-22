<?php

namespace App\Http\Controllers;

use App\Services\DeviceVerificationService;
use App\Traits\ApiResponse;
use App\Constants\ApiErrorCode;
use Illuminate\Http\Request;
use App\Constants\DeviceType;

class DeviceController extends Controller
{
    use ApiResponse;

    private $deviceService;

    public function __construct(DeviceVerificationService $deviceService)
    {
        $this->deviceService = $deviceService;
    }

    public function index(Request $request)
    {
        $devices = $request->user()
        ->activeDevices()
        ->get()
        ->map(function ($device) {
            $device->device_type_name = DeviceType::getReadableName($device->device_type);
            return $device;
        });

        return $this->successResponse($devices, 'Devices retrieved successfully');
    }

    public function register(Request $request)
    {
        $device_type = $request->header('X-Device-Type');
        
        if (!$device_type || !DeviceType::isValid($device_type)) {
            return $this->errorResponse(ApiErrorCode::DEVICE_TYPE_INVALID);
        }

        $device_info = [
            'user_agent' => $request->userAgent(),
            'ip' => $request->ip(),
            'type' => $device_type
        ];
        
        $device_id = $this->deviceService->generateDeviceId($device_info);
        
        $result = $this->deviceService->verifyDevice(
            $request->user()->id,
            $device_id,
            $device_type,
        );
        
        if (!$result['success']) {
            $error_code = ApiErrorCode::DEVICE_VERIFICATION_FAILED;
            
            if (isset($verification_result['error_code'])) {
                $error_code = $verification_result['error_code'];
            }
            
            return $this->errorResponse($error_code);
        }
        
        return $this->successResponse(
            [
                'device_id' => $device_id,
                'device_type' => $device_type,
                'device_type_name' => DeviceType::getReadableName($device_type)
            ],
            'Device registered successfully'
        );
    }

    public function remove(Request $request, $device_id)
    {
        $device = $request->user()->devices()->where('device_id', $device_id)->first();
        
        if (!$device) {
            return $this->errorResponse(ApiErrorCode::DEVICE_NOT_FOUND);
        }

        $device->update(['is_active' => false]);
        return $this->successResponse(null, 'Device removed successfully');
    }
}