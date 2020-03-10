<?php

namespace App\Controller;

use App\Component\Utility;
use App\Entity\B2STableSetting;
use App\Entity\B2STableSettings;
use App\Entity\PinballYGameStats;
use App\Entity\PinballYMedia;
use App\Entity\PinballYMenu;
use App\Entity\ScreenRes;
use App\Entity\VPinMameRegEntries;
use App\Entity\VPinMameRegEntry;
use App\Form\Type\B2STableSettingDisabledType;
use App\Form\Type\B2STableSettingType;
use App\Form\Type\VPinMameRegEntryType;
use GitWrapper\GitException;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Windows\Registry\KeyNotFoundException;

class TablesController extends AbstractSettingsController
{
    /**
     * @Route("/tables", name="tables")
     */
    public function index(Request $request)
    {
        list($tables, $backglassChoices) = Utility::getExistingTablesAndBackglassChoices($this->settings);
        asort($tables, SORT_NATURAL | SORT_FLAG_CASE);
        return $this->render('tables/index.html.twig', [
            'tables' => $tables,
        ]);
    }

    /**
     * @Route("/table/{hash}/{selected_rom}", name="table")
     */
    public function table(Request $request, string $hash, string $selected_rom)
    {
        list($tables, $backglassChoices) = Utility::getExistingTablesAndBackglassChoices($this->settings);
        $table_name = $tables[$hash];
        $roms = [];
        $alias =
        $added =
        $last_played =
            null;
        $description =
        $manufacturer =
        $year =
            'PinballY required!';
        $topper =
        $dmd =
        $instcard =
            'pup';
        $script = $this->settings->getTablesPath() . DIRECTORY_SEPARATOR . $table_name . '.vbs';
        $script_extracted = file_exists($script);

        if ($script_extracted) {
            $script_content = file_get_contents($script);
            if (preg_match('/cGameName\s*=\s*[\'"]([^\'"]+)[\'"]/i', $script_content, $matches)) {
                $rom = $matches[1];
                if (!file_exists($this->settings->getRomsPath() . DIRECTORY_SEPARATOR . $rom . '.zip')) {
                    $aliases = $this->settings->getAliasRoms();
                    if (isset($aliases[$rom])) {
                        $alias = $rom;
                        $rom = $aliases[$rom];
                    }
                }
                $roms = [$rom];
            }
        }

        if ($pinballYDatabaseFile = $this->settings->getPinballYVPXDatabaseFile()) {
            $pinballYMenu = new PinballYMenu();
            $pinballYMenu->setFile($pinballYDatabaseFile)->load();
            if ($pinballYMenuEntry = $pinballYMenu->getMenuEntry($table_name)) {
                $pinballYGameStats = new PinballYGameStats();
                $pinballYGameStats->setFile($this->settings->getPinballYPath() . DIRECTORY_SEPARATOR . 'GameStats.csv')->load();
                if ($pinballYGameStat = $pinballYGameStats->getStat($pinballYMenuEntry->getDescription())) {
                    if ($date = $pinballYGameStat->getDateAddedDateTime()) {
                        $added = $date->format('Y-m-d H:i:s');
                    }
                    if ($date = $pinballYGameStat->getLastPlayedDateTime()) {
                        $last_played = $date->format('Y-m-d H:i:s');
                    }
                    $topper = $pinballYGameStat->isTopperShownWhenRunning() ? 'pinbally' : 'pup';
                    $dmd = $pinballYGameStat->isDmdShownWhenRunning() ? 'pinbally' : 'pup';
                    $instcard = $pinballYGameStat->isInstructionCardShownWhenRunning() ? 'pinbally' : 'pup';
                }
                $pinballYMedia = new PinballYMedia($pinballYMenuEntry->getDescription());
                $pinballYMedia->setPath($this->settings->getPinballYPath())->load();
                $description = $pinballYMenuEntry->getDescription();
                $manufacturer = $pinballYMenuEntry->getManufacturer() ?? 'unknown';
                $year = $pinballYMenuEntry->getYear() ?? 'unknown';
                $roms = $roms ?: Utility::getRomsForTable($description, $this->settings);
            }
        } elseif (!$roms) {
            $this->addFlash('danger', 'This "all in one" page uses PinballY\'s database to detect the ROM candidates. Alternatively you can extract the table script and the ROM would be looked up in it.');
        }

        $tableMapping = $this->settings->getTableMapping();
        $rom_choices = [];
        $vPinMameRegEntries = new VPinMameRegEntries();
        foreach ($roms as $rom) {
            $rom_choices[$rom . ' >>> DOF: ' .  $tableMapping[$rom]] = $rom;
        }

        if ('_' !== $selected_rom) {
            $roms = [$selected_rom];
        }

        foreach ($roms as $rom) {
            $vPinMameRegEntry = new VPinMameRegEntry();
            try {
                $vPinMameRegEntry->setRom($rom)->setTable($tableMapping[$rom] ?? '')->load();
                $vPinMameRegEntries->addEntry($vPinMameRegEntry);
            }
            catch (KeyNotFoundException $e) {
                $this->addFlash('warning', 'Registry: ' . $e->getMessage());
            }
        }

        $b2sTableSettings = new B2STableSettings();
        $b2sTableSettings->setRoms($roms)->setPath($this->settings->getTablesPath())->load(true);

        $screenRes = new ScreenRes();
        $screenRes->setPath($this->settings->getTablesPath())->load();

        $pupPacks = Utility::getExistingPupPacks($this->settings, $roms);

        $formBuilder = $this->createFormBuilder()
            ->add('table_name', TextType::class, [
                'disabled' => true,
                'data' => $description,
                'label' => false,
            ])
            ->add('manufacturer', TextType::class, [
                'disabled' => true,
                'data' => $manufacturer,
                'label' => false,
            ])
            ->add('year', TextType::class, [
                'disabled' => true,
                'data' => $year,
                'label' => false,
            ])
            ->add('table_file', TextType::class, [
                'disabled' => true,
                'data' => $table_name . '.vpx',
                'label' => false,
            ])
            ->add('added', TextType::class, [
                'disabled' => true,
                'data' => $added ?? 'unknown',
                'label' => false,
            ])
            ->add('last_played', TextType::class, [
                'disabled' => true,
                'data' => $last_played ?? 'never',
                'label' => false,
            ])
            ->add('entries', CollectionType::class, [
                'entry_type' => VPinMameRegEntryType::class,
                'data' => $vPinMameRegEntries->getEntries(),
                'label' => false,
            ])
            ->add('save', SubmitType::class, ['label' => 'Save']);

        if (count($roms) > 1) {
            $formBuilder->add('select_rom', SubmitType::class, ['label' => 'Select ROM']);
        }

        $pupPack = false;
        $disabled_puppack = '';

        if (count($roms) === 1) {
            $disabled_puppack = '_' . $roms[0];
            if (isset($pupPacks[$roms[0]]) || isset($pupPacks[$disabled_puppack])) {
                $formBuilder->add('pup_pack', CheckboxType::class, [
                    'data' => isset($pupPacks[$roms[0]]),
                    'label' => false,
                    'required' => false,
                ]);
                $pupPack = true;
            }
            elseif ($alias && (isset($pupPacks[$alias]) || isset($pupPacks['_' . $alias]))) {
                $formBuilder->add('pup_pack', CheckboxType::class, [
                    'data' => isset($pupPacks[$alias]),
                    'label' => false,
                    'required' => false,
                ]);
                $pupPack = true;
                $disabled_puppack = '_' . $alias;
            } else {
                $formBuilder->add('pup_pack', TextType::class, [
                    'disabled' => true,
                    'data' => 'No PUP Pack found for ' . $roms[0],
                    'label' => false,
                ]);
            }

            $formBuilder
                ->add('rom', TextType::class, [
                    'disabled' => true,
                    'data' => $roms[0],
                    'label' => false,
                ])
                ->add('topper', ChoiceType::class, [
                    'choices' => ['PinballY' => 'pinbally', 'None or PUP Pack' => 'pup'],
                    'data' => $topper,
                    'label' => false,
                ])
                ->add('backglass', ChoiceType::class, [
                    'choices' => Utility::getGroupedBackglassChoices($backglassChoices, $tables[$hash]),
                    'data' => $backglassChoices[$tables[$hash]] ?? '_',
                    'label' => false,
                ])
                ->add('dmd', ChoiceType::class, [
                    'choices' => ['PinballY' => 'pinbally', 'VPinMame or B2S or PUP Pack or None' => 'pup'],
                    // 'choices' => ['VPinMame external (freezy)' => 'external', 'VPinMame' => 'internal', 'B2S' => 'b2s', 'PinballY' => 'pinbally', 'None or PUP Pack' => 'pup'],
                    'data' => $dmd,
                    'label' => false,
                ])
                ->add('instruction', ChoiceType::class, [
                    'choices' => ['PinballY' => 'pinbally', 'None or PUP Pack' => 'pup'],
                    'data' => $instcard,
                    'label' => false,
                ])
                ->add('b2s_table_setting', B2STableSettingType::class, [
                    'data' => $b2sTableSettings->getTableSetting($roms[0])->trackChanges(true),
                    'label' => false,
                ]);
            if ($alias) {
                $formBuilder
                    ->add('alias', TextType::class, [
                        'disabled' => true,
                        'data' => $alias,
                        'label' => false,
                    ]);
            }
        } else {
            $formBuilder
                ->add('pup_pack', TextType::class, [
                    'disabled' => true,
                    'data' => 'Select ROM first.',
                    'label' => false,
                ])
                ->add('rom', ChoiceType::class, [
                    'choices' => $rom_choices,
                    'label' => false,
                ])
                ->add('topper', TextType::class, [
                    'disabled' => true,
                    'data' => 'Select ROM first.',
                    'label' => false,
                ])
                ->add('backglass', TextType::class, [
                    'disabled' => true,
                    'data' => 'Select ROM first.',
                    'label' => false,
                ])
                ->add('dmd', TextType::class, [
                    'disabled' => true,
                    'data' => 'Select ROM first.',
                    'label' => false,
                ])
                ->add('instruction', TextType::class, [
                    'disabled' => true,
                    'data' => 'Select ROM first.',
                    'label' => false,
                ])
                ->add('b2s_table_setting', B2STableSettingDisabledType::class, [
                    'data' => new B2STableSetting('default'),
                    'label' => false,
                ]);
        }

        $formBuilder->add('play', SubmitType::class, ['label' => 'Play']);
        $formBuilder->add('edit_table', SubmitType::class, ['label' => 'Edit Table']);
        $formBuilder->add('export_pov', SubmitType::class, ['label' => 'Export POV']);
        $formBuilder->add('extract_script', SubmitType::class, ['label' => 'Extract Script']);
        if ($script_extracted) {
            $formBuilder->add('edit_script', SubmitType::class, ['label' => 'Edit Script']);
        }

        $form = $formBuilder->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var \Symfony\Component\Form\Form $form */
            $name = $form->getClickedButton()->getConfig()->getName();
            $data = $form->getData();
            $filesystem = $this->getFilesystem();
            switch ($name) {
                case 'select_rom':
                    return $this->redirectToRoute('table', ['hash' => $hash, 'selected_rom' => $data['rom'] ?? '_']);

                case 'play':
                    $this->startVisualPinball($table_name);
                    break;

                case 'edit_table':
                    $this->startVisualPinball($table_name, 'Edit');
                    break;

                case 'export_pov':
                    if ($this->settings->isVersionControl()) {
                        $workingCopy = $this->getGitWorkingCopy($this->settings->getTablesPath(), ['*.pov', '*.vbs']);
                    }
                    $this->startVisualPinball($table_name, 'Pov');
                    if ($this->settings->isVersionControl() && $workingCopy->hasChanges()) {

                        try {
                            $workingCopy->add($table_name . '.pov');
                            $status = $workingCopy->run('status', ['-s', '-uno']);
                            if (!empty($status)) {
                                $workingCopy->commit($table_name . '.pov', ['m' => 'exported from ' . $table_name]);
                            }
                        } catch (GitException $e) {
                            $this->addFlash('danger', nl2br($e->getMessage()));
                        }
                    }
                    break;

                case 'extract_script':
                    if ($this->settings->isVersionControl()) {
                        $workingCopy = $this->getGitWorkingCopy($this->settings->getTablesPath(), ['*.pov', '*.vbs']);
                    }
                    $this->startVisualPinball($table_name, 'ExtractVBS');
                    if ($this->settings->isVersionControl() && $workingCopy->hasChanges()) {

                        try {
                            $workingCopy->add($table_name . '.vbs');
                            $status = $workingCopy->run('status', ['-s', '-uno']);
                            if (!empty($status)) {
                                $workingCopy->commit($table_name . '.vbs', ['m' => 'extracted from ' . $table_name]);
                            }
                        } catch (GitException $e) {
                            $this->addFlash('danger', nl2br($e->getMessage()));
                        }
                    }
                    return $this->redirectToRoute('table', ['hash' => $hash, 'selected_rom' => $selected_rom]);

                case 'edit_script':
                    return $this->redirectToRoute('textedit_editor', [
                        'directory' => $this->settings->getTablesPath(),
                        'file' => $table_name . '.vbs',
                        'mode' => 'ace/mode/vbscript',
                        'help' => $this->settings->isVersionControl() ? base64_encode('Script is under version control.') : null,
                        'hash' => $hash,
                        'selected_rom' => $data['rom'] ?? '_',
                    ]);

                case 'save':
                    $this->saveBackglass($table_name, $data['backglass']);

                    if (isset($pinballYGameStat)) {
                        $pinballYGameStat->trackChanges(true)
                            ->setTopperShownWhenRunning('pinbally' === $data['topper'])
                            ->setDmdShownWhenRunning('pinbally' === $data['dmd'])
                            ->setInstructionCardShownWhenRunning('pinbally' === $data['instruction']);
                        if ($pinballYGameStat->hasChanges()) {
                            $pinballYGameStats->setStat($pinballYGameStat)->persist();
                        }
                    }

                    if ($data['b2s_table_setting']->hasChanges()) {
                        $b2sTableSettings->setTableSetting($data['b2s_table_setting'])->persist();
                    }

                    /** @var VPinMameRegEntry $regEntry */
                    foreach ($data['entries'] as $regEntry) {
                        $regEntry->persist();
                    }

                    if ($pupPack) {
                        $path = $this->settings->getPinUpPacksPath() . DIRECTORY_SEPARATOR;
                        if (isset($pupPacks[$disabled_puppack]) && !empty($data['pup_pack'])) {
                            // Activate PUPPack.
                            $rom = ltrim($disabled_puppack, '_');
                            if ($filesystem->exists($path . $disabled_puppack)) {
                                try {
                                    $filesystem->rename($path . $disabled_puppack, $path . $rom, true);
                                    $this->addFlash('success', 'Renamed PUP Pack ' . $disabled_puppack . ' to ' . $rom);
                                } catch (\Exception $e) {
                                }
                            }
                        } elseif (isset($pupPacks[$roms[0]]) && empty($data['pup_pack'])) {
                            // Deactivate PUPPack.
                            if ($filesystem->exists($path . $roms[0])) {
                                try {
                                    $filesystem->rename($path . $roms[0], $path . '_' . $roms[0], true);
                                    $this->addFlash('success', 'Renamed PUP Pack' . $roms[0] . ' to _' . $roms[0]);
                                } catch (\Exception $e) {
                                }
                            }
                        } elseif ($alias && isset($pupPacks[$alias]) && empty($data['pup_pack'])) {
                            // Deactivate PUPPack.
                            if ($filesystem->exists($path .$alias)) {
                                try {
                                    $filesystem->rename($path . $alias, $path . '_' . $alias, true);
                                    $this->addFlash('success', 'Renamed PUP Pack' . $alias . ' to _' . $alias);
                                } catch (\Exception $e) {
                                }
                            }
                        }
                    }

                    break;
            }
        }

        return $this->render('/tables/table.html.twig', [
            'table_form' => $form->createView(),
            'wheel_image' => isset($pinballYMedia) ? base64_encode($pinballYMedia->getWheelImage()) : null,
            'backglass_image' => isset($pinballYMedia) ? base64_encode($pinballYMedia->getBackglassImage()) : null,
            'dmd_image' => isset($pinballYMedia) ? base64_encode($pinballYMedia->getDmdImage()) : null,
            'topper_image' => isset($pinballYMedia) ? base64_encode($pinballYMedia->getTopperImage()) : null,
            'table_image' => isset($pinballYMedia) ? base64_encode($pinballYMedia->getTableImage()) : null,
            'instruction_image' => isset($pinballYMedia) ? base64_encode($pinballYMedia->getInstructionCardImage()) : null,
            'roms' => $roms,
            'romfiles' => $this->settings->getRoms(),
            'altcolor' => $this->settings->getAltcolorRoms(),
            'altsound' => $this->settings->getAltsoundRoms(),
        ]);
    }

    protected function saveBackglass($table, $backglass_file) {
        $filesystem = $this->getFilesystem();
        $table_path = $this->settings->getTablesPath() . DIRECTORY_SEPARATOR;
        $target_backglass_file = $table . '.directb2s';
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
                    if (strpos($backglass_file, '_') !== 0 && $filesystem->exists($table_path . $filesystem->exists($table_path . str_replace('.directb2s', '.vpx', $target_backglass_file)))) {
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
                            break(1);
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

    protected function startVisualPinball(string $table, string $command = 'Play') {
        $table_argument = escapeshellarg($this->settings->getTablesPath() . DIRECTORY_SEPARATOR . $table . '.vpx');
        $command = $this->settings->getVisualPinballExe() . ' -' . $command . ' ' . $table_argument;
        ob_start();
        $stdout = $command . "\r\n" . passthru($command);
        $errout = ob_get_clean();
        if ($stdout) {
            $this->addFlash($errout ? 'message' : 'success', nl2br($stdout));
        }
        if ($errout) {
            $this->addFlash('warning', nl2br($errout));
        }
    }
}
