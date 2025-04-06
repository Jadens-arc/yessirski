<?php

namespace App\Controller;

use App\Entity\Artist;
use App\Entity\ArtistTrack;
use App\Entity\Track;
use App\Service\MetadataManager;
use App\Service\Ytdlp;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Uid\Uuid;

class IndexController extends AbstractController
{
    #[Route('/', name: 'app_index')]
    public function index(Request $request, EntityManagerInterface $em): Response
    {
        $tracks = $em->getRepository(Track::class)
            ->createQueryBuilder('t')
            ->where('t.active = true')
            ->orderBy('t.created', 'DESC')
            ->getQuery()
            ->getResult();
        return $this->render("Index/index.html.twig", ["tracks" => $tracks]);
    }

    #[Route('/api/search', name: 'app_search')]
    public function search(Request $request, Ytdlp $ytdlp): JsonResponse
    {
        $request_data = json_decode($request->getContent(), true);
        try {
            $title = $ytdlp->get_video_title($request_data["url"]);
        } catch (\Exception $e) {
            return $this->json([
                "status" => "error",
                "message" => $e->getMessage(),
            ]);
        }
        return $this->json([
            "status" => "success",
            "data" => [
                "title" => $title
            ]
        ]);
    }

    #[Route('/api/download', name: 'app_download')]
    public function download(Request $request, EntityManagerInterface $em, Ytdlp $ytdlp, MetadataManager $manager): JsonResponse
    {
        $request_data = json_decode($request->getContent(), true);

        $track = new Track();
        $track->setTitle($request_data["title"]);
        $track->setPath("tracks/" . $track->getUuid()->toBase32() . ".mp3");
        try {
            $result = $ytdlp->download_video($track, $request_data["url"]);
        } catch (\Exception $e) {
            return $this->json([
                "status" => "error",
                "message" => $e->getMessage(),
            ]);
        }
        $em->persist($track);

        foreach ($request_data["artists"] as $artist) {
            $artist = $em->getRepository(Artist::class)->find($artist);
            $artst_track = new ArtistTrack();
            $artst_track->setArtist($artist);
            $artst_track->setTrack($track);
            $em->persist($artst_track);
        }

        $em->flush();
        $manager->update_title($track);
        return $this->json([
            "status" => $result ? "success" : "error",
            "uuid" => $track->getUuid(),
            "title" => $track->getTitle(),
        ]);
    }

    #[Route('/api/dropdown/artists', name: 'app_dropdown_artists')]
    public function dropdownArtists(Request $request, EntityManagerInterface $em, MetadataManager $manager): JsonResponse
    {
        $term = $request->query->get('term');
        $page = $request->query->getInt('page', 0);
        $page_size = 10;

        $artists = $em
            ->getRepository(Artist::class)
            ->createQueryBuilder('a')
            ->orWhere('a.name LIKE :term')
            ->setParameter('term', sprintf('%%%s%%', $term))
            ->setFirstResult($page * $page_size)
            ->setMaxResults($page_size + 1) // So we know if there are more to fetch
            ->getQuery()
            ->getResult();

        $results = [];
        foreach($artists as $artist) {
            $results[] = [
                'id' => $artist->getId(),
                'text' => sprintf('%s',
                    $artist->getName(),
                )
            ];
        }

        return new JsonResponse([
            'results' => array_slice($results, 0, $page_size),
            'pagination' => [
                'more' => count($results) == $page_size + 1
            ]
        ]);

        return $this->json($results);
    }


    #[Route('/search', name: 'app_index_search')]
    public function menu_search(): Response
    {
        return $this->render("Index/index.html.twig");
    }
}
