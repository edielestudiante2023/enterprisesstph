

INSERT INTO `client_policies` (`client_id`, `policy_type_id`, `policy_content`) VALUES
( 999, 1, ' '),
( 999, 4, ' '),
( 999, 5, ' '),
( 999, 6, ' '),
( 999, 7, ' '),
( 999, 8, ' '),
( 999, 9, ' '),
( 999, 10, ' '),
( 999, 11, ' '),
( 999, 12, ' '),
( 999, 13, ' '),
( 999, 14, ' '),
( 999, 15, ' '),
( 999, 16, ' '),
( 999, 17, ' '),
( 999, 18, ' '),
( 999, 19, ' '),
( 999, 20, ' '),
( 999, 21, ' '),
( 999, 22, ' '),
( 999, 23, ' '),
( 999, 24, ' '),
( 999, 25, ' '),
( 999, 26, ' '),
( 999, 27, ' '),
( 999, 28, ' '),
( 999, 29, ' '),
( 999, 30, ' '),
( 999, 31, ' '),
( 999, 32, ' '),
( 999, 33, ' '),
( 999, 34, ' '),
( 999, 35, ' '),
( 999, 36, ' '),
( 999, 37, ' '),
( 999, 38, ' '),
( 999, 39, ' '),
( 999, 40, ' '),
( 999, 41, ' '),
( 999, 42, ' '),
( 999, 43, ' '),
( 999, 44, ' '),
( 999, 45, ' '),
( 999, 46, ' ');

/* TIEMSTAMP

UPDATE `client_policies`
SET `created_at` = NOW(),
    `updated_at` = NOW()
WHERE `created_at` IS NULL OR `updated_at` IS NULL;

 2. Si necesitas actualizar los campos existentes que están en NULL
Para aquellos registros que ya tienen los valores NULL, puedes ejecutar una consulta para actualizarlos:

sql
Copiar código

UPDATE `client_policies`
SET `created_at` = NOW(),
    `updated_at` = NOW()
WHERE `created_at` IS NULL OR `updated_at` IS NULL;*/
