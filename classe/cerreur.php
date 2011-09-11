<?php

/**
* Classe de gestion des erreurs
* Multi-vue, multi-langue, multi-support (html, fichier, mail),
* mode debugage html, et handler d'erreur g�n�rique html.
* Gestion d'un log
*/
class CErreur
{
	var $message;
	var $msg_debug;
	var $log; // Gestion d'un tablo de conf pour les log


	/**
	* Constructeur, avec postionnement du handler d'erreurs.
	*/
	function __construct()
	{
		global $DOCUMENT_ROOT;

		$this->message = ""; // Message d'erreur
		$this->msg_debug = array(); // Tableau des messages de debug

		$this->log = array();
		$this->log['pb'] = 0; // Si probleme avec le fichier de log
		$this->log['fin_ligne'] = "\n";

		$this->log['fichier'] = "$DOCUMENT_ROOT/log.txt";

		// Entetes du fichier de log
		$this->log['entete'][0] = "";
		$this->log['entete'][1] = "";

		//error_reporting ( E_USER_ERROR | E_PARSE | E_ERROR );
		$old_error_handler = set_error_handler("denootis_error_handler");

		return 0;
	}


	/**
	* R�cuperation du message d'erreur positionn�.
	* Sortie :
	*	$msg : message deja postionn�, en vue d'un afichage ult�rieur.
	*/
	function get_erreur( &$msg )
	{
		$msg = $this->message;

		return 0;
	}



	/**
	* Affichage de l'erreur de facon bloquante.
	*
	* Global :
	*	$cnx : instanciation de la classe de connexion bdd.
	*
	* Entree :
	*	$msg : optionnel, message a afficher, si non present, affichage
	*		   du message positionn�.
	*
	* ATTENTION : Il faut faire un  add_log() AVANT de faire un aff_bloquant(), pour savoir ce qu'il s'est pass� !
	*/
	function aff_bloquant( $msg="" )
	{
		global $WEB, $PHP_SELF, $PATH_WEB_RELATIF;
		global $MODE_DEBUG, $DOCUMENT_ROOT, $ihm;

		$force_arret = 1;

		if ( $msg!="" )
		{
			$this->message = $msg;
		}
		else
		{
			$this->message = "Erreur interne";

			// Cas d'un debug lev� par handler (-> err sql), on va arreter le prog
			$force_arret = 1;
		}

	$mesg_err = $this->message;

	$aff =<<< EOT
<table BORDER='0' CELLPADDING='0' CELLSPACING=''>
 <tr>
   <td valign='top' align='right'><img valign='top' src='/myhome/lib/image/erreur.jpg' border='0'></td>
   <td valign='middle' align='left'><div class='erreur'>$mesg_err</div></td>
 </tr>
</table>
<br>
EOT;

// Affichage pr�alable des debugs avant le bloquant.
$this->aff_debug();

//$ihm->header();
print $aff;
$ihm->footer_autre( "<a href='javascript:history.go(-1)'>Retour</a> | <a href='$PATH_WEB_RELATIF'>Sommaire</a>" );

exit();

return 0;
}




/**
* Positionnement du message interne de l'erreur.
* Entree :
*	$msg : Message a afficher par la suite.
*/
function set_erreur( $msg="" )
{
	$this->message = $msg;

	return 0;
}


/**
* Destructeur
*/
function destruct()
{
	return 0;
}



/**
* Ajout du message dans un fichier de log
*
* ENTREE :
*	$msg : message en FR (pas de multi-langue !) pour les developpeurs !
*/
function add_log( $msg="" )
{
	global $MODE_DEBUG;

	if ( $msg=="" ) return 0;
	else
	{
		$msg = ereg_replace( "\n", " ", $msg );
		$msg = ereg_replace( "<br>", "", $msg );
		$msg = date("d/m/y H:i:s")." - ".$_SERVER['REMOTE_ADDR']." - {$this->log['entete'][0]} - {$this->log['entete'][1]} - $msg";
	}

	$t_color = explode( ".", $_SERVER['REMOTE_ADDR'] );

	$color = sprintf("%02s%02s%02s", substr(dechex($t_color[1]+$t_color[2]+$t_color[3]*7), -2),
	substr(dechex($t_color[2]+$t_color[3]*8), -2), substr(dechex($t_color[3]+$t_color[3]*9), -2) );

	if ( @is_writable($this->log['fichier']) )
	{
		if ( ! $handle=@fopen($this->log['fichier'], 'a') )
		{
			$this->log['pb'] = 1;
		}

		// Filtrage sur caracteres
		$msg = ereg_replace( "</tr>", " | ", $msg );
		$msg = strip_tags( $msg );
		if ( ! @fwrite($handle, "<font color='#$color'>$msg</font>{$this->log['fin_ligne']}") )
		{
			$this->log['pb'] = 1;
		}

		@fclose($handle);
	}
	else
	{
		$this->log['pb'] = 1;
	}

	return 0;
}



/**
* Debuguage, ajout d'un message dans un tableau de debugage.
*	$_SESSION[];
* Entree :
*	$msg : optionnel, mesasge a afficher, sinon derniere erreur ($this->message).
* Sortie :
*	Message dans une fenetre html de nom "debug".
*/
function add_debug( $msg="" )
{
	array_push( $this->msg_debug, $msg );

	return 0;
}


/**
* Debuguage, extension de la classe des erreurs.
* Global :
*	$_SESSION[];
* Entree :
*	$msg : optionnel, mesasge a afficher, sinon tableau des messages $this->msg_debug.
* Sortie :
*	Message dans une fenetre html de nom "debug".
*/
function aff_debug( $msg="" )
{
	global $MODE_DEBUG;

	return 0;
}

}



/**
* Handler d'erreur DENOOTIS.
*
* Utilis� sur erreurs graves (cnx bdd, err sql, ...),
* afin de pr�venir l'�quipe technique par email ou fichier de log, ...
*
* FONCTIONNEMENT :
*  Si MODE DEBUG => affichage direct des erreurs, dans navigateur ( = mode dev)
*  sinon (mode prod), affichage d'un message general d'erreur $txt['erreur'][24];
*    et loggue dans fichier log, ainsi qu'envoi de mail.
*
*/
function denootis_error_handler( $errno, $errmsg, $filename, $linenum, $vars )
{
	global $erreur;
	global $MODE_DEBUG;

	// D&eacute;finit un tableau associatif avec les cha&icirc;nes d'erreur
	// En fait, les seuls niveaux qui nous interessent
	// sont 2,8,256,512 et 1024

	$errortype = array (
	1   =>  "Erreur",
	2   =>  "Alerte",
	4   =>  "Error d'analyse",
	8   =>  "Note",
	16  =>  "Core Error",
	32  =>  "Core Warning",
	64  =>  "Compile Error",
	128 =>  "Compile Warning",
	256 =>  "Erreur sp&eacute;cifique",
	512 =>  "Alerte sp&eacute;cifique",
	1024=>  "Note sp&eacute;cifique"
	);

	// Les niveaux qui seront enregistr&eacute;s
	$user_errors = array( E_USER_ERROR, E_USER_WARNING, E_PARSE, E_USER_NOTICE);
	//error_reporting ( E_USER_ERROR | E_USER_WARNING | E_USER_NOTICE );
	//error_reporting ( E_USER_ERROR | E_PARSE | E_USER_WARNING | E_USER_NOTICE  );

	if ( in_array($errno, $user_errors) )
	{
		// Pour affichage debugage fenetre appli web
		//$err = "<div class=debug><errorentry>==> E R R E U R&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;F A T A L E <==<br>\n";
		$err = "<div class=debug><errorentry>";
		//$dt = date("Y-m-d H:i:s (T)");
		//$err .= "\t<datetime>".$dt."</datetime><br>\n";
		//$err .= "\t<errornum>n� ERREUR=".$errno."</errornum><br>\n";
		//$err .= "\t<errortype>TYPE=".$errortype[$errno]."</errortype><br>\n";
		$err .= "\t<errormsg>".$errmsg."</errormsg><br>\n";
		$err .= "\t<scriptname>FICHIER=[ $m / $v ] ".$filename."</scriptname>,";
		$err .= "\t<scriptlinenum>LIGNE=".$linenum."</scriptlinenum><br>\n";
		//error_reporting ( E_USER_ERROR );
		//$err .= "\t<vartrace>VARIABLES=".wddx_serialize_value($vars,"Variables")."</vartrace>\n";
		$err .= "</errorentry></div>\n\n";

		// Pour le mail
		if ( $MODE_DEBUG==0 )
		$err = str_replace( "<br>", " - ", $errmsg)." | FICHIER= $m $v | $filename | LIGNE=$linenum\n";

		$erreur->set_erreur( $err );

		// Pour les logs disk
		if ( $MODE_DEBUG==0 )
		{
			$errmsg = str_replace( "\n", " - ", $errmsg);
			$err_log = str_replace( "<br>", " - ", $errmsg)." | FICHIER= $m $v | $filename | LIGNE=$linenum\n";
			$erreur->add_log( $err_log );
		}

		$erreur->aff_bloquant();
	}

	return 0;

	/*
	// sauvegarde de l'erreur, et mail si c'est critique
	error_log($err, 3, "/usr/local/php4/error.log");
	if ($errno == E_USER_ERROR)
	mail("phpdev@example.com","Critical User Error",$err);
	*/

	define ("FATAL",E_USER_ERROR);
	define ("ERROR",E_USER_WARNING);
	define ("WARNING",E_USER_NOTICE);

	print "-- ERREUR --<br>";

	// configure le niveau de rapport d'erreur pour ce script
	error_reporting (FATAL | ERROR | WARNING);

	switch ($errno)
	{
		case FATAL:
		$msg = "<b>FATAL</b> [$errno] $errstr<br>\n";
		$msg .= "FICHIER:".$errfile.", LIGNE:".$errline;
		//echo ", PHP ".PHP_VERSION." (".PHP_OS.")<br>\n";
		$erreur->set_erreur( $msg );
		$erreur->add_log( $msg );
		$erreur->aff_bloquant();
		//exit(1);
		break;

		case ERROR:
		$msg = "<b>ERROR</b> [$errno] $errstr<br>\n";
		$erreur->set_erreur( $msg );
		$erreur->add_log( $msg );
		$erreur->aff_bloquant();
		break;

		case WARNING:
		$msg = "<b>WARNING</b> [$errno] $errstr<br>\n";
		$erreur->set_erreur( $msg );
		$erreur->add_log( $msg );
		$erreur->aff_bloquant();
		break;

		default:
		/*
		E_ERROR
		E_WARNING
		E_PARSE
		E_NOTICE
		E_CORE_ERROR
		E_CORE_WARNING
		E_COMPILE_ERROR
		E_COMPILE_WARNING
		E_USER_ERROR
		E_USER_WARNING
		E_USER_NOTICE
		E_ALL
		*/

		/*
		$msg = "Erreur d�tect�e : Unkown error type: [$errno] $errstr<br>\n";
		$erreur->set_erreur( $msg );
		$erreur->aff_erreur();
		*/
		break;
	}

	return 0;
}
