<?php
error_reporting(0);
ob_clean();
header('Content-Type: image/png');
readfile(__DIR__ . '/../pics/ativa_logo.png');
