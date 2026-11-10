INSERT INTO roles_administration (titre_role)
SELECT 'Comptable'
FROM DUAL
WHERE NOT EXISTS (
    SELECT 1
    FROM roles_administration
    WHERE LOWER(titre_role) = LOWER('Comptable')
);
