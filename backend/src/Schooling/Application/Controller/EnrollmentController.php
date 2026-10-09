<?php

namespace App\Schooling\Application\Controller;

use App\Billing\Domain\Service\BillingEngine;
use App\Billing\Domain\Service\StructuredReferenceGenerator;
use App\IdentityAccess\Domain\Entity\ParentUser;
use App\IdentityAccess\Domain\Entity\Student;
use App\IdentityAccess\Domain\Entity\User;
use App\Schooling\Domain\Entity\Enrollment;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/v1/enrollments', name: 'api_v1_enrollments_')]
class EnrollmentController extends AbstractController
{
    #[Route('', name: 'create', methods: ['POST'])]
    public function createEnrollment(
        Request $request,
        EntityManagerInterface $em,
        BillingEngine $billingEngine,
        StructuredReferenceGenerator $referenceGenerator,
        UserPasswordHasherInterface $hasher,
        LoggerInterface $logger
    ): JsonResponse {
        $data = json_decode($request->getContent(), true) ?? [];
        $logger->info('Dossier d\'inscription reçu', ['data' => $data]);

        try {
            // Support both nested structure and flat structure
            $studentFirstName = trim($data['student']['firstName'] ?? $data['studentFirstName'] ?? '');
            $studentLastName = trim($data['student']['lastName'] ?? $data['studentLastName'] ?? '');
            $parentEmail = trim($data['parent']['email'] ?? $data['parentEmail'] ?? '');
            $parentPhone = trim($data['parent']['phone'] ?? $data['parentPhone'] ?? '');
            $parentFullName = trim($data['parent']['fullName'] ?? $data['parentFullName'] ?? '');
            $desiredLevel = trim($data['student']['assignedGroup'] ?? $data['desiredLevel'] ?? $data['assignedGroup'] ?? 'Classe Débutant 2A (6-8 ans)');
            $dateOfBirthStr = $data['student']['dateOfBirth'] ?? $data['dateOfBirth'] ?? '2018-05-12';

            if (empty($studentFirstName) || empty($studentLastName) || empty($parentEmail)) {
                $logger->warning('Inscription rejetée: dossier incomplet', ['data' => $data]);
                return $this->json(['error' => 'Dossier d\'inscription incomplet (Prénom, Nom et Email requis)'], Response::HTTP_BAD_REQUEST);
            }

            // 1. Parent Setup: Link existing ParentUser if email matches, or create new User + ParentUser
            $userParent = $em->getRepository(User::class)->createQueryBuilder('u')
                ->where('LOWER(u.email) = LOWER(:email)')
                ->setParameter('email', strtolower($parentEmail))
                ->getQuery()
                ->getOneOrNullResult();

            if (!$userParent) {
                $userParent = new User();
                $userParent->setEmail($parentEmail);
                $userParent->setPassword($hasher->hashPassword($userParent, 'parentpassword123'));
                $userParent->setRoles(['ROLE_PARENT']);
                $userParent->setLocale('fr');
                $userParent->setIsActive(true);
                $em->persist($userParent);
            }

            $parent = $em->getRepository(ParentUser::class)->findOneBy(['user' => $userParent]);
            if (!$parent) {
                $parent = new ParentUser();
                $parent->setUser($userParent);
                $parent->setFullName(!empty($parentFullName) ? $parentFullName : 'Parent Responsable');
                $parent->setPhone(!empty($parentPhone) ? $parentPhone : '+352 691 000 000');
                $parent->setPreferredPaymentMethod($data['paymentMethod'] ?? 'STRIPE');
                $parent->setAddress($data['student']['address'] ?? $data['address'] ?? 'Luxembourg');
                $em->persist($parent);
            } else {
                if (!empty($parentFullName)) {
                    $parent->setFullName($parentFullName);
                }
                if (!empty($parentPhone)) {
                    $parent->setPhone($parentPhone);
                }
            }

            // 2. Student Setup
            $studentUserEmail = strtolower($studentFirstName) . '.' . time() . '@student.lu';
            $userStudent = new User();
            $userStudent->setEmail($studentUserEmail);
            $userStudent->setPassword($hasher->hashPassword($userStudent, 'studentpassword123'));
            $userStudent->setRoles(['ROLE_STUDENT']);
            $userStudent->setLocale('fr');
            $userStudent->setIsActive(true);
            $em->persist($userStudent);

            $student = new Student();
            $student->setUser($userStudent);
            $student->setParent($parent);
            $student->setFirstName($studentFirstName);
            $student->setLastName($studentLastName);
            try {
                $student->setDateOfBirth(new \DateTimeImmutable($dateOfBirthStr));
            } catch (\Exception $e) {
                $student->setDateOfBirth(new \DateTimeImmutable('-8 years'));
            }
            $student->setPlaceOfBirth($data['student']['placeOfBirth'] ?? $data['placeOfBirth'] ?? null);
            $student->setNationality($data['student']['nationality'] ?? $data['nationality'] ?? 'Luxembourgeoise');
            $student->setAddress($data['student']['address'] ?? $data['address'] ?? 'Luxembourg');
            $student->setAllergies($data['student']['allergies'] ?? $data['allergies'] ?? null);
            $student->setHealthIssues($data['student']['healthIssues'] ?? $data['healthIssues'] ?? null);
            $student->setFamilyNotes($data['student']['familyNotes'] ?? $data['familyNotes'] ?? null);
            $student->setImageRightsGranted($data['student']['imageRightsGranted'] ?? $data['imageRightsGranted'] ?? true);
            $student->setGdprConsent($data['student']['gdprConsent'] ?? $data['gdprConsent'] ?? true);
            $student->setAssignedGroup($desiredLevel);
            $em->persist($student);

            // Calculate pricing based on parent's children count
            $existingChildrenCount = count($parent->getStudents());
            $childRank = $existingChildrenCount + 1;
            $calculatedAmount = $billingEngine->calculateChildPrice($childRank);

            // 3. Enrollment Setup (Set initial temporary non-null reference to satisfy DB NOT NULL constraint)
            $enrollment = new Enrollment();
            $enrollment->setStudent($student);
            $enrollment->setSelectedDay($data['selectedDay'] ?? 'SAMEDI');
            $enrollment->setSelectedTimeSlot($data['selectedTimeSlot'] ?? '09:00 - 12:00');
            $enrollment->setTotalAmount(number_format($calculatedAmount, 2, '.', ''));
            $enrollment->setStructuredReference('TEMP-' . mt_rand(100000, 999999));

            $em->persist($enrollment);
            $em->flush();

            // 4. Generate ISO 11649 Structured Reference with real enrollment ID
            $rfRef = $referenceGenerator->generateIso11649($enrollment->getId());
            $enrollment->setStructuredReference($rfRef);

            // 5. Generate Payment Schedule
            $schedules = $billingEngine->generateSchedules($enrollment, $data['paymentFrequency'] ?? 'ANNUAL');
            foreach ($schedules as $schedule) {
                $em->persist($schedule);
            }

            $em->flush();

            $logger->info('Inscription enregistrée avec succès', [
                'enrollmentId' => $enrollment->getId(),
                'studentId' => $student->getId(),
                'studentName' => $student->getFirstName() . ' ' . $student->getLastName(),
                'structuredReference' => $rfRef
            ]);

            return $this->json([
                'message' => 'Inscription enregistrée avec succès',
                'enrollmentId' => $enrollment->getId(),
                'studentId' => 'student_' . $student->getId(),
                'studentName' => $student->getFirstName() . ' ' . $student->getLastName(),
                'assignedGroup' => $student->getAssignedGroup(),
                'structuredReference' => $rfRef,
                'totalAmount' => $enrollment->getTotalAmount(),
                'schedulesCount' => count($schedules)
            ], Response::HTTP_CREATED);

        } catch (\Throwable $e) {
            $logger->error('Erreur lors de la création d\'une inscription', [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $data
            ]);

            return $this->json([
                'error' => 'Une erreur s\'est produite lors de l' . '\'' . 'enregistrement. ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}


