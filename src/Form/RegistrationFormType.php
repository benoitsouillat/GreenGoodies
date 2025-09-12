<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstname', TextType::class, [
                'required' => true,
                'label' => 'Prénom',
                'row_attr' => ['class' => 'form-group'],
            ])
            ->add('lastname', TextType::class, [
                'required' => true,
                'label' => 'Nom',
                'row_attr' => ['class' => 'form-group'],
            ])
            ->add('email', TextType::class, [
                'required' => true,
                'label' => 'Adresse email',
                'row_attr' => ['class' => 'form-group'],
            ])
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'Les mot de passes ne correspondent pas.',
                'options' => [
                    'attr' => [
                        'class' => 'password-field'
                    ]
                ],
                'first_options'  => [
                    'label' => 'Mot de passe',
                    'row_attr' => ['class' => 'form-group'],
                    'constraints' => [
                        new NotBlank([
                            'message' => 'Veuillez entrer un mot de passe',
                        ]),
                        new Length([
                            'min' => 6,
                            'minMessage' => 'Votre mot de passe doit contenir au moins {{ limit }} caractères',
                            // max length allowed by Symfony for security reasons
                            'max' => 4096,
                        ]),
                    ],
                ],
                'second_options' => [
                    'label' => 'Confirmer le mot de passe',
                    'row_attr' => ['class' => 'form-group'],
                    'constraints' => [
                        new NotBlank([
                            'message' => 'Veuillez confirmer votre mot de passe',
                        ]),
                  ],
                ],
                'required' => true,
                'mapped' => false,
            ])
            ->add('CGU', CheckboxType::class, [
                'mapped' => false,
                'required' => true,
                'label' => 'J\'accepte les CGU de GreenGoodies',
                'constraints' => [
                    new IsTrue([
                        'message' => 'Vous devez accepter les conditions générales d\'utilisation.',
                    ]),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
