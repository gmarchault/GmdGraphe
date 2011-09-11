<?php

class CAffiche_graphe
{
 var $prefix;
 var $path_web;
 var $path_appli;


 function __construct()
 {
  global $DOCUMENT_ROOT, $PATH_WEB_RELATIF, $PREFIX_BDD;

  $this->prefix = $PREFIX_BDD;
  $this->path_appli = "$PATH_WEB_RELATIF/releve/graphe/bitmap";
  $this->path_web = "$DOCUMENT_ROOT$this->path_appli";

 }



 /**
 * Destructeur
 */
 function destruct()
 {
	return 0;
 }



/**
*
* NON OPERATIONNEL !!!!!!!!!!!!!!!
*
* Recherche des graphes deja generes
*
* SORTIE :
*	$exist=0|1 : precise l'existance prealable ou non du fichier sur disk.
*	Si oui, positionne alors $this->nom_disk et $this->nom_web
*/
function recherche_graphe( &$exist )
{
	global $erreur, $cnx, $DOCUMENT_ROOT;

	$exist = 0;

	$t_f_exclure = array( ".", ".." );
	$t_info = array();
	$fichier_present = 0;

	// Formattage du nom en recherche d'image :
	$nom = "{$this->num_data}_{$this->type}_{$this->t_image['nbre']}_";

	//print "recherche de $nom...<br>";

	// Recherche si user possede une image
	if ( @is_dir($this->path_web) )
	{
		if ( $dh=@opendir($this->path_web) )
		{
			while ( ($filee=@readdir($dh))!==false )
			{
				if ( in_array($filee, $t_f_exclure ) ) continue;

				// Recherche de l'image
				if ( substr($filee, 0, strlen($nom))==$nom )
				{
					$fichier_present = 1;
					// Positionnement du nom_web a partir du nom_disk trouv�
					$this->set_nom_image( "$this->path_web/$filee" );

					// Recherche du texte car image trouvee
					// Formattage du nom du fichier texte en recherche d'image :
					$nom_txt = "$this->path_web/{$filee}.{$lang_d[0]}.txt.inc";

					if ( @is_file( $nom_txt ) )
					{
						$txt_image = implode( " ", @file( $nom_txt ));
						if ( $txt_image!="" ) $this->titre = stripslashes(strip_tags($txt_image));
					}

					break;
				}
			}
			@closedir($dh);
		}
	}
	@clearstatcache();
	$exist = $fichier_present;

	return 0;
}


/**
*
* NON OPERATIONNEL !!!!!!!!
*
* Suppression d'une illustration d'un num_data et nbre donn�
*
* REM :
*	$t_image::image_mode doit �tre a "maj"
*
* SORTIE :
*	$msg : message de suppression ok ou nok.
*	Retourne -1 si parametres image non existante
*	Retourne -2 si echec de suppression
*/
function supprime( &$msg )
{
	global $erreur, $cnx, $DOCUMENT_ROOT;

	// Suppression de l'image b
	$resultat = @unlink( $this->nom_disk );
	if ( $resultat==false ) { $msg.= "[$this->nom_disk]"; return -2; }

	// Suppression eventuelle de l'image s
	$nom_disk_s = eregi_replace( "_b_", "_s_", $this->nom_disk);
	$resultat = @unlink( $nom_disk_s );

	// REM : il faudrait supprimer toutes les langues !!!!!
	// A faire plus tard.
	$nom_txt = "{$this->nom_disk}.{$lang_d[0]}.txt.inc";
	$resultat = @unlink( $nom_txt );
	$this->titre = "";

	//$erreur->add_log( "cimage_annonce::supprime() : del de $this->nom_disk et $nom_disk_s et $nom_txt" );

	@clearstatcache();

	return 0;
}


/**
* Suppression de toutes les illustrations d'un $module (sans prefix)
* ex: electricite => rel_electricite_*  ou  velo_gm
* ---  OU ---
* Suppression de tous les graphes du $this->prefix 
*
* SORTIE :
*	Retourne le total de graphes supprimes
*/
function purge_images( $module="" )
{
	$t_f_exclure = array( ".", ".." );
	$total_del = 0;

	// Format de recherche des images a purger.
	$nom = $this->prefix.$module;

	// Recherche si user possede une image
	if ( @is_dir($this->path_web) )
	{
		if ( $dh=@opendir($this->path_web) )
		{
			while ( ($filee=@readdir($dh))!==false )
			{
				if ( in_array($filee, $t_f_exclure ) ) continue;
				//$res = substr($filee, 0, strlen($nom));
				//print "comparaison : [$filee]=[$res]=[$nom]<br>";

				if ( substr($filee, 0, strlen($nom))==$nom )
				{
					@unlink( "$this->path_web/$filee" );
					$total_del++;
				}
			}
			@closedir($dh);
		}
	}
	@clearstatcache();

	return $total_del;
}


/**
* Recherche sur disk toutes les illustrations d'un $module (sans prefix)
* ex: electricite => rel_electricite_*  ou  velo_gm
* ---  OU ---
* Recherche sur disk de tous les graphes du $this->prefix 
*
* ENTREE :
*  $t_module : tableau des modules recherches
*  REM : ne marche que pour le 1er actuellement !!!!!!!!
*
*
* SORTIE :
*	Retourne les graphes trouves avec les cles : [nom], [nom_web], [nom_physique] en param de sortie 
*	et le nombre de graphes trouves en sortie de fonction.
*/
function recherche_disk( &$t_res, $t_module=array() )
{
	$t_res = array();
	$t_f_exclure = array( ".", ".." );
	$total_found = 0;

	// Format de recherche des images a purger.
	$nom = $this->prefix.$t_module[0];

	//print "<br>recherche de $this->path_web/$nom<br>";

	// Recherche si user possede une image
	if ( @is_dir($this->path_web) )
	{
		if ( $dh=@opendir($this->path_web) )
		{
			while ( ($filee=@readdir($dh))!==false )
			{
				if ( in_array($filee, $t_f_exclure ) ) continue;
				//$res = substr($filee, 0, strlen($nom));
				//print "comparaison : [$filee]=[$res]=[$nom]<br>";

				if ( substr($filee, 0, strlen($nom))==$nom )
				{
					$t_res['nom'][$total_found] = $filee;
					$t_res['nom_physique'][$total_found] = "$this->path_web/$filee";
					$t_res['nom_web'][$total_found] = "$this->path_appli/$filee";
					$total_found++;
				}
			}
			@closedir($dh);
		}
	}
	@clearstatcache();

	return $total_found;
}

}
