<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

/**
 * @extends AbstractCrudController<User>
 */
class UserCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    #[\Override]
    public function configureFields(string $pageName): iterable
    {
        /** @var string $avatarsDir */
        $avatarsDir = $this->getParameter('avatars_dir');

        return [
            IdField::new('id')->onlyOnIndex(),
            DateField::new('createdAt'),
            TextField::new('email'),
            TextField::new('pseudo'),
            ArrayField::new('roles'),
            ImageField::new('avatarName', 'Avatar')
                ->setBasePath($avatarsDir)
                ->setUploadedFileNamePattern('[randomhash].[extension]')
                ->setFormTypeOptions(['required' => false])
                ->setUploadDir(\sprintf('public/%s', $avatarsDir))
                ->setTemplatePath('admin/fields/liip_image.html.twig')
                ->setCustomOption('liip_filter', 'avatar_thumb_128'),
        ];
    }
}
