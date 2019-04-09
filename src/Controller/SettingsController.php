<?php

namespace App\Controller;

use App\Entity\Settings;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class SettingsController extends AbstractController
{
    /**
     * @Route("/settings", name="settings")
     */
    public function index(Request $request)
    {
        $settings = new Settings();
        $settings->load();

        $form = $this->createFormBuilder($settings)
            ->add('lcpApiKey', TextType::class, ['label' => 'LCP_APIKEY'])
            ->add('dofPath', TextType::class, ['label' => 'DOF Path'])
            ->add('visualPinballPath', TextType::class, ['label' => 'Visual Pinball Path'])
            ->add('save', SubmitType::class, ['label' => 'Save settings'])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Settings $settings */
            $settings = $form->getData();

            /** @var \Symfony\Component\Form\Form $form */
            $name = $form->getClickedButton()->getConfig()->getName();
            switch ($name) {
                case 'save':
                    try {
                        $settings->persist();
                    } catch (\Exception $e) {
                        $this->addFlash('warning', $e->getMessage());
                        break;
                    }

                    if ('save' === $name) {
                        $this->addFlash('success', 'Saved settings to '.$settings->getIni().'.');
                    }
                    break;
            }
        }

        return $this->render('settings/index.html.twig', [
            'settings_form' => $form->createView(),
        ]);
    }

    public function dofPath(ValidatorInterface $validator)
    {
        $author = new Author();

        // ... do something to the $author object

        $errors = $validator->validate($author);

        if (count($errors) > 0) {
            /*
             * Uses a __toString method on the $errors variable which is a
             * ConstraintViolationList object. This gives us a nice string
             * for debugging.
             */
            $errorsString = (string) $errors;

            return new Response($errorsString);
        }

        return new Response('The author is valid! Yes!');
    }
}
