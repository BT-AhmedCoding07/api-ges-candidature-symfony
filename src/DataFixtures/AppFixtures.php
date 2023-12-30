<?php

namespace App\DataFixtures;

use App\Entity\Candidature;
use App\Entity\Referentiel;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private $userPasswordHasher;

    public function __construct(UserPasswordHasherInterface $userPasswordHasher)
    {
        $this->userPasswordHasher = $userPasswordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        // Création d'un user admin
            $userAdmin = new User();
            $userAdmin->setEmail("admin@simplon.com");
            $userAdmin->setRoles(["ROLE_ADMIN"]);
            $userAdmin->setPassword($this->userPasswordHasher->hashPassword($userAdmin, "password"));
            $manager->persist($userAdmin);
            // Création de deux autres candidats
        for ($i = 1; $i <= 2; $i++) {
            $candidat = new User();
            $candidat->setEmail("candidat$i@simplon.com");
            $candidat->setRoles(["ROLE_CANDIDAT"]);
            $candidat->setPassword($this->userPasswordHasher->hashPassword($candidat, "password"));
            $manager->persist($candidat);

            // Création d'une candidature pour chaque candidat
            $candidature = new Candidature();
            $candidature->setStatus('en attente'); // À vous de définir le statut initial
            $candidature->setUser($candidat);
            $candidature->setReferentiel($manager->getRepository(Referentiel::class)->find(rand(1, 10))); // Choisir un referentiel existant au hasard
            $manager->persist($candidature);
        }
        for ($i = 0; $i < 10; $i++) {
            $referentiel = new Referentiel();
            $referentiel->setLibelle(' Libelle ' . $i . 'du ref');
            $referentiel->setDescription('Description  ' . $i . 'du referentiel');
            $referentiel->setEcheances('mois' .  $i);
            $manager->persist($referentiel);
        }

        $manager->flush();
    }
}
