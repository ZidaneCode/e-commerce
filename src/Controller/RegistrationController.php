<?php

namespace App\Controller;

use App\Entity\Users;
use App\Form\RegistrationFormType;
use App\Repository\UsersRepository;
use App\Security\UsersAthenticatorAuthenticator;
use App\Service\JWTService;
use App\Service\SendMailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class RegistrationController extends AbstractController
{
    #[Route('/inscription', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, 
    UserAuthenticatorInterface $userAuthenticator, UsersAthenticatorAuthenticator $authenticator, 
    EntityManagerInterface $entityManager,SendMailService $mail,JWTService $jwt): Response
    {
        $user = new Users();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $entityManager->persist($user);
            $entityManager->flush();
            // do anything else you need here, like send an email

            //on cree le JWT de l'utilisateur
            //on cree le header
            $header=[
                    "alg"=> "HS256",
                    "typ"=>"JWT"
            ];
            //on cree le payload
            $payload=[
                'user_id'=> $user->getId()
            ];

            //on génére le token
            $token=$jwt->generate($header,$payload,$this->getParameter('app.jwtsecret'));
           
            //on envoie un Email
            $mail->send(
                       'ne-pas-reponder.e-commerce@zidane.com',
                       $user->getEmail(),
                       'Activation de votre compte E_commerce',
                       'register',
                       compact('user','token')
        );

            return $userAuthenticator->authenticateUser(
                $user,
                $authenticator,
                $request
            );
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
    #[Route('/verif/{token}',name:'verify_user')]
    public function verifyUser($token ,JWTService $jwt,UsersRepository $usersRepository,EntityManagerInterface $em): Response
    {
        //on vérifie si le token est valide ,n'a pas expiré et n'à pas été modifié
        if( $jwt->isValide($token)&&!$jwt->isExpired($token)&&$jwt->check($token,$this->getParameter('app.jwtsecret')))
        {
            //on recupére le payloade
            $payload=$jwt->getPayload($token);
            //on récupere  l'utilisateur de token
            $user=$usersRepository->find($payload['user_id']);
            //on vérife si l'utilisateur existe dans la base de données et son compte n'a pas encour activé
            if ($user&&!$user->getIsVerified()) {
                $user->setIsVerified(true);
                $em->flush($user);
                $this->addFlash('success','votre compte est validé');
                return $this->redirectToRoute('profile_index');
            }

        }
        //ici un problème se pose dans le token 
        $this->addFlash('danger','le token est invalide ou expiré',null);
        return $this->redirectToRoute('app_login');
    }

    #[Route('/renvoiverif',name:'resend_verif')]
    public function resendVerif(JWTService $jwt,SendMailService $mail,UsersRepository $usersRepository):Response
    {
        $user=$this->getUser();
        if(!$user){
            $this->addFlash('danger','vous devez éter connecté pour acceder a cette page');
            return $this->redirectToRoute('app_login');

        }
        if ($user->getIsVerified()) {
            $this->addFlash('warning','cet utilisateur est déjà activé');
            return $this->redirectToRoute('profile_index');
        }
        //on cree le JWT de l'utilisateur
            //on cree le header
        $header=[
                "alg"=> "HS256",
                "typ"=>"JWT"
        ];
        //on cree le payload
        $payload=[
            'user_id'=> $user->getId()
        ];

        //on génére le token
        $token=$jwt->generate($header,$payload,$this->getParameter('app.jwtsecret'));
       
        //on envoie un Email
        $mail->send(
                   'ne-pas-reponder@e-commerce.com',
                   $user->getEmail(),
                   'Activation de votre compte E_commerce',
                   'register',
                   compact('user','token')
    );
                $this->addFlash('success',' E-mail de confirmation envoyer vérifie ta boite mail');
                return $this->redirectToRoute('app_main');

    }
}
