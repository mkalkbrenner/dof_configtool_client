<?php

namespace App\Controller;

use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class DownloadController extends AbstractSettingsController
{
    /**
     * @Route("/download", name="download")
     */
    public function index(Request $request)
    {
        $form = $this->createFormBuilder()
            ->add('download', SubmitType::class, ['label' => 'Download config files'])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var \Symfony\Component\Form\Form $form */
            $name = $form->getClickedButton()->getConfig()->getName();
            switch ($name) {
                case 'download':
                    ini_set('set_time_limit', 0);

                    $zip_file = $this->filesystem->tempnam($this->filesystem->getTempDir(), 'dof_config');
                    if (copy('http://configtool.vpuniverse.com/api.php?query=getconfig&apikey=' . $this->settings->getLcpApiKey(), $zip_file) && filesize($zip_file)) {
                        try {
                            $config_path = $this->settings->getDofConfigPath();
                            $zip = new \ZipArchive();
                            if (true == $zip->open($zip_file)) {
                                $zip->extractTo($config_path);
                                $zip->close();
                                $this->addFlash('success', 'Successfully downloaded and extracted configuration files to ' . $config_path . '.');
                            } else {
                                $this->addFlash('warning', 'Failed to extract downloaded files! Please verify that the target directory is writable.');
                            }
                        } catch (\Exception $e) {
                            $this->addFlash('warning', file_get_contents($zip_file));
                        }
                    } else {
                        $this->addFlash('warning', 'Download failed!');
                    }
                    $this->filesystem->remove($zip_file);
                    break;
            }
        }

        return $this->render('download/index.html.twig', [
            'download_form' => $form->createView(),
        ]);
    }
}
