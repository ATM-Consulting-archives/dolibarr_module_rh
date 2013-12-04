-- ============================================================================
-- Copyright (C) 2013		Florian Henry	<florian.henry@open-concept.pro>
--
-- This program is free software; you can redistribute it and/or modify
-- it under the terms of the GNU General Public License as published by
-- the Free Software Foundation; either version 3 of the License, or
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
-- Structure de la table llx_agefodd_stagiaire_certif
--

CREATE TABLE IF NOT EXISTS llx_agefodd_stagiaire_certif (
  rowid integer NOT NULL auto_increment PRIMARY KEY,
  entity integer NOT NULL DEFAULT 1,
  fk_user_author integer default NULL,
  fk_user_mod integer NOT NULL,
  datec datetime NOT NULL,
  tms timestamp NOT NULL,
  fk_stagiaire integer NOT NULL,
  fk_session_agefodd integer NOT NULL,
  fk_session_stagiaire integer NOT NULL,
  certif_code varchar(200) NOT NULL,
  certif_label varchar(200) NOT NULL,
  certif_dt_start datetime NOT NULL,
  certif_dt_end datetime NOT NULL,
  certif_dt_warning datetime DEFAULT NULL,
  import_key		varchar(14)
) ENGINE=InnoDB;

