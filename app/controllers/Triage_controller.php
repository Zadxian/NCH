<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

/**
 * Controller: Triage_controller
 * 
 * Automatically generated via CLI.
 */
class Triage_controller extends Controller {
    public function __construct()
    {
        parent::__construct();
        $this->call->helper('triage_helpers');
    }

    public function index()
    {
          $data = [];
        if ($this->io->method() === 'post') {
            $yesNo = ['fever', 'cough', 'sore_throat', 'headache', 'vomiting', 'diarrhea',
                      'abdominal_pain', 'dizziness', 'rash', 'chronic_condition', 'pregnant',
                      'other_symptoms', 'difficulty_breathing', 'chest_pain',
                      'severe_bleeding', 'fainting'];
           $answers = [
    'age_group'     => $_POST['age_group'] ?? '',
    'pain_level'    => (int) ($_POST['pain_level'] ?? 0),
    'duration_days' => (int) ($_POST['duration_days'] ?? 0),
];
foreach ($yesNo as $field) {
    $answers[$field] = isset($_POST[$field]) ? 1 : 0;
}
            foreach ($yesNo as $field) {
                $answers[$field] = $this->io->post($field) ? 1 : 0;   // unchecked boxes are not sent
            }
            $data['result'] = triage_suggest($answers);               // asks the Python service
        }
        $this->call->view('triage_view', $data);
    }
}