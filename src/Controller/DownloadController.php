<?php

namespace App\Controller;

use App\Entity\Settings;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class DownloadController extends AbstractController
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

                    $dofConfigtoolDownload = new Settings();
                    $dofConfigtoolDownload->load();

                    $zip_file = tempnam(sys_get_temp_dir(), 'dof_config');
                    if (copy('http://configtool.vpuniverse.com/api.php?query=getconfig&apikey=' . $dofConfigtoolDownload->getLcpApiKey(), $zip_file) && filesize($zip_file)) {
                        try {
                            $config_path = $dofConfigtoolDownload->getDofConfigPath();
                            if (!is_dir($config_path)) {
                                mkdir($config_path);
                            }
                            $zip = new \ZipArchive();
                            if ($zip->open($zip_file) && $zip->extractTo($config_path)) {
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
                    @unlink($zip_file);
                    break;
            }
        }

        return $this->render('download/index.html.twig', [
            'download_form' => $form->createView(),
        ]);
    }
}
