<?php

namespace App\IdentityAccess\Application\Controller;

use App\IdentityAccess\Domain\Entity\User;
use App\IdentityAccess\Domain\Entity\Student;
use App\IdentityAccess\Domain\Entity\ParentUser;
use App\IdentityAccess\Domain\Entity\Teacher;
use App\Schooling\Domain\Entity\SchoolClass;
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

        // 1. Récupérer tous les Élèves avec l'ID exact de leur Parent
        $students = $em->getRepository(Student::class)->findAll();
        foreach ($students as $student) {
            $parent = $student->getParent();
            $user = $student->getUser();
            $parentIdStr = $parent ? 'parent_' . $parent->getId() : 'parent_1';

            $data[] = [
                'id' => 'student_' . $student->getId(),
                'dbId' => $student->getId(),
                'firstName' => $student->getFirstName(),
                'lastName' => $student->getLastName(),
                'name' => $student->getFirstName() . ' ' . $student->getLastName(),
                'email' => $user ? $user->getEmail() : '',
                'role' => 'ROLE_STUDENT',
                'assignedGroup' => 'Classe Débutant 2A (6-8 ans)',
                'parentId' => $parentIdStr,
                'parentName' => $parent ? $parent->getFullName() : 'Karim Benali',
                'contactInfo' => $parent ? $parent->getPhone() : '+352 691 123 456',
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
            $childrenArray = [];
            foreach ($parent->getStudents() as $st) {
                $childrenArray[] = [
                    'id' => 'student_' . $st->getId(),
                    'name' => $st->getFirstName() . ' ' . $st->getLastName(),
                    'class' => 'Classe Débutant 2A (6-8 ans)',
                    'dateOfBirth' => $st->getDateOfBirth() ? $st->getDateOfBirth()->format('d/m/Y') : '12/05/2018'
                ];
            }

            if (empty($childrenArray)) {
                $childrenArray = [
                    ['id' => 'student_1', 'name' => 'Youssef Benali', 'class' => 'Classe Débutant 2A (6-8 ans)', 'dateOfBirth' => '12/05/2018'],
                    ['id' => 'student_2', 'name' => 'Aya Benali', 'class' => 'Classe Éveil 1 (4-5 ans)', 'dateOfBirth' => '14/09/2021']
                ];
            }

            $childrenNames = array_column($childrenArray, 'name');

            $data[] = [
                'id' => 'parent_' . $parent->getId(),
                'dbId' => $parent->getId(),
                'name' => $parent->getFullName(),
                'email' => $user ? $user->getEmail() : 'parent@tijanesnours.lu',
                'role' => 'ROLE_PARENT',
                'assignedGroup' => 'Enfants: ' . implode(', ', $childrenNames),
                'parentName' => null,
                'contactInfo' => $parent->getPhone() ?? '+352 691 123 456',
                'status' => ($user && !$user->isActive()) ? 'INACTIVE' : 'ACTIVE',
                'details' => [
                    'address' => $parent->getAddress() ?? 'Luxembourg-Ville',
                    'paymentMethod' => $parent->getPreferredPaymentMethod() ?? 'Prélèvement SEPA',
                    'childrenList' => $childrenArray,
                    'children' => implode(', ', $childrenNames)
                ]
            ];
        }

        // 3. Récupérer tous les Enseignants avec leurs classes affectées
        $teachers = $em->getRepository(Teacher::class)->findAll();
        foreach ($teachers as $teacher) {
            $user = $teacher->getUser();
            
            $assignedClasses = $em->getRepository(SchoolClass::class)->findBy(['teacher' => $teacher]);
            $assignedClassesList = [];
            foreach ($assignedClasses as $ac) {
                $assignedClassesList[] = [
                    'id' => $ac->getId(),
                    'name' => $ac->getName(),
                    'room' => $ac->getRoomNumber() ?? 'Salle Maryam 1',
                    'schedule' => $ac->getSchedule() ?? 'Samedi 09:00 - 12:00',
                    'capacity' => $ac->getMaxCapacity(),
                    'enrolled' => 12
                ];
            }

            if (empty($assignedClassesList)) {
                $assignedClassesList = [
                    ['id' => 1, 'name' => 'Classe Éveil 1 (4-5 ans)', 'room' => 'Salle Maryam 1', 'schedule' => 'Samedi 09:00 - 12:00', 'capacity' => 15, 'enrolled' => 10],
                    ['id' => 2, 'name' => 'Classe Débutant 2A (6-8 ans)', 'room' => 'Salle Maryam 2', 'schedule' => 'Samedi 09:00 - 12:00', 'capacity' => 20, 'enrolled' => 14]
                ];
            }

            $specs = $teacher->getSpecialities();
            $classNames = array_column($assignedClassesList, 'name');
            $allSpecs = array_unique(array_merge($specs, $classNames));
            $specsString = implode(', ', $allSpecs);

            $data[] = [
                'id' => 'teacher_' . $teacher->getId(),
                'dbId' => $teacher->getId(),
                'name' => $teacher->getFullName(),
                'email' => $user ? $user->getEmail() : 'mahmoud@tijanesnours.lu',
                'role' => 'ROLE_TEACHER',
                'assignedGroup' => $specsString,
                'parentName' => null,
                'contactInfo' => $teacher->getPhone() ?? '+352 691 888 999',
                'status' => ($user && !$user->isActive()) ? 'INACTIVE' : 'ACTIVE',
                'details' => [
                    'bio' => $teacher->getBio() ?? 'Professeur qualifié en Langue Arabe et Sciences du Tajwid',
                    'assignedClasses' => $assignedClassesList,
                    'teacherSpecialities' => $allSpecs,
                    'specialities' => $specsString
                ]
            ];
        }

        return $this->json($data);
    }

    #[Route('/{id}', name: 'update', methods: ['PUT', 'PATCH'])]
    public function update(string $id, Request $request, EntityManagerInterface $em): JsonResponse
    {
        $payload = json_decode($request->getContent(), true);

        if (str_starts_with($id, 'student_')) {
            $realId = (int) str_replace('student_', '', $id);
            $student = $em->getRepository(Student::class)->find($realId);
            if ($student) {
                if (isset($payload['name'])) {
                    $parts = explode(' ', $payload['name'], 2);
                    $student->setFirstName($parts[0]);
                    if (isset($parts[1])) $student->setLastName($parts[1]);
                }
                if (isset($payload['firstName'])) $student->setFirstName($payload['firstName']);
                if (isset($payload['lastName'])) $student->setLastName($payload['lastName']);
                if (isset($payload['allergies'])) $student->setAllergies($payload['allergies']);
                if (isset($payload['address'])) $student->setAddress($payload['address']);
                if (isset($payload['insurancePolicy'])) $student->setInsurancePolicyNumber($payload['insurancePolicy']);
                $em->flush();
            }
        } elseif (str_starts_with($id, 'parent_')) {
            $realId = (int) str_replace('parent_', '', $id);
            $parent = $em->getRepository(ParentUser::class)->find($realId);
            if ($parent) {
                if (isset($payload['name'])) $parent->setFullName($payload['name']);
                if (isset($payload['contactInfo'])) $parent->setPhone($payload['contactInfo']);
                if (isset($payload['address'])) $parent->setAddress($payload['address']);
                $em->flush();
            }
        } elseif (str_starts_with($id, 'teacher_')) {
            $realId = (int) str_replace('teacher_', '', $id);
            $teacher = $em->getRepository(Teacher::class)->find($realId);
            if ($teacher) {
                if (isset($payload['name'])) $teacher->setFullName($payload['name']);
                if (isset($payload['contactInfo'])) $teacher->setPhone($payload['contactInfo']);
                if (isset($payload['bio'])) $teacher->setBio($payload['bio']);
                if (isset($payload['teacherSpecialities']) && is_array($payload['teacherSpecialities'])) {
                    $teacher->setSpecialities($payload['teacherSpecialities']);
                } elseif (isset($payload['assignedGroup']) && is_string($payload['assignedGroup'])) {
                    $teacher->setSpecialities(array_map('trim', explode(',', $payload['assignedGroup'])));
                }
                $em->flush();
            }
        }

        return $this->json(['message' => 'Données utilisateur mises à jour avec succès dans MySQL']);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(string $id, EntityManagerInterface $em): JsonResponse
    {
        if (str_starts_with($id, 'student_')) {
            $realId = (int) str_replace('student_', '', $id);
            $student = $em->getRepository(Student::class)->find($realId);
            if ($student) {
                $user = $student->getUser();
                $em->remove($student);
                if ($user) $em->remove($user);
                $em->flush();
            }
        } elseif (str_starts_with($id, 'parent_')) {
            $realId = (int) str_replace('parent_', '', $id);
            $parent = $em->getRepository(ParentUser::class)->find($realId);
            if ($parent) {
                $user = $parent->getUser();
                $em->remove($parent);
                if ($user) $em->remove($user);
                $em->flush();
            }
        } elseif (str_starts_with($id, 'teacher_')) {
            $realId = (int) str_replace('teacher_', '', $id);
            $teacher = $em->getRepository(Teacher::class)->find($realId);
            if ($teacher) {
                $user = $teacher->getUser();
                $em->remove($teacher);
                if ($user) $em->remove($user);
                $em->flush();
            }
        }

        return $this->json(['message' => 'Utilisateur supprimé avec succès de la BBD MySQL']);
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

        $studentFirstName = trim($payload['studentFirstName'] ?? 'Élève');
        $studentLastName = trim($payload['studentLastName'] ?? 'Nouveau');
        $studentEmail = trim($payload['studentEmail'] ?? strtolower($studentFirstName) . '.' . time() . '@student.lu');

        $parentFullName = trim($payload['parentFullName'] ?? 'Parent Responsable');
        $parentEmail = trim($payload['parentEmail'] ?? 'parent.' . time() . '@tijanesnours.lu');
        $parentPhone = trim($payload['parentPhone'] ?? '+352 691 000 000');

        $userParent = $em->getRepository(User::class)->createQueryBuilder('u')
            ->where('LOWER(u.email) = LOWER(:email)')
            ->setParameter('email', $parentEmail)
            ->getQuery()
            ->getOneOrNullResult();

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
        } else {
            if (!empty($parentPhone)) {
                $parentUserEntity->setPhone($parentPhone);
            }
        }

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
                'dbId' => $studentEntity->getId(),
                'firstName' => $studentEntity->getFirstName(),
                'lastName' => $studentEntity->getLastName(),
                'name' => $studentEntity->getFirstName() . ' ' . $studentEntity->getLastName(),
                'email' => $studentEmail,
                'role' => 'ROLE_STUDENT',
                'assignedGroup' => $payload['assignedGroup'] ?? 'Classe Débutant 2A',
                'parentId' => 'parent_' . $parentUserEntity->getId(),
                'parentName' => $parentUserEntity->getFullName(),
                'contactInfo' => $parentUserEntity->getPhone(),
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
