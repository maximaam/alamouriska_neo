<?php

declare(strict_types=1);

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;

class PostCommentForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('comment', TextareaType::class, [
                'attr' => [
                    'class' => 'p-2',
                    'placeholder' => 'Comment...',
                ],
                'label_attr' => [
                    'class' => 'd-none',
                ],
                'constraints' => [
                    new Length([
                        'min' => 10,
                        'max' => 1000,
                        'minMessage' => 'Your comment must be at least {{ limit }} characters long.',
                        'maxMessage' => 'Your comment cannot be longer than {{ limit }} characters.',
                    ]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
