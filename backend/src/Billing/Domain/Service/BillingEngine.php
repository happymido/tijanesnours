<?php

namespace App\Billing\Domain\Service;

use App\Billing\Domain\Entity\PaymentSchedule;
use App\Schooling\Domain\Entity\Enrollment;
use App\Schooling\Domain\Entity\SystemSetting;
use Doctrine\ORM\EntityManagerInterface;

class BillingEngine
{
    private ?EntityManagerInterface $em = null;

    public function __construct(?EntityManagerInterface $em = null)
    {
        $this->em = $em;
    }

    private function getSetting(string $key, float $default): float
    {
        if (!$this->em) {
            return $default;
        }

        try {
            $setting = $this->em->getRepository(SystemSetting::class)->findOneBy(['settingKey' => $key]);
            if ($setting && is_numeric($setting->getSettingValue())) {
                return (float) $setting->getSettingValue();
            }
        } catch (\Throwable $e) {
            // Fallback to default if table doesn't exist yet
        }

        return $default;
    }

    /**
     * Calculates family pricing according to school tariff (configurable in DB):
     * 1 child  = 690 €
     * 2 children = 1300 €
     * 3 children = 1800 €
     * 4 children = 2400 €
     */
    public function getFamilyPackagePrice(int $totalChildren): float
    {
        $t1 = $this->getSetting('tariff_1_child', 690.00);
        $t2 = $this->getSetting('tariff_2_children', 1300.00);
        $t3 = $this->getSetting('tariff_3_children', 1800.00);
        $t4 = $this->getSetting('tariff_4_children', 2400.00);

        return match ($totalChildren) {
            1 => $t1,
            2 => $t2,
            3 => $t3,
            4 => $t4,
            default => $totalChildren > 4 ? ($t4 + ($totalChildren - 4) * 600.00) : $t1,
        };
    }

    /**
     * Returns individual child price depending on rank in family
     */
    public function calculateChildPrice(int $childIndex): float
    {
        $t1 = $this->getSetting('tariff_1_child', 690.00);
        $t2 = $this->getSetting('tariff_2_children', 1300.00);
        $t3 = $this->getSetting('tariff_3_children', 1800.00);
        $t4 = $this->getSetting('tariff_4_children', 2400.00);

        return match ($childIndex) {
            1 => $t1,
            2 => max(0.0, $t2 - $t1), // e.g. 1300 - 690 = 610 €
            3 => max(0.0, $t3 - $t2), // e.g. 1800 - 1300 = 500 €
            4 => max(0.0, $t4 - $t3), // e.g. 2400 - 1800 = 600 €
            default => 600.00,
        };
    }

    /**
     * Generates installment payment schedule for an enrollment based on frequency
     * Frequencies: ANNUAL (1x), SEMESTRIAL (2x), TRIMESTRIAL (3x), MONTHLY (9x)
     */
    public function generateSchedules(Enrollment $enrollment, string $frequency): array
    {
        $total = (float) $enrollment->getTotalAmount();
        if ($total <= 0.0) {
            $total = $this->getSetting('tariff_1_child', 690.00);
        }
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
