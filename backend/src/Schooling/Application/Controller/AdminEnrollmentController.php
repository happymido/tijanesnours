<?php

namespace App\Schooling\Application\Controller;

use App\Billing\Domain\Entity\Payment;
use App\Billing\Domain\Entity\PaymentSchedule;
use App\Billing\Domain\Service\BillingEngine;
use App\Schooling\Domain\Entity\Enrollment;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/v1/admin/enrollments', name: 'api_v1_admin_enrollments_')]
class AdminEnrollmentController extends AbstractController
{
    #[Route('', name: 'list', methods: ['GET'])]
    public function list(EntityManagerInterface $em): JsonResponse
    {
        $enrollments = $em->getRepository(Enrollment::class)->findBy([], ['id' => 'DESC']);
        $billingEngine = new BillingEngine($em);
        $data = [];

        foreach ($enrollments as $enrollment) {
            $student = $enrollment->getStudent();
            $parent = $student?->getParent();
            $parentUser = $parent?->getUser();

            $payments = $em->getRepository(Payment::class)->findBy([
                'enrollment' => $enrollment,
                'status' => 'VALIDATED'
            ]);

            $paidAmount = 0.0;
            $lastPaymentMethod = null;

            foreach ($payments as $p) {
                $paidAmount += (float) $p->getAmount();
                $lastPaymentMethod = $p->getPaymentMethod();
            }

            $totalAmount = (float) $enrollment->getTotalAmount();
            if ($totalAmount <= 0.0) {
                // Determine child rank in family
                $existingCount = 0;
                if ($parent) {
                    foreach ($parent->getStudents() as $st) {
                        if ($st->getId() <= $student?->getId()) {
                            $existingCount++;
                        }
                    }
                }
                if ($existingCount === 0) $existingCount = 1;

                $totalAmount = $billingEngine->calculateChildPrice($existingCount);
                $enrollment->setTotalAmount((string) $totalAmount);
                $em->persist($enrollment);
                $em->flush();
            }

            $remainingAmount = max(0.0, $totalAmount - $paidAmount);

            // Determine status
            if ($paidAmount >= $totalAmount) {
                $status = 'PAID';
            } elseif ($paidAmount > 0.0) {
                $status = 'PARTIAL_PAID';
            } else {
                $status = $enrollment->getStatus() ?? 'PENDING';
            }

            $data[] = [
                'id' => $enrollment->getId(),
                'enrollmentId' => $enrollment->getId(),
                'studentId' => $student ? 'student_' . $student->getId() : null,
                'studentName' => $student ? ($student->getFirstName() . ' ' . $student->getLastName()) : 'Élève Inconnu',
                'parentName' => $parent ? $parent->getFullName() : 'Parent Non Renseigné',
                'parentEmail' => $parentUser ? $parentUser->getEmail() : ($parent ? 'parent@tijanesnours.lu' : ''),
                'parentPhone' => $parent ? $parent->getPhone() : '',
                'assignedGroup' => $student?->getAssignedGroup() ?? 'Non Affecté',
                'status' => $status,
                'paymentMethod' => $lastPaymentMethod ?? 'NON_SPECIFIE',
                'totalAmount' => $totalAmount,
                'paidAmount' => $paidAmount,
                'remainingAmount' => $remainingAmount,
                'structuredReference' => $enrollment->getStructuredReference() ?? ('RF42-2026-0000' . $enrollment->getId()),
                'createdAt' => $enrollment->getCreatedAt()->format('d/m/Y H:i')
            ];
        }

        return $this->json($data);
    }

    #[Route('/{id}/payment', name: 'record_payment', methods: ['POST'])]
    public function recordPayment(
        int $id,
        Request $request,
        EntityManagerInterface $em,
        LoggerInterface $logger
    ): JsonResponse {
        $data = json_decode($request->getContent(), true) ?? [];
        $enrollment = $em->getRepository(Enrollment::class)->find($id);
        $billingEngine = new BillingEngine($em);

        if (!$enrollment) {
            return $this->json(['error' => 'Inscription introuvable'], Response::HTTP_NOT_FOUND);
        }

        $paymentMethod = $data['paymentMethod'] ?? 'CASH'; // CASH, WIRE_TRANSFER, PARTIAL_TRANCHE_1
        $totalAmount = (float) $enrollment->getTotalAmount();
        if ($totalAmount <= 0.0) {
            $totalAmount = $billingEngine->calculateChildPrice(1);
            $enrollment->setTotalAmount((string)$totalAmount);
        }

        $paymentAmount = 0.0;
        $newStatus = 'PAID';

        if ($paymentMethod === 'PARTIAL_TRANCHE_1') {
            $defaultTranche = round($totalAmount / 3, 2);
            if ($defaultTranche <= 0) $defaultTranche = 230.0;
            $paymentAmount = !empty($data['amount']) ? (float)$data['amount'] : $defaultTranche;
            $newStatus = 'PARTIAL_PAID';
        } else {
            $paymentAmount = !empty($data['amount']) ? (float)$data['amount'] : $totalAmount;
            $newStatus = 'PAID';
        }

        // Create Payment record
        $payment = new Payment();
        $payment->setEnrollment($enrollment);
        $studentUser = $enrollment->getStudent()?->getUser();
        if ($studentUser) {
            $payment->setUser($studentUser);
        } elseif ($enrollment->getStudent()?->getParent()?->getUser()) {
            $payment->setUser($enrollment->getStudent()->getParent()->getUser());
        }
        $payment->setAmount(number_format($paymentAmount, 2, '.', ''));
        $payment->setPaymentMethod($paymentMethod);
        $payment->setStatus('VALIDATED');
        $payment->setTransactionReference($data['reference'] ?? ('PAY-' . strtoupper($paymentMethod) . '-' . date('Ymd-His')));
        $payment->setStructuredReferenceUsed($enrollment->getStructuredReference());
        $payment->setReceiptNumber('REC-' . date('Ymd') . '-' . rand(1000, 9999));
        $payment->setValidatedAt(new \DateTimeImmutable());

        $enrollment->setStatus($newStatus);

        // Update PaymentSchedules
        $schedules = $em->getRepository(PaymentSchedule::class)->findBy(['enrollment' => $enrollment]);
        if ($paymentMethod === 'PARTIAL_TRANCHE_1') {
            if (!empty($schedules[0])) {
                $schedules[0]->setStatus('PAID');
                $schedules[0]->setPaymentMethod('PARTIAL_TRANCHE_1');
                $payment->setSchedule($schedules[0]);
            }
        } else {
            foreach ($schedules as $sc) {
                $sc->setStatus('PAID');
                $sc->setPaymentMethod($paymentMethod);
            }
            if (!empty($schedules[0])) {
                $payment->setSchedule($schedules[0]);
            }
        }

        $em->persist($payment);
        $em->flush();

        $logger->info('Paiement d\'inscription enregistré par l\'admin', [
            'enrollmentId' => $enrollment->getId(),
            'paymentMethod' => $paymentMethod,
            'amount' => $paymentAmount,
            'status' => $newStatus
        ]);

        return $this->json([
            'message' => 'Paiement enregistré avec succès dans la base de données',
            'enrollmentId' => $enrollment->getId(),
            'status' => $newStatus,
            'paymentMethod' => $paymentMethod,
            'paidAmount' => $paymentAmount,
            'receiptNumber' => $payment->getReceiptNumber()
        ]);
    }
}
