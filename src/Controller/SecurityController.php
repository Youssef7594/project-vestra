<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

use App\Entity\Users;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use App\Form\LoginFormType;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasher;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Encoder\PasswordHasherInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;


class SecurityController extends AbstractController
{
     // Route de la page de connexion
    #[Route('/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils, Request $request, UserPasswordHasherInterface $passwordHasher, CsrfTokenManagerInterface $csrfTokenManager, Security $security, EntityManagerInterface $entityManager): Response
    {
    // Si l'utilisateur est déjà connecté, on le redirige
    if ($security->getUser()) {
        return $this->redirectToRoute('app_home');
    }

    // On récupère les erreurs d'authentification s'il y en a
    $error = $authenticationUtils->getLastAuthenticationError();
    $lastUsername = $authenticationUtils->getLastUsername();

    // Si le formulaire est soumis et valide
    if ($request->isMethod('POST')) {
        $email = $request->request->get('email');
        $password = $request->request->get('password');

        // Récupérer l'utilisateur par son email
        $user = $entityManager->getRepository(Users::class)->findOneBy(['email' => $email]);

        if ($user && password_verify($password, $user->getPassword())) {
            // Si l'email et le mot de passe sont valides, on authentifie l'utilisateur
            // Utilisation de Symfony Security pour connecter l'utilisateur
            return $this->redirectToRoute('app_home');
        } else {
            // Sinon, afficher un message d'erreur
            $this->addFlash('error', 'Identifiants incorrects.');
        }
    }

    return $this->render('security/login.html.twig', [
        'last_username' => $lastUsername,
        'error' => $error,
    ]);
    
    }



     // Route pour déconnexion
    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
         // Cette méthode peut rester vide : elle sera interceptée par la configuration du firewall dans security.yaml
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }





     // Route pour l'inscription de nouveaux utilisateurs
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager): Response
    {
        $user = new Users();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            // Vérification si le nom d'utilisateur est déjà pris
            $existingUser = $entityManager->getRepository(Users::class)->findOneBy(['username' => $user->getUsername()]);
            if ($existingUser) {
                $this->addFlash('error', 'Le nom d\'utilisateur est déjà pris.');
                return $this->redirectToRoute('app_register');
            }
    
            // Vérification si l'email est déjà pris
            $existingEmail = $entityManager->getRepository(Users::class)->findOneBy(['email' => $user->getEmail()]);
            if ($existingEmail) {
                $this->addFlash('error', 'L\'adresse email est déjà utilisée.');
                return $this->redirectToRoute('app_register');
            }
    
            // Validation du mot de passe (minimum 7 caractères et 2 chiffres)
            $plainPassword = $user->getPlainPassword();
            if (strlen($plainPassword) < 7 || !preg_match('/\d.*\d/', $plainPassword)) {
                $this->addFlash('error', 'Le mot de passe doit contenir au moins 7 caractères et 2 chiffres.');
                return $this->redirectToRoute('app_register');
            }
    
            // Hachage du mot de passe
            $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
            $user->setPassword($hashedPassword); // On définit le mot de passe haché dans l'entité User
    
            // Vérifier si un rôle a été choisi
            $role = $user->getRole();
            if (!$role || !in_array($role, ['TALENT', 'RECRUTEUR'])) {
                // Si aucun rôle n'est défini ou un rôle invalide a été sélectionné
                $this->addFlash('error', 'Vous devez choisir un rôle valide.');
                return $this->redirectToRoute('app_register');
            }
    
            // Sauvegarde de l'utilisateur en base de données
            $entityManager->persist($user);
            $entityManager->flush();
    
            // Message de succès
            $this->addFlash('success', 'Votre compte a été créé avec succès.');
    
            // Redirige l'utilisateur vers la page de connexion
            return $this->redirectToRoute('app_login');
        }
    
        // Si le formulaire n'est pas soumis ou valide, on renvoie la vue avec les erreurs éventuelles
        return $this->render('security/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }


    
}
