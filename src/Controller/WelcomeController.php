<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class WelcomeController extends AbstractController
{
    /**
     * @Route("/welcome", name="welcome")
     */
    public function index()
    {
        $parsedown = new \Parsedown();

        return $this->render('welcome/index.html.twig', [
            'readme' => $parsedown->parse(file_get_contents(__DIR__. '/../../README.md')),
        ]);
    }
}
