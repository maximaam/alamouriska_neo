<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Vich\UploaderBundle\Form\Type\VichImageType;

class RegistrationForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'trim' => true,
                'attr' => [
                    'placeholder' => 'label.email_address',
                    'title' => 'label.email_address',
                    'autocomplete' => 'email',
                ],
                'label' => 'label.email_address',
                'label_attr' => [
                    'class' => 'd-none',
                ],
                'help' => 'label.email_private',
            ])
            ->add('plainPassword', PasswordType::class, [
                // instead of being set onto the object directly,
                // this is read and encoded in the controller
                'mapped' => false,
                'trim' => true,
                'attr' => [
                    'placeholder' => 'label.password',
                    'title' => 'label.password',
                    'autocomplete' => 'new-password',
                ],
                'label' => 'label.password',
                'label_attr' => [
                    'class' => 'd-none',
                ],
                'help' => 'label.password_constraints',
                'constraints' => [
                    new NotBlank(message: 'password_required'),
                    new Length(min: 6, max: 64, minMessage: 'password_min_length', maxMessage: 'password_max_length'),
                ],
            ])
            ->add('pseudo', null, [
                'trim' => true,
                'attr' => [
                    'placeholder' => 'label.pseudo',
                    'title' => 'label.pseudo',
                ],
                'label' => 'label.pseudo',
                'label_attr' => [
                    'class' => 'd-none',
                ],
                'help' => 'label.pseudo_only_alnum',
            ])
            ->add('avatarFile', VichImageType::class, [
                'label' => 'form.label.avatar',
                'label_attr' => [
                    'class' => 'd-none',
                ],
                'required' => false,
                'allow_delete' => true,
                'download_uri' => false,
                'image_uri' => true,
                'attr' => [
                    'data-controller' => 'image-upload',
                    'placeholder' => 'form.label.avatar',
                ],
                'help' => 'form.help.avatar_upload',
                'row_attr' => [
                    'class' => 'avatar-fieldset mb-3',
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
