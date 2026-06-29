-- Client portal: sub-user admin flag and per-project assignments
-- Run once against tasktechfod_task (or your application database).

-- Primary client contact can manage sub-users in the portal
ALTER TABLE `users`
  ADD COLUMN `is_client_admin` TINYINT(1) NOT NULL DEFAULT 0 AFTER `client_id`;

-- Per-project access for client portal sub-users
CREATE TABLE IF NOT EXISTS `client_user_projects` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL,
  `project_id` INT UNSIGNED NOT NULL,
  `permission` ENUM('view','tickets','manage') NOT NULL DEFAULT 'view',
  `assigned_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `assigned_by` INT UNSIGNED NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_client_user_project` (`user_id`, `project_id`),
  KEY `idx_cup_project` (`project_id`),
  KEY `idx_cup_user` (`user_id`),
  CONSTRAINT `fk_cup_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_cup_project` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Mark the earliest portal user per client as the client admin (backward compatible)
UPDATE `users` u
INNER JOIN (
  SELECT MIN(`id`) AS `id`
  FROM `users`
  WHERE `client_id` IS NOT NULL
  GROUP BY `client_id`
) first_user ON u.`id` = first_user.`id`
SET u.`is_client_admin` = 1;

-- Grant existing client admins access to all current projects for their company
INSERT IGNORE INTO `client_user_projects` (`user_id`, `project_id`, `permission`, `assigned_by`)
SELECT u.`id`, p.`id`, 'manage', NULL
FROM `users` u
INNER JOIN `projects` p ON p.`client_id` = u.`client_id`
WHERE u.`is_client_admin` = 1 AND u.`client_id` IS NOT NULL;
