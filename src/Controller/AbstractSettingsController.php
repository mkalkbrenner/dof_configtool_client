<?php

namespace App\Controller;

use App\Component\Filesystem;
use App\Entity\Settings;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

abstract class AbstractSettingsController extends AbstractController
{
    /**
     * @var Settings
     */
    protected $settings;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    public function __construct()
    {
        $this->settings = new Settings();
        $this->settings->load();

        $this->filesystem = new Filesystem();
    }
}
