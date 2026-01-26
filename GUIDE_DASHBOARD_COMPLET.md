# 📚 GUIDE COMPLET DU DASHBOARD AECS 2025-2026

## Table des matières
1. [Gestion des utilisateurs](#gestion-des-utilisateurs)
2. [Architecture PHP](#architecture-php)
3. [Fonctionnalités JavaScript](#fonctionnalités-javascript)
4. [Configuration des pôles](#configuration-des-pôles)

---

## ✅ Gestion des utilisateurs

### ➕ AJOUTER UN UTILISATEUR AVEC PHPMYADMIN

#### 🧭 ÉTAPE 1 — Ouvrir phpMyAdmin
1. Ouvre ton navigateur
2. Va à l'adresse de ton serveur : `http://192.168.1.100/phpMyAdmin/index.php`
3. Connexion :
   - Utilisateur : `root`
   - Mot de passe : `Aecs17Villa`
4. Sélectionne `mariaDB10`

#### 🗄️ ÉTAPE 2 — Sélectionner la base de données
1. Dans la colonne de gauche, sélectionne `aecs_dashboard`
2. Clique sur le nom de ta base de données `users`

#### ➕ ÉTAPE 3 — Cliquer sur "Insérer"
En haut de l'écran, clique sur **Insérer**

#### ✍️ ÉTAPE 4 — Remplir le formulaire
Tu verras une ligne avec plusieurs champs :

| Champ | Action | Exemple |
|-------|--------|---------|
| **id** | ❌ Laisse vide | Le système le remplira automatiquement |
| **username** | ➡️ Nom de connexion | `prenom_nom` |
| **password** | ➡️ Mot de passe | `Aecs17Villa` |
| **role** | ➡️ Rôle de l'utilisateur | `admin`, `coordinateur`, `operationnelle` |
| **pole** | ➡️ Pôle(s) autorisé(s) | Un seul : `Ecologie` |
| | | Plusieurs (séparés par virgule) : `Lien social,Ecologie` |
| **created_at** | ✔️ Laisse tel quel | Se remplit automatiquement |

⚠️ **IMPORTANT** : Respecte l'orthographe exacte des pôles !

#### 💾 ÉTAPE 5 — Enregistrer
1. Descends en bas de la page
2. Clique sur **Exécuter**

---

### 🛠️ CONFIGURATION DES PÔLES

#### AJOUTER LE PÔLE DANS LES FORMULAIRES HTML

Cherche dans ton fichier `dashboard.php` les blocs comme :
```html
<select name="pole">
```

Tu verras quelque chose comme :
```html
<option value="">-- Sélectionner un pôle --</option>
<option value="Lien social">Lien social</option>
<option value="Ecologie">Ecologie</option>
```

**➕ Ajouter un nouveau pôle**

Exemple : Santé
```html
<option value="Santé">Santé</option>
```

📍 **C'est ici que l'utilisateur choisit le pôle**

#### AJOUTER LE PÔLE DANS LES FILTRES

Chaque nouveau pôle doit être ajouté dans les filtres.
Cherche des sections similaires à :
```html
<select id="filter_pole">
```

Ajoute le même `<option>` :
```html
<option value="Santé">Santé</option>
```

⚠️ **Le texte doit être strictement identique partout !**

---

## 🔧 Architecture PHP

### 📁 Vue d'ensemble des fichiers PHP

```
dashboard_api.php      → API de synchronisation avec la base de données
dashboard.php          → Page principale du tableau de bord
login.php              → Authentification utilisateur
logout.php             → Déconnexion
index.php              → Accueil de l'application
upload.php             → Gestion des téléchargements de fichiers
download.php           → Téléchargement de fichiers
delete_file.php        → Suppression de fichiers
```

---

### 📡 **dashboard_api.php** — API REST pour la synchronisation

#### 🎯 Fonction principale
Gère la communication entre l'interface JavaScript (frontend) et la base de données MariaDB (backend).

#### 🔑 Fonctions principales

##### **GET** — Récupérer toutes les données
```php
GET /dashboard_api.php
```

**Ce qu'elle fait :**
- Vérifie que l'utilisateur est connecté (session active)
- Se connecte à la base `aecs_dashboard`
- Récupère toutes les activités de la table `activites`
- Récupère toutes les activités de coordination de la table `coord_activites`
- Retourne les données en JSON

**Réponse :**
```json
{
    "status": "success",
    "activities": [...],
    "coordActivities": [...]
}
```

##### **POST** — Sauvegarder les données
```php
POST /dashboard_api.php
Content-Type: application/json

{
    "activities": [...],
    "coordActivities": [...]
}
```

**Ce qu'elle fait :**
1. Vérifie que l'utilisateur est authentifié
2. Valide que toutes les activités de coordination ont un `typeAtelier`
3. Lance une transaction (tout ou rien)
4. **Vide les deux tables** (`activites` et `coord_activites`)
5. **Ré-insère toutes les données** reçues du frontend
6. Valide les contraintes de clé étrangère
7. Valide la cohérence (dates, heures, durées)
8. Commit la transaction ou rollback en cas d'erreur

**Réponse en cas de succès :**
```json
{
    "status": "success",
    "message": "Données sauvegardées avec succès"
}
```

**Réponse en cas d'erreur :**
```json
{
    "status": "error",
    "message": "Description de l'erreur",
    "error": "Détail technique"
}
```

#### 🔒 Sécurité
- ✅ Vérification de session obligatoire
- ✅ Préparation des requêtes SQL (protection contre les injections)
- ✅ Gestion des transactions (cohérence des données)
- ✅ CORS activé pour les requêtes cross-origin
- ✅ En-tête JSON pour les réponses structurées

---

### 🔐 **login.php** — Authentification utilisateur

#### 🎯 Fonction principale
Authentifier l'utilisateur et créer une session.

#### 🔑 Processus de connexion

1. **Récepción des données** (formulaire POST)
   ```html
   <form method="post">
       <input name="username" required>
       <input name="password" type="password" required>
   </form>
   ```

2. **Vérification dans la base de données**
   - Cherche l'utilisateur dans la table `users` par son `username`
   - Compare le mot de passe envoyé avec celui stocké

3. **Création de la session** (si identifiants corrects)
   ```php
   $_SESSION['user_id']   = 123
   $_SESSION['username']  = 'jean_dupont'
   $_SESSION['prenom']    = 'Jean'
   $_SESSION['nom']       = 'Dupont'
   $_SESSION['role']      = 'coordinateur'
   $_SESSION['pole']      = 'Ecologie'
   ```

4. **Redirection**
   - ✅ Succès → `index.php`
   - ❌ Erreur → Affiche message d'erreur sur la page de login

#### ⚠️ Points importants
- Le format du username est : `prenom_nom` (utilisé pour extraire prénom et nom)
- Les mots de passe sont actuellement en clair (à sécuriser avec password_hash() en production)
- Les erreurs d'authentification sont volontairement vagues pour la sécurité

---

### 🚪 **logout.php** — Déconnexion

#### 🎯 Fonction principale
Terminer la session et rediriger vers la page de connexion.

#### 🔑 Processus
```php
session_start();      // Récupère la session existante
session_destroy();    // Supprime toutes les données de session
header('Location: login.html');  // Redirection vers login
exit;
```

---

### 🏠 **index.php** — Accueil de l'application

#### 🎯 Fonction principale
Affiche la page d'accueil uniquement si l'utilisateur est connecté.

#### 🔑 Contenu
- Navigation vers Dashboard
- Affichage du nom et rôle de l'utilisateur
- Logo AECS
- Bienvenue personnalisée
- Bouton de déconnexion

#### ✅ Sécurité
Vérifie la session au démarrage :
```php
if (!isset($_SESSION['user_id'])) {
    header('Location: login.html');
    exit;
}
```

---

### 📊 **dashboard.php** — Tableau de bord principal

#### 🎯 Fonction principale
Interface complète pour enregistrer et gérer :
- 📝 Les activités opérationnelles (équipe)
- 🎯 Les activités de coordination (coordinateurs)
- 📈 Les statistiques

#### 🔑 Sections principales

**1. Onglet "Équipe Opérationnelle"**
- Formulaire d'ajout d'activité
- Tableau des activités avec filtres
- Édition/suppression d'activités

**2. Onglet "Coordinateur"**
- Formulaire d'ajout d'objectif de coordination
- Tableau des objectifs avec partenaires
- Gestion des pièces jointes

**3. Onglet "Statistiques Équipe"**
- Graphiques en barres (activités par type, responsable, mois)
- Compteurs (total heures, moyenne par activité)
- Filtrage par pôle

**4. Onglet "Statistiques Coordinateur"**
- Graphiques de coordination
- Taux de réussite des objectifs
- Répartition par statut/projet

#### 🔒 Sécurité
- Vérification de session
- Filtrage des données par pôle utilisateur
- Échappement HTML des données affichées

---

### 📤 **upload.php** — Gestion des téléchargements

#### 🎯 Fonction principale
Recevoir et valider les fichiers envoyés par l'utilisateur.

#### 🔑 Processus

1. **Vérification de sécurité**
   - L'utilisateur doit être authentifié
   - Seul POST est accepté
   - Un fichier est obligatoire

2. **Validation du fichier**
   ```php
   - Taille max : 10 MB
   - Types autorisés :
     * PDF
     * Images : JPG, PNG, GIF, WebP
     * Documents : DOC, DOCX
   ```

3. **Sauvegarde**
   - Crée le dossier `uploads/` s'il n'existe pas
   - Génère un nom de fichier unique (timestamp + hash)
   - Stocke le fichier

4. **Réponse JSON**
   ```json
   {
       "status": "success",
       "filename": "1674567890_abc123def.pdf"
   }
   ```

#### 🔒 Sécurité
- ✅ Vérification de MIME type (pas d'extensions faciles à falsifier)
- ✅ Limite de taille (10 MB)
- ✅ Noms de fichier aléatoires (protection contre les collisions)
- ✅ Session requise

---

### 📥 **download.php** — Téléchargement de fichiers

#### 🎯 Fonction principale
Servir les fichiers uploadés de façon sécurisée.

#### 🔑 Processus

1. **Vérification de sécurité**
   - L'utilisateur doit être authentifié
   - Le fichier doit exister dans `uploads/`
   - Protection contre les path traversal (`../`, `\\`)

2. **Détermination du type MIME**
   - PDF → `application/pdf`
   - PNG → `image/png`
   - DOCX → `application/vnd.openxmlformats-officedocument.wordprocessingml.document`

3. **Envoi du fichier**
   ```php
   header('Content-Type: ' . $mimeType);
   header('Content-Length: ' . filesize($filepath));
   header('Content-Disposition: inline; filename="..."');
   readfile($filepath);
   ```

#### 💡 Note
- Les fichiers s'ouvrent en ligne (pas téléchargement forcé)
- Les PDFs et images s'affichent dans le navigateur

---

### 🗑️ **delete_file.php** — Suppression de fichiers

#### 🎯 Fonction principale
Supprimer les fichiers uploadés de façon sécurisée.

#### 🔑 Processus

1. **Réception de la demande** (POST)
   ```php
   POST /delete_file.php
   body: { "filename": "1674567890_abc123def.pdf" }
   ```

2. **Vérification de sécurité**
   - L'utilisateur doit être authentifié
   - Protection contre les path traversal
   - Le fichier doit exister

3. **Suppression**
   ```php
   unlink('uploads/' . $filename);
   ```

4. **Réponse**
   ```json
   {
       "status": "success",
       "message": "File deleted successfully"
   }
   ```

---

## 💻 Fonctionnalités JavaScript

### 📋 Gestion des formulaires et sélecteurs

#### **addPartnerToList()**
Ajoute un partenaire saisi dans le champ `autrePartenaire` à la liste globale `partnersList` pour le projet sélectionné dans `coordProjet`, en évitant les doublons, puis met à jour la liste déroulante des partenaires via `updatePartnersSelect()`.

#### **updatePartnersSelect()**
Met à jour le `<select id="coordPartenaire">` avec les partenaires correspondant au projet choisi, en construisant des options uniques, en gardant la valeur actuelle et en ajoutant une option "Autre (préciser)".

#### **toggleAutrePartenaire()**
Affiche ou cache le champ texte "autre partenaire" (`autrePartenaireGroup`) selon que l'option sélectionnée dans `coordPartenaire` soit "Autre", et rend le champ requis ou non.

#### **toggleAutreTypeCoord()**
Même logique pour le type d'atelier de coordination : montre ou cache le champ texte `autreTypeCoord` selon la valeur "Autre" dans `coordTypeAtelier`.

#### **toggleAutreType()**
Gère l'affichage du champ "autre type d'atelier" pour le formulaire d'activités opérationnelles (`typeAtelier` / `autreType`).

#### **toggleAutreResponsable()**
Gère l'affichage du champ "autre responsable" (`autreResponsableGroup`) selon si `responsable` vaut "Autre".

#### **toggleAutrePublic()**
Gère le champ "autre public" (`autrePublicGroup`) en fonction de la sélection "Autre" dans `coordPublic`.

#### **toggleAutreResponsableCoord()**
Version "coordination" du responsable : montre ou cache le champ `autreResponsableCoord` selon la valeur de `coordResponsable`.

#### **toggleAutreProjet()**
Pour le formulaire de coordination (`coordProjet`), affiche le champ `autreProjet` si le projet sélectionné est "Autre", réinitialise la sélection partenaire et les champs liés, puis rappelle `updatePartnersSelect()`.

#### **toggleAutreProjetOp()**
Pour le formulaire opérationnel (`projet`), affiche ou cache le champ `autreProjetOp` en fonction de la valeur "Autre" et règle l'attribut required.

---

### 📎 Gestion des fichiers joints

#### **handleFileUpload(event)**
Récupère le fichier choisi par l'utilisateur, stocke son nom dans `currentFileName`, l'affiche dans l'élément `fileName` et rend visible le bloc `filePreview`.

#### **removeFile()**
Réinitialise `currentFileName`, efface la valeur du champ `coordPieceJointe` et masque l'aperçu de fichier.

---

### 🔄 Navigation par onglets

#### **switchTab(tab)**
Retire la classe `active` de tous les onglets et sections, l'applique sur l'onglet cliqué et la section correspondant à l'id `tab`, puis met à jour les statistiques si l'onglet "stats" ou "statsCoord" est activé.

---

### ⏱️ Calculs de durée

#### **calculateDuration()**
Lit `heureDebut` et `heureFin`, calcule la différence en minutes, puis formate la durée en texte du type `XhYY` (ou `0h` si les horaires sont invalides) et la met dans `duree`.

#### **calculateCoordDuration()**
Même logique pour les champs `coordHeureDebut` et `coordHeureFin`, met à jour `coordDuree` et appelle ensuite `calculateTotalDuration()` pour recalculer la durée totale.

#### **calculateTotalDuration()**
Parse la durée d'activité (`coordDuree`) et la durée de préparation (`coordDureePrep`) exprimées en `XhYY` ou `XXmin`, additionne le tout en minutes puis formate une durée totale en `XhYY` dans `coordDureeTotale`.

---

### 💾 Soumission des formulaires

#### **Listener activityForm.submit**
1. Empêche l'envoi HTTP standard
2. Résout les valeurs "Autre" pour `typeAtelier`, `responsable`, `projet` en prenant les champs texte associés
3. Construit un objet `activity` avec toutes les infos (projet, pôle, mois, date, période, objectif, type, responsable, lieu, heures, durée, participants, commentaire, statut)
4. Met à jour `activities` (ajout ou modification selon `editingIndex`)
5. Réinitialise le formulaire, masque les blocs "Autre"
6. Appelle `renderActivities()` et `saveDataToDB()`

#### **Listener coordForm.submit**
1. Empêche le submit classique
2. Gère les valeurs "Autre" pour `public`, `responsable`, `projet`, `partenaire`, `typeAtelier`
3. Crée un objet `coordActivity` avec mois, date, période, responsable, projet, partenaire, type, activité, pièce jointe, public, matériel, lieu, durée de préparation, statut, commentaires, pôle
4. Met à jour `coordActivities` (ajout/modif)
5. Ajoute éventuellement un partenaire personnalisé à `partnersList`
6. Cache les blocs "Autre", supprime le fichier
7. Met à jour les filtres partenaires
8. Relance `renderCoordActivities()` et `saveDataToDB()`
9. Réinitialise le formulaire

---

### 📊 Rendu des tableaux d'activités

#### **renderActivities()**
1. Récupère le `<tbody id="activitiesTableBody">`
2. Applique les filtres via `getFilteredActivities()`
3. Si aucun résultat → affiche une ligne "Aucune activité ne correspond aux filtres sélectionnés"
4. Sinon, génère les lignes HTML avec projet, pôle, mois, date, période, objectif+lieu, type, responsable, horaires, durée, statut, et boutons d'édition/suppression `editActivity()` / `deleteActivity()`

#### **getFilteredActivities()**
Lit les filtres (mois, responsable, type, statut, pôle) dans les `<select>` correspondants et retourne uniquement les `activities` qui correspondent à tous les filtres renseignés.

#### **filterActivities()**
Wrapper qui relance simplement `renderActivities()` après changement de filtres.

#### **renderCoordActivities()**
1. Récupère `<tbody id="coordTableBody">`
2. Applique `getFilteredCoordActivities()`
3. Si aucun résultat → ligne "Aucun objectif enregistré."
4. Sinon, affiche chaque objectif avec projet, pôle, mois, partenaire, date, période, type d'atelier, activité (avec éventuelle pièce jointe, commentaires, matériel), responsable, public, durée de préparation, statut et boutons `editCoordActivity()` / `deleteCoordActivity()`

#### **getFilteredCoordActivities()**
Filtre `coordActivities` selon projet, partenaire, mois, responsable et pôle à partir des filtres utilisateur.

#### **filterCoordActivities()**
Met à jour la liste des partenaires disponibles pour le filtre via `updatePartnersFilterSelect()` puis relance `renderCoordActivities()`.

#### **updatePartnersFilterSelect()**
Construit les options du filtre `filterCoordPartenaire` à partir de la liste unique des partenaires présents dans `coordActivities`, en conservant la valeur sélectionnée si possible.

---

### ✏️ Édition / suppression d'éléments

#### **editActivity(index)**
1. Charge l'activité `activities[index]` dans le formulaire
2. Renseigne tous les champs
3. Gère les valeurs standard / "Autre" pour projet, type, responsable en appelant les fonctions `toggleAutre*`
4. Fait défiler la page en haut
5. Définit `editingIndex` pour que la prochaine sauvegarde modifie l'entrée

#### **deleteActivity(index)**
1. Affiche une confirmation
2. Supprime l'élément du tableau `activities`
3. Relance `renderActivities()` et appelle `saveDataToDB()`

#### **editCoordActivity(index)**
1. Même principe pour `coordActivities`
2. Remplit le formulaire coordination avec les valeurs de l'objectif
3. Gère les listes standard pour responsables, projets, partenaires, publics (avec les `toggleAutre*` associés)
4. Réaffiche la pièce jointe éventuelle
5. Remplit matériel, lieu, durée de préparation, statut, commentaires et type d'atelier
6. Remonte en haut de page

#### **deleteCoordActivity(index)**
1. Confirme la suppression d'un objectif
2. L'enlève de `coordActivities`
3. Relance `renderCoordActivities()` et sauvegarde via `saveDataToDB()`

---

### 🔄 Synchronisation avec MariaDB

#### **loadDataFromDB()** (async)
1. Fait un fetch GET sur `API_URL` (dashboard_api.php)
2. Si `status === 'success'`, remplit `activities` et `coordActivities` à partir des colonnes SQL (mapping type_atelier, heure_debut, etc.)
3. Appelle `renderActivities()`, `renderCoordActivities()`, `updatePartnersSelect()`, `updateStats()` et `updateCoordStatistics()`
4. Logge les erreurs éventuelles en console

#### **saveDataToDB()** (async)
1. Envoie via fetch POST un JSON contenant `{ activities, coordActivities }` à l'API
2. Lit la réponse
3. Logge le succès ou l'erreur
4. Met à jour les statistiques (`updateStats()`, `updateCoordStatistics()`) en cas de succès

---

### 📈 Fonctions statistiques

#### **parseDureeToHours(dureeStr)**
Convertit une chaîne de durée (formats comme `2h02`, `1h5`, `50min`, `50 m`, ou un nombre brut interprété en minutes) en nombre d'heures (float).

#### **formatHoursToHM(hoursFloat)**
Transforme un nombre d'heures (float) en texte lisible : `Xh Ymin`, `Xh` ou `Ymin` selon la valeur.

#### **updateStats()**
1. Filtre `activities` par pôle (`filterStatsPole`)
2. Calcule :
   - Nombre total d'activités
   - Total d'heures (via `parseDureeToHours`)
   - Heures par responsable
   - Nombre par type
   - Nombre par mois
   - Nombre de responsables distincts
   - Moyenne d'heures par activité
3. Met à jour les compteurs HTML (`totalActivites`, `responsablesActifs`, `totalHeures`, `moyenneHeures`)
4. Affiche les "bar charts" via `renderBarChart()` pour type, responsable et mois

#### **updateCoordStatistics()**
1. Filtre `coordActivities` par pôle (`filterStatsPoleCoord`)
2. Calcule :
   - Total d'objectifs
   - Heures de préparation totales
   - Heures de préparation par responsable
   - Répartition par statut
   - Répartition par projet
   - Répartition par mois
   - Répartition par partenaire
   - Répartition par public
   - Nombre d'objectifs terminés
   - Taux de réussite (en %)
3. Met à jour les compteurs coordination
4. Appelle `renderCoordBarChart()` pour plusieurs dimensions (statut, projet, responsable, mois, partenaire, public)

---

### 📊 Rendu des bar charts

#### **renderCoordBarChart(containerId, dataObj, unitLabel)**
1. Récupère le conteneur
2. Si pas de données → "Aucune donnée disponible"
3. Calcule la valeur maximale
4. Pour chaque entrée (label, value), crée un bloc barre dont la largeur est proportionnelle à la valeur
5. Formate la valeur en heures avec `formatHoursToHM` si `unitLabel === 'h'`, sinon affiche la valeur brute

#### **renderBarChart(containerId, dataObj, unitLabel)**
Même principe que `renderCoordBarChart` mais pour les statistiques générales des activités.

---

### 🎨 Affichage / masquage des formulaires

#### **toggleActivityForm()**
Affiche ou masque le formulaire d'activités (`activityForm`) et change le texte du bouton `toggleActivityBtn` entre "Afficher" et "Masquer".

#### **toggleCoordForm()**
Idem pour le formulaire de coordination (`coordForm`) avec le bouton `toggleCoordFormBtn`.

---

## 📝 Structure de la base de données

### Table `users`
```sql
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(50),
    pole VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### Table `activites`
```sql
CREATE TABLE activites (
    id INT AUTO_INCREMENT PRIMARY KEY,
    projet VARCHAR(255),
    pole VARCHAR(255),
    mois VARCHAR(50),
    date DATE,
    periode VARCHAR(50),
    objectif TEXT,
    type_atelier VARCHAR(255),
    responsable VARCHAR(255),
    lieu VARCHAR(255),
    heure_debut TIME,
    heure_fin TIME,
    duree VARCHAR(50),
    participants INT,
    commentaire TEXT,
    statut VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### Table `coord_activites`
```sql
CREATE TABLE coord_activites (
    id INT AUTO_INCREMENT PRIMARY KEY,
    mois VARCHAR(50),
    date DATE,
    periode VARCHAR(50),
    responsable VARCHAR(255),
    projet VARCHAR(255),
    partenaire VARCHAR(255),
    type_atelier VARCHAR(255),
    activite TEXT,
    piece_jointe VARCHAR(255),
    public VARCHAR(255),
    materiel TEXT,
    lieu VARCHAR(255),
    duree_prep VARCHAR(50),
    statut VARCHAR(50),
    commentaires TEXT,
    pole VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

---

## 🚀 Flux de travail complet

### 📝 Ajouter une activité (Équipe Opérationnelle)

1. L'utilisateur remplissage le formulaire dans le tab "Équipe Opérationnelle"
2. À la soumission, JavaScript capture les données dans un objet `activity`
3. L'objet est ajouté au tableau `activities` en mémoire
4. `renderActivities()` redessine le tableau HTML
5. `saveDataToDB()` envoie tous les `activities` au serveur en POST
6. `dashboard_api.php` reçoit les données, les valide et les sauvegarde en BDD
7. À chaque rechargement, `loadDataFromDB()` récupère les données depuis la BDD

### 🎯 Ajouter un objectif de coordination

1. L'utilisateur remplit le formulaire dans le tab "Coordinateur"
2. Les fichiers peuvent être attachés via `upload.php`
3. À la soumission, JavaScript crée un objet `coordActivity`
4. L'objet est ajouté au tableau `coordActivities` en mémoire
5. `renderCoordActivities()` redessine le tableau HTML
6. `saveDataToDB()` synchronise avec la BDD via `dashboard_api.php`
7. Les fichiers sont stockés dans le dossier `uploads/`

### 📊 Consulter les statistiques

1. L'utilisateur clique sur l'onglet "Statistiques Équipe" ou "Statistiques Coordinateur"
2. `switchTab()` appelle `updateStats()` ou `updateCoordStatistics()`
3. Les fonctions filtrent les données par pôle et calculent les totaux
4. `renderBarChart()` ou `renderCoordBarChart()` dessine les graphiques
5. Les compteurs sont mis à jour (total heures, nombre d'activités, etc.)

---

## 🔒 Sécurité

### ✅ Authentification et autorisation
- Vérification de session à chaque accès
- Extraction du pôle utilisateur pour filtrer les données
- Rôles : `admin`, `coordinateur`, `operationnelle`

### ✅ Protection contre les injections
- Requêtes SQL préparées (PDO avec paramètres)
- Validation des types de données
- Échappement HTML (`htmlspecialchars()`)

### ✅ Gestion des fichiers
- Validation de MIME type (pas juste l'extension)
- Limite de taille (10 MB)
- Noms de fichier aléatoires
- Protection contre les path traversal (`../`, `\\`)

### ✅ CORS et headers
- CORS configuré pour les requêtes cross-origin
- Content-Type spécifié pour les réponses JSON
- Content-Disposition pour les téléchargements

---

## 🐛 Troubleshooting

### Le formulaire ne sauvegarde pas
1. Vérifie que tu es connecté
2. Ouvre la console navigateur (F12) pour voir les erreurs
3. Vérifie que `dashboard_api.php` répond en GET
4. Vérifie les droits d'accès à la BDD

### Les fichiers uploadés ne s'affichent pas
1. Vérifie que le dossier `uploads/` existe et a les droits d'écriture
2. Vérifie la limite de taille dans `upload.php` (10 MB)
3. Vérifie le type MIME dans `download.php`

### Les pôles ne s'affichent pas dans les filtres
1. Ajoute l'option `<select id="filter_pole">`
2. Assurez-vous que le texte du pôle est strictement identique

### Les statistiques ne se mettent pas à jour
1. Vérifie que les données sont sauvegardées en BDD
2. Clique sur l'onglet Statistics pour forcer l'actualisation
3. Vérifie la console navigateur pour les erreurs

---

## 📞 Support

Pour toute question :
- Vérifie d'abord ce guide
- Consulte la console navigateur (F12 → Console)
- Vérifie les logs de BDD (phpMyAdmin)
- Contacte l'administrateur AECS

---

**Dernière mise à jour :** 26 janvier 2026
**Version :** 1.0
**Auteur :** GitHub Copilot
