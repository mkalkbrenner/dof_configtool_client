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
            ->add('save', SubmitType::class, ['label' => 'Save'])
            ->add('default_changes', SubmitType::class, ['label' => 'Save and apply changes to default to all entries without changes']);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var \Symfony\Component\Form\Form $form */
            $name = $form->getClickedButton()->getConfig()->getName();
            switch ($name) {
                case 'default_changes':
                    if ($default = $regEntries->getEntry('default')) {
                        if ($changes = $default->getChanges()) {
                            foreach (array_keys($changes) as $property) {
                                foreach ($regEntries as $regName => $regEntry) {
                                    if ('default' !== $regName) {
                                        $specificChanges = $regEntry->getChanges();
                                        if (!$specificChanges || !isset($specificChanges[$property])) {
                                            $regEntry->{'set' . $property}($default->{'get' . $property}());
                                        }
                                    }
                                }
                            }
                        }
                    }
                    break;
            }

            foreach ($regEntries as $regEntry) {
                $regEntry->persist();
            }

            // Force reload data from registry.
            return $this->redirectToRoute('reg_edit');
        }

        return $this->render('reg_edit/index.html.twig', [
            'reg_edit_form' => $form->createView(),
            'roms' => array_keys($regEntries->getEntries()),
        ]);
    }
}
