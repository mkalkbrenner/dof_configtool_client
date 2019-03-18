<?php

namespace App\Form\Type;

use App\Entity\VPinMameRegEntries;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class VPinMameRegEntriesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('entries', CollectionType::class, [
            'entry_type' => VPinMameRegEntryType::class,
            #'entry_options' => ['label_format' => 'reg_edit_form.rom']
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => VPinMameRegEntries::class,
        ]);
    }
}