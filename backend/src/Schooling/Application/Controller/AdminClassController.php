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
                'schedule' => $c->getSchedule() ?? 'Samedi 09:00 - 12:00 (Matin)',
                'category' => $c->getCategory() ? $c->getCategory()->getName() : 'Langue Arabe',
                'level' => $c->getLevel() ? $c->getLevel()->getName() : '6-8 ans (Débutant)',
                'teacher' => $c->getTeacher() ? $c->getTeacher()->getFullName() : 'Cheikh Mahmoud'
            ];
        }

        // Initialisation si base vide lors de la première requête
        if (empty($classData)) {
            $cat1 = new CourseCategory();
            $cat1->setName('Langue Arabe');
            $cat1->setDescription('Apprentissage de la lecture, écriture, grammaire & vocabulaire');
            $cat1->setColor('#047857');
            $em->persist($cat1);

            $cat2 = new CourseCategory();
            $cat2->setName('Coran & Tajwid');
            $cat2->setDescription('Mémorisation, récitation et règles de Tajwid');
            $cat2->setColor('#d97706');
            $em->persist($cat2);

            $cat3 = new CourseCategory();
            $cat3->setName('Éducation Éthique');
            $cat3->setDescription('Valeurs morales et comportementales');
            $cat3->setColor('#2563eb');
            $em->persist($cat3);

            $lvl1 = new CourseLevel();
            $lvl1->setName('4-5 ans (Éveil)');
            $lvl1->setTargetAgeMin(4);
            $lvl1->setTargetAgeMax(5);
            $lvl1->setDescription('Initiation ludique aux lettres et à la langue');
            $em->persist($lvl1);

            $lvl2 = new CourseLevel();
            $lvl2->setName('6-8 ans (Débutant)');
            $lvl2->setTargetAgeMin(6);
            $lvl2->setTargetAgeMax(8);
            $lvl2->setDescription('Apprentissage de la lecture fluide');
            $em->persist($lvl2);

            $lvl3 = new CourseLevel();
            $lvl3->setName('9-12 ans (Intermédiaire)');
            $lvl3->setTargetAgeMin(9);
            $lvl3->setTargetAgeMax(12);
            $lvl3->setDescription('Grammaire et mémorisation du Saint Coran');
            $em->persist($lvl3);

            $lvl4 = new CourseLevel();
            $lvl4->setName('13-16 ans (Avancé Tajwid)');
            $lvl4->setTargetAgeMin(13);
            $lvl4->setTargetAgeMax(16);
            $lvl4->setDescription('Étude approfondie des règles de Tajwid');
            $em->persist($lvl4);

            $class1 = new SchoolClass();
            $class1->setName('Classe Éveil 1');
            $class1->setRoomNumber('Salle Maryam 1');
            $class1->setMaxCapacity(12);
            $class1->setSchedule('Samedi 09:00 - 12:00 (Matin)');
            $class1->setCategory($cat1);
            $class1->setLevel($lvl1);
            $em->persist($class1);

            $class2 = new SchoolClass();
            $class2->setName('Classe Débutant 2A');
            $class2->setRoomNumber('Salle Maryam 2');
            $class2->setMaxCapacity(15);
            $class2->setSchedule('Samedi 09:00 - 12:00 (Matin)');
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
                    'level' => '4-5 ans (Éveil)',
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
                    'level' => '6-8 ans (Débutant)',
                    'teacher' => 'Cheikh Mahmoud'
                ]
            ];
        }

        // 2. Récupérer les catégories depuis MySQL
        $categories = $em->getRepository(CourseCategory::class)->findAll();
        $catData = [];
        foreach ($categories as $cat) {
            $catData[] = [
                'id' => $cat->getId(),
                'name' => $cat->getName(),
                'description' => $cat->getDescription(),
                'color' => $cat->getColor() ?? '#047857',
                'icon' => $cat->getColor() === '#d97706' ? '📖' : ($cat->getColor() === '#2563eb' ? '✨' : '🗣️')
            ];
        }

        // 3. Récupérer les niveaux depuis MySQL
        $levels = $em->getRepository(CourseLevel::class)->findAll();
        $levelData = [];
        foreach ($levels as $l) {
            $levelData[] = [
                'id' => $l->getId(),
                'name' => $l->getName(),
                'minAge' => $l->getTargetAgeMin(),
                'maxAge' => $l->getTargetAgeMax(),
                'ageGroup' => $l->getTargetAgeMin() . '-' . $l->getTargetAgeMax() . ' ans',
                'description' => $l->getDescription() ?? 'Objectifs pédagogiques et programme annuel'
            ];
        }

        // 4. Liste des Créneaux Horaires configurables
        $schedulesList = [
            ['id' => 1, 'day' => 'Samedi', 'startTime' => '09:00', 'endTime' => '12:00', 'label' => 'Matin', 'name' => 'Samedi 09:00 - 12:00 (Matin)'],
            ['id' => 2, 'day' => 'Samedi', 'startTime' => '14:00', 'endTime' => '17:00', 'label' => 'Après-Midi', 'name' => 'Samedi 14:00 - 17:00 (Après-Midi)'],
            ['id' => 3, 'day' => 'Dimanche', 'startTime' => '09:00', 'endTime' => '12:00', 'label' => 'Matin', 'name' => 'Dimanche 09:00 - 12:00 (Matin)'],
            ['id' => 4, 'day' => 'Dimanche', 'startTime' => '14:00', 'endTime' => '17:00', 'label' => 'Après-Midi', 'name' => 'Dimanche 14:00 - 17:00 (Après-Midi)'],
            ['id' => 5, 'day' => 'Mercredi', 'startTime' => '14:00', 'endTime' => '17:00', 'label' => 'Rattrapage', 'name' => 'Mercredi 14:00 - 17:00 (Rattrapage)'],
            ['id' => 6, 'day' => 'Vendredi', 'startTime' => '17:30', 'endTime' => '19:30', 'label' => 'Soirée', 'name' => 'Vendredi 17:30 - 19:30 (Soirée)']
        ];

        // 5. Récupérer tous les Enseignants réels depuis la base de données MySQL
        $teachers = $em->getRepository(Teacher::class)->findAll();
        $teacherData = [];
        foreach ($teachers as $t) {
            $specs = $t->getSpecialities();
            $teacherData[] = [
                'id' => $t->getId(),
                'name' => $t->getFullName(),
                'speciality' => !empty($specs) ? implode(', ', $specs) : 'Langue Arabe & Tajwid',
                'email' => $t->getUser() ? $t->getUser()->getEmail() : '',
                'phone' => $t->getPhone() ?? '+352 691 888 999'
            ];
        }

        if (empty($teacherData)) {
            $teacherData = [
                ['id' => 1, 'name' => 'Cheikh Mahmoud', 'speciality' => 'Langue Arabe & Tajwid'],
                ['id' => 2, 'name' => 'Oustaz Hassan', 'speciality' => 'Coran & Mémorisation'],
                ['id' => 3, 'name' => 'Mme Souad', 'speciality' => 'Éducation Éthique & Arabe']
            ];
        }

        return $this->json([
            'classes' => $classData,
            'categories' => $catData,
            'levels' => $levelData,
            'schedules' => $schedulesList,
            'teachers' => $teacherData
        ]);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $payload = json_decode($request->getContent(), true);

        $name = trim($payload['name'] ?? 'Nouvelle Classe');
        $room = trim($payload['roomNumber'] ?? 'Salle Maryam 1');
        $capacity = (int) ($payload['maxCapacity'] ?? 15);
        $schedule = trim($payload['schedule'] ?? 'Samedi 09:00 - 12:00 (Matin)');

        $classEntity = new SchoolClass();
        $classEntity->setName($name);
        $classEntity->setRoomNumber($room);
        $classEntity->setMaxCapacity($capacity);
        $classEntity->setSchedule($schedule);

        if (!empty($payload['teacherId'])) {
            $teacher = $em->getRepository(Teacher::class)->find((int) $payload['teacherId']);
            if ($teacher) $classEntity->setTeacher($teacher);
        }

        $em->persist($classEntity);
        $em->flush();

        return $this->json([
            'message' => 'Classe créée avec succès dans MySQL',
            'class' => [
                'id' => $classEntity->getId(),
                'name' => $classEntity->getName(),
                'roomNumber' => $classEntity->getRoomNumber(),
                'maxCapacity' => $classEntity->getMaxCapacity(),
                'currentEnrolled' => 0,
                'schedule' => $classEntity->getSchedule(),
                'category' => 'Langue Arabe',
                'level' => '6-8 ans (Débutant)',
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

    // CRUD CATÉGORIES EN BBD MYSQL
    #[Route('/categories', name: 'category_create', methods: ['POST'])]
    public function createCategory(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $payload = json_decode($request->getContent(), true);
        $cat = new CourseCategory();
        $cat->setName(trim($payload['name'] ?? 'Nouvelle Catégorie'));
        $cat->setDescription(trim($payload['description'] ?? ''));
        $cat->setColor($payload['color'] ?? '#047857');
        $em->persist($cat);
        $em->flush();

        return $this->json([
            'message' => 'Catégorie créée dans MySQL',
            'category' => [
                'id' => $cat->getId(),
                'name' => $cat->getName(),
                'description' => $cat->getDescription(),
                'color' => $cat->getColor(),
                'icon' => '📚'
            ]
        ], Response::HTTP_CREATED);
    }

    #[Route('/categories/{id}', name: 'category_update', methods: ['PUT', 'PATCH'])]
    public function updateCategory(int $id, Request $request, EntityManagerInterface $em): JsonResponse
    {
        $cat = $em->getRepository(CourseCategory::class)->find($id);
        if ($cat) {
            $payload = json_decode($request->getContent(), true);
            if (isset($payload['name'])) $cat->setName($payload['name']);
            if (isset($payload['description'])) $cat->setDescription($payload['description']);
            if (isset($payload['color'])) $cat->setColor($payload['color']);
            $em->flush();
        }
        return $this->json(['message' => 'Catégorie mise à jour dans MySQL']);
    }

    #[Route('/categories/{id}', name: 'category_delete', methods: ['DELETE'])]
    public function deleteCategory(int $id, EntityManagerInterface $em): JsonResponse
    {
        $cat = $em->getRepository(CourseCategory::class)->find($id);
        if ($cat) {
            $em->remove($cat);
            $em->flush();
        }
        return $this->json(['message' => 'Catégorie supprimée de MySQL']);
    }

    // CRUD NIVEAUX EN BBD MYSQL
    #[Route('/levels', name: 'level_create', methods: ['POST'])]
    public function createLevel(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $payload = json_decode($request->getContent(), true);
        $lvl = new CourseLevel();
        $lvl->setName(trim($payload['name'] ?? 'Nouveau Niveau'));
        $lvl->setTargetAgeMin((int) ($payload['minAge'] ?? 6));
        $lvl->setTargetAgeMax((int) ($payload['maxAge'] ?? 10));
        $lvl->setDescription(trim($payload['description'] ?? ''));
        $em->persist($lvl);
        $em->flush();

        return $this->json([
            'message' => 'Niveau créé dans MySQL',
            'level' => [
                'id' => $lvl->getId(),
                'name' => $lvl->getName(),
                'minAge' => $lvl->getTargetAgeMin(),
                'maxAge' => $lvl->getTargetAgeMax(),
                'ageGroup' => $lvl->getTargetAgeMin() . '-' . $lvl->getTargetAgeMax() . ' ans',
                'description' => $lvl->getDescription()
            ]
        ], Response::HTTP_CREATED);
    }

    #[Route('/levels/{id}', name: 'level_update', methods: ['PUT', 'PATCH'])]
    public function updateLevel(int $id, Request $request, EntityManagerInterface $em): JsonResponse
    {
        $lvl = $em->getRepository(CourseLevel::class)->find($id);
        if ($lvl) {
            $payload = json_decode($request->getContent(), true);
            if (isset($payload['name'])) $lvl->setName($payload['name']);
            if (isset($payload['minAge'])) $lvl->setTargetAgeMin((int)$payload['minAge']);
            if (isset($payload['maxAge'])) $lvl->setTargetAgeMax((int)$payload['maxAge']);
            if (isset($payload['description'])) $lvl->setDescription($payload['description']);
            $em->flush();
        }
        return $this->json(['message' => 'Niveau mis à jour dans MySQL']);
    }

    #[Route('/levels/{id}', name: 'level_delete', methods: ['DELETE'])]
    public function deleteLevel(int $id, EntityManagerInterface $em): JsonResponse
    {
        $lvl = $em->getRepository(CourseLevel::class)->find($id);
        if ($lvl) {
            $em->remove($lvl);
            $em->flush();
        }
        return $this->json(['message' => 'Niveau supprimé de MySQL']);
    }
}
