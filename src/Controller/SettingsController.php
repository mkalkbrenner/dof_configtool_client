<?php

namespace App\Controller;

use App\Entity\Settings;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class SettingsController extends AbstractSettingsController
{
    /**
     * @Route("/settings", name="settings")
     */
    public function index(Request $request)
    {
        $formBuilder = $this->createFormBuilder($this->settings)
            ->add('lcpApiKey', TextType::class, ['label' => 'LCP_APIKEY'])
            ->add('dofPath', TextType::class, ['label' => 'DOF Path'])
            ->add('visualPinballPath', TextType::class, ['label' => 'Visual Pinball Path'])
            ->add('bsPatchBinary', TextType::class, ['label' => 'pspatch Binary'])
            ->add('versionControl', CheckboxType::class, ['label' => 'Enable Version Control via Git'])
            ->add('gitBinary', TextType::class, ['label' => 'Git Binary'])
            ->add('gitUser', TextType::class, ['label' => 'Git User'])
            ->add('gitEmail', TextType::class, ['label' => 'Git Email']);

        $dofConfigPath = $this->settings->getDofConfigPath();
        if (is_dir($dofConfigPath) && is_readable($dofConfigPath)) {
            foreach (scandir($dofConfigPath) as $file) {
               if (preg_match('/^directoutputconfig\d+\.ini$/i', $file)) {

               }
            }
        }

        $form = $formBuilder
            ->add('save', SubmitType::class, ['label' => 'Save settings'])
            ->add('autodetect', SubmitType::class, ['label' => 'Autodetect port assignments'])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Settings $settings */
            $this->settings = $form->getData();

            /** @var \Symfony\Component\Form\Form $form */
            $name = $form->getClickedButton()->getConfig()->getName();
            switch ($name) {
                case 'save':
                    try {
                        $this->settings->persist();
                    } catch (\Exception $e) {
                        $this->addFlash('warning', $e->getMessage());
                        break;
                    }

                    if ('save' === $name) {
                        $this->addFlash('success', 'Saved settings to '.$this->settings->getIni().'.');
                    }
                    break;
            }
        }

        return $this->render('settings/index.html.twig', [
            'settings_form' => $form->createView(),
        ]);
    }
}
