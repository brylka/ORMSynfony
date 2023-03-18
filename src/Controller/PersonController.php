<?php

namespace App\Controller;

use App\Entity\Person;
use Doctrine\ORM\EntityManagerInterface;
use Faker\Factory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PersonController extends AbstractController
{
    #[Route('/person', name: 'app_person')]
    public function index(): Response
    {
        return $this->render('person/index.html.twig', [
            'controller_name' => 'PersonController',
        ]);
    }

    #[Route('/generate-random-persons', name: 'app_generate_random_persons')]
    public function generateRandomPersons(EntityManagerInterface $entityManager): Response
    {

        $entityManager->getConnection()->executeStatement('TRUNCATE TABLE person');

        $faker = Factory::create();
        $people = [];

        for ($i = 0; $i < 100; $i++) {
            $person = new Person();
            $person->setName($faker->firstName)
                ->setSurname($faker->lastName)
                ->setAge($faker->numberBetween(18, 100))
                ->setEmail($faker->safeEmail);

            $entityManager->persist($person);
            $people[] = $person;
        }

        $entityManager->flush();

        return $this->render('person/random_people.html.twig', [
            'people' => $people,
        ]);
    }
}
