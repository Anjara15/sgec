<?php

namespace App\Form;

use App\Entity\Role;
use App\Entity\Service;
use App\Entity\User;
use App\Enum\UserStatut;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, ['label' => 'Nom'])
            ->add('prenom', TextType::class, ['label' => 'Prénom'])
            ->add('email', EmailType::class, ['label' => 'Email'])
            ->add('plainPassword', PasswordType::class, [
                'mapped' => false,
                'required' => $options['require_password'],
                'label' => $options['require_password'] ? 'Mot de passe' : 'Nouveau mot de passe (optionnel)',
                'constraints' => $options['require_password'] ? [
                    new NotBlank(message: 'Mot de passe obligatoire.'),
                    new Length(min: 8, minMessage: 'Au moins {{ limit }} caractères.'),
                ] : [new Length(min: 8, minMessage: 'Au moins {{ limit }} caractères.')],
            ])
            ->add('roles', ChoiceType::class, [
                'label' => 'Rôles Symfony',
                'choices' => [
                    'Administrateur' => 'ROLE_ADMIN',
                    'Agent de courrier' => 'ROLE_AGENT_COURRIER',
                    'Responsable de service' => 'ROLE_RESPONSABLE',
                    'Utilisateur' => 'ROLE_USER',
                ],
                'multiple' => true,
                'expanded' => true,
            ])
            ->add('customRoles', EntityType::class, [
                'class' => Role::class,
                'choice_label' => 'nom',
                'multiple' => true,
                'expanded' => true,
                'required' => false,
                'label' => 'Rôles métier',
            ])
            ->add('service', EntityType::class, [
                'class' => Service::class,
                'choice_label' => 'nom',
                'required' => false,
                'placeholder' => 'Aucun',
                'label' => 'Service',
            ])
            ->add('statut', EnumType::class, [
                'class' => UserStatut::class,
                'choice_label' => fn (UserStatut $s) => $s->label(),
                'label' => 'Statut',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'require_password' => true,
        ]);
        $resolver->setAllowedTypes('require_password', 'bool');
    }
}
