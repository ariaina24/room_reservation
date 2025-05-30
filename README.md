# RoomReservation

## Description

**RoomReservation** est une application web développée avec Symfony (backend) et Twig (frontend) permettant aux utilisateurs de consulter, réserver et gérer des salles.  
Le projet inclut la gestion des utilisateurs, un système de réservation avec différents statuts (Confirmée, En attente, Refusée, Annulée), et un dashboard d'administration pour la gestion des salles.

---

## Fonctionnalités principales

- Inscription / Authentification des utilisateurs.
- Liste des salles avec image, capacité, localisation et description.
- Réservation de salles avec gestion des plages horaires.
- Consultation des réservations avec filtre sur les statuts.
- Annulation des réservations par l'utilisateur.
- Dashboard administrateur pour ajouter, modifier, supprimer des salles.
- Statuts de réservation avec codes couleurs et distinctions visuelles.
- Gestion sécurisée avec tokens CSRF pour les formulaires sensibles.

---

## Technologies utilisées

- **Backend :** Symfony 6.x
- **Frontend :** Twig + Tailwind CSS
- **Base de données :** MySQL
- **Gestion des dépendances :** Composer
- **Version PHP :** >= 8.1

---

## Prérequis

Avant de lancer le projet, assurez-vous d’avoir :

- PHP >= 8.1 installé
- Composer installé ([https://getcomposer.org/](https://getcomposer.org/))
- Une base de données MySQL ou compatible configurée
- Symfony CLI (optionnel, mais recommandé) : [https://symfony.com/download](https://symfony.com/download)
- Node.js et npm si vous souhaitez recompiler les assets Tailwind (optionnel)

---

## Installation

1. **Cloner le dépôt**

```bash
git clone git@github.com:ariaina24/room_reservation.git
cd room_reservation
````

2. **Installer les dépendances PHP**

```bash
composer install
```

3. **Configurer la base de données**

- Copier `.env` en `.env.local` et modifier la variable `DATABASE_URL` :

```env
DATABASE_URL="mysql://db_user:db_password@127.0.0.1:3306/db_name?serverVersion=8.0"
```

4. **Créer la base de données et exécuter les migrations**

```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

5. **Charger des données de test pour l'admin**

Si tu as un fichier de fixtures :

```bash
php bin/console doctrine:fixtures:load
```

6. **Compiler les assets (optionnel)**

Si tu modifies le CSS avec Tailwind et que tu souhaites recompiler :

```bash
npm install
npm run watch
```

7. **Lancer le serveur Symfony**

```bash
symfony server:start
```

Le site sera accessible sur [http://localhost:8000](http://localhost:8000)

---

## Utilisation

- Crée un compte utilisateur via la page d’inscription.
- Connecte-toi et réserve des salles via le menu.
- Consulte tes réservations à venir dans la page "Mes Réservations".
- En tant qu'administrateur (rôle ROLE\_ADMIN), accède au dashboard pour gérer les salles (ajout, modification, suppression).
- POur acceder a l'admin: adresse email: **<admin@gmail.com>**, mot de passe: **<admin>**

---

## Points importants

- Les formulaires sensibles sont protégés par des tokens CSRF.
- Les réservations ont des statuts :

  - **Confirmée** (acceptée)
  - **En attente** (à valider par l’administrateur)
  - **Refusée** (annulée par l’administrateur)
- Les utilisateurs ne peuvent annuler que leurs propres réservations en statut Confirmée ou En attente.
- Le système affiche des badges et couleurs différentes selon le statut.
- La suppression des salles est confirmée via un popup JS pour éviter toute suppression accidentelle.

---

## Tests

- Aucun test automatique n’est inclus actuellement.
- Tests manuels recommandés :

  - Créer un utilisateur, réserver une salle, vérifier la liste des réservations.
  - Annuler une réservation et vérifier la mise à jour de l’interface.
  - En mode administrateur, ajouter/modifier/supprimer une salle et vérifier le bon fonctionnement.
  - Vérifier la protection CSRF sur les actions sensibles.

---

## Structure du projet

```
├── config/             # Configuration Symfony
├── public/             # Assets publics et point d'entrée web
├── src/
│   ├── Controller/     # Contrôleurs Symfony
│   ├── Entity/         # Entités Doctrine
│   ├── Repository/     # Repositories
│   ├── Security/       # Gestion sécurité et authentification
│   └── ...
├── templates/          # Vues Twig
├── migrations/         # Migrations Doctrine
├── translations/       # Traductions
├── var/
├── vendor/
└── ...
```

---

## Remarques

- Ce projet a été développé pour un usage pédagogique et peut être amélioré avec des tests automatisés, une API REST, et une intégration frontend plus avancée (React/Vue).
- Les mots de passe sont stockés de façon sécurisée avec bcrypt via Symfony Security.
- Les emails de notification ne sont pas implémentés, mais peuvent être ajoutés facilement via Symfony Mailer.

---

## Contact

Pour toute question ou problème, vous pouvez me contacter à :
**Ton Nom** - [njivaariaina47@gmail.com](mailto:njivaariaina47@gmail.com)

---

Merci d’avoir pris le temps d’évaluer ce projet !

```

---
