<?php

namespace App\Controller;

use App\Entity\Artist;
use App\Entity\ArtistTrack;
use App\Entity\Play;
use App\Entity\Track;
use App\Form\TrackType;
use App\Service\MetadataManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class TrackController extends AbstractController
{
    #[Route('/track/{uuid}', name: 'app_track_edit')]
    public function edit(Request $request, EntityManagerInterface $em, MetadataManager $manager, $uuid): Response
    {
        $track = $em->getRepository(Track::class)->findOneBy(["uuid" => Uuid::fromString($uuid)]);
        if (!$track) {
            throw new \Exception("Track not found");
        }

        $artist_ids = [];
        foreach ($track->getArtistTracks() as $artistTrack) {
            $artist_ids[] = $artistTrack->getArtist()->getId();
        }

        $form = $this->createForm(TrackType::class, $track);
        $form->get("artists")->setData(implode(",", $artist_ids));
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Track $track */
            $track = $form->getData();
            $artist_ids = explode(",", $form->get("artists")->getData());

            foreach ($track->getArtistTracks() as $artistTrack) {
                $id = $artistTrack->getArtist()->getId();
                if (!in_array($id, $artist_ids)) {
                    $em->remove($artistTrack);
                } else {
                    array_splice($artist_ids, array_search($id, $artist_ids), 1);
                }
            }

            foreach ($artist_ids as $artist_id) {
                $artist = $em->getRepository(Artist::class)->find($artist_id);
                if (!$artist) {
                    throw new \Exception("Arist not found");
                }

                $artistTrack = new ArtistTrack();
                $artistTrack->setArtist($artist);
                $artistTrack->setTrack($track);
                $em->persist($artistTrack);
            }


            $em->persist($track);
            $em->flush();
            $track_id = $track->getId();
            $em->detach($track);
            $em->clear();

            $manager->update_metadata($em->getRepository(Track::class)->find($track_id));
            return $this->redirectToRoute("app_index");
        }

        return $this->render('track/edit.html.twig', ["form" => $form, "track" => $track]);
    }

    #[Route('/track/{uuid}/audio', name: 'app_track_audio')]
    public function getAudio(Request $request, Filesystem $filesystem, EntityManagerInterface $em, $uuid): Response
    {
        $track = $em->getRepository(Track::class)->findOneBy(["uuid" => Uuid::fromString($uuid)]);
        // make a new play entity, attach it to track and to artist
        $play = new Play();
        $play->setTrack($track);
        $em->persist($play);
        $em->flush();

        $path = $this->getParameter('kernel.project_dir') . "/public/" . $track->getPath();
        // This should return the file located in /mySymfonyProject/web/public-resources/TextFile.txt
        // to being viewed in the Browser
        return new BinaryFileResponse($path);
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
