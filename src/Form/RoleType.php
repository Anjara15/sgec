<?php

namespace App\Form;

use App\Entity\Role;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RoleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, ['label' => 'Nom'])
            ->add('description', TextareaType::class, ['required' => false, 'label' => 'Description'])
            ->add('permissions', ChoiceType::class, [
                'label' => 'Permissions',
                'choices' => [
                    'Voir courriers' => 'courrier.view',
                    'Créer courriers' => 'courrier.create',
                    'Éditer courriers' => 'courrier.edit',
                    'Valider courriers' => 'courrier.validate',
                    'Transmettre courriers' => 'courrier.transmit',
                    'Archiver courriers' => 'courrier.archive',
                    'Gérer utilisateurs' => 'user.manage',
                    'Gérer services' => 'service.manage',
                    'Gérer paramètres' => 'param.manage',
                    'Exporter' => 'export.run',
                ],
                'multiple' => true,
                'expanded' => true,
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Role::class]);
    }
}
