-- Normaliser les noms de projets existants:
-- 1) Supprimer les accents
-- 2) Mettre en majuscules
-- 3) Nettoyer les espaces de début/fin

UPDATE projects
SET nom_projet = UPPER(
    TRIM(
        REGEXP_REPLACE(
            REGEXP_REPLACE(
                REGEXP_REPLACE(
                    REGEXP_REPLACE(
                        REGEXP_REPLACE(
                            REGEXP_REPLACE(
                                REGEXP_REPLACE(
                                    REGEXP_REPLACE(nom_projet, '[àáâãäåÀÁÂÃÄÅ]', 'a'),
                                    '[èéêëÈÉÊË]', 'e'
                                ),
                                '[ìíîïÌÍÎÏ]', 'i'
                            ),
                            '[òóôõöøÒÓÔÕÖØ]', 'o'
                        ),
                        '[ùúûüÙÚÛÜ]', 'u'
                    ),
                    '[ýÿÝŸ]', 'y'
                ),
                '[çÇ]', 'c'
            ),
            '[ñÑ]', 'n'
        )
    )
)
WHERE nom_projet IS NOT NULL;

-- Optionnel: gérer les ligatures
UPDATE projects
SET nom_projet = REPLACE(REPLACE(REPLACE(REPLACE(nom_projet, 'œ', 'oe'), 'Œ', 'OE'), 'æ', 'ae'), 'Æ', 'AE')
WHERE nom_projet IS NOT NULL;
