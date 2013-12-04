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
--
-- Contraintes pour la table llx_agefodd_formation_cursus

ALTER TABLE llx_agefodd_stagiaire_cursus ADD CONSTRAINT llx_agefodd_stagiaire_cursus_ibfk_1 FOREIGN KEY (fk_cursus) REFERENCES llx_agefodd_cursus (rowid) ON DELETE CASCADE;
ALTER TABLE llx_agefodd_stagiaire_cursus ADD CONSTRAINT llx_agefodd_stagiaire_cursus_ibfk_2 FOREIGN KEY (fk_stagiaire) REFERENCES llx_agefodd_stagiaire (rowid) ON DELETE CASCADE;

