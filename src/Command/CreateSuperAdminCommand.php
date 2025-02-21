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
use Symfony\Component\Validator\Constraints as Assert;

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

        // Vérification de la validité de l'email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $io->error('L\'email n\'est pas valide. Veuillez entrer un email sous la forme exemple@domaine.com.');
            return Command::FAILURE;
        }


        // Demander le mot de passe avec confirmation
        while (true) {
            $password = $io->askHidden('Veuillez entrer le mot de passe du super administrateur : ');

            // Vérifier que le mot de passe contient au moins 8 caractères
            if (strlen($password) < 8) {
                $io->error('Le mot de passe doit contenir au moins 8 caractères.');
                continue;
            }

            $passwordConfirmation = $io->askHidden('Veuillez confirmer le mot de passe : ');

            if ($password !== $passwordConfirmation) {
                $io->error('Les mots de passe ne correspondent pas. Veuillez réessayer.');
                continue;
            }

            break;
        }


        // Demander le nom
        while (true) {
            $nom = $io->ask('Veuillez entrer le nom du super administrateur : ');
            if (strlen($nom) < 3) {
                $io->error('Le nom doit contenir au moins 3 caractères.');
                continue;
            }
            break;
        }

        // Demander le prénom
        while (true) {
            $prenom = $io->ask('Veuillez entrer le prénom du super administrateur : ');
            if (strlen($prenom) < 3) {
                $io->error('Le prénom doit contenir au moins 3 caractères.');
                continue;
            }
            break;
        }


        while (true) {
            $dateNaissance = $io->ask('Veuillez entrer la date de naissance du super administrateur (YYYY-MM-DD) : ');
        
            // Vérifier si la date respecte le format YYYY-MM-DD
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateNaissance)) {
                $io->error('Format de date invalide. Utilisez le format YYYY-MM-DD.');
                continue;
            }
        
            try {
                $dateNaissanceObj = new \DateTime($dateNaissance);
                break; // Sortir de la boucle si la date est valide
            } catch (\Exception $e) {
                $io->error('Date invalide. Assurez-vous qu\'elle est correcte.');
            }
        }
        


        $tel = $io->ask('Veuillez entrer le numéro de téléphone du super administrateur : ');


                // Vérifier si le numéro de téléphone contient exactement 8 chiffres
        if (!preg_match('/^\d{8}$/', $tel)) {
            $io->error('Le numéro de téléphone doit contenir exactement 8 chiffres.');
            return Command::FAILURE;
        }

        // Vérifier si le numéro de téléphone existe déjà
        $existingUserWithTel = $this->entityManager->getRepository(User::class)->findOneBy(['tel' => $tel]);
        if ($existingUserWithTel) {
            $io->error('Un utilisateur avec ce numéro de téléphone existe déjà !');
            return Command::FAILURE;
        }

        $adresse = $io->ask('Veuillez entrer l\'adresse du super administrateur : ');

        // Créer un nouvel utilisateur
        $user = new User();
        $user->setEmail($email);
        $user->setNom($nom);
        $user->setPrenom($prenom);
        $user->setDateNaissance(new \DateTime($dateNaissance));
        $user->setTel($tel);
        $user->setAdresse($adresse);

        // Vérifier si l'utilisateur a au moins 18 ans
        $now = new \DateTime();
        $age = $now->diff($user->getDateNaissance())->y;
        if ($age < 18) {
            $io->error('Le super administrateur doit avoir au moins 18 ans.');
            return Command::FAILURE;
        }

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
