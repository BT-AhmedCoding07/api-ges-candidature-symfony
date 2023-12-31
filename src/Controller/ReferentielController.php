<?php

namespace App\Controller;

use App\Entity\Referentiel;
use App\Repository\ReferentielRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

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
    /**
     * Cette méthode permet de supprimer un referentiel par rapport à son id.
     * @param Referentiel $referentiel
     * @param EntityManagerInterface $em
     * @return JsonResponse
     */
    #[Route('/api/referentiels/{id}', name:'deleteRefentiel', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisants pour mettre à jour un referentiel')]
   //deleteReferentiel = destroy
    public function deleteReferentiel(Referentiel $referentiel,  EntityManagerInterface $em) : JsonResponse
    {
        $em->remove($referentiel);
        $em->flush();
        return  new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
    /**
     * Cette méthode permet d'insérer un nouveau referentiel.
     * Exemple de données :
     * {
     *     "libelle": "Data Science",
     *     "description": "Cet réferentiel décrit la formation sur le Data Science et ces compétences à développer",
     *     "echeances": "9 mois"
     * }
     *
     * @param Request $request
     * @param SerializerInterface $serializer
     * @param EntityManagerInterface $em
     * @param UrlGeneratorInterface $urlGenerator
     * @return JsonResponse
     */

    #[Route('/api/referentiels', name:"createReferentiel", methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisants pour mettre à jour un referentiel')]
    //createReferentiel = post
    public function createReferentiel(Request $request, SerializerInterface $serializer, EntityManagerInterface $em, UrlGeneratorInterface $urlGenerator,ValidatorInterface $validator): JsonResponse
    {
        $referentiel = $serializer->deserialize($request->getContent(), Referentiel::class, 'json');
        // On vérifie les erreurs
        $errors = $validator->validate($referentiel);

        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), Response::HTTP_BAD_REQUEST, [], true);
        }

        $em->persist($referentiel);
        $em->flush();

        $jsonReferentiel = $serializer->serialize($referentiel, 'json', ['groups' => 'getReferentiels']);

        $location = $urlGenerator->generate('detailReferentiel', ['id' => $referentiel->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonReferentiel, Response::HTTP_CREATED, ["Location" => $location], true);
    }
    /**
     * Cette méthode permet de mettre à jour un réferentiel en fonction de son id.
     *
     * Exemple de données :
     * {
     * "libelle": "Data Science P1",
     * "description": "Cet réferentiel décrit la formation sur le Data Science et ces compétences à développer",
     * "echeances": "9 mois"
     * }
     * @param Request $request
     * @param SerializerInterface $serializer
     * @param EntityManagerInterface $em
     * @return JsonResponse
     */
    #[Route('/api/referentiels/{id}', name:"updateReferentiel", methods:['PUT'])]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisants pour mettre à jour un referentiel')]
    //updateReferentiel = update
    public function updateReferentiel(Request $request, SerializerInterface $serializer, Referentiel $currentReferentiel, EntityManagerInterface $em,): JsonResponse {
        $updatedReferentiel = $serializer->deserialize($request->getContent(), Referentiel::class, 'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $currentReferentiel]);
        $em->persist($updatedReferentiel);
        $em->flush();
        return new JsonResponse([
            "message " => "Update successfully",
                "status"  => Response::HTTP_NO_CONTENT
            ]);
    }
}
