<?php

$download = parse_ini_file(__DIR__ . '/download.ini', TRUE);
$config_path = trim($download['download']['DOF_CONFIG_PATH'], DIRECTORY_SEPARATOR);

$tweaks = parse_ini_file(__DIR__ . '/tweaks.ini', TRUE);

$mods = [];
$mods_applied = [];
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
                        $mods_applied[$file][$game[0]][] = '"default_effect_duration[' . $port . '] = ' . $setting . '": ' . $game[$port] . ' => ' . $new;
                        $game[$port] = $new;
                      }
                      break;

                    case 'turn_off':
                      $game_names = explode(',', $setting);
                      array_walk($game_names, 'trim');
                      if (in_array($game[0], $game_names) && 0 != $game[$port]) {
                        $mods_applied[$file][$game[0]][] = '"turn_off[' . $port . '] = ' . $setting . '": ' . $game[$port] . ' => ' . 0;
                        $game[$port] = 0;
                      }
                      break;

                    case 'turn_on':
                      $game_names = explode(',', $setting);
                      array_walk($game_names, 'trim');
                      if (!in_array($game[0], $game_names) && 0 != $game[$port]) {
                        $mods_applied[$file][$game[0]][] = '"turn_on[' . $port . '] = ' . $setting . '": ' . $game[$port] . ' => ' . 0;
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
                        $mods_applied[$file][$game[0]][] = '"adjust_intensity[' . $port . '] = ' . $setting . '": ' . $game[$port] . ' => ' . $new;
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

    print 'List of pending modifications for ' . $file . "\r\n";
    $changes = FALSE;
    foreach ($mods_applied[$file] as $name => $mods) {
      if ($mods) {
        print "\t" . $name . "\r\n";
        foreach ($mods as $mod) {
          print "\t\t" . $mod . "\r\n";
        }
        $changes = TRUE;
      }
    }

    if (!$changes) {
      print "\t No changes.\r\n";
    }
    else {
      $line = readline('Write changes to ' . $file . ' [yes|no] (yes)? ');
      if ('yes' == strtolower($line) || empty($line)) {
        file_put_contents($file, $head . '[Config DOF]' . implode("\r\n", $games));
      }
      else {
        print "Modifications skipped.\r\n\r\n";
      }
    }
  }
}
