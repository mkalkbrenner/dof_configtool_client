<?php

namespace App\Controller;

use App\Entity\TextDiffFile;
use App\Entity\TextFile;
use App\Form\Type\AceDiffType;
use GitWrapper\GitException;
use Norzechowicz\AceEditorBundle\Form\Extension\AceEditor\Type\AceEditorType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class TextEditController extends AbstractSettingsController
{
    /**
     * @Route("/textedit", name="textedit")
     */
    public function index(Request $request, SessionInterface $session)
    {
        $formBuilder = $this->createFormBuilder();

        $branches = [];
        if ($this->settings->isVersionControl()) {
            try {
                $workingCopy = $this->getGitWorkingCopy($this->settings->getDofConfigPath());
                $branches = $workingCopy->getBranches()->all();
                foreach ($branches as $branch) {
                    if ('master' !== $branch) {
                        $formBuilder->add('cabinetxml_' . $branch, SubmitType::class, ['label' => 'Edit ' . $branch]);
                        $formBuilder->add('globalconfigb2sserverxml_' . $branch, SubmitType::class, ['label' => 'Edit ' . $branch]);
                        if ('download' !== $branch) {
                            $formBuilder->add('freshcabinetxml_' . $branch, SubmitType::class, ['label' => 'Fresh copy from download']);
                            $formBuilder->add('freshglobalconfigb2sserverxml_' . $branch, SubmitType::class, ['label' => 'Fresh copy from download']);
                            if ('day' === $branch && in_array('night', $branches)) {
                                $formBuilder->add('nightcabinetxml_' . $branch, SubmitType::class, ['label' => 'Copy from night']);
                                $formBuilder->add('nightglobalconfigb2sserverxml_' . $branch, SubmitType::class, ['label' => 'Copy from night']);
                            } elseif ('night' === $branch && in_array('day', $branches)) {
                                $formBuilder->add('daycabinetxml_' . $branch, SubmitType::class, ['label' => 'Copy from day']);
                                $formBuilder->add('dayglobalconfigb2sserverxml_' . $branch, SubmitType::class, ['label' => 'Copy from day']);
                            }
                        }
                    }
                }
            } catch (GitException $e) {
                $this->addFlash('warning', $e->getMessage());
            }
        } else {
            $formBuilder->add('cabinetxml_', SubmitType::class, ['label' => 'Edit']);
            $formBuilder->add('globalconfigb2sserverxml_', SubmitType::class, ['label' => 'Edit']);
        }

        if ($this->settings->getPinballYPath()) {
            $formBuilder->add('mainjs_', SubmitType::class, ['label' => 'Edit']);
        }

        $form = $formBuilder
            ->add('vpmaliastxt_', SubmitType::class, ['label' => 'Edit'])
            ->add('dmddeviceini_', SubmitType::class, ['label' => 'Edit'])
            ->add('b2stablesettingsxml_', SubmitType::class, ['label' => 'Edit'])
            ->add('screenrestxt_', SubmitType::class, ['label' => 'Edit'])
            ->add('globalpluginvbs_', SubmitType::class, ['label' => 'Edit'])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var \Symfony\Component\Form\Form $form */
            list($name, $cycle) = explode('_', $form->getClickedButton()->getConfig()->getName());

            switch ($name) {
                case 'cabinetxml':
                    return $this->redirectToRoute('textedit_editor', [
                        'directory' => $this->settings->getDofConfigPath(),
                        'file' => 'Cabinet.xml',
                        'mode' => 'ace/mode/xml',
                        'cycle' => $cycle,
                    ]);

                case 'freshcabinetxml':
                    return $this->redirectToRoute('textedit_editor', [
                        'directory' => $this->settings->getDofConfigPath(),
                        'file' => 'Cabinet.xml',
                        'mode' => 'ace/mode/xml',
                        'cycle' => $cycle,
                        'source' => 'download',
                    ]);

                case 'nightcabinetxml':
                    return $this->redirectToRoute('textedit_editor', [
                        'directory' => $this->settings->getDofConfigPath(),
                        'file' => 'Cabinet.xml',
                        'mode' => 'ace/mode/xml',
                        'cycle' => $cycle,
                        'source' => 'night',
                    ]);

                case 'daycabinetxml':
                    return $this->redirectToRoute('textedit_editor', [
                        'directory' => $this->settings->getDofConfigPath(),
                        'file' => 'Cabinet.xml',
                        'mode' => 'ace/mode/xml',
                        'cycle' => $cycle,
                        'source' => 'day',
                    ]);

                case 'globalconfigb2sserverxml':
                    return $this->redirectToRoute('textedit_editor', [
                        'directory' => $this->settings->getDofConfigPath(),
                        'file' => 'GlobalConfig_B2SServer.xml',
                        'mode' => 'ace/mode/xml',
                        'cycle' => $cycle,
                    ]);

                case 'freshglobalconfigb2sserverxml':
                    return $this->redirectToRoute('textedit_editor', [
                        'directory' => $this->settings->getDofConfigPath(),
                        'file' => 'GlobalConfig_B2SServer.xml',
                        'mode' => 'ace/mode/xml',
                        'cycle' => $cycle,
                        'source' => 'download',
                    ]);

                case 'nightglobalconfigb2sserverxml':
                    return $this->redirectToRoute('textedit_editor', [
                        'directory' => $this->settings->getDofConfigPath(),
                        'file' => 'GlobalConfig_B2SServer.xml',
                        'mode' => 'ace/mode/xml',
                        'cycle' => $cycle,
                        'source' => 'night',
                    ]);

                case 'dayglobalconfigb2sserverxml':
                    return $this->redirectToRoute('textedit_editor', [
                        'directory' => $this->settings->getDofConfigPath(),
                        'file' => 'GlobalConfig_B2SServer.xml',
                        'mode' => 'ace/mode/xml',
                        'cycle' => $cycle,
                        'source' => 'day',
                    ]);

                case 'mainjs':
                    return $this->redirectToRoute('textedit_editor', [
                        'directory' => $this->settings->getPinballYPath() . DIRECTORY_SEPARATOR . 'Scripts',
                        'file' => 'main.js',
                        'mode' => 'ace/mode/javascript',
                    ]);

                case 'vpmaliastxt':
                    return $this->redirectToRoute('textedit_editor', [
                        'directory' => $this->settings->getVPinMamePath(),
                        'file' => 'VPMAlias.txt',
                        'mode' => 'ace/mode/text',
                        'help' => base64_encode('Format per line: alias,rom'),
                    ]);

                case 'dmddeviceini':
                    return $this->redirectToRoute('textedit_editor', [
                        'directory' => $this->settings->getVPinMamePath(),
                        'file' => 'DmdDevice.ini',
                        'mode' => 'ace/mode/properties',
                    ]);

                case 'b2stablesettingsxml':
                    return $this->redirectToRoute('textedit_editor', [
                        'directory' => $this->settings->getTablesPath(),
                        'file' => 'B2STableSettings.xml',
                        'mode' => 'ace/mode/xml',
                    ]);

                case 'screenrestxt':
                    return $this->redirectToRoute('textedit_editor', [
                        'directory' => $this->settings->getTablesPath(),
                        'file' => 'ScreenRes.txt',
                        'mode' => 'ace/mode/text',
                    ]);

                case 'globalpluginvbs':
                    return $this->redirectToRoute('textedit_editor', [
                        'directory' => $this->settings->getVisualPinballPath() . DIRECTORY_SEPARATOR . 'Scripts',
                        'file' => 'GlobalPlugIn.vbs',
                        'mode' => 'ace/mode/vbscript',
                        'help' => $this->settings->isVersionControl() ? base64_encode('Script is not under version control!') : null,
                    ]);
            }
        }

        $changes = $session->get('git_diff', '');
        if ($changes) {
            $session->remove('git_diff');
        }

        return $this->render('textedit/index.html.twig', [
            'textedit_form' => $form->createView(),
            'git_diff' => nl2br($changes),
            'branches' => $branches,
        ]);
    }

    /**
     * @Route("/textedit/editor", name="textedit_editor")
     */
    public function edit(Request $request, SessionInterface $session)
    {
        $directory = $request->query->get('directory');
        if (!$directory) {
            $this->addFlash('danger', 'The settings are incomplete or the directory could not be found.');
            return $this->redirectToRoute('textedit');
        }
        $file = $request->query->get('file');
        $mode = $request->query->get('mode');
        $cycle = $request->query->get('cycle');
        $source = $request->query->get('source');
        $help = $request->query->get('help');
        $hash = $request->query->get('hash');
        $selected_rom = $request->query->get('selected_rom');
        $previous_branch = '';
        $workingCopy = null;
        $branch = $source ?? $cycle;

        try {
            if ($cycle && $this->settings->isVersionControl()) {
                $workingCopy = $this->getGitWorkingCopy($directory);
                $previous_branch = $this->getCurrentBranch($workingCopy);
                $workingCopy->checkout($branch);
            }

            $textFile = new TextFile($directory, $file);
            $textFile->load();

            if ($previous_branch && $branch !== $previous_branch) {
                $workingCopy->checkout($previous_branch);
            }
        } catch (GitException $e) {
            $this->addFlash('warning', $e->getMessage());
            return $this->redirectToRoute('textedit');
        }

        $defaults = [
            'mode' => $mode,
            'theme' => null,
            'width' => '100%',
            'height' => 600,
            'font_size' => 12,
            'tab_size' => null,
            'read_only' => null,
            'use_soft_tabs' => null,
            'use_wrap_mode' => true,
            'show_print_margin' => null,
            'show_invisibles' => null,
            'highlight_active_line' => true,
            'options_enable_basic_autocompletion' => false,
            'options_enable_live_autocompletion' => false,
            'options_enable_snippets' => false,
            'keyboard_handler' => null
        ];

        $form = $this->createFormBuilder($textFile)
            ->add('text', AceEditorType::class, [
                'label' => $textFile->getPath(),
                'help' => $help ? base64_decode($help) : null,
                'required' => false,
            ] + $defaults)
            ->add('cancel', SubmitType::class, ['label' => 'Cancel'])
            ->add('save', SubmitType::class, ['label' => 'Save'])
            ->getForm();

        $form->handleRequest($request);

        $changes = '';
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var TextFile $textFile */
            $textFile = $form->getData();

            /** @var \Symfony\Component\Form\Form $form */
            $name = $form->getClickedButton()->getConfig()->getName();
            switch ($name) {
                case 'save':
                    if (($cycle || $hash) && $this->settings->isVersionControl()) {
                        try {
                            $workingCopy = $this->getGitWorkingCopy($directory);
                            if ($cycle) {
                                $workingCopy->checkout($cycle);
                            }
                        } catch (GitException $e) {
                            $this->addFlash('warning', $e->getMessage());
                        }
                    }

                    $textFile->persist();

                    try {
                        if (($cycle || $hash) && $this->settings->isVersionControl() && $workingCopy->hasChanges()) {
                            $version = 0;
                            if ($cycle) {
                                $history = preg_split('/\r\n?|\n/', $workingCopy->log('--pretty=format:"%s"', '-1'));
                                if ($history) {
                                    $latest = array_shift($history);
                                    if (preg_match('/Version (\d+) \|/', $latest, $matches)) {
                                        $version = $matches[1];
                                    }
                                }
                            }
                            $workingCopy->add($file);
                            $status = $workingCopy->run('status', ['-s', '-uno']);
                            if (!empty($status)) {
                                $workingCopy->commit($file, ['m' => 'Version ' . $version . ' | edited ' . $file]);
                            }
                            $changes = nl2br($workingCopy->run('show'));

                            if ($previous_branch && $cycle !== $previous_branch) {
                                $workingCopy->checkout($previous_branch);
                            }
                        }
                    } catch (GitException $e) {
                        $this->addFlash('warning', $e->getMessage());
                    }

                    if ($hash && $selected_rom) {
                        return $this->redirectToRoute('table', ['hash' => $hash, 'selected_rom' => $selected_rom]);
                    }

                    $session->set('git_diff', $changes);
                    return $this->redirectToRoute('textedit');

                case 'cancel':
                    if ($hash && $selected_rom) {
                        return $this->redirectToRoute('table', ['hash' => $hash, 'selected_rom' => $selected_rom]);
                    }
                    return $this->redirectToRoute('textedit');
            }
        }

        return $this->render('textedit/edit.html.twig', [
            'textedit_form' => $form->createView(),
            'file' => $file,
            'git_diff' => nl2br($changes),
            'cycle' => $cycle,
        ]);
    }

    /**
     * @Route("/textedit/select_diff", name="textedit_select_diff")
     */
    public function select_diff(Request $request, SessionInterface $session)
    {
        $directory = $request->query->get('directory');
        if (!$directory) {
            $this->addFlash('danger', 'The settings are incomplete or the directory could not be found.');
            return $this->redirectToRoute('textedit');
        }
        $file = $request->query->get('file');
        $mode = $request->query->get('mode');
        $cycle = $request->query->get('cycle');
        $source = $request->query->get('source');
        $help = $request->query->get('help');
        $hash = $request->query->get('hash');
        $selected_rom = $request->query->get('selected_rom');

        $formBuilder = $this->createFormBuilder();

        if (($cycle || $hash) && $this->settings->isVersionControl()) {
            try {
                $workingCopy = $this->getGitWorkingCopy($directory);

                $history = preg_split('/\r\n?|\n/', $workingCopy->log('--pretty=format:"%H_##_%s | %ad"', '--date=rfc2822', '-50', $file));
                if ($history) {
                    $latest = array_shift($history);
                    $choices = [];
                    foreach ($history as $entry) {
                        list($version, $label) = explode('_##_', trim($entry, '"'));
                        $choices[$label] = $version;
                    }
                    $formBuilder->add('history', ChoiceType::class, [
                        'choices' => array_merge(['-' => '_'], $choices),
                        'label' => 'File history',
                        'help' => 'Older versions of the same file'
                    ]);
                }
            } catch (GitException $e) {
                $this->addFlash('warning', $e->getMessage());
            }
        }

        if (preg_match('/(\.[^.]+)$/', $file, $matches)) {
            $candidates = [];
            $same_kind = [];
            $lower_file = mb_strtolower(substr($file, 0, 10));
            foreach (scandir($directory) as $filename) {
                if ($filename !== $file) {
                    if (preg_match('/' . preg_quote($matches[1], '/') . '$/i', $filename)) {
                        $same_kind[] = $filename;
                        $distance = levenshtein(mb_strtolower(substr($filename, 0, 10)), $lower_file);
                        if ($distance <= 2) {
                            $candidates[$filename] = $distance;
                        }
                    }
                }
            }
            if ($same_kind) {
                asort($same_kind, SORT_NATURAL);
                if ($candidates) {
                    asort($candidates, SORT_NUMERIC);
                    $choices = [
                        'Suggested' => array_combine(array_keys($candidates), array_keys($candidates)),
                        'All' => array_combine($same_kind, $same_kind),
                    ];
                } else {
                    $choices = array_combine($same_kind, $same_kind);
                }
                $formBuilder->add('same_kind', ChoiceType::class, [
                    'choices' => array_merge(['-' => '_'], $choices),
                    'label' => 'Other files',
                    'help' => ' Files of the of the same kind in the same directory',
                ]);
            }
        }

        $form = $formBuilder
            ->add('compare', SubmitType::class, ['label' => 'Compare'])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            return $this->redirectToRoute('textedit_diff', [
                'directory' => $directory,
                'file' => $file,
                'left_revision' => $data['history'] !== '_' ? $data['history'] : null,
                'left_file' => $data['same_kind'] !== '_' ? $data['same_kind'] : null,
                'mode' => $mode,
                'cycle' => $cycle,
                'source' => $source,
                'hash' => $hash,
                'selected_rom' => $selected_rom,
                'help' => $help,
            ]);
        }

        return $this->render('textedit/select_diff.html.twig', [
            'select_diff_form' => $form->createView(),
            'file' => $file,
        ]);
    }

        /**
         * @Route("/textedit/diff", name="textedit_diff")
         */
        public function diff(Request $request, SessionInterface $session)
    {
        $directory = $request->query->get('directory');
        if (!$directory) {
            $this->addFlash('danger', 'The settings are incomplete or the directory could not be found.');
            return $this->redirectToRoute('textedit');
        }
        $file = $request->query->get('file');
        $left_revision = $request->query->get('left_revision');
        $left_file = $request->query->get('left_file');
        $mode = $request->query->get('mode');
        $cycle = $request->query->get('cycle');
        $source = $request->query->get('source');
        $help = $request->query->get('help');
        $hash = $request->query->get('hash');
        $selected_rom = $request->query->get('selected_rom');
        $previous_branch = '';
        $workingCopy = null;
        $branch = $source ?? $cycle;

        $content = '';
        if (!empty($left_revision) && $this->settings->isVersionControl()) {
            try {
                $workingCopy = $this->getGitWorkingCopy($directory);
                $content = $workingCopy->show($left_revision . ':' . $file);

            } catch (GitException $e) {
                $this->addFlash('warning', $e->getMessage());
            }
        } elseif (!empty($left_file)) {
            $other_file = $directory . DIRECTORY_SEPARATOR . $left_file;
            if (is_file($other_file)) {
                $content = file_get_contents($other_file);
            }
        }

        try {
            if ($cycle && $this->settings->isVersionControl()) {
                $workingCopy = $this->getGitWorkingCopy($directory);
                $previous_branch = $this->getCurrentBranch($workingCopy);
                $workingCopy->checkout($branch);
            }

            $textFile = new TextDiffFile($directory, $file);
            $textFile->load();
            $textFile->setLeft($content);

            if ($previous_branch && $branch !== $previous_branch) {
                $workingCopy->checkout($previous_branch);
            }
        } catch (GitException $e) {
            $this->addFlash('warning', $e->getMessage());
            return $this->redirectToRoute('textedit');
        }

        $defaults = [
            'mode' => $mode,
            'theme' => null,
            'width' => '100%',
            'height' => 600,
            'font_size' => 12,
            'tab_size' => null,
            'read_only' => null,
            'use_soft_tabs' => null,
            'use_wrap_mode' => true,
            'show_print_margin' => null,
            'show_invisibles' => null,
            'highlight_active_line' => true,
            'options_enable_basic_autocompletion' => false,
            'options_enable_live_autocompletion' => false,
            'options_enable_snippets' => false,
            'keyboard_handler' => null
        ];

        $form = $this->createFormBuilder($textFile)
            ->add('left', TextareaType::class, [
                    'label' => $left_revision ?? $left_file,
                    'required' => false,
                ])
            ->add('right', AceDiffType::class, [
                    'label' => $textFile->getPath(),
                    'required' => false,
                ] + $defaults)
            ->add('cancel', SubmitType::class, ['label' => 'Cancel'])
            ->add('save', SubmitType::class, ['label' => 'Save the right side'])
            ->getForm();

        $form->handleRequest($request);

        $changes = '';
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var TextDiffFile $textFile */
            $textFile = $form->getData();

            /** @var \Symfony\Component\Form\Form $form */
            $name = $form->getClickedButton()->getConfig()->getName();
            switch ($name) {
                case 'save':
                    if (($cycle || $hash) && $this->settings->isVersionControl()) {
                        try {
                            $workingCopy = $this->getGitWorkingCopy($directory);
                            if ($cycle) {
                                $workingCopy->checkout($cycle);
                            }
                        } catch (GitException $e) {
                            $this->addFlash('warning', $e->getMessage());
                        }
                    }

                    $textFile->persist();

                    try {
                        if (($cycle || $hash) && $this->settings->isVersionControl() && $workingCopy->hasChanges()) {
                            $version = 0;
                            if ($cycle) {
                                $history = preg_split('/\r\n?|\n/', $workingCopy->log('--pretty=format:"%s"', '-1'));
                                if ($history) {
                                    $latest = array_shift($history);
                                    if (preg_match('/Version (\d+) \|/', $latest, $matches)) {
                                        $version = $matches[1];
                                    }
                                }
                            }
                            $workingCopy->add($file);
                            $status = $workingCopy->run('status', ['-s', '-uno']);
                            if (!empty($status)) {
                                $workingCopy->commit($file, ['m' => 'Version ' . $version . ' | edited ' . $file]);
                            }
                            $changes = nl2br($workingCopy->run('show'));

                            if ($previous_branch && $cycle !== $previous_branch) {
                                $workingCopy->checkout($previous_branch);
                            }
                        }
                    } catch (GitException $e) {
                        $this->addFlash('warning', $e->getMessage());
                    }

                    if ($hash && $selected_rom) {
                        return $this->redirectToRoute('table', ['hash' => $hash, 'selected_rom' => $selected_rom]);
                    }

                    $session->set('git_diff', $changes);
                    return $this->redirectToRoute('textedit');

                case 'cancel':
                    if ($hash && $selected_rom) {
                        return $this->redirectToRoute('table', ['hash' => $hash, 'selected_rom' => $selected_rom]);
                    }
                    return $this->redirectToRoute('textedit');
            }
        }

        return $this->render('textedit/diff.html.twig', [
            'textedit_form' => $form->createView(),
            'file' => $file,
            'git_diff' => nl2br($changes),
            'cycle' => $cycle,
        ]);
    }
}
