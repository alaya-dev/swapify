<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Console\Style\SymfonyStyle;


class CreateSuperAdminCommand extends Command
{
    private $entityManager;
    private $passwordHasher;
    private $validator;

    public function __construct(EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher, ValidatorInterface $validator)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->passwordHasher = $passwordHasher;
        $this->validator = $validator;
    }

    protected static $defaultName = 'app:create-super-admin';

    protected function configure()
    {
        $this
            ->setDescription('Créer un super administrateur de manière interactive');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Utilisation de SymfonyStyle pour une meilleure interaction
        $io = new SymfonyStyle($input, $output);

        // Demander l'email
        $email = $io->ask('Veuillez entrer l\'email du super administrateur : ');

        // Vérification si l'email existe déjà
        $existingUser = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
        if ($existingUser) {
            $io->error('Un utilisateur avec cet email existe déjà !');
            return Command::FAILURE;
        }

        // Demander le mot de passe
        $password = $io->askHidden('Veuillez entrer le mot de passe du super administrateur : ');

        // Demander les autres informations : nom, prénom, téléphone, adresse, etc.
        $nom = $io->ask('Veuillez entrer le nom du super administrateur : ');
        $prenom = $io->ask('Veuillez entrer le prénom du super administrateur : ');
        $dateNaissance = $io->ask('Veuillez entrer la date de naissance du super administrateur (YYYY-MM-DD) : ');
        $tel = $io->ask('Veuillez entrer le numéro de téléphone du super administrateur : ');
        $adresse = $io->ask('Veuillez entrer l\'adresse du super administrateur : ');

        // Créer un nouvel utilisateur
        $user = new User();
        $user->setEmail($email);
        $user->setNom($nom);
        $user->setPrenom($prenom);
        $user->setDateNaissance(new \DateTime($dateNaissance));
        $user->setTel($tel);
        $user->setAdresse($adresse);

        // Hacher le mot de passe
        $hashedPassword = $this->passwordHasher->hashPassword($user, $password);
        $user->setPassword($hashedPassword);

        // Assigner le rôle "ROLE_SUPER_ADMIN"
        $user->setRoles(['ROLE_SUPER_ADMIN']);

        // Valider l'utilisateur avec les contraintes de l'entité (y compris celles de "tel", "email", etc.)
        $errors = $this->validator->validate($user);

        if (count($errors) > 0) {
            // Si des erreurs sont détectées, afficher les messages d'erreur
            foreach ($errors as $error) {
                $io->error($error->getMessage());
            }
            return Command::FAILURE;
        }

        // Sauvegarder dans la base de données
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $io->success('Le super administrateur a été créé avec succès.');

        return Command::SUCCESS;
    }
}
