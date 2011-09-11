<?php 
require_once "conf/conf.php";
require_once "classe/caffiche_graphe.php";

$titre = "Gm's Home";
$menu = "";
$analytic = "";
$agent = $_SERVER["HTTP_USER_AGENT"];
$ihm = new CIhm( $PATH_WEB_RELATIF, $titre, $analytic, $menu, $agent );
$aff['head'] = $ihm->get_header();
$aff['titre'] = $ihm->get_tetiere( "Affichage graphique" );
$aff['footer'] = $ihm->footer();

// Parametres d'entree
$module = $_REQUEST[module];

$aff['module'] = "";
if ( $module ) $aff['module'] = "module choisi : $module<br><br>";

//print "<a href='/myhome/releve/graphe/generation/bitmap/generation_all.php'>(Re)g�n�rer tous les graphes</a><br><br>";

$aff['module'] .= "Liste des graphes disponibles :<br><br>";

$graphe = new CAffiche_graphe();
$t_module = array(); // A modifier : doit contenir les modules selected.
// Recherche des graphes sur disk
$total_trouves = $graphe->recherche_disk( $t_res, $t_module );
// Affichage des graphes trouves
for ( $i=0; $i<$total_trouves; $i++ )
{
 $nom = cutilitaire::module_short( $t_res['nom'][$i], $PREFIX_BDD );
 $nom = substr( $nom, 0, strrpos($nom, ".") );
 $aff['module'] .= "- <a href=\"{$t_res['nom_web'][$i]}\" target='graphe'>$nom</a><br>";
}


//
// Affichage
//
print $aff['$header'];
print $aff['titre'];
print $aff['module'];
print $aff['footer'];
