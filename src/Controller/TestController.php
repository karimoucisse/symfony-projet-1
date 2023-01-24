<?php

namespace App\Controller;

use App\Entity\Categorie;
use App\Form\CategoryType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TestController extends AbstractController
{
    #[Route('/', name: 'app_test')]
    public function index(EntityManagerInterface $em, Request $request): Response
    {
        //  Création d'un nouvel object pour le formulaire
        $category = new Categorie();
        // creation du formulaire basé sur la class CategoryType et l'object category
        $form = $this->createForm(CategoryType::class, $category);
        // damande au formulaire d'analyser la requete HTTP
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($category); // prepare la sauvegarde
            $em->flush(); // execute la sauvegarde
            $this->addFlash('success', 'Categorie ajoutée');
        }

        $categories = $em->getRepository(Categorie::class)->findAll();
        /**
         * findAll() : récupere toute la table
         * findBy() : findAll avec clause WHERE
         * findOneBy : récupere un élément avec une clause WHERE
         **/

        return $this->render('test/index.html.twig', [
            'categories' => $categories,
            'ajout' => $form->createView(),
        ]);
    }


    #[Route('/category/{id}', name:'category')]
    public function category(Categorie $category = null, EntityManagerInterface $em, Request $request): Response
    {
        if($category == null){
            $this->addFlash('danger', 'Catégories introuvable');
            return $this->redirectToRoute('app_test');
        }
        
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($category); // permet l'ajout et la maj
            $em->flush(); // execute la sauvegarde

            $this->addFlash('success', 'Categorie modifier');
        }

        return $this->render('test/category.html.twig', [
            'category' => $category,
            'modifier' => $form->createView(),
        ]);
    }

    #[Route('/category/delete/{id}', name: 'category_delete')]
    public function delete(Categorie $category = null, EntityManagerInterface $em): Response
    {
        if($category == null){
            $this->addFlash('danger', 'Catégorie introuvable');
        }else {
            $em->remove($category);
            $em->flush();
            $this->addFlash('warning', 'Catégorie supprimée');
        }
        return $this->redirectToRoute('app_test');
    }
}
