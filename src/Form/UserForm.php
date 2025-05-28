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
            ->add('pseudo', null, [
                'help' => 'label.pseudo_only_alnum',
            ])
            ->add('enableCommunityContact', null, [
                'label' => 'user.enable_community_contact'
            ])
            ->add('enablePostNotification', null, [
                'label' => 'user.allow_post_notification'
            ])
            ->add('avatarFile', VichImageType::class, [
                'label' => 'label.profile_image',
                'required' => false,
                'allow_delete' => true,
                'download_uri' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
