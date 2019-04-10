<?php

namespace App\Controller;

use App\Entity\Settings;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class BackglassesController extends AbstractController
{
    /**
     * @var Settings
     */
    private $settings;

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
                'choices' => $this->getGroupedBackglassChoices($backglassChoices, $basename),
                'data' => $backglassChoices[$basename] ?? '_',
            ]);
        }

        $form = $form->add('assign', SubmitType::class, ['label' => 'Assign'])->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $settings = $this->getSettings();
            $table_path = $settings->getTablesPath() . DIRECTORY_SEPARATOR;
            $data = $form->getData();
            $count = array_count_values($data);
            foreach ($data as $key => $backglass_file) {
                $target_backglass_file = $table_path . $tables[$key] . '.directb2s';
                if ('_' !== $backglass_file) {
                    if (!file_exists($target_backglass_file)) {
                        if ($count[$backglass_file] > 1) {
                            if (copy( $table_path . $backglass_file, $target_backglass_file)) {
                                $this->addFlash('success', 'Copied ' . $backglass_file . ' to ' . $tables[$key] . '.directb2s');
                            }
                        } else {
                            if (rename( $table_path . $backglass_file, $target_backglass_file)) {
                                $this->addFlash('success', 'Renamed ' . $backglass_file . ' to ' . $tables[$key] . '.directb2s');
                            }
                        }
                    }
                } else {
                    if (file_exists($target_backglass_file)) {
                        if (rename( $target_backglass_file, $table_path . '_' . $tables[$key] . '.directb2s')) {
                            $this->addFlash('success', 'Renamed ' . $tables[$key] . '.directb2s to _' . $tables[$key] . '.directb2s');
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
        foreach (scandir($this->getSettings()->getTablesPath()) as $filename) {
            $basename = preg_replace('/\.vpx/i', '', $filename);
            if ($basename !== $filename) {
                $tables[md5($filename)] = $basename;
                continue;
            }
            $basename = preg_replace('/\.directb2s/i', '', $filename);
            if ($basename !== $filename) {
                $backglasses[$basename] = $filename;
            }
        }
        return [$tables, $backglasses];
    }

    private function getGroupedBackglassChoices(array $choices, string $basename): array
    {
        $group = [
            'Current' => [],
            'Suggested' => [],
            'Enabled' => [],
            'Disabled'=> [],
        ];

        foreach ($choices as $key => $value) {
            if ($key === $basename) {
                $group['Current'][$key] = $value;
            } elseif (strtolower(substr(ltrim($key, '_'), 0, 4)) === strtolower(substr($basename, 0, 4))) {
                $group['Suggested'][$key] = $value;
            } elseif (0 !== strpos($key, '_')) {
                $group['Enabled'][$key] = $value;
            } else {
                $group['Disabled'][$key] = $value;
            }
        }

        return [
            'No Backglass or PUP Pack' => '_',
        ] + array_filter($group, 'count');
    }

    private function getSettings(): Settings {
        if (!$this->settings) {
            $this->settings = new Settings();
            $this->settings->load();
        }
        return $this->settings;
    }
}
