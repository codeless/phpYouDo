DROP TABLE IF EXISTS lipsum;
CREATE TABLE lipsum (
	lorem	INTEGER PRIMARY KEY,
	ipsum	TEXT NOT NULL,
	dolor	NUMERIC NOT NULL,
	sit	INTEGER NOT NULL,
	amet	REAL
);

INSERT INTO lipsum (ipsum, dolor, sit, amet) VALUES
	("Donec malesuada", 10, 20, 30.1),
	("Ipsum in accumsan ornare", 20, 30, 40.2),
	("Nulla tortor adipiscing nunc", 30, 40, 50.3);
