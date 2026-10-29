<?php

namespace App\Pedagogy\Application\Controller;

use App\IdentityAccess\Domain\Entity\Student;
use App\IdentityAccess\Domain\Entity\User;
use App\Pedagogy\Domain\Entity\Attendance;
use App\Pedagogy\Domain\Entity\Bulletin;
use App\Pedagogy\Domain\Entity\PedagogicalSubject;
use App\Pedagogy\Domain\Entity\PedagogicalSkill;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/v1/student', name: 'api_v1_student_')]
class StudentPedagogyController extends AbstractController
{
    #[Route('/attendance', name: 'attendance', methods: ['GET'])]
    public function getStudentAttendance(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $studentRepo = $em->getRepository(Student::class);
        $student = null;

        // 1. Essayer avec l'utilisateur authentifié
        $user = $this->getUser();
        if ($user instanceof User) {
            $student = $studentRepo->findOneBy(['user' => $user]);
        }

        // 2. Fallback avec paramètre email ou student_id
        if (!$student) {
            $email = $request->query->get('email');
            if ($email) {
                $userByEmail = $em->getRepository(User::class)->findOneBy(['email' => $email]);
                if ($userByEmail) {
                    $student = $studentRepo->findOneBy(['user' => $userByEmail]);
                }
            }
        }

        if (!$student) {
            $studentId = $request->query->get('student_id');
            if ($studentId) {
                $student = $studentRepo->find($studentId);
            }
        }

        // 3. Fallback sur le premier élève inscrit (Youssef Benali) si aucun identifiant explicite
        if (!$student) {
            $students = $studentRepo->findAll();
            $student = $students[0] ?? null;
        }

        if (!$student) {
            return $this->json([
                'student' => 'Élève',
                'className' => 'Classe Débutant 2A (6-8 ans)',
                'summary' => [
                    'totalSessions' => 0,
                    'presents' => 0,
                    'absences' => 0,
                    'lates' => 0,
                    'rate' => 100.0
                ],
                'history' => []
            ]);
        }

        $attRepo = $em->getRepository(Attendance::class);
        $records = $attRepo->findBy(['student' => $student], ['date' => 'DESC']);

        $totalSessions = count($records);
        $presents = 0;
        $absences = 0;
        $lates = 0;
        $history = [];

        foreach ($records as $r) {
            $status = strtoupper($r->getStatus());
            if ($status === 'PRESENT') {
                $presents++;
                $statusLabel = 'Présent';
            } elseif ($status === 'ABSENT') {
                $absences++;
                $statusLabel = 'Absent';
            } elseif ($status === 'LATE') {
                $lates++;
                $statusLabel = 'Retard';
            } else {
                $presents++;
                $statusLabel = 'Présent';
            }

            $history[] = [
                'id' => $r->getId(),
                'date' => $r->getDate()?->format('Y-m-d'),
                'formattedDate' => $r->getDate()?->format('d/m/Y') ?? 'Date non définie',
                'status' => $status,
                'statusLabel' => $statusLabel,
                'justification' => $r->getJustification() ?? '',
                'className' => $student->getAssignedGroup() ?? 'Classe Débutant 2A (6-8 ans)'
            ];
        }

        $rate = $totalSessions > 0 ? round(($presents / $totalSessions) * 100, 1) : 100.0;

        return $this->json([
            'studentId' => $student->getId(),
            'student' => $student->getFirstName() . ' ' . $student->getLastName(),
            'className' => $student->getAssignedGroup() ?? 'Classe Débutant 2A (6-8 ans)',
            'summary' => [
                'totalSessions' => $totalSessions,
                'presents' => $presents,
                'absences' => $absences,
                'lates' => $lates,
                'rate' => $rate
            ],
            'history' => $history
        ]);
    }

    #[Route('/evaluations', name: 'evaluations', methods: ['GET'])]
    public function getStudentEvaluations(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $studentRepo = $em->getRepository(Student::class);
        $student = null;

        // 1. Utilisateur connecté
        $user = $this->getUser();
        if ($user instanceof User) {
            $student = $studentRepo->findOneBy(['user' => $user]);
        }

        // 2. Paramètre student_id (utilisé pour les parents ou l'admin)
        $studentId = $request->query->get('student_id');
        if ($studentId) {
            $student = $studentRepo->find($studentId);
        }

        // 3. Fallback sur le premier élève
        if (!$student) {
            $students = $studentRepo->findAll();
            $student = $students[0] ?? null;
        }

        if (!$student) {
            return $this->json([
                'student' => 'Élève',
                'className' => 'Classe Débutant 2A (6-8 ans)',
                'curriculum' => [],
                'evaluations' => []
            ]);
        }

        // 4. Charger le référentiel des matières et compétences actives
        $subjects = $em->getRepository(PedagogicalSubject::class)->findBy(['isActive' => true], ['orderIndex' => 'ASC', 'id' => 'ASC']);
        $curriculum = [];
        foreach ($subjects as $s) {
            $skills = [];
            foreach ($s->getSkills() as $sk) {
                if ($sk->isActive()) {
                    $skills[] = [
                        'id' => $sk->getId(),
                        'name' => $sk->getName(),
                        'code' => $sk->getCode(),
                        'orderIndex' => $sk->getOrderIndex()
                    ];
                }
            }
            $curriculum[] = [
                'id' => $s->getId(),
                'name' => $s->getName(),
                'code' => $s->getCode(),
                'icon' => $s->getIcon(),
                'orderIndex' => $s->getOrderIndex(),
                'skills' => $skills
            ];
        }

        // 5. Charger tous les bulletins de cet élève
        $bulletinRepo = $em->getRepository(Bulletin::class);
        $bulletins = $bulletinRepo->findBy(['student' => $student], ['id' => 'DESC']);

        $evaluations = [];
        foreach ($bulletins as $b) {
            $evaluations[] = [
                'id' => $b->getId(),
                'term' => $b->getTerm(),
                'schoolYear' => $b->getSchoolYear(),
                'ratings' => $b->getRatings() ?? [],
                'averageStars' => number_format((float)$b->getAverageStars(), 1, '.', ''),
                'teacherRemarks' => $b->getTeacherRemarks() ?? 'Élève assidu et travail régulier.',
                'teacherName' => $b->getTeacher()?->getFullName() ?? 'Cheikh Mahmoud',
                'updatedAt' => $b->getUpdatedAt()?->format('d/m/Y') ?? $b->getGeneratedAt()->format('d/m/Y')
            ];
        }

        $classroomName = 'Classe Débutant 2A (6-8 ans)';
        $firstClass = $student->getSchoolClasses()->first();
        if ($firstClass) {
            $classroomName = $firstClass->getName();
        } elseif ($student->getAssignedGroup()) {
            $classroomName = $student->getAssignedGroup();
        }

        return $this->json([
            'studentId' => $student->getId(),
            'student' => $student->getFirstName() . ' ' . $student->getLastName(),
            'className' => $classroomName,
            'curriculum' => $curriculum,
            'evaluations' => $evaluations
        ]);
    }
}
