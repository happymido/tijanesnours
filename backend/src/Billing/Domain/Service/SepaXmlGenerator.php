<?php

namespace App\Billing\Domain\Service;

use App\Billing\Domain\Entity\PaymentSchedule;

class SepaXmlGenerator
{
    /**
     * Generates SEPA Direct Debit ISO 20022 Pain.008.001.02 XML document for batch processing
     */
    public function generatePain008Batch(array $schedules, \DateTimeInterface $executionDate): string
    {
        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><Document xmlns="urn:iso:std:iso:20022:tech:xsd:pain.008.001.02"/>');
        
        $cstmrDrctDbtInitn = $xml->addChild('CstmrDrctDbtInitn');
        
        // Group Header
        $grpHdr = $cstmrDrctDbtInitn->addChild('GrpHdr');
        $grpHdr->addChild('MsgId', 'TIJANES-SEPA-' . time());
        $grpHdr->addChild('CreDtTm', date('Y-m-d\TH:i:s'));
        $grpHdr->addChild('NbOfTxs', (string) count($schedules));
        $grpHdr->addChild('InitgPty')->addChild('Nm', 'Ecole Tijanes Nours ASBL');
        
        // Payment Information
        $pmtInf = $cstmrDrctDbtInitn->addChild('PmtInf');
        $pmtInf->addChild('PmtInfId', 'PMT-INF-' . date('Ymd-His'));
        $pmtInf->addChild('PmtMtd', 'DD');
        $pmtInf->addChild('NbOfTxs', (string) count($schedules));
        $pmtInf->addChild('ReqdColltnDt', $executionDate->format('Y-m-d'));
        
        $cdtr = $pmtInf->addChild('Cdtr');
        $cdtr->addChild('Nm', 'Ecole Tijanes Nours ASBL');
        
        foreach ($schedules as $schedule) {
            if (!$schedule instanceof PaymentSchedule) continue;
            
            $enrollment = $schedule->getEnrollment();
            $student = $enrollment?->getStudent();
            $parent = $student?->getParent();
            
            $drctDbtTxInf = $pmtInf->addChild('DrctDbtTxInf');
            
            $pmtId = $drctDbtTxInf->addChild('PmtId');
            $pmtId->addChild('EndToEndId', $enrollment->getStructuredReference() ?? ('SCHED-' . $schedule->getId()));
            
            $instdAmt = $drctDbtTxInf->addChild('InstdAmt', $schedule->getAmount());
            $instdAmt->addAttribute('Ccy', 'EUR');
            
            $drctDbtTx = $drctDbtTxInf->addChild('DrctDbtTx');
            $mndtRltdInf = $drctDbtTx->addChild('MndtRltdInf');
            $mndtRltdInf->addChild('MndtId', $parent?->getSepaMandateRef() ?? ('MANDATE-' . $parent?->getId()));
            $mndtRltdInf->addChild('DtOfSgntr', $parent?->getSepaMandateSignatureDate()?->format('Y-m-d') ?? date('Y-m-d'));
            
            $dbtrAgt = $drctDbtTxInf->addChild('DbtrAgt');
            $dbtrAgt->addChild('FinInstnId')->addChild('BIC', $parent?->getBic() ?? 'UNKNOWN');
            
            $dbtr = $drctDbtTxInf->addChild('Dbtr');
            $dbtr->addChild('Nm', $parent?->getFullName() ?? 'Parent');
            
            $dbtrAcct = $drctDbtTxInf->addChild('DbtrAcct');
            $dbtrAcct->addChild('Id')->addChild('IBAN', str_replace(' ', '', $parent?->getIban() ?? ''));
            
            $rmtInf = $drctDbtTxInf->addChild('RmtInf');
            $rmtInf->addChild('Strd')->addChild('CdtrRefInf')->addChild('Ref', $enrollment->getStructuredReference());
        }
        
        return $xml->asXML();
    }
}
