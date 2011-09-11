<?php

class CIhm
{
    public  $head_title;
    private $_type_navigateur;
    private $_google_analytics;
    private $_path_web_relatif;
    public  $menu; // Si menu dynamique
    CONST NAVIGATEUR_MOBILE = "Mobile";
    CONST NAVIGATEUR_PC = "PC";

    /**
     *
     * @param string $path_web_relatif path_web_relatif
     * @param string $title title header
     * @param string $analytic code analytics like UA-XXXXXXX-X
     * @param string $menu menu
     */
    function __construct( $path_web_relatif="", $title="", $analytic="", $menu="", $agent="" )
    {
      if ( $title!="" ) $this->head_title = $title;
      else $this->head_title = "Gm's MyHome";
      $this->_type_navigateur = CIhm::NAVIGATEUR_PC;
      $this->menu = $menu;
      $this->_path_web_relatif = $path_web_relatif;

      $this->_set_type_navigateur($agent);

      $this->_google_analytics =<<< EOT
    <script type="text/javascript">
    var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
    document.write(unescape("%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));
    </script>
    <script type="text/javascript">
    var pageTracker = _gat._getTracker("$analytic");
    pageTracker._initData();
    pageTracker._trackPageview();
    </script>
EOT;
      return 0;
    }


    /**
    *
    */
    function destruct()
    {
      return 0;
    }


    /**
    * Retourne le code html du bandeau de titre de la page (message du titre + menu)
    *
     * @param string $msg message a afficher
    */
    function get_tetiere( $msg="" )
    {
     $aff =<<< EOT
    <table border=0>
    <tr>
     <td><a href='$this->_path_web_relatif'><img src='$this->_path_web_relatif/lib/image/myhome.jpg' border='0'></a></td>
     <td><div class='titre'>$msg</div></td>
     <td>$this->menu</td>
    </tr>
    </table>
    <br>
EOT;
     return $aff;
    }


    /**
    *
    */
    function get_header()
    {
    // Affichage entete
    $aff =<<< EOT
    <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
    <html>
    <head>
      <meta content="text/html; charset=ISO-8859-1" http-equiv="content-type">
      <title>$this->head_title</title>
      <link media="all" rel="stylesheet" href="$this->_path_web_relatif/style/ordinateur.css" type="text/css">
      <script type="text/javascript" src="lib/jquery/jquery-1.2.1.pack.js"></script>
    </head>

    <body>
EOT;
    //  <link media="all" rel="stylesheet" href="$PATH_WEB_RELATIF/style/mobile.css" type="text/css">
     return $aff;
    }


    /**
    *
    */
    function footer()
    {
     $aff =<<< EOT
    <br>
    <font class='menu'>Copyright @2011 Gm | <a href='$this->_path_web_relatif/doc' target='doc'>Doc</a> | <a href='$this->_path_web_relatif'>Sommaire</a> </font>
    $this->_google_analytics
    </body>
    </html>
EOT;
     return $aff;
    }



    /**
    *
    * @param string $agent
    */
    private function _set_type_navigateur( $agent="" )
    {
     if ( stristr( $agent, "palm" )===FALSE )
      $this->_type_navigateur = CIhm::NAVIGATEUR_MOBILE;
     else
      $this->_type_navigateur = CIhm::NAVIGATEUR_PC;

     return 0;
    }


    /**
    *
    */
    public function get_type_navigateur()
    {
     return $this->_type_navigateur;
    }

}
