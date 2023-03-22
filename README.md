# Aplikacja MVC w Symfony 6.2 z obsługą CRUD

Aplikacja internetowa zbudowana w oparciu o wzorzec MVC, wykorzystująca framework Symfony w wersji 6.2. Aplikacja umożliwia wykonywanie operacji CRUD (Create, Read, Update, Delete) na danych.

## Instalacja

Aby zainstalować aplikację, wykonaj następujące kroki:

1. Sklonuj repozytorium na swoim komputerze.
2. Zainstaluj zależności za pomocą [Composer](https://getcomposer.org/), wykonując polecenie: `composer install`
3. Skonfiguruj połączenie z bazą danych, edytując plik `.env`. Znajdź linijkę zaczynającą się od `DATABASE_URL` i dostosuj ustawienia MySQL do swojego systemu. Przykładowo: `DATABASE_URL="mysql://root@127.0.0.1:3306/ormsymfony?serverVersion=mariadb-10.4.27"`
4. Utwórz bazę danych, wykonując polecenie: `php bin/console doctrine:database:create`
5. Przeprowadź migracje, aby utworzyć strukturę tabel w bazie danych, wykonując polecenie: `php bin/console doctrine:migrations:migrate`
6. Zainstaluj zależności dla frontendu, korzystając z npm, wykonując polecenie: `npm install`
7. Uruchom kompilację assetów, aby przygotować pliki CSS i JavaScript dla aplikacji, wykonując polecenie: `npm run dev`

Teraz aplikacja powinna być gotowa do użycia. Uruchom wbudowany serwer Symfony za pomocą polecenia:

    symfony server:start

Następnie otwórz przeglądarkę i wpisz adres serwera wraz z trasą /person (domyślnie http://127.0.0.1:8000/person) w pasku adresu, aby rozpocząć korzystanie z aplikacji.

Funkcjonalność
Aplikacja umożliwia zarządzanie danymi z wykorzystaniem operacji CRUD:

Tworzenie (Create) - dodawanie nowych obiektów
Odczyt (Read) - wyświetlanie szczegółów obiektów
Aktualizacja (Update) - edycja istniejących obiektów
Usuwanie (Delete) - usuwanie obiektów z bazy danych