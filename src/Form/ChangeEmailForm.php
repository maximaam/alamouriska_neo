<?php

declare(strict_types=1);

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ChangeEmailForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('newEmail', EmailType::class, [
                'trim' => true,
                'attr' => [
                    'placeholder' => 'label.email_address_new',
                    'title' => 'label.email_address_new',
                    'autocomplete' => 'email',
                ],
                'label' => 'label.email_address_new',
                'label_attr' => [
                    'class' => 'd-none',
                ],
                'help' => 'label.email_private',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}
