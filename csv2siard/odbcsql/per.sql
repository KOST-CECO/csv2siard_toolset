SELECT DISTINCT
	person_id,
	name,
	TRIM(per.strasse & ' ' & per.strasse_nr) AS strasse,
	land,
	plz,
	ort
FROM gv_person.csv AS per;
