<?php

namespace App\Controller;

use App\Component\Utility;
use App\Entity\B2STableSetting;
use App\Entity\B2STableSettings;
use App\Entity\DirectOutputConfig;
use App\Entity\DmdDevice;
use App\Entity\PinballYGameStats;
use App\Entity\PinballYMedia;
use App\Entity\PinballYMenu;
use App\Entity\ScreenRes;
use App\Entity\VPinMameRegEntries;
use App\Entity\VPinMameRegEntry;
use App\Form\Type\B2STableSettingDisabledType;
use App\Form\Type\B2STableSettingType;
use App\Form\Type\VPinMameRegEntryType;
use App\TweaksTrait;
use GitWrapper\GitException;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Windows\Registry\KeyNotFoundException;

class TablesController extends AbstractSettingsController
{
    use TweaksTrait;

    /**
     * @Route("/tables", name="tables")
     */
    public function index(Request $request)
    {
        $pinballYMenu = null;
        if ($pinballYDatabaseFile = $this->settings->getPinballYVPXDatabaseFile()) {
            $pinballYMenu = new PinballYMenu();
            $pinballYMenu->setFile($pinballYDatabaseFile)->load();
        }

        list($tables, $backglassChoices) = Utility::getExistingTablesAndBackglassChoices($this->settings);
        asort($tables, SORT_NATURAL | SORT_FLAG_CASE);

        $old_tables = [];
        $new_tables = [];

        if ($pinballYMenu) {
            foreach ($tables as $hash => $table) {
                if ($pinballYMenuEntry = $pinballYMenu->getMenuEntry($table)) {
                    $old_tables[$hash] = $table;
                } else {
                    $new_tables[$hash] = $table;
                }
            }
        } else {
            $old_tables = $tables;
        }

        return $this->render('tables/index.html.twig', [
            'old_tables' => $old_tables,
            'new_tables' => $new_tables,
            'pinbally' => is_object($pinballYMenu),
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
        $fastflips =
            null;
        $description =
        $manufacturer =
        $year =
        $ipdbid =
            'PinballY required!';
        $topper =
        $dmd =
        $instcard =
            'pup';
        $script = $this->settings->getTablesPath() . DIRECTORY_SEPARATOR . $table_name . '.vbs';

        $script_extracted = file_exists($script);
        $configured_tables = [];

        if ($script_extracted) {
            // Don't use file_get_contents() because preg has issues with detecting the beginning of a line with some table scripts.
            if ($handle = fopen($script, "r")) {
                while (($line = fgets($handle)) !== false) {
                    if (preg_match('/^[^\']*\bcGameName\s*=\s*[\'"]([^\'"]+)[\'"]/i', $line, $matches)) {
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
                    if (preg_match('/^[^\']*\bInitVpmFFlipsSAM/i', $line)) {
                        $fastflips = 'InitVpmFFlipsSAM';
                    }
                    if (!$fastflips && preg_match('/^[^\']*\bUseSolenoids\s*=\s*2/i', $line)) {
                       $fastflips = 'UseSolenoids = 2';
                    }
                    if ($roms && $fastflips) {
                        break;
                    }
                }
                fclose($handle);
            }
        } else {
            $fastflips = 'Unable to detect, extract script first!';
            $this->addFlash('warning', 'The table script is not extracted. Detections like ROMs or Fast Flips are disabled or use weaker fallbacks. Click "Extract Script".');
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
                $ipdbid = '<a href="https://www.ipdb.org/machine.cgi?id=' . $pinballYMenuEntry->getIpdbid() . '" target="_blank">IPD No. ' . $pinballYMenuEntry->getIpdbid() . '</a>' ?? 'unknown';
                $roms = $roms ?: Utility::getRomsForTable($description, $this->settings);
            } elseif ($configured_tables = $pinballYMenu->getTables()) {
                asort($configured_tables, SORT_NATURAL);
                $lower_table_name = mb_strtolower(substr($table_name, 0, 10));
                $candidates = [];
                foreach ($configured_tables as $configured_table_name) {
                    $distance = levenshtein(mb_strtolower(substr($configured_table_name, 0, 10)), $lower_table_name);
                    if ($distance <= 2) {
                        $candidates[$configured_table_name] = $distance;
                    } else {
                        $menuEntry = $pinballYMenu->getMenuEntry($configured_table_name);
                        $distance = levenshtein(mb_strtolower(substr($menuEntry->getDescription(), 0, 10)), $lower_table_name);
                        if ($distance <= 2) {
                            $candidates[$configured_table_name] = $distance;
                        }
                    }
                }

                $configured_tables = array_combine($configured_tables, $configured_tables);
                if ($candidates) {
                    asort($candidates, SORT_NUMERIC);
                    $configured_tables = [
                        'Suggested' => array_combine(array_keys($candidates), array_keys($candidates)),
                        'All' => $configured_tables
                    ];
                }
            }
        } elseif (!$roms) {
            $this->addFlash('danger', 'This "all in one" page uses PinballY\'s database to detect the ROM candidates. Alternatively you can extract the table script and the ROM would be looked up in it.');
        }

        $tableMapping = $this->settings->getTableMapping();
        $rom_choices = [];
        $vPinMameRegEntries = new VPinMameRegEntries();
        foreach ($roms as $rom) {
            if (isset($tableMapping[$rom])) {
                $rom_choices[$rom . ' >>> DOF: ' .  $tableMapping[$rom]] = $rom;
            }
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

        $dmdDevice = new DmdDevice();
        $dmdDeviceSettings = $dmdDevice->setPath($this->settings->getVPinMamePath())->trackChanges()->load()->getSettingsParsed();

        $pupPacks = Utility::getExistingPupPacks($this->settings, $roms);

        $formBuilder = $this->createFormBuilder()
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
            ->add('fastflips', TextType::class, [
                'disabled' => true,
                'data' => $fastflips ?? 'no',
                'label' => false,
            ])
            ->add('entries', CollectionType::class, [
                'entry_type' => VPinMameRegEntryType::class,
                'data' => $vPinMameRegEntries->getEntries(),
                'label' => false,
            ])
            ->add('save', SubmitType::class, ['label' => 'Save']);

        if ($configured_tables) {
            $formBuilder
                ->add('table_name', ChoiceType::class, [
                    'choices' => array_merge(["Don't copy anything" => '_'], $configured_tables),
                    'label' => false,
                    'help' => 'The table is not yet configured in your frontend. But maybe it is just a new version of an existing one? You can copy an existing config here.',
                ])
                ->add('copy_pov', CheckboxType::class, [
                    'data' => false,
                    'label' => 'Copy POV',
                    'required' => false,
                ])
                ->add('copy_backglass', CheckboxType::class, [
                    'data' => false,
                    'label' => 'Copy Backglass if exists',
                    'required' => false,
                ]);
        } else {
            $formBuilder->add('table_name', TextType::class, [
                'disabled' => true,
                'data' => $description,
                'label' => false,
            ]);
        }

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
                ])
                ->add('dmddevice_virtualdmd', CheckboxType::class, [
                    'data' => $dmdDevice->isEnabled($alias ?? $roms[0], 'virtualdmd'),
                    'label' => false,
                    'help' => 'Has no effect unless "showpindmd" is activated in the corresponding VPinMAME registry entry!',
                    'required' => false,
                    'disabled' => true,
                ])
                ->add('dmddevice_alphanumeric', CheckboxType::class, [
                    'data' => $dmdDevice->isEnabled($alias ?? $roms[0], 'alphanumeric'),
                    'label' => false,
                    'help' => 'Has no effect unless "showpindmd" is activated in the corresponding VPinMAME registry entry!',
                    'required' => false,
                    'disabled' => true,
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
            $formBuilder->add('compare_script', SubmitType::class, ['label' => 'Compare Script']);
        }

        $dofTable = [];
        if (count($roms) === 1) {
            $daySettingsParsed = $this->getTweaks()->getDaySettingsParsed();
            $dofTable = $this->getDofTableRows($alias ?? $roms[0], $formBuilder, $daySettingsParsed);
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
                    $this->extractPOV($table_name);
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

                case 'compare_script':
                    return $this->redirectToRoute('textedit_select_diff', [
                        'directory' => $this->settings->getTablesPath(),
                        'file' => $table_name . '.vbs',
                        'mode' => 'ace/mode/vbscript',
                        'help' => $this->settings->isVersionControl() ? base64_encode('Script is under version control.') : null,
                        'hash' => $hash,
                        'selected_rom' => $data['rom'] ?? '_',
                    ]);

                case 'save':
                    if ($configured_tables && '_' !== $data['table_name']) {
                        if ($existingPinballYMenuEntry = $pinballYMenu->getMenuEntry($data['table_name'])) {
                            $newPinballYMenuEntry = clone $existingPinballYMenuEntry;
                            $newPinballYMenuEntry->setName($table_name);
                            $pinballYMenu->addMenuEntry($newPinballYMenuEntry)->persist();
                            $this->addFlash('success', 'Copied PinballY menu entry from ' . $data['table_name'] . ' to ' . $table_name);
                        }

                        if (!empty($data['copy_pov'])) {
                            $filesystem = $this->getFilesystem();
                            $table_path = $this->settings->getTablesPath() . DIRECTORY_SEPARATOR;
                            $pov = $data['table_name'] . '.pov';
                            if (!$filesystem->exists($table_path . $pov)) {
                                $this->extractPOV($data['table_name']);
                            }
                            if ($filesystem->exists($table_path . $pov)) {
                                $target_pov = $table_name . '.pov';
                                try {
                                    $filesystem->copy($table_path . $pov, $table_path . $target_pov);
                                    $this->addFlash('success', 'Copied ' . $pov . ' to ' . $target_pov);
                                } catch (\Exception $e) {
                                }
                            }
                        }

                        if (!empty($data['copy_backglass']) && !empty($backglassChoices[$data['table_name']])) {
                            $data['backglass'] = $backglassChoices[$data['table_name']];
                        }
                    }

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

                    $dof_overwrites = [];
                    $save_dof = false;
                    foreach ($data as $dof_key => $dof_value) {
                        if (strpos($dof_key, 'dof_') === 0) {
                            list (, $dof_deviceId, $dof_rom, $dof_port) = explode('_', $dof_key);
                            $dof_value = trim($dof_value);
                            if (strlen($dof_value) > 0) {
                                $dof_overwrites['directoutputconfig' . $dof_deviceId . '.ini']['section:' . $dof_rom]['string_overwrite'][$dof_port] = $dof_value;
                            }
                        }
                    }
                    foreach ($dof_overwrites as $file => $sections) {
                        foreach ($sections as $section => $tweaks) {
                            $stored_string_overwrites = $daySettingsParsed[$file][$section]['string_overwrite'] ?? [];
                            if (array_diff_assoc($tweaks['string_overwrite'], $stored_string_overwrites) || array_diff_assoc($stored_string_overwrites, $tweaks['string_overwrite'])) {
                                $daySettingsParsed[$file][$section]['string_overwrite'] = $tweaks['string_overwrite'];
                                $save_dof = true;
                            }
                        }
                    }
                    if ($save_dof) {
                        $tweaks = $this->getTweaks();
                        $tweaks->setDaySettingsParsed($daySettingsParsed);
                        try {
                            $tweaks->persist();
                            if ($this->settings->isVersionControl()) {
                                $workingCopy = $this->getGitWorkingCopy($tweaks->getDirectory());
                                if ($workingCopy->hasChanges()) {
                                    $workingCopy->add('*.ini');
                                    $workingCopy->commit('Saved string_overwrites for ' . $selected_rom);
                                }
                            }
                            return $this->redirectToRoute('tweak_confirm', ['cycle' => 'day', 'hash' => $hash, 'selected_rom' => $selected_rom]);
                        } catch (\Exception $e) {
                            $this->addFlash('warning', $e->getMessage());
                        }
                    }

                    // The form needs to be rebuilt!
                    return $this->redirectToRoute('table', ['hash' => $hash, 'selected_rom' => $selected_rom]);
            }
        }

        if ($this->settings->isVersionControl()) {
            try {
                $workingCopy = $this->getGitWorkingCopy($this->settings->getDofConfigPath());
                $branch = $this->getCurrentBranch($workingCopy);
            } catch (GitException $e) {
                $this->addFlash('warning', $e->getMessage());
            }
        }

        $instruction_image = isset($pinballYMedia) ? base64_encode($pinballYMedia->getInstructionCardImage()) : null;
        $pinballrebel_manufacturer = '';
        if ($manufacturer) {
            switch ($manufacturer) {
                case'Data East':
                case 'Game Plan':
                case'Spooky Pinball':
                    $pinballrebel_manufacturer = str_replace(' ', '_', $manufacturer);
                    break;

                case 'Bally':
                case 'Midway':
                    $pinballrebel_manufacturer = 'bally';
                    break;

                case 'Alvin G':
                case 'Bell Games':
                case 'Chicago Coin':
                    $pinballrebel_manufacturer = str_replace(' ', '_', strtolower($manufacturer));
                    break;

                case 'Atari':
                case 'Capcom':
                    $pinballrebel_manufacturer = strtolower($manufacturer);
                    break;

                default:
                    $pinballrebel_manufacturer = $manufacturer;
            }
        }

        return $this->render('/tables/table.html.twig', [
            'table_form' => $form->createView(),
            'wheel_image' => isset($pinballYMedia) ? base64_encode($pinballYMedia->getWheelImage()) : null,
            'backglass_image' => isset($pinballYMedia) ? base64_encode($pinballYMedia->getBackglassImage()) : null,
            'dmd_image' => isset($pinballYMedia) ? base64_encode($pinballYMedia->getDmdImage()) : null,
            'topper_image' => isset($pinballYMedia) ? base64_encode($pinballYMedia->getTopperImage()) : null,
            'table_image' => isset($pinballYMedia) ? base64_encode($pinballYMedia->getTableImage()) : null,
            'instruction_image' => $instruction_image,
            'roms' => $roms,
            'romfiles' => $this->settings->getRoms(),
            'altcolor' => $this->settings->getAltcolorRoms(),
            'altsound' => $this->settings->getAltsoundRoms(),
            'dof_rows' => $dofTable,
            'cycle' => $branch ?? 'download',
            'ipdbid' => $ipdbid,
            'description' => $description ?? null,
            'pinballrebel_manufacturer' => urlencode($pinballrebel_manufacturer),
        ]);
    }

    protected function extractPOV(string $table_name) {
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
                    if (strpos($backglass_file, '_') !== 0 && $filesystem->exists($table_path . $backglass_file) && $filesystem->exists($table_path . str_replace('.directb2s', '.vpx', $target_backglass_file))) {
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

    protected function getDofTableRows(string $rom, FormBuilderInterface $formBuilder, ?array $daySettingsParsed): array
    {
        $rows = [];
        $files = [];
        if ($dof_config_path = $this->settings->getDofConfigPath()) {
            foreach (scandir($dof_config_path) as $file) {
                if (preg_match('/^directoutputconfig(\d+)\.ini$/i', $file, $matches)) {
                    $file_path = $this->settings->getDofConfigPath() . DIRECTORY_SEPARATOR . $matches[0];
                    if (!isset($mods[$file_path])) {
                        $files[$matches[1]] = $file_path;
                    }
                }
            }
        }

        $portAssignments = $this->settings->getPortAssignments();

        if ($this->settings->isVersionControl()) {
            $workingCopy = $this->getGitWorkingCopy($dof_config_path);
        }
        foreach ($files as $deviceId => $file) {
            $directOutputConfig = new DirectOutputConfig($file);
            $games_day = $games_night = [];
            if (isset($workingCopy)) {
                $basename = basename($file);
                $directOutputConfig->load($workingCopy->show('download:' . $basename));
                if ($contents = $workingCopy->show('day:' . $basename)) {
                    $directOutputConfigDay = new DirectOutputConfig($file);
                    $games_day = $directOutputConfigDay->load($contents)->getGames();
                }
                if ($contents = $workingCopy->show('night:' . $basename)) {
                    $directOutputConfigNight = new DirectOutputConfig($file);
                    $games_night = $directOutputConfigNight->load($contents)->getGames();
                }
            }
            else {
                $directOutputConfig->load();
            }
            $rgb_ports = $directOutputConfig->getRgbPorts();
            $games = $directOutputConfig->getGames();
            while ($rom && !isset($games[$rom])) {
                $rom = substr($rom, 0, -1);
            }

            $colspan = ((int) !empty($games_day)) + ((int) !empty($games_night)) + ((int) (!empty($games_day) || !empty($games_night)));
            if ($rom && !empty($games[$rom])) {
                $basename = basename($file);
                $rows[] = '<tr><th scope="col" colspan="' . (3 + $colspan) . '" bgcolor="#6495ed">' . $directOutputConfig->getDeviceName() . ': ' . $basename . '</th>';

                foreach ($games[$rom] as $port => $dof_string) {
                    $row = '';
                    $key = 'dof_' . $deviceId . '_' . $rom . '_' . $port;
                    if (!in_array($port,$rgb_ports[$rom] ?? [])) {
                        $row .= '<th scope="row" bgcolor="' . (in_array($port + 1, $rgb_ports[$rom] ?? []) ? 'red' : 'white') . '">' . ($port ?: 'Port') . '</th>';
                        if ($port) {
                            $row .= '<th  scope="row"' . (in_array($port + 1, $rgb_ports[$rom] ?? []) ? ' rowspan="3"' : '') . '>' . ($portAssignments[$deviceId][$port] ?? '') . '</th>';
                            $row .= '<td' . (in_array($port + 1, $rgb_ports[$rom] ?? []) ? ' rowspan="3"' : '') . '>' . $dof_string . '</td>';
                            if (isset($games_day[$rom][$port])) {
                                $row .= '<td' . (in_array($port + 1, $rgb_ports[$rom] ?? []) ? ' rowspan="3"' : '') . '>' . ($dof_string === $games_day[$rom][$port] ? '<i>identical to download</i>' : '<b>' . $games_day[$rom][$port] . '</b>') . '</td>';
                            }
                            if (isset($games_night[$rom][$port])) {
                                $row .= '<td' . (in_array($port + 1, $rgb_ports[$rom] ?? []) ? ' rowspan="3"' : '') . '>' . ($dof_string === $games_night[$rom][$port] ? '<i>identical to download</i>' : '<b>' . $games_night[$rom][$port] . '</b>') . '</td>';
                            }
                            $row .= '<td' . (in_array($port + 1, $rgb_ports[$rom] ?? []) ? ' rowspan="3"' : '') . '>';
                            $formBuilder->add($key, TextType::class, [
                                'label' => false,
                                'data' => $daySettingsParsed['directoutputconfig' . $deviceId . '.ini']['section:' . $rom]['string_overwrite'][$port] ?? '',
                                'required' => false,
                            ]);
                        } else {
                            $row .= '<th scope="row">Description</th>';
                            $row .= '<th scope="row">' . $dof_string . ' (download)</th>';
                            if (isset($games_day[$rom][$port])) {
                                $row .= '<th scope="row">' . $games_day[$rom][$port] . ' (day)</th>';
                            }
                            if (isset($games_night[$rom][$port])) {
                                $row .= '<th scope="row">' . $games_night[$rom][$port] . ' (night)</th>';
                            }
                            $row .= '<th scope="row" width="25%">overwrite (only for day mode)</th>';
                        }
                    } else {
                        $row .= '<th scope="row" bgcolor="' . (in_array($port + 1, $rgb_ports[$rom]) ? 'green' : 'blue') . '">' . $port . '</th>';
                    }

                    $rows[$key] = $row;
                }
            }
        }
        return $rows;
    }

}
