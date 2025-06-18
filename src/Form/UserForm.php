<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichImageType;

class UserForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('avatarFile', VichImageType::class, [
                'label' => 'label.avatar',
                'label_attr' => [
                    'class' => 'd-none',
                ],
                'required' => false,
                'allow_delete' => true,
                'download_uri' => false,
                'image_uri' => true,
                'attr' => [
                    'data-controller' => 'image-upload',
                    'accept' => 'image/*',
                ],
                'help' => 'form.help.avatar_upload',
                'row_attr' => [
                    'class' => 'avatar-fieldset mb-3',
                ],
            ])
            ->add('pseudo', null, [
                'label_attr' => [
                    'class' => 'd-none',
                ],
                'help' => 'label.pseudo_only_alnum',
            ])
            ->add('enableCommunityContact', null, [
                'label' => 'label.enable_community_contact',
            ])
            ->add('enablePostNotification', null, [
                'label' => 'label.allow_post_notification',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
