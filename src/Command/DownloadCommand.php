<?php

namespace App\Command;

use App\Controller\DownloadController;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class DownloadCommand extends AbstractSettingsCommand
{
    protected static $defaultName = 'dof:download';

    /** @var DownloadController */
    protected $downloadController;

    /**
     * @param string|null $name The name of the command; passing null means it must be set in configure()
     */
    public function __construct(string $name = null, DownloadController $downloadController, SessionInterface $session)
    {
        $this->downloadController = $downloadController;
        parent::__construct($name, $session);
    }

    protected function configure()
    {
        $this
            ->setDescription('Download DOF ini files')
            ->addArgument('type', InputArgument::OPTIONAL, 'both: database and your individual ini files (default); db: just the database; ini: your individual ini files')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $type = $input->getArgument('type');
        if ('ini' === $type) {
            $names = ['download'];
        } elseif ('db' === $type) {
            $names = ['database'];
        } else {
            $names = ['download', 'database'];
        }

        $this->downloadController->doDownload($names);
        $this->printFlashes($output);

        return 0;
    }
}
