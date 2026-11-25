<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ValidationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('motif', TextareaType::class, [
            'required' => $options['motif_required'],
            'label' => 'Motif / commentaire',
            'attr' => ['rows' => 3],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['motif_required' => false]);
        $resolver->setAllowedTypes('motif_required', 'bool');
    }
}
