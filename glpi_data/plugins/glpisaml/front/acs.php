<?php
/**
 *  ------------------------------------------------------------------------
 *  GlpiSAML
 *  GlpiSAML is heavily influenced by the initial work of Derrick Smith's
 *  PhpSaml. This project's intend is to address some structural issues
 *  caused by the gradual development of GLPI. It intends to use more of the
 *  GLPI core samlConfigs and php8/composer namespaces.
 *
 *  Copyright (C) 2024 by Chris Gralike
 *  ------------------------------------------------------------------------
 *
 * LICENSE
 *
 * This file is part of GlpiSAML project.
 * GlpiSAML plugin is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * GlpiSAML is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with GlpiSAML. If not, see <http://www.gnu.org/licenses/>.
 *
 * ------------------------------------------------------------------------
 *
 *  @package    GlpiSAML
 *  @version    1.1.4
 *  @author     Chris Gralike
 *  @copyright  Copyright (c) 2023 by Chris Gralike
 *  @license    GPLv2+
 *  @see        https://github.com/DonutsNL/GlpiSAML/readme.md
 *  @link       https://github.com/DonutsNL/GlpiSAML
 *  @since      1.0.0
 * ------------------------------------------------------------------------
 **/
use GlpiPlugin\Glpisaml\LoginFlow\Acs;
use GlpiPlugin\Glpisaml\LoginState;

// Capture the post and get before GLPI does.
$post = $_POST;     // Contains the samlResponse;

// https://codeberg.org/QuinQuies/glpisaml/issues/45
// Added idpId because we need to be able to unpack the samlResponse,
// get het RequestId inside and match that with our database and work
// from there as a way to get arround the cookie requirements.
$get = $_GET;       // Contains at least the idpId field;

// Use a countable datatype to empty the global
// https://github.com/derricksmith/phpsaml/issues/153
$_POST = [];
$_GET = [];

// Load GLPI define("DO_NOT_CHECK_ORIGIN", true);
define("NOT_GLPI_LOGIN", true);
define("GLPI_SKIP_CSRF_CHECK", true);
$backup_post = $_POST;
$_POST = [];
include_once dirname(__DIR__, 3) . '/vendor/autoload.php';
include_once dirname(__DIR__) . '/vendor/autoload.php';
include_once dirname(__DIR__, 3) . '/inc/includes.php';
$_POST = $backup_post;                       //NOSONAR - Cant be included with USE.

// Load ACS
try{
    $acs = new Acs($get, $post);
} catch(Throwable $e) {
    print "An error occurred:".$e;
}
