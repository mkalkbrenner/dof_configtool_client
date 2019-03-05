<?php

$download = parse_ini_file(__DIR__ . '/download.ini', TRUE);
$config_path = trim($download['download']['DOF_CONFIG_PATH'], DIRECTORY_SEPARATOR);

$tweaks = parse_ini_file(__DIR__ . '/tweaks.ini', TRUE);

$modifications = [];

foreach ($tweaks as $config_file => $adjustments) {
  $file = $config_path . DIRECTORY_SEPARATOR . $config_file;
  $modifications[$file] = [];
  if ($contents = file_get_contents($file)) {
    list($head, $config) = explode('[Config DOF]', $contents);
    $games = [];
    foreach (explode("\n", $config) as $game_row) {
      $game = str_getcsv($game_row);
      $modifications[$file][$game[0]] = [];
      foreach ($adjustments as $name => $settings) {
        switch ($name) {

          case 'default_effect_duration':
            foreach ($settings as $port => $duration) {
              if (isset($game[$port])) {
                $triggers = explode('/', $game[$port]);
                foreach ($triggers as &$trigger) {
                  $trigger = preg_replace('/([SWE]\d+$)/', '$1 ' . $duration, $trigger);
                }
                $new = implode('/', $triggers);
                if ($new != $game[$port]) {
                  $modifications[$file][$game[0]][] = '"default_effect_duration[' . $port . '] = ' . $duration . '": '. $game[$port] . ' => ' . $new;
                  $game[$port] = $new;
                }
              }
            }
            break;

          case 'turn_off':
            foreach ($settings as $port => $game_names) {
              if (!empty($game[$port])) {
                $game_names_array = explode(',', $game_names);
                array_walk($game_names_array, 'trim');
                if (in_array($game[0], $game_names_array)) {
                  $modifications[$file][$game[0]][] = '"turn_off[' . $port . '] = ' . $game_names . '": '. $game[$port] . ' => ' . 0;
                  $game[$port] = 0;
                }
              }
            }
            break;

            case 'turn_on':
            foreach ($settings as $port => $game_names) {
              if (!empty($game[$port])) {
                $game_names_array = explode(',', $game_names);
                array_walk($game_names_array, 'trim');
                if (!in_array($game[0], $game_names_array)) {
                  $modifications[$file][$game[0]][] = '"turn_on[' . $port . '] = ' . $game_names . '": '. $game[$port] . ' => ' . 0;
                  $game[$port] = 0;
                }
              }
            }
            break;
        }
      }
      $games[] = implode(',', $game);
    }
    file_put_contents($file, $head . '[Config DOF]' . implode("\n", $games));
  }
}

foreach ($modifications as $file => $game) {
  print $file . "\n";
  $changes = FALSE;
  foreach ($game as $name => $mods) {
    if ($mods) {
      print "\t" . $name . "\n";
      foreach ($mods as $mod) {
        print "\t\t" . $mod . "\n";
      }
      $changes = TRUE;
    }
  }
  if (!$changes) {
    print "\t No changes.\n";
  }
}
