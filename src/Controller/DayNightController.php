<?php

namespace App\Controller;

use App\Component\Utility;
use GitWrapper\GitException;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class DayNightController extends AbstractSettingsController
{
    /**
     * @Route("/day-night", name="day_night")
     */
    public function index(Request $request)
    {
        if (!$this->settings->isVersionControl()) {
            $this->addFlash('warning', 'Day night cycle requires version control to be enabled. Check your settings.');
            return $this->redirectToRoute('settings');
        }

        try {
            $workingCopy = $this->getGitWorkingCopy($this->settings->getDofConfigPath());
            $log = $workingCopy->log('--pretty=format:"%s | %ad"', '--date=rfc2822', '-1');
            $branch = $this->getCurrentBranch($workingCopy);
            $branches = $workingCopy->getBranches()->all();
        } catch (GitException $e) {
            $this->addFlash('warning', $e->getMessage());
        }

        $formBuilder = $this->createFormBuilder();
        $cmd = [];
        foreach (['day' => 'day', 'night' => 'night', 'download' => 'unmodified/downloaded'] as $name => $label) {
            if (in_array($name, $branches)) {
                if ($name !== $branch) {
                    $formBuilder->add($name, SubmitType::class, ['label' => 'Switch to ' . $label . ' DOF settings']);
                }
                $cmd[$label] = $this->settings->getGitBinary() . ' -C ' . $this->settings->getDofConfigPath() . ' checkout ' . $name;
            }
        }
        foreach (['day' => 'day', 'night' => 'night'] as $name => $label) {
            if (in_array($name, $branches)) {
                $formBuilder->add($name . '_diff', SubmitType::class, ['label' => 'Show differences of ' . $label . ' compared to unmodified/downloaded version']);
            }
        }
        $form = $formBuilder->getForm();

        if (!$cmd) {
            $this->addFlash('warning', 'No optional DOF settings detected. Try to download and tweak them first.');
        }

        $form->handleRequest($request);

        $diffs = [];
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var \Symfony\Component\Form\Form $form */
            $branch = $form->getClickedButton()->getConfig()->getName();
            try {
                if (strpos($branch, '_diff') !== false) {
                    $branch = str_replace('_diff', '', $branch);
                    $previous_branch = $this->getCurrentBranch($workingCopy);

                    $download_files = [];
                    $branch_files = [];

                    $workingCopy->checkout('download');
                    foreach (scandir($this->settings->getDofConfigPath()) as $file) {
                        if (preg_match('/^directoutputconfig\d+\.ini$/i', $file, $matches)) {
                            $file_path = $this->settings->getDofConfigPath() . DIRECTORY_SEPARATOR . $matches[0];
                            $download_files[$file_path] = file_get_contents($file_path);
                        }
                    }

                    $workingCopy->checkout($branch);
                    foreach (scandir($this->settings->getDofConfigPath()) as $file) {
                        if (preg_match('/^directoutputconfig\d+\.ini$/i', $file, $matches)) {
                            $file_path = $this->settings->getDofConfigPath() . DIRECTORY_SEPARATOR . $matches[0];
                            $branch_files[$file_path] = file_get_contents($file_path);
                        }
                    }

                    $diffs = Utility::getDiffTables($download_files, $branch_files, $this->settings);

                    $workingCopy->checkout($previous_branch);
                } else {
                    $workingCopy->checkout($branch);
                }
            } catch (GitException $e) {
                $this->addFlash('warning', $e->getMessage());
            }
            // We need to redirect to build a new form based on the new branch.
            return $this->redirectToRoute('day_night');
        }

        return $this->render('day_night/index.html.twig', [
            'day_night_form' => $form->createView(),
            'branch' => $branch,
            'log' => $log,
            'cmd' => $cmd,
            'diffs' => $diffs,
        ]);
    }
}
