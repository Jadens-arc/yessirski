<?php

namespace App\Controller;

use App\Entity\Artist;
use App\Form\ArtistType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ArtistController extends AbstractController
{
    #[Route('/artist', name: 'app_artist')]
    public function index(EntityManagerInterface $em): Response
    {
        $artists = $em->getRepository(Artist::class)
            ->createQueryBuilder("a")
            ->addOrderBy("a.name")
            ->getQuery()
            ->getResult();
        return $this->render('artist/index.html.twig', ["artists" => $artists]);
    }

    #[Route('/artist/new', name: 'app_artist_new')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $artist = new Artist();
        $form = $this->createForm(ArtistType::class, $artist);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $artist = $form->getData();
            $em->persist($artist);
            $em->flush();
            return $this->redirectToRoute("app_artist");
        }

        return $this->render('artist/new.html.twig', ["form" => $form, "artist" => $artist]);
    }

    #[Route('/artist/{id}', name: 'app_artist_edit')]
    public function edit(Request $request, EntityManagerInterface $em, $id): Response
    {
        $artist = $em->getRepository(Artist::class)->find($id);
        if (!$artist) {
            throw new \Exception("Artist not found");
        }

        $form = $this->createForm(ArtistType::class, $artist);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $artist = $form->getData();
            $em->persist($artist);
            $em->flush();
        }

        return $this->render('artist/new.html.twig', ["form" => $form, "artist" => $artist]);
    }
}
