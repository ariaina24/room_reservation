# RoomReservation

## Description

RoomReservation est une application Symfony + Twig permettant aux utilisateurs de consulter, réserver et gérer des salles avec un dashboard d’administration.

## Fonctionnalités principales

- Inscription / connexion utilisateur
- Liste des salles avec détails
- Réservation avec gestion des statuts (Confirmée, En attente, Refusée, Annulée)
- Consultation et annulation des réservations
- Dashboard admin pour gérer les salles (CRUD)
- Protection CSRF sur les formulaires sensibles

## Technologies

- Symfony 6.x (PHP >= 8.1)
- Twig, Tailwind CSS
- MySQL
- Composer

## Prérequis

- PHP >= 8.1, Composer
- Base de données MySQL
- Symfony CLI (optionnel)
- Node.js + npm (optionnel, pour compiler Tailwind)

## Installation rapide

```bash
git clone git@github.com:ariaina24/room_reservation.git
cd room_reservation
composer install
# Configurer DATABASE_URL dans .env.local
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
php bin/console doctrine:fixtures:load   # optionnel pour données admin
npm install && npm run watch             # optionnel pour CSS
symfony server:start
````

Accéder à [http://localhost:8000](http://localhost:8000)

## Utilisation

- Créer un compte, se connecter, réserver des salles.
- Consulter et annuler ses réservations.
- Admin : gestion des salles (login admin : **[admin@gmail.com](mailto:admin@gmail.com)** / mdp : **admin**).

## Points importants

- Statuts des réservations avec codes couleurs.
- Annulation possible seulement pour ses propres réservations Confirmée ou En attente.
- Suppression des salles sécurisée par confirmation JS.
- Formulaires protégés contre CSRF.

## Tests manuels recommandés

- Création, réservation, annulation utilisateur.
- Gestion des salles en admin.
- Vérification des protections CSRF.

## Contact

ANDRIAMBOAVONJY Njiva Ariaina — [njivaariaina47@gmail.com](mailto:njivaariaina47@gmail.com)

---
