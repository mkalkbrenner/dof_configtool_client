<?php

namespace App\Controller;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ColorizeController extends AbstractSettingsController
{
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
            $files = $this->getPatchFiles($patch->getPathname());

            if (!empty($files['txt'])) {
                $this->addFlash('info', nl2br($files['txt']));
            }
            if (isset($files['diff'])) {
                $this->addFlash('success', 'Detected patch.');
                return $this->redirectToRoute('colorize_patch', ['patch_path' => $files['dir']]);
            }
            if (isset($files['vni'])) {
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
        $patch_path = $request->get('patch_path');

        $form = $this->createFormBuilder()
            ->add('rom', FileType::class, ['label' => 'ROM file to patch (zip or bin)', 'required' => false])
            ->add('existing_rom', ChoiceType::class, ['label' => 'ROM file to patch', 'choices' => $this->getExistingRomFileChoices(), 'required' => false])
            ->add('name', TextType::class, ['label' => 'Desired file name for the colorized ROM'])
            ->add('colorize', SubmitType::class, ['label' => 'Colorize ROM'])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $name = basename(basename($data['name'], '.bin'), '.zip');

            if (!empty($data['rom'])) {
                /** @var UploadedFile $rom */
                $rom = $data['rom'];
                $rom->move($patch_path, $rom->getClientOriginalName());
            } elseif (!empty($data['existing_rom'])) {
                $this->filesystem->copy($this->settings->getRomsPath() . DIRECTORY_SEPARATOR . $data['existing_rom'], $patch_path . DIRECTORY_SEPARATOR . $data['existing_rom']);
            } else {
                $this->addFlash('warning', 'Failed to open ROM.');
            }

            $files = $this->scanPatchDir($request->get('patch_path'));

            if (!empty($files['bin'])) {

                $bspatch = 'bspatch'; // Unix
                if (extension_loaded('com_dotnet')) {
                    $bspatch = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'bin' . DIRECTORY_SEPARATOR . 'bsdiff_win_exe' . DIRECTORY_SEPARATOR . 'bspatch.exe';
                }

                $colorized_rom = $files['dir'] . DIRECTORY_SEPARATOR . $name . '.bin';
                $command = $bspatch . ' ' . $files['dir'] . DIRECTORY_SEPARATOR . $files['bin'] . ' ' . $colorized_rom . ' ' . $files['dir'] . DIRECTORY_SEPARATOR . $files['diff'];
                if (false !== system($command) && $this->filesystem->exists($colorized_rom)) {

                    $colorized_rom_zip = new \ZipArchive();
                    $colorized_rom_zip_file = $this->settings->getRomsPath() . DIRECTORY_SEPARATOR . $name . '.zip';
                    if (true === $colorized_rom_zip->open($colorized_rom_zip_file, \ZipArchive::CREATE)) {
                        $colorized_rom_zip->addFile($colorized_rom, $name . '.bin');
                        if ($colorized_rom_zip->close()) {
                            $this->addFlash('success', 'Saved colorized ROM as ' . $colorized_rom_zip_file . '.');
                            $this->copyPin2DmdFiles($files, $name);
                            $this->filesystem->remove($files['dir']);
                            return $this->redirectToRoute('colorize');
                        }
                        $this->addFlash('warning', 'Failed to save ' . $colorized_rom_zip_file . '.');
                    } else {
                        $this->addFlash('warning', 'Failed to open ' . $colorized_rom_zip_file . '.');
                    }
                } else {
                    $this->addFlash('warning', 'Failed to execute ' . $command);
                }

                $this->filesystem->remove($files['dir']);
            } else {
                $this->addFlash('warning', 'Failed to patch the ROM.');
            }
        }

        return $this->render('colorize/patch.html.twig', [
            'colorize_form' => $form->createView(),
            'patch_path' => $patch_path,
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
            'dir' => $this->filesystem->tempdir($this->filesystem->getTempDir(), 'color_patch_'),
        ];
        $patch_zip = new \ZipArchive();
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
            } elseif (preg_match('/\.zip$/i', $filename)) {
                $this->extractRomFile($filename, $files);
            } elseif (preg_match('/\.bin$/i', $filename)) {
                $files['bin'] = $filename;
            }
        }

        return $files;
    }

    private function extractRomFile(string $rom_filename, array &$files): void
    {
        $patch_zip = new \ZipArchive();
        if (true === $patch_zip->open($files['dir'] . DIRECTORY_SEPARATOR . $rom_filename) && $patch_zip->extractTo($files['dir'])) {
            foreach (scandir($files['dir']) as $filename) {
                if (preg_match('/\.bin$/i', $filename)) {
                    $files['bin'] = $filename;
                    break;
                }
            }
            $patch_zip->close();
        }
    }

    private function copyPin2DmdFiles(array $files, string $rom_name): void {
        $altcolor_dir = $this->settings->getAltcolorPath();
        $rom_dir = $altcolor_dir . DIRECTORY_SEPARATOR . $rom_name;
        try {
            $this->filesystem->mkdir($rom_dir);
            if (!empty($files['pal'])) {
                $pal = $rom_dir . DIRECTORY_SEPARATOR . 'pin2dmd.pal';
                $this->filesystem->copy($files['dir'] . DIRECTORY_SEPARATOR . $files['pal'], $pal);
                $this->addFlash('success', 'Saved ' . $pal . '.');
            }
            if (!empty($files['vni'])) {
                $vni = $rom_dir . DIRECTORY_SEPARATOR . 'pin2dmd.vni';
                $this->filesystem->copy($files['dir'] . DIRECTORY_SEPARATOR . $files['vni'], $vni);
                $this->addFlash('success', 'Saved ' . $vni . '.');
            }
        } catch(\Exception $e) {
            $this->addFlash('warning', $e->getMessage());
        }
    }

    private function getExistingRomFileChoices(): array
    {
        $choices = [];
        foreach (scandir($this->settings->getRomsPath()) as $filename) {
            if (preg_match('/\.zip$/i', $filename)) {
                $choices[$filename] = $filename;
            }
        }
        return $choices;
    }
}
