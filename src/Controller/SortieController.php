<?php

namespace App\Controller;

use App\Entity\Sortie;
use App\Entity\Produit;
use App\Form\SortieType;
use Doctrine\ORM\Mapping\OneToMany;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class SortieController extends AbstractController
{
    /**
     * @Route("/sortie/liste", name="sortie_liste")
     */
    public function index()
    {
        $em = $this->getDoctrine()->getManager();

        $s = new Sortie();
        $form = $this->createForm(SortieType::class, $s, array('action'=>$this->generateUrl('sortie_add')));
        $data['form'] = $form->createView();

        $data['sorties'] = $em->getRepository(Sortie::class)->findAll();
        
        return $this->render('sortie/liste.html.twig',$data);
    }

    /**
     * @Route("/sortie/get/{id}", name="sortie_get")
     */
    public function getSortie($id)
    {
        return $this->render('sortie/liste.html.twig');
    }

    /**
     * @Route("/sortie/add", name="sortie_add")
     */
    public function add(Request $request)
    {
        $s = new Sortie();
        $form = $this->createForm(SortieType::class, $s);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $em = $this->getDoctrine()->getManager();
            $s = $form->getData(); 
            $qs = $s; 
            $p = $em->getRepository(Produit::class)->find($s->getProduit()->getId());

            if($p->getQteStock() < $s->getQteS()){
                $s = new Sortie();
                $form = $this->createForm(SortieType::class, $s, array('action'=>$this->generateUrl('sortie_add')));
                $data['form'] = $form->createView();
                $data['sorties'] = $em->getRepository(Sortie::class)->findAll();
                $data['error_message'] = 'Le stock de '.$qs->getProduit().'s disponible est inférieur à '.$qs->getQteS();

                return $this->render('sortie/liste.html.twig',$data);

            }else{
                $em->persist($s);
                $em->flush();

                //Updating product table
                $stock = $p->getQteStock() - $s->getQteS();
                $p->setQteStock($stock);
                $em->flush();

                return $this->redirectToRoute('sortie_liste');
            }
        }

    }

}
