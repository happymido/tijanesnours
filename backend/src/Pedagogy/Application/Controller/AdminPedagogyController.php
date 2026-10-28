<?php

namespace App\Pedagogy\Application\Controller;

use App\IdentityAccess\Domain\Entity\Student;
use App\IdentityAccess\Domain\Entity\Teacher;
use App\Pedagogy\Domain\Entity\Attendance;
use App\Pedagogy\Domain\Entity\Bulletin;
use App\Pedagogy\Domain\Entity\Grade;
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
}
