<?php
require 'include/header.php';

if (file_exists(__DIR__ . '/../config/download.ini')) {
  $download = parse_ini_file(__DIR__ . '/../config/download.ini', TRUE);
}

if (empty($download['download']) || empty($download['download']['DOF_CONFIG_PATH'])) {
  print "Incomplete settings detected.";
  exit;
}

$modded_files = [];
$mods_applied = [];

if (!empty($_POST['action']) && 'tweak' == $_POST['action']) {
  ini_set('set_time_limit', 0);
  $config_path = trim($download['download']['DOF_CONFIG_PATH'], DIRECTORY_SEPARATOR);

  $tweaks = parse_ini_file(__DIR__ . '/../config/tweaks.ini', TRUE);

  $mods = [];
  $file = '';

  foreach ($tweaks as $section => $adjustments) {
    if (strpos($section, '.ini')) {
      $file = $config_path . DIRECTORY_SEPARATOR . $section;
      if (file_exists($file)) {
        $mods[$file] = [0 => $adjustments];
        $mods_applied[$file] = [];
      }
      else {
        $file = '';
      }
    }
    elseif ($file) {
      $mods[$file][$section] = $adjustments;
    }
  }

  foreach ($mods as $file => $per_game_mods) {
    if ($contents = file_get_contents($file)) {
      list($head, $config) = explode('[Config DOF]', $contents);
      foreach (explode("\r\n", $config) as $game_row) {
        if ($game_row = trim($game_row)) {
          $game = str_getcsv($game_row);
          $mods_applied[$file][$game[0]] = [];
          foreach ($per_game_mods as $game_name => $adjustments) {
            foreach ($adjustments as $name => $settings) {
              foreach ($settings as $port => $setting) {
                // Skip global setting when a game-specific setting exists.
                if ($game_name === $game[0] || (!$game_name && (!isset($per_game_mods[$game[0]]) || !isset($per_game_mods[$game[0]][$name]) || !isset($per_game_mods[$game[0]][$name][$port])))) {
                  if (isset($game[$port])) {

                    switch ($name) {

                      case 'default_effect_duration':
                        $triggers = explode('/', $game[$port]);
                        foreach ($triggers as &$trigger) {
                          $trigger = preg_replace('/([SWE]\d+$)/', '$1 ' . $setting, $trigger);
                        }
                        $new = implode('/', $triggers);
                        if ($new != $game[$port]) {
                          $mods_applied[$file][$game[0]][] = '"default_effect_duration[' . $port . '] = ' . $setting . '": ' . "\r\n" . $game[$port] . ' => ' . $new;
                          $game[$port] = $new;
                        }
                        break;

                      case 'turn_off':
                        $game_names = explode(',', $setting);
                        array_walk($game_names, 'trim');
                        if (in_array($game[0], $game_names) && 0 != $game[$port]) {
                          $mods_applied[$file][$game[0]][] = '"turn_off[' . $port . '] = ' . $setting . '": ' . "\r\n" . $game[$port] . ' => ' . 0;
                          $game[$port] = 0;
                        }
                        break;

                      case 'turn_on':
                        $game_names = explode(',', $setting);
                        array_walk($game_names, 'trim');
                        if (!in_array($game[0], $game_names) && 0 != $game[$port]) {
                          $mods_applied[$file][$game[0]][] = '"turn_on[' . $port . '] = ' . $setting . '": ' . "\r\n" . $game[$port] . ' => ' . 0;
                          $game[$port] = 0;
                        }
                        break;

                      case 'adjust_intensity':
                        $triggers = explode('/', $game[$port]);
                        foreach ($triggers as &$trigger) {
                          if (preg_match('/[I](\d+)/', $trigger, $matches)) {
                            $intensity = (int) (((int) $matches[1]) * ((float) $setting));
                            if ($intensity < 1) {
                              $intensity = 1;
                            }
                            if ($intensity > 48) {
                              $intensity = 48;
                            }
                            $trigger = preg_replace('/[I]\d+/', 'I' . $intensity, $trigger);
                          }
                        }
                        $new = implode('/', $triggers);
                        if ($new != $game[$port]) {
                          $mods_applied[$file][$game[0]][] = '"adjust_intensity[' . $port . '] = ' . $setting . '":' . "\r\n" . $game[$port] . ' => ' . $new;
                          $game[$port] = $new;
                        }
                        break;
                    }

                  }
                }
              }
            }
          }
          $games[] = implode(',', $game);
        }
        else {
          $games[] = '';
        }
      }
      $modded_files[$file] = $head . '[Config DOF]' . implode("\r\n", $games);
    }
  }
}

?>
    <form class="needs-validation" action="/pages/tweak.php" method="post">
      <h4>Applied Tweaks:</h4>
      <?php foreach ($modded_files as $file => $content) {
      ?>
      <hr>
      <h4><?php print basename($file); ?></h4>
      <?php
        foreach ($mods_applied[$file] as $name => $mods) {
          if ($mods) {
         ?>
         <h5><?php print $name; ?></h5>
         <?php foreach ($mods as $mod) { ?>
          <textarea class="form-control" disabled><?php print $mod; ?></textarea><br>
         <?php
          }?>
           <?php
         }
        }
        ?>
      <input type="hidden" name="<?php print base64_encode($file); ?>" value="<?php print base64_encode($content); ?>">
      <?php
      }
      ?>
      <hr>
      <input type="hidden" name="action" value="save">
      <button class="btn btn-primary btn-lg btn-block" type="submit">Save</button>
    </form>
<?php
require 'include/footer.php';
