<?php

namespace App\Controller;

use App\Component\Utility;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class PinUpPacksController extends AbstractSettingsController
{
    /**
     * @Route("/puppacks", name="puppacks")
     */
    public function index(Request $request)
    {
        if (!$this->settings->getPinUpSystemPath()) {
            $this->addFlash('warning', 'PinUp System patch not set. Check your settings.');
            return $this->redirectToRoute('settings');
        }

        $pupPacks = $this->getExistingPupPacks();

        $form = $this->createFormBuilder();

        foreach ($pupPacks as $rom => $table) {
            $real_rom = ltrim($rom, '_');
            $form->add(md5($real_rom), CheckboxType::class, [
                'label' => $real_rom . ' >>> DOF: ' . $table,
                'data' => $real_rom === $rom,
                'required' => false,
            ]);
        }

        $form = $form->add('save', SubmitType::class, ['label' => 'Save'])->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $filesystem = $this->getFilesystem();
            $path = $this->settings->getPinUpPacksPath() . DIRECTORY_SEPARATOR;
            $data = $form->getData();

            foreach (array_keys($pupPacks) as $rom) {
                $real_rom = ltrim($rom, '_');
                $md5_real_rom = md5($real_rom);
                if (!empty($data[$md5_real_rom]) && $real_rom !== $rom) {
                    // Activate PUPPack.
                    if ($filesystem->exists($path . $rom)) {
                        try {
                            $filesystem->rename($path . $rom, $path . $real_rom, true);
                            $this->addFlash('success', 'Renamed ' . $rom . ' to ' . $real_rom);
                        } catch (\Exception $e) {
                        }
                    }
                }
                elseif (empty($data[$md5_real_rom]) && $real_rom === $rom) {
                    // Deactivate PUPPack.
                    if ($filesystem->exists($path . $real_rom)) {
                        try {
                            $filesystem->rename($path . $real_rom, $path . '_' . $real_rom, true);
                            $this->addFlash('success', 'Renamed ' . $real_rom . ' to _' . $real_rom);
                        } catch (\Exception $e) {
                        }
                    }
                }
            }
            // We need to redirect to force a form refresh.
            return $this->redirectToRoute('puppacks');
        }

        return $this->render('puppacks/index.html.twig', [
            'puppacks_form' => $form->createView(),
        ]);
    }

    private function getExistingPupPacks(): array
    {
        $path = $this->settings->getPinUpPacksPath();
        if (!file_exists($path)) {
            $this->addFlash('danger', 'Directory ' . $path . ' does not exist!');
            return [];
        }
        if (!is_writable($path)) {
            $this->addFlash('warning', 'Directory ' . $path . ' is not writable!');
        }
        return Utility::getExistingPupPacks($this->settings);
    }
}
