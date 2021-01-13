<?php

namespace App\Controller;

use App\Security\CardAuthenticator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    /**
     * @Route("/login", name="app_login")
     */
    public function login(AuthenticationUtils $authenticationUtils, SessionInterface $session): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('index');
        }

        $attempts = $session->get(CardAuthenticator::LOGIN_ATTEMPTS, 0);

        $error = $authenticationUtils->getLastAuthenticationError();

        return $this->render('security/user_login.html.twig', ['error' => $error, 'attempts' => $attempts]);
    }

    /**
     * @Route("/logout", name="app_logout")
     */
    public function logout(SessionInterface $session)
    {
        $session->invalidate();
    }
}
