<?php

class CIhm
{
     var $head_title;
     var $_type_navigateur;
     var $google_analytics;
     var $menu; // Si menu dynamique

    function __construct()
    {
      global $NAVIGATEUR_PC;

      $this->head_title = "Gm's MyHome";
      $this->type_navigateur = $NAVIGATEUR_PC;
      $this->menu = "";

     $this->google_analytics =<<< EOT
    <script type="text/javascript">
    var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
    document.write(unescape("%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));
    </script>
    <script type="text/javascript">
    var pageTracker = _gat._getTracker("UA-XXXXXXX-X");
    pageTracker._initData();
    pageTracker._trackPageview();
    </script>
EOT;

      $this->_set_type_navigateur();

      return 0;
    }


    function destruct()
    {


      return 0;
    }


    function titre( $msg="" )
    {
     global $PATH_WEB_RELATIF;

     $aff =<<< EOT
    <table border=0>
    <tr>
     <td><a href='$PATH_WEB_RELATIF'><img src='$PATH_WEB_RELATIF/lib/image/myhome.jpg' border='0'></a></td>
     <td><div class='titre'>$msg</div></td>
     <td>$this->menu</td>
    </tr>
    </table>
    <br>
EOT;

     print $aff;

     return 0;
    }


    function header( $menu="" )
    {
     global $PATH_WEB_RELATIF;

     $this->menu = $menu;

    // Detection navigateur

    // Affichage entete
    $aff =<<< EOT
    <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
    <html>
    <head>
      <meta content="text/html; charset=ISO-8859-1" http-equiv="content-type">
      <title>$this->head_title</title>
      <link media="all" rel="stylesheet" href="$PATH_WEB_RELATIF/style/ordinateur.css" type="text/css">
      <script type="text/javascript" src="lib/jquery/jquery-1.2.1.pack.js"></script>
    </head>

    <body>
EOT;

    //  <link media="all" rel="stylesheet" href="$PATH_WEB_RELATIF/style/mobile.css" type="text/css">
     print $aff;

     return 0;
    }


    function footer()
    {
     global $PATH_WEB_RELATIF ;

     $aff =<<< EOT
    <br>
    <font class='menu'>Copyright @2006 Gm | <a href='$PATH_WEB_RELATIF/doc' target='doc'>Doc</a> | <a href='$PATH_WEB_RELATIF'>Sommaire</a> </font>
    $this->google_analytics
    </body>
    </html>
EOT;

     print $aff;

     return 0;
    }


    function footer_autre( $what )
    {
     $aff =<<< EOT
    <br>
    <div class='menu'>$what</div>
    </body>
    </html>
EOT;

     print $aff;

     return 0;
    }


    function _set_type_navigateur()
    {
     global $NAVIGATEUR_MOBILE, $NAVIGATEUR_PC;

     if ( stristr( $_SERVER["HTTP_USER_AGENT"], "palm" )===FALSE )
      $this->_type_navigateur = $NAVIGATEUR_PC;
     else
      $this->_type_navigateur = $NAVIGATEUR_MOBILE;

     return 0;
    }


    function get_type_navigateur()
    {
     return $this->_type_navigateur;
    }


}

