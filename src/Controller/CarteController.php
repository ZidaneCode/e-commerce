<?php

    namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
#[Route('/carte',name:'cart_')]
class CarteController extends AbstractController
{
    #[Route('/',name:'index')]
    public function index():Response
    {
        return $this->render('carte/index.html.twig',[]);
    }
    #[Route('/add/{id}',name:'add')]
    public function add($id ,SessionInterface $session):Response
    {
        $panier=$session->get("panier",[]);
        if (!empty($panier[$id])) {
            $panier[$id]++;
        }
        else{
            $panier[$id]= 1;
        }
        dd($panier);
    }
}