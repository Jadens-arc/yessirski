<?php

namespace App\Controller;

use App\Entity\Track;
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
    public function download(Request $request, EntityManagerInterface $em, Ytdlp $ytdlp): JsonResponse
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
        $em->flush();
        return $this->json([
            "status" => $result ? "success" : "error",
            "uuid" => $track->getUuid(),
            "title" => $track->getTitle(),
        ]);
    }

    #[Route('/search', name: 'app_index_search')]
    public function menu_search(): Response
    {
        return $this->render("Index/index.html.twig");
    }


    #[Route('/tracks/remove/{uuid}', name: 'app_remove_track')]
    public function remove_track(Request $request, EntityManagerInterface $em, string $uuid): Response
    {
        $track = $em->getRepository(Track::class)->findOneBy(["uuid" => Uuid::fromString($uuid)]);
        $track->setActive(false);
        $track->setDeactivationDate(new \DateTime());
        $em->persist($track);
        $em->flush();
        return $this->redirectToRoute("app_index");
    }
}
