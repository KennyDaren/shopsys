<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Form\Admin\BlogArticleFormType;
use App\Model\Blog\Article\BlogArticleDataFactory;
use App\Model\Blog\Article\BlogArticleFacade;
use App\Model\Blog\Article\BlogArticleGridFactory;
use Shopsys\FrameworkBundle\Component\ConfirmDelete\ConfirmDeleteResponseFactory;
use Shopsys\FrameworkBundle\Component\Domain\AdminDomainFilterTabsFacade;
use Shopsys\FrameworkBundle\Component\Router\Security\Annotation\CsrfProtection;
use Shopsys\FrameworkBundle\Controller\Admin\AdminBaseController;
use Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider;
use Shopsys\FrameworkBundle\Model\Article\Exception\ArticleNotFoundException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BlogArticleController extends AdminBaseController
{
    /**
     * @param \App\Model\Blog\Article\BlogArticleFacade $blogArticleFacade
     * @param \App\Model\Blog\Article\BlogArticleDataFactory $blogArticleDataFactory
     * @param \Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider $breadcrumbOverrider
     * @param \Shopsys\FrameworkBundle\Component\ConfirmDelete\ConfirmDeleteResponseFactory $confirmDeleteResponseFactory
     * @param \App\Model\Blog\Article\BlogArticleGridFactory $blogArticleGridFactory
     * @param \Shopsys\FrameworkBundle\Component\Domain\AdminDomainFilterTabsFacade $adminDomainFilterTabsFacade
     */
    public function __construct(
        private readonly BlogArticleFacade $blogArticleFacade,
        private readonly BlogArticleDataFactory $blogArticleDataFactory,
        private readonly BreadcrumbOverrider $breadcrumbOverrider,
        private readonly ConfirmDeleteResponseFactory $confirmDeleteResponseFactory,
        private readonly BlogArticleGridFactory $blogArticleGridFactory,
        private readonly AdminDomainFilterTabsFacade $adminDomainFilterTabsFacade,
    ) {
    }

    /**
     * @Route("/blog/article/list/", name="admin_blogarticle_list")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction(): Response
    {
        $domainFilterNamespace = 'blog-article';
        $selectedDomainId = $this->adminDomainFilterTabsFacade->getSelectedDomainId($domainFilterNamespace);

        $grid = $this->blogArticleGridFactory->create($selectedDomainId);

        return $this->render('Admin/Content/Blog/Article/list.html.twig', [
            'gridView' => $grid->createView(),
            'domainFilterNamespace' => $domainFilterNamespace,
        ]);
    }

    /**
     * @Route("/blog/article/edit/{id}", requirements={"id" = "\d+"}, name="admin_blogarticle_edit")
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editAction(Request $request, int $id): Response
    {
        $blogArticle = $this->blogArticleFacade->getById($id);
        $blogArticleData = $this->blogArticleDataFactory->createFromBlogArticle($blogArticle);

        $form = $this->createForm(BlogArticleFormType::class, $blogArticleData, [
            'blogArticle' => $blogArticle,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->blogArticleFacade->edit($id, $blogArticleData);

            $this
                ->addSuccessFlashTwig(
                    t('Blog article <strong><a href="{{ url }}">{{ name }}</a></strong> has been updated'),
                    [
                        'name' => $blogArticle->getName(),
                        'url' => $this->generateUrl('admin_blogarticle_edit', ['id' => $blogArticle->getId()]),
                    ],
                );

            return $this->redirectToRoute('admin_blogarticle_list');
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addErrorFlashTwig(t('Please check the correctness of all data filled.'));
        }

        $this->breadcrumbOverrider->overrideLastItem(t('Editing blog article - %name%', ['%name%' => $blogArticle->getName()]));

        return $this->render('Admin/Content/Blog/Article/edit.html.twig', [
            'form' => $form->createView(),
            'blogArticle' => $blogArticle,
        ]);
    }

    /**
     * @Route("/blog/article/new/", name="admin_blogarticle_new")
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function newAction(Request $request): Response
    {
        $blogArticleData = $this->blogArticleDataFactory->create();

        $form = $this->createForm(BlogArticleFormType::class, $blogArticleData, [
            'blogArticle' => null,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $blogArticle = $this->blogArticleFacade->create($blogArticleData);

            $this
                ->addSuccessFlashTwig(
                    t('Blog article <strong><a href="{{ url }}">{{ name }}</a></strong> has been created'),
                    [
                        'name' => $blogArticle->getName(),
                        'url' => $this->generateUrl('admin_blogarticle_edit', ['id' => $blogArticle->getId()]),
                    ],
                );

            return $this->redirectToRoute('admin_blogarticle_list');
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addErrorFlashTwig(t('Please check the correctness of all data filled.'));
        }

        return $this->render('Admin/Content/Blog/Article/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/blog/article/delete/{id}", requirements={"id" = "\d+"}, name="admin_blogarticle_delete")
     * @CsrfProtection
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteAction(int $id): Response
    {
        try {
            $fullName = $this->blogArticleFacade->getById($id)->getName();

            $this->blogArticleFacade->delete($id);

            $this->addSuccessFlashTwig(
                t('Blog article <strong>{{ name }}</strong> has been removed'),
                [
                    'name' => $fullName,
                ],
            );
        } catch (ArticleNotFoundException $ex) {
            $this->addErrorFlash(t('Selected blog article does not exist.'));
        }

        return $this->redirectToRoute('admin_blogarticle_list');
    }

    /**
     * @Route("/blog/article/delete-confirm/{id}", requirements={"id" = "\d+"}, name="admin_blogarticle_deleteconfirm")
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteConfirmAction(int $id): Response
    {
        $message = t('Do you really want to remove this blog article?');

        return $this->confirmDeleteResponseFactory->createDeleteResponse($message, 'admin_blogarticle_delete', $id);
    }
}
