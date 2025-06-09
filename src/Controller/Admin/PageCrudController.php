<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Page;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class PageCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Page::class;
    }

    public function configureFields(string $pageName): iterable
    {
        /** @var string $pagesDir */
        $pagesDir = $this->getParameter('pages_dir');

        return [
            IdField::new('id')->onlyOnIndex(),
            TextField::new('title'),
            TextEditorField::new('description'),
            ImageField::new('pageImageName', 'Image')
                ->setBasePath($pagesDir)
                ->setUploadedFileNamePattern('[randomhash].[extension]')
                ->setFormTypeOptions(['required' => false])
                ->setUploadDir(\sprintf('public/%s', $pagesDir))
                ->setTemplatePath('admin/fields/liip_image.html.twig')
                ->setCustomOption('liip_filter', 'page_image'),
        ];
    }
}
