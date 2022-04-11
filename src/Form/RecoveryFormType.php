<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class RecoveryFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'attr' => [
                    'class' => 'formtext',
                    'placeholder' => 'Ваш email',
                    'autofocus' => 'on',
                    'minlength' => '1',
                    'maxlength' => '40',
                    'autocomplete' => 'on',
                    'value' => $options['last_username']
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Пожалуйста, введите email'
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
                    'maxlength' => '40'
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
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null,
            'last_username' => ''
        ]);
    }
}
