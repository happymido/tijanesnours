<?php

namespace App\Pedagogy\Application\Controller;

use App\IdentityAccess\Domain\Entity\Student;
use App\IdentityAccess\Domain\Entity\Teacher;
use App\Pedagogy\Domain\Entity\Attendance;
use App\Pedagogy\Domain\Entity\Bulletin;
use App\Pedagogy\Domain\Entity\Grade;
use App\Pedagogy\Domain\Entity\PedagogicalSubject;
use App\Pedagogy\Domain\Entity\PedagogicalSkill;
use App\Schooling\Domain\Entity\SchoolClass;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/v1/admin/pedagogy', name: 'api_v1_admin_pedagogy_')]
class AdminPedagogyController extends AbstractController
{
    #[Route('/grades', name: 'list_grades', methods: ['GET'])]
    public function listGrades(EntityManagerInterface $em): JsonResponse
    {
        $grades = $em->getRepository(Grade::class)->findBy([], ['id' => 'DESC']);
        $data = [];

        foreach ($grades as $g) {
            $student = $g->getStudent();
            $classroomName = 'Classe Débutant 2A (6-8 ans)';
            if ($student) {
                $firstClass = $student->getSchoolClasses()->first();
                if ($firstClass) {
                    $classroomName = $firstClass->getName();
                } elseif ($student->getAssignedGroup()) {
                    $classroomName = $student->getAssignedGroup();
                }
            }

            $data[] = [
                'id' => $g->getId(),
                'studentId' => $student?->getId(),
                'student' => $student ? ($student->getFirstName() . ' ' . $student->getLastName()) : 'Élève Inconnu',
                'classroom' => $classroomName,
                'title' => $g->getTitle(),
                'subject' => str_contains(strtolower($g->getTitle() ?? ''), 'arabe') ? 'Langue Arabe' : 'Coran & Tajwid',
                'date' => $g->getExamDate()?->format('d/m/Y') ?? 'Récemment',
                'score' => number_format((float)$g->getScore(), 2, '.', ''),
                'term' => $g->getTerm() ?? 'Trimestre 3'
            ];
        }

        return $this->json($data);
    }

    #[Route('/grades', name: 'create_grade', methods: ['POST'])]
    public function createGrade(
        Request $request,
        EntityManagerInterface $em,
        LoggerInterface $logger
    ): JsonResponse {
        $payload = json_decode($request->getContent(), true) ?? [];
        $studentId = $payload['studentId'] ?? null;
        $studentName = $payload['student'] ?? '';
        $title = $payload['title'] ?? 'Évaluation Scolaire';
        $score = $payload['score'] ?? 18.0;
        $term = $payload['term'] ?? 'Trimestre 3';

        $studentRepo = $em->getRepository(Student::class);
        $student = null;

        if ($studentId) {
            $student = $studentRepo->find($studentId);
        }

        if (!$student && $studentName) {
            $students = $studentRepo->findAll();
            foreach ($students as $st) {
                $fullName = $st->getFirstName() . ' ' . $st->getLastName();
                if (mb_stripos($fullName, $studentName) !== false || mb_stripos($studentName, $st->getFirstName()) !== false) {
                    $student = $st;
                    break;
                }
            }
        }

        if (!$student) {
            $students = $studentRepo->findAll();
            $student = $students[0] ?? null;
        }

        if (!$student) {
            return $this->json(['error' => 'Élève introuvable en base de données'], Response::HTTP_NOT_FOUND);
        }

        $teacher = $em->getRepository(Teacher::class)->findOneBy([]);

        $grade = new Grade();
        $grade->setStudent($student);
        if ($teacher) {
            $grade->setTeacher($teacher);
        }
        $grade->setTitle($title);
        $grade->setScore(number_format((float)$score, 2, '.', ''));
        $grade->setExamDate(new \DateTimeImmutable());
        $grade->setTerm($term);

        $em->persist($grade);
        $em->flush();

        $logger->info('Note créée et enregistrée en BBD', [
            'gradeId' => $grade->getId(),
            'studentId' => $student->getId(),
            'score' => $score
        ]);

        return $this->json([
            'success' => true,
            'message' => 'Note enregistrée avec succès dans la base de données',
            'id' => $grade->getId(),
            'student' => $student->getFirstName() . ' ' . $student->getLastName(),
            'title' => $grade->getTitle(),
            'score' => $grade->getScore()
        ], Response::HTTP_CREATED);
    }

    #[Route('/bulletins', name: 'list_bulletins', methods: ['GET'])]
    public function listBulletins(EntityManagerInterface $em): JsonResponse
    {
        $bulletins = $em->getRepository(Bulletin::class)->findBy([], ['id' => 'DESC']);
        $data = [];

        foreach ($bulletins as $b) {
            $student = $b->getStudent();
            $data[] = [
                'id' => $b->getId(),
                'studentId' => $student?->getId(),
                'student' => $student ? ($student->getFirstName() . ' ' . $student->getLastName()) : 'Élève Inconnu',
                'term' => $b->getTerm(),
                'average' => number_format((float)$b->getGeneralAverage(), 1, '.', ''),
                'appreciation' => $b->getTeacherAppreciation() ?? 'Très bon trimestre.'
            ];
        }

        return $this->json($data);
    }

    #[Route('/attendance', name: 'list_attendance', methods: ['GET'])]
    public function listAttendance(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $classFilter = $request->query->get('class');
        $statusFilter = strtoupper($request->query->get('status', ''));
        $dateFilter = $request->query->get('date');

        $attendances = $em->getRepository(Attendance::class)->findBy([], ['date' => 'DESC', 'id' => 'DESC']);
        $items = [];

        $totalCount = 0;
        $presentsCount = 0;
        $absentsCount = 0;
        $latesCount = 0;

        foreach ($attendances as $att) {
            $student = $att->getStudent();
            $classroom = $student?->getAssignedGroup() ?? 'Classe Débutant 2A (6-8 ans)';
            $status = strtoupper($att->getStatus());
            $dateFormatted = $att->getDate()?->format('d/m/Y') ?? date('d/m/Y');
            $dateRaw = $att->getDate()?->format('Y-m-d') ?? date('Y-m-d');

            // Apply filters
            if ($classFilter && $classFilter !== 'ALL' && !str_contains(strtolower($classroom), strtolower($classFilter))) {
                continue;
            }
            if ($statusFilter && $statusFilter !== 'ALL' && $status !== $statusFilter) {
                continue;
            }
            if ($dateFilter && $dateRaw !== $dateFilter) {
                continue;
            }

            $totalCount++;
            if ($status === 'ABSENT') {
                $absentsCount++;
                $statusDisplay = 'ABSENT';
            } elseif ($status === 'LATE') {
                $latesCount++;
                $statusDisplay = 'RETARD';
            } else {
                $presentsCount++;
                $statusDisplay = 'PRÉSENT';
            }

            $items[] = [
                'id' => $att->getId(),
                'studentId' => $student?->getId(),
                'student' => $student ? ($student->getFirstName() . ' ' . $student->getLastName()) : 'Élève Inconnu',
                'classroom' => $classroom,
                'date' => $dateFormatted,
                'dateRaw' => $dateRaw,
                'status' => $statusDisplay,
                'statusCode' => $status,
                'justification' => $att->getJustification()
            ];
        }

        $rate = $totalCount > 0 ? round(($presentsCount / $totalCount) * 100, 1) : 100.0;

        return $this->json([
            'items' => $items,
            'stats' => [
                'total' => $totalCount,
                'presents' => $presentsCount,
                'absents' => $absentsCount,
                'lates' => $latesCount,
                'rate' => $rate
            ]
        ]);
    }

    #[Route('/attendance', name: 'create_or_update_attendance', methods: ['POST'])]
    public function createOrUpdateAttendance(
        Request $request,
        EntityManagerInterface $em,
        LoggerInterface $logger
    ): JsonResponse {
        $payload = json_decode($request->getContent(), true) ?? [];
        $studentId = $payload['studentId'] ?? null;
        $dateStr = $payload['date'] ?? (new \DateTimeImmutable())->format('Y-m-d');
        $status = strtoupper($payload['status'] ?? 'PRESENT');
        $justification = $payload['justification'] ?? null;

        if (!$studentId) {
            return $this->json(['error' => 'Identifiant élève requis.'], Response::HTTP_BAD_REQUEST);
        }

        $student = $em->getRepository(Student::class)->find($studentId);
        if (!$student) {
            return $this->json(['error' => 'Élève introuvable.'], Response::HTTP_NOT_FOUND);
        }

        try {
            $date = new \DateTimeImmutable($dateStr);
        } catch (\Exception $e) {
            $date = new \DateTimeImmutable();
        }

        $attRepo = $em->getRepository(Attendance::class);
        $attendance = $attRepo->createQueryBuilder('a')
            ->where('a.student = :student')
            ->andWhere('a.date = :date')
            ->setParameter('student', $student)
            ->setParameter('date', $date->format('Y-m-d'))
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        if (!$attendance) {
            $attendance = new Attendance();
            $attendance->setStudent($student);
            $attendance->setDate($date);
            $em->persist($attendance);
        }

        if (!in_array($status, ['PRESENT', 'ABSENT', 'LATE'])) {
            $status = 'PRESENT';
        }

        $attendance->setStatus($status);
        $attendance->setJustification($justification);

        $em->flush();

        $logger->info('Émargement enregistré par Admin', [
            'id' => $attendance->getId(),
            'student' => $student->getFirstName() . ' ' . $student->getLastName(),
            'status' => $status,
            'date' => $date->format('Y-m-d')
        ]);

        return $this->json([
            'success' => true,
            'message' => 'Émargement enregistré avec succès.',
            'id' => $attendance->getId(),
            'student' => $student->getFirstName() . ' ' . $student->getLastName(),
            'status' => $status,
            'date' => $date->format('d/m/Y')
        ], Response::HTTP_OK);
    }

    #[Route('/attendance/{id}', name: 'delete_attendance', methods: ['DELETE'])]
    public function deleteAttendance(int $id, EntityManagerInterface $em): JsonResponse
    {
        $attendance = $em->getRepository(Attendance::class)->find($id);
        if (!$attendance) {
            return $this->json(['error' => 'Enregistrement introuvable.'], Response::HTTP_NOT_FOUND);
        }

        $em->remove($attendance);
        $em->flush();

        return $this->json(['success' => true, 'message' => 'Enregistrement supprimé avec succès.']);
    }

    #[Route('/curriculum', name: 'curriculum', methods: ['GET'])]
    public function getCurriculum(EntityManagerInterface $em): JsonResponse
    {
        $subjects = $em->getRepository(PedagogicalSubject::class)->findBy([], ['orderIndex' => 'ASC', 'id' => 'ASC']);

        // Auto-seed if empty
        if (empty($subjects)) {
            $defaultCurriculum = [
                ['name' => 'Saint Coran', 'code' => 'CORAN', 'icon' => '📖', 'order' => 1, 'skills' => [
                    ['name' => 'Mémorisation des sourates', 'code' => 'memorisation_sourates', 'order' => 1]
                ]],
                ['name' => 'Éducation Islamique', 'code' => 'EDUCATION_ISLAMIQUE', 'icon' => '🕌', 'order' => 2, 'skills' => [
                    ['name' => 'Acquisition des concepts religieux prescrits', 'code' => 'acquisition_concepts', 'order' => 1]
                ]],
                ['name' => 'Lecture', 'code' => 'LECTURE', 'icon' => '📚', 'order' => 3, 'skills' => [
                    ['name' => 'Lecture fluide avec le respect de la ponctuation', 'code' => 'lecture_fluide', 'order' => 1],
                    ['name' => 'Compréhension', 'code' => 'comprehension', 'order' => 2]
                ]],
                ['name' => 'Expression Orale', 'code' => 'EXPRESSION_ORALE', 'icon' => '🗣️', 'order' => 4, 'skills' => [
                    ['name' => 'Mémoriser les dialogues', 'code' => 'memoriser_dialogues', 'order' => 1],
                    ['name' => 'Mémoriser le vocabulaire', 'code' => 'memoriser_vocabulaire', 'order' => 2],
                    ['name' => 'Former des phrases avec les mots acquis', 'code' => 'former_phrases', 'order' => 3],
                    ['name' => 'Compréhension orale', 'code' => 'comprehension_orale', 'order' => 4]
                ]],
                ['name' => 'Dictée', 'code' => 'DICTEE', 'icon' => '✍️', 'order' => 5, 'skills' => [
                    ['name' => 'Écrire correctement les mots dictés', 'code' => 'ecrire_mots', 'order' => 1]
                ]]
            ];

            foreach ($defaultCurriculum as $subData) {
                $sub = new PedagogicalSubject();
                $sub->setName($subData['name']);
                $sub->setCode($subData['code']);
                $sub->setIcon($subData['icon']);
                $sub->setOrderIndex($subData['order']);
                $sub->setIsActive(true);
                $em->persist($sub);

                foreach ($subData['skills'] as $skData) {
                    $sk = new PedagogicalSkill();
                    $sk->setSubject($sub);
                    $sk->setName($skData['name']);
                    $sk->setCode($skData['code']);
                    $sk->setOrderIndex($skData['order']);
                    $sk->setIsActive(true);
                    $em->persist($sk);
                }
            }
            $em->flush();
            $subjects = $em->getRepository(PedagogicalSubject::class)->findBy([], ['orderIndex' => 'ASC', 'id' => 'ASC']);
        }

        $data = [];
        foreach ($subjects as $s) {
            $skillsData = [];
            foreach ($s->getSkills() as $sk) {
                $skillsData[] = [
                    'id' => $sk->getId(),
                    'subjectId' => $s->getId(),
                    'name' => $sk->getName(),
                    'code' => $sk->getCode(),
                    'orderIndex' => $sk->getOrderIndex(),
                    'isActive' => $sk->isActive()
                ];
            }

            $data[] = [
                'id' => $s->getId(),
                'name' => $s->getName(),
                'code' => $s->getCode(),
                'icon' => $s->getIcon(),
                'orderIndex' => $s->getOrderIndex(),
                'isActive' => $s->isActive(),
                'skills' => $skillsData
            ];
        }

        return $this->json($data);
    }

    #[Route('/subjects', name: 'save_subject', methods: ['POST'])]
    public function saveSubject(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $payload = json_decode($request->getContent(), true) ?? [];
        $id = $payload['id'] ?? null;
        $name = trim($payload['name'] ?? '');
        $code = trim($payload['code'] ?? '');
        $icon = trim($payload['icon'] ?? '📚');
        $orderIndex = (int)($payload['orderIndex'] ?? 0);
        $isActive = isset($payload['isActive']) ? (bool)$payload['isActive'] : true;

        if (empty($name)) {
            return $this->json(['error' => 'Le nom de la matière est obligatoire.'], Response::HTTP_BAD_REQUEST);
        }

        if (empty($code)) {
            $code = strtoupper(preg_replace('/[^a-zA-Z0-9_]/', '_', $name));
        }

        $repo = $em->getRepository(PedagogicalSubject::class);
        $subject = null;
        if ($id) {
            $subject = $repo->find($id);
        }
        if (!$subject) {
            $subject = new PedagogicalSubject();
        }

        $subject->setName($name);
        $subject->setCode($code);
        $subject->setIcon($icon);
        $subject->setOrderIndex($orderIndex);
        $subject->setIsActive($isActive);

        $em->persist($subject);
        $em->flush();

        return $this->json([
            'success' => true,
            'message' => 'Matière enregistrée avec succès en base de données.',
            'subject' => [
                'id' => $subject->getId(),
                'name' => $subject->getName(),
                'code' => $subject->getCode(),
                'icon' => $subject->getIcon(),
                'orderIndex' => $subject->getOrderIndex(),
                'isActive' => $subject->isActive()
            ]
        ]);
    }

    #[Route('/subjects/{id}', name: 'delete_subject', methods: ['DELETE'])]
    public function deleteSubject(int $id, EntityManagerInterface $em): JsonResponse
    {
        $subject = $em->getRepository(PedagogicalSubject::class)->find($id);
        if (!$subject) {
            return $this->json(['error' => 'Matière introuvable.'], Response::HTTP_NOT_FOUND);
        }

        $em->remove($subject);
        $em->flush();

        return $this->json(['success' => true, 'message' => 'Matière supprimée avec succès.']);
    }

    #[Route('/skills', name: 'save_skill', methods: ['POST'])]
    public function saveSkill(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $payload = json_decode($request->getContent(), true) ?? [];
        $id = $payload['id'] ?? null;
        $subjectId = $payload['subjectId'] ?? null;
        $name = trim($payload['name'] ?? '');
        $code = trim($payload['code'] ?? '');
        $orderIndex = (int)($payload['orderIndex'] ?? 0);
        $isActive = isset($payload['isActive']) ? (bool)$payload['isActive'] : true;

        if (!$subjectId || empty($name)) {
            return $this->json(['error' => 'Matière et intitulé de la compétence obligatoires.'], Response::HTTP_BAD_REQUEST);
        }

        $subject = $em->getRepository(PedagogicalSubject::class)->find($subjectId);
        if (!$subject) {
            return $this->json(['error' => 'Matière parente introuvable.'], Response::HTTP_NOT_FOUND);
        }

        if (empty($code)) {
            $code = strtolower(preg_replace('/[^a-zA-Z0-9_]/', '_', $name));
        }

        $repo = $em->getRepository(PedagogicalSkill::class);
        $skill = null;
        if ($id) {
            $skill = $repo->find($id);
        }
        if (!$skill) {
            $skill = new PedagogicalSkill();
        }

        $skill->setSubject($subject);
        $skill->setName($name);
        $skill->setCode($code);
        $skill->setOrderIndex($orderIndex);
        $skill->setIsActive($isActive);

        $em->persist($skill);
        $em->flush();

        return $this->json([
            'success' => true,
            'message' => 'Compétence enregistrée avec succès en base de données.',
            'skill' => [
                'id' => $skill->getId(),
                'subjectId' => $subject->getId(),
                'name' => $skill->getName(),
                'code' => $skill->getCode(),
                'orderIndex' => $skill->getOrderIndex(),
                'isActive' => $skill->isActive()
            ]
        ]);
    }

    #[Route('/skills/{id}', name: 'delete_skill', methods: ['DELETE'])]
    public function deleteSkill(int $id, EntityManagerInterface $em): JsonResponse
    {
        $skill = $em->getRepository(PedagogicalSkill::class)->find($id);
        if (!$skill) {
            return $this->json(['error' => 'Compétence introuvable.'], Response::HTTP_NOT_FOUND);
        }

        $em->remove($skill);
        $em->flush();

        return $this->json(['success' => true, 'message' => 'Compétence supprimée avec succès.']);
    }

    #[Route('/evaluations', name: 'list_evaluations', methods: ['GET'])]
    public function listEvaluations(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $classFilter = $request->query->get('class');
        $termFilter = $request->query->get('term');
        $studentId = $request->query->get('student_id');

        $repo = $em->getRepository(Bulletin::class);
        $bulletins = $repo->findBy([], ['id' => 'DESC']);

        $items = [];
        foreach ($bulletins as $b) {
            $student = $b->getStudent();
            if ($studentId && $student?->getId() != $studentId) {
                continue;
            }

            $classroom = 'Classe Débutant 2A (6-8 ans)';
            if ($student) {
                $firstClass = $student->getSchoolClasses()->first();
                if ($firstClass) {
                    $classroom = $firstClass->getName();
                } elseif ($student->getAssignedGroup()) {
                    $classroom = $student->getAssignedGroup();
                }
            }

            if ($classFilter && $classFilter !== 'ALL') {
                if (strcasecmp($classroom, $classFilter) !== 0 && !str_contains(strtolower($classroom), strtolower($classFilter))) {
                    continue;
                }
            }

            if ($termFilter && $termFilter !== 'ALL' && strcasecmp($b->getTerm(), $termFilter) !== 0) {
                continue;
            }

            $ratings = $b->getRatings() ?? [];

            $items[] = [
                'id' => $b->getId(),
                'studentId' => $student?->getId(),
                'student' => $student ? ($student->getFirstName() . ' ' . $student->getLastName()) : 'Élève Inconnu',
                'classroom' => $classroom,
                'term' => $b->getTerm(),
                'schoolYear' => $b->getSchoolYear(),
                'ratings' => $ratings,
                'averageStars' => number_format((float)$b->getAverageStars(), 1, '.', ''),
                'teacherRemarks' => $b->getTeacherRemarks() ?? 'Très bon investissement et régularité exemplaire.',
                'teacherName' => $b->getTeacher()?->getFullName() ?? 'Cheikh Mahmoud',
                'updatedAt' => $b->getUpdatedAt()?->format('d/m/Y') ?? $b->getGeneratedAt()->format('d/m/Y')
            ];
        }

        return $this->json($items);
    }

    #[Route('/evaluations', name: 'save_evaluation', methods: ['POST'])]
    public function saveEvaluation(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $payload = json_decode($request->getContent(), true) ?? [];
        $studentId = $payload['studentId'] ?? null;
        $term = $payload['term'] ?? 'Trimestre 1';
        $schoolYear = $payload['schoolYear'] ?? '2026-2027';
        $ratings = $payload['ratings'] ?? [];
        $teacherRemarks = $payload['teacherRemarks'] ?? ($payload['remarks'] ?? '');

        if (!$studentId) {
            return $this->json(['error' => 'Identifiant élève obligatoire.'], Response::HTTP_BAD_REQUEST);
        }

        $student = $em->getRepository(Student::class)->find($studentId);
        if (!$student) {
            return $this->json(['error' => 'Élève introuvable.'], Response::HTTP_NOT_FOUND);
        }

        $repo = $em->getRepository(Bulletin::class);
        $bulletin = $repo->findOneBy(['student' => $student, 'term' => $term, 'schoolYear' => $schoolYear]);
        if (!$bulletin) {
            $bulletin = new Bulletin();
            $bulletin->setStudent($student);
            $bulletin->setTerm($term);
            $bulletin->setSchoolYear($schoolYear);
        }

        // Calculate average stars
        $starValues = array_values($ratings);
        $avg = 5.00;
        if (!empty($starValues)) {
            $validStars = array_filter($starValues, fn($v) => is_numeric($v));
            if (!empty($validStars)) {
                $avg = round(array_sum($validStars) / count($validStars), 2);
            }
        }

        $teacher = $em->getRepository(Teacher::class)->findOneBy([]);
        if ($teacher) {
            $bulletin->setTeacher($teacher);
        }

        $bulletin->setRatings($ratings);
        $bulletin->setAverageStars((string)$avg);
        $bulletin->setTeacherRemarks($teacherRemarks);
        $bulletin->setUpdatedAt(new \DateTimeImmutable());

        $em->persist($bulletin);
        $em->flush();

        return $this->json([
            'success' => true,
            'message' => 'Évaluation enregistrée avec succès.',
            'id' => $bulletin->getId(),
            'averageStars' => $avg,
            'term' => $term
        ]);
    }

    #[Route('/evaluations/{id}', name: 'delete_evaluation', methods: ['DELETE'])]
    public function deleteEvaluation(int $id, EntityManagerInterface $em): JsonResponse
    {
        $bulletin = $em->getRepository(Bulletin::class)->find($id);
        if (!$bulletin) {
            return $this->json(['error' => 'Évaluation introuvable.'], Response::HTTP_NOT_FOUND);
        }

        $em->remove($bulletin);
        $em->flush();

        return $this->json(['success' => true, 'message' => 'Évaluation supprimée avec succès.']);
    }

    #[Route('/bulletins/students', name: 'bulletins_students_list', methods: ['GET'])]
    public function getBulletinsStudentsList(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $term = $request->query->get('term', 'Premier Semestre 2024/2025');
        $schoolYear = $request->query->get('school_year', '2024-2025');
        $classId = $request->query->get('class_id');
        $search = trim((string)$request->query->get('search', ''));

        $classesRepo = $em->getRepository(SchoolClass::class);
        $classes = $classesRepo->findAll();
        $classesList = [];
        foreach ($classes as $c) {
            $classesList[] = [
                'id' => $c->getId(),
                'name' => $c->getName()
            ];
        }

        $studentRepo = $em->getRepository(Student::class);
        $allStudents = $studentRepo->findAll();

        $bulletinRepo = $em->getRepository(Bulletin::class);
        $attRepo = $em->getRepository(Attendance::class);
        $teacherRepo = $em->getRepository(Teacher::class);
        $defaultTeacher = $teacherRepo->findOneBy([]);

        $filteredStudents = [];
        foreach ($allStudents as $s) {
            // Filter by search
            if ($search !== '') {
                $haystack = strtolower($s->getFirstName() . ' ' . $s->getLastName() . ' ' . ($s->getFirstNameAr() ?? '') . ' ' . ($s->getLastNameAr() ?? ''));
                if (!str_contains($haystack, strtolower($search))) {
                    continue;
                }
            }

            // Filter by class if provided
            $matchedClass = null;
            foreach ($s->getSchoolClasses() as $sc) {
                if (!$classId || (int)$sc->getId() === (int)$classId) {
                    $matchedClass = $sc;
                    break;
                }
            }
            if ($classId && !$matchedClass) {
                continue;
            }

            $className = $matchedClass ? $matchedClass->getName() : ($s->getAssignedGroup() ?? 'Classe Débutant 2A (6-8 ans)');
            $teacher = $matchedClass?->getTeacher() ?? $defaultTeacher;

            // Count absences
            $absencesCount = $attRepo->count(['student' => $s, 'status' => 'ABSENT']);

            // Find bulletin for term & schoolYear (or fallback to latest bulletin)
            $bulletin = $bulletinRepo->findOneBy([
                'student' => $s,
                'term' => $term,
                'schoolYear' => $schoolYear
            ]);

            if (!$bulletin) {
                $bulletin = $bulletinRepo->findOneBy(['student' => $s], ['id' => 'DESC']);
            }

            $filteredStudents[] = [
                'id' => $s->getId(),
                'firstName' => $s->getFirstName(),
                'firstNameAr' => $s->getFirstNameAr() ?? $s->getFirstName(),
                'lastName' => $s->getLastName(),
                'lastNameAr' => $s->getLastNameAr() ?? $s->getLastName(),
                'fullName' => $s->getFirstName() . ' ' . $s->getLastName(),
                'fullNameAr' => ($s->getFirstNameAr() ?? $s->getFirstName()) . ' ' . ($s->getLastNameAr() ?? $s->getLastName()),
                'className' => $className,
                'classId' => $matchedClass?->getId(),
                'teacherName' => $bulletin?->getTeacher()?->getFullName() ?? $teacher?->getFullName() ?? 'Cheikh Mahmoud',
                'teacherNameAr' => $bulletin?->getTeacher()?->getFullNameAr() ?? $teacher?->getFullNameAr() ?? 'الشيخ محمود',
                'absencesCount' => $absencesCount,
                'hasBulletin' => !empty($bulletin),
                'bulletinId' => $bulletin?->getId(),
                'term' => $bulletin?->getTerm() ?? $term,
                'schoolYear' => $bulletin?->getSchoolYear() ?? $schoolYear,
                'averageStars' => $bulletin ? number_format((float)$bulletin->getAverageStars(), 1, '.', '') : '5.0',
                'ratings' => $bulletin?->getRatings() ?? [],
                'teacherRemarks' => $bulletin?->getTeacherRemarks() ?? 'Élève assidu(e), travail sérieux et régulier.',
                'teacherRemarksAr' => $bulletin?->getTeacherRemarksAr() ?? 'تلميذ(ة) مجتهد(ة), خلوق(ة) وذو(ات) مهارات ممتازة وسلوك طيب. حفظك الله ووفقك دائما.'
            ];
        }

        return $this->json([
            'classes' => $classesList,
            'availableYears' => ['2024-2025', '2025-2026', '2026-2027'],
            'availableTerms' => [
                'Premier Semestre 2024/2025',
                'Deuxième Semestre 2024/2025',
                'Trimestre 1 (2026-2027)',
                'Trimestre 2 (2026-2027)',
                'Trimestre 3 (2025-2026)'
            ],
            'selectedTerm' => $term,
            'selectedYear' => $schoolYear,
            'totalStudents' => count($filteredStudents),
            'students' => $filteredStudents
        ]);
    }

    #[Route('/bulletins/detail/{studentId}', name: 'bulletins_student_detail', methods: ['GET'])]
    public function getBulletinDetail(int $studentId, Request $request, EntityManagerInterface $em): JsonResponse
    {
        $term = $request->query->get('term', 'Premier Semestre 2024/2025');
        $schoolYear = $request->query->get('school_year', '2024-2025');

        $student = $em->getRepository(Student::class)->find($studentId);
        if (!$student) {
            return $this->json(['error' => 'Élève introuvable.'], Response::HTTP_NOT_FOUND);
        }

        // 1. Absences
        $attRepo = $em->getRepository(Attendance::class);
        $absencesCount = $attRepo->count(['student' => $student, 'status' => 'ABSENT']);

        // 2. Curriculum
        $subjects = $em->getRepository(PedagogicalSubject::class)->findBy(['isActive' => true], ['orderIndex' => 'ASC', 'id' => 'ASC']);
        $curriculum = [];
        foreach ($subjects as $s) {
            $skillsData = [];
            foreach ($s->getSkills() as $sk) {
                if ($sk->isActive()) {
                    $skillsData[] = [
                        'id' => $sk->getId(),
                        'name' => $sk->getName(),
                        'nameAr' => $sk->getNameAr() ?? $sk->getName(),
                        'code' => $sk->getCode(),
                        'orderIndex' => $sk->getOrderIndex()
                    ];
                }
            }
            $curriculum[] = [
                'id' => $s->getId(),
                'name' => $s->getName(),
                'nameAr' => $s->getNameAr() ?? $s->getName(),
                'code' => $s->getCode(),
                'icon' => $s->getIcon(),
                'orderIndex' => $s->getOrderIndex(),
                'skills' => $skillsData
            ];
        }

        // 3. Bulletin
        $bulletinRepo = $em->getRepository(Bulletin::class);
        $bulletin = $bulletinRepo->findOneBy([
            'student' => $student,
            'term' => $term,
            'schoolYear' => $schoolYear
        ]);
        if (!$bulletin) {
            $bulletin = $bulletinRepo->findOneBy(['student' => $student], ['id' => 'DESC']);
        }

        // 4. Class & Teacher
        $firstClass = $student->getSchoolClasses()->first();
        $className = $firstClass ? $firstClass->getName() : ($student->getAssignedGroup() ?? 'Classe Débutant 2A (6-8 ans)');
        
        // Derive level name in FR and AR
        $levelFr = 'Niveau 3';
        $levelAr = 'المستوى 3';
        if (preg_match('/(\d+)/', $className, $matches)) {
            $levelFr = 'Niveau ' . $matches[1];
            $levelAr = 'المستوى ' . $matches[1];
        }

        $teacherRepo = $em->getRepository(Teacher::class);
        $defaultTeacher = $teacherRepo->findOneBy([]);
        $teacher = $bulletin?->getTeacher() ?? $firstClass?->getTeacher() ?? $defaultTeacher;

        $teacherName = $teacher?->getFullName() ?? 'Afifa Jlassi';
        $teacherNameAr = $teacher?->getFullNameAr() ?? 'عفيفة الجلاصي';

        // 5. Bilingual titles based on period
        $titleFr = 'Bilan du premier semestre ' . $schoolYear;
        $titleAr = 'سجل نتائج السداسي الأول ' . $schoolYear;
        if (str_contains(strtolower($term), 'deuxième') || str_contains(strtolower($term), 'second') || str_contains(strtolower($term), 's2')) {
            $titleFr = 'Bilan du deuxième semestre ' . $schoolYear;
            $titleAr = 'سجل نتائج السداسي الثاني ' . $schoolYear;
        } elseif (str_contains(strtolower($term), 'trimestre 1')) {
            $titleFr = 'Bilan du premier trimestre ' . $schoolYear;
            $titleAr = 'سجل نتائج الثلاثي الأول ' . $schoolYear;
        } elseif (str_contains(strtolower($term), 'trimestre 2')) {
            $titleFr = 'Bilan du deuxième trimestre ' . $schoolYear;
            $titleAr = 'سجل نتائج الثلاثي الثاني ' . $schoolYear;
        } elseif (str_contains(strtolower($term), 'trimestre 3')) {
            $titleFr = 'Bilan du troisième trimestre ' . $schoolYear;
            $titleAr = 'سجل نتائج الثلاثي الثالث ' . $schoolYear;
        }

        // 6. Ratings map (1..4 stars or 1..5 stars mapped to 1..4)
        $rawRatings = $bulletin?->getRatings() ?? [];
        $starLevels = [];
        $starSymbols = [];
        foreach ($rawRatings as $skillId => $val) {
            $numeric = (int)$val;
            // Map 1-5 to 1-4 scale
            $level = 3;
            if ($numeric <= 1) $level = 1;
            elseif ($numeric <= 3) $level = 2;
            elseif ($numeric === 4) $level = 3;
            elseif ($numeric >= 5) $level = 4;

            $starLevels[$skillId] = $level;
            $starSymbols[$skillId] = str_repeat('*', $level);
        }

        // Fallback default ratings if empty: all objectives reached (***)
        if (empty($starLevels)) {
            foreach ($curriculum as $sub) {
                foreach ($sub['skills'] as $sk) {
                    $starLevels[$sk['id']] = 3;
                    $starSymbols[$sk['id']] = '***';
                }
            }
        }

        // 7. Remarks
        $teacherRemarksFr = $bulletin?->getTeacherRemarks() ?? "Une bonne élève, polie , avec de bonnes compétences, mais peu de participation et peu de travail à la maison, mais elle peut obtenir de meilleurs résultats. Que Allah vous protège ma fille.";
        $teacherRemarksAr = $bulletin?->getTeacherRemarksAr() ?? "بيان تلميذة جيدة, خلوقة ومؤدبة ,و تمتلك مهارات جيدة لكن قليلة المشاركة وينقصها القليل من العمل في البيت , وتستطيع أن تحقق نتائج أفضل ,حفظك الله ورعاك يا ابنتي";

        return $this->json([
            'student' => [
                'id' => $student->getId(),
                'firstName' => $student->getFirstName(),
                'firstNameAr' => $student->getFirstNameAr() ?? $student->getFirstName(),
                'lastName' => $student->getLastName(),
                'lastNameAr' => $student->getLastNameAr() ?? $student->getLastName(),
                'fullName' => $student->getFirstName() . ' ' . $student->getLastName(),
                'fullNameAr' => ($student->getFirstNameAr() ?? $student->getFirstName()) . ' ' . ($student->getLastNameAr() ?? $student->getLastName()),
                'className' => $className,
                'levelFr' => $levelFr,
                'levelAr' => $levelAr
            ],
            'teacher' => [
                'fullName' => $teacherName,
                'fullNameAr' => $teacherNameAr
            ],
            'school' => [
                'nameFr' => 'Ecole Tijanes Nours',
                'nameAr' => 'مدرسة تيجان النور',
                'basmala' => 'بسم الله الرحمان الرحيم',
                'address' => '32, Rue Dernier Sol, L-2543 Luxembourg',
                'phone' => '+352 20 60 10 10',
                'logoUrl' => '/logo.png'
            ],
            'period' => [
                'term' => $term,
                'schoolYear' => $schoolYear,
                'titleFr' => $titleFr,
                'titleAr' => $titleAr
            ],
            'absences' => [
                'count' => $absencesCount,
                'labelFr' => 'Absences',
                'labelAr' => 'الغيابات'
            ],
            'curriculum' => $curriculum,
            'ratings' => $starLevels,
            'starSymbols' => $starSymbols,
            'teacherRemarks' => [
                'fr' => $teacherRemarksFr,
                'ar' => $teacherRemarksAr
            ],
            'legend' => [
                ['symbol' => '*', 'fr' => 'Les objectifs n\'ont pas encore été atteints', 'ar' => 'لم يتم تحقيق أهداف التعلم بعد'],
                ['symbol' => '**', 'fr' => 'Certains objectifs ont été atteints.', 'ar' => 'تم تحقيق بعض الأهداف التعليمية'],
                ['symbol' => '***', 'fr' => 'Objectifs atteints', 'ar' => 'تم تحقيق جميع الأهداف التعليمية'],
                ['symbol' => '****', 'fr' => 'Objectifs atteints avec excellence.', 'ar' => 'تم تحقيق أهداف التعلم بامتياز']
            ]
        ]);
    }

    #[Route('/bulletins/save', name: 'bulletins_save_data', methods: ['POST'])]
    public function saveBulletinData(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $payload = json_decode($request->getContent(), true) ?? [];

        $studentId = $payload['studentId'] ?? null;
        $term = $payload['term'] ?? 'Premier Semestre 2024/2025';
        $schoolYear = $payload['schoolYear'] ?? '2024-2025';
        $ratings = $payload['ratings'] ?? [];
        $teacherRemarks = $payload['teacherRemarks'] ?? '';
        $teacherRemarksAr = $payload['teacherRemarksAr'] ?? '';

        if (!$studentId) {
            return $this->json(['error' => 'Identifiant élève obligatoire.'], Response::HTTP_BAD_REQUEST);
        }

        $student = $em->getRepository(Student::class)->find($studentId);
        if (!$student) {
            return $this->json(['error' => 'Élève introuvable.'], Response::HTTP_NOT_FOUND);
        }

        $bulletinRepo = $em->getRepository(Bulletin::class);
        $bulletin = $bulletinRepo->findOneBy([
            'student' => $student,
            'term' => $term,
            'schoolYear' => $schoolYear
        ]);

        if (!$bulletin) {
            $bulletin = new Bulletin();
            $bulletin->setStudent($student);
            $bulletin->setTerm($term);
            $bulletin->setSchoolYear($schoolYear);
        }

        // Calculate average
        $starValues = array_values($ratings);
        $avg = 4.00;
        if (!empty($starValues)) {
            $validStars = array_filter($starValues, fn($v) => is_numeric($v));
            if (!empty($validStars)) {
                $avg = round(array_sum($validStars) / count($validStars), 2);
            }
        }

        $defaultTeacher = $em->getRepository(Teacher::class)->findOneBy([]);
        if ($defaultTeacher && !$bulletin->getTeacher()) {
            $bulletin->setTeacher($defaultTeacher);
        }

        $bulletin->setRatings($ratings);
        $bulletin->setAverageStars((string)$avg);
        $bulletin->setTeacherRemarks($teacherRemarks);
        $bulletin->setTeacherRemarksAr($teacherRemarksAr);
        $bulletin->setUpdatedAt(new \DateTimeImmutable());

        $em->persist($bulletin);
        $em->flush();

        return $this->json([
            'success' => true,
            'message' => 'Bulletin scolaire enregistré avec succès.',
            'id' => $bulletin->getId(),
            'averageStars' => $avg
        ]);
    }
}
