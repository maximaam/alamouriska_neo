<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Post;
use App\Enum\PostType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
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
            ])
            ->add('title')
            ->add('description')
            ->add('postImageFile', VichImageType::class, [
                'label' => 'Photo',
                'required' => false,
                'allow_delete' => true,
            ])
            ->addEventListener(FormEvents::SUBMIT, static function (FormEvent $event): void {
                $post = $event->getData();
                if (\in_array($post->getType(), [PostType::proverb->value, PostType::joke->value], true)) {
                    $post->setTitle(mb_strimwidth((string) $post->getDescription(), 0, 100, '...'));
                }
            });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Post::class,
        ]);
    }
}
