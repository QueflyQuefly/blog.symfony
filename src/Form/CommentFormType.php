<?php

namespace App\Form;

use App\Entity\Comments;
use PhpParser\Comment;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;


class CommentFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('content', TextareaType::class, [
                'attr' => [
                    'class' => 'commenttextarea',
                    'placeholder' => 'Опишите ваши эмоции :-) (до 500 символов)',
                    'autofocus' => 'on',
                    'minlength' => '1',
                    'maxlength' => '500',
                    'autocomplete' => 'on',
                    'wrap' => 'hard',
                    'spellcheck' => 'true'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Пожалуйста, введите текст комментария'
                    ]),
                    new Length([
                        'min' => 1,
                        'minMessage' => 'Необходимо ввести не менее {{ limit }} знаков',
                        'max' => 600,
                        'maxMessage' => 'Необходимо ввести не более {{ limit }} знаков'
                    ])
                ]
            ])
            ->add('submit', SubmitType::class, [
                'attr' => [
                    'class' => 'formsubmit',
                    'value' => 'Добавить комментарий',
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Comments::class
        ]);
    }
}
