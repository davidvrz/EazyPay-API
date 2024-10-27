<?php

class PDOConnection {
	private static $host = "localhost";
	private static $dbname = "eazypay";
	private static $dbuser = "eazypay";
	private static $dbpass = "eazypaypebb";
	private static $db_singleton = null;

	public static function getInstance() {
		if (self::$db_singleton == null) {
			self::$db_singleton = new PDO(
			"mysql:host=".self::$host.";dbname=".self::$dbname.";charset=utf8mb4", // connection string
			self::$dbuser,
			self::$dbpass,
			array( // options
				PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
				PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
			)
		);
	}
	return self::$db_singleton;
}
}

?>
