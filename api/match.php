<?php
// api/match.php - AI & Rule-Based Tutor Matching Endpoint
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/helpers.php';

$user = requireRole(['student']);
$input = getInput();

$subject = trim($input['subject'] ?? 'Mathematics');
$grade = trim($input['grade'] ?? 'Senior High');
$learningStyle = trim($input['learning_style'] ?? ($input['learningStyle'] ?? 'Visual & Diagrams'));
$preferredTime = trim($input['preferred_time'] ?? ($input['preferredTime'] ?? 'Afternoon'));

if (empty($subject)) {
    jsonError(400, 'Subject is required.');
}

// Validate subject against subjects catalog
$stCat = $pdo->prepare("SELECT COUNT(*) as cnt FROM subjects WHERE LOWER(name) = ?");
$stCat->execute([strtolower($subject)]);
if ($stCat->fetch()['cnt'] == 0) {
    jsonError(400, "Subject '$subject' is not in the subjects catalog.");
}

// Validate learning style against allow-list
$allowedStyles = ['Visual & Diagrams', 'Hands-on Practice', 'Step-by-Step Explanation', 'Auditory & Discussion'];
if (!in_array($learningStyle, $allowedStyles, true)) {
    $learningStyle = 'Step-by-Step Explanation';
}

// Check 10-minute cache per student + subject
$cacheKey = 'ai_match_' . $user['id'] . '_' . md5(strtolower($subject) . strtolower($learningStyle));
if (isset($_SESSION[$cacheKey]) && (time() - $_SESSION[$cacheKey]['timestamp'] < 600)) {
    echo json_encode($_SESSION[$cacheKey]['response']);
    exit();
}

// Fetch approved and non-deactivated tutors teaching the subject
$stmt = $pdo->prepare("SELECT id, name, initials, rating, reviews_count, hourly_rate, subjects, learning_styles, bio, availability_slots FROM tutors WHERE approval_status = 'Approved' AND deactivated = 0");
$stmt->execute();
$allTutors = $stmt->fetchAll();

$eligibleTutors = [];
foreach ($allTutors as $t) {
    $subList = array_map('strtolower', array_map('trim', explode(',', $t['subjects'])));
    if (in_array(strtolower($subject), $subList, true)) {
        $eligibleTutors[] = $t;
    }
}

if (empty($eligibleTutors)) {
    $resPayload = [
        'status' => 'success',
        'match_mode' => 'Rule-based match',
        'subject' => $subject,
        'data' => []
    ];
    echo json_encode($resPayload);
    exit();
}

// Load AI config if available
$aiKey = '';
$aiUrl = 'https://api.openai.com/v1/chat/completions';
$aiModel = 'gpt-4o-mini';

if (file_exists(__DIR__ . '/../ai_config.php')) {
    require_once __DIR__ . '/../ai_config.php';
    if (defined('AI_API_KEY')) $aiKey = AI_API_KEY;
    if (defined('AI_API_URL')) $aiUrl = AI_API_URL;
    if (defined('AI_MODEL')) $aiModel = AI_MODEL;
}

$useAI = false;
$aiResults = null;

if (!empty($aiKey)) {
    $safeTutors = array_map(function($t) {
        return [
            'id' => $t['id'],
            'name' => $t['name'],
            'rating' => (float)$t['rating'],
            'reviewsCount' => (int)$t['reviews_count'],
            'hourlyRate' => (int)$t['hourly_rate'],
            'learningStyles' => $t['learning_styles'],
            'bio' => mb_substr($t['bio'] ?? '', 0, 300)
        ];
    }, $eligibleTutors);

    $prompt = "You are an AI Tutor Matching engine for TutorLink. A student needs help with Subject: '$subject', Grade Level: '$grade', Learning Style: '$learningStyle', Preferred Time: '$preferredTime'.\n";
    $prompt .= "Here is the candidate tutors list: " . json_encode($safeTutors) . ".\n";
    $prompt .= "Evaluate and rank all tutors for fit. Output strictly a JSON array of objects with keys: 'id' (string), 'score' (integer 0-100), 'reason' (one sentence string explaining match reason). Output valid JSON only, no markdown formatting.";

    $ch = curl_init($aiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $aiKey
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
        'model' => $aiModel,
        'messages' => [['role' => 'user', 'content' => $prompt]],
        'temperature' => 0.3
    ]));
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($response && $httpCode === 200) {
        $jsonRes = json_decode($response, true);
        $content = $jsonRes['choices'][0]['message']['content'] ?? '';
        $contentClean = trim(str_replace(['```json', '```'], '', $content));
        $parsedAI = json_decode($contentClean, true);

        if (is_array($parsedAI) && count($parsedAI) > 0) {
            $useAI = true;
            $aiResults = $parsedAI;
        }
    }
}

$rankedMatches = [];

if ($useAI && is_array($aiResults)) {
    $aiMap = [];
    foreach ($aiResults as $ar) {
        if (isset($ar['id'])) {
            $aiMap[$ar['id']] = $ar;
        }
    }

    foreach ($eligibleTutors as $t) {
        $ar = $aiMap[$t['id']] ?? null;
        $score = $ar && isset($ar['score']) ? max(0, min(100, intval($ar['score']))) : 80;
        $reason = $ar && !empty($ar['reason']) ? trim($ar['reason']) : "AI recommended tutor based on $subject compatibility.";

        $tData = toCamelArray($t);
        $tData['subjects'] = array_map('trim', explode(',', $t['subjects']));
        $tData['learningStyles'] = array_map('trim', explode(',', $t['learning_styles']));
        $tData['score'] = $score;
        $tData['matchReason'] = $reason;

        $rankedMatches[] = $tData;
    }

    usort($rankedMatches, function($a, $b) { return $b['score'] - $a['score']; });
    $matchMode = 'AI match';
} else {
    // Fallback: Server-side Rule-based match
    foreach ($eligibleTutors as $t) {
        $score = 50; // Subject match base

        $lStyles = array_map('strtolower', array_map('trim', explode(',', $t['learning_styles'])));
        if (in_array(strtolower($learningStyle), $lStyles, true)) {
            $score += 20;
        }

        $availSlots = array_map('strtolower', array_map('trim', explode(',', $t['availability_slots'] ?? '')));
        if (in_array(strtolower($preferredTime), $availSlots, true)) {
            $score += 15;
        }

        // Tutor with 0 reviews is not penalized: use neutral rating 3.5
        $effectiveRating = (intval($t['reviews_count']) > 0) ? floatval($t['rating']) : 3.5;
        $score += (int)round(($effectiveRating / 5.0) * 15);
        $score = min(100, max(0, $score));

        $tData = toCamelArray($t);
        $tData['subjects'] = array_map('trim', explode(',', $t['subjects']));
        $tData['learningStyles'] = array_map('trim', explode(',', $t['learning_styles']));
        $tData['score'] = $score;
        $tData['matchReason'] = "Rule-based compatibility match for $subject.";

        $rankedMatches[] = $tData;
    }

    usort($rankedMatches, function($a, $b) { return $b['score'] - $a['score']; });
    $matchMode = 'Rule-based match';
}

$responsePayload = [
    'status' => 'success',
    'match_mode' => $matchMode,
    'subject' => $subject,
    'data' => $rankedMatches
];

// Store in 10-min cache
$_SESSION[$cacheKey] = [
    'timestamp' => time(),
    'response' => $responsePayload
];

echo json_encode($responsePayload);
