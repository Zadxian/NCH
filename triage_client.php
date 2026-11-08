<?php
/**
 * Calls the Python triage service. Adapt this into a LavaLust library or model
 * (follow the LavaLust docs for where to place it and how to load it).
 * Send only clinical answers. Never send a name or contact number.
 */
function triage_suggest(array $answers): array
{
    $ch = curl_init('http://127.0.0.1:5000/predict');
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'X-API-Key: ' . (getenv('TRIAGE_API_KEY') ?: ''),
        ],
        CURLOPT_POSTFIELDS     => json_encode($answers),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 5,
    ]);
    $body   = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // If the service is down or fails, route the patient to staff instead of guessing.
    if ($body === false || $status !== 200) {
        return ['suggestion' => 'needs_staff_review', 'error' => true];
    }
    return json_decode($body, true) ?: ['suggestion' => 'needs_staff_review', 'error' => true];
}

// Example use inside a controller:
// $result = triage_suggest([
//     'age_group' => 'child', 'fever' => 1, 'cough' => 0, 'sore_throat' => 0,
//     'headache' => 0, 'vomiting' => 1, 'diarrhea' => 1, 'abdominal_pain' => 0,
//     'dizziness' => 0, 'rash' => 0, 'pain_level' => 4, 'duration_days' => 5,
//     'chronic_condition' => 0, 'pregnant' => 0,
// ]);
// $result['suggestion'] is: urgent, non_urgent, needs_staff_review, or red_flag
