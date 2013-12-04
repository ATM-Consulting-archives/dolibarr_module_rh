--for pgsql
UPDATE llx_agefodd_stagiaire as st SET nom=sp.name, prenom=sp.firstname, civilite=sp.civilite, fk_soc=sp.fk_soc, tel1=sp.phone, 
tel2=sp.phone_perso, mail=sp.email FROM llx_socpeople as sp WHERE st.fk_socpeople=sp.rowid;

--for mysql
UPDATE llx_agefodd_stagiaire as st, llx_socpeople as sp SET nom=sp.name,
prenom=sp.firstname, civilite=sp.civilite, fk_soc=sp.fk_soc,tel1=sp.phone,
tel2=sp.phone_perso, mail=sp.email WHERE st.fk_socpeople=sp.rowid;

--repeat this update each time if install on Dolibarr 3.2
ALTER TABLE llx_actioncomm MODIFY elementtype VARCHAR(32);
