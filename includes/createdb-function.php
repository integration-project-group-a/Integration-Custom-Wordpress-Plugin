<?php
/**
 * Created by PhpStorm.
 * User: Fernando Andrade
 * Date: 4/16/2019
 * Time: 17:56
 * @version: 1.0
 *
 * Create new database tables
 *
 *
 */
//Versie van de database. handig bij updates, de versie nummer moet worden verhoogd.
global $visitor_db_version;
$visitor_db_version = '1.0';

//functie om databasetabel aanmaken
function visitordb_install () {
	global $wpdb;
	global $visitor_db_version;

	//prefix belangrijk om consistentie in de database te behouden.
	// Deze table wordt dus 'wp_visitordb'
	$table_name = $wpdb->prefix . "visitordb";

	//Vermijdt dat sommige characters worden omvromd naar '?'
	$charset_colalte = $wpdb->get_charset_collate();

	//sql statement om tabel aan te maken
	$sql = "CREATE TABLE $table_name (
		id int(10) NOT NULL AUTO_INCREMENT,
		uuid varchar(50),
		firstname varchar(30) NOT NULL,
		lastname varchar(30) NOT NULL,
		email varchar(50) NOT NULL,
		timestamp datetime DEFAULT '00000-00-00 00:00:00' NOT NULL,
		version int(10) NOT NULL,
		isActive boolean DEFAULT 0 NOT NULL,
		banned boolean DEFAULT 0 NOT NULL,
		birthdate date DEFAULT '1990-01-01',
		btw_nummer varchar(70) DEFAULT 'none', 
		gsm_nummer varchar(50) DEFAULT 'none', 
		sender varchar(30) DEFAULT 'Front-end',
		gdpr boolean DEFAULT 0,
		extra varchar(70),
		PRIMARY KEY (id)

	) $charset_collate;";
	//Vermijdt dat sommige characters worden omvromd naar '?'

	// sql statement wordt niet direct geëxecuted, maar die wordt gemigreerd dankzij onderstaande require_once en dbDelta
	require_once( ABSPATH . "wp-admin/includes/upgrade.php" );
	dbDelta( $sql );

	//De huidige versie wordt bewaard
	add_option( 'visitor_db_version', $visitor_db_version );


	//Gebruik onderstande functie wanneer er geupdated moet.
	//De aangepaste sql statement moet hier staan
	//Laatste functie 'visitordb_upgrade_check' moet ook uit commen tworden gehaald
/*
	$installed_version = get_option( "visitor_db_version" );
	if ($installed_version != $visitor_db_version) {
		$sql = "CREATE TABLE " . $table_name . "(
			id int(10) NOT NULL AUTO_INCREMENT,
			uuid varchar(50),
			firstname varchar(30) NOT NULL,
			lastname varchar(30) NOT NULL,
			email varchar(50) NOT NULL,
			timestamp datetime DEFAULT '00000-00-00 00:00:00' NOT NULL,
			version int(10) NOT NULL,
			isActive boolean DEFAULT 0 NOT NULL,
			banned boolean DEFAULT 0 NOT NULL,
			birthdate date DEFAULT '1990-01-01',
			btw-nummer varchar(70) DEFAULT 'none',
			gsm-nummer varchar(50) DEFAULT 'none',
			sender varchar(30) DEFAULT 'Front-end',
			gdpr boolean DEFAULT 0,
			extra varchar(100),
			PRIMARY KEY (id)
		);";

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);

		update_option('visitor_db_version', $visitor_db_version);
	};
*/
}


//functie om dummy data in de database te prompen, mag weg
function visitordb_install_data() {
	global $wpdb;
	$table_name = $wpdb->prefix . 'visitordb';

	$wpdb->insert($table_name, array(
		'firstname' => 'Fernando',
		'lastname' => 'Andrade',
		'email' => 'fernando.andrade@outlook.es',
		'timestamp' => current_time( 'mysql' ),
		'version' => '1'
	));
	$wpdb->insert($table_name, array(
		'firstname' => 'Jens',
		'lastname' => 'De Smeyter',
		'email' => 'jens.ds@outlook.es',
		'timestamp' => current_time( 'mysql' ),
		'version' => '1'
	));
}


//deze functie moet ook uit comment worden gehaald bij het updaten van de database
//Deze functie zorgt ervoor dat, er bij een update, de bovenstaande code om tabel te updaten uitgevoerd wordt
/*
function visitordb_upgrade_check() {
	global $visitor_db_version;
	if (get_site_option('visitor_db_version') != $visitor_db_version) {
		visitordb_install();
	}
}
add_action( 'plugins_loaded', 'visitordb_upgrade_check');
*/