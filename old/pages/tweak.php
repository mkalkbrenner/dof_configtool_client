<?php
require 'include/header.php';

if (file_exists(__DIR__ . '/../config/download.ini')) {
  $download = parse_ini_file(__DIR__ . '/../config/download.ini', TRUE);
}

if (empty($download['download']) || empty($download['download']['DOF_CONFIG_PATH'])) {
  print "Incomplete settings detected.";
  exit;
}

if (!empty($_POST['action']) && 'save' == $_POST['action']) {
  ini_set('set_time_limit', 0);
  $config_path = trim($download['download']['DOF_CONFIG_PATH'], DIRECTORY_SEPARATOR);

  foreach ($_POST as $file => $content) {
    $file = base64_decode($file);
    if (file_exists($file)) {
      file_put_contents($file, base64_decode($content));
    }
  }
}
?>
    <form class="needs-validation" action="/pages/tweak.2.php" method="post">
      <h4>Tweak your configuration files.</h4>
      <hr>
      <input type="hidden" name="action" value="tweak">
      <button class="btn btn-primary btn-lg btn-block" type="submit">Tweak</button>
    </form>
<?php
require 'include/footer.php';
