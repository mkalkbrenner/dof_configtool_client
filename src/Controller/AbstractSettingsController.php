<?php

namespace App\Controller;

use App\Component\Filesystem;
use App\Entity\Settings;
use GitWrapper\GitException;
use GitWrapper\GitWorkingCopy;
use GitWrapper\GitWrapper;
use Psr\Container\ContainerInterface;
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

    /**
     * @return Settings
     */
    public function getSettings(): Settings
    {
        return $this->settings;
    }

    /**
     * @return Filesystem
     */
    public function getFilesystem(): Filesystem
    {
        return $this->filesystem;
    }

    /**
     * @return FilesystemAdapter
     */
    public function getCache(): FilesystemAdapter
    {
        return $this->cache;
    }

    protected function getGitWorkingCopy(string $path): GitWorkingCopy
    {
        $gitWrapper = new GitWrapper($this->settings->getGitBinary());

        $workingCopy = $gitWrapper->workingCopy($path);
        if (!$workingCopy->isCloned()) {
            $workingCopy->init();
            $workingCopy->config('user.email', $this->settings->getGitEmail());
            $workingCopy->config('user.name', $this->settings->getGitUser());
            try {
                $workingCopy->add('*.ini');
                $workingCopy->add('*.xml');
                $workingCopy->add('*.txt');
            } catch (GitException $e) {
                // nop
            }
            $workingCopy->commit('Initial import of existing files.');
            $workingCopy->setCloned(true);
        }

        return $workingCopy;
    }

    protected function getCurrentBranch(GitWorkingCopy $workingCopy): string
    {
        return trim($workingCopy->run('symbolic-ref', ['--short', 'HEAD']));
    }
}
