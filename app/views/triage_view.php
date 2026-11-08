<?php /* View: app/views/triage.php. Receives optional $result (array with 'suggestion') from the controller. */
$messages = [
    'red_flag'           => ['Please go to the staff now.', 'You reported a warning sign. Staff will attend to you right away.'],
    'urgent'             => ['Suggested level: Urgent', 'A nurse or doctor will confirm this and call you as soon as possible.'],
    'non_urgent'         => ['Suggested level: Non-urgent', 'A nurse or doctor will confirm this. Please wait for your number.'],
    'needs_staff_review' => ['Please see a nurse', 'A staff member will check your symptoms in person.'],
];
$symptoms = ['fever' => 'Fever', 'cough' => 'Cough', 'sore_throat' => 'Sore throat', 'headache' => 'Headache',
             'vomiting' => 'Vomiting', 'diarrhea' => 'Diarrhea', 'abdominal_pain' => 'Stomach pain',
             'dizziness' => 'Dizziness', 'rash' => 'Skin rash'];
$flags = ['difficulty_breathing' => 'Difficulty breathing', 'chest_pain' => 'Chest pain',
          'severe_bleeding' => 'Severe bleeding', 'fainting' => 'Fainting or passing out'];
$key = isset($result['suggestion']) ? $result['suggestion'] : null;
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Symptom check</title>
<link href="https://fonts.googleapis.com/css2?family=Atkinson+Hyperlegible:wght@400;700&amp;display=swap" rel="stylesheet">
<style>
  :root { --ink:#12302B; --accent:#0F766E; --line:#C9DAD6; --bg:#F3F7F6; }
  body { margin:0; background:var(--bg); color:var(--ink); font-family:'Atkinson Hyperlegible',sans-serif; }
  main { max-width:560px; margin:0 auto; padding:20px; display:flex; flex-direction:column; gap:16px; }
  h1 { font-size:26px; margin:0; }
  fieldset, .card { background:#fff; border:1px solid var(--line); border-radius:12px; padding:16px; margin:0; }
  legend { font-weight:700; padding:0 6px; }
  label.opt { display:flex; align-items:center; gap:10px; min-height:44px; font-size:17px; }
  input[type=checkbox], input[type=radio] { width:22px; height:22px; accent-color:var(--accent); }
  input[type=number] { min-height:44px; width:90px; font-size:17px; border:1px solid #7A9A94; border-radius:8px; padding:0 10px; }
  .notice { background:#FFF4E5; color:#6B3A00; border-radius:10px; padding:12px; font-size:14px; }
  button { min-height:52px; border:0; border-radius:10px; background:var(--accent); color:#fff; font:700 17px inherit; font-family:inherit; }
  .result { border-left:6px solid var(--accent); }
  .result.red_flag { border-left-color:#B42318; }
  .result h2 { margin:0 0 6px; font-size:22px; }
</style>
</head>
<body>
<main>
  <h1>Symptom check</h1>
  <div class="notice">This gives a suggestion only. Staff confirm every result. Do not type your name or contact number here. For severe symptoms, tell the staff right away.</div>

  <?php if ($key && isset($messages[$key])): ?>
  <div class="card result <?= htmlspecialchars($key) ?>" role="status">
    <h2><?= htmlspecialchars($messages[$key][0]) ?></h2>
    <div><?= htmlspecialchars($messages[$key][1]) ?></div>
  </div>
  <?php endif; ?>

  <form method="post" action="">
    <fieldset>
      <legend>Do you have any of these right now?</legend>
      <?php foreach ($flags as $name => $label): ?>
        <label class="opt"><input type="checkbox" name="<?= $name ?>" value="1"> <?= $label ?></label>
      <?php endforeach; ?>
    </fieldset>

    <fieldset>
      <legend>Age group</legend>
      <label class="opt"><input type="radio" name="age_group" value="child" required> Child</label>
      <label class="opt"><input type="radio" name="age_group" value="adult"> Adult</label>
      <label class="opt"><input type="radio" name="age_group" value="elderly"> Elderly</label>
    </fieldset>

    <fieldset>
      <legend>Symptoms</legend>
      <?php foreach ($symptoms as $name => $label): ?>
        <label class="opt"><input type="checkbox" name="<?= $name ?>" value="1"> <?= $label ?></label>
      <?php endforeach; ?>
      <label class="opt"><input type="checkbox" name="other_symptoms" value="1"> Other symptoms not listed</label>
    </fieldset>

    <fieldset>
      <legend>Details</legend>
      <label class="opt">Pain level (0 to 10) <input type="number" name="pain_level" min="0" max="10" value="0" required></label>
      <label class="opt">Days with symptoms <input type="number" name="duration_days" min="0" max="365" value="1" required></label>
      <label class="opt"><input type="checkbox" name="chronic_condition" value="1"> I have a long-term health condition</label>
      <label class="opt"><input type="checkbox" name="pregnant" value="1"> I am pregnant</label>
    </fieldset>

    <button type="submit">Get suggestion</button>
  </form>
</main>
</body>
</html>