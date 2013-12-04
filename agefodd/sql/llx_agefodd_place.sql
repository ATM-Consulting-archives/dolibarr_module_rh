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
-- Structure de la table llx_agefodd_place
--

CREATE TABLE IF NOT EXISTS llx_agefodd_place (
  rowid integer NOT NULL auto_increment PRIMARY KEY,
  ref_interne varchar(80) NOT NULL,
  adresse varchar(255),
  cp varchar(10),
  ville varchar(50),
  fk_pays integer,
  tel varchar(20),
  fk_societe integer NOT NULL,
  notes text,
  acces_site text,
  note1 text,
  archive smallint NOT NULL DEFAULT 0,
  fk_reg_interieur integer,
  fk_user_author integer NOT NULL,
  datec datetime NOT NULL,
  fk_user_mod integer NOT NULL,
  tms timestamp NOT NULL,
  entity integer NOT NULL DEFAULT 1
) ENGINE=InnoDB;

