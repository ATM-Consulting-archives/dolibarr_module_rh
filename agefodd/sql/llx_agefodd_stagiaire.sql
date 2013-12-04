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
-- Structure de la table llx_agefodd_stagiaire
--

CREATE TABLE IF NOT EXISTS llx_agefodd_stagiaire (
  rowid integer NOT NULL auto_increment PRIMARY KEY,
  entity integer NOT NULL DEFAULT 1,
  nom varchar(50) NOT NULL,
  prenom varchar(50) NOT NULL,
  civilite varchar(6) NOT NULL,
  fk_user_author integer default NULL,
  fk_user_mod integer NOT NULL,
  datec datetime NOT NULL,
  tms timestamp NOT NULL,
  fk_soc integer NOT NULL,
  fk_socpeople integer default NULL,
  fonction varchar(60) default NULL,
  tel1 varchar(30) default NULL,
  tel2 varchar(30) default NULL,
  mail varchar(100) default NULL,
  date_birth datetime default NULL,
  place_birth varchar(100) default NULL,
  note text,
  import_key varchar(14)
) ENGINE=InnoDB;

