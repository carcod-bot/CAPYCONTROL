-- Archivo para insertar únicamente el usuario administrador inicial
-- Usuario: capiadmin
-- Contraseña: admincapi123

INSERT INTO `users` (`username`, `password`, `role`, `permissions`, `dark_mode`, `created_at`, `updated_at`) VALUES
('capiadmin', '$2y$12$FKQHXoDJjkGKTEYnZvAibOXG6FKiHXuEBU4E2zLHcd3OIQ7AkdrZa', 'admin', '[]', 0, NOW(), NOW());
