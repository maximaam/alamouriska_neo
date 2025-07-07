<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Wall;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class WallForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('description', TextareaType::class, [
            'label' => 'Another brick in the wall...',
            'help' => 'form.help.wall_input',
            'attr' => [
                'rows' => 8,
            ],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Wall::class,
        ]);
    }
}
