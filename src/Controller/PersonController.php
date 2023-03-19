<?php

namespace App\Controller;

use App\Entity\Person;
use Doctrine\ORM\EntityManagerInterface;
use Faker\Factory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class PersonController extends AbstractController
{
    #[Route('/person', name: 'app_person')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $people = $entityManager->getRepository(Person::class)->findAll();

        return $this->render('person/index.html.twig', [
            'people' => $people,
        ]);
    }

    #[Route('/person/create', name: 'app_person_create')]
    public function create(Request $request, EntityManagerInterface $entityManager): Response
    {
        $person = new Person();

        $form = $this->createFormBuilder($person)
            ->add('name', TextType::class)
            ->add('surname', TextType::class)
            ->add('age', IntegerType::class)
            ->add('email', EmailType::class)
            ->add('save', SubmitType::class, ['label' => 'Create Person'])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($person);
            $entityManager->flush();

            return $this->redirectToRoute('app_person');
        }

        return $this->render('person/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/person/{id}/edit', name: 'app_person_edit', requirements: ['id' => '\d+'])]
    public function edit(Request $request, EntityManagerInterface $entityManager, Person $person): Response
    {
        $form = $this->createFormBuilder($person)
            ->add('name', TextType::class)
            ->add('surname', TextType::class)
            ->add('age', IntegerType::class)
            ->add('email', EmailType::class)
            ->add('save', SubmitType::class, ['label' => 'Update Person'])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_person');
        }

        return $this->render('person/edit.html.twig', [
            'form' => $form->createView(),
            'person' => $person,
        ]);
    }

    #[Route('/person/{id}/delete', name: 'app_person_delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function delete(EntityManagerInterface $entityManager, Person $person): Response
    {
        $entityManager->remove($person);
        $entityManager->flush();

        return $this->redirectToRoute('app_person');
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
