# ColisCar - Plateforme de Covoiturage et Transport de Colis

Plateforme web complète combinant les fonctionnalités de covoiturage (BlaBlaCar) et de transport de colis (Cocolis).

## Fonctionnalités

- **Gestion des utilisateurs** : Inscription, connexion, profils avec rôles (conducteur, expéditeur, utilisateur)
- **Gestion des trajets** : Publication, modification, suppression de trajets avec options (personnes/colis/mixte)
- **Recherche avancée** : Recherche par ville, date, type avec affichage sur carte
- **Réservations** : Réservation de places et envoi de colis avec suivi de statut
- **Messagerie interne** : Communication entre conducteur et utilisateur
- **Notifications email** : Emails automatiques pour les réservations, messages, rappels
- **Tableaux de bord** : Interfaces dédiées pour conducteur, utilisateur et admin
- **APIs externes** : Intégration Google Maps, Stripe, notifications

## Installation

### Prérequis

- PHP >= 8.1
- Composer
- Node.js et npm
- MySQL ou PostgreSQL
- Serveur web (Apache/Nginx) ou PHP built-in server

### Étapes d'installation

1. **Cloner le projet**
```bash
cd ColisCar
```

2. **Installer les dépendances PHP**
```bash
composer install
```

3. **Installer les dépendances JavaScript**
```bash
npm install
```

4. **Configurer l'environnement** (IMPORTANT - À faire avant composer install)
```bash
# Sur Windows (PowerShell)
Copy-Item env.example .env

# Sur Linux/Mac
cp env.example .env
```

5. **Installer les dépendances PHP**
```bash
composer install
```

6. **Générer la clé d'application**
```bash
php artisan key:generate
```

7. **Configurer la base de données dans `.env`**
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=coliscar
DB_USERNAME=root
DB_PASSWORD=votre_mot_de_passe
```

8. **Exécuter les migrations**
```bash
php artisan migrate --seed
```

9. **Créer le lien symbolique pour le stockage**
```bash
php artisan storage:link
```

10. **Compiler les assets**
```bash
npm run dev
# ou pour la production
npm run build
```

11. **Démarrer le serveur**
```bash
php artisan serve
```

Le site sera accessible sur `http://localhost:8000`

## Configuration des APIs

### Google Maps

Ajoutez votre clé API dans `.env` :
```env
GOOGLE_MAPS_API_KEY=votre_cle_api
```

### Stripe

Ajoutez vos clés Stripe dans `.env` :
```env
STRIPE_KEY=pk_test_...
STRIPE_SECRET=sk_test_...
```

### OAuth (Google)

Pour l'authentification Google, configurez dans `.env` :
```env
GOOGLE_CLIENT_ID=votre_client_id
GOOGLE_CLIENT_SECRET=votre_client_secret
```

## Utilisation

### Comptes par défaut

Après l'installation avec `--seed`, vous pouvez vous connecter avec :

- **Admin** : admin@coliscar.com / password
- **Conducteur** : conducteur@coliscar.com / password
- **Utilisateur** : user@coliscar.com / password

### Rôles

- **Admin** : Accès complet à la plateforme
- **Conducteur** : Peut publier des trajets et transporter passagers/colis
- **Expéditeur** : Peut envoyer des colis
- **Utilisateur** : Peut réserver des places et envoyer des colis

## Structure du projet

```
app/
├── Http/
│   ├── Controllers/     # Contrôleurs
│   ├── Middleware/      # Middleware personnalisés
│   └── Requests/        # Form requests
├── Models/              # Modèles Eloquent
├── Services/            # Services (APIs, paiements, etc.)
└── Mail/                # Classes d'email

database/
├── migrations/          # Migrations de base de données
└── seeders/             # Seeders

resources/
├── views/               # Vues Blade
└── js/                  # Assets JavaScript

routes/
└── web.php              # Routes web
```

## Technologies utilisées

- **Backend** : Laravel 10
- **Frontend** : Blade, Alpine.js, Vite
- **Base de données** : MySQL/PostgreSQL
- **APIs** : Google Maps, Stripe
- **Authentification** : Laravel Sanctum

## Documentation supplémentaire

- **[Guide d'installation détaillé](INSTALLATION.md)** : Instructions complètes d'installation et de configuration
- **[Guide d'utilisation](GUIDE_UTILISATION.md)** : Guide complet pour utiliser toutes les fonctionnalités de la plateforme

## Licence

MIT

