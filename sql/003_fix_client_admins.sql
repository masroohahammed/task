-- Ensure primary client contacts are flagged as portal admins.
-- Matches portal users whose email equals the client company email,
-- and sets admin on the earliest portal user per client when none exists.

UPDATE `users` u
INNER JOIN `clients` c ON c.id = u.client_id
INNER JOIN `roles` r ON r.id = u.role_id AND r.slug = 'client'
SET u.is_client_admin = 1
WHERE LOWER(TRIM(u.email)) = LOWER(TRIM(c.email));

UPDATE `users` u
INNER JOIN (
  SELECT MIN(u2.id) AS id
  FROM `users` u2
  INNER JOIN `roles` r2 ON r2.id = u2.role_id AND r2.slug = 'client'
  WHERE u2.client_id IS NOT NULL
    AND NOT EXISTS (
      SELECT 1 FROM `users` ua
      INNER JOIN `roles` ra ON ra.id = ua.role_id AND ra.slug = 'client'
      WHERE ua.client_id = u2.client_id AND ua.is_client_admin = 1
    )
  GROUP BY u2.client_id
) pick ON pick.id = u.id
SET u.is_client_admin = 1;
