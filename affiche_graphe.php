<?php 
require_once "conf/conf.php";
require_once "classe/caffiche_graphe.php";

$ihm->header();
$ihm->titre( "Affichage graphique" );


$module = $_REQUEST[module];
if ( $module ) print "module choisi : $module<br><br>";

//print "<a href='/myhome/releve/graphe/generation/bitmap/generation_all.php'>(Re)g�n�rer tous les graphes</a><br><br>";

print "Liste des graphes disponibles :<br><br>";

$graphe = new CAffiche_graphe();
$t_module = array(); // A modifier : doit contenir les modules selected.
// Recherche des graphes sur disk
$total_trouves = $graphe->recherche_disk( $t_res, $t_module );
// Affichage des graphes trouves
for ( $i=0; $i<$total_trouves; $i++ )
{
 $nom = cutilitaire::module_short( $t_res['nom'][$i], $PREFIX_BDD );
 $nom = substr( $nom, 0, strrpos($nom, ".") );
 print "- <a href=\"{$t_res['nom_web'][$i]}\" target='graphe'>$nom</a><br>";
}

$ihm->footer();
?>