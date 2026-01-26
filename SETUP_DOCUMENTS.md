# 📄 Système de Gestion des Documents - Installation et Utilisation

## 🎯 Objectif
Permettre l'ajout et la gestion de documents (PDF, photos, documents Word) aux objectifs coordinateurs avec stockage sécurisé sur le serveur.

## 📦 Fichiers créés/modifiés

### Nouveaux fichiers :
1. **upload.php** - Endpoint pour uploader les fichiers
2. **download.php** - Endpoint pour télécharger/afficher les fichiers
3. **delete_file.php** - Endpoint pour supprimer les fichiers
4. **setup_uploads.php** - Script d'initialisation
5. **DOCUMENT_MANAGEMENT_GUIDE.md** - Guide détaillé

### Fichiers modifiés :
1. **dashboard.php** - Formulaire et tableau améliorés
2. **dashboard_api.php** - Sauvegarde du champ piece_jointe
3. **dashboard.css** - Styles pour les documents

### Dossier créé :
- **uploads/** - Stockage des fichiers uploadés

## 🚀 Installation rapide

### Étape 1 : Initialiser le dossier uploads
```bash
php setup_uploads.php
```
Cela va :
- Créer le dossier `/uploads/`
- Ajouter des protections (.htaccess, index.php)
- Vérifier la configuration

### Étape 2 : Créer la colonne en base de données
Si elle n'existe pas déjà :
```sql
ALTER TABLE coord_activites ADD COLUMN piece_jointe varchar(500);
```

### Étape 3 : Tester le système
1. Aller sur Dashboard → Vue Coordinateur
2. Remplir un formulaire
3. Sélectionner un fichier (PDF, JPG, PNG, DOCX, etc.)
4. Soumettre le formulaire
5. Vérifier que le document apparaît dans le tableau

## 💡 Utilisation

### Pour l'utilisateur
1. **Ajouter un document** :
   - Cliquer sur "Pièce jointe (Documents, Photos)"
   - Sélectionner un fichier (max 10MB)
   - Voir la progression et la confirmation
   - Soumettre le formulaire

2. **Consulter un document** :
   - Dans le tableau coordinateur, colonne "Documents"
   - Cliquer sur le lien "Voir" pour afficher le fichier
   - Les fichiers s'ouvrent dans un nouvel onglet

3. **Remplacer un document** :
   - Éditer l'activité
   - Sélectionner un nouveau fichier
   - Le nouveau fichier remplace l'ancien

## 🔒 Sécurité implémentée

✅ **Validation côté client**
- Vérification de la taille (max 10MB)
- Vérification du type MIME
- Message d'erreur clair

✅ **Validation côté serveur**
- Vérification de session (login required)
- Validation du type MIME avec finfo_file()
- Noms de fichiers uniques (uniqid)
- Prévention des path traversal attacks

✅ **Protection du dossier uploads**
- .htaccess : bloque l'exécution PHP
- index.php : prévient le listing des répertoires
- Permissions strictes (755/775)

## 📋 Formats acceptés

| Format | Icône | Type MIME |
|--------|-------|-----------|
| PDF | 📄 | application/pdf |
| JPG/JPEG | 🖼️ | image/jpeg |
| PNG | 🖼️ | image/png |
| GIF | 🖼️ | image/gif |
| WEBP | 🖼️ | image/webp |
| DOC | 📝 | application/msword |
| DOCX | 📝 | application/vnd.openxmlformats-officedocument.wordprocessingml.document |

## 🔧 API Endpoints

### POST /upload.php
Uploads un fichier
```javascript
const formData = new FormData();
formData.append('file', file);

fetch('upload.php', {
    method: 'POST',
    body: formData
}).then(r => r.json());

// Response:
// { status: 'success', filename: 'doc_123_file.pdf', originalName: 'file.pdf' }
```

### GET /download.php?file=FILENAME
Télécharge/affiche un fichier
```html
<a href="download.php?file=doc_123_file.pdf">Télécharger</a>
```

### POST /delete_file.php
Supprime un fichier
```javascript
const formData = new FormData();
formData.append('filename', 'doc_123_file.pdf');

fetch('delete_file.php', {
    method: 'POST',
    body: formData
}).then(r => r.json());
```

## 📊 Structure de la base de données

```sql
CREATE TABLE coord_activites (
    -- ... colonnes existantes ...
    piece_jointe varchar(500) NULL COMMENT 'Nom du fichier jointà',
    -- ... colonnes suivantes ...
);
```

Exemples de valeurs :
- `doc_1_63f4a8c9e2b45.pdf`
- `doc_2_63f4a8c9e2c67.jpg`
- `doc_3_63f4a8d0e3f78.docx`

## 🐛 Dépannage

### Erreur "File too large"
- Vérifier la taille du fichier (max 10MB)
- Compresser l'image si nécessaire

### Erreur "File type not allowed"
- Format non accepté
- Formats acceptés : PDF, JPG, PNG, GIF, WEBP, DOC, DOCX
- Convertir le fichier au bon format

### Le fichier ne s'ouvre pas
- Vérifier que le dossier `uploads/` existe
- Vérifier les permissions : `chmod 755 uploads/`
- Vérifier que le fichier existe sur le serveur

### Le fichier n'apparaît pas dans le tableau
- Vérifier que la colonne `piece_jointe` existe en BD
- Vérifier la sauvegardeussia on des données avec `saveDataToDB()`
- Vérifier les logs PHP

### Erreur 401 sur téléchargement
- Session expirée
- Se reconnecter à l'application

## 📈 Maintenance

### Nettoyer les fichiers orphelins
```bash
# Trouver les fichiers qui n'existent plus en BD
php cleanup_orphaned_files.php
```

### Vérifier l'espace disque
```bash
du -sh uploads/
```

### Sauvegarder les fichiers
```bash
tar -czf uploads_backup.tar.gz uploads/
```

## 🎓 Exemple complet

### HTML
```html
<form id="coordForm">
    <!-- Autres champs -->
    
    <div class="form-group">
        <label>Pièce jointe</label>
        <input type="file" id="coordPieceJointe" 
               accept=".pdf,.jpg,.jpeg,.png,.gif,.webp,.doc,.docx"
               onchange="handleFileUpload(event)">
    </div>
    
    <div id="filePreview" style="display: none;">
        <strong>📎 Fichier :</strong> <span id="fileName"></span>
        <button type="button" onclick="removeFile()">✕</button>
    </div>
    
    <button type="submit">✅ Ajouter objectif</button>
</form>
```

### JavaScript
```javascript
let currentFileName = '';

function handleFileUpload(event) {
    const file = event.target.files[0];
    if (!file) return;
    
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
            document.getElementById('fileName').textContent = data.originalName;
            document.getElementById('filePreview').style.display = 'block';
        }
    });
}

function removeFile() {
    currentFileName = '';
    document.getElementById('coordPieceJointe').value = '';
    document.getElementById('filePreview').style.display = 'none';
}
```

### PHP (sauvegarde)
```php
$coordActivity = [
    // ... autres données ...
    'pieceJointe' => $currentFileName ?? null,
];

// Sauvegarde en BD
$pdo->prepare("
    INSERT INTO coord_activites (..., piece_jointe)
    VALUES (..., :piece_jointe)
")->execute([
    // ... autres params ...
    ':piece_jointe' => $coordActivity['pieceJointe'],
]);
```

## ✅ Checklist finale

- [ ] Script `setup_uploads.php` exécuté
- [ ] Dossier `uploads/` créé avec permissions 755
- [ ] Colonne `piece_jointe` créée en BD
- [ ] Tous les fichiers PHP copiés
- [ ] Fichiers CSS/JS mis à jour
- [ ] Test d'upload d'un fichier ✓
- [ ] Test d'affichage dans le tableau ✓
- [ ] Test de téléchargement ✓
- [ ] Test avec différents formats ✓

## 📞 Support

Pour toute question ou problème, consulter :
- [DOCUMENT_MANAGEMENT_GUIDE.md](./DOCUMENT_MANAGEMENT_GUIDE.md) - Guide technique détaillé
- Vérifier les logs PHP : `/var/log/php-fpm/error.log`
- Vérifier les logs Apache/Nginx : `/var/log/apache2/error.log` ou `/var/log/nginx/error.log`

---

**Version** : 1.0  
**Dernière mise à jour** : 2026-01-19  
**Auteur** : Système AECS Dashboard
