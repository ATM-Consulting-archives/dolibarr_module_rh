-- ============================================================================
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
CREATE TABLE IF NOT EXISTS llx_agefodd_opca
(
	rowid integer NOT NULL auto_increment PRIMARY KEY,
	fk_soc_trainee	integer	NOT NULL,
	fk_session_agefodd integer NOT NULL,
	date_ask_OPCA datetime DEFAULT NULL,
	is_date_ask_OPCA smallint NOT NULL DEFAULT 0,
	is_OPCA smallint NOT NULL DEFAULT 0,
	fk_soc_OPCA integer DEFAULT NULL,
	fk_socpeople_OPCA integer DEFAULT NULL,
	num_OPCA_soc varchar(100) DEFAULT NULL,
	num_OPCA_file varchar(100) DEFAULT NULL,
	fk_user_author	integer	NOT NULL,
	datec	datetime  NOT NULL,
	fk_user_mod integer NOT NULL,
	tms timestamp NOT NULL
) ENGINE=InnoDB;
