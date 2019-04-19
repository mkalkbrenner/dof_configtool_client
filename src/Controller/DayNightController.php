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
            $branch = trim($workingCopy->run('symbolic-ref', ['--short', 'HEAD']));
            $branches = $workingCopy->getBranches()->all();
        } catch (GitException $e) {
            $this->addFlash('warning', $e->getMessage());
        }

        $formBuilder = $this->createFormBuilder();
        $button = false;
        if ('day' !== $branch && in_array('day', $branches)) {
            $formBuilder->add('day', SubmitType::class, ['label' => 'Switch to day DOF settings']);
            $button = true;
        }
        if ('night' !== $branch && in_array('night', $branches)) {
            $formBuilder->add('night', SubmitType::class, ['label' => 'Switch to night DOF settings']);
            $button = true;
        }
        if ('download' !== $branch && in_array('download', $branches)) {
            $formBuilder->add('download', SubmitType::class, ['label' => 'Switch to unmodified/downloaded DOF settings']);
            $button = true;
        }
        $form = $formBuilder->getForm();

        if (!$button) {
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
        ]);
    }
}
