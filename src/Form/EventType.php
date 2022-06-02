<?php

namespace App\Form;

use App\Entity\Event;
use Doctrine\DBAL\Types\TextType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType as TypeTextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EventType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('image', FileType::class, array('empty_data' => null, 'label' => 'Imagen del evento', 'label_attr'=>['class'=>'fs-5 mb-3 fw-bold mt-5' ]))
            ->add('title', TypeTextType::class, array('label_attr'=>['class'=>'fs-5 mb-3 fw-bold']))
            ->add('description',TextareaType::class, array('label_attr'=>['class'=>'mb-3 fs-5 fw-bold']))
            ->add('date', DateType::class, array('label_attr'=>['class'=>'mb-2 fs-5 fw-bold']))
            ->add('save', SubmitType::class, array('label' => 'Aceptar', 'attr'=>['class'=>'btn btn-dark mt-3 fs-4 fw-bold mb-5']));

        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Event::class,
        ]);
    }
}
