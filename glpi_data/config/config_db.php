<?php
class DB extends DBmysql {
   public $dbhost = 'mysql';
   public $dbuser = 'glpiuser';
   // The MySQL password is the literal value configured in docker-compose.
   // Do not URL-encode the '@' here; mysqli/PDO does not decode it.
   public $dbpassword = 'Ativ@Adm2026';
   public $dbdefault = 'glpidb';
   public $use_timezones = true;
   public $use_utf8mb4 = true;
   public $allow_datetime = false;
   public $allow_signed_keys = false;
}
