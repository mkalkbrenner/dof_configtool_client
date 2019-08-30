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
use Symfony\Component\Filesystem\Filesystem;
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
     * @Route("/table/{hash}", name="table")
     */
    public function table(Request $request, string $hash)
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
            $vPinMameRegEntry = new VPinMameRegEntry();
            $vPinMameRegEntry->setRom($rom)->setTable($tableMapping[$rom] ?? '')->load();
            $vPinMameRegEntries->addEntry($vPinMameRegEntry);
        }
        $b2sTableSettings = new B2STableSettings();
        $b2sTableSettings->setRoms($roms)->setPath($this->settings->getTablesPath())->load();

        $screenRes = new ScreenRes();
        $screenRes->setPath($this->settings->getTablesPath())->load();

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
            ->add('rom', ChoiceType::class, [
                'choices' => $rom_choices,
                'data' => null ?? '', // @todo
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
            ->add('topper', ChoiceType::class, [
                'choices' => ['PinballY' => 'pinbally', 'PUP or none' => 'pup'],
                'data' => null ?? '_', // @todo
                'label' => false,
            ])
            ->add('backglass', ChoiceType::class, [
                'choices' => Utility::getGroupedBackglassChoices($backglassChoices, $tables[$hash]),
                'data' => $backglassChoices[$tables[$hash]] ?? '_',
                'label' => false,
            ])
            ->add('dmd', ChoiceType::class, [
                'choices' => ['VPinMame external (freezy)' => 'external', 'VPinMame' => 'internal', 'B2S' => 'b2s', 'PinballY' => 'pinbally', 'PUP or none' => 'pup'],
                'data' => null ?? '_', // @todo
                'label' => false,
            ])
            ->add('instruction', ChoiceType::class, [
                'choices' => ['PinballY' => 'pinbally', 'PUP or none' => 'pup'],
                'data' => null ?? '_', // @todo
                'label' => false,
            ])
            ->add('entries', CollectionType::class, [
                'entry_type' => VPinMameRegEntryType::class,
                'data' => $vPinMameRegEntries->getEntries(),
                'label' => false,
            ])
            ->add('save', SubmitType::class, ['label' => 'Save']);

        if (count($roms) === 1) {
            $formBuilder->add('b2s_table_setting', B2STableSettingType::class, [
                'data' => $b2sTableSettings->getTableSetting($roms[0]),
                'label' => false,
            ]);
        } else {
            $formBuilder->add('b2s_table_setting', B2STableSettingDisabledType::class, [
                'data' => new B2STableSetting('default'),
                'label' => false,
            ]);
        }

        $form = $formBuilder->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $filesystem = new Filesystem();
            $table_path = $this->settings->getTablesPath() . DIRECTORY_SEPARATOR;
            $data = $form->getData();
            $count = array_count_values($data);
            foreach ($data as $key => $backglass_file) {
                $target_backglass_file = $tables[$key] . '.directb2s';
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
                            if ($count[$backglass_file] > 1) {
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
                                    break(2);
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
            // We need to redirect to force a form refresh.
            return $this->redirectToRoute('backglasses');
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
}
