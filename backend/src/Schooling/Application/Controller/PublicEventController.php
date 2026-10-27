<?php

namespace App\Schooling\Application\Controller;

use App\Schooling\Domain\Entity\EventCategory;
use App\Schooling\Domain\Entity\SchoolEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/v1/public', name: 'api_v1_public_events_')]
class PublicEventController extends AbstractController
{
    #[Route('/event-categories', name: 'categories_list', methods: ['GET'])]
    public function getCategories(EntityManagerInterface $em): JsonResponse
    {
        $categories = $em->getRepository(EventCategory::class)->findBy(
            ['isActive' => true],
            ['position' => 'ASC', 'name' => 'ASC']
        );

        $data = [];
        foreach ($categories as $cat) {
            $data[] = [
                'id' => $cat->getId(),
                'name' => $cat->getName(),
                'slug' => $cat->getSlug(),
                'color' => $cat->getColor(),
                'description' => $cat->getDescription(),
                'position' => $cat->getPosition()
            ];
        }

        return $this->json($data);
    }

    #[Route('/events', name: 'list', methods: ['GET'])]
    public function getEvents(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $categoryId = $request->query->get('category_id');

        $qb = $em->getRepository(SchoolEvent::class)->createQueryBuilder('e')
            ->leftJoin('e.category', 'c')
            ->addSelect('c')
            ->where('e.isPublished = :published')
            ->setParameter('published', true)
            ->orderBy('e.eventDate', 'ASC');

        if (!empty($categoryId) && is_numeric($categoryId)) {
            $qb->andWhere('e.category = :catId')
               ->setParameter('catId', (int)$categoryId);
        }

        $events = $qb->getQuery()->getResult();

        $data = [];
        foreach ($events as $event) {
            $cat = $event->getCategory();
            $data[] = [
                'id' => $event->getId(),
                'title' => $event->getTitle(),
                'slug' => $event->getSlug(),
                'description' => $event->getDescription(),
                'eventDate' => $event->getEventDate()->format(\DateTimeInterface::ATOM),
                'endDate' => $event->getEndDate()?->format(\DateTimeInterface::ATOM),
                'location' => $event->getLocation(),
                'capacity' => $event->getCapacity(),
                'imageUrl' => $event->getImageUrl(),
                'translations' => $event->getTranslations() ?? [],
                'category' => $cat ? [
                    'id' => $cat->getId(),
                    'name' => $cat->getName(),
                    'slug' => $cat->getSlug(),
                    'color' => $cat->getColor()
                ] : null
            ];
        }

        return $this->json($data);
    }

    #[Route('/events/{id}', name: 'details', methods: ['GET'])]
    public function getEventDetails(int $id, EntityManagerInterface $em): JsonResponse
    {
        $event = $em->getRepository(SchoolEvent::class)->find($id);

        if (!$event || !$event->isPublished()) {
            return $this->json(['error' => 'Événement introuvable ou non publié.'], 404);
        }

        $cat = $event->getCategory();
        return $this->json([
            'id' => $event->getId(),
            'title' => $event->getTitle(),
            'slug' => $event->getSlug(),
            'description' => $event->getDescription(),
            'eventDate' => $event->getEventDate()->format(\DateTimeInterface::ATOM),
            'endDate' => $event->getEndDate()?->format(\DateTimeInterface::ATOM),
            'location' => $event->getLocation(),
            'capacity' => $event->getCapacity(),
            'imageUrl' => $event->getImageUrl(),
            'translations' => $event->getTranslations() ?? [],
            'category' => $cat ? [
                'id' => $cat->getId(),
                'name' => $cat->getName(),
                'slug' => $cat->getSlug(),
                'color' => $cat->getColor()
            ] : null
        ]);
    }
}
