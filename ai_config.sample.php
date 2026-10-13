<?php
// ai_config.sample.php
// 1. Copy this file to  ai_config.php  (that file is gitignored, so your key is never committed).
// 2. Paste your API key below.
// 3. Leave 'api_key' empty to run without AI: TutorLink then uses "Rule-based match".
return [
    // 'anthropic' (Claude) or 'gemini' (Google AI Studio, has a free tier)
    'provider' => 'anthropic',

    // Your secret API key. Never put it in app.js or any file that is committed.
    'api_key'  => '',

    // Model name. Check your provider's model list; change it here if a name is retired.
    //   anthropic: 'claude-haiku-5-5'      gemini: 'gemini-2.5-flash'
    'model'    => 'claude-haiku-5-5',

    // Seconds to wait for the AI before falling back to rule-based matching
    'timeout'  => 10,

    // Leave empty for the real provider. Only used by tests to point at a fake AI server.
    'base_url' => '',
];
