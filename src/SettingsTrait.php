<?php

namespace App;

use App\Component\Filesystem;
use App\Entity\Settings;
use GitWrapper\GitException;
use GitWrapper\GitWorkingCopy;
use GitWrapper\GitWrapper;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

trait SettingsTrait
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

    protected function loadSettings()
    {
        $this->settings = new Settings();
        $this->settings->load();

        $this->filesystem = new Filesystem();
        $this->cache = new FilesystemAdapter();
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
