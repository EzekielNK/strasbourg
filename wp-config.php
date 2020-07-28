<?php
/**
 * La configuration de base de votre installation WordPress.
 *
 * Ce fichier contient les réglages de configuration suivants : réglages MySQL,
 * préfixe de table, clés secrètes, langue utilisée, et ABSPATH.
 * Vous pouvez en savoir plus à leur sujet en allant sur
 * {@link http://codex.wordpress.org/fr:Modifier_wp-config.php Modifier
 * wp-config.php}. C’est votre hébergeur qui doit vous donner vos
 * codes MySQL.
 *
 * Ce fichier est utilisé par le script de création de wp-config.php pendant
 * le processus d’installation. Vous n’avez pas à utiliser le site web, vous
 * pouvez simplement renommer ce fichier en "wp-config.php" et remplir les
 * valeurs.
 *
 * @package WordPress
 */

// ** Réglages MySQL - Votre hébergeur doit vous fournir ces informations. ** //
/** Nom de la base de données de WordPress. */
define('DB_NAME', 'db777807159');

/** Utilisateur de la base de données MySQL. */
define('DB_USER', 'dbo777807159');

/** Mot de passe de la base de données MySQL. */
define('DB_PASSWORD', 'Ounefille1!');

/** Adresse de l’hébergement MySQL. */
define('DB_HOST', 'db777807159.hosting-data.io');

/** Jeu de caractères à utiliser par la base de données lors de la création des tables. */
define('DB_CHARSET', 'utf8');

/** Type de collation de la base de données.
  * N’y touchez que si vous savez ce que vous faites.
  */
define('DB_COLLATE', '');

/**#@+
 * Clés uniques d’authentification et salage.
 *
 * Remplacez les valeurs par défaut par des phrases uniques !
 * Vous pouvez générer des phrases aléatoires en utilisant
 * {@link https://api.wordpress.org/secret-key/1.1/salt/ le service de clefs secrètes de WordPress.org}.
 * Vous pouvez modifier ces phrases à n’importe quel moment, afin d’invalider tous les cookies existants.
 * Cela forcera également tous les utilisateurs à se reconnecter.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         'b>z<T,k>grQK=mm;b4C7AQEHO*q<7?6D5>9]R%&4yzuk73r,Dw}g!:$Wuz{/]FdJ');
define('SECURE_AUTH_KEY',  'c5J@P-)+{$SFP4f1[b7V*y _YM8&;m# YL?6TEV]/OXDN>oUOQU%3r,.r4jcpB??');
define('LOGGED_IN_KEY',    '$@=)mf%_9yl$u3O:W5.:+P_wJI4IIi>X1E1tn9|N3:V(!BsifmHZ.wmM-CO4m&T^');
define('NONCE_KEY',        'ZU~-;%rR*z]:D<b0U~x(,+|bZ3rA+/6;IO.no8||y(Sxw,:(~98;Y09A.j_-koLX');
define('AUTH_SALT',        'x3p`ryrQ#F#5i4cWYR?vZB`OuMO3X:l_o +&P$DtJQuy]oR#Ya$w%CoG#IEp4jNn');
define('SECURE_AUTH_SALT', '4/do~zb90|G0&{E9{VZ=+s8b^t7n2RkQFE/jT,_e&4,Wuw5n1ou*(JavBHA7<<{-');
define('LOGGED_IN_SALT',   'x|;/kibEdh>J2TvQ^z|J^|3GR++n^SrJ|,wg./!^1-7LN{Xf<emP;exfVv,gx:HW');
define('NONCE_SALT',       '+DQ #K&Cd%=RT|95vTmnma|&FeF# bBJXn-L>7bcn#+Gy:hGR}jyvNgfbuIEf$6W');
/**#@-*/

/**
 * Préfixe de base de données pour les tables de WordPress.
 *
 * Vous pouvez installer plusieurs WordPress sur une seule base de données
 * si vous leur donnez chacune un préfixe unique.
 * N’utilisez que des chiffres, des lettres non-accentuées, et des caractères soulignés !
 */
$table_prefix = 'wp_';

/**
 * Pour les développeurs : le mode déboguage de WordPress.
 *
 * En passant la valeur suivante à "true", vous activez l’affichage des
 * notifications d’erreurs pendant vos essais.
 * Il est fortemment recommandé que les développeurs d’extensions et
 * de thèmes se servent de WP_DEBUG dans leur environnement de
 * développement.
 *
 * Pour plus d’information sur les autres constantes qui peuvent être utilisées
 * pour le déboguage, rendez-vous sur le Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define('WP_DEBUG', false);

/* C’est tout, ne touchez pas à ce qui suit ! Bonne publication. */

/** Chemin absolu vers le dossier de WordPress. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Réglage des variables de WordPress et de ses fichiers inclus. */
require_once(ABSPATH . 'wp-settings.php');
