<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

class LoginForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('_username', EmailType::class, [
                'trim' => true,
                'label' => 'label.email_address',
                'required' => true,
                'attr' => [
                    'autocomplete' => "email",
                    'autofocus' => true,
                ],
            ])
            ->add('_password', PasswordType::class, [
                'trim' => true,
                'label' => 'label.password',
                'required' => true,
                'attr' => [
                    'autocomplete' => 'current-password',
                ],
            ])
             ->add('_csrf_token', HiddenType::class, [
                'trim' => true,
                'attr' => [
                    'data-controller' => 'csrf-protection',
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'label.login',
                'row_attr' => [
                    'class' => 'text-end',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
