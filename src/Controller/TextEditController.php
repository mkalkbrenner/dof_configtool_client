<?php

namespace App\Controller;

use App\Entity\TextFile;
use GitWrapper\GitException;
use Norzechowicz\AceEditorBundle\Form\Extension\AceEditor\Type\AceEditorType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
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
            'theme' => 'ace/theme/monokai',
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
                    if ($cycle && $this->settings->isVersionControl()) {
                        try {
                            $workingCopy = $this->getGitWorkingCopy($directory);
                            $workingCopy->checkout($cycle);
                        } catch (GitException $e) {
                            $this->addFlash('warning', $e->getMessage());
                        }
                    }

                    $textFile->persist();

                    try {
                        if ($cycle && $this->settings->isVersionControl() && $workingCopy->hasChanges()) {
                            $version = 0;
                            $history = preg_split('/\r\n?|\n/', $workingCopy->log('--pretty=format:"%s"', '-1'));
                            if ($history) {
                                $latest = array_shift($history);
                                if (preg_match('/Version (\d+) \|/', $latest, $matches)) {
                                    $version = $matches[1];
                                }
                            }

                            $workingCopy->add($file);
                            $workingCopy->commit('Version ' . $version . ' | edited ' . $file);
                            $changes = nl2br($workingCopy->run('show'));

                            if ($previous_branch && $cycle !== $previous_branch) {
                                $workingCopy->checkout($previous_branch);
                            }
                        }
                    } catch (GitException $e) {
                        $this->addFlash('warning', $e->getMessage());
                    }

                    $session->set('git_diff', $changes);
                    return $this->redirectToRoute('textedit');

                case 'cancel':
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
}
