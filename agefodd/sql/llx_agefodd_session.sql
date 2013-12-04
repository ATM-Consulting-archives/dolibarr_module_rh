-- ============================================================================
-- Copyright (C) 2009-2010	Erick Bullier	<eb.dev@ebiconsulting.fr>
-- Copyright (C) 2010-2011	Regis Houssin	<regis@dolibarr.fr>
-- Copyright (C) 2012		Florian Henry	<florian.henry@open-concept.pro>
--
-- This program is free software; you can redistribute it and/or modify
-- it under the terms of the GNU General Public License as published by
-- the Free Software Foundation; either version 2 of the License, or
-- (at your option) any later version.
--
-- This program is distributed in the hope that it will be useful,
-- but WITHOUT ANY WARRANTY; without even the implied warranty of
-- MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
-- GNU General Public License for more details.
--
-- You should have received a copy of the GNU General Public License
-- along with this program; if not, write to the Free Software
-- Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.
--
-- ============================================================================
--
-- Structure de la table llx_agefodd_session
--

CREATE TABLE IF NOT EXISTS llx_agefodd_session (
  rowid integer NOT NULL auto_increment PRIMARY KEY,
  entity integer NOT NULL DEFAULT 1,
  fk_soc int NULL,
  fk_formation_catalogue integer NOT NULL,
  fk_session_place integer NOT NULL,
  type_session integer NULL,
  nb_place integer NULL,
  nb_stagiaire integer NULL,
  force_nb_stagiaire integer NULL,
  nb_subscribe_min integer NULL,
  fk_product integer NULL,
  dated datetime default NULL,
  datef datetime default NULL,
  notes text NOT NULL,
  color varchar(32) NULL,
  cost_trainer double(24,8) DEFAULT 0,         
  cost_site double(24,8) DEFAULT 0,
  cost_trip double(24,8) NULL,    
  sell_price double(24,8) DEFAULT 0, 
  is_date_res_site smallint NOT NULL DEFAULT 0,
  date_res_site datetime DEFAULT NULL,
  is_date_res_trainer smallint NOT NULL DEFAULT 0,
  date_res_trainer datetime DEFAULT NULL,
  date_ask_OPCA datetime DEFAULT NULL,
  is_date_ask_OPCA smallint NOT NULL DEFAULT 0,
  is_OPCA smallint NOT NULL DEFAULT 0,
  fk_soc_OPCA integer DEFAULT NULL,
  fk_socpeople_OPCA integer DEFAULT NULL,
  num_OPCA_soc varchar(100) DEFAULT NULL,
  num_OPCA_file varchar(100) DEFAULT NULL,
  fk_user_author integer NOT NULL,
  datec datetime NOT NULL,
  fk_user_mod integer NOT NULL,
  tms timestamp NOT NULL,
  archive smallint NOT NULL DEFAULT 0,
  status integer DEFAULT NULL,
  duree_session integer NOT NULL DEFAULT 0,
  intitule_custo varchar(100) DEFAULT NULL,
  import_key varchar(36) DEFAULT NULL,
  ref_ext varchar(50) DEFAULT NULL
) ENGINE=InnoDB;
