<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
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
            ->add('email', TextType::class, [
                'attr' => [
                    'class' => 'formtext',
                    'placeholder' => 'Введите email',
                    'autofocus' => 'on',
                    'minlength' => '1',
                    'maxlength' => '40',
                    'value' => $options['email']
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Пожалуйста, введите email',
                    ]),
                    new Length([
                        'min' => 1,
                        'minMessage' => 'Необходимо ввести не менее {{ limit }} знаков',
                        'max' => 50,
                        'maxMessage' => 'Необходимо ввести не более {{ limit }} знаков',
                    ])
                ]
            ])
            ->add('fio', TextType::class, [
                'attr' => [
                    'class' => 'formtext',
                    'placeholder' => 'Введите ФИО или псевдоним',
                    'minlength' => '1',
                    'maxlength' => '40',
                    'value' => $options['fio']
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Пожалуйста, введите ФИО или псевдоним',
                    ]),
                    new Length([
                        'min' => 1,
                        'minMessage' => 'Необходимо ввести не менее {{ limit }} знаков',
                        'max' => 50,
                        'maxMessage' => 'Необходимо ввести не более {{ limit }} знаков',
                    ]),
                ]
            ])
            ->add('agreeTerms', CheckboxType::class, [
                'mapped' => false,
                'constraints' => [
                    new IsTrue([
                        'message' => 'Вам следует согласиться с правилами сайта',
                    ]),
                ],
            ])
            ->add('plainPassword', PasswordType::class, [
                'mapped' => false,
                'attr' => [
                    'class' => 'formtext',
                    'placeholder' => 'Введите пароль',
                    'autocomplete' => 'new-password',
                    'minlength' => '1',
                    'maxlength' => '40',
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Пожалуйста, введите пароль',
                    ]),
                    new Length([
                        'min' => 1,
                        'minMessage' => 'Пароль должен содержать не менее {{ limit }} знаков',
                        // max length allowed by Symfony for security reasons
                        'max' => 50,
                        'maxMessage' => 'Необходимо ввести не более {{ limit }} знаков',
                    ]),
                ],
            ])
            ->add('addAdmin', CheckboxType::class, [
                'mapped' => false,
                'required' => false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'email' => '',
            'fio' => ''
        ]);
    }
}
