<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Post;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class PostCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Post::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            // IdField::new('id'),
            DateField::new('createdAt'),
            ChoiceField::new('type'),
            TextField::new('title'),
            TextEditorField::new('description'),
            // ImageField::new('postImageName'),
            ImageField::new('postImageName', 'Image')
                ->setBasePath('/uploads/images/posts')
                ->setUploadedFileNamePattern('[randomhash].[extension]')
                ->setFormTypeOptions(['required' => false])
                // ->onlyOnIndex()
                ->setUploadDir('/public/uploads/images/posts')
                ->setTemplatePath('admin/fields/liip_image.html.twig'),
        ];
    }
}
