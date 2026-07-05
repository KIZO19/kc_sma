-- Enforce uniqueness for student matricules and payment receipt references.
CREATE UNIQUE INDEX IF NOT EXISTS idx_eleves_matricule_unique ON eleves (matricule);
CREATE UNIQUE INDEX IF NOT EXISTS idx_ecritures_reference_recu_unique ON ecritures_comptables_eleves (reference_recu);
