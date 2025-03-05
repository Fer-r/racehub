<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/auth')]
final class AuthController extends AbstractController
{
    #[Route('/register', name: 'app_register', methods: ['GET'])]
    public function showRegister(): Response
    {
        return $this->render('main/register.html.twig');
    }
    
    #[Route('/register', name:'app_register_submit', methods: ['POST'])]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        try {
            $password = $request->request->get('password');
            $passwordConfirm = $request->request->get('password_confirm');

            if ($password !== $passwordConfirm) {
                $this->addFlash('error', 'Las contraseñas no coinciden');
                return $this->redirectToRoute('app_register');
            }

            $user = new User();
            $user->setEmail($request->request->get('email'));
            $user->setName($request->request->get('nombre'));
            $user->setBanned(0);
            $user->setPassword($userPasswordHasher->hashPassword($user, $password));

            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'Usuario registrado correctamente');
            return $this->redirectToRoute('app_login');
            
        } catch (\Exception $e) {
            $this->addFlash('error', 'Error al registrar el usuario');
            return $this->redirectToRoute('app_register');
        }
    }
    #[Route('/login', name: 'app_login', methods: ['GET'])]
    public function showLogin(AuthenticationUtils $authenticationUtils): Response
    {
        return $this->render('main/login.html.twig', [
            'error' => $authenticationUtils->getLastAuthenticationError(),
            'last_username' => $authenticationUtils->getLastUsername(),
        ]);
    }

    #[Route('/login/submit', name: 'app_login_submit', methods: ['POST'])]
    public function login(AuthenticationUtils $authenticationUtils): JsonResponse
    {
        $error = $authenticationUtils->getLastAuthenticationError();
        
        if ($error) {
            return $this->json([
                'error' => $error->getMessage()
            ], Response::HTTP_UNAUTHORIZED);
        }

        $user = $this->getUser();
        
        if (!$user) {
            return $this->json([
                'error' => 'Invalid credentials'
            ], Response::HTTP_UNAUTHORIZED);
        }

        return $this->json([
            'user' => $user,
            'message' => 'Logged in successfully'
        ], Response::HTTP_OK, [], ['groups' => 'user:read']);
    }

    #[Route('/logout', name: 'app_logout', methods: ['POST'])]
    public function logout(): JsonResponse
    {
        // The actual logout is handled by the security system
        return $this->json([
            'message' => 'Logged out successfully'
        ], Response::HTTP_OK);
    }
}
