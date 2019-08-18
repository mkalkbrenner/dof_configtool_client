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

        if ($this->settings->isVersionControl()) {
            try {
                $workingCopy = $this->getGitWorkingCopy($this->settings->getDofConfigPath());
                $branches = $workingCopy->getBranches();
                foreach ($branches as $branch) {
                    $formBuilder->add('cabinet_xml|' . $branch, SubmitType::class, ['label' => 'Edit Cabinet.xml (DOF) for cycle ' . $branch]);
                }
            } catch (GitException $e) {
                $this->addFlash('warning', $e->getMessage());
            }
        } else {
            $formBuilder->add('cabinet_xml|', SubmitType::class, ['label' => 'Edit Cabinet.xml (DOF)']);
        }

        $form = $formBuilder->add('dmddevice_ini|', SubmitType::class, ['label' => 'Edit DmdDevice.ini (freezy\'s dll)'])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var \Symfony\Component\Form\Form $form */
            list($name, $cycle) = explode('|', $form->getClickedButton()->getConfig()->getName());

            switch ($name) {
                case 'cabinet_xml':
                    return $this->redirectToRoute('textedit_editor', [
                        'directory' => $this->settings->getDofConfigPath(),
                        'file' => 'Cabinet.xml',
                        'mode' => 'ace/mode/xml',
                        'cycle' => $cycle,
                    ]);

                case 'dmddevice_ini':
                    return $this->redirectToRoute('textedit_editor', [
                        'directory' => $this->settings->getVPinMamePath(),
                        'file' => 'dmddevice.ini',
                        'mode' => 'ace/mode/properties',
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
        ]);
    }

    /**
     * @Route("/textedit/editor", name="textedit_editor")
     */
    public function edit(Request $request, SessionInterface $session)
    {
        $directory = $request->query->get('directory');
        $file = $request->query->get('file');
        $mode = $request->query->get('mode');
        $cycle = $request->query->get('cycle');
        $previous_branch = '';
        $workingCopy = null;

        try {
            if ($cycle && $this->settings->isVersionControl()) {
                $workingCopy = $this->getGitWorkingCopy($directory);
                $previous_branch = $this->getCurrentBranch($workingCopy);
                $workingCopy->checkout($cycle);
            }

            $textFile = new TextFile($directory, $file);
            $textFile->load();

            if ($previous_branch && $cycle !== $previous_branch) {
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
        ]);
    }
}
