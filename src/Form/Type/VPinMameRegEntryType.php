<?php

namespace App\Form\Type;

use App\Entity\VPinMameRegEntry;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class VPinMameRegEntryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('rom', HiddenType::class)
            ->add('cabinet_mode', CheckboxType::class, ['label' => 'cabinet_mode', 'required' => FALSE])
            ->add('ignore_rom_crc', CheckboxType::class, ['label' => 'ignore_rom_crc', 'required' => FALSE])
            ->add('sound', CheckboxType::class, ['label' => 'sound', 'required' => FALSE])
            ->add('sound_mode', ChoiceType::class, ['label' => 'sound_mode', 'choices' => [1 => 1, 2 => 2, 3 => 3], 'required' => FALSE])
            ->add('samples', CheckboxType::class, ['label' => 'samples', 'required' => FALSE])
            ->add('ddraw', CheckboxType::class, ['label' => 'ddraw', 'required' => FALSE])
            ->add('dmd_colorize', CheckboxType::class, ['label' => 'dmd_colorize', 'required' => FALSE])
            ->add('showpindmd', CheckboxType::class, ['label' => 'showpindmd', 'required' => FALSE])
            ->add('showwindmd', CheckboxType::class, ['label' => 'showwindmd', 'required' => FALSE])
            ->add('synclevel', NumberType::class, ['label' => 'synclevel', 'required' => FALSE]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => VPinMameRegEntry::class,
        ]);
    }
}