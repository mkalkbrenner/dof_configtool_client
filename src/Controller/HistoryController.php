<?php

namespace App\Controller;

use GitWrapper\GitException;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class HistoryController extends AbstractSettingsController
{
    /**
     * @Route("/history", name="history")
     */
    public function index(Request $request)
    {
        if (!$this->settings->isVersionControl()) {
            $this->addFlash('warning', 'History requires version control to be enabled. Check your settings.');
            return $this->redirectToRoute('settings');
        }

        try {
            $workingCopy = $this->getGitWorkingCopy($this->settings->getDofConfigPath());
            $history = preg_split('/\r\n?|\n/', $workingCopy->log('--pretty=format:"%H_##_%s | %ad"', '--date=rfc2822', '-50'));
            if ($history) {
                $latest = array_shift($history);
                list(, $current) = explode('_##_', trim($latest, '"'));
            }
            $branch = $this->getCurrentBranch($workingCopy);
        } catch (GitException $e) {
            $this->addFlash('warning', $e->getMessage());
        }

        $formBuilder = $this->createFormBuilder();
        foreach ($history as $log) {
            list($hash, $msg) = explode('_##_', trim($log, '"'));
            $formBuilder->add($hash, SubmitType::class, ['label' => $msg]);
        }
        $form = $formBuilder->getForm();

        $form->handleRequest($request);
        $changes = '';
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var \Symfony\Component\Form\Form $form */
            $hash = $form->getClickedButton()->getConfig()->getName();
            try {
                // @see https://stackoverflow.com/questions/44727750/how-do-i-restore-a-previous-version-as-a-new-commit-in-git
                $workingCopy->checkout($hash, '.');
                if ($workingCopy->hasChanges()) {
                    preg_match('/^(.+) \|[^\|]+$/', $form->getClickedButton()->getConfig()->getAttribute('data_collector/passed_options')['label'], $msg);
                    $workingCopy->add('*.ini');
                    $workingCopy->add('*.xml');
                    $workingCopy->add('*.png');
                    $workingCopy->commit($msg[1]);
                }
            } catch (GitException $e) {
                $this->addFlash('warning', $e->getMessage());
            }
            // We need to redirect to build a new form based on the new branch.
            return $this->redirectToRoute('history');
        }

        return $this->render('history/index.html.twig', [
            'history_form' => $form->createView(),
            'branch' => $branch,
            'current' => $current,
        ]);
    }
}
