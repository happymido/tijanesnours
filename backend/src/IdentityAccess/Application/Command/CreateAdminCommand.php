<?php

namespace App\IdentityAccess\Application\Command;

use App\IdentityAccess\Domain\Entity\ParentUser;
use App\IdentityAccess\Domain\Entity\Student;
use App\IdentityAccess\Domain\Entity\Teacher;
use App\IdentityAccess\Domain\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-users',
    description: 'Crée et peuple les données de démonstration MySQL (Admin, Comptable, Parents, Enseignants, Élèves).',
)]
class CreateAdminCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly UserPasswordHasherInterface $hasher
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // 1. Admin
        $admin = $this->em->getRepository(User::class)->findOneBy(['email' => 'admin@tijanesnours.lu']);
        if (!$admin) {
            $admin = new User();
            $admin->setEmail('admin@tijanesnours.lu');
            $admin->setPassword($this->hasher->hashPassword($admin, 'adminpassword123'));
            $admin->setRoles(['ROLE_ADMIN']);
            $admin->setLocale('fr');
            $this->em->persist($admin);
        }

        // 2. Agent Comptable
        $agent = $this->em->getRepository(User::class)->findOneBy(['email' => 'comptable@tijanesnours.lu']);
        if (!$agent) {
            $agent = new User();
            $agent->setEmail('comptable@tijanesnours.lu');
            $agent->setPassword($this->hasher->hashPassword($agent, 'agentpassword123'));
            $agent->setRoles(['ROLE_PAYMENT_AGENT']);
            $agent->setLocale('fr');
            $this->em->persist($agent);
        }

        // 3. Parent
        $parentUser = $this->em->getRepository(User::class)->findOneBy(['email' => 'parent@tijanesnours.lu']);
        if (!$parentUser) {
            $parentUser = new User();
            $parentUser->setEmail('parent@tijanesnours.lu');
            $parentUser->setPassword($this->hasher->hashPassword($parentUser, 'parentpassword123'));
            $parentUser->setRoles(['ROLE_PARENT']);
            $parentUser->setLocale('fr');
            $this->em->persist($parentUser);
        }

        $parentEntity = $this->em->getRepository(ParentUser::class)->findOneBy(['user' => $parentUser]);
        if (!$parentEntity) {
            $parentEntity = new ParentUser();
            $parentEntity->setUser($parentUser);
            $parentEntity->setFullName('Karim Benali');
            $parentEntity->setPhone('+352 691 123 456');
            $parentEntity->setAddress('Luxembourg-Ville');
            $this->em->persist($parentEntity);
        }

        // 4. Élèves de test
        $studentUser = $this->em->getRepository(User::class)->findOneBy(['email' => 'youssef@student.lu']);
        if (!$studentUser) {
            $studentUser = new User();
            $studentUser->setEmail('youssef@student.lu');
            $studentUser->setPassword($this->hasher->hashPassword($studentUser, 'studentpassword123'));
            $studentUser->setRoles(['ROLE_STUDENT']);
            $studentUser->setLocale('fr');
            $this->em->persist($studentUser);

            $studentEntity = new Student();
            $studentEntity->setUser($studentUser);
            $studentEntity->setParent($parentEntity);
            $studentEntity->setFirstName('Youssef');
            $studentEntity->setLastName('Benali');
            $studentEntity->setDateOfBirth(new \DateTimeImmutable('2018-05-12'));
            $this->em->persist($studentEntity);
        }

        // 5. Enseignant de test
        $teacherUser = $this->em->getRepository(User::class)->findOneBy(['email' => 'mahmoud@tijanesnours.lu']);
        if (!$teacherUser) {
            $teacherUser = new User();
            $teacherUser->setEmail('mahmoud@tijanesnours.lu');
            $teacherUser->setPassword($this->hasher->hashPassword($teacherUser, 'teacherpassword123'));
            $teacherUser->setRoles(['ROLE_TEACHER']);
            $teacherUser->setLocale('ar');
            $this->em->persist($teacherUser);

            $teacherEntity = new Teacher();
            $teacherEntity->setUser($teacherUser);
            $teacherEntity->setFullName('Cheikh Mahmoud');
            $teacherEntity->setPhone('+352 691 888 999');
            $teacherEntity->setSpecialities(['Langue Arabe & Tajwid']);
            $this->em->persist($teacherEntity);
        }

        $this->em->flush();
        $io->success('Toutes les données de test (Admin, Comptable, Parent, Élève, Enseignant) sont peuplées dans MySQL !');

        return Command::SUCCESS;
    }
}
