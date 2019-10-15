<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class BinaryController extends AbstractController
{
    /**
     * @Route("/binary/{file}", name="binary")
     */
    public function index(Request $request, string $file)
    {
        return new BinaryFileResponse(base64_decode($file));
    }
}
