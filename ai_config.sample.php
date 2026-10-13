<?php
// ai_config.sample.php - AI API Configuration Template
// Rename/copy this file to ai_config.php and insert your AI provider API key.

if (!defined('AI_API_KEY')) {
    define('AI_API_KEY', '');
}
if (!defined('AI_API_URL')) {
    define('AI_API_URL', 'https://api.openai.com/v1/chat/completions');
}
if (!defined('AI_MODEL')) {
    define('AI_MODEL', 'gpt-4o-mini');
}
?>