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

ALTER TABLE llx_agefodd_session_element ADD CONSTRAINT llx_agefodd_session_element_ibfk_1 FOREIGN KEY (fk_session_agefodd) REFERENCES llx_agefodd_session (rowid) ON DELETE CASCADE;
ALTER TABLE llx_agefodd_session_element ADD INDEX fk_session_element (fk_session_agefodd);
ALTER TABLE llx_agefodd_session_element ADD INDEX idxagefodd_session_element_fk_element (fk_element);
