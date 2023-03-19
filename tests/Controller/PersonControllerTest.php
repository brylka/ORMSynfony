<?php

namespace App\Tests\Controller;

use App\Entity\Person;
use App\Repository\PersonRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class PersonControllerTest extends WebTestCase
{
    public function testGenerateRandomPersons(): void
    {
        $client = static::createClient();

        // Wyczyść tabelę i dodaj jeden rekord
        $person = new Person();
        $person->setName('John');
        $person->setSurname('Doe');
        $person->setAge(30);
        $person->setEmail('john.doe@example.com');

        $entityManager = self::$kernel->getContainer()->get('doctrine.orm.entity_manager');
        $entityManager->getConnection()->executeStatement('TRUNCATE TABLE person');
        $entityManager->persist($person);
        $entityManager->flush();

        // Sprawdź, czy w bazie danych jest tylko jeden rekord
        $repo = $entityManager->getRepository(Person::class);
        $this->assertEquals(1, count($repo->findAll()));

        // Uruchom trasę
        $client->request('GET', '/generate-random-persons');
        $this->assertResponseIsSuccessful();

        // Sprawdź, czy w bazie danych jest dokładnie 100 rekordów
        $this->assertEquals(100, count($repo->findAll()));

        // Sprawdź, czy oryginalny rekord został usunięty
        $this->assertEmpty($repo->findOneBy(['name' => 'John', 'surname' => 'Doe']));
    }

    public function testIndex(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/person');

        // Pobierz dane z bazy danych
        $personRepository = $this->getPersonRepository();
        $people = $personRepository->findAll();

        // Sprawdź, czy liczba wierszy tabeli jest taka sama jak liczba osób
        $tableRows = $crawler->filter('table tbody tr');
        $this->assertCount(count($people), $tableRows);

        // Sprawdź, czy dane z bazy danych zgadzają się z tymi wyświetlanymi w widoku
        foreach ($people as $index => $person) {
            /** @var Person $person */
            $this->assertStringContainsString($person->getName(), $tableRows->eq($index)->text());
            $this->assertStringContainsString($person->getSurname(), $tableRows->eq($index)->text());
            $this->assertStringContainsString((string)$person->getAge(), $tableRows->eq($index)->text());
            $this->assertStringContainsString($person->getEmail(), $tableRows->eq($index)->text());
        }
    }

    public function testCreate(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/person/create');

        $form = $crawler->filter('form')->form();

        // Wypełnij formularz danymi
        $form['form[name]'] = 'John';
        $form['form[surname]'] = 'Doe';
        $form['form[age]'] = 30;
        $form['form[email]'] = 'john.doe@example.com';

        // Wyślij formularz
        $client->submit($form);

        // Upewnij się, że przekierowanie nastąpiło do ścieżki '/person'
        $this->assertTrue($client->getResponse()->isRedirect('/person'));

        // Śledź przekierowanie i sprawdź, czy nowa osoba została dodana do listy
        $crawler = $client->followRedirect();

        $this->assertSelectorTextContains('table tbody tr:last-child', 'John');
        $this->assertSelectorTextContains('table tbody tr:last-child', 'Doe');
        $this->assertSelectorTextContains('table tbody tr:last-child', '30');
        $this->assertSelectorTextContains('table tbody tr:last-child', 'john.doe@example.com');

        // Upewnij się, że rekord został dodany do bazy danych
        $personRepository = $this->getPersonRepository();
        $person = $personRepository->findOneBy(['email' => 'john.doe@example.com']);

        $this->assertNotNull($person);
        $this->assertSame('John', $person->getName());
        $this->assertSame('Doe', $person->getSurname());
        $this->assertSame(30, $person->getAge());
        $this->assertSame('john.doe@example.com', $person->getEmail());
    }

    public function testEdit(): void
    {
        $client = static::createClient();

        // Stwórz nową osobę
        $person = new Person();
        $person->setName('John');
        $person->setSurname('Doe');
        $person->setAge(30);
        $person->setEmail('john.doe@example.com');

        // Zapisz osobę do bazy danych
        $entityManager = self::$kernel->getContainer()->get('doctrine.orm.entity_manager');
        $entityManager->persist($person);
        $entityManager->flush();

        // Pobierz id stworzonej osoby
        $personId = $person->getId();

        // Przejdź do strony edycji osoby
        $crawler = $client->request('GET', "/person/{$personId}/edit");

        // Wyszukaj formularz po selektorze CSS, na przykład po znaczniku 'form'
        $form = $crawler->filter('form')->form();

        // Zaktualizuj dane osoby
        $form['form[name]'] = 'Jane';
        $form['form[surname]'] = 'Doe';
        $form['form[age]'] = 25;
        $form['form[email]'] = 'jane.doe@example.com';

        // Wyślij formularz
        $client->submit($form);

        // Sprawdź, czy przekierowano do strony głównej
        $this->assertTrue($client->getResponse()->isRedirect('/person'));

        // Przejdź na stronę główną
        $crawler = $client->followRedirect();

        // Sprawdź, czy zmienione dane są widoczne na liście osób
        $this->assertStringContainsString('Jane', $crawler->filter('table')->text());
        $this->assertStringContainsString('Doe', $crawler->filter('table')->text());
        $this->assertStringContainsString('25', $crawler->filter('table')->text());
        $this->assertStringContainsString('jane.doe@example.com', $crawler->filter('table')->text());
    }

    public function testDelete(): void
    {
        $client = static::createClient();

        // Stwórz nową osobę
        $person = new Person();
        $person->setName('John');
        $person->setSurname('Doe');
        $person->setAge(30);
        $person->setEmail('john.doe@example.com');

        // Zapisz osobę do bazy danych
        $entityManager = self::$kernel->getContainer()->get('doctrine.orm.entity_manager');
        $entityManager->persist($person);
        $entityManager->flush();

        // Pobierz id stworzonej osoby
        $personId = $person->getId();

        // Przejdź na stronę główną
        $crawler = $client->request('GET', '/person');

        // Sprawdź, czy osoba znajduje się na liście
        $this->assertStringContainsString('John', $crawler->filter('table')->text());

        // Prześlij żądanie usunięcia osoby
        $client->request('POST', "/person/{$personId}/delete");

        // Sprawdź, czy przekierowano do strony głównej
        $this->assertTrue($client->getResponse()->isRedirect('/person'));

        // Przejdź na stronę główną
        $crawler = $client->followRedirect();

        // Sprawdź, czy link do edycji dla usuniętej osoby nie istnieje
        $editLinks = $crawler->filter("a[href='". $client->getContainer()->get('router')->generate('app_person_edit', ['id' => $personId]) ."']");

        $this->assertEquals(0, $editLinks->count());

        // Jeśli istnieje link do edycji, sprawdź, czy nie zawiera danych usuniętej osoby
        if ($editLinks->count() > 0) {
            $this->assertStringNotContainsString('John', $editLinks->text());
            $this->assertStringNotContainsString('Doe', $editLinks->text());
            $this->assertStringNotContainsString('30', $editLinks->text());
            $this->assertStringNotContainsString('john.doe@example.com', $editLinks->text());
        }
    }

    private function getPersonRepository(): PersonRepository
    {
        // Uruchamia kernel (jądro aplikacji), aby uzyskać dostęp do kontenera zależności
        self::bootKernel();
        // Pobiera kontener zależności i korzysta z Doctrine, aby pobrać repozytorium dla encji Person
        return self::$kernel->getContainer()->get('doctrine')->getRepository(Person::class);
    }
}