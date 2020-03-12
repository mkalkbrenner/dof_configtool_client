<?php

namespace App\Controller;

use GitWrapper\GitException;
use Symfony\Component\Routing\Annotation\Route;

class WelcomeController extends AbstractSettingsController
{
    /**
     * @Route("/welcome", name="welcome")
     */
    public function index()
    {
        if (isset($_SERVER['PROGRAM_DATA'])) {
            $this->filesystem->remove($this->filesystem->getTempDir());
        }

        $branch = 'download';
        $log = 'unknown';
        if ($this->settings->isVersionControl()) {
            try {
                $workingCopy = $this->getGitWorkingCopy($this->settings->getDofConfigPath());
                $log = $workingCopy->log('--pretty=format:"%s | %ad"', '--date=rfc2822', '-1');
                $branch = $this->getCurrentBranch($workingCopy);
            } catch (GitException $e) {
                $this->addFlash('warning', $e->getMessage());
            }
        }

        return $this->render('welcome/index.html.twig', [
            'remote' => $this->settings->isRemoteAccess() ? $this->settings->getIp() . ':' . $this->settings->getPort() : null,
            'branch' => $branch,
            'log' => $log,
        ]);
    }

    /**
     * @Route("/about", name="about")
     */
    public function about()
    {
        return $this->render('welcome/about.html.twig', [
            'readme' => $this->getReadme(),
        ]);
    }

    /**
     * @return mixed
     * @throws \Psr\Cache\InvalidArgumentException
     */
    protected function getReadme() {
        $readme = $this->cache->getItem('welcome.readme');
        if (!$readme->isHit()) {
            $content = file_get_contents(__DIR__ . '/../../README.md');
            $content = preg_replace('/## Installation.*## Usage/sm', '## Usage', $content);
            $parsedown = new \Parsedown();
            $readme->set($parsedown->parse($content));
        }
        return $readme->get();
    }
}
