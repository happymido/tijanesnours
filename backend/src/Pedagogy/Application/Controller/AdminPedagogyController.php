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
            $data[] = [
                'id' => $g->getId(),
                'studentId' => $student?->getId(),
                'student' => $student ? ($student->getFirstName() . ' ' . $student->getLastName()) : 'Élève Inconnu',
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
    public function listAttendance(EntityManagerInterface $em): JsonResponse
    {
        $attendances = $em->getRepository(Attendance::class)->findBy([], ['id' => 'DESC'], 20);
        $data = [];

        foreach ($attendances as $att) {
            $student = $att->getStudent();
            $data[] = [
                'id' => $att->getId(),
                'student' => $student ? ($student->getFirstName() . ' ' . $student->getLastName()) : 'Élève Inconnu',
                'classroom' => $student?->getAssignedGroup() ?? 'Classe Débutant 2A',
                'date' => $att->getDate()?->format('d/m/Y') ?? date('d/m/Y'),
                'status' => $att->getStatus() === 'PRESENT' ? 'PRÉSENT' : 'ABSENT',
                'justification' => $att->getJustification()
            ];
        }

        // Fallback demo items if zero attendances recorded yet
        if (count($data) === 0) {
            $students = $em->getRepository(Student::class)->findAll();
            foreach ($students as $st) {
                $data[] = [
                    'id' => $st->getId(),
                    'student' => $st->getFirstName() . ' ' . $st->getLastName(),
                    'classroom' => $st->getAssignedGroup() ?? 'Classe Débutant 2A',
                    'date' => date('d/m/Y'),
                    'status' => 'PRÉSENT',
                    'justification' => null
                ];
            }
        }

        return $this->json($data);
    }
}
