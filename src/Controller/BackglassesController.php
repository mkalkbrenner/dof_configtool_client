<?php

namespace App\Controller;

use App\Component\Utility;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class BackglassesController extends AbstractSettingsController
{
    /**
     * @Route("/backglasses", name="backglasses")
     */
    public function index(Request $request)
    {
        list($tables, $backglassChoices) = $this->getExistingTablesAndBackglassChoices();

        $form = $this->createFormBuilder();

        foreach ($tables as $key => $basename) {
            $form->add($key, ChoiceType::class, [
                'label' => $basename,
                'choices' => Utility::getGroupedBackglassChoices($backglassChoices, $basename),
                'data' => $backglassChoices[$basename] ?? '_',
            ]);
        }

        $form = $form->add('assign', SubmitType::class, ['label' => 'Assign'])->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $filesystem = $this->getFilesystem();
            $table_path = $this->settings->getTablesPath() . DIRECTORY_SEPARATOR;
            $data = $form->getData();
            $count = array_count_values($data);
            foreach ($data as $key => $backglass_file) {
                $target_backglass_file = $tables[$key] . '.directb2s';
                $disabled_backglass_file = '_' . $target_backglass_file;
                if ($target_backglass_file !== $backglass_file) {
                    if ('_' !== $backglass_file) {
                        if ($filesystem->exists($table_path . $target_backglass_file)) {
                            try {
                                $filesystem->rename($table_path . $target_backglass_file, $table_path . $disabled_backglass_file);
                                $this->addFlash('success', 'Renamed ' . $target_backglass_file . ' to ' . $disabled_backglass_file);
                            } catch (\Exception $e) {
                            }
                        }
                        if (!$filesystem->exists($table_path . $target_backglass_file)) {
                            if ($count[$backglass_file] > 1) {
                                try {
                                    $filesystem->copy($table_path . $backglass_file, $table_path . $target_backglass_file);
                                    $this->addFlash('success', 'Copied ' . $backglass_file . ' to ' . $target_backglass_file);
                                } catch (\Exception $e) {
                                }
                            } else {
                                try {
                                    $filesystem->rename($table_path . $backglass_file, $table_path . $target_backglass_file);
                                    $this->addFlash('success', 'Renamed ' . $backglass_file . ' to ' . $target_backglass_file);
                                } catch (\Exception $e) {
                                }
                            }
                        }
                    } else {
                        if ($filesystem->exists($table_path . $target_backglass_file)) {
                            while ($filesystem->exists($table_path . $disabled_backglass_file)) {
                                if (preg_match('/^(.+?)(\d*)\.directb2s$/', $disabled_backglass_file, $matches)) {
                                    $counter = (int) ($matches[2] ?? 0);
                                    $disabled_backglass_file = $matches[1] . ++$counter . '.directb2s';
                                }
                                else {
                                    $this->addFlash('warning', 'Failed to disable ' . $target_backglass_file);
                                    break(2);
                                }
                            }
                            try {
                                $filesystem->rename($table_path . $target_backglass_file, $table_path . $disabled_backglass_file);
                                $this->addFlash('success', 'Renamed ' . $target_backglass_file . ' to _' . $disabled_backglass_file);
                            } catch (\Exception $e) {
                            }
                        }
                    }
                }
            }
            // We need to redirect to force a form refresh.
            return $this->redirectToRoute('backglasses');
        }

        return $this->render('backglasses/index.html.twig', [
            'backglasses_form' => $form->createView(),
        ]);
    }

    private function getExistingTablesAndBackglassChoices(): array
    {
        $tables_path = $this->settings->getTablesPath();
        if (!is_writable($tables_path)) {
            $this->addFlash('danger', 'Directory ' . $tables_path . ' is not writable!');
        }
        return Utility::getExistingTablesAndBackglassChoices($this->settings);
    }
}
