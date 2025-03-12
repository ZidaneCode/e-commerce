<?php

namespace App\DataFixtures;

use App\Entity\Users;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\String\Slugger\SluggerInterface;
use Faker;

class UsersFixtures extends Fixture
{
    public function __construct(private UserPasswordHasherInterface $passwordEncoder,
                                private SluggerInterface $slugger
    ){}
    public function load(ObjectManager $manager): void
    {
        $admin= new Users();
        $admin->setEmail('touazidjoudi@gmail.com');
        $admin->setLastname("Touzi");
        $admin->setFirstname('Djoudi');
        $admin->setAddress('Rue-07-bejaia');
        $admin->setZipcode('06006');
        $admin->setCity('Bejaia');
        $admin->setPassword($this->passwordEncoder->hashPassword($admin,'admin'));
        $admin->setRoles(['ROLE_ADMIN']);
        $manager->persist($admin);

        $faker = Faker\Factory::create('fr_FR');


        for($usr=1 ; $usr <=5  ;$usr++){

            $user= new Users();
            $user->setEmail($faker->email);
            $user->setLastname($faker->lastName);
            $user->setFirstname($faker->firstName);
            $user->setAddress($faker->streetAddress);
            $user->setZipcode(str_replace(' ','' ,$faker->postCode));
            $user->setCity($faker->city);
            $user->setPassword($this->passwordEncoder->hashPassword($user,'secret'));
            dump($user);
            $manager->persist($user);
        }


        $manager->flush();
    }
}
