<?php

namespace App\Form;

use App\Entity\ParametreSysteme;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ParametreSystemeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('cle', TextType::class, ['label' => 'Clé', 'disabled' => $options['edit_mode']])
            ->add('valeur', TextareaType::class, ['label' => 'Valeur', 'attr' => ['rows' => 3]])
            ->add('description', TextareaType::class, ['required' => false, 'label' => 'Description']);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ParametreSysteme::class,
            'edit_mode' => false,
        ]);
    }
}
