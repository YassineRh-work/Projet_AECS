<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.html');
    exit;
}
$userRole = $_SESSION['role'] ?? 'user';
// Parsing virgules → tableau
$userPoleString = $_SESSION['pole'] ?? '';
$userPoles = !empty($userPoleString) ? array_map('trim', explode(',', $userPoleString)) : [];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard AECS 2025-2026</title>
    <link rel="stylesheet" href="dashboard.css">
    <link rel="stylesheet" href="index.css">
</head>
<body>
        <header>
            <div class="logo">
                <div class="user-tooltip"><?php echo htmlspecialchars($_SESSION['prenom'] . ' ' . $_SESSION['nom'] . ' (' . $_SESSION['role'] . ')', ENT_QUOTES, 'UTF-8'); ?></div>
            </div>
            <div class="header-right">
                <nav>
                    <ul>
                        <li><a href="index.php">Accueil</a></li>
                        <li><a href="dashboard.php">Dashboard</a></li>
                    </ul>
                </nav>
                <div class="user-info">
                    👤🌐
                    <div class="user-tooltip"><?php echo htmlspecialchars($_SESSION['prenom'] . ' ' . $_SESSION['nom'] . ' (' . $_SESSION['role'] . ')', ENT_QUOTES, 'UTF-8'); ?></div>
                </div>
                <button onclick="window.location.href='logout.php'" class="btn-logout">⏻ Déconnexion</button>
            </div>
        </header>
        
        <div class="tabs">
            <button class="tab active" onclick="switchTab('coordinateur')">🎯 Coordinateur</button>
            <button class="tab" onclick="switchTab('operationnelle')">👥 Équipe Opérationnelle</button>
            <button class="tab" onclick="switchTab('stats')">📈 Statistiques Équipe</button>
            <button class="tab" onclick="switchTab('statsCoord')">📊 Statistiques Coordinateur</button>
        </div>

        <!-- Section Équipe Opérationnelle -->
        <div id="operationnelle" class="dashboard-section">
            <div class="form-section">
                <div class="section-header">
                    <h2>➕ Ajouter une activité</h2>
                    <button class="btn" id="toggleActivityBtn" onclick="toggleActivityForm()">Afficher</button>
                </div>
                <form id="activityForm" style="display: none;" novalidate>
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Projet</label>
                            <select id="projet" onchange="toggleAutreProjetOp()">
                                <option value="">Sélectionner...</option>
                                <option value="La Villette">La Villette</option>
                                <option value="Orange Center">Orange Center</option>
                                <option value="CLAS">CLAS</option>
                                <option value="Science PO">Science PO</option>
                                <option value="Autre">Autre (préciser)</option>
                            </select>
                        </div>
                        <div class="form-group" id="autreProjetOpGroup" style="display: none;">
                            <label>Préciser le projet</label>
                            <input type="text" id="autreProjetOp" name="autreProjetOp" placeholder="Entrez le nom du projet">
                        </div>
                        <div class="form-group">
                            <label>Mois</label>
                            <select id="mois" required>
                                <option value="">Sélectionner...</option>
                                <option value="Janvier">Janvier</option>
                                <option value="Février">Février</option>
                                <option value="Mars">Mars</option>
                                <option value="Avril">Avril</option>
                                <option value="Mai">Mai</option>
                                <option value="Juin">Juin</option>
                                <option value="Juillet">Juillet</option>
                                <option value="Août">Août</option>
                                <option value="Septembre">Septembre</option>
                                <option value="Octobre">Octobre</option>
                                <option value="Novembre">Novembre</option>
                                <option value="Décembre">Décembre</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Date</label>
                            <input type="date" id="date" required>
                        </div>
                        <div class="form-group">
                            <label>Période</label>
                            <select id="periode" required>
                                <option value="">Sélectionner...</option>
                                <option value="Matin">Matin</option>
                                <option value="Après-midi">Après-midi</option>
                                <option value="Soirée">Soirée</option>
                                <option value="Journée">Journée complète</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Objectif</label>
                            <input type="text" id="objectif" placeholder="Description de l'activité" required>
                        </div>
                        <div class="form-group">
                            <label>Type d'atelier</label>
                            <select id="typeAtelier" required onchange="toggleAutreType()">
                                <option value="">Sélectionner...</option>
                                <option value="Atelier">Atelier</option>
                                <option value="Réunion">Réunion</option>
                                <option value="Formation">Formation</option>
                                <option value="Entretien individuel">Entretien individuel</option>
                                <option value="Autre">Autre (préciser)</option>
                            </select>
                        </div>
                        <div class="form-group" id="autreTypeGroup" style="display: none;">
                            <label>Préciser le type</label>
                            <input type="text" id="autreType" name="autreType" placeholder="Entrez le type d'atelier">
                        </div>
                        <div class="form-group">
                            <label>Responsable</label>
                            <select id="responsable" required onchange="toggleAutreResponsable()">
                                <option value="">Sélectionner...</option>
                                <option value="Amira">Amira</option>
                                <option value="Karim">Karim</option>
                                <option value="Denis">Denis</option>
                                <option value="Autre">Autre (préciser)</option>
                            </select>
                        </div>
                        <div class="form-group" id="autreResponsableGroup" style="display: none;">
                            <label>Préciser le responsable</label>
                            <input type="text" id="autreResponsable" name="autreResponsable" placeholder="Entrez le nom du responsable">
                        </div>
                        <div class="form-group">
                            <label>Lieu</label>
                            <input type="text" id="lieu" placeholder="Salle, bureau..." required>
                        </div>
                        <div class="form-group">
                            <label>Heure début</label>
                            <input type="time" id="heureDebut" required onchange="calculateDuration()">
                        </div>
                        <div class="form-group">
                            <label>Heure fin</label>
                            <input type="time" id="heureFin" required onchange="calculateDuration()">
                        </div>
                        <div class="form-group">
                            <label>Durée (auto)</label>
                            <input type="text" id="duree" readonly style="background: #f0f0f0; font-weight: bold; color: #2e7d32;">
                        </div>
                        <div class="form-group">
                            <label>Participants</label>
                            <input type="text" id="participants" placeholder="Noms des participants">
                        </div>
                        <div class="form-group">
                            <label>Pôles</label>
                            <select id="pole" required>
                                <option value="">Sélectionner...</option>
                                <?php if ($userRole === 'admin'): ?>
                                <!-- Admin : tous les pôles -->
                                <option value="Animation de l'espace public">Animation de l'espace public</option>
                                <option value="Animation parentalité">Animation parentalité</option>
                                <option value="Accompagnement à la jeunesse">Accompagnement à la jeunesse</option>
                                <option value="Ecologie">Ecologie</option>
                                <option value="Précarité alimentaire">Précarité alimentaire</option>
                                <option value="Lien social">Lien social</option>
                                <option value="Inclusion numérique">Inclusion numérique</option>
                                <option value="Voix citoyennes">Voix citoyennes</option>
                                <option value="Insertion professionnelle">Insertion professionnelle</option>
                                <option value="Numérique">Numérique</option>
                                <?php else: ?>
                                <!-- Non-admin : UNIQUEMENT son pôle, pré-sélectionné -->
                                <?php foreach ($userPoles as $pole): ?>
                                    <option value="<?php echo htmlspecialchars($pole); ?>">
                                    <?php echo htmlspecialchars($pole); ?>
                                    </option>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Commentaire</label>
                        <textarea id="commentaire" placeholder="Notes supplémentaires..."></textarea>
                    </div>
                    <button type="submit" class="btn">✅ Ajouter l'activité</button>
                </form>
            </div>

            <div class="activities-table">
                <div class="table-header">
                    <h2>📅 Planning des activités</h2>
                    <div class="filters">
                        <select id="filterMois" onchange="filterActivities()">
                            <option value="">Tous les mois</option>
                            <option value="Janvier">Janvier</option>
                            <option value="Février">Février</option>
                            <option value="Mars">Mars</option>
                            <option value="Avril">Avril</option>
                            <option value="Mai">Mai</option>
                            <option value="Juin">Juin</option>
                            <option value="Juillet">Juillet</option>
                            <option value="Août">Août</option>
                            <option value="Septembre">Septembre</option>
                            <option value="Octobre">Octobre</option>
                            <option value="Novembre">Novembre</option>
                            <option value="Décembre">Décembre</option>
                        </select>
                        <select id="filterPole" onchange="filterActivities()">
                            <option value="">Tous les pôles</option>
                            <option value="Animation de l'espace public">Animation de l'espace public</option>
                            <option value="Animation parentalité">Animation parentalité</option>
                            <option value="Accompagnement à la jeunesse">Accompagnement à la jeunesse</option>
                            <option value="Ecologie">Ecologie</option>
                            <option value="Précarité alimentaire">Précarité alimentaire</option>
                            <option value="Lien social">Lien social</option>
                            <option value="Inclusion numérique">Inclusion numérique</option>
                            <option value="Voix citoyennes">Voix citoyennes</option>
                            <option value="Insertion professionnelle">Insertion professionnelle</option>
                            <option value="Numérique">Numérique</option>
                        </select>
                        <select id="filterResponsable" onchange="filterActivities()">
                            <option value="">Tous les responsables</option>
                            <option value="Amira">Amira</option>
                            <option value="Karim">Karim</option>
                            <option value="Denis">Denis</option>
                        </select>
                        <select id="filterType" onchange="filterActivities()">
                            <option value="">Tous les types</option>
                            <option value="Atelier">Atelier</option>
                            <option value="Réunion">Réunion</option>
                            <option value="Formation">Formation</option>
                            <option value="Entretien individuel">Entretien individuel</option>
                        </select>
                        <select id="filterStatut" onchange="filterActivities()">
                            <option value="">Tous les statuts</option>
                            <option value="Prévu">Prévu</option>
                            <option value="En cours">En cours</option>
                            <option value="Terminé">Terminé</option>
                            <option value="Annulé">Annulé</option>
                        </select>
                    </div>
                </div>
                <div style="overflow-x: auto;">
                    <table>
                        <thead>
                            <tr>
                                <th>Projet</th>
                                <th>Pôle</th>
                                <th>Mois</th>
                                <th>Date</th>
                                <th>Période</th>
                                <th>Objectif</th>
                                <th>Type</th>
                                <th>Responsable</th>
                                <th>Horaires</th>
                                <th>Durée</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="activitiesTableBody">
                            <tr>
                                <td colspan="11" class="empty-state">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                    <p>Aucune activité planifiée. Commencez par ajouter une activité ci-dessus.</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Section Coordinateur -->
        <div id="coordinateur" class="dashboard-section active">
            <div class="form-section">
                <div class="section-header">
                    <h2>🎯 Vue Coordinateur - Gestion stratégique</h2>
                    <button class="btn" id="toggleCoordFormBtn" onclick="toggleCoordForm()">Afficher</button>
                </div>
                <form id="coordForm" style="display: none;" novalidate>
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Pôles</label>
                            <select id="pole_coord" required>
                                <option value="">Sélectionner...</option>
                                <?php if ($userRole === 'admin'): ?>
                                <!-- Admin : tous les pôles -->
                                <option value="Animation de l'espace public">Animation de l'espace public</option>
                                <option value="Animation parentalité">Animation parentalité</option>
                                <option value="Accompagnement à la jeunesse">Accompagnement à la jeunesse</option>
                                <option value="Ecologie">Ecologie</option>
                                <option value="Précarité alimentaire">Précarité alimentaire</option>
                                <option value="Lien social">Lien social</option>
                                <option value="Inclusion numérique">Inclusion numérique</option>
                                <option value="Voix citoyennes">Voix citoyennes</option>
                                <option value="Insertion professionnelle">Insertion professionnelle</option>
                                <option value="Numérique">Numérique</option>
                                <?php else: ?>
                                <!-- Non-admin : UNIQUEMENT son pôle, pré-sélectionné -->
                                <?php foreach ($userPoles as $pole): ?>
                                    <option value="<?php echo htmlspecialchars($pole); ?>">
                                    <?php echo htmlspecialchars($pole); ?>
                                    </option>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Projet</label>
                            <select id="coordProjet" onchange="toggleAutreProjet()">
                                <option value="">Sélectionner...</option>
                                <option value="La Villette">La Villette</option>
                                <option value="Orange Center">Orange Center</option>
                                <option value="CLAS">CLAS</option>
                                <option value="Science PO">Science PO</option>
                                <option value="Autre">Autre (préciser)</option>
                            </select>
                        </div>
                        <div class="form-group" id="autreProjetGroup" style="display: none;">
                            <label>Préciser le projet</label>
                            <input type="text" id="autreProjet" name="autreProjet" placeholder="Entrez le nom du projet">
                        </div>
                        <div class="form-group">
                            <label>Partenaire</label>
                            <select id="coordPartenaire" onchange="toggleAutrePartenaire()">
                                <option value="">Sélectionner...</option>
                                <option value="Autre">Autre (préciser)</option>
                            </select>
                        </div>
                        <div class="form-group" id="autrePartenaireGroup" style="display: none;">
                            <label>Préciser le partenaire</label>
                            <input type="text" id="autrePartenaire" name="autrePartenaire" placeholder="Entrez le nom du partenaire" onchange="addPartnerToList()">
                        </div>
                        <div class="form-group">
                            <label>Type d'atelier</label>
                            <select id="coordTypeAtelier" onchange="toggleAutreTypeCoord()">
                                <option value="">Sélectionner...</option>
                                <option value="Atelier">Atelier</option>
                                <option value="Réunion">Réunion</option>
                                <option value="Formation">Formation</option>
                                <option value="Entretien individuel">Entretien individuel</option>
                                <option value="Autre">Autre (préciser)</option>
                            </select>
                        </div>
                        <div class="form-group" id="autreTypeCoordGroup" style="display: none;">
                            <label>Préciser le type</label>
                            <input type="text" id="autreTypeCoord" name="autreTypeCoord" placeholder="Entrez le type d'atelier">
                        </div>
                        <div class="form-group">
                            <label>Mois</label>
                            <select id="coordMois" required>
                                <option value="">Sélectionner...</option>
                                <option value="Janvier">Janvier</option>
                                <option value="Février">Février</option>
                                <option value="Mars">Mars</option>
                                <option value="Avril">Avril</option>
                                <option value="Mai">Mai</option>
                                <option value="Juin">Juin</option>
                                <option value="Juillet">Juillet</option>
                                <option value="Août">Août</option>
                                <option value="Septembre">Septembre</option>
                                <option value="Octobre">Octobre</option>
                                <option value="Novembre">Novembre</option>
                                <option value="Décembre">Décembre</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Date début</label>
                            <input type="date" id="coordDate" required>
                        </div>
                        <div class="form-group">
                            <label>Date fin</label>
                            <input type="date" id="coordDateFin">
                        </div>
                        <div class="form-group">
                            <label>Responsable</label>
                            <select id="coordResponsable" required onchange="toggleAutreResponsableCoord()">
                                <option value="">Sélectionner...</option>
                                <option value="Amira">Amira</option>
                                <option value="Karim">Karim</option>
                                <option value="Denis">Denis</option>
                                <option value="Équipe complète">Équipe complète</option>
                                <option value="Autre">Autre (préciser)</option>
                            </select>
                        </div>
                        <div class="form-group" id="autreResponsableCoordGroup" style="display: none;">
                            <label>Préciser le responsable</label>
                            <input type="text" id="autreResponsableCoord" name="autreResponsableCoord" placeholder="Entrez le nom du responsable">
                        </div>
                        <div class="form-group">
                            <label>Activité / Objectif</label>
                            <input type="text" id="coordActivite" placeholder="Description de l'objectif" required>
                        </div>
                        <div class="form-group">
                            <label>Pièce jointe (Documents, Photos)</label>
                            <input type="file" id="coordPieceJointe" accept=".pdf,.jpg,.jpeg,.png,.gif,.webp,.doc,.docx" onchange="handleFileUpload(event)">
                            <small style="color: #666; font-size: 12px;">Formats acceptés: PDF, JPG, PNG, GIF, WEBP, DOC, DOCX (Max 10MB)</small>
                        </div>
                    </div>
                    <div id="filePreview" style="margin-top: 10px; padding: 10px; background: #f0f0f0; border-radius: 6px; display: none;">
                        <strong>📎 Fichier joint:</strong> <span id="fileName"></span>
                        <button type="button" onclick="removeFile()" style="margin-left: 10px; padding: 4px 8px; background: #ef5350; color: white; border: none; border-radius: 4px; cursor: pointer;">✕</button>
                    </div>
                    <div id="uploadProgress" style="margin-top: 10px; display: none;">
                        <div style="background: #e0e0e0; border-radius: 4px; overflow: hidden;">
                            <div id="progressBar" style="width: 0%; height: 20px; background: #4caf50; transition: width 0.3s;"></div>
                        </div>
                        <small id="progressText" style="color: #666;">Téléchargement en cours...</small>
                    </div>
                    <div class="form-grid" style="margin-top: 20px;">
                        <div class="form-group">
                            <label>Public ciblé</label>
                            <select id="coordPublic" onchange="toggleAutrePublic()">
                                <option value="">Sélectionner...</option>
                                <option value="Enfants (6-12 ans)">Enfants (6-12 ans)</option>
                                <option value="Adolescents (13-17 ans)">Adolescents (13-17 ans)</option>
                                <option value="Adultes">Adultes</option>
                                <option value="Seniors">Seniors</option>
                                <option value="Familles">Familles</option>
                                <option value="Bénévoles">Bénévoles</option>
                                <option value="Personnel">Personnel</option>
                                <option value="Autre">Autre (préciser)</option>
                            </select>
                        </div>
                        <div class="form-group" id="autrePublicGroup" style="display: none;">
                            <label>Préciser le public</label>
                            <input type="text" id="autrePublic" name="autrePublic" placeholder="Entrez le public ciblé">
                        </div>
                        <div class="form-group">
                            <label>Description de l'action partenaire</label>
                            <textarea id="coordActPart" placeholder="Décrivez le matériel requis et l'action à réaliser..." style="min-height: 80px;"></textarea>
                        </div>
                        <div class="form-group">
                            <label>Description de l'action projet</label>
                            <textarea id="coordActProjet" placeholder="Décrivez le matériel requis et l'action à réaliser..." style="min-height: 80px;"></textarea>
                        </div>
                        <div class="form-group">
                            <label>Lieu</label>
                            <input type="text" id="coordLieu" placeholder="Localisation">
                        </div>
                        <div class="form-group">
                            <label>Durée de préparation</label>
                            <input type="text" id="coordDureePrep" placeholder="Ex: 1h30, 45min, 2h">
                        </div>
                        <div class="form-group">
                            <label>Statut</label>
                            <select id="coordStatut" required>
                                <option value="En planification">En planification</option>
                                <option value="Validé">Validé</option>
                                <option value="En cours">En cours</option>
                                <option value="Terminé">Terminé</option>
                                <option value="Reporté">Reporté</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Commentaires</label>
                        <textarea id="coordCommentaires" placeholder="Notes et observations du coordinateur..."></textarea>
                    </div>
                    <button type="submit" class="btn">✅ Ajouter objectif</button>
                </form>
            </div>

            <div class="activities-table">
                <div class="table-header">
                    <h2>📋 Suivi des objectifs</h2>
                    <div class="filters">
                        <select id="filterCoordProjet" onchange="filterCoordActivities()">
                            <option value="">Tous les projets</option>
                            <option value="La Villette">La Villette</option>
                            <option value="Orange Center">Orange Center</option>
                            <option value="CLAS">CLAS</option>
                            <option value="Science PO">Science PO</option>
                        </select>
                        <select id="filterCoordPole" onchange="filterCoordActivities()">
                            <option value="">Tous les pôles</option>
                            <option value="Animation de l'espace public">Animation de l'espace public</option>
                            <option value="Animation parentalité">Animation parentalité</option>
                            <option value="Accompagnement à la jeunesse">Accompagnement à la jeunesse</option>
                            <option value="Ecologie">Ecologie</option>
                            <option value="Précarité alimentaire">Précarité alimentaire</option>
                            <option value="Lien social">Lien social</option>
                            <option value="Inclusion numérique">Inclusion numérique</option>
                            <option value="Voix citoyennes">Voix citoyennes</option>
                            <option value="Insertion professionnelle">Insertion professionnelle</option>
                            <option value="Numérique">Numérique</option>
                        </select>
                        <select id="filterCoordPartenaire" onchange="filterCoordActivities()">
                            <option value="">Tous les partenaires</option>
                        </select>
                        <select id="filterCoordMois" onchange="filterCoordActivities()">
                            <option value="">Tous les mois</option>
                            <option value="Janvier">Janvier</option>
                            <option value="Février">Février</option>
                            <option value="Mars">Mars</option>
                            <option value="Avril">Avril</option>
                            <option value="Mai">Mai</option>
                            <option value="Juin">Juin</option>
                            <option value="Juillet">Juillet</option>
                            <option value="Août">Août</option>
                            <option value="Septembre">Septembre</option>
                            <option value="Octobre">Octobre</option>
                            <option value="Novembre">Novembre</option>
                            <option value="Décembre">Décembre</option>
                        </select>
                        <select id="filterCoordResponsable" onchange="filterCoordActivities()">
                            <option value="">Tous les responsables</option>
                            <option value="Amira">Amira</option>
                            <option value="Karim">Karim</option>
                            <option value="Denis">Denis</option>
                            <option value="Équipe complète">Équipe complète</option>
                        </select>
                    </div>
                </div>
                <div style="overflow-x: auto;">
                    <table>
                        <thead>
                            <tr>
                                <th>Pôle</th>
                                <th>Projet</th>
                                <th>Mois</th>
                                <th>Partenaire</th>
                                <th>Date début</th>
                                <th>Date fin</th>
                                <th>Type d'atelier</th>
                                <th>Activité</th>
                                <th>Responsable</th>
                                <th>Public</th>
                                <th>Description Partenaire</th>
                                <th>Description Projet</th>
                                <th>Durée prép.</th>
                                <th>Documents</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="coordTableBody">
                            <tr>
                                <td colspan="8" class="empty-state">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                    </svg>
                                    <p>Aucun objectif enregistré. Ajoutez un objectif stratégique ci-dessus.</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Section Statistiques -->
        <div id="stats" class="dashboard-section">
        
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="filters">
                        <select id="filterStatsPole">
                            <option value="">Tous les pôles</option>
                            <option value="Animation de l'espace public">Animation de l'espace public</option>
                            <option value="Animation parentalité">Animation parentalité</option>
                            <option value="Accompagnement à la jeunesse">Accompagnement à la jeunesse</option>
                            <option value="Ecologie">Ecologie</option>
                            <option value="Précarité alimentaire">Précarité alimentaire</option>
                            <option value="Lien social">Lien social</option>
                            <option value="Inclusion numérique">Inclusion numérique</option>
                            <option value="Voix citoyennes">Voix citoyennes</option>
                            <option value="Insertion professionnelle">Insertion professionnelle</option>
                            <option value="Numérique">Numérique</option>
                        </select>
                    </div>
                </div>
                <div class="stat-card">
                    <h3>📊 Total activités</h3>
                    <div class="value" id="totalActivites">0</div>
                    <div class="label">activités planifiées</div>
                </div>
                <div class="stat-card">
                    <h3>⏰ Heures totales</h3>
                    <div class="value" id="totalHeures">0h</div>
                    <div class="label">heures prévues</div>
                </div>
                <div class="stat-card">
                    <h3>👥 Responsables actifs</h3>
                    <div class="value" id="responsablesActifs">0</div>
                    <div class="label">membres de l'équipe</div>
                </div>
                <div class="stat-card">
                    <h3>📈 Moyenne</h3>
                    <div class="value" id="moyenneHeures">0h</div>
                    <div class="label">par activité</div>
                </div>
            </div>

            <div class="chart-container">
                <h3>📊 Activités par type</h3>
                <div class="bar-chart" id="chartType"></div>
            </div>

            <div class="chart-container">
                <h3>👥 Charge de travail par responsable</h3>
                <div class="bar-chart" id="chartResponsable"></div>
            </div>

            <div class="chart-container">
                <h3>📅 Activités par mois</h3>
                <div class="bar-chart" id="chartMois"></div>
            </div>
        </div>

        <!-- Section Statistiques Coordinateur -->
        <div id="statsCoord" class="dashboard-section">
        
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="filters">
                        <select id="filterStatsPoleCoord">
                            <option value="">Tous les pôles</option>
                            <option value="Animation de l'espace public">Animation de l'espace public</option>
                            <option value="Animation parentalité">Animation parentalité</option>
                            <option value="Accompagnement à la jeunesse">Accompagnement à la jeunesse</option>
                            <option value="Ecologie">Ecologie</option>
                            <option value="Précarité alimentaire">Précarité alimentaire</option>
                            <option value="Lien social">Lien social</option>
                            <option value="Inclusion numérique">Inclusion numérique</option>
                            <option value="Voix citoyennes">Voix citoyennes</option>
                            <option value="Insertion professionnelle">Insertion professionnelle</option>
                            <option value="Numérique">Numérique</option>
                        </select>
                    </div>
                </div>
                <div class="stat-card">
                    <h3>📋 Total objectifs</h3>
                    <div class="value" id="totalObjectifsCoord">0</div>
                    <div class="label">objectifs stratégiques</div>
                </div>
                <div class="stat-card">
                    <h3>⏰ Heures préparation</h3>
                    <div class="value" id="totalHeuresPrepCoord">0h</div>
                    <div class="label">temps de préparation</div>
                </div>
                <div class="stat-card">
                    <h3>✅ Objectifs réalisés</h3>
                    <div class="value" id="objectifsTerminesCoord">0</div>
                    <div class="label">terminés</div>
                </div>
            </div>

            <div class="chart-container">
                <h3>🎯 Objectifs par statut</h3>
                <div class="bar-chart" id="chartStatutCoord"></div>
            </div>

            <div class="chart-container">
                <h3>🚀 Objectifs par projet</h3>
                <div class="bar-chart" id="chartProjetCoord"></div>
            </div>

            <div class="chart-container">
                <h3>👤 Charge de travail par responsable coordinateur</h3>
                <div class="bar-chart" id="chartResponsableCoord"></div>
            </div>

            <div class="chart-container">
                <h3>📅 Objectifs par mois</h3>
                <div class="bar-chart" id="chartMoisCoord"></div>
            </div>

            <div class="chart-container">
                <h3>🤝 Partenaires impliqués</h3>
                <div class="bar-chart" id="chartPartenaireCoord"></div>
            </div>

            <div class="chart-container">
                <h3>👥 Public cible</h3>
                <div class="bar-chart" id="chartPublicCoord"></div>
            </div>
        </div>

	

    <script>
        let activities = [];
        let coordActivities = [];
        let editingIndex = -1;
        let coordEditingIndex = -1;
        let currentFileName = '';
        let partnersList = [];

        function addPartnerToList() {
            const partnerInput = document.getElementById('autrePartenaire');
            const partner = partnerInput.value.trim();
            const selectedProjet = document.getElementById('coordProjet').value;
            if (partner && !partnersList.some(p => p.projet === selectedProjet && p.partenaire === partner)) {
                partnersList.push({ projet: selectedProjet, partenaire: partner });
                updatePartnersSelect();
            }
        }

        function updatePartnersSelect() {
            const select = document.getElementById('coordPartenaire');
            const selectedProjet = document.getElementById('coordProjet').value;
            const currentValue = select.value;
            
            // Filter partners by selected project
            const projectPartners = selectedProjet 
                ? partnersList.filter(p => p.projet === selectedProjet).map(p => p.partenaire)
                : partnersList.map(p => p.partenaire);
            
            const uniquePartners = [...new Set(projectPartners)];
            select.innerHTML = '<option value="">Sélectionner...</option>' + 
                uniquePartners.map(p => `<option value="${p}">${p}</option>`).join('') +
                '<option value="Autre">Autre (préciser)</option>';
            select.value = currentValue || '';
        }

        function toggleAutrePartenaire() {
            const partSelect = document.getElementById('coordPartenaire');
            const autreGroup = document.getElementById('autrePartenaireGroup');
            const autreInput = document.getElementById('autrePartenaire');
            
            if (partSelect.value === 'Autre') {
                autreGroup.style.display = 'block';
                autreInput.required = true;
            } else {
                autreGroup.style.display = 'none';
                autreInput.required = false;
                autreInput.value = '';
            }
        }

        function toggleAutreTypeCoord() {
            const typeSelect = document.getElementById('coordTypeAtelier');
            const autreGroup = document.getElementById('autreTypeCoordGroup');
            const autreInput = document.getElementById('autreTypeCoord');
            
            if (typeSelect.value === 'Autre') {
                autreGroup.style.display = 'block';
                autreInput.required = true;
            } else {
                autreGroup.style.display = 'none';
                autreInput.required = false;
                autreInput.value = '';
            }
        }

        function handleFileUpload(event) {
            const file = event.target.files[0];
            if (!file) return;

            const maxSize = 10 * 1024 * 1024; // 10MB
            if (file.size > maxSize) {
                alert('Le fichier est trop volumineux (max 10MB)');
                event.target.value = '';
                return;
            }

            const allowedTypes = ['application/pdf', 'image/jpeg', 'image/png', 'image/gif', 'image/webp', 
                                 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
            if (!allowedTypes.includes(file.type)) {
                alert('Type de fichier non autorisé. Formats acceptés: PDF, JPG, PNG, GIF, WEBP, DOC, DOCX');
                event.target.value = '';
                return;
            }

            // Afficher la barre de progression
            const progressDiv = document.getElementById('uploadProgress');
            const progressBar = document.getElementById('progressBar');
            const progressText = document.getElementById('progressText');
            progressDiv.style.display = 'block';
            progressBar.style.width = '0%';

            // Créer FormData et envoyer le fichier
            const formData = new FormData();
            formData.append('file', file);

            // Simuler la progression
            let progress = 0;
            const progressInterval = setInterval(() => {
                if (progress < 90) {
                    progress += Math.random() * 30;
                    progressBar.style.width = Math.min(progress, 90) + '%';
                }
            }, 200);

            fetch('upload.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                clearInterval(progressInterval);
                if (data.status === 'success') {
                    currentFileName = data.filename;
                    document.getElementById('fileName').textContent = data.originalName + ' ✓';
                    document.getElementById('filePreview').style.display = 'block';
                    progressBar.style.width = '100%';
                    progressText.textContent = 'Fichier téléchargé avec succès';
                    setTimeout(() => {
                        progressDiv.style.display = 'none';
                    }, 2000);
                } else {
                    alert('Erreur: ' + (data.message || 'Upload failed'));
                    event.target.value = '';
                    removeFile();
                }
            })
            .catch(error => {
                clearInterval(progressInterval);
                alert('Erreur lors du téléchargement: ' + error);
                event.target.value = '';
                removeFile();
            });
        }

        function removeFile() {
            currentFileName = '';
            document.getElementById('coordPieceJointe').value = '';
            document.getElementById('filePreview').style.display = 'none';
            document.getElementById('uploadProgress').style.display = 'none';
        }

        function switchTab(tab) {
            document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.dashboard-section').forEach(s => s.classList.remove('active'));
            
            event.target.classList.add('active');
            document.getElementById(tab).classList.add('active');
            
            if (tab === 'stats') {
                updateStatistics();
            } else if (tab === 'statsCoord') {
                updateCoordStatistics();
            }
        }

        function toggleAutreType() {
            const typeSelect = document.getElementById('typeAtelier');
            const autreGroup = document.getElementById('autreTypeGroup');
            const autreInput = document.getElementById('autreType');
            
            if (typeSelect.value === 'Autre') {
                autreGroup.style.display = 'block';
                autreInput.required = true;
            } else {
                autreGroup.style.display = 'none';
                autreInput.required = false;
                autreInput.value = '';
            }
        }

        function toggleAutreResponsable() {
            const respSelect = document.getElementById('responsable');
            const autreGroup = document.getElementById('autreResponsableGroup');
            const autreInput = document.getElementById('autreResponsable');
            
            if (respSelect.value === 'Autre') {
                autreGroup.style.display = 'block';
                autreInput.required = true;
            } else {
                autreGroup.style.display = 'none';
                autreInput.required = false;
                autreInput.value = '';
            }
        }

        function toggleAutrePublic() {
            const publicSelect = document.getElementById('coordPublic');
            const autreGroup = document.getElementById('autrePublicGroup');
            const autreInput = document.getElementById('autrePublic');
            
            if (publicSelect.value === 'Autre') {
                autreGroup.style.display = 'block';
                autreInput.required = true;
            } else {
                autreGroup.style.display = 'none';
                autreInput.required = false;
                autreInput.value = '';
            }
        }

        function toggleAutreResponsableCoord() {
            const respSelect = document.getElementById('coordResponsable');
            const autreGroup = document.getElementById('autreResponsableCoordGroup');
            const autreInput = document.getElementById('autreResponsableCoord');
            
            if (respSelect.value === 'Autre') {
                autreGroup.style.display = 'block';
                autreInput.required = true;
            } else {
                autreGroup.style.display = 'none';
                autreInput.required = false;
                autreInput.value = '';
            }
        }

        function toggleAutreProjet() {
            const projetSelect = document.getElementById('coordProjet');
            const autreGroup = document.getElementById('autreProjetGroup');
            const autreInput = document.getElementById('autreProjet');
            
            if (projetSelect.value === 'Autre') {
                autreGroup.style.display = 'block';
            } else {
                autreGroup.style.display = 'none';
                autreInput.value = '';
            }
            
            // Reset partner selection when project changes
            document.getElementById('coordPartenaire').value = '';
            document.getElementById('autrePartenaireGroup').style.display = 'none';
            document.getElementById('autrePartenaire').value = '';
            updatePartnersSelect();
        }

        function toggleAutreProjetOp() {
            const projetSelect = document.getElementById('projet');
            const autreGroup = document.getElementById('autreProjetOpGroup');
            const autreInput = document.getElementById('autreProjetOp');
            
            if (projetSelect.value === 'Autre') {
                autreGroup.style.display = 'block';
                autreInput.required = true;
            } else {
                autreGroup.style.display = 'none';
                autreInput.required = false;
                autreInput.value = '';
            }
        }

        function calculateDuration() {
            const debut = document.getElementById('heureDebut').value;
            const fin = document.getElementById('heureFin').value;
            
            if (debut && fin) {
                const [hD, mD] = debut.split(':').map(Number);
                const [hF, mF] = fin.split(':').map(Number);
                
                const minutesDebut = hD * 60 + mD;
                const minutesFin = hF * 60 + mF;
                const diffMinutes = minutesFin - minutesDebut;
                
                if (diffMinutes > 0) {
                    const heures = Math.floor(diffMinutes / 60);
                    const minutes = diffMinutes % 60;
                    document.getElementById('duree').value = `${heures}h${minutes > 0 ? minutes.toString().padStart(2, '0') : ''}`;
                } else {
                    document.getElementById('duree').value = '0h';
                }
            }
        }

        function calculateCoordDuration() {
            const debut = document.getElementById('coordHeureDebut').value;
            const fin = document.getElementById('coordHeureFin').value;
            
            if (debut && fin) {
                const [hD, mD] = debut.split(':').map(Number);
                const [hF, mF] = fin.split(':').map(Number);
                
                const minutesDebut = hD * 60 + mD;
                const minutesFin = hF * 60 + mF;
                const diffMinutes = minutesFin - minutesDebut;
                
                if (diffMinutes > 0) {
                    const heures = Math.floor(diffMinutes / 60);
                    const minutes = diffMinutes % 60;
                    document.getElementById('coordDuree').value = `${heures}h${minutes > 0 ? minutes.toString().padStart(2, '0') : ''}`;
                } else {
                    document.getElementById('coordDuree').value = '0h';
                }
            }
            
            calculateTotalDuration();
        }

        function calculateTotalDuration() {
            const dureeActivite = document.getElementById('coordDuree').value;
            const dureePrep = document.getElementById('coordDureePrep').value;
            
            let totalMinutes = 0;
            
            if (dureeActivite) {
                const match = dureeActivite.match(/(\d+)h(\d*)/);
                if (match) {
                    totalMinutes += parseInt(match[1]) * 60 + (match[2] ? parseInt(match[2]) : 0);
                }
            }
            
            if (dureePrep) {
                const prepMatch = dureePrep.match(/(\d+)h(\d*)|(\d+)min/);
                if (prepMatch) {
                    if (prepMatch[1]) {
                        totalMinutes += parseInt(prepMatch[1]) * 60 + (prepMatch[2] ? parseInt(prepMatch[2]) : 0);
                    } else if (prepMatch[3]) {
                        totalMinutes += parseInt(prepMatch[3]);
                    }
                }
            }
            
            if (totalMinutes > 0) {
                const heures = Math.floor(totalMinutes / 60);
                const minutes = totalMinutes % 60;
                document.getElementById('coordDureeTotale').value = `${heures}h${minutes > 0 ? minutes.toString().padStart(2, '0') : ''}`;
            } else {
                document.getElementById('coordDureeTotale').value = '';
            }
        }

        document.getElementById('coordDureePrep')?.addEventListener('input', calculateTotalDuration);

        document.getElementById('activityForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            let typeAtelier = document.getElementById('typeAtelier').value;
            if (typeAtelier === 'Autre') {
                typeAtelier = document.getElementById('autreType').value;
            }

            let responsable = document.getElementById('responsable').value;
            if (responsable === 'Autre') {
                responsable = document.getElementById('autreResponsable').value;
            }

            let projet = document.getElementById('projet').value;
            if (projet === 'Autre') {
                projet = document.getElementById('autreProjetOp').value;
            }
            const pole = document.getElementById('pole').value;
            const activity = {
                projet: projet || '',
                pole: pole || '',  
                mois: document.getElementById('mois').value,
                date: document.getElementById('date').value,
                periode: document.getElementById('periode').value,
                objectif: document.getElementById('objectif').value,
                type: typeAtelier,
                responsable: responsable,
                lieu: document.getElementById('lieu').value,
                heureDebut: document.getElementById('heureDebut').value,
                heureFin: document.getElementById('heureFin').value,
                duree: document.getElementById('duree').value,
                participants: document.getElementById('participants').value,
                commentaire: document.getElementById('commentaire').value,
                statut: 'Prévu'
            };
            
            if (editingIndex >= 0) {
                activities[editingIndex] = activity;
                editingIndex = -1;
            } else {
                activities.push(activity);
            }
            
            this.reset();
            document.getElementById('autreTypeGroup').style.display = 'none';
            document.getElementById('autreResponsableGroup').style.display = 'none';
            document.getElementById('autreProjetOpGroup').style.display = 'none';
            renderActivities();
			saveDataToDB();
        });

        document.getElementById('coordForm').addEventListener('submit', function(e) {
            e.preventDefault();

            let publicCible = document.getElementById('coordPublic').value;
            if (publicCible === 'Autre') {
                publicCible = document.getElementById('autrePublic').value;
            }

            let responsable = document.getElementById('coordResponsable').value;
            if (responsable === 'Autre') {
                responsable = document.getElementById('autreResponsableCoord').value;
            }

            let projet = document.getElementById('coordProjet').value;
            if (projet === 'Autre') {
                projet = document.getElementById('autreProjet').value;
            }

            let partenaire = document.getElementById('coordPartenaire').value;
            if (partenaire === 'Autre') {
                partenaire = document.getElementById('autrePartenaire').value;
            }

            let typeAtelier = document.getElementById('coordTypeAtelier').value;
            if (typeAtelier === 'Autre') {
                typeAtelier = document.getElementById('autreTypeCoord').value;
            }
            
            const coordActivity = {
                mois: document.getElementById('coordMois').value,
                date: document.getElementById('coordDate').value,
                dateFin: document.getElementById('coordDateFin').value || '',
                responsable: responsable,
                projet: projet || '',
                partenaire: partenaire || '',
                typeAtelier: typeAtelier || '',
                activite: document.getElementById('coordActivite').value,
                pieceJointe: currentFileName || '',
                public: publicCible,
                materiel: document.getElementById('coordMateriel')?.value || '',
                lieu: document.getElementById('coordLieu').value,
                dureePrep: document.getElementById('coordDureePrep').value,
                statut: document.getElementById('coordStatut').value,
                commentaires: document.getElementById('coordCommentaires').value,
                pole: document.getElementById('pole_coord').value,
                actPart: document.getElementById('coordActPart')?.value || '',
                actProjet: document.getElementById('coordActProjet')?.value || '',
            };
            
            if (coordEditingIndex >= 0) {
                coordActivities[coordEditingIndex] = coordActivity;
                coordEditingIndex = -1;
            } else {
                coordActivities.push(coordActivity);
            }
            
            // Add partner to list if it's a custom one
            const selectedProjet = document.getElementById('coordProjet').value;
            if (partenaire && selectedProjet && !partnersList.some(p => p.projet === selectedProjet && p.partenaire === partenaire)) {
                partnersList.push({ projet: selectedProjet, partenaire: partenaire });
            }
            
            document.getElementById('autrePublicGroup').style.display = 'none';
            document.getElementById('autreResponsableCoordGroup').style.display = 'none';
            document.getElementById('autreProjetGroup').style.display = 'none';
            document.getElementById('autrePartenaireGroup').style.display = 'none';
            document.getElementById('autreTypeCoordGroup').style.display = 'none';
            removeFile();
            updatePartnersFilterSelect();
            renderCoordActivities();
			saveDataToDB(); 
            this.reset();
        });

        function renderActivities() {
            const tbody = document.getElementById('activitiesTableBody');
            const filteredActivities = getFilteredActivities();
            
            if (filteredActivities.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="11" class="empty-state">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            <p>Aucune activité ne correspond aux filtres sélectionnés.</p>
                        </td>
                    </tr>
                `;
                return;
            }
            
            tbody.innerHTML = filteredActivities.map((activity, index) => `
                <tr>
                    <td>${activity.projet ? '<strong>📁 ' + activity.projet + '</strong>' : '-'}</td>
                    <td>${activity.pole}</td>
                    <td>${activity.mois}</td>
                    <td>${activity.date}</td>
                    <td>${activity.periode}</td>
                    <td><strong>${activity.objectif}</strong><br><small>${activity.lieu}</small></td>
                    <td>${activity.type}</td>
                    <td>${activity.responsable}</td>
                    <td>${activity.heureDebut} - ${activity.heureFin}</td>
                    <td><span class="duration-display">${activity.duree}</span></td>
                    <td><span class="status-badge status-${activity.statut.toLowerCase().replace(' ', '')}">${activity.statut}</span></td>
                    <td>
                        <div class="action-buttons">
                            <button class="btn btn-small btn-edit" onclick="editActivity(${activities.indexOf(activity)})">✏️</button>
                            <button class="btn btn-small btn-delete" onclick="deleteActivity(${activities.indexOf(activity)})">🗑️</button>
                        </div>
                    </td>
                </tr>
            `).join('');
        }

        function getFilteredCoordActivities() {
            const filterProjet = document.getElementById('filterCoordProjet').value;
            const filterPartenaire = document.getElementById('filterCoordPartenaire').value;
            const filterMois = document.getElementById('filterCoordMois').value;
            const filterResponsable = document.getElementById('filterCoordResponsable').value;
            const poleFilter = document.getElementById('filterCoordPole').value;

            return coordActivities.filter(activity => {
                return (!filterProjet || activity.projet === filterProjet) &&
                       (!filterPartenaire || activity.partenaire === filterPartenaire) &&
                       (!filterMois || activity.mois === filterMois) &&
                       (!filterResponsable || activity.responsable === filterResponsable) &&
                       (!poleFilter || activity.pole === poleFilter);

            });
        }

        function filterCoordActivities() {
            updatePartnersFilterSelect();
            renderCoordActivities();
        }

        function updatePartnersFilterSelect() {
            const uniquePartners = [...new Set(coordActivities.map(a => a.partenaire).filter(p => p))];
            const select = document.getElementById('filterCoordPartenaire');
            const currentValue = select.value;
            select.innerHTML = '<option value="">Tous les partenaires</option>';
            uniquePartners.forEach(partner => {
                select.innerHTML += `<option value="${partner}">${partner}</option>`;
            });
            select.value = currentValue;
        }

        function renderCoordActivities() {
            const tbody = document.getElementById('coordTableBody');
            const filteredActivities = getFilteredCoordActivities();
            
            if (filteredActivities.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="12" class="empty-state">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                            <p>Aucun objectif enregistré.</p>
                        </td>
                    </tr>
                `;
                return;
            }
            
            tbody.innerHTML = filteredActivities.map((activity, index) => {
                const actualIndex = coordActivities.indexOf(activity);
                const documentLink = activity.pieceJointe ? getDocumentLink(activity.pieceJointe) : '-';
                return `
                <tr>
                    <td><strong>${activity.pole || ''}</strong></td>
                    <td>${activity.projet ? '<strong>📁 ' + activity.projet + '</strong>' : '-'}</td>
                    <td>${activity.mois}</td>
                    <td>${activity.partenaire || '-'}</td>
                    <td>${activity.date}</td>
                    <td>${activity.dateFin || '-'}</td>
                    <td>${activity.typeAtelier || '-'}</td>
                    <td><strong>${activity.activite}</strong><br><small>${activity.commentaires || ''}</small>${activity.materiel ? '<br><small>📋 ' + activity.materiel + '</small>' : ''}</td>
                    <td>${activity.responsable}</td>
                    <td>${activity.public || '-'}</td>
                    <td>${activity.actPart || '-'}</td>
                    <td>${activity.actProjet || '-'}</td>
                    <td>${activity.dureePrep ? '🔧 Prép: ' + activity.dureePrep : '-'}</td>
                    <td>${documentLink}</td>
                    <td><span class="status-badge status-${activity.statut.toLowerCase().replace(' ', '')}">${activity.statut}</span></td>
                    <td>
                        <div class="action-buttons">
                            <button class="btn btn-small btn-edit" onclick="editCoordActivity(${actualIndex})">✏️</button>
                            <button class="btn btn-small btn-delete" onclick="deleteCoordActivity(${actualIndex})">🗑️</button>
                        </div>
                    </td>
                </tr>
            `;
            }).join('');
        }

        function getDocumentLink(filename) {
            if (!filename) return '-';
            const ext = filename.split('.').pop().toLowerCase();
            const isImage = ['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(ext);
            const isPdf = ext === 'pdf';
            const isDoc = ['doc', 'docx'].includes(ext);
            
            let type = 'doc';
            if (isImage) type = 'image';
            if (isPdf) type = 'pdf';
            
            return `<a href="download.php?file=${encodeURIComponent(filename)}" target="_blank" class="document-link ${type}">Voir</a>`;
        }

        function getFilteredActivities() {
            const filterMois = document.getElementById('filterMois').value;
            const filterResponsable = document.getElementById('filterResponsable').value;
            const filterType = document.getElementById('filterType').value;
            const filterStatut = document.getElementById('filterStatut').value;
            const poleFilter = document.getElementById('filterPole').value;

            return activities.filter(activity => {
                return (!filterMois || activity.mois === filterMois) &&
                       (!filterResponsable || activity.responsable === filterResponsable) &&
                       (!filterType || activity.type === filterType) &&
                       (!filterStatut || activity.statut === filterStatut)&&
                       (!poleFilter || activity.pole === poleFilter);
            });
        }

        function filterActivities() {
            renderActivities();
        }

        function editActivity(index) {
            const activity = activities[index];
            editingIndex = index;

            const standardProjets = ['La Villette', 'Orange Center', 'CLAS', 'Science PO'];
            if (standardProjets.includes(activity.projet)) {
                document.getElementById('projet').value = activity.projet;
            } else if (activity.projet) {
                document.getElementById('projet').value = 'Autre';
                document.getElementById('autreProjetOp').value = activity.projet;
                toggleAutreProjetOp();
            }
            
            document.getElementById('mois').value = activity.mois;
            document.getElementById('date').value = activity.date;
            document.getElementById('periode').value = activity.periode;
            document.getElementById('objectif').value = activity.objectif;
            
            const standardTypes = ['Atelier', 'Réunion', 'Formation', 'Entretien individuel'];
            if (standardTypes.includes(activity.type)) {
                document.getElementById('typeAtelier').value = activity.type;
            } else {
                document.getElementById('typeAtelier').value = 'Autre';
                document.getElementById('autreType').value = activity.type;
                toggleAutreType();
            }

            const standardResponsables = ['Amira', 'Karim', 'Denis'];
            if (standardResponsables.includes(activity.responsable)) {
                document.getElementById('responsable').value = activity.responsable;
            } else {
                document.getElementById('responsable').value = 'Autre';
                document.getElementById('autreResponsable').value = activity.responsable;
                toggleAutreResponsable();
            }
            document.getElementById('lieu').value = activity.lieu;
            document.getElementById('heureDebut').value = activity.heureDebut;
            document.getElementById('heureFin').value = activity.heureFin;
            document.getElementById('duree').value = activity.duree;
            document.getElementById('participants').value = activity.participants;
            document.getElementById('pole').value = activity.pole || '';
            document.getElementById('commentaire').value = activity.commentaire;
            
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        function deleteActivity(index) {
            if (confirm('Êtes-vous sûr de vouloir supprimer cette activité ?')) {
                activities.splice(index, 1);
                renderActivities();
                saveDataToDB();
            }
        }

        function editCoordActivity(index) {
            const activity = coordActivities[index];
            coordEditingIndex = index;

            document.getElementById('coordMois').value = activity.mois;
            document.getElementById('coordDate').value = activity.date;
            document.getElementById('coordDateFin').value = activity.dateFin || '';

            const standardResponsables = ['Amira', 'Karim', 'Denis', 'Équipe complète'];
            if (standardResponsables.includes(activity.responsable)) {
                document.getElementById('coordResponsable').value = activity.responsable;
            } else {
                document.getElementById('coordResponsable').value = 'Autre';
                document.getElementById('autreResponsableCoord').value = activity.responsable;
                toggleAutreResponsableCoord();
            }

            const standardProjets = ['La Villette', 'Orange Center', 'CLAS', 'Science PO'];
            if (standardProjets.includes(activity.projet)) {
                document.getElementById('coordProjet').value = activity.projet;
            } else if (activity.projet) {
                document.getElementById('coordProjet').value = 'Autre';
                document.getElementById('autreProjet').value = activity.projet;
                toggleAutreProjet();
            }

            updatePartnersSelect();
            const standardPartenaires = partnersList
                .filter(p => p.projet === activity.projet)
                .map(p => p.partenaire);
            if (standardPartenaires.includes(activity.partenaire)) {
                document.getElementById('coordPartenaire').value = activity.partenaire;
            } else if (activity.partenaire) {
                document.getElementById('coordPartenaire').value = 'Autre';
                document.getElementById('autrePartenaire').value = activity.partenaire;
                toggleAutrePartenaire();
            }

            document.getElementById('coordActivite').value = activity.activite;

            if (activity.pieceJointe) {
                currentFileName = activity.pieceJointe;
                document.getElementById('fileName').textContent = activity.pieceJointe;
                document.getElementById('filePreview').style.display = 'block';
            }

            const standardPublics = [
                'Enfants (6-12 ans)',
                'Adolescents (13-17 ans)',
                'Adultes',
                'Seniors',
                'Familles',
                'Bénévoles',
                'Personnel'
            ];
            if (standardPublics.includes(activity.public)) {
                document.getElementById('coordPublic').value = activity.public;
            } else {
                document.getElementById('coordPublic').value = 'Autre';
                document.getElementById('autrePublic').value = activity.public;
                toggleAutrePublic();
            }

            document.getElementById('coordMateriel')?.value && (document.getElementById('coordMateriel').value = activity.materiel || '');
            document.getElementById('coordLieu').value = activity.lieu || '';
            document.getElementById('coordDureePrep').value = activity.dureePrep || '';
            document.getElementById('coordStatut').value = activity.statut || 'Prévu';
            document.getElementById('coordCommentaires').value = activity.commentaires || '';

			// Type d'atelier
			const standardTypes = [
				'Atelier',
				'Réunion',
				'Formation',
				'Entretien individuel',
				'Autre'
			];

			if (standardTypes.includes(activity.typeAtelier)) {
				document.getElementById('coordTypeAtelier').value = activity.typeAtelier;
			} else if (activity.typeAtelier) {
				// si plus tard tu ajoutes un "Autre (préciser)" avec un champ texte dédié
				document.getElementById('coordTypeAtelier').value = 'Autre';
				// document.getElementById('autreTypeAtelierCoord').value = activity.typeAtelier;
				toggleAutreTypeCoord();
			} else {
				document.getElementById('coordTypeAtelier').value = '';
			}

			document.getElementById('coordActPart').value = activity.actPart || '';
			document.getElementById('coordActProjet').value = activity.actProjet || '';
            document.getElementById('pole_coord').value = activity.pole || '';

            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        function deleteCoordActivity(index) {
            if (confirm('Êtes-vous sûr de vouloir supprimer cet objectif ?')) {
                coordActivities.splice(index, 1);
                renderCoordActivities();
                saveDataToDB();
            }
        }

        // ========== SYNCHRO AVEC MARIADB ==========
        const API_URL = 'http://192.168.1.100/dashboard_api.php';

        async function loadDataFromDB() {
            try {
                const res = await fetch(API_URL);
                const data = await res.json();
                if (data.status === 'success') {
                    activities = (data.activities || []).map(a => ({
					// colonnes venant de la base
					id: a.id,
					projet: a.projet,
                    pole: a.pole,
					mois: a.mois,
					date: a.date,
					periode: a.periode,
					objectif: a.objectif,
					// mapping important :
					type: a.type_atelier,          // <- colonne SQL vers propriété JS
					responsable: a.responsable,
					lieu: a.lieu,
					heureDebut: a.heure_debut,     // <- colonne SQL vers propriété JS
					heureFin: a.heure_fin,         // <- colonne SQL vers propriété JS
					duree: a.duree,
					participants: a.participants,
					commentaire: a.commentaire,
					statut: a.statut || 'Prévu'
					}));

				coordActivities = (data.coordActivities || []).map(c => ({
					id: c.id,
					projet: c.projet,
                    pole: c.pole ?? null,
					mois: c.mois,
					date: c.date,
					dateFin: c.date_fin || '',
					responsable: c.responsable,
					activite: c.activite,
					public: c.public_cible,
					lieu: c.lieu,
					statut: c.statut,
					commentaires: c.commentaires,
					partenaire: c.partenaire || null,
					typeAtelier: c.type_atelier || null,
					dureePrep: c.duree_prep || null,
					materiel: c.materiel || null,
					pieceJointe: c.piece_jointe || null,
					actPart: c.description_action_partenaire || null,
					actProjet: c.description_action_projet || null
					}));
				

                    renderActivities();
                    renderCoordActivities();
                    updatePartnersSelect();
                    console.log('✅ Données chargées depuis MariaDB');
					updateStats();
					updateCoordStatistics();
                } else {
                    console.log('⚠️ Erreur API load:', data.message);
                }
            } catch (e) {
                console.log('⚠️ Impossible de charger depuis MariaDB', e);
            }
        }

        async function saveDataToDB() {
            try {
                const payload = {
                    activities: activities,
                    coordActivities: coordActivities
                };
                const res = await fetch(API_URL, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                const data = await res.json();
                if (data.status === 'success') {
                    console.log('💾 Données sauvegardées dans MariaDB');
                } else {
                    console.log('⚠️ Erreur API save:', data.message);
                }
            } catch (e) {
                console.log('⚠️ Impossible de sauvegarder dans MariaDB', e);
            }
        }

        // Charger les données au démarrage
        loadDataFromDB();

		// =====================
		// STATISTIQUES DASHBOARD
		// =====================

		function parseDureeToHours(dureeStr) {
			if (!dureeStr) return 0;
			dureeStr = dureeStr.toString().trim().toLowerCase();

			// Format "hhHmm" ou "hHm" : 2h02, 1h05, 0h50, 1h5
			// On capture la partie avant et après le "h"
			const hCompactMatch = dureeStr.match(/(\d+)\s*h\s*(\d{1,2})?/);
			if (hCompactMatch) {
				const h = parseInt(hCompactMatch[1], 10);
				const m = hCompactMatch[2] ? parseInt(hCompactMatch[2], 10) : 0;
				return h + (m / 60);
			}

			// Formats "50min", "50 min", "50m"
			const mMatch = dureeStr.match(/(\d+)\s*(?:min|m)/);
			if (mMatch) {
				const minutes = parseInt(mMatch[1], 10);
				return minutes / 60;
			}

			// Si rien trouvé mais nombre seul → minutes
			const num = parseFloat(dureeStr.replace(',', '.'));
			if (!isNaN(num)) {
				return num / 60;
			}

			return 0;
		}

		
		function formatHoursToHM(hoursFloat) {
			if (!hoursFloat || hoursFloat <= 0) return '0 min';
			const totalMinutes = Math.round(hoursFloat * 60);
			const h = Math.floor(totalMinutes / 60);
			const m = totalMinutes % 60;
			if (h > 0 && m > 0) return `${h}h ${m}min`;
			if (h > 0) return `${h}h`;
			return `${m}min`;
		}

		function updateStats() {
			
            const poleFilter = document.getElementById('filterStatsPole')?.value;

            
            // on part de la liste activities globale déjà remplie par loadDataFromDB()
			//const list = activities || [];
            const list = activities.filter(a => !poleFilter || a.pole === poleFilter);

			// 1. Total activités
			const total = list.length;

			// 2. Heures totales (en utilisant le champ "duree")
			let totalHeures = 0;
			const heuresParResponsable = {};
			const activitesParType = {};
			const activitesParMois = {};
			const responsablesSet = new Set();
			console.log('DEBUG durees activities:', list.map(a => a.duree));

			list.forEach(a => {
				const dureeHeures = parseDureeToHours(a.duree);
				totalHeures += dureeHeures;

				// Responsable
				if (a.responsable) {
					responsablesSet.add(a.responsable);
					heuresParResponsable[a.responsable] = (heuresParResponsable[a.responsable] || 0) + dureeHeures;
				}

				// Type d'atelier (a.type dans ton mapping)
				const type = a.type || 'Non précisé';
				activitesParType[type] = (activitesParType[type] || 0) + 1;

				// Mois
				const mois = a.mois || 'Non précisé';
				activitesParMois[mois] = (activitesParMois[mois] || 0) + 1;
			});

			// 3. Responsables actifs
			const nbResponsables = responsablesSet.size;

			// 4. Moyenne d'heures par activité
			const moyenne = total > 0 ? (totalHeures / total) : 0;

			// Mise à jour des compteurs
			document.getElementById('totalActivites').textContent = total.toString();
			document.getElementById('responsablesActifs').textContent = nbResponsables.toString();
			document.getElementById('totalHeures').textContent = formatHoursToHM(totalHeures);
			document.getElementById('moyenneHeures').textContent = formatHoursToHM(moyenne);

			// Mise à jour des "graphes" simples en barres (HTML/CSS)
			renderBarChart('chartType', activitesParType, 'Activités');
			renderBarChart('chartResponsable', heuresParResponsable, 'h');
			renderBarChart('chartMois', activitesParMois, 'Activités');
		}

		function updateCoordStatistics() {
			const poleFilter = document.getElementById('filterStatsPoleCoord')?.value;
			const list = coordActivities.filter(c => !poleFilter || c.pole === poleFilter);

			// 1. Total objectifs
			const total = list.length;

			// 2. Heures de préparation
			let totalHeuresPrep = 0;
			const heuresPrepParResponsable = {};
			const objectifsParStatut = {};
			const objectifsParProjet = {};
			const objectifsParMois = {};
			const objectifsParPartenaire = {};
			const objectifsParPublic = {};
			const responsablesSet = new Set();
			let objectifsTermines = 0;

			list.forEach(c => {
				// Heures de préparation
				const dureeHeures = parseDureeToHours(c.dureePrep);
				totalHeuresPrep += dureeHeures;

				// Responsable
				if (c.responsable) {
					responsablesSet.add(c.responsable);
					heuresPrepParResponsable[c.responsable] = (heuresPrepParResponsable[c.responsable] || 0) + dureeHeures;
				}

				// Statut
				const statut = c.statut || 'Non défini';
				objectifsParStatut[statut] = (objectifsParStatut[statut] || 0) + 1;
				if (statut.toLowerCase() === 'terminé') {
					objectifsTermines++;
				}

				// Projet
				const projet = c.projet || 'Non affecté';
				objectifsParProjet[projet] = (objectifsParProjet[projet] || 0) + 1;

				// Mois
				const mois = c.mois || 'Non précisé';
				objectifsParMois[mois] = (objectifsParMois[mois] || 0) + 1;

				// Partenaire
				if (c.partenaire) {
					objectifsParPartenaire[c.partenaire] = (objectifsParPartenaire[c.partenaire] || 0) + 1;
				}

				// Public
				if (c.public) {
					objectifsParPublic[c.public] = (objectifsParPublic[c.public] || 0) + 1;
				}
			});

			// Taux de réussite
			// const tauxReussite = total > 0 ? Math.round((objectifsTermines / total) * 100) : 0;

			// Mise à jour des compteurs
			document.getElementById('totalObjectifsCoord').textContent = total.toString();
			document.getElementById('totalHeuresPrepCoord').textContent = formatHoursToHM(totalHeuresPrep);
			document.getElementById('objectifsTerminesCoord').textContent = objectifsTermines.toString();
			// document.getElementById('tauxReussiteCoord').textContent = tauxReussite + '%';

			// Mise à jour des graphiques
			renderCoordBarChart('chartStatutCoord', objectifsParStatut, 'Objectifs');
			renderCoordBarChart('chartProjetCoord', objectifsParProjet, 'Objectifs');
			renderCoordBarChart('chartResponsableCoord', heuresPrepParResponsable, 'h');
			renderCoordBarChart('chartMoisCoord', objectifsParMois, 'Objectifs');
			renderCoordBarChart('chartPartenaireCoord', objectifsParPartenaire, 'Objectifs');
			renderCoordBarChart('chartPublicCoord', objectifsParPublic, 'Objectifs');
		}

		function renderCoordBarChart(containerId, dataObj, unitLabel) {
			const container = document.getElementById(containerId);
			if (!container) {
				console.error('❌ Container pas trouvé:', containerId);
				return;
			}
			
			const entries = Object.entries(dataObj);
			if (entries.length === 0) {
				container.innerHTML = '<p class="empty-state">Aucune donnée disponible</p>';
				return;
			}
			
			const maxValue = Math.max(...entries.map(([k, v]) => v || 0));
			
			container.innerHTML = entries.map(([label, value]) => {
				const percentage = maxValue > 0 ? (value / maxValue) * 100 : 0;
				let displayValue = value;
				
				// Format heures si unitLabel = 'h'
				if (unitLabel === 'h') {
					displayValue = formatHoursToHM(value);
				}
				
				return `
					<div class="bar-item">
						<span class="bar-label">${label}</span>
						<div class="bar-container">
							<div class="bar-fill" style="width: ${percentage}%">
								${displayValue}
							</div>
						</div>
					</div>
				`;
			}).join('');
		}
        
        document.getElementById('filterStatsPole').addEventListener('change', updateStats);
        document.getElementById('filterStatsPoleCoord').addEventListener('change', updateCoordStatistics);

        function renderBarChart(containerId, dataObj, unitLabel) {
            const container = document.getElementById(containerId);
            if (!container) {
                console.error('❌ Container pas trouvé:', containerId);
                return;
            }
            
            const entries = Object.entries(dataObj);
            if (entries.length === 0) {
                container.innerHTML = '<p class="empty-state">Aucune donnée disponible</p>';
                return;
            }
            
            const maxValue = Math.max(...entries.map(([k, v]) => v || 0));
            
            container.innerHTML = entries.map(([label, value]) => {
                const percentage = maxValue > 0 ? (value / maxValue) * 100 : 0;
                let displayValue = value;
                
                // Format heures si unitLabel = 'h'
                if (unitLabel === 'h') {
                    displayValue = formatHoursToHM(value);
                }
                
                return `
                    <div class="bar-item">
                        <span class="bar-label">${label}</span>
                        <div class="bar-container">
                            <div class="bar-fill" style="width: ${percentage}%">
                                ${displayValue}
                            </div>
                        </div>
                    </div>
                `;
            }).join('');
        }


		
		async function saveDataToDB() {
            try {
                const payload = {
                    activities: activities,
                    coordActivities: coordActivities
                };
                const res = await fetch(API_URL, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                const data = await res.json();
                if (data.status === 'success') {
                    console.log('💾 Données sauvegardées dans MariaDB');
					updateStats();
					updateCoordStatistics();
                } else {
                    console.log('⚠️ Erreur API save:', data.message);
                }
            } catch (e) {
                console.log('⚠️ Impossible de sauvegarder dans MariaDB', e);
            }
        }

        function toggleActivityForm() {
            const form = document.getElementById('activityForm');
            const btn = document.getElementById('toggleActivityBtn');
            if (form.style.display === 'none') {
                form.style.display = 'block';
                btn.textContent = 'Masquer';
            } else {
                form.style.display = 'none';
                btn.textContent = 'Afficher';
            }
        }
        function toggleCoordForm() {
            const form = document.getElementById('coordForm');
            const btn  = document.getElementById('toggleCoordFormBtn');

            if (form.style.display === 'none' || form.style.display === '') {
                form.style.display = 'block';
                btn.textContent = 'Masquer';
            } else {
                form.style.display = 'none';
                btn.textContent = 'Afficher';
            }
        }

        // Charger les données au démarrage
        loadDataFromDB();


    </script>
    <footer class="site-footer">
        <div class="container">
            © AECS 2025-2026
        </div>
    </footer>
</body>
</html>