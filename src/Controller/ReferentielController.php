<?php

namespace App\Controller;

use App\Entity\Referentiel;
use App\Repository\ReferentielRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class ReferentielController extends AbstractController
{
    /**
     * Cette méthode permet de récupérer l'ensemble des referentiels.
     *
     * @param ReferentielRepository $referentielRepository
     * @param SerializerInterface $serializer
     * @return JsonResponse
     */
    #[Route('/api/referentiels', name: 'referentiel', methods: ['GET'])]
   //getReferentielList == index
    public function index(ReferentielRepository $referentielRepository, SerializerInterface $serializer, Request $request): JsonResponse
    {
        //$referentielList = $referentielRepository->findAll();
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 3);
        $referentielList =$referentielRepository ->findAllWithPagination($page, $limit);
        $jsonReferentielList = $serializer->serialize($referentielList,'json', ['groups' => 'getReferentiels']);
        return new JsonResponse($jsonReferentielList, Response::HTTP_OK,[], true);
    }
    /**
     * Cette méthode permet de récupérer un referentiel en particulier en fonction de son id.
     *
     * @param Referentiel $referentiel
     * @param SerializerInterface $serializer
     * @return JsonResponse
     */
    #[Route('/api/referentiels/{id}', name:'detailReferentiel', methods: ['GET'])]
    //getDetailReferentiel == show
    public function getDetailReferentiel(int $id,ReferentielRepository $referentielRepository, SerializerInterface $serializer): JsonResponse
    {
        $referentiel = $referentielRepository->find($id);
        if ($referentiel){
            $jsonReferentiel = $serializer->serialize($referentiel, 'json', ['groups'=>'getReferentiels']);
            return new JsonResponse( $jsonReferentiel,Response::HTTP_OK ,[], true);
        }
        return new  JsonResponse(null, Response::HTTP_NOT_FOUND);
    }

}
