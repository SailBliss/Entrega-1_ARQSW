-- Isabela Ruiz, Nicolas Ortiz
-- Datos ficticios de Sync. Ejecute las migraciones antes de importar este archivo.

SET NAMES utf8mb4;
START TRANSACTION;

INSERT INTO users (id, name, email, email_verified_at, password, is_admin, remember_token, created_at, updated_at) VALUES
    (1, 'Usuario Demo', 'demo@example.com', NULL, '$2y$12$//9ktYCKkuqPKBVOqcWic.6oROmWgBFA1EUlu/SXh2XvOX93OVWdO', 0, NULL, NOW(), NOW()),
    (2, 'Administrador', 'admin@example.com', NULL, '$2y$12$//9ktYCKkuqPKBVOqcWic.6oROmWgBFA1EUlu/SXh2XvOX93OVWdO', 1, NULL, NOW(), NOW())
ON DUPLICATE KEY UPDATE
    name = VALUES(name), password = VALUES(password), is_admin = VALUES(is_admin), updated_at = NOW();

INSERT INTO watches (id, name, brand, description, price, stock, image, created_at, updated_at) VALUES
    (1, 'Casio F-91W', 'Casio', 'Clásico digital con cronómetro, alarma diaria y correa de resina.', 95000, 25, 'img/watches/casio-f91w.svg', NOW(), NOW()),
    (2, 'Casio G-Shock GA-2100', 'Casio', 'Caja octagonal "CasiOak", resistente a golpes y a 200 m de agua.', 420000, 12, 'img/watches/casio-gshock-ga2100.svg', NOW(), NOW()),
    (3, 'Seiko 5 SNK809', 'Seiko', 'Automático con esfera azul, calendario día/fecha y correa de lona.', 650000, 8, 'img/watches/seiko-5-snk809.svg', NOW(), NOW()),
    (4, 'Seiko Presage Cocktail', 'Seiko', 'Automático de vestir con esfera degradé y cristal de zafiro.', 1800000, 5, 'img/watches/seiko-presage-cocktail.svg', NOW(), NOW()),
    (5, 'Citizen Eco-Drive BM7100', 'Citizen', 'Carga solar, sin baterías, con correa de acero inoxidable.', 980000, 9, 'img/watches/citizen-ecodrive-bm7100.svg', NOW(), NOW()),
    (6, 'Citizen Promaster Diver', 'Citizen', 'Reloj de buceo Eco-Drive, hermético hasta 200 metros.', 1500000, 6, 'img/watches/citizen-promaster-diver.svg', NOW(), NOW()),
    (7, 'Orient Bambino V2', 'Orient', 'Automático elegante con cristal abovedado y esfera champán.', 720000, 10, 'img/watches/orient-bambino-v2.svg', NOW(), NOW()),
    (8, 'Orient Kamasu', 'Orient', 'Diver automático con bisel de cerámica y 200 m de resistencia.', 890000, 7, 'img/watches/orient-kamasu.svg', NOW(), NOW()),
    (9, 'Tissot PRX Powermatic 80', 'Tissot', 'Estilo años 70, automático con 80 horas de reserva de marcha.', 2900000, 4, 'img/watches/tissot-prx-powermatic80.svg', NOW(), NOW()),
    (10, 'Tissot Gentleman', 'Tissot', 'Caja de acero con zafiro, ideal para uso diario y formal.', 3400000, 3, 'img/watches/tissot-gentleman.svg', NOW(), NOW()),
    (11, 'Timex Weekender', 'Timex', 'Analógico casual con correa NATO intercambiable e Indiglo.', 280000, 20, 'img/watches/timex-weekender.svg', NOW(), NOW()),
    (12, 'Fossil Grant Chrono', 'Fossil', 'Cronógrafo de cuarzo con correa de cuero y estilo vintage.', 540000, 11, 'img/watches/fossil-grant-chrono.svg', NOW(), NOW())
ON DUPLICATE KEY UPDATE
    name = VALUES(name), brand = VALUES(brand), description = VALUES(description), price = VALUES(price),
    stock = VALUES(stock), image = VALUES(image), updated_at = NOW();

COMMIT;
