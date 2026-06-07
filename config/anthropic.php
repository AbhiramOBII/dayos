<?php

return [

    'api_key' => env('ANTHROPIC_API_KEY'),

    'model' => env('ANTHROPIC_MODEL', 'claude-sonnet-4-5'),

    'max_tokens' => (int) env('ANTHROPIC_MAX_TOKENS', 1024),

    'api_version' => '2023-06-01',

    'base_url' => 'https://api.anthropic.com/v1',

];
