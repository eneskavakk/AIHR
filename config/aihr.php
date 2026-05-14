<?php

return [
    'ai_service_url' => env('AI_SERVICE_URL', 'http://127.0.0.1:8001'),
    'ai_service_timeout' => env('AI_SERVICE_TIMEOUT', 180),
    'ai_service_token' => env('AI_SERVICE_TOKEN', ''),
    'ollama_base_url' => env('OLLAMA_BASE_URL', 'http://127.0.0.1:11434'),
    'ollama_model' => env('OLLAMA_MODEL', 'qwen2.5:7b'),
    'ollama_fallback_model' => env('OLLAMA_FALLBACK_MODEL', 'llama3.1:8b'),
    'max_cv_upload_size_kb' => env('MAX_CV_UPLOAD_SIZE_KB', 5120),
    'analysis_retry_count' => env('AI_ANALYSIS_RETRY_COUNT', 1),
    'admin_emails' => array_values(array_filter(array_map(
        'trim',
        explode(',', env('FILAMENT_ADMIN_EMAILS', 'admin@example.com')),
    ))),
];
