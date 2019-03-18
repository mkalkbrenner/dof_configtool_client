<?php

namespace App\Form\Type;

use App\Entity\VPinMameRegEntry;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class VPinMameRegEntryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('rom', HiddenType::class)
            ->add('cabinet_mode', CheckboxType::class, ['label' => 'cabinet_mode'])
            ->add('ignore_rom_crc', CheckboxType::class, ['label' => 'ignore_rom_crc'])
            ->add('ddraw', CheckboxType::class, ['label' => 'ddraw']);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => VPinMameRegEntry::class,
        ]);
    }
}