<?php

namespace App\Form;

use App\Entity\Courrier;
use App\Entity\Service;
use App\Entity\User;
use App\Enum\Confidentialite;
use App\Enum\CourrierType as CourrierTypeEnum;
use App\Enum\Priorite;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\File;

class CourrierType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('type', EnumType::class, [
                'class' => CourrierTypeEnum::class,
                'choice_label' => fn (CourrierTypeEnum $t) => $t->label(),
                'label' => 'Type',
            ])
            ->add('objet', TextType::class, ['label' => 'Objet'])
            ->add('description', TextareaType::class, ['required' => false, 'label' => 'Description', 'attr' => ['rows' => 4]])
            ->add('dateDocument', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
                'input' => 'datetime_immutable',
                'label' => 'Date du document',
            ])
            ->add('expediteur', TextType::class, ['label' => 'Expéditeur'])
            ->add('destinataire', TextType::class, ['label' => 'Destinataire'])
            ->add('serviceOrigine', EntityType::class, [
                'class' => Service::class,
                'choice_label' => 'nom',
                'required' => false,
                'placeholder' => '—',
                'label' => 'Service d\'origine',
            ])
            ->add('serviceDestinataire', EntityType::class, [
                'class' => Service::class,
                'choice_label' => 'nom',
                'required' => false,
                'placeholder' => '—',
                'label' => 'Service destinataire',
            ])
            ->add('priorite', EnumType::class, [
                'class' => Priorite::class,
                'choice_label' => fn (Priorite $p) => $p->label(),
                'label' => 'Priorité',
            ])
            ->add('confidentialite', EnumType::class, [
                'class' => Confidentialite::class,
                'choice_label' => fn (Confidentialite $c) => $c->label(),
                'label' => 'Confidentialité',
            ])
            ->add('agentResponsable', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'nomComplet',
                'required' => false,
                'placeholder' => '—',
                'label' => 'Agent responsable',
            ])
            ->add('pieces', FileType::class, [
                'mapped' => false,
                'required' => false,
                'multiple' => true,
                'label' => 'Pièces jointes',
                'constraints' => [
                    new All([
                        new File(maxSize: '10M', mimeTypes: [
                            'application/pdf',
                            'image/*',
                            'application/msword',
                            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                            'application/vnd.ms-excel',
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            'text/plain',
                        ]),
                    ]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Courrier::class]);
    }
}
