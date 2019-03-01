<?php

$download = parse_ini_file(__DIR__ . '/download.ini', TRUE);
$config_path = trim($download['general']['DOF_CONFIG_PATH'], DIRECTORY_SEPARATOR);

$zip_file = tempnam(sys_get_temp_dir(), 'dof_config');
if (copy('http://configtool.vpuniverse.com/api.php?query=getconfig&apikey=' . $download['general']['LCP_APIKEY'], $zip_file)) {
  $zip = new \ZipArchive();
  if ($zip->open($zip_file)) {
    $zip->extractTo($config_path);
  }
}
