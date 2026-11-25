<?php

namespace App\Form;

use App\Entity\Service;
use App\Enum\CourrierStatut;
use App\Enum\CourrierType;
use App\Enum\Priorite;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CourrierSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('reference', TextType::class, ['required' => false, 'label' => 'Référence'])
            ->add('objet', TextType::class, ['required' => false, 'label' => 'Objet'])
            ->add('type', EnumType::class, [
                'class' => CourrierType::class,
                'required' => false,
                'placeholder' => 'Tous',
                'choice_label' => fn (CourrierType $t) => $t->label(),
                'label' => 'Type',
            ])
            ->add('expediteur', TextType::class, ['required' => false, 'label' => 'Expéditeur'])
            ->add('destinataire', TextType::class, ['required' => false, 'label' => 'Destinataire'])
            ->add('service', EntityType::class, [
                'class' => Service::class,
                'choice_label' => 'nom',
                'required' => false,
                'placeholder' => 'Tous',
                'label' => 'Service',
            ])
            ->add('statut', EnumType::class, [
                'class' => CourrierStatut::class,
                'required' => false,
                'placeholder' => 'Tous',
                'choice_label' => fn (CourrierStatut $s) => $s->label(),
                'label' => 'Statut',
            ])
            ->add('priorite', EnumType::class, [
                'class' => Priorite::class,
                'required' => false,
                'placeholder' => 'Toutes',
                'choice_label' => fn (Priorite $p) => $p->label(),
                'label' => 'Priorité',
            ])
            ->add('date_debut', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
                'label' => 'Du',
            ])
            ->add('date_fin', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
                'label' => 'Au',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'method' => 'GET',
            'csrf_protection' => false,
        ]);
    }
}
