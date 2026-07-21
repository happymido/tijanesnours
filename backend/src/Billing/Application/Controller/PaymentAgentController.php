<?php

namespace App\Billing\Application\Controller;

use App\Billing\Domain\Entity\Payment;
use App\Billing\Domain\Entity\PaymentSchedule;

use App\Billing\Domain\Service\SepaXmlGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/v1/payment-agent', name: 'api_v1_payment_agent_')]
#[IsGranted('ROLE_PAYMENT_AGENT')]
class PaymentAgentController extends AbstractController
{
    #[Route('/manual-payment', name: 'manual_payment', methods: ['POST'])]
    public function recordManualPayment(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (empty($data['scheduleId']) || empty($data['amount']) || empty($data['paymentMethod'])) {
            return $this->json(['error' => 'Données incomplètes (scheduleId, amount, paymentMethod requis)'], Response::HTTP_BAD_REQUEST);
        }

        $schedule = $em->getRepository(PaymentSchedule::class)->find($data['scheduleId']);
        if (!$schedule) {
            return $this->json(['error' => 'Échéance introuvable'], Response::HTTP_NOT_FOUND);
        }

        $payment = new Payment();
        $payment->setSchedule($schedule);
        $payment->setEnrollment($schedule->getEnrollment());
        $payment->setUser($this->getUser());
        $payment->setAgentUser($this->getUser());
        $payment->setAmount((string) $data['amount']);
        $payment->setPaymentMethod($data['paymentMethod']); // CASH, WIRE_TRANSFER, CHECK
        $payment->setStatus('VALIDATED');
        $payment->setTransactionReference($data['transactionReference'] ?? null);
        $payment->setStructuredReferenceUsed($data['structuredReferenceUsed'] ?? $schedule->getEnrollment()?->getStructuredReference());
        $payment->setReceiptNumber('REC-' . date('Ymd') . '-' . rand(1000, 9999));
        $payment->setValidatedAt(new \DateTimeImmutable());

        $schedule->setStatus('PAID');

        $em->persist($payment);
        $em->flush();

        return $this->json([
            'message' => 'Paiement manuel enregistré et validé avec succès par l\'Agent Comptable',
            'receiptNumber' => $payment->getReceiptNumber(),
            'paymentId' => $payment->getId()
        ], Response::HTTP_CREATED);
    }

    #[Route('/sepa-xml', name: 'sepa_xml', methods: ['POST'])]
    public function generateSepaBatch(Request $request, EntityManagerInterface $em, SepaXmlGenerator $sepaGenerator): Response
    {
        $data = json_decode($request->getContent(), true);
        $scheduleIds = $data['scheduleIds'] ?? [];

        $schedules = $em->getRepository(PaymentSchedule::class)->findBy(['id' => $scheduleIds]);

        $xmlContent = $sepaGenerator->generatePain008Batch($schedules, new \DateTimeImmutable('tomorrow'));

        return new Response($xmlContent, Response::HTTP_OK, [
            'Content-Type' => 'application/xml',
            'Content-Disposition' => 'attachment; filename="sepa-batch-pain008-' . date('Ymd') . '.xml"'
        ]);
    }
}
