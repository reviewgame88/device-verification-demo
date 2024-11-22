<?php
namespace App\Http\Controllers\Learning;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class LearningController extends Controller
{
    use ApiResponse;

    public function checkAccess(Request $request)
    {
        $deviceInfo = $request->attributes->get('device_info');
        
        return $this->successResponse([
            'access_granted' => true,
            'device' => [
                'id' => $request->attributes->get('device_id'),
                'info' => $deviceInfo
            ],
            'user' => [
                'id' => $request->user()->id,
                'email' => $request->user()->email,
                'current_level' => 'Intermediate',
                'learning_streak' => 5, // Số ngày học liên tiếp
                'total_points' => 1200
            ]
        ], 'Access to English learning module granted');
    }

    public function listCourses(Request $request)
    {
        // Mock data cho khóa học tiếng Anh
        $courses = [
            [
                'id' => 1,
                'level' => 'Beginner',
                'title' => 'Basic English Communication',
                'description' => 'Master basic English conversations and grammar',
                'duration' => '30 days',
                'total_lessons' => 20,
                'skills' => ['Speaking', 'Listening', 'Grammar'],
                'progress' => 45 // Phần trăm hoàn thành
            ],
            [
                'id' => 2,
                'level' => 'Intermediate',
                'title' => 'Business English',
                'description' => 'English for professional environments',
                'duration' => '45 days',
                'total_lessons' => 25,
                'skills' => ['Business Writing', 'Presentation', 'Negotiation'],
                'progress' => 20
            ],
            [
                'id' => 3,
                'level' => 'Advanced',
                'title' => 'IELTS Preparation',
                'description' => 'Comprehensive IELTS training program',
                'duration' => '60 days',
                'total_lessons' => 30,
                'skills' => ['Reading', 'Writing', 'Speaking', 'Listening'],
                'progress' => 0
            ]
        ];

        return $this->successResponse($courses, 'English courses retrieved successfully');
    }

    public function getCourse(Request $request, $courseId)
    {
        // Mock data cho một khóa học cụ thể
        $course = [
            'id' => $courseId,
            'title' => 'Business English',
            'level' => 'Intermediate',
            'description' => 'Master English in professional environments',
            'duration' => '45 days',
            'total_lessons' => 25,
            'current_progress' => 20,
            'chapters' => [
                [
                    'id' => 1,
                    'title' => 'Office Communication',
                    'duration' => '5 days',
                    'lessons' => [
                        [
                            'id' => 1,
                            'title' => 'Email Writing',
                            'type' => 'Writing',
                            'duration' => '30 minutes',
                            'status' => 'completed'
                        ],
                        [
                            'id' => 2,
                            'title' => 'Meeting Vocabulary',
                            'type' => 'Vocabulary',
                            'duration' => '45 minutes',
                            'status' => 'in_progress'
                        ]
                    ]
                ],
                [
                    'id' => 2,
                    'title' => 'Presentation Skills',
                    'duration' => '7 days',
                    'lessons' => [
                        [
                            'id' => 3,
                            'title' => 'Structure Your Presentation',
                            'type' => 'Speaking',
                            'duration' => '45 minutes',
                            'status' => 'locked'
                        ],
                        [
                            'id' => 4,
                            'title' => 'Q&A Handling',
                            'type' => 'Speaking',
                            'duration' => '30 minutes',
                            'status' => 'locked'
                        ]
                    ]
                ]
            ],
            'learning_stats' => [
                'time_spent' => '12 hours',
                'completed_lessons' => 5,
                'vocabulary_learned' => 150,
                'speaking_exercises' => 8,
                'writing_tasks' => 4
            ],
            'access_info' => [
                'device_id' => $request->attributes->get('device_id'),
                'device_type' => $request->header('X-Device-Type'),
                'last_accessed' => now()->toIso8601String(),
                'remaining_days' => 35
            ]
        ];

        return $this->successResponse($course, 'Course details retrieved successfully');
    }
}