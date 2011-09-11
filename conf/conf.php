<?php 
require_once "bdd_dev.php";

$PREFIX_BDD = "rel_";

$NAVIGATEUR_MOBILE = "Mobile";
$NAVIGATEUR_PC = "PC";

$PATH_WEB_RELATIF = "/myhome";
$PATH_WEB_ABSOLU = "http://$WEB$PATH_WEB_RELATIF";

// Rep a purger :
$PHP_SELF = $_SERVER['PHP_SELF'];
$DOCUMENT_ROOT = $_SERVER['DOCUMENT_ROOT'];
$UPLOAD_REP = "$DOCUMENT_ROOT/tmp";

//$GRAPHE_FORMAT = "gif";
$GRAPHE_FORMAT = "jpg";

$GRAPHE_REP_WEB = "$PATH_WEB_RELATIF/releve/graphe/bitmap";
$GRAPHE_REP_DISK = "$DOCUMENT_ROOT/$GRAPHE_REP_WEB";
$GRAPHE_NOM_UNIQUE = 0; // Pour eviter les pbs des images cachees.

$XML_PATH_CONFFILE = "$DOCUMENT_ROOT/$PATH_WEB_RELATIF/releve/graphe/generation/bitmap/xml";

$MODE_DEBUG = 1;

require_once "../classe/cutilitaire.php";
require_once "../classe/cerreur.php";
require_once "../classe/cconnexion.php";
require_once "../classe/cihm.php";

// L'ordre est important :
$ihm = new CIhm();
$erreur = new CErreur();
$cnx = new CConnexion( $DATABASE );
?>
