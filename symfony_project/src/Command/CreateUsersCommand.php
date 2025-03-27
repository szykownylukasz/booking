<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class CreateUsersCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('app:create-users')
            ->setDescription('Creates default users');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Create admin user
        $admin = new User();
        $admin->setUsername('admin');
        $admin->setRoles(['ROLE_ADMIN']);
        $hashedPassword = $this->passwordHasher->hashPassword($admin, 'admin');
        $admin->setPassword($hashedPassword);
        
        $this->entityManager->persist($admin);

        // Create first regular user
        $user1 = new User();
        $user1->setUsername('user1');
        $user1->setRoles(['ROLE_USER']);
        $hashedPassword = $this->passwordHasher->hashPassword($user1, 'user1');
        $user1->setPassword($hashedPassword);
        
        $this->entityManager->persist($user1);

        // Create second regular user
        $user2 = new User();
        $user2->setUsername('user2');
        $user2->setRoles(['ROLE_USER']);
        $hashedPassword = $this->passwordHasher->hashPassword($user2, 'user2');
        $user2->setPassword($hashedPassword);
        
        $this->entityManager->persist($user2);

        $this->entityManager->flush();

        $output->writeln('Users created successfully!');

        return Command::SUCCESS;
    }
}
