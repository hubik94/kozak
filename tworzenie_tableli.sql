CREATE TABLE `losowania` (
`id`  bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT ,
`wpis`  int(10) UNSIGNED NOT NULL ,
`losowanie_od`  datetime NOT NULL ,
`losowanie_do`  datetime NOT NULL ,
`zwyciezca`  text CHARACTER SET utf8 COLLATE utf8_polish_ci NULL ,
PRIMARY KEY (`id`),
UNIQUE INDEX `unikat` (`wpis`, `losowanie_od`, `losowanie_do`) USING BTREE ,
INDEX `wpisy` (`wpis`) USING BTREE 
)
ENGINE=MyISAM
DEFAULT CHARACTER SET=utf8 COLLATE=utf8_polish_ci
AUTO_INCREMENT=15
CHECKSUM=0
ROW_FORMAT=DYNAMIC
DELAY_KEY_WRITE=0
;
