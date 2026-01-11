-- Agregar campo id_consultor_responsable a tbl_contratos
ALTER TABLE tbl_contratos
ADD COLUMN id_consultor_responsable INT(11) NULL AFTER email_responsable_sgsst,
ADD KEY fk_contracts_consultor (id_consultor_responsable);

-- Opcional: agregar foreign key si quieres integridad referencial
-- ALTER TABLE tbl_contratos
-- ADD CONSTRAINT fk_contracts_consultor
-- FOREIGN KEY (id_consultor_responsable) REFERENCES tbl_consultor(id_consultor)
-- ON DELETE SET NULL ON UPDATE CASCADE;
