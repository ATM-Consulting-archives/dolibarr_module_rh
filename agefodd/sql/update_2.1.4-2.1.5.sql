ALTER TABLE llx_agefodd_session ADD COLUMN fk_product integer NULL AFTER nb_subscribe_min;
ALTER TABLE llx_agefodd_session ADD COLUMN status integer DEFAULT NULL AFTER archive;
ALTER TABLE llx_agefodd_session ADD COLUMN duree_session integer NOT NULL DEFAULT 0 AFTER status;
ALTER TABLE llx_agefodd_session ADD COLUMN intitule_custo varchar(80) DEFAULT NULL AFTER duree_session;

UPDATE llx_agefodd_session SET status=4 WHERE archive=1; 
UPDATE llx_agefodd_session SET status=2 WHERE status IS NULL; 

--pgsql
UPDATE llx_actioncomm as upd SET fk_soc=agsession.fk_soc FROM llx_agefodd_session as agsession WHERE upd.fk_element=agsession.rowid AND upd.elementtype='agefodd_agsession';

--MySQL
UPDATE llx_agefodd_session as agsession, llx_actioncomm as upd SET upd.fk_soc=agsession.fk_soc WHERE upd.fk_element=agsession.rowid AND upd.elementtype='agefodd_agsession';

