<?php

namespace App\Controller;

use App\Entity\Categorie;
use App\Entity\Produit;
use App\Form\ProduitType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProduitController extends AbstractController
{
    #[Route('/produit', name: 'app_produit')]
    public function index(EntityManagerInterface $em, Request $request): Response
    {
        $produit = new Produit();
        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){

            $imageFile = $form->get('image')->getData();

            // this condition is needed because the 'brochure' field is not required
            // so the PDF file must be processed only when a file is uploaded
            if ($imageFile) {
                $newFilename = uniqid().'.'.$imageFile->guessExtension();

                // Move the file to the directory where brochures are stored
                try {
                    $imageFile->move(
                        $this->getParameter('uploads_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    $this->addFlash('danger', $e->getMessage());
                    return $this->redirectToRoute('app_produit');
                }

                // updates the 'imageFilename' property to store the PDF file name
                // instead of its contents
                $produit->setimage($newFilename);
            }

            $em->persist($produit);
            $em->flush();
            $this->addFlash('success', 'produit créer');
        }

        return $this->render('produit/index.html.twig', [
            'controller_name' => 'ProduitController',
            'ajout' => $form->createView(),
        ]);
    }

    #[Route('/{categorie}/produits', name: 'produits')]
    public function produits(Categorie $categorie= null, EntityManagerInterface $em): Response 
    {
        if($categorie == null){
            $produits = $em->getRepository(Produit::class)->findAll();
        }else {
            $produits = $em->getRepository(Produit::class)->findBy($categorie);
        }

        return $this->render('produit/produits.html.twig', [
            'produits' => $produits
        ]);
    }

    #[Route('/produit/{id}', name: 'produit')]
    public function produit(Produit $produit= null, EntityManagerInterface $em, Request $request): Response
    {
        if($produit == null) {
            $this->addFlash('danger', 'Produit introuvable');
            return $this->redirectToRoute('app_produit');
        }
        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $em->persist($produit);
            $em->flush();
            $this->addFlash('success', 'Produit modifier');
        }

        return $this->render('produit/produit.html.twig', [
            'produit' => $produit,
            'modifier' => $form->createView(),
        ]);
    }

    #[Route('/produit_delete/{id}', name: 'produit_delete')]
    public function delete(Produit $produit= null, EntityManagerInterface $em, Request $request): Response
    {
        if($produit == null){
            $this->addFlash('danger', 'Produit introuvable');
        }else {
            $em->remove($produit);
            $em->flush();
        }
        return $this->redirectToRoute('app_produit');
    }



}
