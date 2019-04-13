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

        $parsedown = new \Parsedown();

        return $this->render('welcome/index.html.twig', [
            'readme' => $parsedown->parse(file_get_contents(__DIR__. '/../../README.md')),
        ]);
    }
}
