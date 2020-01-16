<?php

namespace App\Command;

use App\SettingsTrait;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

abstract class AbstractSettingsCommand extends Command
{
    use SettingsTrait;

    /** @var SessionInterface */
    protected $session;

    /**
     * @param string|null $name The name of the command; passing null means it must be set in configure()
     */
    public function __construct(string $name = null, SessionInterface $session)
    {
        $this->loadSettings();
        $this->session = $session;
        parent::__construct($name);
    }

    protected function printFlashes(OutputInterface $output)
    {
        foreach ($this->session->getFlashBag()->all() as $flash) {
            $output->writeln($flash);
        }
    }
}
