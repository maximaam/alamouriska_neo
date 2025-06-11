<?php

declare(strict_types=1);

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

// use Symfony\Component\Validator\Constraints\NotCompromisedPassword;
// use Symfony\Component\Validator\Constraints\PasswordStrength;

class ChangePasswordForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'options' => [
                    'attr' => [
                        'autocomplete' => 'new-password',
                    ],
                    'label_attr' => [
                        'class' => 'd-none',
                    ],
                ],
                'first_options' => [
                    'constraints' => [
                        new NotBlank([
                            'message' => 'Please enter a password',
                        ]),
                        new Length([
                            'min' => 6,
                            'minMessage' => 'Your password should be at least {{ limit }} characters',
                            // max length allowed by Symfony for security reasons
                            'max' => 32,
                        ]),
                        // new PasswordStrength(),
                        // new NotCompromisedPassword(),
                    ],
                    'label' => 'label.new_password',
                    'attr' => [
                        'placeholder' => 'label.new_password',
                        'title' => 'label.new_password',
                    ],
                ],
                'second_options' => [
                    'label' => 'label.repeat_password',
                    'attr' => [
                        'placeholder' => 'label.repeat_password',
                        'title' => 'label.repeat_password',
                    ],
                    'label_attr' => [
                        'class' => 'd-none',
                    ],
                ],
                'invalid_message' => 'The password fields must match.',
                // Instead of being set onto the object directly,
                // this is read and encoded in the controller
                'mapped' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}
