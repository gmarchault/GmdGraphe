<?php
class CUtilitaire
{
    /**
    * Retourne le nom du module (de la table) sans le $prefix ni les "_"
    *
    * @param string $module module full name
    * @param string $prefix prefix des tables de la bdd (rel_ par defaut)
    *
    * @return string module short name
    *
    * @assert ("rel_mytable_1","rel_") == "mytable 1"
    * @assert ("rel_mytable_1","") == "rel mytable 1"
    * @assert ("rel_mytable_1","tot") == "rel mytable 1"
    * @assert ("","") == ""
    * @assert ("","rel_") == ""
    */
    static function module_short( $module="", $prefix="" )
    {
     if ( $module=="" ) return "";
     if ( substr($module,0,strlen($prefix))!=$prefix ) return strtr( $module, "_", " " );
     return strtr( substr( $module, strlen($prefix) ), "_", " " );
    }


    /**
    * Retourne une date format ISO en format FR
    *
    * @param string $date  date au format ISO : AAAA-MM-JJ (sans les heures)
    *
    * @return string formattee en date FR
    *
    * @assert ("2011-05-11") == "11/05/2011"
    * @assert () == ""
    * @assert ("2011-5-11") == "2011-5-11"
    * @assert ("11/05/11") == "11/05/11"
    *
    */
    static function date_iso2fr( $date="" )
    {
     if ( $date=="" || strlen($date)!=10 ) return $date;

     $t_date = explode( "-", $date );
     if ( sizeof($t_date)==0 ) return -1;

     return $t_date[2]."/".$t_date[1]."/".$t_date[0];
    }

}
