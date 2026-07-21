<?php

namespace App\Schooling\Application\Controller;

use App\Billing\Domain\Service\BillingEngine;
use App\Billing\Domain\Service\StructuredReferenceGenerator;
use App\IdentityAccess\Domain\Entity\ParentUser;
use App\IdentityAccess\Domain\Entity\Student;
use App\Schooling\Domain\Entity\Enrollment;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/v1/enrollments', name: 'api_v1_enrollments_')]
class EnrollmentController extends AbstractController
{
    #[Route('', name: 'create', methods: ['POST'])]
    public function createEnrollment(
        Request $request,
        EntityManagerInterface $em,
        BillingEngine $billingEngine,
        StructuredReferenceGenerator $referenceGenerator
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        if (empty($data['student']['firstName']) || empty($data['student']['lastName']) || empty($data['parent']['email'])) {
            return $this->json(['error' => 'Dossier d\'inscription incomplet'], Response::HTTP_BAD_REQUEST);
        }

        // 1. Parent Setup
        $parent = new ParentUser();
        $parent->setFullName($data['parent']['fullName']);
        $parent->setPhone($data['parent']['phone']);
        $parent->setPreferredPaymentMethod($data['paymentMethod'] ?? 'STRIPE');
        $em->persist($parent);

        // 2. Student Setup
        $student = new Student();
        $student->setParent($parent);
        $student->setFirstName($data['student']['firstName']);
        $student->setLastName($data['student']['lastName']);
        $student->setDateOfBirth(new \DateTimeImmutable($data['student']['dateOfBirth']));
        $student->setPlaceOfBirth($data['student']['placeOfBirth'] ?? null);
        $student->setNationality($data['student']['nationality'] ?? null);
        $student->setAddress($data['student']['address'] ?? null);
        $student->setAllergies($data['student']['allergies'] ?? null);
        $student->setHealthIssues($data['student']['healthIssues'] ?? null);
        $student->setFamilyNotes($data['student']['familyNotes'] ?? null);
        $student->setImageRightsGranted($data['student']['imageRightsGranted'] ?? true);
        $student->setGdprConsent($data['student']['gdprConsent'] ?? true);
        $em->persist($student);

        // 3. Enrollment Setup
        $enrollment = new Enrollment();
        $enrollment->setStudent($student);
        $enrollment->setSelectedDay($data['selectedDay'] ?? 'SAMEDI');
        $enrollment->setSelectedTimeSlot($data['selectedTimeSlot'] ?? '09:00 - 12:00');
        $enrollment->setTotalAmount('450.00');

        $em->persist($enrollment);
        $em->flush();

        // 4. Generate ISO 11649 Structured Reference
        $rfRef = $referenceGenerator->generateIso11649($enrollment->getId());
        $enrollment->setStructuredReference($rfRef);

        // 5. Generate Payment Schedule
        $schedules = $billingEngine->generateSchedules($enrollment, $data['paymentFrequency'] ?? 'ANNUAL');
        foreach ($schedules as $schedule) {
            $em->persist($schedule);
        }

        $em->flush();

        return $this->json([
            'message' => 'Inscription enregistrée avec succès',
            'enrollmentId' => $enrollment->getId(),
            'structuredReference' => $rfRef,
            'totalAmount' => $enrollment->getTotalAmount(),
            'schedulesCount' => count($schedules)
        ], Response::HTTP_CREATED);
    }
}
