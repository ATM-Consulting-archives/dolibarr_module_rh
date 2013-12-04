ALTER TABLE llx_agefodd_place MODIFY adresse varchar(255);
ALTER TABLE llx_agefodd_place MODIFY cp varchar(10);
ALTER TABLE llx_agefodd_place MODIFY ville varchar(50);
ALTER TABLE llx_agefodd_place MODIFY fk_pays integer;
ALTER TABLE llx_agefodd_place MODIFY tel varchar(20);
ALTER TABLE llx_agefodd_place MODIFY notes text;
ALTER TABLE llx_agefodd_place MODIFY fk_reg_interieur integer NULL;
ALTER TABLE llx_agefodd_facture MODIFY fk_commande integer;
ALTER TABLE llx_agefodd_facture MODIFY fk_facture integer;
ALTER TABLE llx_agefodd_convention MODIFY sig text;





