<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Post;
use App\Enum\PostType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Vich\UploaderBundle\Form\Type\VichImageType;

class PostForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('type', ChoiceType::class, [
                'choices' => PostType::cases(),
                'choice_label' => fn (PostType $type) => \sprintf('post.%s.singular', $type->name),
                'choice_value' => fn (?PostType $type) => $type?->value,
                'placeholder' => 'label.select_type',
                'attr' => [
                    'data-controller' => 'post-form',
                ],
                'label_attr' => [
                    'class' => 'd-none',
                ],
            ])
            ->add('title', null, [
                'attr' => [
                    'disabled' => true,
                    'placeholder' => 'label.title_latin_alphabet',
                ],
                'help' => 'form.help.title_latin_alphabet',
                'label_attr' => [
                    'class' => 'd-none',
                ],
            ])
            ->add('titleArabic', null, [
                'attr' => [
                    'placeholder' => 'label.title_arabic_alphabet',
                    'dir' => 'rtl',
                    'disabled' => true,
                ],
                'help' => 'form.help.title_arabic_alphabet',
                'help_html' => true,
                'label_attr' => [
                    'class' => 'd-none',
                ],
            ])
            ->add('description', null, [
                'attr' => [
                    'class' => '',
                    'placeholder' => 'label.description',
                    'rows' => 7,
                ],
                'label_attr' => [
                    'class' => 'd-none',
                ],
            ])
            ->add('postImageFile', VichImageType::class, [
                'label' => 'Photo (option)',
                'required' => false,
                'allow_delete' => true,
                'download_uri' => false,
                'image_uri' => true,
                'attr' => [
                    'data-controller' => 'image-upload',
                    'accept' => 'image/*',
                ],
            ])
            ->add('question', null, [
                'label' => 'label.post_is_question',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Post::class,
            'constraints' => [
                new Callback(self::validateInputs(...)),
            ],
        ]);
    }

    public static function validateInputs(Post $post, ExecutionContextInterface $context): void
    {
        if (PostType::word === $post->getType() && str_contains($post->getTitle(), ' ')) {
            $context->buildViolation('word_contains_spaces')
            ->atPath('title')
            ->addViolation();
        }
    }
}
