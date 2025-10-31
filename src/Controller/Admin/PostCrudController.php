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

/**
 * @extends AbstractCrudController<Post>
 */
class PostCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Post::class;
    }

    #[\Override]
    public function configureFields(string $pageName): iterable
    {
        /** @var string $postsDir */
        $postsDir = $this->getParameter('posts_dir');

        return [
            IdField::new('id')->onlyOnIndex(),
            DateField::new('createdAt'),
            ChoiceField::new('type'),
            TextField::new('title'),
            TextEditorField::new('description'),
            ImageField::new('postImageName', 'Image')
                ->setBasePath($postsDir)
                ->setUploadedFileNamePattern('[randomhash].[extension]')
                ->setFormTypeOptions(['required' => false])
                ->setUploadDir(\sprintf('public/%s', $postsDir))
                ->setTemplatePath('admin/fields/liip_image.html.twig')
                ->setCustomOption('liip_filter', 'post_image'),
        ];
    }
}
