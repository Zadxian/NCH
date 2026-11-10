<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

/**
 * Controller: app/controllers/Chatbot.php
 * index() shows the chat page. send() receives each chat message from the page's JavaScript,
 * forwards it to the local Python service, and returns the reply as JSON.
 * Compare the first lines with an existing controller (e.g. Welcome.php) in your project.
 */
class Triage_controller extends Controller
{
    private $service = 'http://127.0.0.1:5000/chat';

    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        $this->call->view('chatbot_view');
    }

    public function send()
    {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'method not allowed']);
            return;
        }
        $in = json_decode(file_get_contents('php://input'), true);
        if (!is_array($in)) {
            http_response_code(400);
            echo json_encode(['error' => 'bad request']);
            return;
        }
        $state = (isset($in['state']) && is_array($in['state'])) ? $in['state'] : [];
        $payload = [
            'state'   => (object) $state,                                  // {} when empty
            'message' => substr((string) ($in['message'] ?? ''), 0, 500),
        ];

        $ch = curl_init($this->service);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'X-API-Key: ' . (getenv('TRIAGE_API_KEY') ?: ''),
            ],
            CURLOPT_POSTFIELDS     => json_encode($payload),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 5,
        ]);
        $body   = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // If the Python service is down, send the patient to staff instead of guessing.
        if ($body === false || $status !== 200 || !is_array(json_decode($body, true))) {
            echo json_encode([
                'done'       => true,
                'suggestion' => 'needs_staff_review',
                'reply'      => 'The assistant is not available right now. Please see a nurse.',
            ]);
            return;
        }
        echo $body;
    }
}