<?php

namespace App\IdentityAccess\Application\Command;

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
    description: 'Crée les comptes de démonstration (Admin, Agent Comptable, Parent) dans la base de données.',
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

        $usersData = [
            [
                'email' => 'admin@tijanesnours.lu',
                'password' => 'adminpassword123',
                'roles' => ['ROLE_ADMIN'],
                'label' => 'Administrateur Général'
            ],
            [
                'email' => 'comptable@tijanesnours.lu',
                'password' => 'agentpassword123',
                'roles' => ['ROLE_PAYMENT_AGENT'],
                'label' => 'Agent Comptable / Financier'
            ],
            [
                'email' => 'parent@tijanesnours.lu',
                'password' => 'parentpassword123',
                'roles' => ['ROLE_PARENT'],
                'label' => 'Responsable Légal (Parent)'
            ],
        ];

        foreach ($usersData as $data) {
            $user = $this->em->getRepository(User::class)->findOneBy(['email' => $data['email']]);
            if (!$user) {
                $user = new User();
                $user->setEmail($data['email']);
                $user->setPassword($this->hasher->hashPassword($user, $data['password']));
                $user->setRoles($data['roles']);
                $user->setLocale('fr');
                $this->em->persist($user);
                $io->success(sprintf('Création du compte %s: %s (mdp: %s)', $data['label'], $data['email'], $data['password']));
            } else {
                $io->info(sprintf('Le compte %s existe déjà.', $data['email']));
            }
        }

        $this->em->flush();

        return Command::SUCCESS;
    }
}
