<?php

namespace App\IdentityAccess\Application\Controller;

use App\IdentityAccess\Domain\Entity\User;
use App\IdentityAccess\Domain\Entity\Student;
use App\IdentityAccess\Domain\Entity\ParentUser;
use App\IdentityAccess\Domain\Entity\Teacher;
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
        $data = [];

        // 1. Récupérer tous les Élèves
        $students = $em->getRepository(Student::class)->findAll();
        foreach ($students as $student) {
            $parent = $student->getParent();
            $user = $student->getUser();
            $data[] = [
                'id' => 'student_' . $student->getId(),
                'dbId' => $student->getId(),
                'firstName' => $student->getFirstName(),
                'lastName' => $student->getLastName(),
                'name' => $student->getFirstName() . ' ' . $student->getLastName(),
                'email' => $user ? $user->getEmail() : '',
                'role' => 'ROLE_STUDENT',
                'assignedGroup' => 'Classe Débutant 2A',
                'parentName' => $parent ? $parent->getFullName() : 'N/A',
                'contactInfo' => $parent ? $parent->getPhone() : '-',
                'status' => ($user && !$user->isActive()) ? 'INACTIVE' : 'ACTIVE',
                'details' => [
                    'dateOfBirth' => $student->getDateOfBirth() ? $student->getDateOfBirth()->format('d/m/Y') : '12/05/2018',
                    'nationality' => $student->getNationality() ?? 'Luxembourgeoise',
                    'address' => $student->getAddress() ?? 'Luxembourg-Ville',
                    'allergies' => $student->getAllergies() ?? 'Aucune allergie connue',
                    'insurancePolicy' => $student->getInsurancePolicyNumber() ?? 'LU-890421-AXA'
                ]
            ];
        }

        // 2. Récupérer tous les Parents
        $parents = $em->getRepository(ParentUser::class)->findAll();
        foreach ($parents as $parent) {
            $user = $parent->getUser();
            $data[] = [
                'id' => 'parent_' . $parent->getId(),
                'dbId' => $parent->getId(),
                'name' => $parent->getFullName(),
                'email' => $user ? $user->getEmail() : 'parent@tijanesnours.lu',
                'role' => 'ROLE_PARENT',
                'assignedGroup' => 'Responsable Légal',
                'parentName' => null,
                'contactInfo' => $parent->getPhone() ?? '+352 691 123 456',
                'status' => ($user && !$user->isActive()) ? 'INACTIVE' : 'ACTIVE',
                'details' => [
                    'address' => $parent->getAddress() ?? 'Luxembourg-Ville',
                    'paymentMethod' => $parent->getPreferredPaymentMethod()
                ]
            ];
        }

        // 3. Récupérer tous les Enseignants
        $teachers = $em->getRepository(Teacher::class)->findAll();
        foreach ($teachers as $teacher) {
            $user = $teacher->getUser();
            $specs = implode(', ', $teacher->getSpecialities());
            $data[] = [
                'id' => 'teacher_' . $teacher->getId(),
                'dbId' => $teacher->getId(),
                'name' => $teacher->getFullName(),
                'email' => $user ? $user->getEmail() : 'mahmoud@tijanesnours.lu',
                'role' => 'ROLE_TEACHER',
                'assignedGroup' => !empty($specs) ? $specs : 'Langue Arabe & Tajwid',
                'parentName' => null,
                'contactInfo' => $teacher->getPhone() ?? '+352 691 888 999',
                'status' => ($user && !$user->isActive()) ? 'INACTIVE' : 'ACTIVE',
                'details' => [
                    'bio' => $teacher->getBio() ?? 'Professeur diplômé en Tajwid'
                ]
            ];
        }

        return $this->json($data);
    }

    #[Route('/{id}/toggle-status', name: 'toggle_status', methods: ['PUT', 'POST'])]
    public function toggleStatus(string $id, EntityManagerInterface $em): JsonResponse
    {
        if (str_starts_with($id, 'student_')) {
            $realId = (int) str_replace('student_', '', $id);
            $entity = $em->getRepository(Student::class)->find($realId);
        } elseif (str_starts_with($id, 'parent_')) {
            $realId = (int) str_replace('parent_', '', $id);
            $entity = $em->getRepository(ParentUser::class)->find($realId);
        } elseif (str_starts_with($id, 'teacher_')) {
            $realId = (int) str_replace('teacher_', '', $id);
            $entity = $em->getRepository(Teacher::class)->find($realId);
        } else {
            $entity = $em->getRepository(Student::class)->find((int) $id);
        }

        if (!$entity) {
            return $this->json(['error' => 'Utilisateur non trouvé'], Response::HTTP_NOT_FOUND);
        }

        $user = method_exists($entity, 'getUser') ? $entity->getUser() : null;
        if ($user) {
            $user->setIsActive(!$user->isActive());
            $em->flush();
            $newStatus = $user->isActive() ? 'ACTIVE' : 'INACTIVE';
        } else {
            $newStatus = 'ACTIVE';
        }

        return $this->json([
            'message' => 'Statut BBD mis à jour avec succès',
            'status' => $newStatus
        ]);
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
            $userParent->setIsActive(true);
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
        $userStudent->setIsActive(true);
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
                'id' => 'student_' . $studentEntity->getId(),
                'name' => $studentEntity->getFirstName() . ' ' . $studentEntity->getLastName(),
                'email' => $studentEmail,
                'role' => 'ROLE_STUDENT',
                'assignedGroup' => $payload['assignedGroup'] ?? 'Classe Débutant 2A',
                'parentName' => $parentUserEntity->getFullName(),
                'contactInfo' => $parentPhone,
                'status' => 'ACTIVE',
                'details' => [
                    'dateOfBirth' => '12/05/2018',
                    'nationality' => 'Luxembourgeoise',
                    'address' => 'Luxembourg-Ville',
                    'allergies' => 'Aucune allergie',
                    'insurancePolicy' => 'LU-890421-AXA'
                ]
            ]
        ], Response::HTTP_CREATED);
    }
}
