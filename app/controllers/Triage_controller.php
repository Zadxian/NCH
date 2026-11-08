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
                'age_group'     => $this->io->post('age_group'),
                'pain_level'    => (int) $this->io->post('pain_level'),
                'duration_days' => (int) $this->io->post('duration_days'),
            ];
            foreach ($yesNo as $field) {
                $answers[$field] = $this->io->post($field) ? 1 : 0;   // unchecked boxes are not sent
            }
            $data['result'] = triage_suggest($answers);               // asks the Python service
        }
        $this->call->view('triage_view', $data);
    }
}