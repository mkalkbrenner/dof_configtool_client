<?php

namespace App\Controller;

use App\Entity\DirectOutputConfig;
use App\Entity\DofDatabaseSettings;
use GitWrapper\GitException;
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
            ->add('download_database', SubmitType::class, ['label' => 'Download your config files & DOF database config files'])
            ->add('download', SubmitType::class, ['label' => 'Download your config files only'])
            ->add('database', SubmitType::class, ['label' => 'Download DOF database config files only'])
            ->getForm();

        $form->handleRequest($request);

        $changes = '';
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var \Symfony\Component\Form\Form $form */
            $names = explode('_', $form->getClickedButton()->getConfig()->getName());
            foreach ($names as $name) {
                switch ($name) {
                    case 'database':
                        $this->settings = new DofDatabaseSettings();
                        $this->settings->load();
                    // no break;
                    case 'download':
                        ini_set('set_time_limit', 0);

                        $zip_file = $this->filesystem->tempnam($this->filesystem->getTempDir(), 'dof_config');
                        if (copy('http://configtool.vpuniverse.com/api.php?query=getconfig&apikey=' . $this->settings->getLcpApiKey(), $zip_file) && filesize($zip_file)) {
                            $config_path = $this->settings->getDofConfigPath();
                            try {
                                $previous_branch = 'download';
                                if ($this->settings->isVersionControl()) {
                                    $workingCopy = $this->getGitWorkingCopy($config_path);
                                    $branches = $workingCopy->getBranches();
                                    if (!in_array('download', $branches->all())) {
                                        $workingCopy->checkoutNewBranch('download');
                                    } else {
                                        $previous_branch = $this->getCurrentBranch($workingCopy);
                                        $workingCopy->checkout('download');
                                    }
                                }

                                $zip = new \ZipArchive();
                                if (true == $zip->open($zip_file)) {
                                    $zip->extractTo($config_path);
                                    $zip->close();
                                    $version = 0;

                                    foreach (scandir($config_path) as $file) {
                                        if (preg_match(DirectOutputConfig::FILE_PATERN, $file, $matches)) {
                                            $directOutputConfig = new DirectOutputConfig($config_path . DIRECTORY_SEPARATOR . $file);
                                            $directOutputConfig->load();
                                            $version = $directOutputConfig->getVersion();
                                            break;
                                        }
                                    }
                                    $this->addFlash('success', 'Successfully downloaded and extracted configuration files version ' . $version . ' to ' . $config_path . '.');

                                    if ($this->settings->isVersionControl() && $workingCopy->hasChanges()) {
                                        try {
                                            $workingCopy->add('*.ini');
                                            $workingCopy->add('*.xml');
                                            $workingCopy->add('*.png');
                                            $workingCopy->commit('Version ' . $version . ' | downloaded from configtool.vpuniverse.com');
                                            $changes = nl2br($workingCopy->run('show'));
                                            if ('download' !== $previous_branch) {
                                                $workingCopy->checkout($previous_branch);
                                            }
                                        } catch (GitException $e) {
                                            $this->addFlash('warning', $e->getMessage());
                                        }
                                    }
                                } else {
                                    $this->addFlash('warning', 'Failed to extract downloaded files! Please verify that the target directory is writable.');
                                }
                            } catch (GitException $e) {
                                $this->addFlash('warning', $e->getMessage());
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
        }

        return $this->render('download/index.html.twig', [
            'download_form' => $form->createView(),
            'git_diff' => $changes,
        ]);
    }
}
