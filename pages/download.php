<?php
require 'include/header.php';

if (file_exists(__DIR__ . '/../config/download.ini')) {
  $download = parse_ini_file(__DIR__ . '/../config/download.ini', TRUE);
}

if (empty($download['download']) || empty($download['download']['LCP_APIKEY']) || empty($download['download']['DOF_CONFIG_PATH'])) {
  print "Incomplete settings detected.";
  exit;
}

if (!empty($_POST['action']) && 'download' == $_POST['action']) {
  ini_set('set_time_limit', 0);
  $config_path = trim($download['download']['DOF_CONFIG_PATH'], DIRECTORY_SEPARATOR);

  $zip_file = tempnam(sys_get_temp_dir(), 'dof_config');
  if (copy('http://configtool.vpuniverse.com/api.php?query=getconfig&apikey=' . $download['download']['LCP_APIKEY'], $zip_file)) {
    $zip = new \ZipArchive();
    if ($zip->open($zip_file)) {
      $zip->extractTo($config_path);
    }
  }
}

?>
    <form class="needs-validation" action="/pages/download.php" method="post">
      <h4>Download your configuration files.</h4>
      <input type="hidden" name="action" value="download">
      <hr>
      <button class="btn btn-primary btn-lg btn-block" type="submit">Download</button>
    </form>
<?php
require 'include/footer.php';
