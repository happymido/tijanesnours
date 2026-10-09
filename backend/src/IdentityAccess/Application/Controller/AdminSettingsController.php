<?php

namespace App\IdentityAccess\Application\Controller;

use App\Schooling\Domain\Entity\SystemSetting;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/v1/admin/settings', name: 'api_v1_admin_settings_')]
class AdminSettingsController extends AbstractController
{
    #[Route('', name: 'get', methods: ['GET'])]
    public function getSettings(EntityManagerInterface $em): JsonResponse
    {
        $settings = $em->getRepository(SystemSetting::class)->findAll();
        $data = [
            'settings' => [],
            'list' => []
        ];

        foreach ($settings as $setting) {
            $data['settings'][$setting->getSettingKey()] = $setting->getSettingValue();
            $data['list'][] = [
                'id' => $setting->getId(),
                'key' => $setting->getSettingKey(),
                'value' => $setting->getSettingValue(),
                'label' => $setting->getSettingLabel(),
                'group' => $setting->getSettingGroup(),
                'updatedAt' => $setting->getUpdatedAt()?->format('d/m/Y H:i')
            ];
        }

        // Provide defaults if DB was empty for any key
        $defaults = [
            'tariff_1_child' => '690.00',
            'tariff_2_children' => '1300.00',
            'tariff_3_children' => '1800.00',
            'tariff_4_children' => '2400.00',
            'tranche_1_amount' => '230.00',
            'school_name' => 'École Tijanes Nours ASBL',
            'school_rcs' => 'RCS F12999',
            'school_address' => 'Centre Maryam / LJM Luxembourg',
            'school_email' => 'contact@tijanesnours.lu',
        ];

        foreach ($defaults as $key => $val) {
            if (!isset($data['settings'][$key])) {
                $data['settings'][$key] = $val;
            }
        }

        return $this->json($data);
    }

    #[Route('', name: 'update', methods: ['POST', 'PUT'])]
    public function updateSettings(
        Request $request,
        EntityManagerInterface $em,
        LoggerInterface $logger
    ): JsonResponse {
        $payload = json_decode($request->getContent(), true) ?? [];
        if (empty($payload)) {
            return $this->json(['error' => 'Payload invalide ou vide'], Response::HTTP_BAD_REQUEST);
        }

        $repo = $em->getRepository(SystemSetting::class);
        $updatedCount = 0;

        foreach ($payload as $key => $value) {
            if ($value === null) continue;
            
            $setting = $repo->findOneBy(['settingKey' => $key]);
            if (!$setting) {
                $setting = new SystemSetting();
                $setting->setSettingKey((string)$key);
                $setting->setSettingGroup(str_starts_with($key, 'tariff_') || str_contains($key, 'fee') || str_contains($key, 'tranche') ? 'FINANCE' : 'GENERAL');
                $em->persist($setting);
            }
            
            $setting->setSettingValue((string)$value);
            $updatedCount++;
        }

        $em->flush();

        $logger->info('Paramètres système mis à jour par l\'administrateur', [
            'keysUpdated' => array_keys($payload)
        ]);

        return $this->json([
            'message' => sprintf('%d paramètre(s) mis à jour avec succès', $updatedCount),
            'success' => true
        ]);
    }
}
