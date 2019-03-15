<?php

namespace App\Controller;

use App\Entity\DofConfigtoolDownload;
use App\Entity\Tweaks;
use GorHill\FineDiff\FineDiff;
use iphis\FineDiff\Diff;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class TweakController extends AbstractController
{
    /**
     * @Route("/tweak", name="tweak")
     */
    public function index(Request $request)
    {
        $form = $this->createFormBuilder()
            ->add('settings', SubmitType::class, ['label' => 'Edit tweak settings'])
            ->add('tweak', SubmitType::class, ['label' => 'Tweak DOF configuration files'])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var \Symfony\Component\Form\Form $form */
            $name = $form->getClickedButton()->getConfig()->getName();
            switch ($name) {
                case 'settings':
                    return $this->redirectToRoute('tweak_settings');

                case 'tweak':
                    return $this->redirectToRoute('tweak_confirm');
            }
        }

        return $this->render('tweak/index.html.twig', [
            'tweak_form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/tweak/settings", name="tweak_settings")
     */
    public function settings(Request $request)
    {
        $tweaks = new Tweaks();
        $tweaks->load();

        $form = $this->createFormBuilder($tweaks)
            ->add('settings', TextareaType::class, ['label' => 'Settings'])
            ->add('save', SubmitType::class, ['label' => 'Save settings'])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Tweaks $tweaks */
            $tweaks = $form->getData();

            try {
                $tweaks->persist();
                $this->addFlash('success', 'Saved settings to tweaks.ini.');
                return $this->redirectToRoute('tweak');
            } catch (\Exception $e) {
                $this->addFlash('warning', $e->getMessage());
            }
        }

        return $this->render('tweak/settings.html.twig', [
            'settings_form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/tweak/confirm", name="tweak_confirm")
     */
    public function confirm(Request $request)
    {
        ini_set('set_time_limit', 0);

        $dofConfigtoolDownload = new DofConfigtoolDownload();
        $dofConfigtoolDownload->load();

        $tweaks = new Tweaks();
        $tweaks->load();

        $mods = [];
        $modded_files = [];
        $files = [];

        $file = '';
        foreach ($tweaks->getSettingsParsed() as $section => $adjustments) {
            if (strpos($section, '.ini')) {
                $file = $dofConfigtoolDownload->getDofConfigPath() . DIRECTORY_SEPARATOR . $section;
                if (file_exists($file)) {
                    $mods[$file] = [0 => $adjustments];
                } else {
                    $file = '';
                }
            } elseif ($file) {
                $mods[$file][$section] = $adjustments;
            }
        }

        foreach ($mods as $file => $per_game_mods) {
            if ($contents = file_get_contents($file)) {
                $files[$file] = $contents;
                list($head, $config) = explode('[Config DOF]', $contents);
                foreach (explode("\r\n", $config) as $game_row) {
                    if ($game_row = trim($game_row)) {
                        $game = str_getcsv($game_row);
                        foreach ($per_game_mods as $game_name => $adjustments) {
                            foreach ($adjustments as $name => $settings) {
                                foreach ($settings as $port => $setting) {
                                    // Skip global setting when a game-specific setting exists.
                                    if ($game_name === $game[0] || (!$game_name && (!isset($per_game_mods[$game[0]]) || !isset($per_game_mods[$game[0]][$name]) || !isset($per_game_mods[$game[0]][$name][$port])))) {
                                        if (isset($game[$port])) {

                                            switch ($name) {

                                                case 'default_effect_duration':
                                                    $triggers = explode('/', $game[$port]);
                                                    foreach ($triggers as &$trigger) {
                                                        $trigger = preg_replace('/([SWE]\d+$)/', '$1 ' . $setting, $trigger);
                                                    }
                                                    $new = implode('/', $triggers);
                                                    if ($new != $game[$port]) {
                                                        $game[$port] = $new;
                                                    }
                                                    break;

                                                case 'turn_off':
                                                    $game_names = explode(',', $setting);
                                                    array_walk($game_names, 'trim');
                                                    if (in_array($game[0], $game_names) && 0 != $game[$port]) {
                                                        $game[$port] = 0;
                                                    }
                                                    break;

                                                case 'turn_on':
                                                    $game_names = explode(',', $setting);
                                                    array_walk($game_names, 'trim');
                                                    if (!in_array($game[0], $game_names) && 0 != $game[$port]) {
                                                        $game[$port] = 0;
                                                    }
                                                    break;

                                                case 'adjust_intensity':
                                                    $triggers = explode('/', $game[$port]);
                                                    foreach ($triggers as &$trigger) {
                                                        if (preg_match('/[I](\d+)/', $trigger, $matches)) {
                                                            $intensity = (int)(((int)$matches[1]) * ((float)$setting));
                                                            if ($intensity < 1) {
                                                                $intensity = 1;
                                                            }
                                                            if ($intensity > 48) {
                                                                $intensity = 48;
                                                            }
                                                            $trigger = preg_replace('/[I]\d+/', 'I' . $intensity, $trigger);
                                                        }
                                                    }
                                                    $new = implode('/', $triggers);
                                                    if ($new != $game[$port]) {
                                                        $game[$port] = $new;
                                                    }
                                                    break;
                                            }

                                        }
                                    }
                                }
                            }
                        }
                        $games[] = implode(',', $game);
                    } else {
                        $games[] = '';
                    }
                }
                $modded_files[$file] = $head . '[Config DOF]' . implode("\r\n", $games);
            }
        }

        $diff = new Diff();
        $diffs = [];
        foreach ($modded_files as $file => $content) {
            $old_lines = explode("\r\n", $files[$file]);
            $new_lines = explode("\r\n", $content);
            foreach($old_lines as $number => $line) {
                if ($line != $new_lines[$number]) {
                    $diff_cells = explode(',', $diff->render($line, $new_lines[$number]));
                    $header = '';
                    $data = '';
                    foreach ($diff_cells as $port => $dof_string) {
                        $dof_string = str_replace('<ins>', '<ins class="bg-success">', $dof_string);
                        $dof_string = str_replace('<del>', '<del class="bg-danger">', $dof_string);
                        $header .= '<th scope="col">' . ($port ?: '') . '</th>';
                        if ($port) {
                            $data .= '<td>' . $dof_string . '</td>';
                        } else {
                            $data .= '<th scope="row">' . $dof_string . '</th>';
                        }
                    }
                    $diffs[basename($file)][] = '<tr>' . $header . '</tr><tr>' . $data . '</tr>';
                }
            }
        }

        $form = $this->createFormBuilder()
            ->add('cancel', SubmitType::class, ['label' => 'Cancel'])
            ->add('save', SubmitType::class, ['label' => 'Save'])
            ->add('files', HiddenType::class, ['data' => base64_encode(serialize($modded_files))])
            ->setAction($this->generateUrl('tweak_do'))
            ->getForm();

        return $this->render('tweak/confirm.html.twig', [
            'confirm_form' => $form->createView(),
            'diffs' => $diffs,
        ]);
    }

    /**
     * @Route("/tweak/do", name="tweak_do")
     */
    public function tweak(Request $request)
    {
        $tweaks = new Tweaks();
        $tweaks->load();

        $form = $this->createFormBuilder()
            ->add('cancel', TextareaType::class, ['label' => 'Cancel'])
            ->add('save', SubmitType::class, ['label' => 'Save'])
            ->add('files', HiddenType::class, ['value' => '']);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Tweaks $tweaks */
            $tweaks = $form->getData();

            try {
                $tweaks->persist();
                $this->addFlash('success', 'Saved settings to tweaks.ini.');
                return $this->redirectToRoute('tweak');
            } catch (\Exception $e) {
                $this->addFlash('warning', $e->getMessage());
            }
        }

        return $this->redirectToRoute('tweak');
    }

}
