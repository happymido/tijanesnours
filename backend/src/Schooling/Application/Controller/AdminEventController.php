<?php

namespace App\Schooling\Application\Controller;

use App\Schooling\Domain\Entity\EventCategory;
use App\Schooling\Domain\Entity\SchoolEvent;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\AsciiSlugger;

#[Route('/api/v1/admin/events', name: 'api_v1_admin_events_')]
class AdminEventController extends AbstractController
{
    private AsciiSlugger $slugger;

    public function __construct()
    {
        $this->slugger = new AsciiSlugger();
    }

    // ==========================================
    // 1. GESTION DES CATÉGORIES D'ÉVÉNEMENTS
    // ==========================================

    #[Route('/categories', name: 'categories_list', methods: ['GET'])]
    public function listCategories(EntityManagerInterface $em): JsonResponse
    {
        $categories = $em->getRepository(EventCategory::class)->findBy([], ['position' => 'ASC', 'name' => 'ASC']);

        $data = [];
        foreach ($categories as $cat) {
            $data[] = [
                'id' => $cat->getId(),
                'name' => $cat->getName(),
                'slug' => $cat->getSlug(),
                'color' => $cat->getColor(),
                'description' => $cat->getDescription(),
                'position' => $cat->getPosition(),
                'isActive' => $cat->isActive(),
                'eventsCount' => $cat->getEvents()->count(),
                'createdAt' => $cat->getCreatedAt()->format('d/m/Y')
            ];
        }

        return $this->json($data);
    }

    #[Route('/categories', name: 'categories_create', methods: ['POST'])]
    public function createCategory(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $payload = json_decode($request->getContent(), true) ?? [];

        $name = trim((string)($payload['name'] ?? ''));
        if (empty($name)) {
            return $this->json(['error' => 'Le nom de la catégorie est obligatoire.'], Response::HTTP_BAD_REQUEST);
        }

        $slug = trim((string)($payload['slug'] ?? ''));
        if (empty($slug)) {
            $slug = strtolower((string)$this->slugger->slug($name));
        }

        // Ensure unique slug
        $existing = $em->getRepository(EventCategory::class)->findOneBy(['slug' => $slug]);
        if ($existing) {
            $slug .= '-' . substr(uniqid(), -4);
        }

        $category = new EventCategory();
        $category->setName($name)
            ->setSlug($slug)
            ->setColor(!empty($payload['color']) ? trim((string)$payload['color']) : '#8CC63F')
            ->setDescription(!empty($payload['description']) ? trim((string)$payload['description']) : null)
            ->setPosition(isset($payload['position']) ? (int)$payload['position'] : 0)
            ->setIsActive(isset($payload['isActive']) ? (bool)$payload['isActive'] : true);

        $em->persist($category);
        $em->flush();

        return $this->json([
            'status' => 'success',
            'message' => 'Catégorie créée avec succès.',
            'category' => [
                'id' => $category->getId(),
                'name' => $category->getName(),
                'slug' => $category->getSlug(),
                'color' => $category->getColor(),
                'description' => $category->getDescription(),
                'position' => $category->getPosition(),
                'isActive' => $category->isActive()
            ]
        ], Response::HTTP_CREATED);
    }

    #[Route('/categories/{id}', name: 'categories_update', methods: ['PUT', 'POST'])]
    public function updateCategory(int $id, Request $request, EntityManagerInterface $em): JsonResponse
    {
        $category = $em->getRepository(EventCategory::class)->find($id);
        if (!$category) {
            return $this->json(['error' => 'Catégorie introuvable.'], Response::HTTP_NOT_FOUND);
        }

        $payload = json_decode($request->getContent(), true) ?? [];

        if (isset($payload['name']) && !empty(trim((string)$payload['name']))) {
            $category->setName(trim((string)$payload['name']));
        }
        if (isset($payload['color'])) {
            $category->setColor(trim((string)$payload['color']));
        }
        if (isset($payload['description'])) {
            $category->setDescription(trim((string)$payload['description']));
        }
        if (isset($payload['position'])) {
            $category->setPosition((int)$payload['position']);
        }
        if (isset($payload['isActive'])) {
            $category->setIsActive((bool)$payload['isActive']);
        }

        $em->flush();

        return $this->json([
            'status' => 'success',
            'message' => 'Catégorie mise à jour avec succès.'
        ]);
    }

    #[Route('/categories/{id}', name: 'categories_delete', methods: ['DELETE'])]
    public function deleteCategory(int $id, EntityManagerInterface $em): JsonResponse
    {
        $category = $em->getRepository(EventCategory::class)->find($id);
        if (!$category) {
            return $this->json(['error' => 'Catégorie introuvable.'], Response::HTTP_NOT_FOUND);
        }

        $em->remove($category);
        $em->flush();

        return $this->json([
            'status' => 'success',
            'message' => 'Catégorie supprimée avec succès.'
        ]);
    }

    // ==========================================
    // 2. GESTION DES ÉVÉNEMENTS SCOLAIRES
    // ==========================================

    #[Route('', name: 'list', methods: ['GET'])]
    public function listEvents(EntityManagerInterface $em): JsonResponse
    {
        $events = $em->getRepository(SchoolEvent::class)->findBy([], ['eventDate' => 'DESC']);

        $data = [];
        foreach ($events as $event) {
            $cat = $event->getCategory();
            $data[] = [
                'id' => $event->getId(),
                'title' => $event->getTitle(),
                'slug' => $event->getSlug(),
                'description' => $event->getDescription(),
                'eventDate' => $event->getEventDate()->format('Y-m-d\TH:i'),
                'eventDateFormatted' => $event->getEventDate()->format('d/m/Y H:i'),
                'endDate' => $event->getEndDate()?->format('Y-m-d\TH:i'),
                'endDateFormatted' => $event->getEndDate()?->format('d/m/Y H:i'),
                'location' => $event->getLocation(),
                'isPublished' => $event->isPublished(),
                'capacity' => $event->getCapacity(),
                'imageUrl' => $event->getImageUrl(),
                'translations' => $event->getTranslations() ?? [],
                'category' => $cat ? [
                    'id' => $cat->getId(),
                    'name' => $cat->getName(),
                    'color' => $cat->getColor()
                ] : null,
                'createdAt' => $event->getCreatedAt()->format('d/m/Y')
            ];
        }

        return $this->json($data);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function createEvent(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $payload = json_decode($request->getContent(), true) ?? [];

        $title = trim((string)($payload['title'] ?? ''));
        $description = trim((string)($payload['description'] ?? ''));
        $eventDateStr = trim((string)($payload['eventDate'] ?? ''));

        if (empty($title) || empty($description) || empty($eventDateStr)) {
            return $this->json(['error' => 'Le titre, la description et la date sont obligatoires.'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $eventDate = new \DateTimeImmutable($eventDateStr);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Format de date invalide.'], Response::HTTP_BAD_REQUEST);
        }

        $endDate = null;
        if (!empty($payload['endDate'])) {
            try {
                $endDate = new \DateTimeImmutable(trim((string)$payload['endDate']));
            } catch (\Exception $e) {}
        }

        $slug = strtolower((string)$this->slugger->slug($title)) . '-' . date('Ymd');
        $existing = $em->getRepository(SchoolEvent::class)->findOneBy(['slug' => $slug]);
        if ($existing) {
            $slug .= '-' . substr(uniqid(), -4);
        }

        $event = new SchoolEvent();
        $event->setTitle($title)
            ->setSlug($slug)
            ->setDescription($description)
            ->setEventDate($eventDate)
            ->setEndDate($endDate)
            ->setLocation(!empty($payload['location']) ? trim((string)$payload['location']) : 'Centre Maryam / LJM Luxembourg')
            ->setIsPublished(isset($payload['isPublished']) ? (bool)$payload['isPublished'] : true)
            ->setCapacity(isset($payload['capacity']) && is_numeric($payload['capacity']) ? (int)$payload['capacity'] : null)
            ->setImageUrl(!empty($payload['imageUrl']) ? trim((string)$payload['imageUrl']) : null)
            ->setTranslations(!empty($payload['translations']) && is_array($payload['translations']) ? $payload['translations'] : []);

        if (!empty($payload['categoryId'])) {
            $cat = $em->getRepository(EventCategory::class)->find((int)$payload['categoryId']);
            if ($cat) {
                $event->setCategory($cat);
            }
        }

        $em->persist($event);
        $em->flush();

        return $this->json([
            'status' => 'success',
            'message' => 'Événement créé avec succès.',
            'id' => $event->getId()
        ], Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'update', methods: ['PUT', 'POST'])]
    public function updateEvent(int $id, Request $request, EntityManagerInterface $em): JsonResponse
    {
        $event = $em->getRepository(SchoolEvent::class)->find($id);
        if (!$event) {
            return $this->json(['error' => 'Événement introuvable.'], Response::HTTP_NOT_FOUND);
        }

        $payload = json_decode($request->getContent(), true) ?? [];

        if (isset($payload['title']) && !empty(trim((string)$payload['title']))) {
            $event->setTitle(trim((string)$payload['title']));
        }
        if (isset($payload['description']) && !empty(trim((string)$payload['description']))) {
            $event->setDescription(trim((string)$payload['description']));
        }
        if (isset($payload['eventDate']) && !empty(trim((string)$payload['eventDate']))) {
            try {
                $event->setEventDate(new \DateTimeImmutable(trim((string)$payload['eventDate'])));
            } catch (\Exception $e) {}
        }
        if (array_key_exists('endDate', $payload)) {
            if (!empty($payload['endDate'])) {
                try {
                    $event->setEndDate(new \DateTimeImmutable(trim((string)$payload['endDate'])));
                } catch (\Exception $e) {}
            } else {
                $event->setEndDate(null);
            }
        }
        if (isset($payload['location'])) {
            $event->setLocation(trim((string)$payload['location']));
        }
        if (isset($payload['isPublished'])) {
            $event->setIsPublished((bool)$payload['isPublished']);
        }
        if (array_key_exists('capacity', $payload)) {
            $event->setCapacity(!empty($payload['capacity']) && is_numeric($payload['capacity']) ? (int)$payload['capacity'] : null);
        }
        if (array_key_exists('imageUrl', $payload)) {
            $event->setImageUrl(!empty($payload['imageUrl']) ? trim((string)$payload['imageUrl']) : null);
        }
        if (array_key_exists('translations', $payload)) {
            $event->setTranslations(is_array($payload['translations']) ? $payload['translations'] : []);
        }
        if (array_key_exists('categoryId', $payload)) {
            if (!empty($payload['categoryId'])) {
                $cat = $em->getRepository(EventCategory::class)->find((int)$payload['categoryId']);
                $event->setCategory($cat ?: null);
            } else {
                $event->setCategory(null);
            }
        }

        $em->flush();

        return $this->json([
            'status' => 'success',
            'message' => 'Événement mis à jour avec succès.'
        ]);
    }

    #[Route('/{id}/toggle-status', name: 'toggle_status', methods: ['PATCH', 'POST'])]
    public function toggleStatus(int $id, EntityManagerInterface $em): JsonResponse
    {
        $event = $em->getRepository(SchoolEvent::class)->find($id);
        if (!$event) {
            return $this->json(['error' => 'Événement introuvable.'], Response::HTTP_NOT_FOUND);
        }

        $event->setIsPublished(!$event->isPublished());
        $em->flush();

        return $this->json([
            'status' => 'success',
            'isPublished' => $event->isPublished(),
            'message' => $event->isPublished() ? 'Événement publié.' : 'Événement basculé en brouillon.'
        ]);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function deleteEvent(int $id, EntityManagerInterface $em): JsonResponse
    {
        $event = $em->getRepository(SchoolEvent::class)->find($id);
        if (!$event) {
            return $this->json(['error' => 'Événement introuvable.'], Response::HTTP_NOT_FOUND);
        }

        $em->remove($event);
        $em->flush();

        return $this->json([
            'status' => 'success',
            'message' => 'Événement supprimé avec succès.'
        ]);
    }
}
