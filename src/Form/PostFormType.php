<?php

namespace App\Form;

use App\Entity\Post;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class PostFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Заголовок: ',
                'attr'  => [
                    'class'       => 'addpostname',
                    'placeholder' => 'Добавьте заголовок поста. Количество символов: от 20 до 180',
                    'autofocus'   => 'on',
                    'minlength'   => '1',
                    'maxlength'   => '120',
                    'value'       => $options['title']
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Пожалуйста, введите заголовок поста'
                    ]),
                    new Length([
                        'min'        => 1,
                        'minMessage' => 'Необходимо ввести не менее {{ limit }} знаков',
                        'max'        => 125,
                        'maxMessage' => 'Необходимо ввести не более {{ limit }} знаков',
                    ])
                ]
            ])
            ->add('image', FileType::class, [
                'label'    => 'Картинка: ',
                'mapped'   => false, 
                'required' => false,
                'attr'     => [
                    'class' => 'addpostimg'
                ]
            ])
            ->add('content', TextareaType::class, [
                'label' => 'Содержание поста:',
                'attr'  => [
                    'class'       => 'addposttextarea',
                    'placeholder' => 'Добавление содержания. Количество символов: от 20 до 4000 с пробелами',
                    'minlength'   => '1',
                    'maxlength'   => '4000',
                    'spellcheck'  => 'true',
                    'wrap'        => 'hard',
                    'value'       => $options['content']
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Пожалуйста, введите содержание поста'
                    ]),
                    new Length([
                        'min'        => 1,
                        'minMessage' => 'Необходимо ввести не менее {{ limit }} знаков',
                        'max'        => 5000,
                        'maxMessage' => 'Необходимо ввести не более {{ limit }} знаков',
                    ])
                ]
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Отправить',
                'attr'  => [
                    'class' => 'formsubmit',
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Post::class,
            'title'      => '',
            'content'    => ''
        ]);
    }
}
