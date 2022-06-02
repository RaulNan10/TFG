<?php

namespace App\Form;

use App\Entity\Assessment;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AssessmentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            
            ->add('description',TextareaType::class, ['label'=>' ','label_attr' => ['class' => 'fs-1']])
            ->add('save', SubmitType::class, array('label' => 'Aceptar', 'attr'=>['class' => 'btn btn-primary mt-2' ]));
;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Assessment::class,
        ]);
    }
}
