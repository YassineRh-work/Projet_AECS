# Guide : Gestion des Documents et Photos

## 📋 Vue d'ensemble
Le système de gestion des documents permet de joindre des fichiers (PDF, photos, documents Word) à chaque objectif coordinateur pour mieux documenter les actions réalisées.

## 📁 Dossier d'upload
- **Localisation** : `/uploads/` (créé automatiquement)
- **Taille max** : 10 MB par fichier
- **Formats acceptés** :
  - PDF (`.pdf`)
  - Images : JPG, PNG, GIF, WEBP
  - Documents : DOC, DOCX

## 🔧 Fichiers créés

### 1. `upload.php` - Endpoint d'upload
- Gère le téléchargement des fichiers
- Valide le type MIME (pas d'extensions dangereuses)
- Génère un nom de fichier unique et sécurisé
- Retourne un JSON avec le statut et le nom du fichier

**Utilisation** :
```javascript
fetch('upload.php', {
    method: 'POST',
    body: formData // formData.append('file', file)
})
```

### 2. `download.php` - Endpoint de téléchargement/affichage
- Sécurise l'accès aux fichiers (validation de session)
- Prévient les path traversal attacks
- Affiche correctement le type MIME
- Permet l'affichage inline pour les PDFs et images

**Utilisation** :
```html
<a href="download.php?file=doc_12345_file.pdf" target="_blank">
    Voir le document
</a>
```

### 3. Modifications en base de données
La colonne `piece_jointe` dans `coord_activites` stocke le nom du fichier uploadé.

**Schéma** :
```sql
ALTER TABLE coord_activites ADD COLUMN piece_jointe varchar(500);
```

## 💻 Modifications du dashboard.php

### Formulaire coordinateur
- Champ input file amélioré avec validation côté client
- Barre de progression du téléchargement
- Prévisualisation du fichier sélectionné
- Gestion des erreurs avec messages clairs

### Tableau d'affichage
- Nouvelle colonne "Documents" 
- Liens cliquables pour voir/télécharger les fichiers
- Icônes différenciées par type de document (PDF, image, document)

## 🔒 Sécurité

### Validation côté client
- Vérification de la taille (max 10MB)
- Vérification du type MIME
- Noms de fichiers valides

### Validation côté serveur (upload.php)
- Vérification de session
- Validation du type MIME avec `finfo_file()`
- Génération de noms de fichiers uniques avec `uniqid()`
- Prévention des injections (path traversal)

### Validation côté serveur (download.php)
- Vérification de session
- Prévention des path traversal
- Vérification de l'existence du fichier

## 🚀 Flux de fonctionnement

### 1. Upload d'un document
```
Utilisateur sélectionne un fichier
    ↓
handleFileUpload() - Validation client
    ↓
Envoi POST vers upload.php
    ↓
upload.php - Validation serveur + sauvegarde
    ↓
currentFileName = nom du fichier
    ↓
Prévisualisation affichée
```

### 2. Sauvegarde en base de données
```
Soumission du formulaire coordForm
    ↓
Données + currentFileName collectées
    ↓
saveDataToDB() 
    ↓
dashboard_api.php reçoit pieceJointe
    ↓
Sauvegarde dans la colonne piece_jointe
```

### 3. Affichage dans le tableau
```
renderCoordActivities()
    ↓
getDocumentLink(filename)
    ↓
Génération du lien download.php?file=...
    ↓
Affichage du lien avec icône appropriée
    ↓
Clic utilisateur → download.php affiche le fichier
```

## 📊 Exemple d'utilisation

### HTML Form
```html
<div class="form-group">
    <label>Pièce jointe (Documents, Photos)</label>
    <input type="file" id="coordPieceJointe" 
           accept=".pdf,.jpg,.jpeg,.png,.gif,.webp,.doc,.docx" 
           onchange="handleFileUpload(event)">
</div>
<div id="filePreview" style="display: none;">
    <span id="fileName"></span>
    <button type="button" onclick="removeFile()">✕</button>
</div>
```

### JavaScript
```javascript
function handleFileUpload(event) {
    const file = event.target.files[0];
    
    // Validation
    if (file.size > 10 * 1024 * 1024) {
        alert('Fichier trop volumineux');
        return;
    }
    
    // Upload
    const formData = new FormData();
    formData.append('file', file);
    
    fetch('upload.php', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.status === 'success') {
            currentFileName = data.filename;
            // Afficher prévisualisation
        }
    });
}
```

## 🐛 Dépannage

### "File too large"
- Vérifier la taille du fichier
- Max 10 MB

### "File type not allowed"
- Vérifier le format
- Extensions acceptées : PDF, JPG, PNG, GIF, WEBP, DOC, DOCX

### Le fichier ne s'affiche pas
- Vérifier que le dossier `/uploads/` existe et est accessible
- Vérifier les permissions (755 ou 775)
- Vérifier que le fichier existe sur le serveur

### Erreur 401 sur download.php
- Session expirée
- Se reconnecter

## 📝 Notes importantes

1. **Gestion des fichiers existants** : Lors de l'édition d'une activité, le fichier précédent ne peut pas être facilement remplacé (il faudrait implémenter une suppression de l'ancien fichier avant upload du nouveau)

2. **Espace disque** : Vérifier régulièrement que le serveur a suffisamment d'espace pour les uploads

3. **Nettoyage** : Les fichiers supprimés via l'interface n'effacent pas le fichier du serveur (à implémenter manuellement)

4. **Intégration base de données** : La colonne `piece_jointe` doit exister dans la table `coord_activites`

## ✅ Checklist d'installation

- [ ] Créer le fichier `/uploads/` avec permissions 755
- [ ] Créer/vérifier la colonne `piece_jointe` en BD
- [ ] Uploader les fichiers PHP (upload.php, download.php)
- [ ] Modifier dashboard.php et dashboard_api.php
- [ ] Mettre à jour dashboard.css
- [ ] Tester l'upload d'un fichier
- [ ] Tester l'affichage dans le tableau
- [ ] Tester le téléchargement d'un fichier
