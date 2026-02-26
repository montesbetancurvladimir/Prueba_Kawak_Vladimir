-- DML (semillas) - Prueba técnica KAWAK
USE kawak_docs;

INSERT INTO PRO_PROCESO (PRO_NOMBRE, PRO_PREFIJO) VALUES
('Ingeniería', 'ING'),
('Calidad', 'CAL'),
('Talento Humano', 'TH'),
('Compras', 'COM'),
('Operaciones', 'OPE')
ON DUPLICATE KEY UPDATE PRO_NOMBRE = VALUES(PRO_NOMBRE);

INSERT INTO TIP_TIPO_DOC (TIP_NOMBRE, TIP_PREFIJO) VALUES
('Instructivo', 'INS'),
('Procedimiento', 'PRO'),
('Política', 'POL'),
('Formato', 'FOR'),
('Manual', 'MAN')
ON DUPLICATE KEY UPDATE TIP_NOMBRE = VALUES(TIP_NOMBRE);

