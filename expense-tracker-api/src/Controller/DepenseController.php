<?php
namespace App\Controller;

use App\Entity\Depense;
use App\Entity\User;
use Pagerfanta\Pagerfanta;
use App\Repository\DepenseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/api/v1/depenses')]
class DepenseController extends AbstractController
{
    private DepenseRepository $depenseRepository;
    private EntityManagerInterface $entityManager;
    private SerializerInterface $serializer;

    public function __construct(
        DepenseRepository $depenseRepository,
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer
    ) {
        $this->depenseRepository = $depenseRepository;
        $this->entityManager = $entityManager;
        $this->serializer = $serializer;
    }

    #[Route('/', name: 'depense_list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        $page = $request->query->getInt('page', 1);
        $maxPerPage = $request->query->getInt('maxPerPage', 10);

        $queryBuilder = $this->depenseRepository->createQueryBuilder('s');
        $adapter = new QueryAdapter($queryBuilder);
        $pagerfanta = new Pagerfanta($adapter);
        $pagerfanta->setMaxPerPage($maxPerPage);
        $pagerfanta->setCurrentPage($page);

        $items = $pagerfanta->getCurrentPageResults();

        $data = $this->serializer->serialize($items, 'json', ['groups' => 'depense_show']);
        // dd($data);
        $response = [
            'data' => json_decode($data),
            'pagination' => [
                'current_page' => $page,
                'max_per_page' => $maxPerPage,
                'total_items' => $pagerfanta->getNbResults(),
                'total_pages' => $pagerfanta->getNbPages(),
            ]
        ];

        return new JsonResponse($response, Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'depense_show', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $depense = $this->depenseRepository->find($id);
        if (!$depense) {
            return new JsonResponse(['message' => 'Depense non trouvé'], Response::HTTP_NOT_FOUND);
        }
        $response = $this->serializer->serialize($depense, 'json', ['groups' => 'depense_show']);
        return new JsonResponse(json_decode($response), Response::HTTP_OK);
    }

    #[Route('/', name: 'depense_create', methods: ['POST'])]
    public function create(Request $request, ValidatorInterface $validator): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $depense = new Depense();
        $depense->setMontant($data['montant'] ?? null)
                ->setCategories($data['categories'] ?? null)
                ->setDate(new \DateTimeImmutable())
                ->setDescription($data['description'] ?? null)
                ->setCreatedAt(new \DateTimeImmutable())
                ->setUpdatedAt(new \DateTimeImmutable());

        if ($data['id_user'] !== null) {
            $id_user = $this->entityManager->getRepository(User::class)->findOneBy(['id' => $data['id_user']]);
            # code...
            $depense->setUser($id_user);
        }

        $errors = $validator->validate($depense);
        if (count($errors) > 0) {
            return new JsonResponse($errors, Response::HTTP_BAD_REQUEST);
        }

        $this->entityManager->persist($depense);
        $this->entityManager->flush();

        $response = json_decode($this->serializer->serialize($depense, 'json', ['groups' => 'depense_show']),true);
        return new JsonResponse($response, Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'depense_update', methods: ['PUT'])]
    public function update(int $id, Request $request, ValidatorInterface $validator): JsonResponse
    {
        $depense = $this->depenseRepository->find($id);
        if (!$depense) {
            // return $this->json(['message' => 'Depense non trouvé'], Response::HTTP_NOT_FOUND);
            return new JsonResponse(['message' => 'Depense non trouvé'], Response::HTTP_NOT_FOUND);
        }

        $response = json_decode($request->getContent(), true);
        $depense->setMontant($response['montant'] ?? $depense->getMontant())
            ->setDescription($response['description'] ?? $depense->getDescription())
            ->setCategories($response['categories'] ?? $depense->getCategories())
            ->setDate($response['date'] ?? $depense->getDate())
            ->setUpdatedAt(new \DateTimeImmutable());

        $errors = $validator->validate($depense);
        if (count($errors) > 0) {
            // return $this->json($errors, Response::HTTP_BAD_REQUEST);
            return new JsonResponse($errors, Response::HTTP_BAD_REQUEST);
        }

        $this->entityManager->flush();

        $response = $this->serializer->serialize($depense, 'json', ['groups' => 'depense']);
        // return $this->json($response, Response::HTTP_OK);
        return new JsonResponse($response, Response::HTTP_OK, [], true);
    }

    #[Route('/{id}', name: 'depense_delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $depense = $this->depenseRepository->find($id);
        if (!$depense) {
            // return $this->json(['message' => 'Depense non trouvé'], Response::HTTP_NOT_FOUND);            
            return new JsonResponse(['message' => 'Depense non trouvé'], Response::HTTP_NOT_FOUND);
        }

        $this->entityManager->remove($depense);
        $this->entityManager->flush();

        // return $this->json(['message' => 'Depense supprimé avec succès'], Response::HTTP_NO_CONTENT);
        return new JsonResponse(['message' => 'Depense supprimé avec succès'], Response::HTTP_NO_CONTENT);
    }

    // Methode pour filter les depenses 
    #[Route('/filter', methods: ['GET'])]
    public function filtreDepenses(Request $request, EntityManagerInterface $em):
    JsonResponse
    {
    $startDate = $request->query->get('start_date');

    $endDate = $request->query->get('end_date');
    $expenses = $em->getRepository(Depense::class)->createQueryBuilder('e')
    ->where('e.user = :user')
    ->andWhere('e.date BETWEEN :start AND :end')
    ->setParameter('user', $this->getUser())
    ->setParameter('start', new \DateTime($startDate))
    ->setParameter('end', new \DateTime($endDate))
    ->getQuery()
    ->getResult();
    return new JsonResponse($expenses, 200);
    }
}
