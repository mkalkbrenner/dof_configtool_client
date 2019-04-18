<?php

namespace App\Controller;

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

        return $this->render('welcome/index.html.twig', [
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
