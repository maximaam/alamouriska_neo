<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Form\ChangeEmailForm;
use App\Form\UserForm;
use App\Repository\UserRepository;
use App\Security\EmailVerifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

#[Route('/user', name: 'app_user_', priority: 3)]
final class UserController extends AbstractController
{
    public function __construct(
        private readonly EmailVerifier $emailVerifier,
        private readonly TranslatorInterface $translator,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    #[Route('/show', name: 'show', methods: ['GET'])]
    public function show(): Response
    {
        return $this->render('user/show.html.twig');
    }

    #[Route('/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(#[CurrentUser] User $user, Request $request): Response
    {
        $form = $this->createForm(UserForm::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();
            $this->addFlash('success', 'flash.profile_edit_success');

            return $this->redirectToRoute('app_user_show', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/change-email', name: 'change_email')]
    public function changeEmail(#[CurrentUser] User $user, Request $request): Response
    {
        $form = $this->createForm(ChangeEmailForm::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $newEmail = $form->get('newEmail')->getData();
            $user->setPendingEmail($newEmail);
            $this->entityManager->flush();

            $template = \sprintf('emails/new_email_confirmation.%s.html.twig', $request->getLocale());

            /** @var string $appNotifier */
            $appNotifier = $this->getParameter('app_notifier_email');
            /** @var string $appName */
            $appName = $this->getParameter('app_name');

            $emailTemplate = (new TemplatedEmail())
                ->from(new Address($appNotifier, $appName))
                ->to($newEmail)
                ->subject($this->translator->trans('email.new_email_confirmation.subject', ['%app_name%' => $appName]))
                ->htmlTemplate($template)
                ->context([
                    'pseudo' => $user->getPseudo(),
                ]);

            $this->emailVerifier->sendEmailConfirmation(
                'app_user_verify_new_email',
                $user,
                $emailTemplate,
                $newEmail
            );

            $this->addFlash('success', 'flash.new_email_verification_sent');

            return $this->redirectToRoute('app_user_show');
        }

        return $this->render('user/change_email.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/verify/new-email', name: 'verify_new_email')]
    public function verifyNewEmail(Request $request, UserRepository $userRepository): Response
    {
        if (null === $id = $request->query->get('id')) {
            return $this->redirectToRoute('app_registration_register');
        }

        if (null === $user = $userRepository->find($id)) {
            return $this->redirectToRoute('app_registration_register');
        }

        try {
            $this->emailVerifier->handleNewEmailConfirmation($request, $user);
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('verify_email_error', $this->translator->trans($exception->getReason(), [], 'VerifyEmailBundle'));

            return $this->redirectToRoute('app_registration_register');
        }

        $this->addFlash('success', 'flash.new_email_verification_success');

        return $this->redirectToRoute('app_security_login');
    }

    #[Route('/delete', name: 'delete', methods: ['GET', 'POST'])]
    public function delete(#[CurrentUser] User $user, Request $request, TokenStorageInterface $tokenStorage): Response
    {
        if (Request::METHOD_POST === $request->getMethod() && $this->isCsrfTokenValid('delete'.$user->getId(), $request->getPayload()->getString('_token'))) {
            $this->entityManager->remove($user);
            $this->entityManager->flush();
            // Clear the runnig session
            $tokenStorage->setToken(null);
            $request->getSession()->invalidate();
            $this->addFlash('success', 'flash.user_deleted_success');

            return $this->redirectToRoute('app_frontend_index', [], Response::HTTP_SEE_OTHER);
        }

        $this->addFlash('warning', 'flash.user_delete_warning');

        return $this->render('user/delete.html.twig', [
            'user' => $user,
        ]);
    }
}
