<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationForm;
use App\Repository\UserRepository;
use App\Security\EmailVerifier;
use App\Utils\RedirectIfAuthenticatedTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
// use Symfony\Component\Security\Http\Attribute\IsGranted;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

// #[IsGranted('IS_ANONYMOUS')]
#[Route(name: 'app_registration_', priority: 7)]
final class RegistrationController extends AbstractController
{
    use RedirectIfAuthenticatedTrait;

    public function __construct(
        private readonly EmailVerifier $emailVerifier,
        private readonly TranslatorInterface $translator,
    ) {
    }

    #[Route('/register', name: 'register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager, Security $security): Response
    {
        if (null !== $redirect = $this->redirectIfAuthenticated($security)) {
            return $redirect;
        }

        $user = new User();
        $form = $this->createForm(RegistrationForm::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData();

            // encode the plain password
            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));

            $entityManager->persist($user);
            $entityManager->flush();

            // generate a signed url and email it to the user
            $template = \sprintf('emails/registration_confirmation.%s.html.twig', $request->getLocale());

            /** @var string $appNotifier */
            $appNotifier = $this->getParameter('app_notifier_email');
            /** @var string $appName */
            $appName = $this->getParameter('app_name');

            $this->emailVerifier->sendEmailConfirmation(
                'app_registration_verify_email',
                $user,
                (new TemplatedEmail())
                    ->from(new Address($appNotifier, $appName))
                    ->to((string) $user->getEmail())
                    ->subject($this->translator->trans('email.registration_confirmation.subject', ['%app_name%' => $appName]))
                    ->htmlTemplate($template)
                    ->context([
                        'pseudo' => $user->getPseudo(),
                    ])
            );

            $this->addFlash('success', 'flash.registration_success');

            return $this->redirectToRoute('app_home_index');
        }

        return $this->render('registration/register.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/verify/email', name: 'verify_email')]
    public function verifyUserEmail(Request $request, TranslatorInterface $translator, UserRepository $userRepository): Response
    {
        $id = $request->query->get('id');

        if (null === $id) {
            return $this->redirectToRoute('app_registration_register');
        }

        $user = $userRepository->find($id);

        if (null === $user) {
            return $this->redirectToRoute('app_registration_register');
        }

        // validate email confirmation link, sets User::isVerified=true and persists
        try {
            $this->emailVerifier->handleEmailConfirmation($request, $user);
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('verify_email_error', $translator->trans($exception->getReason(), [], 'VerifyEmailBundle'));

            return $this->redirectToRoute('app_registration_register');
        }

        $this->addFlash('success', 'flash.registration_email_verification_success');

        return $this->redirectToRoute('app_security_login');
    }
}
