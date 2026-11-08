<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

/**
 * Controller: app/controllers/Triage.php
 * Compare with an existing controller in your project (e.g. Welcome.php) and
 * adjust anything that differs in your LavaLust version.
 */
class Triage extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->call->helper('triage_helper');   // loads app/helpers/triage_helper.php
    }

    public function index()
    {
        $data = [];
        if ($this->io->method() === 'post') {
            $yesNo = ['fever', 'cough', 'sore_throat', 'headache', 'vomiting', 'diarrhea',
                      'abdominal_pain', 'dizziness', 'rash', 'chronic_condition', 'pregnant',
                      'other_symptoms', 'difficulty_breathing', 'chest_pain',
                      'severe_bleeding', 'fainting'];
            // Unchecked boxes are not sent by the browser, so read $_POST with isset/??
            // (io->post() raises a warning when a key is missing).
            $answers = [
                'age_group'     => $_POST['age_group'] ?? '',
                'pain_level'    => (int) ($_POST['pain_level'] ?? 0),
                'duration_days' => (int) ($_POST['duration_days'] ?? 0),
            ];
            foreach ($yesNo as $field) {
                $answers[$field] = isset($_POST[$field]) ? 1 : 0;
            }
            $data['result'] = triage_suggest($answers);               // asks the Python service
        }
        $this->call->view('triage_view', $data);
    }
}