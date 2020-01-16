<?php

namespace App\Controller;

use App\SettingsTrait;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

abstract class AbstractSettingsController extends AbstractController
{
    use SettingsTrait;

    public function __construct()
    {
        $this->loadSettings();
    }

    /**
     * @internal
     * @required
     */
    public function setContainer(ContainerInterface $container)
    {
        static $pathsChecked = false;

        $previous = parent::setContainer($container);

        if  (!$pathsChecked) {
            $path = $this->settings->getVisualPinballPath();
            if (!$path) {
                $this->addFlash('danger', 'Visual Pinball path isn\'t configured, check your settings.');
            } else {
                if (!is_dir($path)) {
                    $this->addFlash('danger', 'Visual Pinball path isn\'t a directory, check your settings.');
                } else {
                    $path = $this->settings->getDofPath();
                    if (!$path || !is_dir($path)) {
                        $this->addFlash('danger', 'DOF path isn\'t a directory, check your settings.');
                    }
                    $path = $this->settings->getTablesPath();
                    if (!$path || !is_dir($path)) {
                        $this->addFlash('danger', 'Visual Pinball table path isn\'t a directory, check your settings.');
                    }
                    // Optional
                    $path = $this->settings->getPinballYPath();
                    if ($path && !is_dir($path)) {
                        $this->addFlash('danger', 'PinballY path isn\'t a directory, check your settings.');
                    }
                    $path = $this->settings->getPinUpSystemPath();
                    if ($path && !is_dir($path)) {
                        $this->addFlash('danger', 'PinUp System path isn\'t a directory, check your settings.');
                    }
                    $binary = $this->settings->getGitBinary();
                    if ($binary && !is_file($binary) && $this->settings->isVersionControl()) {
                        $this->addFlash('danger', 'Git binary isn\'t a file, check your settings.');
                    }
                    $binary = $this->settings->getBsPatchBinary();
                    if ($binary && !is_file($binary)) {
                        $this->addFlash('danger', 'Bspatch binary isn\'t a file, check your settings.');
                    }
                }
            }

            $pathsChecked = true;
        }

        return $previous;
    }
}
