-- 2026-10-23 The four main principals and their brands kept together on every item:
--   Stanely Black And Decker - brand STANLEY, Taparia Tools Ltd - brand TAPARIA,
--   Chicago Pneumatics - brand CP, Shree Prayag Air Controls Pvt Ltd - brand Shree Prayag (created when missing).
-- An item with one of these principals gets the brand, an item with one of these brands gets the principal -
-- where the other one is empty (0) or Not Applicable (1). Items that already have a different real principal
-- or brand are listed at the end and NOT changed. Run once in phpMyAdmin on the bie database. Safe to run again.
-- 2026-10-23 second version: principals found by name as well as code (live codes are STANELY / TAPARIA, the first
-- version looked for STAN / TAPA and skipped both); Chicago uses the existing brand CP, the CHICAGO PNEUMATIC brand
-- the first version created is removed when no item uses it; a pair that cannot be found is shown, not skipped.

-- 1. Shree Prayag brand (created only when missing)
INSERT INTO mst_brand (brand_name, brand_code, brand_status)
SELECT 'SHREE PRAYAG', 'SPAC', 1 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM mst_brand WHERE brand_status = 1 AND (UPPER(brand_name) LIKE '%PRAYAG%' OR UPPER(brand_code) = 'SPAC'));

-- 2. the pairs - a plain helper table (a temporary one cannot be read twice in one query on every MariaDB version)
DROP TABLE IF EXISTS tmp_pb;
CREATE TABLE tmp_pb (company VARCHAR(30), principal_id INT UNSIGNED, brand_id INT UNSIGNED) ENGINE=InnoDB;
INSERT INTO tmp_pb
SELECT 'Stanley',
       (SELECT MIN(principal_id) FROM mst_principal WHERE principal_status = 1 AND (UPPER(principal_code) IN ('STAN', 'STANELY', 'STANLEY') OR UPPER(principal_name) LIKE 'STAN%BLACK%')),
       (SELECT MIN(brand_id) FROM mst_brand WHERE brand_status = 1 AND UPPER(brand_name) = 'STANLEY')
UNION ALL
SELECT 'Taparia',
       (SELECT MIN(principal_id) FROM mst_principal WHERE principal_status = 1 AND (UPPER(principal_code) IN ('TAPA', 'TAPARIA') OR UPPER(principal_name) LIKE 'TAPARIA%')),
       (SELECT MIN(brand_id) FROM mst_brand WHERE brand_status = 1 AND UPPER(brand_name) LIKE 'TAPARIA%')
UNION ALL
SELECT 'Chicago Pneumatic',
       (SELECT MIN(principal_id) FROM mst_principal WHERE principal_status = 1 AND (UPPER(principal_code) = 'CP' OR UPPER(principal_name) LIKE 'CHICAGO%')),
       (SELECT MIN(brand_id) FROM mst_brand WHERE brand_status = 1 AND (UPPER(brand_name) = 'CP' OR UPPER(brand_name) LIKE 'CHICAGO%'))
UNION ALL
SELECT 'Shree Prayag',
       (SELECT MIN(principal_id) FROM mst_principal WHERE principal_status = 1 AND (UPPER(principal_code) = 'SPAC' OR UPPER(principal_name) LIKE '%PRAYAG%')),
       (SELECT MIN(brand_id) FROM mst_brand WHERE brand_status = 1 AND (UPPER(brand_name) LIKE '%PRAYAG%' OR UPPER(brand_code) = 'SPAC'));

-- check: the four pairs - a blank principal or brand here means it was not found and that company is not synced
SELECT t.company, p.principal_name, b.brand_name FROM tmp_pb t LEFT JOIN mst_principal p ON p.principal_id = t.principal_id LEFT JOIN mst_brand b ON b.brand_id = t.brand_id;

-- 3. the duplicate CHICAGO PNEUMATIC brand made by the first version - removed when a CP brand exists and no item uses it
UPDATE mst_brand x
SET x.brand_status = 0
WHERE x.brand_status = 1 AND UPPER(x.brand_name) = 'CHICAGO PNEUMATIC'
  AND x.brand_id NOT IN (SELECT brand_id FROM tmp_pb WHERE brand_id IS NOT NULL)
  AND NOT EXISTS (SELECT 1 FROM tbl_item_details d WHERE d.item_brand_make = x.brand_id);

DELETE FROM tmp_pb WHERE principal_id IS NULL OR brand_id IS NULL;

-- 4. principal set, brand empty -> brand
UPDATE tbl_item_details d JOIN tmp_pb t ON t.principal_id = d.item_principal
SET d.item_brand_make = t.brand_id
WHERE d.item_status = 1 AND d.item_brand_make IN (0, 1);

-- 5. brand set, principal empty -> principal
UPDATE tbl_item_details d JOIN tmp_pb t ON t.brand_id = d.item_brand_make
SET d.item_principal = t.principal_id
WHERE d.item_status = 1 AND d.item_principal IN (0, 1);

-- 6. check: items per pair now, and the ones left as they are because they have a different real principal / brand
SELECT t.company, p.principal_name, b.brand_name, COUNT(d.item_id) AS items_matched
FROM tmp_pb t JOIN mst_principal p ON p.principal_id = t.principal_id JOIN mst_brand b ON b.brand_id = t.brand_id
LEFT JOIN tbl_item_details d ON d.item_status = 1 AND d.item_principal = t.principal_id AND d.item_brand_make = t.brand_id
GROUP BY t.company, p.principal_name, b.brand_name;

SELECT d.item_id, d.item_code, d.item_desciption, p.principal_name, b.brand_name, 'other principal / brand - not changed' AS note
FROM tbl_item_details d
LEFT JOIN mst_principal p ON p.principal_id = d.item_principal
LEFT JOIN mst_brand b ON b.brand_id = d.item_brand_make
WHERE d.item_status = 1
  AND ((d.item_principal IN (SELECT principal_id FROM tmp_pb) AND d.item_brand_make NOT IN (0, 1) AND d.item_brand_make <> (SELECT brand_id FROM tmp_pb x WHERE x.principal_id = d.item_principal))
    OR (d.item_brand_make IN (SELECT brand_id FROM tmp_pb) AND d.item_principal NOT IN (0, 1) AND d.item_principal <> (SELECT principal_id FROM tmp_pb x WHERE x.brand_id = d.item_brand_make)))
ORDER BY p.principal_name, b.brand_name, d.item_code;

DROP TABLE IF EXISTS tmp_pb;
