<?php
namespace App\Controller;

use App\Entity\Site;
use App\Entity\User;
use Pagerfanta\Pagerfanta;
use App\Repository\UsersRepository;
use Doctrine\ORM\EntityManagerInterface;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[Route('/api/v1/users')]
class UserController extends AbstractController
{
    private UsersRepository $usersRepository;
    private EntityManagerInterface $entityManager;
    private SerializerInterface $serializer;

    public function __construct(
        UsersRepository $usersRepository,
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer
    ) {
        $this->usersRepository = $usersRepository;
        $this->entityManager = $entityManager;
        $this->serializer = $serializer;
    }

    #[Route('/', name: 'users_list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        $page = $request->query->getInt('page', 1);
        $maxPerPage = $request->query->getInt('maxPerPage', 10);

        $queryBuilder = $this->usersRepository->createQueryBuilder('u');
        $adapter = new QueryAdapter($queryBuilder);
        $pagerfanta = new Pagerfanta($adapter);
        $pagerfanta->setMaxPerPage($maxPerPage);
        $pagerfanta->setCurrentPage($page);

        $items = $pagerfanta->getCurrentPageResults();
        $data = $this->serializer->serialize($items, 'json', ['groups' => 'user']);

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

    #[Route('/{id}', name: 'users_show', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $user = $this->usersRepository->find($id);
        if (!$user) {
            return $this->json(['message' => 'Utilisateur non trouvé'], Response::HTTP_NOT_FOUND);
        }

        $data = $this->serializer->serialize($user, 'json', ['groups' => 'user']);
        // return $this->json($data, Response::HTTP_OK);
        return new JsonResponse(json_decode($data), Response::HTTP_OK);
    }

    #[Route('/register', name: 'users_create', methods: ['POST'])]
    public function create(Request $request, ValidatorInterface $validator, UserPasswordHasherInterface $passwordHasher): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $user = new User();

        $user->setNom($data['nom'] ?? null)
                ->setRoles($data['roles'] ?? null)
                ->setTelephone($data['telephone'] ?? null)
                ->setFonction($data['fonction'] ?? null)
                ->setCreatedAt(new \DateTimeImmutable())
                ->setUpdatedAt(new \DateTimeImmutable());
    


        if ($data['password'] !== null) {
            # code...
            $hashedPassword = $passwordHasher->hashPassword($user, $data['password']);
            $user->setPassword($hashedPassword);
        }
        // Set related entities if necessary
        // Example: $user->setIdPartenaires($data['id_partenaire'] ?? null);
        // Example: $user->setIdSite($data['id_site'] ?? null);

        $errors = $validator->validate($user);
        if (count($errors) > 0) {
            return $this->json($errors, Response::HTTP_BAD_REQUEST);
        }

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $data = $this->serializer->serialize($user, 'json', ['groups' => 'user']);
        // return $this->json($data, Response::HTTP_CREATED);
        return new JsonResponse($data, Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'users_update', methods: ['PUT'])]
    public function update(int $id, Request $request, ValidatorInterface $validator): JsonResponse
    {
        $user = $this->usersRepository->find($id);
        if (!$user) {
            // return $this->json(['message' => 'Utilisateur non trouvé'], Response::HTTP_NOT_FOUND);
            return new JsonResponse(['message' => 'Utilisateur non trouvé'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);
        $user->setNom($data['nom'] ?? $user->getNom())
                ->setRoles($data['roles'] ?? $user->getRoles())
                ->setPassword($data['password'] ?? $user->getPassword())
                ->setTelephone($data['telephone'] ?? $user->getTelephone())
                ->setFonction($data['fonction'] ?? $user->getFonction())
                ->setUpdatedAt(new \DateTimeImmutable());

        // Update related entities if necessary
        // Example: $user->setIdPartenaires($data['id_partenaire'] ?? $user->getIdPartenaires());
        // Example: $user->setIdSite($data['id_site'] ?? $user->getIdSite());

        $errors = $validator->validate($user);
        if (count($errors) > 0) {
            // return $this->json($errors, Response::HTTP_BAD_REQUEST);
            return new JsonResponse($errors, Response::HTTP_BAD_REQUEST);
        }

        $this->entityManager->flush();

        $data = $this->serializer->serialize($user, 'json', ['groups' => 'user']);
        // return $this->json($data, Response::HTTP_OK);
        return new JsonResponse(json_decode($data), Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'users_delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $user = $this->usersRepository->find($id);
        if (!$user) {
            // return $this->json(['message' => 'Utilisateur non trouvé'], Response::HTTP_NOT_FOUND);
            return new JsonResponse(['message' => 'Utilisateur non найд'], Response::HTTP_NOT_FOUND);
        }

        $this->entityManager->remove($user);
        $this->entityManager->flush();

        // return $this->json(['message' => 'Utilisateur supprimé avec succès'], Response::HTTP_NO_CONTENT);
        return new JsonResponse(['message' => 'Utilisateur supprimé avec succès'], Response::HTTP_NO_CONTENT);
    }
}
