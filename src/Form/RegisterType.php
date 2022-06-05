<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class RegisterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, array('label'=>'Nombre','empty_data' => 'another user', 'attr' => ['class'=>'d-flex mb-4'], 'label_attr'=> ['class'=>'fw-bold fs-3']))    
            ->add('email',EmailType::class, array('attr' => ['class'=>'d-flex mb-4 text'], 'label_attr'=> ['class'=>'fw-bold fs-3']))
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'The password fields must match.',
                'options' => ['attr' => ['class' => 'password-field']],
                'required' => true,
                'first_options'  => ['label' => 'Contraseña', 'attr' => ['class' => 'd-flex align-self-end'], 'label_attr'=> ['class'=>'fw-bold fs-3']],
                'second_options' => ['label' => 'Repetir contraseña',  'attr' => ['class' => 'd-flex align-self-end'], 'label_attr'=> ['class'=>'fw-bold fs-5']]
            ])
            ->add('image', FileType::class, array('empty_data' => null,'attr'=>['class'=>'d-flex'], 'label' => 'Tu imagen', 'label_attr'=> ['class'=>'fw-bold fs-5 mt-4']))
            ->add('save', SubmitType::class, array('label' => 'Aceptar', 'attr'=>['class' => 'btn btn-primary mt-5 fs-4']));
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
