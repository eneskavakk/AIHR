<?php

return [
    'ai_service_url' => env('AI_SERVICE_URL', 'http://127.0.0.1:8001'),
    'ai_service_timeout' => env('AI_SERVICE_TIMEOUT', 180),
    'ai_service_token' => env('AI_SERVICE_TOKEN', ''),
    'max_cv_upload_size_kb' => env('MAX_CV_UPLOAD_SIZE_KB', 5120),
    'admin_emails' => array_values(array_filter(array_map(
        'trim',
        explode(',', env('FILAMENT_ADMIN_EMAILS', 'admin@example.com')),
    ))),
];
