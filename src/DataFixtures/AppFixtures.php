<?php

namespace App\DataFixtures;

use App\Entity\Brand;
use App\Entity\Client;
use App\Entity\Smartphone;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $brand1 = new Brand();
        $brand1->setName('Brand one');
        $manager->persist($brand1);

        $brand2 = new Brand();
        $brand2->setName('Brand two');
        $manager->persist($brand2);

        $client1 = new Client();
        $client1->setName('Client One');
        $manager->persist($client1);

        $client2 = new Client();
        $client2->setName('Client two');
        $manager->persist($client2);

        for ($i = 0; $i < 20; $i++){
            $smartphone = new Smartphone();
            $smartphone->setTitle('Smartphone '. $i )
                ->setSlug('smartphone '. $i )
                ->setPrice(25*$i)
                ->setDescription('A big description of a good smartphone. Buy it !')
                ->setBrand( $i < 10 ? $brand1 : $brand2)
            ;

            $manager->persist($smartphone);

            $user = new User();
            $user
                ->setUsername('username' . $i)
                ->setAddress($i . 'fixtures street')
                ->setEmail($i . 'test@test.com')
                ->setCellphone('+33666666666')
                ->setClient($i < 10 ? $client1 : $client2)
                ->setPassword('this_is_password')
                ->setFirstname('Firstname' . $i)
                ->setLastname('Lastname' . $i)
                ->setIsRegistered($i%2 ? 0 : 1)
                ->setZipcode($i . $i . ' ' . $i . $i . $i)
                ->setRole($i%4 ? (array)'ROLE_BASE' : (array)'ROLE_PREMIUM')
            ;

            $manager->persist($user);
        }

        $manager->flush();
    }
}
