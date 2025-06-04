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
                'label' => 'form.label.avatar',
                'required' => false,
                'allow_delete' => true,
                'download_uri' => false,
                'help' => 'form.help.avatar_upload',
                'row_attr' => ['class' => 'avatar-fieldset mb-3'],
            ])
            ->add('pseudo', null, [
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
