<?php

namespace App\Controller;

use App\Component\Filesystem;
use App\Entity\Settings;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

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

    /**
     * @var FilesystemAdapter
     */
    protected $cache;

    public function __construct()
    {
        $this->settings = new Settings();
        $this->settings->load();

        $this->filesystem = new Filesystem();

        $this->cache = new FilesystemAdapter();
    }
}
