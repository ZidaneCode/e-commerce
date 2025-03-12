<?php
namespace App\Controller\Admin;

use App\Entity\Images;
use App\Entity\Products;
use App\Form\ProductsFormType;
use App\Repository\ProductsRepository;
use App\Service\PictureService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/admin/produits',name:'admin_products_')]
class ProductsController extends AbstractController
{
    #[Route('/',name:'index')]
    public function index(ProductsRepository $productsRepository):Response
    {
        $produits=$productsRepository->findAll();
        return $this->render('admin/products/index.html.twig',compact('produits'));
    }

    #[Route('/ajout',name:'add')]
    public function add(Request $request,EntityManagerInterface $em,SluggerInterface $slugger,
    PictureService $pictureService):Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        //on cree un nouveau produit
        $product=new Products;
        //on cree le formulair
        $productform=$this->createForm(ProductsFormType::class,$product);

       //apres ca il faut récupérer les informations de formulaire donc 
       //on utilise request de httpFondation et pour fiare la mise ajour dans la base de donneés 
       //on utilise EntityManagerInterface

       //on traite la requet de formulaire 
        $productform->handleRequest($request);
        //on vérifie si le formulaire est soumis et valide
        if ($productform->isSubmitted()&&$productform->isValid()) {
            //on récupère les images;
            $images=$productform->get('images')->getData();
            
            foreach($images as $image)
            {
                //on définie le dossier de destination
                $folder='products';
               
                //on apple le service d'ajout
                $fichier=$pictureService->add($image,$folder,300,300);
                $img=new Images();
                $img->setName($fichier);
                $product->addImage($img);

            }
            # on génére le slug
            $slug=$slugger->slug($product->getName());
            $product->setSlug($slug);

            //on arrondit le prix;
            $prix=$product->getPrice()*100;
            $product->setPrice($prix);

            //on stock
            $em->persist($product);//on valide 
            $em->flush();//on execute

            $this->addFlash('success','Produit ajouté avec succès.');
            //on redirige
            return $this->redirectToRoute('admin_products_index');

        }
         #deux méthodes possible pour envoyer le formlulaire cree a la page (template) a afficher
       // return $this->render('admin/products/add.html.twig',[
       //  'productform'=>$productform->createView()
       // ]);
        return $this->renderForm('admin/products/add.html.twig',compact('productform'));
 
    }
    #[Route('/edition/{id}',name:'edit')]
    public function edit(Products $product, Request $request,EntityManagerInterface $em,
    SluggerInterface $slugger,PictureService $pictureService):Response
    {
        //on vérifie si l'utilisateur peut éditer avec voter
        $this->denyAccessUnlessGranted('PRODUCT_EDIT',$product);
        //on divise le prix sur 100;
        $prix=$product->getPrice()/100;
        $product->setPrice($prix);

        //on cree le formulair
        $productform=$this->createForm(ProductsFormType::class,$product);
       

       //apres ca il faut récupérer les informations de formulaire donc 
       //on utilise request de httpFondation et pour fiare la mise ajour dans la base de donneés 
       //on utilise EntityManagerInterface

       //on traite la requet de formulaire 
        $productform->handleRequest($request);
        //on vérifie si le formulaire est soumis et valide
        if ($productform->isSubmitted()&&$productform->isValid()) {
            //on récupère les images;
            $images=$productform->get('images')->getData();
            
            foreach($images as $image)
            {
                //on définie le dossier de destination
                $folder='products';
               
                //on apple le service d'ajout
                $fichier=$pictureService->add($image,$folder,300,300);
                $img=new Images();
                $img->setName($fichier);
                $product->addImage($img);

            }
            # on génére le slug
            $slug=$slugger->slug($product->getName());
            $product->setSlug($slug);

            //on arrondit le prix;
            $prix=$product->getPrice()*100;
            $product->setPrice($prix);

            //on stock
            $em->persist($product);//on valide 
            $em->flush();//on execute

            $this->addFlash('success','Produit modifié avec succès');
            //on redirige
            return $this->redirectToRoute('admin_products_index');

        }
         #deux méthodes possible pour envoyer le formlulaire cree a la page (template) a afficher
        return $this->render('admin/products/edit.html.twig',[
         'productform'=>$productform->createView(),
         'product'=> $product
        ]);
       // return $this->renderForm('admin/products/edit.html.twig',compact('productform'));
     }

    #[Route('/suppression/{id}',name:'delete')]
    public function delete(Products $product):Response
    {
         //on vérifie si l'utilisateur peut supprimer avec voter
         $this->denyAccessUnlessGranted('PRODUCT_DELETE',$product);
        return $this->render('admin/products/index.html.twig');
    }

    #[Route('/suppression/image/{id}',name:'delete_image',methods:['DELETE'])]
    public function deleteImage(Images $image,EntityManagerInterface $em,
    Request $request,PictureService $pictureService):JsonResponse
    {
        //on récupère la donnee de la requete
        $data=json_decode($request->getContent(),true);
        if($this->isCsrfTokenValid('delete'.$image->getId(),$data['_token']))
        {
            //le token est valide 
            //on récupère le nome de l'image
            $name=$image->getName();
            if ($pictureService->delete($name,'products',300,300)) {
                //on suprime l'image de la base de donnees
                $em->remove($image);
                $em->flush();
                return new JsonResponse(['success'=>true],200);
            }
            return new JsonResponse(['error'=>'Erreur de supprission'],400);
        }

        return new JsonResponse(['error'=>'Token invalide'],400);
    }


}