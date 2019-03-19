<?php

namespace App\Controller;

use App\Entity\VPinMameRegEntries;
use App\Form\Type\VPinMameRegEntriesType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class RegEditController extends AbstractController
{
    /**
     * @Route("/regedit", name="reg_edit")
     */
    public function index(Request $request)
    {
        if (!extension_loaded('com_dotnet')) {
            $this->addFlash('warning', 'RegEdit requires the com_dotnet extension. Switching to demo mode.');
        }

        $regEntries = new VPinMameRegEntries();
        $regEntries->load();

        $form = $this->createForm(VPinMameRegEntriesType::class, $regEntries)
            ->add('save', SubmitType::class, ['label' => 'Save']);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            foreach ($regEntries as $regEntry) {
                $regEntry->persist();
            }
        }

        return $this->render('reg_edit/index.html.twig', [
            'reg_edit_form' => $form->createView(),
        ]);
    }
}
