<?php

namespace App\Controller;

use App\Entity\Settings;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ColorizeController extends AbstractController
{
    /**
     * @var Settings
     */
    private $settings;

    /**
     * @Route("/colorize", name="colorize")
     */
    public function index(Request $request)
    {
        $form = $this->createFormBuilder()
            ->add('patch', FileType::class, ['label' => 'Color patch file (zip)'])
            ->add('check', SubmitType::class, ['label' => 'Check Color Patch'])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            /** @var UploadedFile $patch */
            $patch = $data['patch'];
            $files = $this->getPatchFiles($patch->getRealPath());

            if (!empty($files['txt'])) {
                $this->addFlash('info', nl2br($files['txt']));
            }
            if (isset($files['diff'])) {
                $this->addFlash('success', 'Detected patch.');
                return $this->redirectToRoute('colorize_patch', ['patch_path' => $files['dir']]);
            } elseif (isset($files['vni'])) {
                $this->addFlash('success', 'Detected vni file.');
                return $this->redirectToRoute('colorize_extract', ['patch_path' => $files['dir']]);
            }
            $this->addFlash('warning', 'The patch zip file is invalid.');
        }

        return $this->render('colorize/index.html.twig', [
            'colorize_form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/colorize/patch", name="colorize_patch")
     */
    public function patch(Request $request)
    {
        $form = $this->createFormBuilder()
            ->add('rom', FileType::class, ['label' => 'ROM file to patch (zip or bin)', 'required' => false])
            ->add('existing_rom', ChoiceType::class, ['label' => 'ROM file to patch', 'choices' => $this->getExistingRomFileChoices(), 'required' => false])
            ->add('name', TextType::class, ['label' => 'Colorized ROM file name'])
            ->add('colorize', SubmitType::class, ['label' => 'Colorize ROM'])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $name = $data['name'];
            $settings = $this->getSettings();
            $files = $this->scanPatchDir($request->get('patch_path'));
            $rom_realpath = '';

            if (!empty($data['rom'])) {
                /** @var UploadedFile $rom */
                $rom = $data['rom'];
                switch (strtolower($rom->getClientOriginalExtension())) {
                    case 'ZIP':
                    case 'zip':
                        $rom_file = $this->getRomFile($rom_realpath);
                        $rom_realpath = $rom_file['rom'];
                        break;

                    default:
                        $rom_realpath = $rom->getRealPath();
                }
            } elseif (!empty($data['existing_rom'])) {
                $rom_realpath = $data['existing_rom'];
            } else {
                $this->addFlash('warning', 'Failed to open ROM.');
            }

            if ($rom_realpath) {
                $bspatch = 'bspatch'; // Unix
                if (extension_loaded('com_dotnet')) {
                    $bspatch = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'bin' . DIRECTORY_SEPARATOR . 'bsdiff_win_exe' . DIRECTORY_SEPARATOR . 'bspatch.exe';
                }

                $colorized_rom = $files['dir'] . DIRECTORY_SEPARATOR . $name . '.bin';
                system($bspatch . ' ' . $rom_realpath . ' ' . $colorized_rom . ' ' . $files['dir'] . DIRECTORY_SEPARATOR . $files['diff']);
                $colorized_rom_zip = new \ZipArchive();
                $colorized_rom_zip_file = $settings->getRomsPath() . DIRECTORY_SEPARATOR . $name . '.zip';
                if (true === $colorized_rom_zip->open($colorized_rom_zip_file, \ZipArchive::CREATE)) {
                    $colorized_rom_zip->addFile($colorized_rom);
                    if ($colorized_rom_zip->close()) {
                        $this->addFlash('success', 'Saved colorized ROM as ' . $colorized_rom_zip_file . '.');
                        $this->copyPin2DmdFiles($files, $name);
                    } else {
                        $this->addFlash('warning', 'Failed to save ' . $colorized_rom_zip_file . '.');
                    }
                } else {
                    $this->addFlash('warning', 'Failed to open ' . $colorized_rom_zip_file . '.');
                }

                foreach (scandir($files['dir']) as $filename) {
                    if (!preg_match('/^\.+$/', $filename)) {
                        @unlink($filename);
                    }
                }
                @rmdir($files['dir']);
            }
        }

        return $this->render('colorize/index.html.twig', [
            'colorize_form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/colorize/extract", name="colorize_extract")
     */
    public function extract(Request $request)
    {
        $form = $this->createFormBuilder()
            ->add('existing_rom', ChoiceType::class, ['label' => 'ROM file', 'choices' => $this->getExistingRomFileChoices()])
            ->add('colorize', SubmitType::class, ['label' => 'Colorize ROM'])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $files = $this->scanPatchDir($request->get('patch_path'));
            $rom_name = basename(strtolower($data['existing_rom']), '.zip');
            $this->copyPin2DmdFiles($files, $rom_name);
        }

        return $this->render('colorize/index.html.twig', [
            'colorize_form' => $form->createView(),
        ]);
    }

    private function getPatchFiles(string $patch_path): array
    {
        $files = [
            'dir' => tempnam(sys_get_temp_dir(), 'color_patch')
        ];
        $patch_zip = new \ZipArchive();
        unlink($files['dir']);
        if (mkdir($files['dir']) && is_dir($files['dir'])) {
            if (true === $patch_zip->open($patch_path)) {
                if ($patch_zip->extractTo($files['dir'])) {
                    $files += $this->scanPatchDir($files['dir']);
                } else {
                    $this->addFlash('warning', 'Failed to extract patch zip.');
                }
                $patch_zip->close();
            } else {
                $this->addFlash('warning', 'Failed to open color patch zip.');
            }
        }
        return $files;
    }

    private function scanPatchDir(string $patch_dir): array
    {
        $files = [
            'dir' => $patch_dir,
            'txt' => '',
        ];

        foreach (scandir($patch_dir) as $filename) {
            if (preg_match('/\.diff$/i', $filename)) {
                $files['diff'] = $filename;
            } elseif (preg_match('/\.pal$/i', $filename)) {
                $files['pal'] = $filename;
            } elseif (preg_match('/\.vni$/i', $filename)) {
                $files['vni'] = $filename;
            } elseif (preg_match('/\.txt$/i', $filename)) {
                $files['txt'] .= file_get_contents($files['dir'] . DIRECTORY_SEPARATOR . $filename);
            }
        }

        return $files;
    }

    private function copyPin2DmdFiles(array $files, string $rom_name): void {
        $settings = $this->getSettings();
        $altcolor_dir = $settings->getAltcolorPath();
        $rom_dir = $altcolor_dir . DIRECTORY_SEPARATOR . $rom_name;
        if (!is_dir($rom_dir)) {
            mkdir($rom_dir);
        }
        if (!empty($files['pal'])) {
            $pal = $rom_dir . DIRECTORY_SEPARATOR . 'pin2dmd.pal';
            if (copy($files['dir'] . DIRECTORY_SEPARATOR . $files['pal'], $pal)) {
                $this->addFlash('success', 'Saved ' . $pal . '.');
            } else {
                $this->addFlash('warning', 'Failed to write ' . $pal . '.');
            }
        }
        if (!empty($files['vni'])) {
            $vni = $rom_dir . DIRECTORY_SEPARATOR . 'pin2dmd.vni';
            if (copy($files['dir'] . DIRECTORY_SEPARATOR . $files['vni'], $vni)) {
                $this->addFlash('success', 'Saved ' . $vni . '.');
            } else {
                $this->addFlash('warning', 'Failed to write ' . $vni . '.');
            }
        }
    }

    private function getRomFile(string $rom_path): array
    {
        $files = [
            'dir' => tempnam(sys_get_temp_dir(), 'rom'),
        ];
        $patch_zip = new \ZipArchive();
        unlink($files['dir']);
        if (mkdir($files['dir']) && is_dir($files['dir'])) {
            if ($patch_zip->open($rom_path) && $patch_zip->extractTo($files['dir'])) {
                foreach (scandir($files['dir']) as $filename) {
                    if (preg_match('/\.bin/i', $filename)) {
                        $files['bin'] = $filename;
                    }
                }
                $patch_zip->close();
            }
        }
        return $files;
    }

    private function getExistingRomFileChoices(): array
    {
        $choices = [];
        foreach (scandir($this->getSettings()->getRomsPath()) as $filename) {
            if (preg_match('/\.zip/i', $filename)) {
                $choices[$filename] = $filename;
            }
        }
        return $choices;
    }

    private function getSettings(): Settings {
        if (!$this->settings) {
            $this->settings = new Settings();
            $this->settings->load();
        }
        return $this->settings;
    }
}
