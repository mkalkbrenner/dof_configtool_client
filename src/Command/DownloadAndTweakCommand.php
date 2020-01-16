<?php

namespace App\Command;

use App\Controller\DownloadController;
use App\Controller\TweakController;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class DownloadAndTweakCommand extends AbstractSettingsCommand
{
    protected static $defaultName = 'dof:download-and-tweak';

    /** @var DownloadController */
    protected $downloadController;

    /** @var TweakController */
    protected $tweakController;

    /**
     * @param string|null $name The name of the command; passing null means it must be set in configure()
     */
    public function __construct(string $name = null, DownloadController $downloadController, TweakController $tweakController, SessionInterface $session)
    {
        $this->downloadController = $downloadController;
        $this->tweakController = $tweakController;
        parent::__construct($name, $session);
    }

    protected function configure()
    {
        $this->setDescription('Download and tweak DOF ini files');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->downloadController->doDownload(['download']);

        $this->printFlashes($output);

        if ($this->tweakController->getTweaks()->getDaySettings()) {
            list(, $modded_files) = $this->tweakController->modFiles('day');
            $this->tweakController->persistFiles('day', $modded_files);
        }

        $this->printFlashes($output);

        if ($this->tweakController->getTweaks()->getNightSettings()) {
            list(, $modded_files) = $this->tweakController->modFiles('night');
            $this->tweakController->persistFiles('night', $modded_files);
        }

        $this->printFlashes($output);

        return 0;
    }
}
