-- ============================================================================
-- Copyright (C) 2013		Florian Henry	<florian.henry@open-concept.pro>
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

CREATE TABLE IF NOT EXISTS llx_agefodd_cursus (
  rowid integer NOT NULL auto_increment PRIMARY KEY,
  ref_interne varchar(80) NULL,
  entity integer NOT NULL DEFAULT 1,
  intitule varchar(80) NOT NULL,
  archive smallint NOT NULL DEFAULT 0,
  fk_user_author integer NOT NULL,
  datec datetime NOT NULL,
  fk_user_mod integer NOT NULL,
  note_private	text,
  note_public	text,
  tms timestamp NOT NULL
) ENGINE=InnoDB;
