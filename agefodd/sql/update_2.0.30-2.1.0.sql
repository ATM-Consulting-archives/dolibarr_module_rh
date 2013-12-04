--new admnlevel training table
CREATE TABLE IF NOT EXISTS llx_agefodd_training_admlevel (
  rowid integer NOT NULL auto_increment PRIMARY KEY,
  fk_agefodd_training_admlevel integer NOT NULL default '0',
  fk_training integer  NOT NULL,
  level_rank integer NOT NULL default 0,
  fk_parent_level integer default 0,
  indice integer NOT NULL,
  intitule varchar(150) NOT NULL,
  delais_alerte integer NOT NULL,
  fk_user_author integer NOT NULL,
  datec datetime NOT NULL,
  fk_user_mod integer NOT NULL,
  tms timestamp NOT NULL
) ENGINE=InnoDB;

ALTER TABLE llx_agefodd_training_admlevel ADD CONSTRAINT llx_agefodd_training_admlevel_ibfk_1 FOREIGN KEY (fk_training) REFERENCES llx_agefodd_formation_catalogue (rowid) ON DELETE CASCADE;
ALTER TABLE llx_agefodd_training_admlevel ADD INDEX fk_agefodd_training_admlevel (fk_training);

INSERT INTO llx_agefodd_training_admlevel(fk_agefodd_training_admlevel,fk_training,level_rank,fk_parent_level,indice,intitule,delais_alerte,fk_user_author,datec,fk_user_mod) SELECT DISTINCT seesadm.rowid,training.rowid, seesadm.level_rank, seesadm.fk_parent_level,seesadm.indice, seesadm.intitule,seesadm.delais_alerte,seesadm.fk_user_author,seesadm.datec,seesadm.fk_user_mod FROM llx_agefodd_session_admlevel as seesadm, llx_agefodd_formation_catalogue as training;

--pgsql
UPDATE llx_agefodd_training_admlevel as upd SET fk_parent_level=ori.rowid FROM llx_agefodd_training_admlevel as ori WHERE upd.fk_parent_level=ori.fk_agefodd_training_admlevel AND upd.level_rank<>0 AND upd.fk_training=ori.fk_training;

--MySQL
UPDATE llx_agefodd_training_admlevel as ori, llx_agefodd_training_admlevel as upd SET upd.fk_parent_level=ori.rowid WHERE upd.fk_parent_level=ori.fk_agefodd_training_admlevel AND upd.level_rank<>0 AND upd.fk_training=ori.fk_training;

ALTER TABLE llx_agefodd_stagiaire ADD COLUMN import_key	varchar(14);
ALTER TABLE llx_agefodd_stagiaire_certif ADD COLUMN import_key	varchar(14);
ALTER TABLE llx_agefodd_session_stagiaire ADD COLUMN import_key	varchar(14);
ALTER TABLE llx_agefodd_stagiaire ADD COLUMN date_birth datetime default NULL AFTER mail;
ALTER TABLE llx_agefodd_stagiaire ADD COLUMN place_birth  varchar(100) default NULL AFTER date_birth;

CREATE TABLE IF NOT EXISTS llx_agefodd_certificate_type (
  rowid integer NOT NULL auto_increment PRIMARY KEY,
  intitule varchar(80) NOT NULL,
  sort smallint NOT NULL,
  active integer NULL,
  tms timestamp NOT NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS llx_agefodd_certif_state (
  rowid integer NOT NULL auto_increment PRIMARY KEY,
  fk_user_author integer default NULL,
  fk_user_mod integer NOT NULL,
  datec datetime NOT NULL,
  tms timestamp NOT NULL,
  fk_certif integer NOT NULL,
  fk_certif_type integer NOT NULL,
  certif_state integer default NULL,
  import_key		varchar(14)
) ENGINE=InnoDB;

ALTER TABLE llx_agefodd_stagiaire_type MODIFY datec datetime;

ALTER TABLE llx_agefodd_formation_catalogue ADD COLUMN note_private text AFTER fk_user_mod;
ALTER TABLE llx_agefodd_formation_catalogue ADD COLUMN note_public  text AFTER note_private;
ALTER TABLE llx_agefodd_formation_catalogue ADD COLUMN fk_product integer AFTER note_public;

ALTER TABLE llx_agefodd_formation_catalogue ADD COLUMN nb_subscribe_min integer NULL AFTER fk_product;
ALTER TABLE llx_agefodd_session ADD COLUMN nb_subscribe_min integer NULL AFTER force_nb_stagiaire;

ALTER TABLE llx_agefodd_session_stagiaire ADD COLUMN status_in_session integer NULL AFTER fk_agefodd_stagiaire_type;

UPDATE llx_agefodd_session SET fk_soc=NULL where fk_soc=-1;
UPDATE llx_agefodd_session SET nb_stagiaire=(SELECT count(rowid) FROM llx_agefodd_session_stagiaire WHERE fk_session_agefodd = llx_agefodd_session.rowid) WHERE (llx_agefodd_session.force_nb_stagiaire=0 OR llx_agefodd_session.force_nb_stagiaire IS NULL);

DELETE FROM llx_actioncomm WHERE elementtype='agefodd_agsession' AND fk_element NOT IN (SELECT rowid FROM llx_agefodd_session);
