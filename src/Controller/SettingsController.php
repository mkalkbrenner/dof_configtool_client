<?php

namespace App\Controller;

use App\Entity\DirectOutputConfig;
use App\Entity\DofDatabaseSettings;
use App\Entity\Settings;
use FM\ElfinderBundle\Form\Type\ElFinderType;
use GitWrapper\GitException;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
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
            // ->add('visualPinballPath', ElFinderType::class, ['label' => 'Visual Pinball Path', 'instance' => 'form', 'enable' => true])
            ->add('bsPatchBinary', TextType::class, ['label' => 'pspatch Binary'])
            ->add('versionControl', CheckboxType::class, ['label' => 'Enable Version Control via Git'])
            ->add('gitBinary', TextType::class, ['label' => 'Git Binary'])
            ->add('gitUser', TextType::class, ['label' => 'Git User'])
            ->add('gitEmail', TextType::class, ['label' => 'Git Email']);

        $rgbToys = $this->settings->getRgbToys();
        $dofConfigPath = $this->settings->getDofConfigPath();
        $dofDatabaseSettings = new DofDatabaseSettings();
        $portAssignmentsDatabase = $dofDatabaseSettings->getPortAssignments();
        $portAssignments = $this->settings->getPortAssignments();
        $directOutputConfigs = [];
        if (is_dir($dofConfigPath) && is_readable($dofConfigPath)) {
            $choices = array_merge(['-'], $portAssignmentsDatabase[51], $portAssignmentsDatabase[30]);
            foreach (scandir($dofConfigPath) as $file) {
               if (preg_match('/^directoutputconfig(\d+)\.ini$/i', $file, $matches)) {
                   $deviceId = $matches[1];
                   $directOutputConfigs[$deviceId] = new DirectOutputConfig($dofConfigPath . DIRECTORY_SEPARATOR . $file);
                   $directOutputConfigs[$deviceId]->load();
                   $deviceName = $directOutputConfigs[$deviceId]->getDeviceName();
                   $ports = 0;
                   foreach ($directOutputConfigs[$deviceId]->getGames() as $game) {
                       if (is_array($game) && max(array_keys($game)) > $ports) {
                           $ports = max(array_keys($game));
                       }
                   }
                   for ($i=1; $i <= $ports; $i++) {
                       $data = explode('|', ($portAssignments[$deviceId][$i]) ?? '-');

                       $formBuilder->add($deviceId . '_' . $i, ChoiceType::class,  [
                           'label' => $deviceName . ' - Port #' . $i,
                           'choices' => array_combine($choices, $choices),
                           'data' => $data,
                           'multiple' => true,
                       ]);

                       foreach ($data as $toy) {
                           if (in_array($toy, $rgbToys)) {
                               for ($k = 0; $k <= 1; $k++) {
                                   $formBuilder->add($deviceId . '_' . ++$i, ChoiceType::class, [
                                       'label' => $deviceName . ' - Port #' . $i,
                                       'choices' => array_combine($choices, $choices),
                                       'data' => $data,
                                       'multiple' => true,
                                       'disabled' => true,
                                   ]);
                               }
                               break;
                           }
                       }
                   }
               }
            }
            $formBuilder->add('autodetect', SubmitType::class, ['label' => 'Autodetect port assignments and save settings']);
        }

        $form = $formBuilder
            ->add('save', SubmitType::class, ['label' => 'Save settings'])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Settings $settings */
            $this->settings = $form->getData();

            /** @var \Symfony\Component\Form\Form $form */
            $name = $form->getClickedButton()->getConfig()->getName();
            switch ($name) {
                case 'autodetect':
                    $previous_branch = '';
                    if ($this->settings->isVersionControl()) {
                        try {
                            $workingCopy = $this->getGitWorkingCopy($this->settings->getDofConfigPath());
                            $branches = $workingCopy->getBranches();
                            if (!in_array('download', $branches->all())) {
                                $workingCopy->checkoutNewBranch('download');
                            } else {
                                $previous_branch = $this->getCurrentBranch($workingCopy);
                                $workingCopy->checkout('download');
                            }
                        } catch (GitException $e) {
                            $this->addFlash('warning', $e->getMessage());
                        }
                    }

                    $dofDatabaseSettings->load();
                    $autodetectedPortAssignments = [];
                    $autodetectedComboCandidates = [];
                    foreach (array_keys($portAssignmentsDatabase) as $databaseDeviceId) {
                        $directOutputConfigsDatabase = new DirectOutputConfig($dofDatabaseSettings->getDofConfigPath() . DIRECTORY_SEPARATOR . 'directoutputconfig' . $databaseDeviceId . '.ini');
                        $directOutputConfigsDatabase->load();
                        $rgbPortsDatabase = $directOutputConfigsDatabase->getRgbPorts();
                        $gamesDatabase = $directOutputConfigsDatabase->getGames();
                        /** @var DirectOutputConfig $directOutputConfig */
                        foreach ($directOutputConfigs as $deviceId => $directOutputConfig) {
                            if ($directOutputConfig->getVersion() !== $directOutputConfigsDatabase->getVersion()) {
                                $this->addFlash('danger', 'Your config files version ' . $directOutputConfig->getVersion() . ' differs from the database version ' . $directOutputConfigsDatabase->getVersion() . '. Download the latest versions first.');
                                return $this->render('settings/index.html.twig', [
                                    'settings_form' => $form->createView(),
                                ]);
                            }
                            $rgbPorts = $directOutputConfig->getRgbPorts();
                            foreach ($directOutputConfig->getGames() as $name => $game) {
                                if (is_array($game) && !empty($game)) {
                                    foreach ($game as $port => $setting) {
                                        if ($port && $setting && !in_array($port, $rgbPorts[$name]) && isset($gamesDatabase[$name])) {
                                            foreach ($gamesDatabase[$name] as $portDatabase => $settingDatabase) {
                                                if ($portDatabase && $settingDatabase && !in_array($portDatabase, $rgbPortsDatabase[$name])) {
                                                    // In case of an "rgbsplit" we need to skip two ports.
                                                    if (isset($portAssignmentsDatabase[$databaseDeviceId][$portDatabase])) {
                                                        $weight = 0;
                                                        if ($setting === $settingDatabase) {
                                                            $weight = 2;
                                                        }
                                                        // In order to match combos we have to perform a substring match.
                                                        elseif (false !== strpos($setting, '/' . $settingDatabase) || false !== strpos($setting, $settingDatabase . '/')) {
                                                            $weight = 1;
                                                            $autodetectedComboCandidates[$deviceId][$port] = true;
                                                        }
                                                        if ($weight) {
                                                            $autodetectedPortAssignments[$deviceId][$port][$portAssignmentsDatabase[$databaseDeviceId][$portDatabase]] =
                                                                ($autodetectedPortAssignments[$deviceId][$port][$portAssignmentsDatabase[$databaseDeviceId][$portDatabase]] ?? 0) + $weight;
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }


                    if ($previous_branch && 'download' !== $previous_branch) {
                        try {
                            $workingCopy->checkout($previous_branch);
                        } catch (GitException $e) {
                            $this->addFlash('warning', $e->getMessage());
                        }
                    }

                    $newPortAssignments = [];
                    foreach ($autodetectedPortAssignments as $deviceId => $ports) {
                        foreach ($ports as $port => $candidates) {
                            $highest_count = 0;
                            foreach ($candidates as $candidate => $count) {
                                if ($count > $highest_count) {
                                    $newPortAssignments[$deviceId][$port] = $candidate;
                                    $highest_count = $count;
                                }
                            }
                            if (isset($newPortAssignments[$deviceId][$port]) && isset($autodetectedComboCandidates[$deviceId][$port])) {
                                unset($candidates[$newPortAssignments[$deviceId][$port]]);
                                $threshold = (int) $highest_count / 4;
                                for ($i = 0; $i < 3; $i++) {
                                    $highest_count = $threshold;
                                    $combo_candidate = '';
                                    foreach ($candidates as $candidate => $count) {
                                        if ($count > $highest_count) {
                                            $combo_candidate = $candidate;
                                            $highest_count = $count;
                                        }
                                    }
                                    if ($combo_candidate) {
                                        $newPortAssignments[$deviceId][$port] .= '|' . $combo_candidate;
                                        unset($candidates[$combo_candidate]);
                                    } else {
                                        $i = 3;
                                    }
                                }
                            }
                        }
                        $this->settings->setPortAssignments($newPortAssignments);
                    }
                    #break;

                case 'save':
                    try {
                        $this->settings->persist();
                    } catch (\Exception $e) {
                        $this->addFlash('warning', $e->getMessage());
                        break;
                    }

                    if ('save' === $name) {
                        $this->addFlash('success', 'Saved settings to ' . $this->settings->getIni() . '.');
                    }
                    // Force reload settings.
                    return $this->redirectToRoute('settings');
            }
        }

        return $this->render('settings/index.html.twig', [
            'settings_form' => $form->createView(),
        ]);
    }
}
