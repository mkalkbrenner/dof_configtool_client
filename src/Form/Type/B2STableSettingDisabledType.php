<?php

namespace App\Form\Type;

use App\Entity\B2STableSetting;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class B2STableSettingDisabledType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $hide_choices = ['default' => 0, 'hide' => 1, 'show'];
        $builder
            ->add('rom', HiddenType::class)
            ->add('HideGrill', TextType::class, ['disabled' => true, 'label' => false])
            ->add('HideB2SDMD', TextType::class, ['disabled' => true, 'label' => false])
            ->add('HideDMD', TextType::class, ['disabled' => true, 'label' => false])
            ->add('LampsSkipFrames', TextType::class, ['disabled' => true, 'label' => false])
            ->add('SolenoidsSkipFrames', TextType::class, ['disabled' => true, 'label' => false])
            ->add('GIStringsSkipFrames', TextType::class, ['disabled' => true, 'label' => false])
            ->add('LEDsSkipFrames', TextType::class, ['disabled' => true, 'label' => false])
            ->add('StartAsEXE', TextType::class, ['disabled' => true, 'label' => false])
            ->add('StartBackground', TextType::class, ['disabled' => true, 'label' => false]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => B2STableSetting::class,
        ]);
    }
}