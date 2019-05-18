<?php

namespace App\Controller;

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
        $form = $formBuilder->getForm();

        if (!$cmd) {
            $this->addFlash('warning', 'No optional DOF settings detected. Try to download and tweak them first.');
        }

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var \Symfony\Component\Form\Form $form */
            $branch = $form->getClickedButton()->getConfig()->getName();
            try {
                $workingCopy->checkout($branch);
            } catch (GitException $e) {
                $this->addFlash('warning', $e->getMessage());
            }
            // We need to redirect to build a new form based on the new branch.
            return $this->redirectToRoute('day_night');
        }

        return $this->render('day_night/index.html.twig', [
            'day_night_form' => $form->createView(),
            'branch' => $branch,
            'cmd' => $cmd,
        ]);
    }
}
