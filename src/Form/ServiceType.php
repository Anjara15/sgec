<?php

namespace App\Form;

use App\Entity\Service;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ServiceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, ['label' => 'Nom'])
            ->add('description', TextareaType::class, ['required' => false, 'label' => 'Description'])
            ->add('responsable', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'nomComplet',
                'required' => false,
                'placeholder' => 'Aucun',
                'label' => 'Responsable',
            ])
            ->add('statut', ChoiceType::class, [
                'choices' => ['Actif' => 'actif', 'Inactif' => 'inactif'],
                'label' => 'Statut',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Service::class]);
    }
}
