SELECT
	geb.id AS gebaeude_id, 
	per.person_id,
	per.rolle_text AS rolle
FROM gv_gebaeude.csv AS geb INNER JOIN gv_person.csv AS per 
	ON geb.id = per.gebaeude_id;
