-- 2026-10-23 The four main principals and their brands kept together on every item:
--   Stanely Black And Decker (STAN) - brand STANLEY, Taparia Tools Ltd (TAPA) - brand TAPARIA,
--   Chicago Pneumatics (CP) - brand CHICAGO PNEUMATIC, Shree Prayag Air Controls Pvt Ltd (SPAC) - brand SHREE PRAYAG (created here).
-- An item with one of these principals gets the brand, an item with one of these brands gets the principal -
-- only where the other one is empty (0) or Not Applicable (1). Items that already have a different real principal
-- or brand are listed at the end and NOT changed. Run once in phpMyAdmin on the bie database. Safe to run again.

-- 1. brands (created only when missing)
INSERT INTO mst_brand (brand_name, brand_code, brand_status)
SELECT 'SHREE PRAYAG', 'SPAC', 1 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM mst_brand WHERE brand_status = 1 AND (UPPER(brand_name) LIKE '%PRAYAG%' OR UPPER(brand_code) = 'SPAC'));
INSERT INTO mst_brand (brand_name, brand_code, brand_status)
SELECT 'CHICAGO PNEUMATIC', 'CP', 1 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM mst_brand WHERE brand_status = 1 AND UPPER(brand_name) LIKE 'CHICAGO%');
INSERT INTO mst_brand (brand_name, brand_code, brand_status)
SELECT 'STANLEY', 'STAN', 1 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM mst_brand WHERE brand_status = 1 AND UPPER(brand_name) = 'STANLEY');
INSERT INTO mst_brand (brand_name, brand_code, brand_status)
SELECT 'TAPARIA', 'TAPA', 1 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM mst_brand WHERE brand_status = 1 AND UPPER(brand_name) LIKE 'TAPARIA%');

-- 2. the pairs
-- a plain helper table (a temporary one cannot be read twice in one query on every MariaDB version), dropped at the end
DROP TABLE IF EXISTS tmp_pb;
CREATE TABLE tmp_pb (principal_id INT UNSIGNED, brand_id INT UNSIGNED) ENGINE=InnoDB;
INSERT INTO tmp_pb
SELECT (SELECT MIN(principal_id) FROM mst_principal WHERE principal_status = 1 AND principal_code = 'STAN'),
       (SELECT MIN(brand_id) FROM mst_brand WHERE brand_status = 1 AND UPPER(brand_name) = 'STANLEY')
UNION ALL
SELECT (SELECT MIN(principal_id) FROM mst_principal WHERE principal_status = 1 AND principal_code = 'TAPA'),
       (SELECT MIN(brand_id) FROM mst_brand WHERE brand_status = 1 AND UPPER(brand_name) LIKE 'TAPARIA%')
UNION ALL
SELECT (SELECT MIN(principal_id) FROM mst_principal WHERE principal_status = 1 AND principal_code = 'CP'),
       (SELECT MIN(brand_id) FROM mst_brand WHERE brand_status = 1 AND UPPER(brand_name) LIKE 'CHICAGO%')
UNION ALL
SELECT (SELECT MIN(principal_id) FROM mst_principal WHERE principal_status = 1 AND principal_code = 'SPAC'),
       (SELECT MIN(brand_id) FROM mst_brand WHERE brand_status = 1 AND (UPPER(brand_name) LIKE '%PRAYAG%' OR UPPER(brand_code) = 'SPAC'));
DELETE FROM tmp_pb WHERE principal_id IS NULL OR brand_id IS NULL;

-- check: the four pairs (principal - brand)
SELECT p.principal_name, p.principal_code, b.brand_name, b.brand_id FROM tmp_pb t JOIN mst_principal p ON p.principal_id = t.principal_id JOIN mst_brand b ON b.brand_id = t.brand_id;

-- 3. principal set, brand empty -> brand
UPDATE tbl_item_details d JOIN tmp_pb t ON t.principal_id = d.item_principal
SET d.item_brand_make = t.brand_id
WHERE d.item_status = 1 AND d.item_brand_make IN (0, 1);

-- 4. brand set, principal empty -> principal
UPDATE tbl_item_details d JOIN tmp_pb t ON t.brand_id = d.item_brand_make
SET d.item_principal = t.principal_id
WHERE d.item_status = 1 AND d.item_principal IN (0, 1);

-- 5. check: items per pair now, and the ones left as they are because they have a different real principal / brand
SELECT p.principal_name, b.brand_name, COUNT(d.item_id) AS items_matched
FROM tmp_pb t JOIN mst_principal p ON p.principal_id = t.principal_id JOIN mst_brand b ON b.brand_id = t.brand_id
LEFT JOIN tbl_item_details d ON d.item_status = 1 AND d.item_principal = t.principal_id AND d.item_brand_make = t.brand_id
GROUP BY t.principal_id, t.brand_id;

SELECT d.item_id, d.item_code, d.item_desciption, p.principal_name, b.brand_name, 'other principal / brand - not changed' AS note
FROM tbl_item_details d
LEFT JOIN mst_principal p ON p.principal_id = d.item_principal
LEFT JOIN mst_brand b ON b.brand_id = d.item_brand_make
WHERE d.item_status = 1
  AND ((d.item_principal IN (SELECT principal_id FROM tmp_pb) AND d.item_brand_make NOT IN (0, 1) AND d.item_brand_make <> (SELECT brand_id FROM tmp_pb x WHERE x.principal_id = d.item_principal))
    OR (d.item_brand_make IN (SELECT brand_id FROM tmp_pb) AND d.item_principal NOT IN (0, 1) AND d.item_principal <> (SELECT principal_id FROM tmp_pb x WHERE x.brand_id = d.item_brand_make)))
ORDER BY p.principal_name, b.brand_name, d.item_code;

DROP TABLE IF EXISTS tmp_pb;
