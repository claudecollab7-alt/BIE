-- 2026-10-23 OPTIONAL - run only if these items should belong to the brand's company.
-- After principal_brand_sync_231026.sql, about 45 items still have a STANLEY / TAPARIA / CP brand but another real
-- principal (Customize, MULTIBRAND, HONDA, YAMAHA - e.g. ST-HMSI "HMSI standardised Stanley kit" is under HONDA).
-- This sets their principal to the brand's company (Stanley / Taparia / Chicago Pneumatics / Shree Prayag), and the
-- brand of items under one of these principals with another real brand to the principal's brand.
-- The first SELECT lists exactly what will change. Safe to run again.

DROP TABLE IF EXISTS tmp_pb;
CREATE TABLE tmp_pb (principal_id INT UNSIGNED, brand_id INT UNSIGNED) ENGINE=InnoDB;
INSERT INTO tmp_pb
SELECT (SELECT MIN(principal_id) FROM mst_principal WHERE principal_status = 1 AND (UPPER(principal_code) IN ('STAN', 'STANELY', 'STANLEY') OR UPPER(principal_name) LIKE 'STAN%BLACK%')),
       (SELECT MIN(brand_id) FROM mst_brand WHERE brand_status = 1 AND UPPER(brand_name) = 'STANLEY')
UNION ALL
SELECT (SELECT MIN(principal_id) FROM mst_principal WHERE principal_status = 1 AND (UPPER(principal_code) IN ('TAPA', 'TAPARIA') OR UPPER(principal_name) LIKE 'TAPARIA%')),
       (SELECT MIN(brand_id) FROM mst_brand WHERE brand_status = 1 AND UPPER(brand_name) LIKE 'TAPARIA%')
UNION ALL
SELECT (SELECT MIN(principal_id) FROM mst_principal WHERE principal_status = 1 AND (UPPER(principal_code) = 'CP' OR UPPER(principal_name) LIKE 'CHICAGO%')),
       (SELECT MIN(brand_id) FROM mst_brand WHERE brand_status = 1 AND (UPPER(brand_name) = 'CP' OR UPPER(brand_name) LIKE 'CHICAGO%'))
UNION ALL
SELECT (SELECT MIN(principal_id) FROM mst_principal WHERE principal_status = 1 AND (UPPER(principal_code) = 'SPAC' OR UPPER(principal_name) LIKE '%PRAYAG%')),
       (SELECT MIN(brand_id) FROM mst_brand WHERE brand_status = 1 AND (UPPER(brand_name) LIKE '%PRAYAG%' OR UPPER(brand_code) = 'SPAC'));
DELETE FROM tmp_pb WHERE principal_id IS NULL OR brand_id IS NULL;

-- what will change
SELECT d.item_id, d.item_code, d.item_desciption, p.principal_name AS principal_now, b.brand_name AS brand_now,
       IF(t.brand_id IS NOT NULL, (SELECT principal_name FROM mst_principal WHERE principal_id = t.principal_id), p.principal_name) AS principal_new,
       IF(t.brand_id IS NULL, (SELECT brand_name FROM mst_brand WHERE brand_id = u.brand_id), b.brand_name) AS brand_new
FROM tbl_item_details d
LEFT JOIN mst_principal p ON p.principal_id = d.item_principal
LEFT JOIN mst_brand b ON b.brand_id = d.item_brand_make
LEFT JOIN tmp_pb t ON t.brand_id = d.item_brand_make
LEFT JOIN tmp_pb u ON u.principal_id = d.item_principal
WHERE d.item_status = 1
  AND ((t.brand_id IS NOT NULL AND d.item_principal NOT IN (0, 1) AND d.item_principal <> t.principal_id AND d.item_principal NOT IN (SELECT principal_id FROM tmp_pb))
    OR (u.principal_id IS NOT NULL AND d.item_brand_make NOT IN (0, 1) AND d.item_brand_make <> u.brand_id AND d.item_brand_make NOT IN (SELECT brand_id FROM tmp_pb)))
ORDER BY d.item_code;

-- brand is one of the four, principal another (not one of the four) -> the brand's principal
UPDATE tbl_item_details d JOIN tmp_pb t ON t.brand_id = d.item_brand_make
SET d.item_principal = t.principal_id
WHERE d.item_status = 1 AND d.item_principal NOT IN (0, 1) AND d.item_principal <> t.principal_id
  AND d.item_principal NOT IN (SELECT principal_id FROM tmp_pb);

-- principal is one of the four, brand another (not one of the four) -> the principal's brand
UPDATE tbl_item_details d JOIN tmp_pb t ON t.principal_id = d.item_principal
SET d.item_brand_make = t.brand_id
WHERE d.item_status = 1 AND d.item_brand_make NOT IN (0, 1) AND d.item_brand_make <> t.brand_id
  AND d.item_brand_make NOT IN (SELECT brand_id FROM tmp_pb);

DROP TABLE IF EXISTS tmp_pb;
