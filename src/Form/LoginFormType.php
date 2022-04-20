<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class LoginFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('_username', EmailType::class, [
                'label' => 'Введите е-mail:',
                'attr'  => [
                    'class'        => 'formtext',
                    'placeholder'  => 'Ваш email',
                    'autofocus'    => 'on',
                    'minlength'    => '1',
                    'maxlength'    => '40',
                    'autocomplete' => 'on',
                    'value'        => $options['last_username']
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Пожалуйста, введите email'
                    ]),
                    new Length([
                        'min'        => 1,
                        'minMessage' => 'Необходимо ввести не менее {{ limit }} знаков',
                        'max'        => 50,
                        'maxMessage' => 'Необходимо ввести не более {{ limit }} знаков',
                    ])
                ]
            ])
            ->add('_password', PasswordType::class, [
                'label' => 'Введите пароль:',
                'attr'  => [
                    'class'        => 'formtext',
                    'placeholder'  => 'Ваш пароль',
                    'minlength'    => '1',
                    'maxlength'    => '40',
                    'autocomplete' => 'off'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Пожалуйста, введите пароль'
                    ]),
                    new Length([
                        'min'        => 1,
                        'minMessage' => 'Необходимо ввести не менее {{ limit }} знаков',
                        'max'        => 50,
                        'maxMessage' => 'Необходимо ввести не более {{ limit }} знаков',
                    ])
                ]
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Войти',
                'attr' => [
                    'class' => 'formsubmit'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class'    => User::class,
            'last_username' => ''
        ]);
    }
}
