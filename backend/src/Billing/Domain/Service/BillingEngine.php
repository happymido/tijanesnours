<?php

namespace App\Billing\Domain\Service;

use App\Billing\Domain\Entity\PaymentSchedule;
use App\Schooling\Domain\Entity\Enrollment;

class BillingEngine
{
    private const BASE_ANNUAL_FEE = 450.00;
    private const REGISTRATION_FEE = 50.00;

    /**
     * Calculates multi-child family discounts
     * 1st child: 0% discount
     * 2nd child: 15% discount
     * 3rd+ child: 25% discount
     */
    public function calculateDiscount(int $childIndex): float
    {
        return match (true) {
            $childIndex === 1 => 0.15,
            $childIndex >= 2 => 0.25,
            default => 0.00,
        };
    }

    /**
     * Generates installment payment schedule for an enrollment based on frequency
     * Frequencies: ANNUAL (1x), SEMESTRIAL (2x), TRIMESTRIAL (3x), MONTHLY (9x)
     */
    public function generateSchedules(Enrollment $enrollment, string $frequency): array
    {
        $total = (float) $enrollment->getTotalAmount();
        $schedules = [];

        $installmentsCount = match ($frequency) {
            'ANNUAL' => 1,
            'SEMESTRIAL' => 2,
            'TRIMESTRIAL' => 3,
            'MONTHLY' => 9,
            default => 1,
        };

        $installmentAmount = round($total / $installmentsCount, 2);
        $baseDate = new \DateTimeImmutable('2026-09-01');

        for ($i = 0; $i < $installmentsCount; $i++) {
            $schedule = new PaymentSchedule();
            $schedule->setEnrollment($enrollment);
            $schedule->setTitle(sprintf('Échéance %d/%d (%s)', $i + 1, $installmentsCount, $frequency));
            
            $intervalMonths = match ($frequency) {
                'ANNUAL' => 0,
                'SEMESTRIAL' => $i * 5,
                'TRIMESTRIAL' => $i * 3,
                'MONTHLY' => $i * 1,
                default => 0,
            };
            
            $schedule->setDueDate($baseDate->modify(sprintf('+%d month', $intervalMonths)));
            $schedule->setAmount(number_format($installmentAmount, 2, '.', ''));
            $schedule->setStatus('PENDING');
            
            $schedules[] = $schedule;
        }

        return $schedules;
    }
}
