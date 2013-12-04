-- ============================================================================
-- Copyright (C) 2012 Florian Henry  <florian.henry@open-concept.pro>
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
-- along with this program. If not, see <http://www.gnu.org/licenses/>.
--
-- ============================================================================

CREATE TRIGGER update_customer_modtime BEFORE UPDATE ON llx_agefodd_contact FOR EACH ROW EXECUTE PROCEDURE update_modified_column();
CREATE TRIGGER update_customer_modtime BEFORE UPDATE ON llx_agefodd_convention FOR EACH ROW EXECUTE PROCEDURE update_modified_column();
CREATE TRIGGER update_customer_modtime BEFORE UPDATE ON llx_agefodd_formateur FOR EACH ROW EXECUTE PROCEDURE update_modified_column();
CREATE TRIGGER update_customer_modtime BEFORE UPDATE ON llx_agefodd_facture FOR EACH ROW EXECUTE PROCEDURE update_modified_column();
CREATE TRIGGER update_customer_modtime BEFORE UPDATE ON llx_agefodd_formation_catalogue FOR EACH ROW EXECUTE PROCEDURE update_modified_column();
CREATE TRIGGER update_customer_modtime BEFORE UPDATE ON llx_agefodd_formation_objectifs_peda FOR EACH ROW EXECUTE PROCEDURE update_modified_column();
CREATE TRIGGER update_customer_modtime BEFORE UPDATE ON llx_agefodd_opca FOR EACH ROW EXECUTE PROCEDURE update_modified_column();
CREATE TRIGGER update_customer_modtime BEFORE UPDATE ON llx_agefodd_place FOR EACH ROW EXECUTE PROCEDURE update_modified_column();
CREATE TRIGGER update_customer_modtime BEFORE UPDATE ON llx_agefodd_reg_interieur FOR EACH ROW EXECUTE PROCEDURE update_modified_column();
CREATE TRIGGER update_customer_modtime BEFORE UPDATE ON llx_agefodd_session_adminsitu FOR EACH ROW EXECUTE PROCEDURE update_modified_column();
CREATE TRIGGER update_customer_modtime BEFORE UPDATE ON llx_agefodd_session_admlevel FOR EACH ROW EXECUTE PROCEDURE update_modified_column();
CREATE TRIGGER update_customer_modtime BEFORE UPDATE ON llx_agefodd_session_calendrier FOR EACH ROW EXECUTE PROCEDURE update_modified_column();
CREATE TRIGGER update_customer_modtime BEFORE UPDATE ON llx_agefodd_session_commercial FOR EACH ROW EXECUTE PROCEDURE update_modified_column();
CREATE TRIGGER update_customer_modtime BEFORE UPDATE ON llx_agefodd_session_contact FOR EACH ROW EXECUTE PROCEDURE update_modified_column();
CREATE TRIGGER update_customer_modtime BEFORE UPDATE ON llx_agefodd_session_formateur FOR EACH ROW EXECUTE PROCEDURE update_modified_column();
CREATE TRIGGER update_customer_modtime BEFORE UPDATE ON llx_agefodd_session_stagiaire FOR EACH ROW EXECUTE PROCEDURE update_modified_column();
CREATE TRIGGER update_customer_modtime BEFORE UPDATE ON llx_agefodd_session FOR EACH ROW EXECUTE PROCEDURE update_modified_column();
CREATE TRIGGER update_customer_modtime BEFORE UPDATE ON llx_agefodd_stagiaire_type FOR EACH ROW EXECUTE PROCEDURE update_modified_column();
CREATE TRIGGER update_customer_modtime BEFORE UPDATE ON llx_agefodd_stagiaire FOR EACH ROW EXECUTE PROCEDURE update_modified_column();
CREATE TRIGGER update_customer_modtime BEFORE UPDATE ON llx_agefodd_stagiaire_certif FOR EACH ROW EXECUTE PROCEDURE update_modified_column();
CREATE TRIGGER update_customer_modtime BEFORE UPDATE ON llx_agefodd_certif_state FOR EACH ROW EXECUTE PROCEDURE update_modified_column();
CREATE TRIGGER update_customer_modtime BEFORE UPDATE ON llx_agefodd_certificate_type FOR EACH ROW EXECUTE PROCEDURE update_modified_column();
CREATE TRIGGER update_customer_modtime BEFORE UPDATE ON llx_agefodd_training_admlevel FOR EACH ROW EXECUTE PROCEDURE update_modified_column();

