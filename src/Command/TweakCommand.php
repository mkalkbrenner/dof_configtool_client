<?php

namespace App\Command;

use App\Controller\TweakController;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class TweakCommand extends AbstractSettingsCommand
{
    protected static $defaultName = 'dof:tweak';

    /** @var TweakController */
    protected $tweakController;

    /**
     * @param string|null $name The name of the command; passing null means it must be set in configure()
     */
    public function __construct(string $name = null, TweakController $tweakController, SessionInterface $session)
    {
        $this->tweakController = $tweakController;
        parent::__construct($name, $session);
    }

    protected function configure()
    {
        $this
            ->setDescription('Tweak DOF ini files')
            ->addArgument('cycle', InputArgument::OPTIONAL, 'both (default); day; night')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $cycle = $input->getArgument('cycle');
        if ('day' === $cycle) {
            $cycles = ['day'];
        } elseif ('night' === $cycle) {
            $cycles = ['night'];
        } else {
            $cycles = ['day', 'night'];
        }

        foreach ($cycles as $cycle) {
            list(, $modded_files) = $this->tweakController->modFiles($cycle);
            $this->tweakController->persistFiles($cycle, $modded_files);
            $this->printFlashes($output);
        }

        return 0;
    }
}
