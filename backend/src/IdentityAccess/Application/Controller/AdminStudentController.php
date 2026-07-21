<?php

namespace App\IdentityAccess\Application\Controller;

use App\IdentityAccess\Domain\Entity\User;
use App\IdentityAccess\Domain\Entity\Student;
use App\IdentityAccess\Domain\Entity\ParentUser;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/v1/admin/students', name: 'api_v1_admin_students_')]
class AdminStudentController extends AbstractController
{
    #[Route('', name: 'list', methods: ['GET'])]
    public function list(EntityManagerInterface $em): JsonResponse
    {
        $students = $em->getRepository(Student::class)->findAll();
        $data = [];

        foreach ($students as $student) {
            $parent = $student->getParent();
            $data[] = [
                'id' => $student->getId(),
                'firstName' => $student->getFirstName(),
                'lastName' => $student->getLastName(),
                'name' => $student->getFirstName() . ' ' . $student->getLastName(),
                'email' => $student->getUser() ? $student->getUser()->getEmail() : '',
                'role' => 'ROLE_STUDENT',
                'assignedGroup' => 'Classe Débutant 2A',
                'parentName' => $parent ? $parent->getFullName() : 'N/A',
                'contactInfo' => $parent ? $parent->getPhone() : '-'
            ];
        }

        return $this->json($data);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $hasher
    ): JsonResponse {
        $payload = json_decode($request->getContent(), true);

        $studentFirstName = $payload['studentFirstName'] ?? 'Élève';
        $studentLastName = $payload['studentLastName'] ?? 'Nouveau';
        $studentEmail = $payload['studentEmail'] ?? strtolower($studentFirstName) . '.' . time() . '@student.lu';

        $parentFullName = $payload['parentFullName'] ?? 'Parent Responsable';
        $parentEmail = $payload['parentEmail'] ?? 'parent.' . time() . '@tijanesnours.lu';
        $parentPhone = $payload['parentPhone'] ?? '+352 691 000 000';

        // 1. Chercher le compte User du Parent
        $userParent = $em->getRepository(User::class)->findOneBy(['email' => $parentEmail]);
        if (!$userParent) {
            $userParent = new User();
            $userParent->setEmail($parentEmail);
            $userParent->setPassword($hasher->hashPassword($userParent, 'parentpassword123'));
            $userParent->setRoles(['ROLE_PARENT']);
            $userParent->setLocale('fr');
            $em->persist($userParent);
        }

        $parentUserEntity = $em->getRepository(ParentUser::class)->findOneBy(['user' => $userParent]);
        if (!$parentUserEntity) {
            $parentUserEntity = new ParentUser();
            $parentUserEntity->setUser($userParent);
            $parentUserEntity->setFullName($parentFullName);
            $parentUserEntity->setPhone($parentPhone);
            $parentUserEntity->setAddress('Luxembourg');
            $em->persist($parentUserEntity);
        }

        // 2. Créer l'Élève
        $userStudent = new User();
        $userStudent->setEmail($studentEmail);
        $userStudent->setPassword($hasher->hashPassword($userStudent, 'studentpassword123'));
        $userStudent->setRoles(['ROLE_STUDENT']);
        $userStudent->setLocale('fr');
        $em->persist($userStudent);

        $studentEntity = new Student();
        $studentEntity->setUser($userStudent);
        $studentEntity->setParent($parentUserEntity);
        $studentEntity->setFirstName($studentFirstName);
        $studentEntity->setLastName($studentLastName);
        $studentEntity->setDateOfBirth(new \DateTimeImmutable('-8 years'));
        $em->persist($studentEntity);

        $em->flush();

        return $this->json([
            'message' => 'Élève et Parent enregistrés avec succès dans MySQL',
            'student' => [
                'id' => $studentEntity->getId(),
                'name' => $studentEntity->getFirstName() . ' ' . $studentEntity->getLastName(),
                'email' => $studentEmail,
                'role' => 'ROLE_STUDENT',
                'assignedGroup' => $payload['assignedGroup'] ?? 'Classe Débutant 2A',
                'parentName' => $parentUserEntity->getFullName(),
                'contactInfo' => $parentPhone
            ]
        ], Response::HTTP_CREATED);
    }
}
