ALTER TABLE llx_agefodd_formation_catalogue ADD COLUMN certif_duration varchar(30) NULL AFTER fk_c_category;
ALTER TABLE llx_agefodd_stagiaire_certif ADD COLUMN certif_dt_warning datetime DEFAULT NULL AFTER certif_dt_end;

--mysql
UPDATE llx_agefodd_stagiaire_certif SET certif_dt_warning=DATE_ADD(certif_dt_end,INTERVAL -6 MONTH) WHERE certif_dt_end IS NOT NULL;

--pgsql
UPDATE llx_agefodd_stagiaire_certif SET certif_dt_warning=(certif_dt_end  - INTERVAL '6 MONTHS') WHERE certif_dt_end IS NOT NULL;
