<?php
$a = session_id();
if(empty($a)) session_start();

//==================================================================================================
//    Nom du module : login.php développé par Frédéric de Marion - frederic.de.marion@free.fr
//--------------------------------------------------------------------------------------------------
//  Version |    Date    | Commentaires
//--------------------------------------------------------------------------------------------------
//    V1.00 | 12/04/2017 | Version originale
//==================================================================================================


	$Paroisse_name = "Notre Dame de la Sagesse"; // Sophia-Antipolis
	$Paroisse_name = "St Paul des 4 vents"; // Lyon

	require("sqlconf.php");
	$db= mysqli_connect( $sqlserver , $login , $password, $sqlbase ) or die("Cannot connect Database : " . mysql_error());
	//mysql_select_db( $sqlbase, $db );
	mysqli_query($db, "SET NAMES 'ISO-8859-1'");
	mysqli_query($db, 'SET NAMES latin1');
	
	$requete_Lieux = 'SELECT * FROM Lieux WHERE IsParoisse = -1';
	$result_Lieux = mysqli_query($db, $requete_Lieux);
	while($row_lieu = mysqli_fetch_array($result_Lieux)){
		$Paroisse_name = $row_lieu['Lieu'];
	}

	header( 'content-type: text/html; charset=UTF-8' );
	echo '<!DOCTYPE HTML>';
	echo '<HTML><HEAD>';
	echo '<TITLE>Database '.$Paroisse_name.'</TITLE>';
	//echo '<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />';
	echo '</HEAD>';
	setlocale (LC_TIME, 'fr_FR','fra');	
	mb_internal_encoding('UTF-8');
	
	//echo '<TABLE align="center">';
	//echo '<TR><TD><IMG SRC="/logo.jpg" HEIGHT=150></TD>';
	//echo '<TD>';

	echo '<TABLE width="420" border="0" align="center" cellpadding="0" cellspacing="1" bgcolor="#CCCCCC">';
	echo '<TR><FORM name="form1" method="post" action="checklogin.php">';
	echo '<TD>';
	echo '<TABLE width="100%" border="0" cellpadding="3" cellspacing="1" bgcolor="#FFFFFF">';
	
	echo '<TR><TD colspan="3" align="center"><IMG SRC="/logo.jpg" HEIGHT=150></TD></TR>';
	
	echo '<TR><TD colspan="3" align="center" ><FONT face=verdana size=2><STRONG>Connection base de données '.$Paroisse_name.'<BR>&nbsp</FONT></STRONG></TD></TR>';
	
	// identifiant
	echo '<TR><TD width="300"><FONT face=verdana size=2>Adresse mail</FONT></TD><TD width="6">:</TD>';
	echo '<TD width="100"><INPUT name="myusername" type="text" id="myusername" size="30"></TD></TR>';

	// date de naissance
	//echo '<TR><TD width="300"><FONT face=verdana size=2>Date de naissance</FONT></TD><TD width="6">:</TD>';
	//echo '<TD width="400"><INPUT name="mynaissance" placeholder="JJ/MM/AAAA" type="text" id="mynaissance" size="10" maxlength="10"></TD></TR>';

	// mot de passe
	echo '<TR><TD><FONT face=verdana size=2>Mot de passe</FONT></TD><TD>:</TD>';
	echo '<TD><INPUT name="mypassword" type="password" id="mypassword"></TD></TR>';
	
	// Niveau souhaité
	echo '<input type=hidden name="mylevelrequested" value=100 >';
	echo '<TR><TD> </TD><TD> </TD><TD><INPUT type="submit" name="Submit" value="Login"></TD></TR>';
	echo '</TABLE></TD></FORM>';
	echo '</TR>';
	
	echo '</TABLE>';
	
	//echo '</TD></TABLE>';
	
?>