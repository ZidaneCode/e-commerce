<?php

namespace App\Controller;

use App\Form\RestPasswordFormType;
use App\Form\RestPasswordRequestFormType;
use App\Repository\UsersRepository;
use App\Service\SendMailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route(path: '/connexion', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // if ($this->getUser()) {
        //     return $this->redirectToRoute('target_path');
        // }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    #[Route(path: '/deconnecter', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
    #[Route('/oubli-pass', name: 'forgotten_password')]
    public function forgoottenPassword(
        Request $request,
        UsersRepository $usersRepository,
        TokenGeneratorInterface $tokenGenerator,
        EntityManagerInterface $entityManager,
        SendMailService $mail

    ): Response
    {
        $form=$this->createForm(RestPasswordRequestFormType::class);
        $form->handleRequest($request);
        //vérification si l'email envoye ou nn
        if ($form->isSubmitted()&&$form->isValid()) {
            # on cherche l'utilisateur par son (email)
            $user=$usersRepository->findOneByEmail($form->get('email')->getData());
            #on verifie si on a l'utilisateur 
            if ($user) {
                #on génére un autre token de réintialisation
                $token=$tokenGenerator->generateToken();
                $user->setResetToken($token);
                $entityManager->persist($user);
                $entityManager->flush();

                //on génére un lien de réintialisation du mo de passe
                $url=$this->generateUrl('reset_pass',['token'=>$token],UrlGeneratorInterface::ABSOLUTE_URL);
                $context=compact('url','user');
                //on envoye l'maile donc en utilse notre service (SendMailService) que ona creé
                $mail->send(
                    'ne-pas-reponder@e-commerce.com',
                    $user->getEmail(),
                    'Réintialisation du mot de passe',
                    'password_reset',
                    $context
                );
                $this->addFlash('success','Email envoyé avec succès');
                return $this->redirectToRoute('app_login');
       
            }
            //user est null
            $this->addFlash('danger','l\'email ne corresponde a aucun compte');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('security/rest_password_request.html.twig',[
            'requestPassForm'=>$form->createView()
        ]);
       
    }

    #[Route('/oubli-pass/{token}',name:'reset_pass')]
    public function resetPass(
        string $token,
        Request $request,
        UsersRepository $usersRepository,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHacher
       

    ): Response
    {
        //on vérifie si on a ce token dans la base de donneés
        $user=$usersRepository->findOneByResetToken($token);
        if ($user) {
           $form=$this->createForm(RestPasswordFormType::class);
           
           $form->handleRequest($request);
           if ($form->isSubmitted()&&$form->isValid()) {
            # on va efface le token
            $user->setResetToken('');
            $user->setPassword(
                $passwordHacher->hashPassword(
                    $user,
                    $form->get('password')->getData()
                )

            );
            $entityManager->persist($user);
            $entityManager->flush();
            $this->addFlash('success','Mot de passe changéavec succès');
            return $this->redirectToRoute('app_login');
           }
           return $this->render('security/reset_password.html.twig',[
            'passForm'=>$form->createView()
           ]);
        }
        $this->addFlash('danger','joton invalide');
        return $this->redirectToRoute('app_login');


    }

}
