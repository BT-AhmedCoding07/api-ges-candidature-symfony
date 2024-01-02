<?php

namespace App\Controller;

use App\Entity\Candidature;
use App\Repository\CandidatureRepository;
use App\Repository\ReferentielRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;


class CandidatureController extends AbstractController
{

    /**
     * Cette méthode permet de récupérer l'ensemble des candidature.
     *
     * @param SerializerInterface $serializer
     * @return JsonResponse
     */
    #[Route('/api/candidatures', name: 'candidature', methods: ['GET'])]
    //getCandidatureList == index
    public function getCandidatureList(CandidatureRepository $candidatureRepository, SerializerInterface $serializer, Request $request): JsonResponse
    {
        //$referentielList = $referentielRepository->findAll();
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 3);
        $candidatureList =$candidatureRepository ->findAllWithPagination($page, $limit);
        $jsonCandidatureList = $serializer->serialize($candidatureList,'json',['groups'=>'getCandidatures']);
        return new JsonResponse($jsonCandidatureList , Response::HTTP_OK,[], true);
    }
    /**
     * Cette méthode permet de récupérer une candidature en particulier en fonction de son id.
     * @param Candidature $candidature
     * @param SerializerInterface $serializer
     * @return JsonResponse
     */
    #[Route('/api/candidatures/{id}', name:'detailCandidature', methods: ['GET'])]
    //getDetailCandidature == show
    public function getDetailCandidature(int $id,CandidatureRepository $candidatureRepository, SerializerInterface $serializer): JsonResponse
    {
        $candidature = $candidatureRepository->find($id);
        if ($candidature){
            $jsonCandidature = $serializer->serialize($candidature, 'json', ['groups'=>'getCandidatures']);
            return new JsonResponse($jsonCandidature,Response::HTTP_OK ,[], true);
        }
        return new  JsonResponse(null, Response::HTTP_NOT_FOUND);
    }
    /**
     * Cette méthode permet d'insérer un nouveau livre.
     * Exemple de données :
     * {
     *     "status": "en attente",
     *     "idUser": 15,
     *     "idReferentiel": 9
     * }
     * Le paramètre idAUser et idReferentiel est géré "à la main", pour créer l'association
     * entre une candidature et un user (candidat) à un referentiel.
     * S'il ne correspond pas à un candidat valide, alors la candidature sera considéré comme sans candidat.
     * @param Request $request
     * @param SerializerInterface $serializer
     * @param EntityManagerInterface $em
     * @param UrlGeneratorInterface $urlGenerator
     * @return JsonResponse
     */
    #[Route('/api/postuler', name:"postuler", methods: ['POST'])]
    public function postuler(Request $request, SerializerInterface $serializer, EntityManagerInterface $em, ReferentielRepository $referentielRepository,UserRepository $userRepository, UrlGeneratorInterface $urlGenerator,ValidatorInterface $validator): JsonResponse {
        $candidature = $serializer->deserialize($request->getContent(), Candidature::class, 'json');
        // On vérifie les erreurs
        $errors = $validator->validate($candidature);

        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), Response::HTTP_BAD_REQUEST, [], true);
        }
        $em->persist($candidature);
        $em->flush();

        $content = $request->toArray();
        $idUser = $content['idCandidat'] ?? -1;

        $content = $request->toArray();
        $idReferentiel = $content['idReferentiel'] ?? -1;
        $candidature->setUser($userRepository->find($idUser));
        $candidature->setReferentiel($referentielRepository->find($idReferentiel));
        $jsonCandidature = $serializer->serialize($candidature, 'json', ['groups' => 'getCandidatures']);
        $location = $urlGenerator->generate('detailCandidature', ['id' => $candidature->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonCandidature, Response::HTTP_CREATED, ["Location" => $location], true);
    }
    /**
     * Cette méthode permet d'accepter une candidature.
     *
     * @param Candidature $candidature
     * @param EntityManagerInterface $em
     * @return JsonResponse
     */
    #[Route('/api/candidatures/{id}/accepter', name: 'accepter_candidature', methods: ['PUT'])]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisants pour supprimer un referentiel')]
    public function accepterCandidature(Candidature $candidature, EntityManagerInterface $em): JsonResponse
    {
        $candidature->setStatus('acceptée');
        $em->flush();
        return new JsonResponse(['message' => 'Candidature acceptée.'], Response::HTTP_OK);
    }

    /**
     * Cette méthode permet de refuser une candidature.
     *
     * @param Candidature $candidature
     * @param EntityManagerInterface $em
     * @return JsonResponse
     */
    #[Route('/api/candidatures/{id}/refuser', name: 'refuser_candidature', methods: ['PUT'])]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisants pour supprimer un referentiel')]
    public function refuserCandidature(Candidature $candidature, EntityManagerInterface $em): JsonResponse
    {
        $candidature->setStatus('refusée');
        $em->flush();
        return new JsonResponse(['message' => 'Candidature refusée.'], Response::HTTP_OK);
    }
}
