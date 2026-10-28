<?php

namespace App\Pedagogy\Application\Controller;

use App\IdentityAccess\Domain\Entity\Student;
use App\IdentityAccess\Domain\Entity\Teacher;
use App\Pedagogy\Domain\Entity\Attendance;
use App\Schooling\Domain\Entity\SchoolClass;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/v1/teacher', name: 'api_v1_teacher_')]
class TeacherPedagogyController extends AbstractController
{
    #[Route('/classes', name: 'classes', methods: ['GET'])]
    public function getTeacherClasses(EntityManagerInterface $em): JsonResponse
    {
        $classes = $em->getRepository(SchoolClass::class)->findAll();
        $allStudents = $em->getRepository(Student::class)->findAll();
        $data = [];

        foreach ($classes as $c) {
            $studentsCount = 0;
            foreach ($allStudents as $st) {
                if ($st->getSchoolClasses()->contains($c) || $st->getAssignedGroup() === $c->getName()) {
                    $studentsCount++;
                }
            }
            $data[] = [
                'id' => $c->getId(),
                'name' => $c->getName(),
                'schedule' => $c->getScheduleSlot() ? $c->getScheduleSlot()->getName() : 'Samedi 09:00 - 12:00',
                'studentsCount' => $studentsCount
            ];
        }

        return $this->json($data);
    }

    #[Route('/attendance', name: 'attendance_get', methods: ['GET'])]
    public function getAttendanceSheet(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $className = $request->query->get('class_name', 'Classe Débutant 2A (6-8 ans)');
        $classId = $request->query->get('class_id');
        $dateStr = $request->query->get('date', (new \DateTimeImmutable())->format('Y-m-d'));

        try {
            $date = new \DateTimeImmutable($dateStr);
        } catch (\Exception $e) {
            $date = new \DateTimeImmutable();
        }

        // 1. Récupérer la classe ciblée
        $targetClass = null;
        if ($classId) {
            $targetClass = $em->getRepository(SchoolClass::class)->find((int)$classId);
        }
        if (!$targetClass && $className) {
            $targetClass = $em->getRepository(SchoolClass::class)->findOneBy(['name' => $className]);
        }

        // 2. Récupérer uniquement les élèves STRICTEMENT inscrits dans cette classe
        $allStudents = $em->getRepository(Student::class)->findAll();
        $matchingStudents = [];

        foreach ($allStudents as $st) {
            $isEnrolled = false;

            if ($targetClass && $st->getSchoolClasses()->contains($targetClass)) {
                $isEnrolled = true;
            } elseif ($className) {
                $assigned = $st->getAssignedGroup();
                if ($assigned) {
                    $assignedList = array_map('trim', explode(',', $assigned));
                    foreach ($assignedList as $item) {
                        if (strcasecmp($item, $className) === 0) {
                            $isEnrolled = true;
                            break;
                        }
                    }
                }
            }

            if ($isEnrolled) {
                $matchingStudents[] = $st;
            }
        }

        // Pas de fallback sur tous les élèves ! Si aucun élève n'est inscrit, la liste reste vide.
        $attRepo = $em->getRepository(Attendance::class);
        $result = [];

        foreach ($matchingStudents as $st) {
            $parent = $st->getParent();

            // Rechercher si un émargement existe déjà pour cet élève à cette date
            $existing = $attRepo->createQueryBuilder('a')
                ->where('a.student = :student')
                ->andWhere('a.date = :date')
                ->setParameter('student', $st)
                ->setParameter('date', $date->format('Y-m-d'))
                ->setMaxResults(1)
                ->getQuery()
                ->getOneOrNullResult();

            $result[] = [
                'id' => $st->getId(),
                'name' => $st->getFirstName() . ' ' . $st->getLastName(),
                'parentName' => $parent ? $parent->getFullName() : 'Tuteur Légal',
                'phone' => $parent ? $parent->getPhone() : '+352 691 123 456',
                'attendance' => $existing ? $existing->getStatus() : 'PRESENT',
                'justification' => $existing ? $existing->getJustification() : '',
                'hasRecord' => $existing !== null,
                'recordId' => $existing?->getId()
            ];
        }

        return $this->json([
            'date' => $date->format('Y-m-d'),
            'className' => $className,
            'students' => $result
        ]);
    }

    #[Route('/attendance', name: 'attendance_save', methods: ['POST'])]
    public function saveAttendanceSheet(
        Request $request,
        EntityManagerInterface $em,
        LoggerInterface $logger
    ): JsonResponse {
        try {
            $payload = $request->toArray();
        } catch (\Throwable $e) {
            $payload = json_decode($request->getContent(), true) ?? [];
        }

        $className = $payload['className'] ?? 'Classe Débutant 2A (6-8 ans)';
        $dateStr = $payload['date'] ?? (new \DateTimeImmutable())->format('Y-m-d');
        $attendancesList = $payload['attendances'] ?? [];

        try {
            $date = new \DateTimeImmutable($dateStr);
        } catch (\Exception $e) {
            $date = new \DateTimeImmutable();
        }

        $studentRepo = $em->getRepository(Student::class);
        $attRepo = $em->getRepository(Attendance::class);

        $savedCount = 0;
        $absentsCount = 0;
        $latesCount = 0;
        $presentsCount = 0;

        foreach ($attendancesList as $item) {
            $studentId = $item['studentId'] ?? $item['id'] ?? null;
            if (!$studentId) {
                continue;
            }

            $student = $studentRepo->find($studentId);
            if (!$student) {
                continue;
            }

            $status = strtoupper($item['attendance'] ?? $item['status'] ?? 'PRESENT');
            if (!in_array($status, ['PRESENT', 'ABSENT', 'LATE'])) {
                $status = 'PRESENT';
            }

            $justification = $item['justification'] ?? null;

            // Chercher émargement existant
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

            $attendance->setStatus($status);
            $attendance->setJustification($justification);

            $savedCount++;
            if ($status === 'ABSENT') {
                $absentsCount++;
            } elseif ($status === 'LATE') {
                $latesCount++;
            } else {
                $presentsCount++;
            }
        }

        $em->flush();

        $logger->info("Feuille d'appel enregistrée par l'enseignant", [
            'date' => $date->format('Y-m-d'),
            'class' => $className,
            'total' => $savedCount,
            'presents' => $presentsCount,
            'absents' => $absentsCount,
            'lates' => $latesCount
        ]);

        return $this->json([
            'success' => true,
            'message' => "Feuille d'appel enregistrée avec succès dans la base de données !",
            'date' => $date->format('Y-m-d'),
            'className' => $className,
            'stats' => [
                'total' => $savedCount,
                'presents' => $presentsCount,
                'absents' => $absentsCount,
                'lates' => $latesCount
            ]
        ], Response::HTTP_OK);
    }
}
