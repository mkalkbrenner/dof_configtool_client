<?php
require 'include/header.php';

if (file_exists(__DIR__ . '/../config/download.ini')) {
  $download = parse_ini_file(__DIR__ . '/../config/download.ini', TRUE);
}

$download = ['download' => [
  'LCP_APIKEY' => !empty($_POST['apikey']) ? $_POST['apikey'] : $download['download']['LCP_APIKEY'],
  'DOF_CONFIG_PATH' => trim(!empty($_POST['dof-config-directory']) ? $_POST['dof-config-directory'] : $download['download']['DOF_CONFIG_PATH'], DIRECTORY_SEPARATOR),
]];

if (!empty($_POST['apikey'])) {
  file_put_contents(__DIR__ . '/../config/download.ini', "[download]\r\nLCP_APIKEY = " . $download['download']['LCP_APIKEY'] . "\r\nDOF_CONFIG_PATH = " . $download['download']['DOF_CONFIG_PATH'] . "\r\n");
}

$tweaks = '';
if (!empty($_POST['tweaks'])) {
  $tweaks = $_POST['tweaks'];
  file_put_contents(__DIR__ . '/../config/tweaks.ini', $tweaks);
}
elseif (file_exists(__DIR__ . '/../config/tweaks.ini')) {
  $tweaks = file_get_contents(__DIR__ . '/../config/tweaks.ini');
}
elseif (file_exists(__DIR__ . '/../config/tweaks.ini.example')) {
  $tweaks = file_get_contents(__DIR__ . '/../config/tweaks.ini.example');
}

?>
    <form action="/pages/settings.php" method="post">
        <h4>Download your DOF config files and save them to </h4>

        <div>
            <label for="apikey">LCP_APIKEY for http://configtool.vpuniverse.com</label>
            <input name="apikey" type="text" class="form-control" id="apikey" placeholder="Your_API_KeY" value="<?php print $download['download']['LCP_APIKEY']; ?>">
            <div class="invalid-feedback">
                You need to register or http://configtool.vpuniverse.com to obtain your API key.
            </div>
        </div>

        <div>
            <label for="dof-config-directory">DOF Config Directory</label>
            <input name="dof-config-directory" type="text" class="form-control" id="dof-config-directory" placeholder="In most cases C:\DirectOutput\config"  value="<?php print $download['download']['DOF_CONFIG_PATH']; ?>" required>
        </div>

        <hr>

        <h4>Tweaks</h4>

        <div>
            <label for="tweaks">tweaks.ini</label>
            <textarea name="tweaks" class="form-control" id="tweaks" rows="5"><?php print $tweaks; ?></textarea>
        </div>

        <hr>
        <button class="btn btn-primary btn-lg btn-block" type="submit">Save</button>
    </form>
<?php
require 'include/footer.php';