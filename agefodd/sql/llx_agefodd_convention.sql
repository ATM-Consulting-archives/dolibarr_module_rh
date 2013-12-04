-- ============================================================================
-- Copyright (C) 2009-2010	Erick Bullier	<eb.dev@ebiconsulting.fr>
-- Copyright (C) 2010-2012	Regis Houssin	<regis@dolibarr.fr>
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

CREATE TABLE IF NOT EXISTS llx_agefodd_convention (
  rowid integer NOT NULL auto_increment PRIMARY KEY,
  fk_agefodd_session integer NOT NULL,
  fk_societe integer NOT NULL,
  intro1 text NOT NULL,
  intro2 text NOT NULL,
  art1 text NOT NULL,
  art2 text NOT NULL,
  art3 text NOT NULL,
  art4 text NOT NULL,
  art5 text NOT NULL,
  art6 text NOT NULL,
  art7 text NOT NULL,
  art8 text NOT NULL,
  sig text,
  notes text NOT NULL,
  fk_user_author integer NOT NULL,
  datec datetime NOT NULL,
  fk_user_mod integer NOT NULL,
  tms timestamp NOT NULL
) ENGINE=InnoDB;
