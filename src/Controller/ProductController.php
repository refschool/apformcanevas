<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/admin",name="admin_")
 */
class ProductController extends AbstractController
{
    /**
     * liste le produits d'une catégorie
     * @Route("/product/detail/{id}", name="detailProduit")
     */
    public function detailProduit(Product $product): Response
    {
        return $this->render('product/detailProduit.html.twig', [
            'product' => $product,
        ]);
    }


    /**
     * @Route("/product/add",name="ajoutProduit")
     */
    public function addProduct(Request $request, EntityManagerInterface $em, SluggerInterface $slugger): Response
    {
        $product = new Product;
        $form = $this->createForm(ProductFormType::class, $product);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $product->setSlug($slugger->slug($product->getName()));
            $idCategory = $form['category']->getData()->getId();

            $em->persist($product);
            $em->flush();

            $this->addFlash('success', 'Produit ajouté avec succès');

            return $this->redirectToRoute('admin_categoryProduct', ['id' => $idCategory]);
        }

        return $this->render('product/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }


    /**
     * @Route("/product/edit/{id}",name="editProduit")
     */
    public function editProduct(Request $request, EntityManagerInterface $em, $id): Response
    {

        $product = $em->getRepository(Product::class)->find($id);
        $form = $this->createForm(ProductFormType::class, $product);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $idCategory = $product->getCategory()->getId();

            $em->persist($product);
            $em->flush();

            $this->addFlash('success', 'Produit édité avec succès');
            return $this->redirectToRoute('admin_categoryProduct', ['id' => $idCategory]);
        }

        return $this->render('product/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }


    /**
     * @Route("/product/delete/{id}",name="deleteProduit")
     */
    public function deleteProduct(Product $product, EntityManagerInterface $em)
    {
        $idCategory = $product->getCategory()->getId(); //comment trouver l'id category ?

        $em->remove($product);
        $em->flush();

        $this->addFlash('success', 'Produit effacé avec succès');
        return $this->redirectToRoute('admin_categoryProduct', ['id' => $idCategory]);
    }
}
