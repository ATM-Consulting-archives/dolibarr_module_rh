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

CREATE TABLE IF NOT EXISTS llx_agefodd_formation_catalogue (
  rowid integer NOT NULL auto_increment PRIMARY KEY,
  ref varchar(40) NOT NULL,
  ref_interne varchar(100) NULL,
  entity integer NOT NULL DEFAULT 1,
  intitule varchar(100) NOT NULL,
  duree integer NOT NULL DEFAULT 0,
  public text NULL,
  methode text NULL,
  prerequis text NULL,
  but text NULL,
  programme text NULL,
  note1 text NULL,
  note2 text NULL,
  archive smallint NOT NULL DEFAULT 0,
  fk_user_author integer NOT NULL,
  datec datetime NOT NULL,
  fk_user_mod integer NOT NULL,
  note_private	text,
  note_public	text,
  fk_product integer,
  nb_subscribe_min integer NULL,
  fk_c_category integer NULL,
  certif_duration varchar(30) NULL,
  tms timestamp NOT NULL
) ENGINE=InnoDB;
