<?php

namespace App\Component;

class Filesystem extends \Symfony\Component\Filesystem\Filesystem
{
    public function getTempDir(): string
    {
        $tmp = sys_get_temp_dir();
        if (isset($_SERVER['PROGRAM_DATA'])) {
            $tmp = $_SERVER['PROGRAM_DATA'].DIRECTORY_SEPARATOR.'tmp';
            if (!$this->exists($tmp)) {
                $this->mkdir($tmp);
            }
        }
        return $tmp;
    }

    public function tempdir($dir, $prefix): string
    {
        $tmp = $this->tempnam($dir, $prefix);
        $this->remove($tmp);
        $this->mkdir($tmp);
        return $tmp;
    }

}