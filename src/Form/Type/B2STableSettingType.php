<?php

namespace App\Form\Type;

use App\Entity\B2STableSetting;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class B2STableSettingType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $hide_choices = ['default' => 0, 'hide' => 1, 'show'];
        $builder
            ->add('rom', HiddenType::class)
            ->add('HideGrill', ChoiceType::class, ['choices' => $hide_choices, 'label' => false])
            ->add('HideB2SDMD', ChoiceType::class, ['choices' => $hide_choices, 'label' => false])
            ->add('HideDMD', ChoiceType::class, ['choices' => $hide_choices, 'label' => false])
            ->add('LampsSkipFrames', ChoiceType::class, ['choices' => $hide_choices, 'label' => false])
            ->add('SolenoidsSkipFrames', ChoiceType::class, ['choices' => $hide_choices, 'label' => false])
            ->add('GIStringsSkipFrames', ChoiceType::class, ['choices' => $hide_choices, 'label' => false])
            ->add('LEDsSkipFrames', ChoiceType::class, ['choices' => $hide_choices, 'label' => false])
            ->add('UsedLEDType', ChoiceType::class, ['choices' => $hide_choices, 'label' => false])
            ->add('IsGlowBulbOn', ChoiceType::class, ['choices' => $hide_choices, 'label' => false])
            ->add('GlowIndex', ChoiceType::class, ['choices' => $hide_choices, 'label' => false])
            ->add('StartAsEXE', ChoiceType::class, ['choices' => $hide_choices, 'label' => false])
            ->add('StartBackground', ChoiceType::class, ['choices' => $hide_choices, 'label' => false])
            ->add('DualMode', ChoiceType::class, ['choices' => $hide_choices, 'label' => false]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => B2STableSetting::class,
        ]);
    }
}