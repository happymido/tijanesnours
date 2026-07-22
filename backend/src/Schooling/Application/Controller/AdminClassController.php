<?php

namespace App\Schooling\Application\Controller;

use App\Schooling\Domain\Entity\SchoolClass;
use App\Schooling\Domain\Entity\CourseCategory;
use App\Schooling\Domain\Entity\CourseLevel;
use App\IdentityAccess\Domain\Entity\Teacher;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/v1/admin/classes', name: 'api_v1_admin_classes_')]
class AdminClassController extends AbstractController
{
    #[Route('', name: 'list', methods: ['GET'])]
    public function list(EntityManagerInterface $em): JsonResponse
    {
        // 1. Récupérer les classes depuis MySQL
        $classes = $em->getRepository(SchoolClass::class)->findAll();
        $classData = [];

        foreach ($classes as $c) {
            $classData[] = [
                'id' => $c->getId(),
                'name' => $c->getName(),
                'roomNumber' => $c->getRoomNumber() ?? 'Salle Maryam 1',
                'maxCapacity' => $c->getMaxCapacity(),
                'currentEnrolled' => 12,
                'schedule' => $c->getSchedule() ?? 'Samedi 09:00 - 12:00',
                'category' => $c->getCategory() ? $c->getCategory()->getName() : 'Langue Arabe',
                'level' => $c->getLevel() ? $c->getLevel()->getName() : '6-8 ans',
                'teacher' => $c->getTeacher() ? $c->getTeacher()->getFullName() : 'Cheikh Mahmoud'
            ];
        }

        // Si la table est vide lors du premier appel, insérer des classes de test
        if (empty($classData)) {
            $cat1 = new CourseCategory();
            $cat1->setName('Langue Arabe');
            $cat1->setDescription('Apprentissage de la lecture et de l\'écriture arabe');
            $cat1->setColor('#047857');
            $em->persist($cat1);

            $cat2 = new CourseCategory();
            $cat2->setName('Coran & Tajwid');
            $cat2->setDescription('Mémorisation et règles de récitation');
            $cat2->setColor('#d97706');
            $em->persist($cat2);

            $lvl1 = new CourseLevel();
            $lvl1->setName('4-5 ans (Éveil)');
            $lvl1->setTargetAgeMin(4);
            $lvl1->setTargetAgeMax(5);
            $em->persist($lvl1);

            $lvl2 = new CourseLevel();
            $lvl2->setName('6-8 ans (Débutant)');
            $lvl2->setTargetAgeMin(6);
            $lvl2->setTargetAgeMax(8);
            $em->persist($lvl2);

            $class1 = new SchoolClass();
            $class1->setName('Classe Éveil 1');
            $class1->setRoomNumber('Salle Khadija 2');
            $class1->setMaxCapacity(12);
            $class1->setSchedule('Samedi 09:00 - 11:30');
            $class1->setCategory($cat1);
            $class1->setLevel($lvl1);
            $em->persist($class1);

            $class2 = new SchoolClass();
            $class2->setName('Classe Débutant 2A');
            $class2->setRoomNumber('Salle Maryam 1');
            $class2->setMaxCapacity(15);
            $class2->setSchedule('Samedi 09:00 - 12:00');
            $class2->setCategory($cat2);
            $class2->setLevel($lvl2);
            $em->persist($class2);

            $em->flush();

            $classData = [
                [
                    'id' => $class1->getId(),
                    'name' => $class1->getName(),
                    'roomNumber' => $class1->getRoomNumber(),
                    'maxCapacity' => $class1->getMaxCapacity(),
                    'currentEnrolled' => 10,
                    'schedule' => $class1->getSchedule(),
                    'category' => 'Langue Arabe',
                    'level' => '4-5 ans',
                    'teacher' => 'Cheikh Mahmoud'
                ],
                [
                    'id' => $class2->getId(),
                    'name' => $class2->getName(),
                    'roomNumber' => $class2->getRoomNumber(),
                    'maxCapacity' => $class2->getMaxCapacity(),
                    'currentEnrolled' => 14,
                    'schedule' => $class2->getSchedule(),
                    'category' => 'Coran & Tajwid',
                    'level' => '6-8 ans',
                    'teacher' => 'Cheikh Mahmoud'
                ]
            ];
        }

        // 2. Récupérer les catégories
        $categories = $em->getRepository(CourseCategory::class)->findAll();
        $catData = [];
        foreach ($categories as $cat) {
            $catData[] = [
                'id' => $cat->getId(),
                'name' => $cat->getName(),
                'description' => $cat->getDescription(),
                'color' => $cat->getColor()
            ];
        }

        // 3. Récupérer les niveaux
        $levels = $em->getRepository(CourseLevel::class)->findAll();
        $levelData = [];
        foreach ($levels as $l) {
            $levelData[] = [
                'id' => $l->getId(),
                'name' => $l->getName(),
                'minAge' => $l->getTargetAgeMin(),
                'maxAge' => $l->getTargetAgeMax()
            ];
        }

        return $this->json([
            'classes' => $classData,
            'categories' => $catData,
            'levels' => $levelData
        ]);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $payload = json_decode($request->getContent(), true);

        $name = trim($payload['name'] ?? 'Nouvelle Classe');
        $room = trim($payload['roomNumber'] ?? 'Salle Maryam 1');
        $capacity = (int) ($payload['maxCapacity'] ?? 15);
        $schedule = trim($payload['schedule'] ?? 'Samedi 09:00 - 12:00');

        $classEntity = new SchoolClass();
        $classEntity->setName($name);
        $classEntity->setRoomNumber($room);
        $classEntity->setMaxCapacity($capacity);
        $classEntity->setSchedule($schedule);

        if (!empty($payload['teacherId'])) {
            $teacher = $em->getRepository(Teacher::class)->find((int) $payload['teacherId']);
            if ($teacher) {
                $classEntity->setTeacher($teacher);
            }
        }

        $em->persist($classEntity);
        $em->flush();

        return $this->json([
            'message' => 'Classe créée et enregistrée avec succès dans la base MySQL',
            'class' => [
                'id' => $classEntity->getId(),
                'name' => $classEntity->getName(),
                'roomNumber' => $classEntity->getRoomNumber(),
                'maxCapacity' => $classEntity->getMaxCapacity(),
                'currentEnrolled' => 0,
                'schedule' => $classEntity->getSchedule(),
                'category' => 'Langue Arabe',
                'level' => '6-8 ans',
                'teacher' => $classEntity->getTeacher() ? $classEntity->getTeacher()->getFullName() : 'Non affecté'
            ]
        ], Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'update', methods: ['PUT', 'PATCH'])]
    public function update(int $id, Request $request, EntityManagerInterface $em): JsonResponse
    {
        $classEntity = $em->getRepository(SchoolClass::class)->find($id);
        if (!$classEntity) {
            return $this->json(['error' => 'Classe non trouvée'], Response::HTTP_NOT_FOUND);
        }

        $payload = json_decode($request->getContent(), true);
        if (isset($payload['name'])) $classEntity->setName($payload['name']);
        if (isset($payload['roomNumber'])) $classEntity->setRoomNumber($payload['roomNumber']);
        if (isset($payload['maxCapacity'])) $classEntity->setMaxCapacity((int)$payload['maxCapacity']);
        if (isset($payload['schedule'])) $classEntity->setSchedule($payload['schedule']);

        $em->flush();

        return $this->json(['message' => 'Classe mise à jour avec succès dans MySQL']);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(int $id, EntityManagerInterface $em): JsonResponse
    {
        $classEntity = $em->getRepository(SchoolClass::class)->find($id);
        if ($classEntity) {
            $em->remove($classEntity);
            $em->flush();
        }

        return $this->json(['message' => 'Classe supprimée avec succès de MySQL']);
    }
}
