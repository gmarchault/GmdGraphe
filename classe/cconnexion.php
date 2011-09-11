<?php
if ( !function_exists( "file_get_contents" ) )
{
	function file_get_contents( $filename )
	{
		global $erreur;

		$fp = @file($filename, 'r+');
		if (!$fp)
		{
			$msg_err = 'file_get_contents ne peut pas lire le fichier ' . $filename;
			$erreur->aff_bloquant( $msg_err );

			return;
		}
		$data = implode( '', $fp);

		return $data;
	}
}


/**
* Connexion bdd
*
* Cette classe sert de connexion / deconnexion / lancement requete,
*
* Entree :
* Possibilite de passer le nom de la database (def="zr")
*/
class CConnexion
{
	var $driver;
	var $server;
	var $user;
	var $password;
	var $database; // Impossible de faire du cross-db !! (11/2003)

	var $db;
	var $id_connexion;
	var $id_resultat_set;
	var $last_oid;
	var $cache_path;

	/**
        *
	*/
	function __construct( $bdd="" )
	{
		global $DOCUMENT_ROOT, $BDD, $DATABASE;

		$this->cache_path = '/tmp/cache_mysql';
		if ( ! @is_dir($this->cache_path) ) @mkdir( $this->cache_path );

		$this->driver = $BDD['driver'];
		$this->server = $BDD['server'];
		$this->user = $BDD['user'];
		$this->password = $BDD['password'];

		// Impossible de faire du cross-db !! (11/2003) :
		if ( $bdd!="" ) $this->database = $bdd;
		else $this->database = $DATABASE;

		$this->id_connexion = -1;
		$this->connexion();

		$this->last_oid = FALSE;
		$this->id_resultat_set = FALSE;

		return 0;
	}



	/**
	* Connexion a la base PostgreSQL
	*
	* Sortie :
	* Implemente $this->id_connexion
	*/
	function connexion()
	{
		global $erreur;

		/* Connexion MySQL */
		$this->id_connexion = mysql_connect($this->server, $this->user, $this->password);
		//if ( ! (@mysql_connection_status($this->id_connexion)==0) )
		if ( FALSE == $this->id_connexion )
		{
			$msg_err = "-*- ERREUR acc�s BDD -*-<br>driver=$this->driver, serveur=$this->server, user=$this->user, database=$this->database<br>";
			$erreur->aff_bloquant( $msg_err );
		}

		return 0;
	}


	/**
	* Destructeur
	*/
	function destruct()
	{
		$this->deconnexion();

		return 0;
	}


	/**
	* Deconnexion de la base
	*
	* NB : Il est preferable d'utiliser la methode $this->destruc()
	*/
	function deconnexion()
	{
		@mysql_close( $this->id_connexion );

		return 0;
	}



	/**
	* Lancement requete pour n arguments sur un SELECT,
	* avec retour du nombre de lignes resultantes
	*
	* Rem: Pour une procedure stock�e, le nom de(s) champ(s) resultant(s) : [Expr_1], ...
	*
	* Entree :
	* 	$the_requete : requete SQL
	*
	* Sortie :
	* 	$t_res : tableau valeurs a 2 dimensions avec nom colonne et indice ligne
	*   	       (ex : $t_res['nom'][0])
	* 	retour du nombre de r�sultats
	*/
	function get_requete( $the_requete, &$t_res )
	{
		global $erreur;

		$tot = 0;
		$t_res = array();

		//print "<br>*** get_requete = [$the_requete]<br>";
		if ( false == @mysql_select_db( $this->database ) ) { return(-1); }

		$res_id = @mysql_query( $the_requete, $this->id_connexion );
		if (! $res_id)
		{
			$msg_err = "-*- ERREUR sql GET -*-<br>[$the_requete]<br>".@mysql_errno($this->id_connexion).":".@mysql_error($this->id_connexion);
			$erreur->aff_bloquant( $msg_err );
		}

		$tot = @mysql_num_rows( $res_id );
		//print "*** total de requete = $tot<br>\n";

		$posit = 0;
		while( $row = @mysql_fetch_array($res_id) )
		{
			for($cpt=0; $cpt<@mysql_num_fields($res_id); $cpt++)
			{
				$champ = @mysql_field_name($res_id,$cpt);
				$t_res[$champ][$posit] = $row[$champ];
				//print "*** {$t_res[$champ][$posit]},$champ,$posit ==<br>\n"; flush();
			}
			$posit++;
		}

		@mysql_free_result($res_id);
		return( $tot );
	}





	/**
	* Requete pour executer une requete de type INSERT, UPDATE, DELETE.
	*
	* Entree :
	*	$the_requete : requete SQL
	*	gestion_err : 0/1, 1 par defaut, mais si 0 alors pas de remont�e d'err
	*		(interessant lors d'insert si doublon...)
	*
	* Sortie :
	* 	retour du nombre de lignes affectees
	*/
	function set_requete( $the_requete, $gestion_err=1 )
	{
		global $erreur;

		$tot = 0;
		$this->last_oid = FALSE;

		//print "set() : requete=[$the_requete]<br>";

		$this->id_resultat_set = mysql_db_query( $this->database, $the_requete, $this->id_connexion );

		if (! $this->id_resultat_set && $gestion_err==1 )
		{
			$msg_err = "-*- ERREUR sql GET -*-<br>[$the_requete]<br>".@mysql_errno($this->id_connexion).":".@mysql_error($this->id_connexion);
			$erreur->aff_bloquant( $msg_err );
		}
		$tot = @mysql_affected_rows();

		$this->last_oid = @mysql_insert_id();
		@mysql_free_result($this->id_resultat_set);

		return( $tot );
	}


	/**
	* Retourne le dernier oid cree lors du dernier insert dans la base.
	*
	* Sortie :
	* 	Retourne la valeur du dernier oid, FALSE si pas trouve.
	*/
	function get_last_oid()
	{
		//print "=>$this->id_resultat_set<br>";
		//if ( PGSQL_COMMAND_OK == mysql_result_status( $this->id_resultat_set ) )

		return $this->last_oid;
	}


	/**
	* Retourne l'id d'une table a partir d'un oid. 
	* a utiliser a partir de $this->get_last_oid(), sur des tables WITH OIDS uniquement;
	* utile lors ajout d'un new enr dans 1 table.
	* EX D'APPEL : $new_id = $cnx->get_id( "pa_critere_ligne_unitaire", $cnx->get_last_oid() );
	* => recupere le new id cr�� dans la table, apres un INSERT. (A faire imm�diatement apres l'INSERT).
	*
	* ENTREE :
	*	nom_table : nom table trait�e
	*	oid : oid recherch�.
	*
	* SORTIE :
	* 	Retourne la valeur du dernier id ins�r� dans la tabel d'entree, -1 si pas trouve ou probleme.
	*/
	function get_id( $nom_table="", $oid=0 )
	{
		$total = 0;

		if ( $nom_table!="" && $oid!="" && $oid!=0 )
		{
			$requete = "SELECT id FROM $nom_table WHERE oid=$oid";
			$total = $this->get_requete( $requete, $t_res );
		}

		if ( $total==1 ) return $t_res['id'][0];
		else return -1;
	}



	/**
	* Retourne la liste des �l�ments d'une table et le nombre d'�l�ments
	* Entrees :
	*		$table : table concern�es
	*		$champs : liste des champs
	*		$options : liste des options
	* Sortie :
	*		$t_res : tableau de resultat
	*		$total : nombre d'�l�ments
	*/
	function liste_tous( $table, &$t_res, &$total, $champs="*", $option="")
	{
		$requete = "SELECT $champs FROM $table $option";
		$total = $this->check_cache( $requete, 86400, $t_res );

		return 0;
	}



	/**
	* Solution de cache disk sur requete SQL SELECT.
	* rep de cache = $this->cache_path (=/tmp/cache_pgsql)
	*
	* ENTREE :
	*		$request : request SQL SELECT pour verifier si resultats cach�s
	*		$time : nbre de secondes a considerer si cache encore valide
	*
	* SORTIE :
	*		$records : tableau de resultats issus du cache ou bien du new select
	*		retourne : nbre de datas dans la requete
	*/
	function check_cache( $request, $time=0, &$records )
	{
		global $erreur;

		$records = "";
		$total = 0;

		//$t_debut = microtime();

		$cachename = $this->cache_path.'/'.md5($request).'.csql';
		if ( @file_exists($cachename) && @filemtime($cachename)>(time()-$time) )
		{
			$records = @unserialize( @file_get_contents($cachename) );

			/*
			$t_fin = microtime();
			$temps = sprintf( "%02f", $t_fin - $t_debut);
			if ( $_SESSION['user']=='gmarchaultp' )
			$erreur->add_log( "check_cache( $request ) / CACHE $temps s" );
			*/

			$t_cle = array_keys( $records );
			$total = sizeof( $records[$t_cle[0]] );

			return $total;
		}
		else
		{
			$total = $this->get_requete( $request, $records );
			/*
			while ($record = mysql_fetch_array($result, NULL, PGSQL_ASSOC) )
			$records[] = $record;
			*/
			$output = @serialize( $records );
			$fp = @fopen( $cachename, "wb" );
			@flock($fp,2);
			@fputs($fp, $output);
			@flock($fp,3);
			@fclose($fp);

			/*
			$t_fin = microtime();
			$temps = $t_fin - $t_debut;
			if ( $_SESSION['user']=='gmarchaultp' )
			$erreur->add_log( "check_cache( $request ) / TO-CACHE $temps s" );
			*/

			return $total;
		}
	}

}
