<?php

namespace App\Controller;

use App\Entity\DofConfigtoolDownload;
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
        $dofConfigtoolDownload = new DofConfigtoolDownload();
        $dofConfigtoolDownload->load();

        $form = $this->createFormBuilder($dofConfigtoolDownload)
            ->add('lcpApiKey', TextType::class, ['label' => 'LCP_APIKEY'])
            ->add('dofConfigPath', TextType::class, ['label' => 'DOF_CONFIG_PATH'])
            ->add('save', SubmitType::class, ['label' => 'Save settings'])
            ->add('download', SubmitType::class, ['label' => 'Download config files'])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var DofConfigtoolDownload $dofConfigtoolDownload */
            $dofConfigtoolDownload = $form->getData();

            /** @var \Symfony\Component\Form\Form $form */
            $name = $form->getClickedButton()->getConfig()->getName();
            switch ($name) {
                case 'download':
                    ini_set('set_time_limit', 0);
                    $zip_file = tempnam(sys_get_temp_dir(), 'dof_config');
                    if (copy('http://configtool.vpuniverse.com/api.php?query=getconfig&apikey=' . $dofConfigtoolDownload->getLcpApiKey(), $zip_file) && filesize($zip_file)) {
                        try {
                            $zip = new \ZipArchive();
                            if ($zip->open($zip_file) && $zip->extractTo($dofConfigtoolDownload->getDofConfigPath())) {
                                $this->addFlash('success', 'Successfully downloaded and extracted configuration files to ' . $dofConfigtoolDownload->getDofConfigPath() . '.');
                            } else {
                                $this->addFlash('warning', 'Failed to extract downloaded files!');
                            }
                        } catch (\Exception $e) {
                            $this->addFlash('warning', file_get_contents($zip_file));
                        }
                    } else {
                        $this->addFlash('warning', 'Download failed!');
                    }
                    @unlink($zip_file);

                // no break;

                case 'save':
                    try {
                        $dofConfigtoolDownload->persist();
                    } catch (\Exception $e) {
                        $this->addFlash('warning', $e->getMessage());
                        break;
                    }

                    if ('save' === $name) {
                        $this->addFlash('success', 'Saved settings to download.ini.');
                    }
                    break;
            }
        }

        return $this->render('download/index.html.twig', [
            'download_form' => $form->createView(),
        ]);
    }
}
