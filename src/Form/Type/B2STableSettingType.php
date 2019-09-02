<?php

namespace App\Form\Type;

use App\Entity\B2STableSetting;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class B2STableSettingType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $hide_choices = ['Visible' => 0, 'Hidden' => 1, 'Standard' => 2];
        $builder
            ->add('rom', HiddenType::class)
            ->add('HideGrill', ChoiceType::class, ['choices' => $hide_choices, 'label' => false])
            ->add('HideB2SDMD', ChoiceType::class, ['choices' => ['Visible' => 0, 'Hidden' => 1], 'label' => false])
            ->add('HideDMD', ChoiceType::class, ['choices' => $hide_choices, 'label' => false])
            ->add('LampsSkipFrames', IntegerType::class, ['label' => false])
            ->add('SolenoidsSkipFrames', IntegerType::class, ['label' => false])
            ->add('GIStringsSkipFrames', IntegerType::class, ['label' => false])
            ->add('LEDsSkipFrames', IntegerType::class, ['label' => false])
            ->add('StartAsEXE', CheckboxType::class, ['label' => false, 'required' => false])
            ->add('StartBackground', CheckboxType::class, ['label' => false, 'required' => false]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => B2STableSetting::class,
        ]);
    }
}