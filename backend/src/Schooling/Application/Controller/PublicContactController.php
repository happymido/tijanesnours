<?php

namespace App\Schooling\Application\Controller;

use App\Schooling\Domain\Entity\SystemSetting;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/v1/public', name: 'api_v1_public_')]
class PublicContactController extends AbstractController
{
    #[Route('/contact', name: 'contact_submit', methods: ['POST'])]
    public function submitContact(
        Request $request,
        EntityManagerInterface $em,
        MailerInterface $mailer,
        ValidatorInterface $validator,
        LoggerInterface $logger
    ): JsonResponse {
        $payload = json_decode($request->getContent(), true) ?? [];

        $constraints = new Assert\Collection([
            'fullName' => [
                new Assert\NotBlank(['message' => 'Le nom complet est obligatoire.']),
                new Assert\Length(['min' => 2, 'max' => 100])
            ],
            'email' => [
                new Assert\NotBlank(['message' => 'L\'adresse email est obligatoire.']),
                new Assert\Email(['message' => 'L\'adresse email n\'est pas valide.'])
            ],
            'phone' => new Assert\Optional([
                new Assert\Length(['max' => 30])
            ]),
            'subject' => [
                new Assert\NotBlank(['message' => 'L\'objet du message est obligatoire.']),
                new Assert\Length(['min' => 3, 'max' => 150])
            ],
            'message' => [
                new Assert\NotBlank(['message' => 'Le contenu du message est obligatoire.']),
                new Assert\Length(['min' => 10, 'max' => 3000])
            ],
        ]);

        $violations = $validator->validate($payload, $constraints);
        if (count($violations) > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $property = trim($violation->getPropertyPath(), '[]');
                $errors[$property] = $violation->getMessage();
            }
            return $this->json([
                'status' => 'error',
                'message' => 'Données du formulaire invalides.',
                'errors' => $errors
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // Retrieve configured recipient email from DB system settings
        $settingRepo = $em->getRepository(SystemSetting::class);
        $setting = $settingRepo->findOneBy(['settingKey' => 'school_email']);
        $recipientEmail = $setting ? trim($setting->getSettingValue()) : 'contact@tijanesnours.lu';

        if (empty($recipientEmail) || !filter_var($recipientEmail, FILTER_VALIDATE_EMAIL)) {
            $recipientEmail = 'contact@tijanesnours.lu';
        }

        $fullName = htmlspecialchars(trim((string)$payload['fullName']), ENT_QUOTES, 'UTF-8');
        $senderEmail = trim((string)$payload['email']);
        $phone = !empty($payload['phone']) ? htmlspecialchars(trim((string)$payload['phone']), ENT_QUOTES, 'UTF-8') : 'Non renseigné';
        $subject = htmlspecialchars(trim((string)$payload['subject']), ENT_QUOTES, 'UTF-8');
        $messageContent = nl2br(htmlspecialchars(trim((string)$payload['message']), ENT_QUOTES, 'UTF-8'));
        $dateFormatted = date('d/m/Y à H:i');

        try {
            $email = (new Email())
                ->from('no-reply@tijanesnours.lu')
                ->to($recipientEmail)
                ->replyTo($senderEmail)
                ->subject("[Contact Site] " . $subject)
                ->html(<<<HTML
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333333; background-color: #f9f9f9; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 12px; overflow: hidden; border: 1px solid #e5e7eb; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        .header { background: #8CC63F; color: #ffffff; padding: 20px 24px; text-align: center; }
        .header h2 { margin: 0; font-size: 20px; }
        .content { padding: 24px; }
        .field { margin-bottom: 15px; }
        .label { font-weight: bold; color: #4b5563; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px; }
        .value { margin-top: 4px; font-size: 15px; color: #111827; }
        .message-box { background: #f3f4f6; border-left: 4px solid #8CC63F; padding: 16px; border-radius: 6px; margin-top: 15px; font-size: 15px; }
        .footer { background: #f9fafb; padding: 15px 24px; font-size: 12px; color: #6b7280; text-align: center; border-top: 1px solid #e5e7eb; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Nouveau message de contact</h2>
            <div style="font-size: 13px; opacity: 0.9; margin-top: 4px;">École Tijanes Nours</div>
        </div>
        <div class="content">
            <div class="field">
                <div class="label">Expéditeur :</div>
                <div class="value"><strong>{$fullName}</strong> (&lt;{$senderEmail}&gt;)</div>
            </div>
            <div class="field">
                <div class="label">Téléphone :</div>
                <div class="value">{$phone}</div>
            </div>
            <div class="field">
                <div class="label">Objet :</div>
                <div class="value">{$subject}</div>
            </div>
            <div class="field">
                <div class="label">Message :</div>
                <div class="message-box">{$messageContent}</div>
            </div>
        </div>
        <div class="footer">
            Message envoyé depuis le formulaire de contact du site Tijanes Nours le {$dateFormatted}.<br>
            Vous pouvez répondre directement à cet email pour contacter <strong>{$fullName}</strong>.
        </div>
    </div>
</body>
</html>
HTML);

            $mailer->send($email);

            $logger->info('Message de contact envoyé avec succès', [
                'recipient' => $recipientEmail,
                'sender' => $senderEmail,
                'subject' => $subject
            ]);

            return $this->json([
                'status' => 'success',
                'message' => 'Votre message a bien été envoyé. Notre équipe vous répondra dans les plus brefs délais.'
            ]);
        } catch (\Throwable $e) {
            $logger->error('Erreur lors de l\'envoi du message de contact : ' . $e->getMessage(), [
                'exception' => $e
            ]);

            return $this->json([
                'status' => 'error',
                'message' => 'Une erreur technique est survenue lors de l\'envoi. Veuillez réessayer ou nous contacter par téléphone.'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
