<?php

namespace App\Controller;

use App\Entity\Tweaks;
use iphis\FineDiff\Diff;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class TweakController extends AbstractSettingsController
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
            ->add('settings', TextareaType::class, ['label' => 'Settings', 'attr' => ['rows' => 20]])
            ->add('save', SubmitType::class, ['label' => 'Save settings'])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Tweaks $tweaks */
            $tweaks = $form->getData();

            try {
                $tweaks->persist();
                $this->addFlash('success', 'Saved settings to '.$tweaks->getIni().'.');
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

        $tweaks = new Tweaks();
        $tweaks->load();

        $mods = [];
        $modded_files = [];
        $files = [];
        $rgb_ports = [];
        $colors = [];

        $file = '';
        foreach ($tweaks->getSettingsParsed() as $section => $adjustments) {
            if (strpos($section, '.ini')) {
                $file = $this->settings->getDofConfigPath() . DIRECTORY_SEPARATOR . $section;
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
                // Normalize line endings.
                $contents = preg_replace('/\R/', "\r\n", $contents);
                $files[$file] = $contents;
                list($head, $config) = explode('[Config DOF]', $contents);
                $colors[$file] = [];
                $color_section = FALSE;
                foreach (explode("\r\n", $head) as $line) {
                    if (strpos($line, '[Colors DOF]') === 0) {
                        $color_section = TRUE;
                        continue;
                    }
                    if ($color_section) {
                        if (strpos($line,'[') === 0) {
                            break;
                        }
                        if (strpos($line, '=#')) {
                            list($color_name, $color_value) = explode('=', $line);
                            $colors[$file][$color_name] = substr($color_value, 0, 7);
                        }
                    }
                }
                $games = [];
                foreach (explode("\r\n", $config) as $game_row) {
                    if (preg_match('/^Pinball[XY]/', $game_row)) {
                        // Don't modify PinballX and PinballY settings. They use a different scheme and would require to
                        // many exceptions for now.
                        $games[] = $game_row;
                        continue;
                    }

                    if ($game_row = trim($game_row)) {
                        $game_row_elements = str_getcsv($game_row);
                        $game_name = $game_row_elements[0];
                        $game = [];
                        $rgb_ports[$file][$game_name] = [];
                        $real_port = 0;
                        foreach ($game_row_elements as $port => $game_row_element) {
                            $game[$real_port] = trim($game_row_element);
                            if ($real_port) { // Skip Rom name on port 0.
                                foreach (array_keys($colors[$file]) as $color) {
                                    if (strpos($game_row_element, $color) !== FALSE) {
                                        $game[++$real_port] = 0;
                                        $rgb_ports[$file][$game_name][] = $real_port;
                                        $game[++$real_port] = 0;
                                        $rgb_ports[$file][$game_name][] = $real_port;
                                        break;
                                    }
                                }
                                if ('0' === $game[$real_port]) {
                                    $game[$real_port] = 0;
                                }
                            }
                            ++$real_port;
                        }
                        foreach ($per_game_mods as $per_game_name => $adjustments) {
                            foreach ($adjustments as $name => $settings) {
                                foreach ($settings as $port => $setting) {
                                    // Skip global setting when a game-specific setting exists.
                                    if ($per_game_name === $game[0] || (!$per_game_name && (!isset($per_game_mods[$game[0]]) || !isset($per_game_mods[$game[0]][$name]) || !isset($per_game_mods[$game[0]][$name][$port])))) {
                                        if (isset($game[$port])) {

                                            switch ($name) {

                                                // merge port 13 and 21 and save the result on port 13.
                                                // merge[17] = 21
                                                case 'merge':
                                                case 'merge_and_turn_off':
                                                    $ports_to_merge = explode(',', $setting);
                                                    foreach ($ports_to_merge as $port_to_merge) {
                                                        $port_to_merge = trim($port_to_merge);
                                                        if ($game[$port_to_merge]) {
                                                            if ($game[$port]) {
                                                                $game[$port] .= '/' . $game[$port_to_merge];
                                                            } else {
                                                                $game[$port] = $game[$port_to_merge];
                                                            }

                                                            if ('merge_and_turn_off' === $name) {
                                                                $game[$port_to_merge] = 0;
                                                            }
                                                        }
                                                    }
                                                    break;

                                                case 'replace':
                                                    $ports_to_merge = explode(',', $setting);
                                                    $replacements = [];
                                                    foreach ($ports_to_merge as $port_to_merge) {
                                                        $port_to_merge = trim($port_to_merge);
                                                        if ($game[$port_to_merge]) {
                                                            $replacements[] = $game[$port_to_merge];
                                                        }
                                                    }
                                                    $replacement = implode('/', $replacements);
                                                    $game[$port] = $replacement ?: '0';
                                                    break;

                                                case 'swap':
                                                    $setting = trim($setting);
                                                    $tmp = $game[$port];
                                                    $game[$port] = $game[$setting];
                                                    $game[$setting] = $tmp;
                                                    break;

                                                case 'overwrite':
                                                    $game[$port] = trim($setting);
                                                    break;

                                                case 'append':
                                                    if ($game[$port]) {
                                                        $setting = trim($setting);
                                                        if (0 === strpos($setting, '/')) {
                                                            $game[$port] .= $setting;
                                                        } else {
                                                            $game[$port] .= ' ' . $setting;
                                                        }
                                                    }
                                                    break;

                                                case 'default_effect_duration':
                                                    if (0 !== $game[$port]) {
                                                        $triggers = explode('/', $game[$port]);
                                                        foreach ($triggers as &$trigger) {
                                                            $trigger = preg_replace('/([SWE]\d+$)/', '$1 ' . trim($setting), $trigger);
                                                        }
                                                        unset($trigger);
                                                        $game[$port] = implode('/', $triggers);
                                                    }
                                                    break;

                                                case 'target_effect_duration':
                                                case 'drop_target_effect_duration':
                                                    if (0 !== $game[$port]) {
                                                        $pattern = (FALSE !== strpos($name, 'drop_target')) ? '@dt@' : '@t@';
                                                        if (false !== strpos($game[$port], $pattern)) {
                                                            $game[$port] = str_replace($pattern, trim($setting), $game[$port]);
                                                        }
                                                    }
                                                    break;

                                                case 'copy_target':
                                                case 'copy_drop_target':
                                                case 'move_target':
                                                case 'move_drop_target':
                                                    if (0 !== $game[$port]) {
                                                        $pattern = (FALSE !== strpos($name, 'drop_target')) ? '@dt@' : '@t@';
                                                        $triggers = explode('/', $game[$port]);
                                                        foreach ($triggers as $key => $trigger) {
                                                            if (false !== strpos($trigger, $pattern)) {
                                                                $ports_to_merge = explode(',', $setting);
                                                                foreach ($ports_to_merge as $port_to_merge) {
                                                                    $port_to_merge = trim($port_to_merge);
                                                                    if (0 !== $game[$port_to_merge]) {
                                                                        $game[$port_to_merge] .= '/' . $trigger;
                                                                    } else {
                                                                        $game[$port_to_merge] = $trigger;
                                                                    }
                                                                }
                                                                if (FALSE === strpos($name, 'copy')) {
                                                                    unset($triggers[$key]);
                                                                }
                                                            }
                                                        }
                                                        unset($trigger);
                                                        $game[$port] = implode('/', $triggers);
                                                    }
                                                    break;

                                                case 'turn_off':
                                                    if (0 !== $game[$port]) {
                                                        $game_names = explode(',', $setting);
                                                        array_walk($game_names, 'trim');
                                                        if (in_array('*', $game_names) || in_array($game[0], $game_names)) {
                                                            $game[$port] = 0;
                                                        }
                                                    }
                                                    break;

                                                case 'turn_on':
                                                    if (0 !== $game[$port]) {
                                                        $game_names = explode(',', $setting);
                                                        array_walk($game_names, 'trim');
                                                        if (!in_array('*', $game_names) && !in_array($game[0], $game_names)) {
                                                            $game[$port] = 0;
                                                        }
                                                    }
                                                    break;

                                                case 'adjust_intensity':
                                                    if (0 !== $game[$port]) {
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
                                                        unset($trigger);
                                                        $game[$port] =  implode('/', $triggers);
                                                    }
                                                    break;

                                                case 'rgb_brightness':
                                                    if (0 !== $game[$port]) {
                                                        $brightness = trim($setting);
                                                        foreach ($colors[$file] as $color_name => $color_value) {
                                                            $color_name = ' ' . $color_name;
                                                            if (false !== strpos($game[$port], $color_name)) {
                                                                $game[$port] = str_replace($color_name, ' ' . $color_value . $brightness, $game[$port]);
                                                            }
                                                        }
                                                    }
                                                    break;
                                            }

                                        }
                                    }
                                }
                            }
                        }
                        foreach ($rgb_ports[$file][$game_name] as $rgb_port) {
                            unset($game[$rgb_port]);
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
                    $old_cells = explode(',', $line);
                    $new_cells = explode(',', $new_lines[$number]);
                    $num_cells = count($old_cells);
                    $diff_cells = [$old_cells[0]];
                    for ($i = 1; $i < $num_cells; $i++) {
                        $diff_cells[] = $diff->render($old_cells[$i], $new_cells[$i]);
                    }
                    $header = '';
                    $data = '';
                    $game_name = $diff_cells[0];
                    $real_port = 0;
                    foreach ($diff_cells as $port => $dof_string) {
                        $dof_string = str_replace(['<ins>', '<del>'], ['<ins class="bg-success">', '<del class="bg-danger">'], $dof_string);

                        $header .= '<th scope="col"' . (in_array($real_port + 1, $rgb_ports[$file][$game_name]) ? ' bgcolor="red">' : '>') . ($real_port ?: '');
                        ++$real_port;
                        $colspan = 1;
                        while (in_array($real_port, $rgb_ports[$file][$game_name])) {
                            ++$colspan;
                            $header .= '</th><th scope="col" bgcolor="' . (2 == $colspan ? 'green' : 'blue') . '">' .  $real_port++;
                        }
                        $header .= '</th>';

                        if ($port) {
                            $data .= '<td' . ($colspan > 1 ? ' colspan="' . $colspan . '"' : ''). '>' . $dof_string . '</td>';
                        } else {
                            $data .= '<th scope="row">' . $dof_string . '</th>';
                        }
                    }
                    $diffs[basename($file)][] = '<tr>' . $header . '</tr><tr>' . $data . '</tr>';
                }
            }
        }

        $formBuilder = $this->createFormBuilder()
            ->add('cancel', SubmitType::class, ['label' => 'Cancel']);
        if ($diffs) {
            $formBuilder->add('save', SubmitType::class, ['label' => 'Save']);
        }
        $form = $formBuilder->add('files', HiddenType::class, ['data' => base64_encode(serialize($modded_files))])
            ->setAction($this->generateUrl('tweak_do'))
            ->getForm();

        $parsedown = new \Parsedown();

        return $this->render('tweak/confirm.html.twig', [
            'confirm_form' => $form->createView(),
            'diffs' => $diffs,
            'docs' => $parsedown->parse(file_get_contents(__DIR__ . '/../../templates/tweak/IniFiles.md')),
        ]);
    }

    /**
     * @Route("/tweak/do", name="tweak_do")
     */
    public function tweak(Request $request)
    {
        $form = $this->createFormBuilder()
            ->add('cancel', SubmitType::class, ['label' => 'Cancel'])
            ->add('save', SubmitType::class, ['label' => 'Save'])
            ->add('files', HiddenType::class, ['data' => ''])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var \Symfony\Component\Form\Form $form */
            $name = $form->getClickedButton()->getConfig()->getName();
            switch ($name) {
                case 'save':
                    try {
                        $files = unserialize(base64_decode($form->getData()['files']), [false]);
                        foreach ($files as $file => $content) {
                            if (file_put_contents($file, $content)) {
                                $this->addFlash('success', 'Saved tweaked version of ' . $file . '.');
                            } else {
                                $this->addFlash('danger', 'Failed to save tweaked version of' . $file . '.');
                            }
                        }
                    } catch (\Exception $e) {
                        $this->addFlash('warning', $e->getMessage());
                    }
                    break;
            }
        }

        return $this->redirectToRoute('tweak');
    }

}
