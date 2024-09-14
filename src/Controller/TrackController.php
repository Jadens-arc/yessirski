<?php

namespace App\Controller;

use App\Entity\Track;
use App\Form\TrackType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Uid\Uuid;

class TrackController extends AbstractController
{
    #[Route('/track/{uuid}', name: 'app_track_edit')]
    public function edit(Request $request, EntityManagerInterface $em, $uuid): Response
    {
        $track = $em->getRepository(Track::class)->findOneBy(["uuid" => Uuid::fromString($uuid)]);
        if (!$track) {
            throw new \Exception("Track not found");
        }

        $form = $this->createForm(TrackType::class, $track);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $track = $form->getData();
            $em->persist($track);
            $em->flush();
            return $this->redirectToRoute("app_index");
        }

        return $this->render('track/edit.html.twig', ["form" => $form, "track" => $track]);
    }
    #[Route('/track/{uuid}/delete', name: 'app_track_delete')]
    public function delete(Request $request, EntityManagerInterface $em, $uuid): Response
    {
        $track = $em->getRepository(Track::class)->findOneBy(["uuid" => Uuid::fromString($uuid)]);
        $track->setActive(false);
        $track->setDeactivationDate(new \DateTime());
        $em->persist($track);
        $em->flush();
        return $this->redirectToRoute("app_index");
    }
}
