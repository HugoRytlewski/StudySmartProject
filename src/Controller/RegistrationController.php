<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use App\Form\UserType;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class RegistrationController extends AbstractController
{

    #[Route('/home', name: 'app_auth')]
    public function home(Request $request, UserPasswordHasherInterface $userPasswordHasher,  EntityManagerInterface $entityManager): Response
    {
        return $this->render('registration/homeauth.html.twig', []);
    }

    #[Route('/register/step1', name: 'app_register_step1')]
    public function registerStep1(Request $request): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user, [
            'step' => 1
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            // Stockez les données dans la session
            $session = $request->getSession();
            $session->set('registration_data', [
                'firstName' => $form->get('firstName')->getData(),
                'lastName' => $form->get('lastName')->getData()
            ]);


            return $this->redirectToRoute('app_register_step2');
        }

        return $this->render('registration/register_step1.html.twig', [
            'registrationForm' => $form->createView()
        ]);
    }


    #[Route('/register/step2', name: 'app_register_step2')]
    public function registerStep2(
        Request $request,
        SessionInterface $session,
        UserPasswordHasherInterface $userPasswordHasher,
        EntityManagerInterface $entityManager
    ): Response {
        if (!$session->has('registration_data')) {
            return $this->redirectToRoute('app_register_step1');
        }

        $user = new User();
        $registrationData = $session->get('registration_data');
        $user->setFirstName($registrationData['firstName']);
        $user->setLastName($registrationData['lastName']);
        $form = $this->createForm(UserType::class, $user, [
            'step' => 2
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('password')->getData()
                )
            );

            $entityManager->persist($user);
            $entityManager->flush();

            $session->remove('registration_data');

            $this->addFlash('success', 'Votre compte a été créé avec succès !');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('registration/register_step2.html.twig', [
            'registrationForm' => $form->createView()
        ]);
    }

    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {

        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();
        return $this->render('registration/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
