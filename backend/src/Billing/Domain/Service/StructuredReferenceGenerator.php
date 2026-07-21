<?php

namespace App\Billing\Domain\Service;

class StructuredReferenceGenerator
{
    /**
     * Generates an ISO 11649 Structured Creditor Reference (RF format)
     * Example output: RF12 2026 0042 89
     */
    public function generateIso11649(int $enrollmentId, int $year = 2026): string
    {
        $rawNumber = sprintf('%d%06d', $year, $enrollmentId);
        
        // Append "271500" (RF + 00 converted to digits: R=27, F=15)
        $numericString = $rawNumber . '271500';
        
        // Calculate modulo 97 checksum
        $checksum = 98 - (int) bcmod($numericString, '97');
        $checksumFormatted = sprintf('%02d', $checksum);

        return sprintf('RF%s-%s-%s', $checksumFormatted, $year, sprintf('%06d', $enrollmentId));
    }
}
