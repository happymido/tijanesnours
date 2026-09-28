<?php

namespace App\Schooling\Application\Controller;

use App\Schooling\Domain\Entity\CourseLevel;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/v1/public', name: 'api_v1_public_')]
class PublicCourseController extends AbstractController
{
    #[Route('/course-levels', name: 'course_levels', methods: ['GET'])]
    public function getCourseLevels(EntityManagerInterface $em): JsonResponse
    {
        $levels = $em->getRepository(CourseLevel::class)->findBy([], ['id' => 'ASC']);

        $data = [];
        foreach ($levels as $level) {
            $data[] = [
                'id' => $level->getId(),
                'name' => $level->getName(),
                'targetAgeMin' => $level->getTargetAgeMin(),
                'targetAgeMax' => $level->getTargetAgeMax(),
                'description' => $level->getDescription(),
                'translations' => $level->getTranslations()
            ];
        }

        return $this->json($data);
    }
}
