USE meditech;

-- 1) États
INSERT INTO etat (ETA_ID, ETA_LIB) VALUES
  ('CL', 'Saisie clôturée'),
  ('CR', 'Fiche créée, saisie en cours'),
  ('NV', 'Non validée'),
  ('VA', 'Validée et mise en paiement'),
  ('RB', 'Remboursée');

-- 2) Visiteurs
INSERT INTO visiteur (VIS_ID, VIS_NOM, VIS_PRENOM, VIS_ADRESSE, VIS_CP, VIS_VILLE, VIS_DATE_EMBAUCHE) VALUES
  ('CaVi', 'Caron',   'Victor',  '11 rue Luxembourg', '20243', 'Isolaccio',    '2023-01-10'),
  ('LaMa', 'Lamy',    'Maxime',  '92 rue Richelieu',  '14410', 'Valdallière', '2018-09-24');

-- 3) Utilisateurs (authentification)
INSERT INTO `USER` (id, login, password, dteConnexion, role) VALUES
  ('CaVi', 'Victorca', 'Iroise29', NULL, 'visiteur'),
  ('LaMa', 'IMANE',    'IMA*',     NULL, 'comptable');

-- 4) Forfaits
INSERT INTO frais_forfait (FOR_ID, FOR_LIB, FOR_MONTANT) VALUES
  ('ETP', 'Forfait Etape',       110),
  ('KM',  'Frais Kilométrique',    1),
  ('NUI', 'Nuitée Hôtel',         80),
  ('REP', 'Repas Restaurant',     25);

-- 5) Fiches de frais en saisie (CR)
INSERT INTO fiche_frais (VIS_ID, ETA_ID, FRR_ANNEE, FRR_MOIS, FRR_DATE_MODIF) VALUES
  ('CaVi', 'CR', '2025', 'JANVIER',  CURDATE()),
  ('LaMa', 'CR', '2025', 'JUIN',     CURDATE());

-- 6) Lignes de frais au forfait
INSERT INTO ligne_frais_forfait (FFR_ID, FOR_ID, LIG_QTE) VALUES
  (1, 'ETP', 2),
  (1, 'KM',  50),
  (1, 'NUI', 1),
  (1, 'REP', 3),
  (2, 'ETP', 1),
  (2, 'KM',  10),
  (2, 'NUI', 0),
  (2, 'REP', 1);

-- 7) Lignes de frais hors‐forfait
INSERT INTO ligne_frais_horsforfait (FFR_ID, DTE, LIBELLE, MONTANT, ETA_ID) VALUES
  (1, '2025-01-05', 'Taxi aéroport',   30.00, 'CR'),
  (1, '2025-01-20', 'Déjeuner client',  45.50, 'CR'),
  (2, '2025-06-10', 'Hôtel extra',     120.00, 'CR');

-- 8) Fiches déjà validées ou non validées pour tester validation.php
INSERT INTO fiche_frais (VIS_ID, ETA_ID, FRR_ANNEE, FRR_MOIS, FRR_DATE_MODIF, FRR_MONTANT_VALIDE, FRR_NB_JUSTIFICATIFS) VALUES
  ('CaVi', 'VA', '2024', 'AVRIL', '2024-04-15',  350.00, 4),
  ('LaMa', 'NV', '2024', 'MARS',  '2024-03-30',   75.00, 1);
