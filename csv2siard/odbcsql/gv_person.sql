SELECT DISTINCT
	person_id,
	name,
	TRIM(gvp.strasse & ' ' & gvp.strasse_nr) AS strasse,
	land,
	plz,
	ort
FROM gv_person.csv AS gvp;
