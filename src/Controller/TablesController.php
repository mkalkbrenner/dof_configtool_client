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
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class TablesController extends AbstractSettingsController
{
    /**
     * @Route("/tables", name="tables")
     */
    public function index(Request $request)
    {
        list($tables, $backglassChoices) = Utility::getExistingTablesAndBackglassChoices($this->settings);

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

        if ($this->settings->getPinballYPath()) {
            $pinballYDatabaseFile = $this->settings->getPinballYVPXDatabaseFile();
            $pinballYMenu = new PinballYMenu();
            $pinballYMenu->setFile($pinballYDatabaseFile)->load();
            if ($pinballYMenuEntry = $pinballYMenu->getMenuEntry($table_name)) {
                $pinballYGameStats = new PinballYGameStats();
                $pinballYGameStats->setFile($this->settings->getPinballYPath() . DIRECTORY_SEPARATOR . 'GameStats.csv')->load();
                if ($pinballYGameStat = $pinballYGameStats->getStat($pinballYMenuEntry->getDescription())) {
                    $added = $pinballYGameStat->getDateAddedDateTime();
                    $last_played = $pinballYGameStat->getLastPlayedDateTime();
                }
                $pinballYMedia = new PinballYMedia($pinballYMenuEntry->getDescription());
                $pinballYMedia->setPath($this->settings->getPinballYPath())->load();
            }
        }

        $roms = Utility::getRomsForTable($pinballYMenuEntry->getDescription(), $this->settings);
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
            $vPinMameRegEntry->setRom($rom)->setTable($tableMapping[$rom] ?? '')->load();
            $vPinMameRegEntries->addEntry($vPinMameRegEntry);
        }

        $b2sTableSettings = new B2STableSettings();
        $b2sTableSettings->setRoms($roms)->setPath($this->settings->getTablesPath())->load(true);

        $screenRes = new ScreenRes();
        $screenRes->setPath($this->settings->getTablesPath())->load();

        $pupPacks = Utility::getExistingPupPacks($this->settings, $roms);

        $formBuilder = $this->createFormBuilder()
            ->add('table_name', TextType::class, [
                'disabled' => true,
                'data' => $pinballYMenuEntry->getDescription(),
                'label' => false,
            ])
            ->add('manufacturer', TextType::class, [
                'disabled' => true,
                'data' => $pinballYMenuEntry->getManufacturer(),
                'label' => false,
            ])
            ->add('year', TextType::class, [
                'disabled' => true,
                'data' => $pinballYMenuEntry->getYear(),
                'label' => false,
            ])
            ->add('table_file', TextType::class, [
                'disabled' => true,
                'data' => $table_name . '.vpx',
                'label' => false,
            ])
            ->add('added', TextType::class, [
                'disabled' => true,
                'data' => $added ? $added->format('Y-m-d H:i:s') : 'unknown',
                'label' => false,
            ])
            ->add('last_played', TextType::class, [
                'disabled' => true,
                'data' => $last_played ? $last_played->format('Y-m-d H:i:s') : 'never',
                'label' => false,
            ])
            ->add('entries', CollectionType::class, [
                'entry_type' => VPinMameRegEntryType::class,
                'data' => $vPinMameRegEntries->getEntries(),
                'label' => false,
            ])
            ->add('select_rom', SubmitType::class, ['label' => 'Select ROM'])
            ->add('save', SubmitType::class, ['label' => 'Save']);

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
                    'data' => null ?? '_', // @todo
                    'label' => false,
                ])
                ->add('backglass', ChoiceType::class, [
                    'choices' => Utility::getGroupedBackglassChoices($backglassChoices, $tables[$hash]),
                    'data' => $backglassChoices[$tables[$hash]] ?? '_',
                    'label' => false,
                ])
                ->add('dmd', ChoiceType::class, [
                    'choices' => ['VPinMame external (freezy)' => 'external', 'VPinMame' => 'internal', 'B2S' => 'b2s', 'PinballY' => 'pinbally', 'None or PUP Pack' => 'pup'],
                    'data' => null ?? '_', // @todo
                    'label' => false,
                ])
                ->add('instruction', ChoiceType::class, [
                    'choices' => ['PinballY' => 'pinbally', 'None or PUP Pack' => 'pup'],
                    'data' => null ?? '_', // @todo
                    'label' => false,
                ])
                ->add('b2s_table_setting', B2STableSettingType::class, [
                    'data' => $b2sTableSettings->getTableSetting($roms[0]),
                    'label' => false,
                ]);
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

                case 'save':
                    $this->saveBackglass($table_name, $data['backglass']);

                    /** @var VPinMameRegEntry $regEntry */
                    foreach ($data['entries'] as $regEntry) {
                        $regEntry->persist();
                    }

                    if ($pupPack) {
                        $path = $this->settings->getPinUpPacksPath() . DIRECTORY_SEPARATOR;
                        if (isset($pupPacks[$disabled_puppack]) && !empty($data['pup_pack'])) {
                            // Activate PUPPack.
                            if ($filesystem->exists($path . '_' . $roms[0])) {
                                try {
                                    $filesystem->rename($path . '_' . $roms[0], $path . $roms[0], true);
                                    $this->addFlash('success', 'Renamed PUP Pack _' . $roms[0] . ' to ' . $roms[0]);
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
                        }
                    }

                    break;
            }
        }

        return $this->render('/tables/table.html.twig', [
            'table_form' => $form->createView(),
            'wheel_image' => urlencode($pinballYMedia->getWheelImage()),
            'backglass_image' => urlencode($pinballYMedia->getBackglassImage()),
            'dmd_image' => urlencode($pinballYMedia->getDmdImage()),
            'topper_image' => urlencode($pinballYMedia->getTopperImage()),
            'table_image' => urlencode($pinballYMedia->getTableImage()),
            'instruction_image' => urlencode($pinballYMedia->getInstructionCardImage()),
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
}
