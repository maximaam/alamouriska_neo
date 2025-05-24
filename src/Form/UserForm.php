<?php

namespace App\Form;

use App\Entity\Image;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichImageType;

class UserForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email')
            //->add('roles')
            //->add('password')
            //->add('isVerified')
            ->add('displayName')
            ->add('enableCommunityContact', null, [
                'label' => 'user.allow_member_contact'
            ])
            ->add('enablePostNotification', null, [
                'label' => 'user.allow_post_notification'
            ])
            ->add('avatarFile', VichImageType::class, [
                'mapped' => false,
                'label' => 'Photo',
                'required' => false,
                'allow_delete' => true,
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
