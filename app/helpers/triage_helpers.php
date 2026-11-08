<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

/**
 * Helper: triage_helpers_helper.php
 * 
 * Automatically generated via CLI.
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
