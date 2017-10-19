<?php

//==================================================================================================
//    Nom du module : Mariage.php d�velopp� par Fr�d�ric de Marion - frederic.de.marion@free.fr
//--------------------------------------------------------------------------------------------------
//  Version |    Date    | Commentaires
//--------------------------------------------------------------------------------------------------
//    V1.00 | 12/04/2017 | Version originale
//==================================================================================================
// 17/05/2017 : Correction, le gestionnaire mariage ne pouvait pas cr�er de nouvelle fiche fianc�e
// 17/05/2017 : Correction, proposer des fiches d�j� cr�es et vierges lors d'une nouvelle cr�ation
// 17/07/2017 : Accompagnateur, Modification Impossible check Acte de Naissance, Bapt�me et Lettre intention
//==================================================================================================

// Initialiser variable si elle n'existe pas
if( ! isset( $edit ) ) $edit = ""; 
if( ! isset( $delete_fiche_fiance ) ) $delete_fiche_fiance = ""; 
if( ! isset( $delete_fiche_fiance_confirme ) ) $delete_fiche_fiance_confirme = ""; 
if( ! isset( $Selectionner_Paroissien ) ) $Selectionner_Paroissien = ""; 
if( ! isset( $upload_Photo_couple ) ) $upload_Photo_couple = ""; 

function debug($ch) {
   global $debug;
   if ($debug)
      echo $ch;
}

//row color

function usecolor( )
{
	$trcolor1 = "#EEEEEE";
	$trcolor2 = "#E1E1E1";
	static $colorvalue;
	if($colorvalue == $trcolor1)
		$colorvalue = $trcolor2;
	else
		$colorvalue = $trcolor1;
	return($colorvalue);
}

session_start();
Global $eCOM_db;
$debug = false;
//$IdSession = $_POST["IdSession"];
//session_readonly();

$Activite= 2; //Preparation mariage
$Activite_id= 2; //Preparation mariage
$SessionEnCours=$_SESSION["Session"];
require('templateMariage.inc');
require('Common.php');
$debug = false;
pCOM_DebugAdd($debug, "Mariage - SessionEnCours=".$SessionEnCours);

require('Paroissien.php');

//edit records
if ( isset( $_GET['action'] ) AND $_GET['action']=="edit") {
//if ($action == "edit") { 
	
	$debug = false;
	
	if ( $_GET['id'] == 0 ) {
		// creation d'une nouvelle fiche impossible si pas gestionnaire ou administrateur
		if ($_SERVER['USER'] > 2 || fCOM_Get_Autorization( $_SESSION["Activite_id"] )>= 30) 
		{
			$id = 0;
			$requete = 'SELECT id FROM Fianc�s WHERE MAJ="0000-00-00 00:00:00" AND Lieu_mariage="" AND Status="" ORDER BY id DESC';
			$result = mysqli_query($eCOM_db, $requete);
			//$row = mysqli_fetch_assoc($result);
			while( $row = mysqli_fetch_assoc( $result)) {
				$id = $row['id'];
			}
			if ( $id == 0 ) {
				$requete = 'INSERT INTO Fianc�s (id, Commentaire) VALUES (0,"")'; 
				pCOM_DebugAdd($debug, 'Mariage:edit - requete01='.$requete);
				mysqli_query($eCOM_db, $requete) or die (mysqli_error($eCOM_db));
				$id = mysql_insert_id();
				mysqli_query($eCOM_db, 'UPDATE Fianc�s SET Session="'.$SessionEnCours.'" WHERE id='.$id.' ') or die (mysqli_error($eCOM_db));

			}
			$_SESSION["RetourPageCourante"]=$_SERVER['PHP_SELF'].'?Session='.$_SESSION["Session"].'&action=edit&id='.$id;
		} else {
			echo '<META http-equiv="refresh" content="0; URL='.$_SERVER['PHP_SELF'].'">';
			mysqli_close($eCOM_db);
			exit;
		}
	} else {
		$id= $_GET['id'];
		$_SESSION["RetourPageCourante"]=$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'];
	}
	

	$requete = 'SELECT * FROM Fianc�s WHERE id='.$id.' '; 
	pCOM_DebugAdd($debug, 'Mariage:edit - requete02='.$requete);
	$result = mysqli_query($eCOM_db, $requete);
	$row = mysqli_fetch_assoc($result);
	
	address_top();
	echo '<link rel="stylesheet" type="text/css" href="includes/Tooltip.css">';
		
	if ($_SERVER['USER'] <= 2 || fCOM_Get_Autorization( $_SESSION["Activite_id"] )>= 30) { 
		$BloquerAcces="";
	} else {
		$BloquerAcces="disabled='disabled'";
	}

	echo '<TABLE WIDTH="100%" BORDER="0" CELLSPACING="1" CELLPADDING="4" BGCOLOR="#FFFFFF">';
	echo '<TR BGCOLOR="#F7F7F7">';
	echo '<TD><FONT FACE="Verdana" SIZE="2"><B>Edition: ';
	echo 'Fiche No '.$row['id'].' </TD>'; 
	if (strftime("%d/%m/%y", fCOM_sqlDateToOut($row['MAJ'])) != "01/01/70" ) {
		echo '<TD align="right"><FONT FACE="Verdana" SIZE="1"> (Derni�re modification au '.strftime("%d/%m/%Y %T", fCOM_sqlDateToOut($row['MAJ'])).')</TD>';
	}
	echo '</TR>';
	

	echo '<TR><TD BGCOLOR="#EEEEEE" Colspan="2"><CENTER><font face="verdana" size="2">';
	echo '<FORM method=post action="'.$_SERVER['PHP_SELF'].'">';
	echo '<TABLE border="0" cellpadding="2" cellspacing="0">';
	
	// Recherche de LUI_id
	$requete2 = 'SELECT T1.`id` 
				FROM QuiQuoi T0 
				LEFT JOIN Individu T1 ON T1.`id`=T0.`Individu_id`
				WHERE T0.`Activite_id`=2 AND T0.`QuoiQuoi_id`=1 AND T0.`Engagement_id`='.$id.' AND T1.`Sex`="M"';
	$result2 = mysqli_query($eCOM_db, $requete2);
	$row2 = mysqli_fetch_assoc($result2);
	$LUI_id = $row2['id'];
	
	if ( $LUI_id > 0) {
		$requete2 = 'SELECT * FROM Individu WHERE id='.$LUI_id.''; 
		pCOM_DebugAdd($debug, 'Mariage:edit - requete03='.$requete2);
		$result2 = mysqli_query($eCOM_db, $requete2);
		$row1 = mysqli_fetch_assoc($result2);
	}
	
	// Recherche de ELLE_id
	$requete2 = 'SELECT T1.`id` 
				FROM QuiQuoi T0 
				LEFT JOIN Individu T1 ON T1.`id`=T0.`Individu_id`
				WHERE T0.`Activite_id`=2 AND T0.`QuoiQuoi_id`=1 AND T0.`Engagement_id`='.$id.' AND T1.`Sex`="F"';
	$result2 = mysqli_query($eCOM_db, $requete2);
	$row2 = mysqli_fetch_assoc($result2);
	$ELLE_id = $row2['id'];
	
	if ( $ELLE_id > 0) {
		$requete2 = 'SELECT * FROM Individu WHERE id='.$ELLE_id.''; 
		pCOM_DebugAdd($debug, 'Mariage:edit - requete04='.$requete2);
		$result2 = mysqli_query($eCOM_db, $requete2);
		$row2 = mysqli_fetch_assoc($result2);
	}
	
	//------
	// LUI
	//------
	
	echo '<TR><TD width="140" bgcolor="#eeeeee" valign="top">';
	if ( $BloquerAcces=="" )
	{
		pCOM_DebugAdd($debug, 'Mariage:edit - LUI_id='.$LUI_id);
		if ( $LUI_id > 0 ) {
			echo '<DIV style="display:inline"><input type="submit" name="Selectionner_Paroissien" value="Le fianc�">';
		} else {
			echo '<DIV style="display:inline"><input type="submit" name="Selectionner_Paroissien" value="S�lectionner le fianc�">';
		}
		echo '<INPUT type="hidden" name="Fiche_id" value="'.$id.'">';
		echo '<INPUT type="hidden" name="ButtomName" value="LUI">';
		echo '</DIV>';
	} else {
		echo '<B><FONT SIZE="3">LUI</FONT></B>';
	}
	echo '</TD>';
	
	echo '<TD bgcolor="#eeeeee" colspan="2">';
	if ( $LUI_id > 0 ) {
		if ($_SERVER['USER'] <= 2 || fCOM_Get_Autorization( $_SESSION["Activite_id"] )>= 30) {
			Display_Photo($row1['Nom'], $row1['Prenom'], $LUI_id, 2);
		} else {
			echo '<FONT SIZE="2">'.ucwords($row1['Prenom']). ' ' .$row1['Nom'].'</FONT>';
		}
		// T�l�phone
		echo '<BR><FONT SIZE="1"><B>T�l�phone: </B>'.Securite_html($row1['Telephone']).'</FONT>';
		// email
		echo '<BR><FONT SIZE="1"><B>Email: </B>'.Securite_html($row1['e_mail']).'</FONT>';
		// adresse
		echo '<BR><FONT SIZE="1"><B>Adresse: </B>'.Securite_html($row1['Adresse']).'</FONT>';

		// Date de Naissance
		echo '<B><FONT SIZE="2"><BR>N� le :</FONT></B>';
		if ( $row1['Naissance'] != "0000-00-00" ) {
			echo '<FONT FACE="Verdana" SIZE="1"> '.date("d/m/Y", strtotime($row1['Naissance']));
			$Age = fCOM_Afficher_Age($row1['Naissance']);
			if ( $Age > -1 ) {
				echo '<FONT FACE="Verdana" SIZE="1"> ('.$Age.' ans) </FONT>';
			}
		} else {
			echo '<FONT FACE="Verdana" SIZE="1">---</FONT>';
		}

		// Confession
		echo '<B><FONT SIZE="2"> Confession :</FONT></B>';
		echo '<select name="LConfession" '.$BloquerAcces.' >';
		foreach ($Liste_Confessions as $LConfession){
			if ($row1['Confession'] == $LConfession){
				echo '<option value="'.$LConfession.'" selected="selected">'.$LConfession.'</option>';
			} else {
				echo '<option value="'.$LConfession.'">'.$LConfession.'</option>';
			}
		}		
		echo '</select>';
	} else {
		echo '<input type=hidden name="LConfession" value="Confession ?">';	
	}

	if ( $LUI_id > 0 && $ELLE_id > 0 ) {
		pCOM_DebugAdd($debug, 'Mariage:edit - ELLE_id='.$ELLE_id);
		// Selection de la declaration intention LUI
		$Declaration="";
		if ($row1['Confession'] == "Catholique") {
			if ($row2['Confession'] == "Catholique"){ $Declaration="1a";}
			else {
				if ($row2['Confession'] == "Sans"){ $Declaration="4a";}
				else {
					if ($row2['Confession'] == "Cat�chum�ne"){ $Declaration="3a";}
					else { $Declaration="2a";}
				}
			}
		} else {
			if ($row1['Confession'] == "Sans") {
				if ($row2['Confession'] == "Catholique"){ 
					$Declaration="4b";
				} else { 
					$Declaration="D. Intention Impossible";
				}
			} else {
				if ($row1['Confession'] == "Cat�chum�ne") {
					if ($row2['Confession'] == "Catholique"){ 
						$Declaration="3b";
					} else { 
						echo "D. Intention Impossible";
					}
				} elseif ($row1['Confession'] == "Musulman") { 
					if ($row2['Confession'] == "Catholique"){ 
						$Declaration="5b";
					} else { 
						echo "D. Intention Impossible";
					}
				} else { 
					if ($row1['Confession'] == "Confession ?" or $row1['Confession'] == "") { 
						$Declaration="";
					} else {
						$Declaration="2b";
					}
				}
			}
		}
		echo " <A href=\"Formulaires/".$Declaration.".pdf\" target=\"_blank\"><FONT SIZE=\"2\">$Declaration</FONT></A>";
	}
	echo "</TD>";

		
	// Photo	
	if ( $LUI_id > 0 && $ELLE_id > 0 ) {
		echo '<TD align="center" valign="middle" rowspan="7">';
		if (file_exists("Photos/" . $row['id'] . ".jpg")) { 
			echo '<IMG SRC="Photos/' . $row['id'] . '.jpg" HEIGHT=150><BR><BR>';
			if ($_SERVER['PHP_AUTH_USER'] == "administrateur" || $_SERVER['PHP_AUTH_USER'] == "gestionnaire" || fCOM_Get_Autorization( $Activite_id ) >= 30) {
			echo "<div align=center><input type=submit name=upload_Photo_couple value='Charger une autre photo...'>"; }		
		} else {
			if ($_SERVER['PHP_AUTH_USER'] == "administrateur" || $_SERVER['PHP_AUTH_USER'] == "gestionnaire" || fCOM_Get_Autorization( $Activite_id ) >= 30) {
			echo "<div align=center><input type=submit name=upload_Photo_couple value='Charger une photo...'>"; }
		}
	}
	echo '</TD></TR>';

	// Enfants de LUI
	if ( $LUI_id > 0 ) {
		if ( $ELLE_id > 0 ) {
			$ConditionWhere='AND T0.`Mere_id`!='.$ELLE_id.'';
		} else {
			$ConditionWhere='';
		}
		$requeteEnfants = 'SELECT T0.id, T0.`Nom`, T0.`Prenom`, T0.`Nom`, T0.`Naissance` 
							FROM `Individu` T0 
							WHERE T0.`Pere_id`='.$LUI_id.' '.$ConditionWhere.' 
							ORDER BY Naissance';
		$debug = false;
		pCOM_DebugAdd($debug, 'Mariage:edit - requeteEnfants01='.$requeteEnfants);
		$TitreLigne ='<TR><TD></TD><TD><FONT SIZE="2">Enfant(s) : </FONT>';
		$resultListEnfants = mysqli_query($eCOM_db, $requeteEnfants);
		while( $ListEnfants = mysqli_fetch_assoc( $resultListEnfants ))
		{
			echo $TitreLigne;
			$TitreLigne = "";
			//$Prenom=$ListEnfants[Prenom];
			Display_Photo("", $ListEnfants['Prenom'], $ListEnfants['id'], "1");
			if (strftime("%d/%m/%y", fCOM_sqlDateToOut($ListEnfants['Naissance'])) != "01/01/70" ) {
				$birthDate = explode("-", $ListEnfants['Naissance']);
				$Age = (date("md", date("U", mktime(0, 0, 0, $birthDate[1], $birthDate[2], $birthDate[0]))) > date("md") ? ((date("Y")-$birthDate[0])-1):(date("Y")-$birthDate[0]));
				//$Prenom= $Prenom." ($Age ans)";
				
				echo '<FONT SIZE="1">('.$Age.' ans) </FONT>';
			} else {
				echo '</A><FONT SIZE="1"> - </FONT>';
			}
		}
		if ($TitreLigne == "")
		{
			echo '</TD></TR>';
		}

		echo '<TR><TD bgcolor="#eeeeee" valign="top"></TD>';
		echo '<TD colspan="2" valign="top"><P>';
		if ($row['LUI_Extrait_Naissance'] == '1') { $optionSelect = "checked"; } else { $optionSelect = ""; };
		echo '<input type="checkbox" name="LUI_Acte_Naissance" '.$BloquerAcces.' id="LUI_Acte_Naissance" '.$optionSelect.' /> <label for="LUI_Acte_Naissance"><FONT SIZE="2">Acte de Naissance</b></label>';
		if ($row['LUI_Extrait_Bapteme'] == '1') { $optionSelect = "checked"; } else { $optionSelect = ""; };
		echo '<input type="checkbox" name="LUI_Acte_Bapteme" '.$BloquerAcces.' id="LUI_Acte_Bapteme" '.$optionSelect.' /> <label for="LUI_Acte_Bapteme"><FONT SIZE="2">Acte de Bapt�me<br></b></label>';
		if ($row['LUI_Lettre_Intention'] == '1') { $optionSelect = "checked"; } else { $optionSelect = ""; };
		echo '<input type="checkbox" name="LUI_Lettre_Intention" '.$BloquerAcces.' id="LUI_Lettre_Intention" '.$optionSelect.' /> <label for="LUI_Lettre_Intention"><FONT SIZE="2">Lettre d\'intention</b></label>';
		echo '</P></TD></TR><TR><TD height="10"></TD></TR>';
	
	} else {
		echo '<input type=hidden name="LUI_Acte_Naissance" value="0">';
		echo '<input type=hidden name="LUI_Acte_Bapteme" value="0">';
		echo '<input type=hidden name="LUI_Lettre_Intention" value="0">';
	}
	
	//------
	// ELLE
	//------
	
	echo '<TR><TD width="140" bgcolor="#eeeeee" valign="top">';
	if ( $BloquerAcces=="" )
	{
		if ( $ELLE_id > 0 ) {
			echo '<DIV style="display:inline"><input type="submit" name="Selectionner_Paroissien" value="La fianc�e">';
		} else {
			echo '<DIV style="display:inline"><input type="submit" name="Selectionner_Paroissien" value="S�lectionner la fianc�e">';
		}
		echo '<INPUT type="hidden" name="Fiche_id" value="'.$id.'">';
		echo '<INPUT type="hidden" name="ButtomName" value="ELLE">';
		echo '</DIV>';
	} else {
		echo '<B><FONT SIZE="3">ELLE</FONT></B>';
	}
	echo '</TD>';
	
	echo '<TD bgcolor="#eeeeee" colspan="2">';
	if ( $ELLE_id > 0 ) {
		if ($_SERVER['USER'] <= 2 || fCOM_Get_Autorization( $_SESSION["Activite_id"] )>= 30) {
			Display_Photo($row2['Nom'], $row2['Prenom'], $ELLE_id, 2);
		} else {
			echo '<FONT SIZE="2">'.ucwords($row2['Prenom']). ' ' .$row2['Nom'].'</FONT>';
		}
		
		// T�l�phone
		echo '<BR><FONT SIZE="1"><B>T�l�phone: </B>'.Securite_html($row2['Telephone']).'</FONT>';
		// email
		echo '<BR><FONT SIZE="1"><B>Email: </B>'.Securite_html($row2['e_mail']).'</FONT>';
		// adresse
		echo '<BR><FONT SIZE="1"><B>Adresse: </B>'.Securite_html($row2['Adresse']).'</FONT>';

		// Date de Naissance
		echo '<B><FONT SIZE="2"><BR>N� le :</FONT></B>';
		if ( $row2['Naissance'] != "0000-00-00" ) {
			echo '<FONT FACE="Verdana" SIZE="1"> '.date("d/m/Y", strtotime($row2['Naissance']));
			$Age = fCOM_Afficher_Age($row2['Naissance']);
			if ( $Age > -1 ) 
			{
				echo '<FONT FACE="Verdana" SIZE="1"> ('.$Age.' ans) </FONT>';
			}
		} else {
			echo '<FONT FACE="Verdana" SIZE="1">---</FONT>';
		}

		// Confession
		echo '<B><FONT SIZE="2"> Confession :</FONT></B>';
		echo '<select name="EConfession" '.$BloquerAcces.' >';
		foreach ($Liste_Confessions as $EConfession){
			if ($row2['Confession'] == $EConfession){
				echo '<option value="'.$EConfession.'" selected="selected">'.$EConfession.'</option>';
			} else {
				echo '<option value="'.$EConfession.'">'.$EConfession.'</option>';
			}
		}		
		echo '</select>';
	} else {
		echo '<input type=hidden name="EConfession" value="Confession ?">';
	}
	
	if ( $LUI_id > 0 && $ELLE_id > 0 ) {
		// Selection de la declaration intention ELLE
		$Declaration="";
		if ($row2['Confession'] == "Catholique") {
			if ($row1['Confession'] == "Catholique"){
				$Declaration="1a";
			} else {
				if ($row1['Confession'] == "Sans"){ 
					$Declaration="4a";
				} elseif ($row1['Confession'] == "Cat�chum�ne"){ 
					$Declaration="3a";
				} elseif ($row1['Confession'] == "Musulman"){ 
					$Declaration="5a";
				} else {
					$Declaration="2a";
				}
			}
		} else {
			if ($row2['Confession'] == "Sans") {
				if ($row1['Confession'] == "Catholique"){ 
					$Declaration="4b";
				} else {
					$Declaration="D. Intention Impossible";
				}
			} else {
				if ($row2['Confession'] == "Cat�chum�ne") {
					if ($row1['Confession'] == "Catholique"){ 
						$Declaration="3b";
					} else { 
						echo "D. Intention Impossible";
					}
				} else { 
					if ($row2['Confession'] == "Confession ?" or $row2['Confession'] == "") { 
						$Declaration="";
					} else {
						$Declaration="2b";
					}
				}
			}
		}
		echo " <a href=\"Formulaires/".$Declaration.".pdf\" target=\"_blank\"><FONT SIZE=\"2\">$Declaration</FONT></a>";
	}
	echo '</TD></TR>';
	
	// Enfants de ELLE
	if ( $ELLE_id > 0 ) {
		if ( $LUI_id > 0 ) {
			$ConditionWhere='AND T0.`Pere_id`!='.$LUI_id.'';
		} else {
			$ConditionWhere='';
		}
		$requeteEnfants = 'SELECT T0.id, T0.`Nom`, T0.`Prenom`, T0.`Nom`, T0.`Naissance` 
							FROM `Individu` T0 
							WHERE T0.`Mere_id`='.$ELLE_id.' '.$ConditionWhere.' 
							ORDER BY Naissance';
		$debug = false;
		pCOM_DebugAdd($debug, 'Mariage:edit - requeteEnfants02='.$requeteEnfants);
		$TitreLigne ='<TR><TD></TD><TD><FONT SIZE="2">Enfant(s) : </FONT>';
		$resultListEnfants = mysqli_query($eCOM_db, $requeteEnfants);
		while( $ListEnfants = mysqli_fetch_assoc( $resultListEnfants ))
		{
			echo $TitreLigne;
			$TitreLigne = "";
			Display_Photo("", $ListEnfants['Prenom'], $ListEnfants['id'], "1");
			if (strftime("%d/%m/%y", fCOM_sqlDateToOut($ListEnfants['Naissance'])) != "01/01/70" ) {
				$birthDate = explode("-", $ListEnfants['Naissance']);
				$Age = (date("md", date("U", mktime(0, 0, 0, $birthDate[1], $birthDate[2], $birthDate[0]))) > date("md") ? ((date("Y")-$birthDate[0])-1):(date("Y")-$birthDate[0]));
				//$Prenom= $Prenom." ($Age ans)";
				
				echo '<FONT SIZE="1">('.$Age.' ans) </FONT>';
			} else {
				echo '</A><FONT SIZE="1"> - </FONT>';
			}
		}
		if ($TitreLigne == "")
		{
			echo '</TD></TR>';
		}
	
		echo '<TR><TD bgcolor="#eeeeee" valign="top"></TD>';
		echo '<TD colspan="2" valign="top"><P>';
		if ($row['ELLE_Extrait_Naissance'] == '1') { $optionSelect = "checked"; } else { $optionSelect = ""; };
		echo '<input type="checkbox" name="ELLE_Acte_Naissance" '.$BloquerAcces.' id="ELLE_Acte_Naissance" '.$optionSelect.' /> <label for="ELLE_Acte_Naissance"><FONT SIZE="2">Acte de Naissance</b></label>';
		if ($row['ELLE_Extrait_Bapteme'] == '1') { $optionSelect = "checked"; } else { $optionSelect = ""; };
		echo '<input type="checkbox" name="ELLE_Acte_Bapteme" '.$BloquerAcces.' id="ELLE_Acte_Bapteme" '.$optionSelect.' /> <label	for="ELLE_Acte_Bapteme"><FONT SIZE="2">Acte de Bapt�me<br></b></label>';
		if ($row['ELLE_Lettre_Intention'] == '1') { $optionSelect = "checked"; } else { $optionSelect = ""; };
		echo '<input type="checkbox" name="ELLE_Lettre_Intention" '.$BloquerAcces.' id="ELLE_Lettre_Intention" '.$optionSelect.' /> <label for="ELLE_Lettre_Intention"><FONT SIZE="2">Lettre d\'intention</b></label>';
		echo '</P></TD></TR><TR><TD height="10"></TD></TR>';
	
	} else {
		echo '<input type=hidden name="ELLE_Acte_Naissance" value="0">';
		echo '<input type=hidden name="ELLE_Acte_Bapteme" value="0">';
		echo '<input type=hidden name="ELLE_Lettre_Intention" value="0">';
	}
	
	if ( $ELLE_id > 0 AND $LUI_id > 0 ) {
		// Premier ministre � avoir accueilli les fianc�s
		
		echo '<TR><TD bgcolor="#eeeeee"><b><FONT SIZE="2">1er contact:</FONT></b></TD><TD>';
		echo '<SELECT name="Prem_Accueil_id" '.$BloquerAcces.' >';
		$Liste_Celebrants = fCOM_Get_liste_celebrants($row['Prem_Accueil_id']);
		foreach ($Liste_Celebrants as $Celebrant_array){
			list($celebrant_id, $celebrant_prenom, $celebrant_nom)=$Celebrant_array;
			if ($row['Prem_Accueil_id'] == $celebrant_id ){
				echo '<option value='.$celebrant_id.' selected="selected">'.$celebrant_prenom.' '.$celebrant_nom.'</option>';
			} else {
				echo '<option value='.$celebrant_id.'>'.$celebrant_prenom.' '.$celebrant_nom.'</option>';
			}
		}
		echo '</SELECT></TD></TR>';
	}
	
	// Accompagnateur
	echo '<TR><TD valign= "top" bgcolor="#eeeeee">';
	if ( $LUI_id > 0 && $ELLE_id > 0 ) {
		echo '<DIV><INPUT type="submit" name="Selectionner_Paroissien" value="Accompagnateur(s)"></TD>';
		echo '<TD>';
		$requete2 = 'SELECT T0.`id`, T0.`Nom`, T0.`Prenom`, T0.`Sex` 
				FROM `Individu` T0 
				LEFT JOIN `QuiQuoi` T1 ON T0.`id`=T1.`Individu_id` 
				WHERE T1.`Activite_id`=2 AND T1.`QuoiQuoi_id`=2 and T1.`Engagement_id`='.$id.'
				ORDER BY Sex, Prenom, Nom';
		$debug = false;
		$NbFiches=0;
		pCOM_DebugAdd($debug, 'Mariage:edit - requete accompagnateur='.$requete2);
		$result2 = mysqli_query($eCOM_db, $requete2);
		while( $row2 = mysqli_fetch_assoc( $result2 ))
		{
			if ( fCOM_Get_Autorization($_SESSION["Activite_id"]) >= 30 ) {
				echo "<A HREF=".$_SERVER['PHP_SELF']."?action=RetirerAccompagnateur&Qui_id=".$row2['id']."&Invite_id=".$id." TITLE='Retirer Accompagnateur'><img src=\"images/moins.gif\" border=0 alt='Delete Accompagnateur'></a>  ";
				fCOM_Display_Photo(Securite_html($row2['Nom']), Securite_html($row2['Prenom']), $row2['id'], 1, True);
			} else {
				fCOM_Display_Photo(Securite_html($row2['Nom']), Securite_html($row2['Prenom']), $row2['id'], 1, False);
			}
			echo '<BR>';
			$NbFiches = $NbFiches +1;
		}
		if ($NbFiches == 0) {
			echo '<FONT FACE="Verdana" SIZE="1">Pas d\'accompagnateurs encore s�lectionn�s</FONT>';
		}
	}
	echo '</TD>';


		
	// Session
	echo '<TR><TD align="left">';
	if ( $LUI_id > 0 && $ELLE_id > 0 ) {
		if ($row['Session']=="0" ) {
			$TestSession = $SessionEnCours;
		} else {
			$TestSession = $row['Session'];
		}
		echo '<B><FONT FACE="Verdana" SIZE="2">Session:</FONT></B></TD><TD>';
		echo '<SELECT name="AnneeSession" '.$BloquerAcces.'>';
		for ($i=2006; $i<=(intval(date("Y"))+5); $i++) {
			if ($i == intval($TestSession)) {echo '<option value="'.$i.'" selected="selected">'.$i.'</option>';} else {echo '<option value="'.$i.'">'.$i.'</option>';}
		}
		echo "</SELECT>";
	
		// Status 
		//echo "<BR>";
		$Liste_Status = array("A affecter");
		$Item = 1;
		$Liste_Status[$Item]="Parcours normal";
		$Item =$Item + 1;
		$Liste_Status[$Item]="Pr�pa. Ext.";
		$Item =$Item + 1;
		$Liste_Status[$Item]="Pr�pa. Ext. + WE";
		$Item =$Item + 1;
		$Liste_Status[$Item]="Annul�/Report�";
		$Item =$Item + 1;
		$Liste_Status[$Item]="WE";
		$Item =$Item + 1;
		$Liste_Status[$Item]="WE Annul�";
		$Item =$Item + 1;
		$Liste_Status[$Item]="CANA WE";
		$Item =$Item + 1;
		$Liste_Status[$Item]="CANA WE Annul�";
		$Item =$Item + 1;
		$Liste_Status[$Item]="Autre";
		echo '<SELECT name="Status" '.$BloquerAcces.' >';
		foreach ($Liste_Status as $Status){
			if ($row['Status'] == $Status){
				echo '<option value="'.$Status.'" selected="selected">'.$Status.'</option>';
			} else {
				echo '<option value="'.$Status.'">'.$Status.'</option>';
			}
		}		
		echo '</SELECT>';
		echo '</TD></TR>';
	


		// Lieu du mariage
		
		// Il n'y a qu'un champs Lieu_mariage, soit le lieu fait partie de la pr�liste, et on le pr�-s�lectionne dans cette liste, soit on remplit le champs libre � droite.
		echo '<TR><TD bgcolor="#eeeeee"><B><FONT SIZE="2">Lieu du mariage:</FONT></B></TD>';
		echo '<TD bgcolor="#eeeeee" colspan="2">';
		echo '<select name="LMariage" '.$BloquerAcces.' >';
		$Liste_Lieu_Celebration = pCOM_Get_liste_lieu_celebration(1000);
		$Lieu_Celebration_trouve = false;
		foreach ($Liste_Lieu_Celebration as $Lieu_Celebration_array){
			list($Lieu_id, $Lieu_name) = $Lieu_Celebration_array;
			if ($row['Lieu_mariage'] == $Lieu_name){
				$Lieu_Celebration_trouve = TRUE;
			}
		}		
		foreach ($Liste_Lieu_Celebration as $Lieu_Celebration_array){
			list($Lieu_id, $Lieu_name) = $Lieu_Celebration_array;
			if ($row['Lieu_mariage'] == $Lieu_name OR ($Lieu_name == "Hors Paroisse" AND $Lieu_Celebration_trouve == False AND $row['Lieu_mariage'] != "" )){
				echo '<option value="'.$Lieu_name.'" selected="selected">'.$Lieu_name.'</option>';
			} else {
				echo '<option value="'.$Lieu_name.'">'.$Lieu_name.'</option>';
			}
		}
		echo '</SELECT>';
		echo ' ';
		echo '<INPUT type=text name=AutreLMariage placeholder="Autre lieu de mariage : <Ville> (<dept>)" ';
		if ( $Lieu_Celebration_trouve == false) {
			echo ' value ="'.$row['Lieu_mariage'].'"';
		}
		echo ' size="40" maxlength="30" '.$BloquerAcces.'>';
		echo '</TD></TR>';
	
		// Date du mariage
		echo '';
		if (! empty($row['Date_mariage'])) {
			$DateYear=substr($row['Date_mariage'],0,4);
			$DateMonth=substr($row['Date_mariage'],5,2);
			$DateDay=substr($row['Date_mariage'],8,2);
			$DateValue = $DateDay."/".$DateMonth."/".$DateYear;
		}

		echo '<TR><TD bgcolor="#eeeeee"><B><FONT SIZE="2">Date du mariage:</FONT></B></TD>';
		echo '<TD width="225" bgcolor="#eeeeee" colspan="2">';
		echo '<input type=text id="DateMariage" name="DateMariage" value ="'.$DateValue.'" size="9" maxlength="10" '.$BloquerAcces.'>';
		if ($BloquerAcces=="") { 
			?>
			<a href="javascript:popupwnd('calendrier.php?idcible=DateMariage&langue=fr','no','no','no','yes','yes','no','50','50','470','400')" target="_self"><img src="images/calendrier.gif" id="Image1" alt="" border="0" style="width:20px;height:20px;"></a></span>
			<?php
		}
		echo '</SELECT>';
		
		echo '<b><FONT SIZE="2">  Heure </FONT></b>';
		$hour = substr($row['Date_mariage'],11,2);
		echo '<SELECT name="heure" '.$BloquerAcces.' >';
		for ($i=0; $i<=23; $i++) {
			if ($i == intval($hour)) {echo '<option value="'.sprintf("%02d", $i).'" selected="selected">'.sprintf("%02d", $i).'</option>';} else {echo '<option value="'.sprintf("%02d", $i).'">'.sprintf("%02d", $i).'</option>';}
		}
		echo '</SELECT>:';

		$min = substr($row['Date_mariage'],14,2);
		echo '<SELECT name="minute" '.$BloquerAcces.' >';
		for ($i=0; $i<=45; $i=$i+15) {
			if ($i == intval($min)) {	echo '<option value="'.sprintf("%02d", $i).'" selected="selected">'.sprintf("%02d", $i).'</option>';} else {echo '<option value="'.sprintf("%02d", $i).'">'.sprintf("%02d", $i).'</option>';}
		}
		echo '</SELECT></TD></TR>';
	
	
	
		// Celebrant
		
		echo '<TR><TD bgcolor="#eeeeee"><b><FONT SIZE="2">C�l�brant:</FONT></b></td>';
		echo '<TD bgcolor="#eeeeee" colspan="2">';
		echo '<SELECT name="Celebrant" '.$BloquerAcces.' >';
		//$Liste_Celebrants = get_liste_celebrants_Mariage();
		$Liste_Celebrants = fCOM_Get_liste_celebrants($row['Celebrant_id']);
		//debug_plus('Mariage.php celebrants are = "'.$Liste_Celebrants[1].'"');
		$Celebrant_trouve = ' value ="'.$row['Celebrant'].'"';
		$Item = 1;
		foreach ($Liste_Celebrants as $Celebrant_array){
			list($celebrant_id, $celebrant_prenom, $celebrant_nom)=$Celebrant_array;
			if ($row['Celebrant_id'] == $celebrant_id AND $celebrant_id != 0 ){
				$Celebrant_trouve = '';
			}
			$Item = $Item + 1;
		}
		
		foreach ($Liste_Celebrants as $Celebrant_array){
			list($celebrant_id, $celebrant_prenom, $celebrant_nom)=$Celebrant_array;
			if ($row['Celebrant_id'] == $celebrant_id ){
				echo '<option value='.$celebrant_id.' selected="selected">'.$celebrant_prenom.' '.$celebrant_nom.'</option>';
			} else {
				echo '<option value='.$celebrant_id.'>'.$celebrant_prenom.' '.$celebrant_nom.'</option>';
			}
		}
		if ($row['Celebrant_id'] == -1 ) {
			echo '<option value=-1 selected="selected">C�l�brant Ext�rieur</option>';
		} else {
			echo '<option value=-1 ">C�l�brant Ext�rieur</option>';
		}
		echo '</SELECT>';
		echo ' ';
		echo '<INPUT type=text name=Autre_Celebrant placeholder="Autre c�l�brant de la liste" '.$Celebrant_trouve .' size="40" maxlength="40" '.$BloquerAcces.'>';
	
	
		echo '</TD></TR>';
	
		// Enfants
		echo '<TR><TD bgcolor="#eeeeee" valign="top"><B><FONT SIZE="2">Enfant:</FONT></B></TD>';
		echo '<TD bgcolor="#eeeeee" colspan="2" valign="bottom">';
		// Enfants en commun
		if ( $LUI_id > 0 && $ELLE_id > 0 ) {
			$requeteEnfants = 'SELECT T0.id, T0.`Nom`, T0.`Prenom`, T0.`Nom`, T0.`Naissance` FROM `Individu` T0 WHERE T0.`Mere_id`='.$ELLE_id.' AND T0.`Pere_id`='.$LUI_id.' ORDER BY Naissance';
			$debug = False;
			pCOM_DebugAdd($debug, 'Mariage:edit - requeteEnfants03='.$requeteEnfants);
			$TitreLigne ='<FONT SIZE="2">Enfant(s) : </FONT>';
			$resultListEnfants = mysqli_query($eCOM_db, $requeteEnfants);
			while( $ListEnfants = mysqli_fetch_assoc( $resultListEnfants )) {
				echo $TitreLigne;
				$TitreLigne = "";
				Display_Photo("", $ListEnfants['Prenom'], $ListEnfants['id'], "1");
				if (strftime("%d/%m/%y", fCOM_sqlDateToOut($ListEnfants['Naissance'])) != "01/01/70" ) {
					$birthDate = explode("-", $ListEnfants['Naissance']);
					$Age = (date("md", date("U", mktime(0, 0, 0, $birthDate[1], $birthDate[2], $birthDate[0]))) > date("md") ? ((date("Y")-$birthDate[0])-1):(date("Y")-$birthDate[0]));
					//$Prenom= $Prenom." ($Age ans)";
				
					echo '<FONT SIZE="1">('.$Age.' ans) </FONT>';
				} else {
					echo '</A><FONT SIZE="1"> - </FONT>';
				}
			}
			if ($TitreLigne == "") {
				echo '<BR>';
			}
		}
		echo '<input type=text name="NEnfant" placeholder="Ex: 0 ou G(2001) F(2002)" value ="'.$row['Enfant'].'" size="40" maxlength="40" '.$BloquerAcces.'>';
		echo '</TD>';
	
	
		// Commentaire ==================
		echo '<TR>';
		if ($_SERVER['PHP_AUTH_USER'] != "sacristie" || fCOM_Get_Autorization( $Activite_id ) >= 20) {
			echo '<TD colspan="2" bgcolor="#eeeeee" VALIGN=TOP><B><FONT SIZE="2">Commentaires:</FONT></B><BR>';
			echo '<textarea cols=65 rows=6 name="Commentaire" maxlength="350" value ="'.$row['Commentaire'].'">'.$row['Commentaire'].'</textarea></TD>';
		}
	
		// Participation financi�re =============================
		if ($_SERVER['USER'] <= 2 || fCOM_Get_Autorization( $_SESSION["Activite_id"] )>= 30) { 
			echo '<TD bgcolor="#eeeeee"><b><FONT SIZE="2">Participation financi�re:</FONT></b><br>';
			echo '<input style="text-align:right" type=text name=Finance_total value ="'.$row['Finance_total'].'" size="5" maxlength="5" '.$BloquerAcces.' value=""><FONT SIZE="2"> Euros</FONT>';
			echo '<BR><B><FONT SIZE="1">Finance commentaires:</FONT></B><br>';
			echo '<textarea cols=25 rows=4 name="Finance_commentaire" maxlength="100" value ="'.$row['Finance_commentaire'].'">'.$row['Finance_commentaire'].'</textarea>';
			echo '</TD>';
		}
	}
	echo '</TR>';
	
	echo '<TR><TD></TD><TD>';
	echo '<input type=hidden name=id value="'.$id.'">';
	
	if ( $LUI_id > 0 && $ELLE_id > 0 ) {
		if ($_SERVER['USER'] <= 3 || fCOM_Get_Autorization( $_SESSION["Activite_id"] )>= 20) {
			echo '<br><div align="center"><input type="submit" name="edit" value="Enregistrer">';
			echo '<input type="reset" name="Reset" value="Reset">';
		}
		if ($_SERVER['USER'] <= 2 || fCOM_Get_Autorization( $_SESSION["Activite_id"] )>= 30) {
			echo '<input type="submit" name="delete_fiche_fiance" value="D�truire la fiche">';
		}
	}
	echo '</TD>';
	echo '</TR></TABLE>';
	echo '</FORM>';
	echo '</CENTER>';

	fCOM_address_bottom();
	mysqli_close($eCOM_db);
	exit(); 
}



function Sauvegarder_fiche_fiance ()// $id, $DateMariage, $Heure, $Minute, $Status, $Prem_Accueil_id, $Celebrant, $Autre_Celebrant, $LMariage, $AutreLMariage, $LConfession, $LUI_Acte_Naissance, $LUI_Acte_Bapteme, $LUI_Lettre_Intention, $EConfession, $ELLE_Acte_Naissance, $ELLE_Acte_Bapteme, $ELLE_Lettre_Intention, $Finance_total, $Finance_commentaire, $AnneeSession, $NEnfant, $Commentaire)
{
	Global $eCOM_db;
	$debug = False;
	
	pCOM_DebugInit($debug);
	pCOM_DebugAdd($debug, "Mariage:Sauvegarder_fiche_fiance - id = ".$_POST['id']);
	pCOM_DebugAdd($debug, "Mariage:Sauvegarder_fiche_fiance - Session = ".$_POST['AnneeSession']);
	pCOM_DebugAdd($debug, "Mariage:Sauvegarder_fiche_fiance - Prem_Accueil_id = ".$_POST['Prem_Accueil_id']);
	pCOM_DebugAdd($debug, "Mariage:Sauvegarder_fiche_fiance - Autre_Celebrant = ".$_POST['Autre_Celebrant']);

	if ( isset($_POST['id']) AND $_POST['id'] > 0 ) {$id = $_POST['id'];} else { $id = 0;}

	if (fCOM_Get_Autorization( $_SESSION["Activite_id"] )>= 30 AND $id > 0 ) {

		if (isset($_POST['DateMariage']) AND $_POST['DateMariage'] != "" AND
			isset($_POST['heure']) AND $_POST['heure'] != "" AND
			isset($_POST['minute']) AND $_POST['minute'] != "" ) {
			$DateTimeValue = fCOM_getSqlDate($_POST['DateMariage'],$_POST['heure'],$_POST['minute'],0);
		} else {
			$DateTimeValue = "";
		}
		pCOM_DebugAdd($debug, "Mariage:Sauvegarder_fiche_fiance - DateTimeValue=".$DateTimeValue);			

		//if ($Celebrant == "C�l�brant Ext�rieur") { $Celebrant = $Autre_Celebrant;}
		//if ($LMariage == "Hors Paroisse") { $LMariage = $AutreLMariage; }
		if ( isset($_POST['LMariage']) AND $_POST['LMariage'] != "" ) 
			{ $LMariage = $_POST['LMariage']; } else { $LMariage = ""; }
		if ( isset($_POST['AutreLMariage']) AND $_POST['AutreLMariage'] != "" ) 
			{ $AutreLMariage = $_POST['AutreLMariage']; } else { $AutreLMariage = ""; }
		if ($AutreLMariage != "") { $LMariage = $AutreLMariage; }
		
		if ( isset($_POST['LUI_Acte_Naissance']) AND $_POST['LUI_Acte_Naissance'] == "on") 
			{ $LActeNaissance = 1; } else { $LActeNaissance = 0; }
		if ( isset($_POST['LUI_Acte_Bapteme']) AND $_POST['LUI_Acte_Bapteme'] == "on") 
			{ $LActeBapteme = 1;	} else { $LActeBapteme = 0;	}
		if ( isset($_POST['LUI_Lettre_Intention']) AND $_POST['LUI_Lettre_Intention'] == "on") 
			{ $LLettreIntention = 1;	} else { $LLettreIntention = 0;	}			
			
		if ( isset($_POST['LConfession']) AND $_POST['LConfession'] != "" ) 
			{ $LConfession = $_POST['LConfession']; } else { $LConfession = ""; }

		if ( isset($_POST['ELLE_Acte_Naissance']) AND $_POST['ELLE_Acte_Naissance'] == "on") 
			{$EActeNaissance = 1; } else { $EActeNaissance = 0; }
		if ( isset($_POST['ELLE_Acte_Bapteme']) AND $_POST['ELLE_Acte_Bapteme'] == "on") 
			{ $EActeBapteme = 1; } else { $EActeBapteme = 0; }
		if ( isset($_POST['ELLE_Lettre_Intention']) AND $_POST['ELLE_Lettre_Intention'] == "on") 
			{ $ELettreIntention = 1; } else { $ELettreIntention = 0; }			
			
		if ( isset($_POST['AnneeSession']) AND $_POST['AnneeSession'] != "" ) 
			{ $AnneeSession = $_POST['AnneeSession']; } else { $AnneeSession = ""; }

		if ( isset($_POST['EConfession']) AND $_POST['EConfession'] != "" ) 
			{ $EConfession = $_POST['EConfession']; } else { $EConfession = ""; }

		if ( isset($_POST['NEnfant']) AND $_POST['NEnfant'] != "" ) 
			{ $NEnfant = $_POST['NEnfant']; } else { $NEnfant = ""; }

		if ( isset($_POST['Status']) ) { $Status = $_POST['Status']; } else { $Status = ""; }
		if ( isset($_POST['Prem_Accueil_id']) AND $_POST['Prem_Accueil_id'] > 0 ) 
			{ $Prem_Accueil_id = $_POST['Prem_Accueil_id']; } else { $Prem_Accueil_id = 0; }
		if ( isset($_POST['Celebrant']) AND $_POST['Celebrant'] > 0 ) 
			{ $Celebrant = $_POST['Celebrant']; } else { $Celebrant = 0; }
		if ( isset($_POST['Autre_Celebrant']) AND $_POST['Autre_Celebrant'] != "" ) 
			{ $Autre_Celebrant = $_POST['Autre_Celebrant']; } else { $Autre_Celebrant = ""; }
		
		if ( isset($_POST['Finance_total']) AND $_POST['Finance_total'] > 0 ) 
			{ $Finance_total = $_POST['Finance_total']; } else { $Finance_total = 0; }
		if ( isset($_POST['Finance_commentaire']) AND $_POST['Finance_commentaire'] != "" ) 
			{ $Finance_commentaire = $_POST['Finance_commentaire']; } else { $Finance_commentaire = ""; }

		if ( isset($_POST['AnneeSession']) AND $_POST['AnneeSession'] != "" ) 
			{ $AnneeSession = $_POST['AnneeSession']; } else { $AnneeSession = ""; }
		if ( isset($_POST['Commentaire']) AND $_POST['Commentaire'] != "" ) 
			{ $Commentaire = $_POST['Commentaire']; } else { $Commentaire = ""; }

		//if ($Celebrant == 999 ) {
		//	$Celebrant = 0;
		//	if ($Autre_Celebrant == "" ) {
		//		$Autre_Celebrant = "C�l�brant Ext�rieur";
		//	}
		//} else
		if ($Celebrant > 0) {
			$Autre_Celebrant = "";
		}
			
		mysqli_query($eCOM_db, 'UPDATE Fianc�s SET Lieu_mariage="'.$LMariage.'" WHERE id='.$id.' ') or die (mysqli_error($eCOM_db));
		if ($DateTimeValue != NULL) {
			mysqli_query($eCOM_db, 'UPDATE Fianc�s SET Date_mariage = "'.$DateTimeValue.'" WHERE id='.$id.' ') or die (mysqli_error($eCOM_db));
		}
		mysqli_query($eCOM_db, 'UPDATE Fianc�s SET Status="'.$Status.'" WHERE id='.$id.' ') or die (mysqli_error($eCOM_db));
		mysqli_query($eCOM_db, 'UPDATE Fianc�s SET Prem_Accueil_id='.$Prem_Accueil_id.' WHERE id='.$id.' ') or die (mysqli_error($eCOM_db));
		mysqli_query($eCOM_db, 'UPDATE Fianc�s SET Celebrant_id='.$Celebrant.' WHERE id='.$id.' ') or die (mysqli_error($eCOM_db));
		mysqli_query($eCOM_db, 'UPDATE Fianc�s SET Celebrant="'.$Autre_Celebrant.'" WHERE id='.$id.' ') or die (mysqli_error($eCOM_db));
		mysqli_query($eCOM_db, 'UPDATE Fianc�s SET LUI_Extrait_Naissance="'.$LActeNaissance.'" WHERE id='.$id.' ') or die (mysqli_error($eCOM_db));
		mysqli_query($eCOM_db, 'UPDATE Fianc�s SET LUI_Extrait_Bapteme="'.$LActeBapteme.'" WHERE id='.$id.' ') or die (mysqli_error($eCOM_db));
		mysqli_query($eCOM_db, 'UPDATE Fianc�s SET LUI_Lettre_Intention="'.$LLettreIntention.'" WHERE id='.$id.' ') or die (mysqli_error($eCOM_db));

		mysqli_query($eCOM_db, 'UPDATE Fianc�s SET ELLE_Extrait_Naissance="'.$EActeNaissance.'" WHERE id='.$id.' ') or die (mysqli_error($eCOM_db));
		mysqli_query($eCOM_db, 'UPDATE Fianc�s SET ELLE_Extrait_Bapteme="'.$EActeBapteme.'" WHERE id='.$id.' ') or die (mysqli_error($eCOM_db));
		mysqli_query($eCOM_db, 'UPDATE Fianc�s SET ELLE_Lettre_Intention="'.$ELettreIntention.'" WHERE id='.$id.' ') or die (mysqli_error($eCOM_db));

		mysqli_query($eCOM_db, 'UPDATE Fianc�s SET Finance_total="'.$Finance_total.'" WHERE id='.$id.' ') or die (mysqli_error($eCOM_db));
		mysqli_query($eCOM_db, 'UPDATE Fianc�s SET Finance_commentaire="'.$Finance_commentaire.'" WHERE id='.$id.' ') or die (mysqli_error($eCOM_db));
		mysqli_query($eCOM_db, 'UPDATE Fianc�s SET Session="'.$AnneeSession.'" WHERE id='.$id.' ') or die (mysqli_error($eCOM_db));
		mysqli_query($eCOM_db, 'UPDATE QuiQuoi SET Session="'.$AnneeSession.'" WHERE Activite_id=2 AND Engagement_id='.$id.' ') or die (mysqli_error($eCOM_db));		
		//}
		mysqli_query($eCOM_db, 'UPDATE Fianc�s SET Enfant="'.$NEnfant.'" WHERE id='.$id.' ') or die (mysqli_error($eCOM_db));
		mysqli_query($eCOM_db, 'UPDATE Fianc�s SET Commentaire="'.$Commentaire.'" WHERE id='.$id.' ') or die (mysqli_error($eCOM_db));
		mysqli_query($eCOM_db, 'UPDATE Fianc�s SET MAJ="'.date("Y-m-d H:i:s").'" WHERE id='.$id.' ') or die (mysqli_error($eCOM_db));
		
		// Sauvegarde de la confession - LUI
		$requete2 = 'SELECT T1.`id` 
				FROM QuiQuoi T0 
				LEFT JOIN Individu T1 ON T1.`id`=T0.`Individu_id`
				WHERE T0.`Activite_id`=2 AND T0.`QuoiQuoi_id`=1 AND T0.`Engagement_id`='.$id.' AND T1.`Sex`="M"';
		$result2 = mysqli_query($eCOM_db, $requete2);
		if (mysqli_num_rows($result2) > 0) {
			$row2 = mysqli_fetch_assoc($result2);
			if ($row2['id'] > 0) {
				mysqli_query($eCOM_db, 'UPDATE Individu SET Confession="'.$LConfession.'" WHERE id='.$row2['id'].' ') or die (mysqli_error($eCOM_db));		
			}
		}
		
		// Sauvegarde de la confession - ELLE
		$requete2 = 'SELECT T1.`id` 
				FROM QuiQuoi T0 
				LEFT JOIN Individu T1 ON T1.`id`=T0.`Individu_id`
				WHERE T0.`Activite_id`=2 AND T0.`QuoiQuoi_id`=1 AND T0.`Engagement_id`='.$id.' AND T1.`Sex`="F"';
		$result2 = mysqli_query($eCOM_db, $requete2);
		if (mysqli_num_rows($result2) > 0) {
			$row2 = mysqli_fetch_assoc($result2);
			if ($row2['id'] > 0) {
				mysqli_query($eCOM_db, 'UPDATE Individu SET Confession="'.$EConfession.'" WHERE id='.$row2['id'].' ') or die (mysqli_error($eCOM_db));
			}
		}
		
		return (0);
		
	} else {
		return (-1);
	}
}


if ( isset( $_POST['edit'] ) AND $_POST['edit']=="Enregistrer") {
//if ($edit) {
	
	$debug = false;
	
	$check_LUI_Acte_Naissance = isset($_POST['LUI_Acte_Naissance']) ? $_POST['LUI_Acte_Naissance'] : "off" ;
	$check_LUI_Acte_Bapteme = isset($_POST['LUI_Acte_Bapteme']) ? $_POST['LUI_Acte_Bapteme'] : "off" ;	
	$check_LUI_Lettre_Intention = isset($_POST['LUI_Lettre_Intention']) ? $_POST['LUI_Lettre_Intention'] : "off" ;	
	$check_ELLE_Acte_Naissance = isset($_POST['ELLE_Acte_Naissance']) ? $_POST['ELLE_Acte_Naissance'] : "off" ;	
	$check_ELLE_Acte_Bapteme = isset($_POST['ELLE_Acte_Bapteme']) ? $_POST['ELLE_Acte_Bapteme'] : "off" ;	
	$check_ELLE_Lettre_Intention = isset($_POST['ELLE_Lettre_Intention']) ? $_POST['ELLE_Lettre_Intention'] : "off" ;	
	
	$retour = Sauvegarder_fiche_fiance (); // $_POST['id'], $_POST['DateMariage'], $_POST['heure'], $_POST['minute'], $_POST['Status'], $_POST['Prem_Accueil_id'], $_POST['Celebrant'], $_POST['Autre_Celebrant'], $_POST['LMariage'], $_POST['AutreLMariage'], $_POST['LConfession'], $check_LUI_Acte_Naissance, $check_LUI_Acte_Bapteme, $check_LUI_Lettre_Intention, $_POST['EConfession'], $check_ELLE_Acte_Naissance, $check_ELLE_Acte_Bapteme, $check_ELLE_Lettre_Intention, $_POST['Finance_total'], $_POST['Finance_commentaire'], $_POST['AnneeSession'], $_POST['NEnfant'], $_POST['Commentaire']);
	if ($retour == 0) {
		echo '<B><CENTER><FONT face="verdana" size="2" color=green>Fiche enregistr�e avec succ�s</FONT></CENTER></B>';
	} else {
		echo '<B><CENTER><FONT face="verdana" size="2" color=red>Impossible d\'enregistrer la fiche</FONT></CENTER></B>';
	}
	echo '<META http-equiv="refresh" content="0; URL='.$_SESSION["RetourPage"].'">';
	exit();
}

//delete part 1
if ( isset( $_POST['delete_fiche_fiance'] ) AND $_POST['delete_fiche_fiance']=="D�truire la fiche") {
//if ($delete_fiche_fiance) {
	Global $eCOM_db;
	$debug = false;

	$requete = 'SELECT T0.`id`,
				(SELECT Concat(T1.`Prenom`, " ",T1.`Nom`) FROM QuiQuoi T2 
					LEFT JOIN Individu T1 ON T2.`Individu_id`=T1.`id` WHERE T2.`Activite_id`=2 AND T2.`QuoiQuoi_id`=1 AND T1.`Sex`="M" AND T2.`Engagement_id`='.$_POST['id'].') AS LUI_Name, 
				(SELECT Concat(T1.`Prenom`, " ",T1.`Nom`) FROM QuiQuoi T2 
					LEFT JOIN Individu T1 ON T2.`Individu_id`=T1.`id` WHERE T2.`Activite_id`=2 AND T2.`QuoiQuoi_id`=1 AND T1.`Sex`="F" AND T2.`Engagement_id`='.$_POST['id'].') AS ELLE_Name
				FROM Fianc�s T0 WHERE T0.`id`='.$_POST['id'].' '; 
	pCOM_DebugInit($debug);
	pCOM_DebugAdd($debug, "Mariage:delete_fiche_fiance - id = ".$_POST['id']);
	pCOM_DebugAdd($debug, "Mariage:delete_fiche_fiance - Requete = ".$requete);

	$result = mysqli_query($eCOM_db, $requete);
	//debug('Enregistrements dans la table <i>personne</i> [ <FONT COLOR=GREEN>' . mysqli_num_rows( $result ) . '</FONT> ]<BR><BR>');

	while($row = mysqli_fetch_assoc($result))
	{
		address_top();
		echo '<TABLE WIDTH="100%" BORDER="0" CELLSPACING="1" CELLPADDING="4" BGCOLOR="#FFFFFF">';
		echo '<TR BGCOLOR="#F7F7F7"><TD><FONT FACE="Verdana" SIZE="2"><B>Destruction d\'une fiche fianc�e</B><BR></TD></TR>';
		echo '<TR><TD BGCOLOR="#EEEEEE">';
		echo '<FONT FACE="Verdana" size="2" >Etes-vous certain de vouloir d�truire cette fiche : <BR><BR>';
		echo $row['ELLE_Name'].' et '.$row['LUI_Name'].' ?</FONT>';
		echo '<P><FORM method=post action='.$_SERVER['PHP_SELF'].'>';
		echo '<input type="submit" name="delete_fiche_fiance_confirme" value="Oui">';
		echo '<input type="submit" name="" value="Non">';
		echo '<input type="hidden" name="id" value='.$_POST['id'].'>';
		echo '</FORM></TD></TR>';
		fCOM_address_bottom();
		mysqli_close($eCOM_db);
		exit();	
	}
}

//delete part 2
if ( isset( $_POST['delete_fiche_fiance_confirme'] ) AND $_POST['delete_fiche_fiance_confirme']=="Oui") {
//if ($delete_fiche_fiance_confirme) {
	Global $eCOM_db;
	$debug = false;
	pCOM_DebugAdd($debug, "Mariage:delete_fiche_fiance_confirme - id=".$id);
	$requete = 'SELECT * FROM Fianc�s WHERE id='.$_POST['id'].' '; 
	pCOM_DebugAdd($debug, "Mariage:delete_fiche_fiance_confirme - requete01=".$requete);
	$result = mysqli_query($eCOM_db, $requete);
    pCOM_DebugAdd($debug, "Mariage:delete_fiche_fiance_confirme - Enreg dans la table ".mysqli_num_rows( $result ));

	//while($row = mysql_fetch_row($result))
	if (mysqli_num_rows( $result ) == 1)
	{ 
        $requete = 'UPDATE Fianc�s SET Actif=0 WHERE id='.$_POST['id'].' '; 
		pCOM_DebugAdd($debug, "Mariage:delete_fiche_fiance_confirme - requete02=".$requete);
        $result = mysqli_query($eCOM_db, $requete); 
		if (!$result) {
			echo 'Impossible d\'ex�cuter la requ�te : ' . mysqli_error($eCOM_db);
			mysqli_close($eCOM_db);
			exit;
        }
		mysqli_query($eCOM_db, 'UPDATE Fianc�s SET MAJ="'.date("Y-m-d H:i:s").'" WHERE id='.$_POST['id'].' ') or die (mysqli_error($eCOM_db));
		echo '<B><CENTER><FONT face="verdana" size="2" color=green>Fiche d�truite avec succ�s</FONT></CENTER></B>';
	}
}


///////////////////////////////////////////////////////////////////
// A RETRAVAILLER
////////////////////////////////////////////////////////////////////

if ( isset( $_POST['Selectionner_Paroissien'] ) AND ( 
	$_POST['Selectionner_Paroissien']=="S�lectionner le fianc�" OR 
	$_POST['Selectionner_Paroissien']=="Le fianc�" OR 
	$_POST['Selectionner_Paroissien']=="S�lectionner la fianc�e" OR 
	$_POST['Selectionner_Paroissien']=="La fianc�e" OR 
	$_POST['Selectionner_Paroissien']=="Accompagnateur(s)" )) {
	
	if  ( $_POST['Selectionner_Paroissien'] == "Accompagnateur(s)" ) {
		$retour = Sauvegarder_fiche_fiance (); // $_POST['Fiche_id'], $_POST['DateMariage'], $_POST['heure'], $_POST['minute'], $_POST['Status'], $_POST['Prem_Accueil_id'], $_POST['Celebrant'], $_POST['Autre_Celebrant'], $_POST['LMariage'], $_POST['AutreLMariage'], $_POST['LConfession'], $_POST['LUI_Acte_Naissance'], $_POST['LUI_Acte_Bapteme'], $_POST['LUI_Lettre_Intention'], $_POST['EConfession'], $_POST['ELLE_Acte_Naissance'], $_POST['ELLE_Acte_Bapteme'], $_POST['ELLE_Lettre_Intention'], $_POST['Finance_total'], $_POST['Finance_commentaire'], $_POST['AnneeSession'], $_POST['NEnfant'], $_POST['Commentaire']);
		if ($retour == 0) {
			echo '<B><CENTER><FONT face="verdana" size="2" color=green>Fiche provisoirement enregistr�e avec succ�s</FONT></CENTER></B>';
		} else {
			echo '<B><CENTER><FONT face="verdana" size="2" color=red>Impossible d\'enregistrer provisoirement la fiche</FONT></CENTER></B>';
		}
	}
	$debug = false;
	pCOM_DebugAdd($debug, "Mariage:Selectionner_Paroissien -> ".$_POST['Selectionner_Paroissien']);
	pCOM_DebugAdd($debug, "Mariage:Selectionner_Paroissien id=".$_POST['Fiche_id']);
	
	if ( $_POST['Selectionner_Paroissien']=="S�lectionner le fianc�" OR 
		 $_POST['Selectionner_Paroissien']=="Le fianc�" ) {
		$_SESSION["Action"] = sprintf("%d\n%d\n%s\n%s", 1, $_POST['Fiche_id'], "T1.`Sex`='M'", "Fianc�s");
		$return = Selectionner_Paroissien_Afficher("S�lectionner le fianc�", 1, $_POST['id'], "T1.`Sex`='M'", False, "QuiQuoi", $_POST['id'] );
		
	} elseif  ( $_POST['Selectionner_Paroissien']=="S�lectionner la fianc�e" OR 
				$_POST['Selectionner_Paroissien']=="La fianc�e" ) {
		$_SESSION["Action"] = sprintf("%d\n%d\n%s\n%s", 1, $_POST['Fiche_id'], "T1.`Sex`='F'", "Fianc�s");
		$return = Selectionner_Paroissien_Afficher("S�lectionner la fianc�e", 1, $_POST['id'], "T1.`Sex`='F'", False, "QuiQuoi", $_POST['id'] );
		
	} elseif  ( $_POST['Selectionner_Paroissien'] == "Accompagnateur(s)" ) {
		$_SESSION["Action"] = sprintf("%d\n%d\n%s\n%s", 2, $_POST['Fiche_id'], "", "Fianc�s");
		$return = Selectionner_Paroissien_Afficher("S�lectionner l'accompagnateur", 2, $_POST['id'], "T0.`Activite_id`=2 AND T0.`Engagement_id`=0 AND T0.`QuoiQuoi_id`=2 AND T0.`Session`=".$SessionEnCours." ", True, "QuiQuoi", $_POST['id'] );
	}

}


Function Selectionner_Paroissien_Afficher($Title, $pQuoiQuoi_id, $Engagement_id, $SqlWhere, $Inscription, $Database, $Champs ) 
{
	Global $eCOM_db;
	address_top();
	
	$debug = False;
	pCOM_DebugAdd($debug, "Mariage:Selectionner_Paroissien_Afficher - Title=".$Title);
	pCOM_DebugAdd($debug, "Mariage:Selectionner_Paroissien_Afficher - pQuoiQuoi_id =".$pQuoiQuoi_id);
	pCOM_DebugAdd($debug, "Mariage:Selectionner_Paroissien_Afficher - Engagement_id =".$Engagement_id);
	pCOM_DebugAdd($debug, "Mariage:Selectionner_Paroissien_Afficher - SqlWhere =".$SqlWhere);
	pCOM_DebugAdd($debug, "Mariage:Selectionner_Paroissien_Afficher - Inscription =".$Inscription);
	pCOM_DebugAdd($debug, "Mariage:Selectionner_Paroissien_Afficher - Database =".$Database);
	pCOM_DebugAdd($debug, "Mariage:Selectionner_Paroissien_Afficher - Champs =".$Champs);

	echo '<TABLE WIDTH="100%" BORDER="0" CELLSPACING="1" CELLPADDING="4" BGCOLOR="#FFFFFF">';
	pCOM_DebugAdd($debug, "Mariage:Selectionner_Paroissien_Afficher - pQuoiQuoi_id =".$pQuoiQuoi_id);
	echo '<TR BGCOLOR="#F7F7F7"><TD><FONT FACE="Verdana" SIZE="2"><B>'.$Title.'</B><BR></TD><TD></TD><TD></TD></TR>';
	echo '<TR><TD BGCOLOR="#EEEEEE">';
	echo '<FONT FACE="Verdana" size="2" ><BR>';

	$Activite_id=$_SESSION["Activite_id"];

	if ( $Inscription == True ) { // Inscription d�j� r�alis�e, ou liste d�j� d�finie
		$requete = 'SELECT T1.`id` AS id, T1.`Prenom` AS Prenom, T1.`Nom` AS Nom, T0.`Detail` AS Classe, T1.`Actif` 
					FROM `QuiQuoi` T0 
					LEFT JOIN `Individu` T1 ON T0.`Individu_id`=T1.`id` 
					WHERE T1.`Actif`=1 AND '.$SqlWhere.'
					GROUP BY T1.`id` 
					ORDER BY T1.`Nom`, T1.`Prenom`';
	} else {
		$requete = 'SELECT T1.`id`, T1.`Prenom`, T1.`Nom`, T1.`Naissance`, T1.`MAJ` 
					FROM Individu T1
					WHERE T1.`Actif`=1 AND '.$SqlWhere.'
					ORDER BY MAJ DESC, T1.`Nom`, T1.`Prenom` 
					LIMIT 0, 10';
					
		pCOM_DebugAdd($debug, "Mariage:Selectionner_Paroissien_Afficher - requete =".$requete);
		
		echo '<TABLE>';
		$trcolor = "#EEEEEE";
		echo "<TR><TD colspan=2><FONT face=verdana size=2>Derniers paroissiens modifi�s</FONT></TD></TR>";
		echo '<TH bgcolor='.$trcolor.'><font face=verdana size=2>S�lectionner</font></TH>';
		echo '<TH bgcolor='.$trcolor.'><font face=verdana size=2>Pr�nom</font></TH>';
		echo '<TH bgcolor='.$trcolor.'><font face=verdana size=2>Nom</font></TH>';

		$result = mysqli_query($eCOM_db, $requete);
		while($row = mysqli_fetch_assoc($result)) {
			$trcolor = usecolor();
			echo '<TR>'; 
			echo '<TD bgcolor='.$trcolor.'><CENTER><A HREF='.$_SERVER['PHP_SELF'].'?action=DeclarerBaseQuiQuoi&Qui_id='.$row['id'].' TITLE="'.$Title.'"><img src="images/plus.gif" border=0 alt="Add Record"></a></TD>  ';
			echo '<TD bgcolor='.$trcolor.'><FONT face=verdana size=2>'.$row['Prenom'].'</FONT></TD>';
			echo '<TD bgcolor='.$trcolor.'><FONT face=verdana size=2>'.$row['Nom'].'</FONT></TD>';
			echo '</TR>'; 
		}
		echo '<TR><TD colspan=2><FONT face=verdana size=2><BR>Tous les paroissiens</FONT></TD></TR>';
		echo '</TABLE></FONT>';
		$requete = 'SELECT T1.`id`, T1.`Prenom`, T1.`Nom`, T1.`Naissance` 
					FROM Individu T1
					WHERE T1.`Actif`=1 AND '.$SqlWhere.'
					ORDER by T1.`Nom`, T1.`Prenom` ';
	}
	
	echo '<TABLE>';
	$trcolor = "#EEEEEE";
	echo '<TH bgcolor='.$trcolor.'><font face=verdana size=2>S�lectionner</font></TH>';
	echo '<TH bgcolor='.$trcolor.'><font face=verdana size=2>Pr�nom</font></TH>';
	echo '<TH bgcolor='.$trcolor.'><font face=verdana size=2>Nom</font></TH>';

	$result = mysqli_query($eCOM_db, $requete);
	while($row = mysqli_fetch_assoc($result)) {
		$trcolor = usecolor();
		echo '<TR>'; 
		echo '<TD bgcolor='.$trcolor.'><CENTER><A HREF='.$_SERVER['PHP_SELF'].'?action=DeclarerBaseQuiQuoi&Qui_id='.$row['id'].' TITLE="'.$Title.'"><img src="images/plus.gif" border=0 alt="Add Record"></a></TD>  ';
		echo '<TD bgcolor='.$trcolor.'><FONT face=verdana size=2>'.$row['Prenom'].'</FONT></TD>';
		echo '<TD bgcolor='.$trcolor.'><FONT face=verdana size=2>'.$row['Nom'].'</FONT></TD>';
		echo '</TR>'; 
	}
	echo '</TABLE><BR></FONT>';
	fCOM_address_bottom();
	mysqli_close($eCOM_db);
	exit;
}



if ( isset( $_GET['action'] ) AND $_GET['action']=="DeclarerBaseQuiQuoi") {
//if ($action == "DeclarerBaseQuiQuoi") { 
	Global $eCOM_db;
	list($QuoiQuoi_id, $Engagement_id, $SqlWhere, $Database) = sscanf($_SESSION["Action"], "%d\n%d\n%s\n%s");
	$debug=False;
	$Activite_id = $_SESSION["Activite_id"];
	pCOM_DebugAdd($debug, "Mariage:Action=DeclarerBaseQuiQuoi -> Action= ".$_SESSION["Action"]);
	pCOM_DebugAdd($debug, "Mariage:Action=DeclarerBaseQuiQuoi -> Activite_id =".$Activite_id);
	pCOM_DebugAdd($debug, "Mariage:Action=DeclarerBaseQuiQuoi -> QuoiQuoi_id =".$QuoiQuoi_id);
	pCOM_DebugAdd($debug, "Mariage:Action=DeclarerBaseQuiQuoi -> Engagement_id =".$Engagement_id);
	pCOM_DebugAdd($debug, "Mariage:Action=DeclarerBaseQuiQuoi -> SqlWhere =".$SqlWhere);
	pCOM_DebugAdd($debug, "Mariage:Action=DeclarerBaseQuiQuoi -> Qui_id =".$_GET['Qui_id']);
	pCOM_DebugAdd($debug, "Mariage:Action=DeclarerBaseQuiQuoi -> Database =".$Database);
	pCOM_DebugAdd($debug, "Mariage:Action=DeclarerBaseQuiQuoi -> Session =".$_SESSION["Session"]);
	pCOM_DebugAdd($debug, "Mariage:Action=DeclarerBaseQuiQuoi -> RetourPageCourante =".$_SESSION["RetourPageCourante"]);
	
	if ($_GET['Qui_id'] > 0 & $Engagement_id > 0) {
		//$Qui = stripAccents($Qui);

		// verifier si la fiche existe d�j�
		$requete = 'SELECT T0.`id` 
		FROM QuiQuoi T0
		LEFT JOIN Individu T1 ON T0.`Individu_id`=T1.`id`
		WHERE T0.`Activite_id`='.$Activite_id.' AND T0.`QuoiQuoi_id`='.$QuoiQuoi_id.' AND T0.`Engagement_id`='.$Engagement_id.' AND '.$SqlWhere.' ';
		pCOM_DebugAdd($debug, "Mariage:Action=DeclarerBaseQuiQuoi -> requete=".$requete);
		
		$result = mysqli_query($eCOM_db, $requete);
		$num_total = mysqli_num_rows($result);
		if ( $num_total > 0 ) {
			pCOM_DebugAdd($debug, "Mariage:Action=DeclarerBaseQuiQuoi -> Num_Total=".$num_total);
			$row = mysqli_fetch_assoc($result);
			mysqli_query($eCOM_db, 'UPDATE QuiQuoi SET Individu_id='.$_GET['Qui_id'].' WHERE id='.$row['id'].' ') or die (mysqli_error($eCOM_db));
			
		} else {
			pCOM_DebugAdd($debug, "Mariage:Action=DeclarerBaseQuiQuoi -> Num_Total=0 INSERT");
			mysqli_query($eCOM_db, 'INSERT INTO QuiQuoi (Individu_id, Activite_id, Engagement_id, QuoiQuoi_id, Session) VALUES ('.$_GET['Qui_id'].','.$Activite_id.','.$Engagement_id.','.$QuoiQuoi_id.', "'.$_SESSION["Session"].'")') or die (mysqli_error($eCOM_db));
		}

		mysqli_query($eCOM_db, 'UPDATE Fianc�s SET MAJ="'.date("Y-m-d H:i:s").'" WHERE id='.$Engagement_id.' ') or die (mysqli_error($eCOM_db));
	}
	$_SESSION["Action"]="";
	echo '<META http-equiv="refresh" content="0; URL='.$_SESSION["RetourPageCourante"].'">';
	mysqli_close($eCOM_db);
	exit;
}


/////////////////////////////////////////////////
// FIN DE A RETRAVAILLER
////////////////////////////////////////////////


if ( isset( $_GET['action'] ) AND $_GET['action']=="RetirerAccompagnateur") {
//if ($action == "RetirerAccompagnateur") {
	Global $eCOM_db;
	$debug = False;
	pCOM_DebugInit($debug);
	pCOM_DebugAdd($debug, "Mariage:RetirerAccompagnateur - id = ".$_GET['id']);
	pCOM_DebugAdd($debug, "Mariage:RetirerAccompagnateur - Session = ".$AnneeSession);
	pCOM_DebugAdd($debug, "Mariage:RetirerAccompagnateur - Date Mariage = ".$_POST['DateMariage']);

	//$retour = Sauvegarder_fiche_fiance ( $id, $_POST['DateMariage'], $_POST['heure'], $_POST['minute'], $Status, $Celebrant, $Autre_Celebrant, $LMariage, $AutreLMariage, $LConfession, $LUI_Acte_Naissance, $LUI_Acte_Bapteme, $LUI_Lettre_Intention, $EConfession, $ELLE_Acte_Naissance, $ELLE_Acte_Bapteme, $ELLE_Lettre_Intention, $Finance_total, $Finance_commentaire, $AnneeSession, $NEnfant, $Commentaire);
	$debug=True;
	if ($_GET['Qui_id'] > 0 & $_GET['Invite_id'] > 0) {
		$Activite_id=$_SESSION["Activite_id"];
		$requete='DELETE FROM QuiQuoi WHERE Individu_id='.$_GET['Qui_id'].' AND Activite_id='.$Activite_id.' AND Engagement_id='.$_GET['Invite_id'].' AND QuoiQuoi_id=2';
		pCOM_DebugAdd($debug, "Mariage:RetirerAccompagnateur - requete_1 =".$requete);
		mysqli_query($eCOM_db, $requete)or die (mysqli_error($eCOM_db));
		$requete="UPDATE Fianc�s SET MAJ='".date("Y-m-d H:i:s")."' WHERE id=".$_GET['Invite_id'];
		pCOM_DebugAdd($debug, "Mariage:RetirerAccompagnateur - requete_2 =".$requete);
		mysqli_query($eCOM_db, $requete)or die (mysqli_error($eCOM_db));
	}
	echo '<META http-equiv="refresh" content="0; URL='.$_SESSION["RetourPageCourante"].'">';
	mysqli_close($eCOM_db);
	exit;
}


if ( isset( $_POST['upload_Photo_couple'] ) AND ( $_POST['upload_Photo_couple']=="Charger une autre photo..." OR $_POST['upload_Photo_couple']=="Charger une photo...")) {
//if ($upload_Photo_couple) {
	
	Global $eCOM_db;
	echo '<form method="POST" action="upload.php" enctype="multipart/form-data">';
	echo '<FONT color=green><h4>La taille maximum du fichier ne doit pas d�passer 50Ko<BR>';
	echo 'Veuillez ne pas mettre d\'accents ni d\'espace dans le nom de l\'image</font><BR>';
	echo 'Fichier (id='.$_POST['id'].') :  <BR></h4>';
	//<!-- On limite le fichier � 100Ko -->
	echo '<input type="hidden" name="MAX_FILE_SIZE" value="50000">';
	echo '<input type="file" name="avatar">';
	echo '<input type=hidden name=id value='.$_POST['id'].'>';
	echo '<input type=hidden name=fichier_target value='.$_POST['id'].'.jpg>';
	echo '<input type=hidden name=Activite value='.$_POST['Activite'].'>';
	echo '<input type="submit" name="envoyer" value="Envoyer le fichier"></form>';
	mysqli_close($eCOM_db);
	exit();
}
	
//======================================
// Vue accompagnateur
//======================================
if ( isset( $_GET['action'] ) AND $_GET['action']=="list_accomp") {
//if ($action == "list_accomp") {
	Global $eCOM_db;
	$debug = false;

	address_top();

	echo '<link rel="stylesheet" type="text/css" href="includes/Tooltip.css">';
	echo '<TABLE WIDTH="100%" BORDER="0" CELLSPACING="1" CELLPADDING="4" BGCOLOR="#FFFFFF">';
	echo '<TR BGCOLOR="#F7F7F7">';
	echo '<TD><FONT FACE="Verdana" SIZE="2"><B>Liste Accompagnateurs</B><BR>';
	echo '</TD></TR>';
	echo '<TR><TD BGCOLOR="#EEEEEE">';

	echo '<table>';
	$trcolor = "#EEEEEE";
	echo '<TH bgcolor='.$trcolor.'><font face=verdana size=2>Accompagnateurs</font></TH>';
	echo '<TH bgcolor='.$trcolor.'><font face=verdana size=2>Adresse</font></TH>';
	echo '<TH bgcolor='.$trcolor.'><font face=verdana size=2>T�l�phone</font></TH>';
	echo '<TH bgcolor='.$trcolor.'><font face=verdana size=2>e-mail</font></TH>';
	echo '<TH bgcolor='.$trcolor.'><font face=verdana size=2>Fianc�s</font></TH>';
	echo '<TH bgcolor='.$trcolor.'><font face=verdana size=2>Couverts</font></TH>';
	
	$Total_pers = 0;
	if ($_SESSION["Session"]=="All") {
		$ExtraRequete='';
	} else {
		$ExtraRequete='AND T0.`Session`='.$_SESSION["Session"].'';
	}


	$requete = '(SELECT GROUP_CONCAT(CONVERT(T1.`id`, CHAR(50)) ORDER BY T1.`Sex` DESC SEPARATOR "_") AS id_Accompagnateur, 
CONCAT(GROUP_CONCAT( DISTINCT T1.`Nom`), "<BR>",GROUP_CONCAT(T1.`Prenom` ORDER BY T1.`Sex` DESC SEPARATOR " et ")) AS Accompagnateur, 
T1.`Adresse` as Adresse, 
GROUP_CONCAT(DISTINCT T1.`Telephone` ORDER BY T1.`Sex` DESC SEPARATOR " ") as Telephone, 
GROUP_CONCAT(DISTINCT T1.`e_mail` ORDER BY T1.`Sex` DESC SEPARATOR "; ") as e_mail, 
T0.`Engagement_id`, 
(SELECT CONCAT(GROUP_CONCAT(DISTINCT T3.`Nom` ORDER BY T3.`Sex` DESC SEPARATOR " / " )) FROM QuiQuoi T2 LEFT JOIN Individu T3 ON T2.`Individu_id`=T3.`id` WHERE T2.`Activite_id`=2 AND T2.`QuoiQuoi_id`=1 AND T0.`Engagement_id`=T2.`Engagement_id` ) AS NomFiances
FROM QuiQuoi T0 
LEFT JOIN Individu T1 ON T0.`Individu_id`=T1.`id` 
WHERE T0.`Activite_id`=2 AND T0.`QuoiQuoi_id`=2 AND T0.`Engagement_id`<>0 '.$ExtraRequete.'
GROUP BY T0.`Engagement_id`)
UNION
(SELECT GROUP_CONCAT(DISTINCT CONVERT(T1.`id`, CHAR(50)) ORDER BY T1.`Sex` DESC SEPARATOR "_") AS id_Accompagnateur, 
CONCAT(GROUP_CONCAT( DISTINCT T1.`Nom`), "<BR>", GROUP_CONCAT(DISTINCT T1.`Prenom` ORDER BY T1.`Sex` DESC SEPARATOR " et ")) AS Accompagnateur, 
T1.`Adresse` as Adresse, 
GROUP_CONCAT(DISTINCT T1.`Telephone` ORDER BY T1.`Sex` DESC SEPARATOR " ") as Telephone, 
GROUP_CONCAT(DISTINCT T1.`e_mail` ORDER BY T1.`Sex` DESC SEPARATOR "; ") as e_mail, 
T0.`Engagement_id`, 
" " AS NomFiances
FROM QuiQuoi T0 
LEFT JOIN Individu T1 ON T0.`Individu_id`=T1.`id` 
WHERE T0.`Activite_id`=2 AND T0.`QuoiQuoi_id`=2 AND T0.`Engagement_id`=0 '.$ExtraRequete.' AND T1.`Pretre`=0 AND T1.`Diacre`=0
GROUP BY T1.`Nom`)
ORDER BY Accompagnateur';

	
	$nb_personnes=2;
	$Memo_Accompagnateur="";
	$result = mysqli_query($eCOM_db, $requete);
	while($row = mysqli_fetch_assoc($result)){

		if ($Memo_Accompagnateur != $row['Accompagnateur']) {
			if ($Memo_Accompagnateur != "") {
				echo '</TD><TD bgcolor='.$trcolor.'>';
				if ($nb_personnes > 0) { echo '<FONT face=verdana size=2>'.$nb_personnes.'</FONT>';}
				echo '</TD></TR>';
				$Total_pers = $Total_pers + $nb_personnes;
				$nb_personnes=2;
				$retour_Chariot="";
			}
			$Memo_Accompagnateur = $row['Accompagnateur'];
			//$nb_personnes = $row[Nb_Pers];
			$trcolor = usecolor();
			echo '<TR><TD width=100 bgcolor='.$trcolor.'><FONT face=verdana size=2>';
			Display_Photo($row['Accompagnateur'], "NO LINK", $row['id_Accompagnateur'], "2");
			echo '</TD>';
			echo '<TD width=200 bgcolor='.$trcolor.'><FONT face=verdana size=2>'.$row['Adresse'].'</FONT></TD>';
			echo '<TD width=70 bgcolor='.$trcolor.'><FONT face=verdana size=2>'.$row['Telephone'].'</FONT></TD>';
			echo '<TD width=70 bgcolor='.$trcolor.'><FONT face=verdana size=2>';
			//echo "<A HREF="mailto:.$row[e_mail].?subject= Preparation Mariage" TITLE='Envoyer un mail a $Accompagnateur'>$row[e_mail]</A></td>";
			echo '<A HREF="mailto:'.$row['e_mail'].'?subject= Pr�paration Mariage : " TITLE="Envoyer un mail a '.$row['Accompagnateur'].'">'.$row['e_mail'].'</A></TD>';
			echo '<TD width=170 bgcolor='.$trcolor.'><FONT face=verdana size=1>';
		}
		if (!isset ($retour_Chariot)) {$retour_Chariot="";};
		echo "".$retour_Chariot."";
		if (file_exists("Photos/".$row['Engagement_id'].".jpg")) { 
			echo '<A HREF=Mariage.php?action=edit&id='.$row['Engagement_id'].' class="tooltip">'.$row['NomFiances'].'';
			echo '<EM><span></span>';
			echo '<img src="Photos/'.$row['Engagement_id'].'.jpg" height="100" border="1" alt="couple_'.$row2['id'].'">';
			echo '<BR>'.$row['NomFiances'].'';
			echo '</EM></A>';
		} else {
			echo '<A HREF=Mariage.php?action=edit&id='.$row['Engagement_id'].' class="tooltip">'.$row['NomFiances'].'</A>';
		}
		$retour_Chariot = '<BR>';
		if ($row['Engagement_id'] <> 0) { $nb_personnes = $nb_personnes + 2;}

	}	
	if ($Memo_Accompagnateur != "") {
		echo '</TD><TD bgcolor='.$trcolor.'>';
		if ($nb_personnes > 0) { echo '<FONT face=verdana size=2>'.$nb_personnes.'</FONT>';}
		echo '</TD></TR>';
		$Total_pers = $Total_pers + $nb_personnes;
		$nb_personnes=2;
	}
	echo "</table><br>";
	echo "<font face=verdana size=2>Pr�voir ".$Total_pers." couverts ( ajouter le secr�tariat suivant disponibilit�).</font>";
	fCOM_address_bottom();
	mysqli_close($eCOM_db);
	exit();
}

//======================================
// Vue Financiere
//======================================
if ( isset( $_GET['action'] ) AND $_GET['action']=="vue_financiere") {
//if ($action == "vue_financiere") {
	Global $eCOM_db;
	$debug = false;

	address_top();
	$_SESSION["RetourPage"]=$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'];
	?>
	<link rel="stylesheet" type="text/css" href="includes/Tooltip.css">
	<TABLE WIDTH="100%" BORDER="0" CELLSPACING="1" CELLPADDING="4" BGCOLOR="#FFFFFF">
	<TR BGCOLOR="#F7F7F7">
	<TD><FONT FACE="Verdana" SIZE="2"><B>Vue financi�re</B><BR>
	</TD>
	</TR>
	<TR>
	<TD BGCOLOR="#EEEEEE">
	<?php
	echo "<table>";
	$trcolor = "#EEEEEE";
	echo "<TH bgcolor=$trcolor><font face=verdana size=2>Fianc�s</font></TH>\n";
	echo "<TH bgcolor=$trcolor><font face=verdana size=2>Accompagnateurs</font></TH>\n";
	echo "<TH bgcolor=$trcolor><font face=verdana size=2>Date</font></TH>\n";
	echo "<TH bgcolor=$trcolor><font face=verdana size	=3>�</font></TH>\n";
	if ($_SESSION["Session"]=="All") {
		$ExtraRequete='';
	} else {
		$ExtraRequete='AND T0.`Session`='.$_SESSION["Session"].'';
	}
				
	$requete = 'SELECT T0.`id`, T0.`Date_mariage`, T0.`Finance_total`, T0.`Status`,
				(SELECT CONCAT(GROUP_CONCAT( DISTINCT T2.`Nom`), " ") FROM QuiQuoi T1 LEFT JOIN Individu T2 ON T1.`Individu_id`=T2.`id` WHERE T1.`Activite_id`=2 AND T1.`QuoiQuoi_id`=2 AND T1.`Engagement_id`=T0.`id`) AS Accompagnateurs, 
				(SELECT CONCAT(GROUP_CONCAT(DISTINCT T4.`Nom` ORDER BY T4.`Sex` DESC SEPARATOR " / " )) FROM QuiQuoi T3 LEFT JOIN Individu T4 ON T3.`Individu_id`=T4.`id` WHERE T3.`Activite_id`=2 AND T3.`QuoiQuoi_id`=1 AND T3.`Engagement_id`=T0.`id` ) AS NomFiances
				FROM Fianc�s T0
				WHERE T0.`Actif`=1 and T0.`Status` <> "Annul�/Report�" AND T0.`Status` <> "CANA WE" AND T0.`Status` <> "CANA WE Annul�" '.$ExtraRequete.' 
				ORDER BY Accompagnateurs, T0.`Date_mariage`';
				
			
	pCOM_DebugAdd($debug, "Mariage:RetirerAccompagnateur - requete=".$requete);
	$result = mysqli_query($eCOM_db, $requete);
	$total = 0;
	while($row = mysqli_fetch_assoc($result)){
		$trcolor = usecolor();
		echo "<TR>";
		echo "<TD bgcolor=$trcolor><FONT face=verdana size=2>";
		
		if (file_exists("Photos/".$row['id'].".jpg")){ 
			echo '<A HREF='.$_SERVER['PHP_SELF'].'?action=edit&id='.$row['id'].' class="tooltip">'.$row['NomFiances'].'';
			echo '<EM><SPAN></SPAN>';
			echo '<img src="Photos/'.$row['id'].'.jpg" height="100" border="1" alt="couple_'.$row['id'].'">';
			echo '<BR>'.$row['NomFiances'];
			echo '</EM></A>';
		} else{
			echo '<A HREF='.$_SERVER['PHP_SELF'].'?action=edit&id='.$row['id'].' >'.$row['NomFiances'].'</A>';
		}
		echo '</FONT></TD>';
		if ( $row['Accompagnateurs'] <> "" ) {
			echo '<TD bgcolor='.$trcolor.'><FONT face=verdana size=2>'.$row['Accompagnateurs'].'</FONT></TD>';
		} else {
			echo '<TD bgcolor='.$trcolor.'><FONT face=verdana size=2><I>'.$row['Status'].'<I></FONT></TD>';
		}
		echo '<td bgcolor='.$trcolor.'><FONT face=verdana size=2>';
		echo strftime("%d/%m/%y &nbsp  %H:%M", fCOM_sqlDateToOut($row['Date_mariage']));
		echo '</FONT></TD>';
		echo '<TD align="right" width="35" bgcolor='.$trcolor.'><FONT face=verdana size=2>'.$row['Finance_total'].'</FONT></TD>';
		$total = $total + $row['Finance_total'];
		echo '</TR>';
	}
	$trcolor = usecolor();
	echo '<TR><TD></TD><TD></TD><TD bgcolor='.$trcolor.'><FONT face=verdana size=2><B>Total</B></FONT></TD><TD bgcolor='.$trcolor.'><FONT face=verdana size=2><B>'.$total.'</B></FONT></TD></TR>';
	echo '</TABLE>';
	fCOM_address_bottom();
	mysqli_close($eCOM_db);
	exit();
}



//view profiles
if ( isset( $_GET['action'] ) AND $_GET['action']=="profile") {
	Global $eCOM_db;
//if ($action == "profile") {
	
	//$result = mysqli_query($eCOM_db, "SELECT * FROM ".$Table." where id = ".$_GET['id']." ");
	//while($row = mysqli_fetch_assoc($result))
	//{ 
	?>
	<html>
	<head>
	</head>
	<body bgcolor="#FFFFFF" link=blue vlink=blue alink=blue>
	<font face="verdana"><center>
	<h3><?php echo $_GET['nom_fiance']."<BR>"; ?>
	<table border=1 cellpadding=2 cellspacing=0 bordercolor=#000000 width="95%" bgcolor=eeeeee>
	<tr>
	<td><font face=verdana size=2>email:</td><td><font face=verdana size=2><?php echo $_GET['email']; ?></td></tr>

	</table><br>										
	<font size="2">
	<A HREF="javascript:window.close()">Close Window</A> | 
	<a href="javascript:location.reload()" target="_self">Refresh/Reload</A>
	<?php
	mysqli_close($eCOM_db);
	exit();	
	//}
}




//--------------------------------------------------------------------------------------
//print one record by id
//--------------------------------------------------------------------------------------
if ( isset( $_GET['action'] ) AND $_GET['action']=="printid") {
//if ($action == "printid") {
	Global $eCOM_db;
	$result = mysqli_query($eCOM_db, "SELECT * FROM ".$Table." where id = ".$_GET['id']." ");
	while($row = mysqli_fetch_assoc($result))
	{ 
		echo "<FONT face=verdana><h3>".$row['LUI_Nom'].", ".$row['ELLE_Nom']."</h3>";
		echo "<FONT face=verdana size=2><B>Nom:</B>".$row['LUI_Prenom']." ".$row['LUI_Nom'].", ".$row['ELLE_Prenom']." ".$row['ELLE_Nom']."<br>";
		echo "<B>Lieu de Mariage:</B>".$row['Lieu_mariage']."<br>";
		echo "<B>Date de Mariage:</B>".$row['Date_mariage']."<br>";
		echo "<B>C�l�brant:</B>".$row['Celebrant']."<br>";
		echo "<B>Accompagnateurs:</B>".$row['Accompagnateurs']."<br>";
		echo "<B>T�l�phone:</B>".$row['Telephone']."<br>";
		echo "<B>Email:</B>".$row['Email']."<br>";
		echo "<B>Adresse:</B>".$row['Adresse']."<br>";
		echo "<B>Enfant:</B>".$row['Enfant']."<br>";
		echo "<B>Commentaire:</B>".$row['Commentaire']."<br>";
		//exit();
	}
}
	
//print all records
if ( isset( $_GET['action'] ) AND $_GET['action']=="printall") {
//if ($action == "printall") {
	Global $eCOM_db;
	?><font face=verdana><h3><?php echo "Session ".$_SESSION["Session"]." "; ?></h3><br><?php
	if ($_SESSION["Session"]=="All") {
		$result = mysqli_query($eCOM_db, "SELECT * FROM Fianc�s ORDER BY Accompagnateurs");
	} else {
		$result = mysqli_query($eCOM_db, "SELECT * FROM Fianc�s where Session = ".$_SESSION["Session"]." ORDER BY Accompagnateurs");
	}
	while($row = mysqli_fetch_assoc($result))
	{ 
	
		echo "<FONT face=verdana size=2>";
		echo "<h3>".$row['LUI_Prenom']." ".$row['LUI_Nom'].", ".$row['ELLE_Prenom']." ".$row['ELLE_Nom']."</h3>";
		echo "<B>Lieu de Mariage:</B>".$row['Lieu_mariage']."<br>";
		echo "<B>Date de Mariage:</B>".$row['Date_mariage']."<br>";
		echo "<B>C�l�brant:</B>".$row['Celebrant']."<br>";
		echo "<B>Accompagnateurs:".$row['Accompagnateurs']."</B><br>";
		echo "<B>T�l�phone:</B>".$row['Telephone']."<br>";
		echo "<B>Email:</B>".$row['Email']."<br>";
		echo "<B>Adresse:</B>".$row['Adresse']."<br>";
		echo "<B>Enfant:</B>".$row['Enfant']."<br>";
		echo "<B>Commentaire:</B>".$row['Commentaire']."<br><br>";
	}
	echo "<br><br>";
	mysqli_close($eCOM_db);
	exit();
}



if ( isset( $_GET['action'] ) AND $_GET['action']=="trombinoscope") {
	Global $eCOM_db;
	$debug = false;
	address_top();
	echo '<link rel="stylesheet" type="text/css" href="includes/Tooltip.css">';
	echo '<TABLE WIDTH="100%" BORDER="0" CELLSPACING="1" CELLPADDING="4" BGCOLOR="#FFFFFF">';
	echo '<TR BGCOLOR="#F7F7F7"><TD><FONT FACE="Verdana" SIZE="2"><B>Trombinoscope des fianc�s de la session '.$_SESSION["Session"].'</B><BR>';
	echo '</TD></TR>';
	echo '<TR><TD BGCOLOR="#EEEEEE">';
	echo '<TABLE>';
	
	$Activite_id = 2; // Pr�paration mariage
	$criteria = "T0.`Detail`";
	$ComplementRequete = ' AND MID(T0.`Session`,1,4)="'.$_SESSION["Session"].'" ';
	$criteria='Accompagnateur';
	$order='DESC';
	$extentionWhere='AND (T0.`Session` = '.$_SESSION["Session"].' OR ( T0.`Session` < '.$_SESSION["Session"].' AND Date_mariage >= CURDATE() AND Accompagnateurs <> "Annul�/Report�" AND Accompagnateurs <> "CANA WE" )) ';
	$SelectAccompagnateur='IF(T0.`Session`<'.$_SESSION["Session"].',Concat("Session ", T0.`Session`),IFNULL((SELECT Concat(GROUP_CONCAT( DISTINCT T6.`Nom`), " ",GROUP_CONCAT(T6.`Prenom` ORDER BY T6.`Sex` DESC SEPARATOR " et ")) FROM QuiQuoi T5 LEFT JOIN Individu T6 ON T5.`Individu_id`=T6.`id` WHERE T5.`Activite_id`=2 AND T5.`QuoiQuoi_id`=2 AND T0.`id`=T5.`Engagement_id` ), T0.`Status`))';
	$SelectAccompagnateur='IF(T0.`Session`<'.$_SESSION["Session"].',Concat("Session ", T0.`Session`),IFNULL((SELECT Concat(GROUP_CONCAT(T6.`Prenom` ORDER BY T6.`Sex` DESC SEPARATOR " et "), " ",GROUP_CONCAT( DISTINCT T6.`Nom`)) FROM QuiQuoi T5 LEFT JOIN Individu T6 ON T5.`Individu_id`=T6.`id` WHERE T5.`Activite_id`=2 AND T5.`QuoiQuoi_id`=2 AND T0.`id`=T5.`Engagement_id` ), T0.`Status`))';

	$requete = 'SELECT T0.`id` AS T0_id, T0.`LUI_Extrait_Naissance`, T0.`LUI_Extrait_Bapteme`, T0.`LUI_Lettre_Intention`, T0.`ELLE_Extrait_Naissance`, T0.`ELLE_Extrait_Bapteme`, T0.`ELLE_Lettre_Intention`, T0.`Lieu_mariage`, T0.`Date_mariage`, T0.`Session` AS Session, IFNULL((SELECT Concat(T4.`Nom`) FROM Individu T4 WHERE T4.`id`= T0.`Celebrant_id`), T0.`Celebrant`) as Celebrant, 
(SELECT T6.`Prenom` FROM QuiQuoi T7 LEFT JOIN Individu T6 ON T7.`Individu_id`=T6.`id` WHERE T7.`Activite_id`=2 AND T7.`QuoiQuoi_id`=1 AND T6.`Sex`="M" AND T0.`id`=T7.`Engagement_id`) AS LUI_Prenom, 
(SELECT T6.`Nom` FROM QuiQuoi T7 LEFT JOIN Individu T6 ON T7.`Individu_id`=T6.`id` WHERE T7.`Activite_id`=2 AND T7.`QuoiQuoi_id`=1 AND T6.`Sex`="M" AND T0.`id`=T7.`Engagement_id`) AS LUI_Nom, 
(SELECT T6.`Telephone` FROM QuiQuoi T7 LEFT JOIN Individu T6 ON T7.`Individu_id`=T6.`id` WHERE T7.`Activite_id`=2 AND T7.`QuoiQuoi_id`=1 AND T6.`Sex`="M" AND T0.`id`=T7.`Engagement_id`) AS LUI_Telephone, 
(SELECT T6.`e_mail` FROM QuiQuoi T7 LEFT JOIN Individu T6 ON T7.`Individu_id`=T6.`id` WHERE T7.`Activite_id`=2 AND T7.`QuoiQuoi_id`=1 AND T6.`Sex`="M" AND T0.`id`=T7.`Engagement_id`) AS LUI_email, 
(SELECT T6.`Confession` FROM QuiQuoi T7 LEFT JOIN Individu T6 ON T7.`Individu_id`=T6.`id` WHERE T7.`Activite_id`=2 AND T7.`QuoiQuoi_id`=1 AND T6.`Sex`="M" AND T0.`id`=T7.`Engagement_id`) AS LUI_Confession, 
(SELECT T6.`Prenom` FROM QuiQuoi T7 LEFT JOIN Individu T6 ON T7.`Individu_id`=T6.`id` WHERE T7.`Activite_id`=2 AND T7.`QuoiQuoi_id`=1 AND T6.`Sex`="F" AND T0.`id`=T7.`Engagement_id`) AS ELLE_Prenom, 
(SELECT T6.`Nom` FROM QuiQuoi T7 LEFT JOIN Individu T6 ON T7.`Individu_id`=T6.`id` WHERE T7.`Activite_id`=2 AND T7.`QuoiQuoi_id`=1 AND T6.`Sex`="F" AND T0.`id`=T7.`Engagement_id`) AS ELLE_Nom, 
(SELECT T6.`Telephone` FROM QuiQuoi T7 LEFT JOIN Individu T6 ON T7.`Individu_id`=T6.`id` WHERE T7.`Activite_id`=2 AND T7.`QuoiQuoi_id`=1 AND T6.`Sex`="F" AND T0.`id`=T7.`Engagement_id`) AS ELLE_Telephone, 
(SELECT T6.`e_mail` FROM QuiQuoi T7 LEFT JOIN Individu T6 ON T7.`Individu_id`=T6.`id` WHERE T7.`Activite_id`=2 AND T7.`QuoiQuoi_id`=1 AND T6.`Sex`="F" AND T0.`id`=T7.`Engagement_id`) AS ELLE_email, 
(SELECT T6.`Confession` FROM QuiQuoi T7 LEFT JOIN Individu T6 ON T7.`Individu_id`=T6.`id` WHERE T7.`Activite_id`=2 AND T7.`QuoiQuoi_id`=1 AND T6.`Sex`="F" AND T0.`id`=T7.`Engagement_id`) AS ELLE_Confession, 
'.$SelectAccompagnateur.' As Accompagnateur
FROM Fianc�s T0
LEFT JOIN Individu T1 ON T1.`id`=T0.`LUI_id` 
LEFT JOIN Individu T2 ON T2.`id`=T0.`ELLE_id`
WHERE T0.`Actif`=1 '.$extentionWhere.'
ORDER BY '.$criteria.' '.$order.''; 

	$resultat = mysqli_query($eCOM_db,  $requete );
	$compteur = 0;
	$MemoAccompagnateur= "";
	while( $row = mysqli_fetch_assoc( $resultat )) {
		if ($row['Accompagnateur'] <> "Pr�pa. Ext. + W" and
			$row['Accompagnateur'] <> "Pr�pa. Ext." and 
			$row['Accompagnateur'] <> "Autre" and
			$row['Accompagnateur'] <> "Annul�/Report�" and
			strpos($row['Accompagnateur'], "Session", 0) === False ) {
			if ($compteur > 5 OR $MemoAccompagnateur != $row['Accompagnateur']) {
				echo "</TR><TR><TD><BR></TD></TR><TR>";
				if ($row['Accompagnateur'] == "" ) {
					echo '<TD bgcolor="#A1A1A1" colspan=5><FONT face=verdana size=2>Pas d\'accompagnateur</FONT></TD></TR><TR>';
				} else {
					echo '<TD bgcolor="#A1A1A1" colspan=5><FONT face=verdana size=2>Couples accompagn�s par '.$row['Accompagnateur'].' :</FONT></TD></TR><TR>';
				}	
				$compteur = 0;
				$MemoAccompagnateur = $row['Accompagnateur'];
			}
			$compteur = $compteur + 1;

			echo '<TD valign="top"><A HREF='.$_SERVER['PHP_SELF'].'?action=edit&id='.$row['T0_id'].'>';	
			if (file_exists("Photos/".$row['T0_id'].".jpg")) { 
				echo '<IMG SRC="Photos/'.$row['T0_id'].'.jpg" HEIGHT=150 border="1"></A>';		
			} else {
				echo '<IMG SRC="Photos/Individu_NULL.jpg" HEIGHT=150 border="1"></A>';
			}
			echo "<BR><FONT face=verdana size=2>".$row['ELLE_Prenom']." et ".$row['LUI_Prenom']."</FONT><BR>";
		}
	}
	echo "</TR></TABLE>";
	fCOM_address_bottom();
	mysqli_close($eCOM_db);
	exit;
}






//----------------------------------------------------------------------
// Listing general de la session
//----------------------------------------------------------------------


function personne_line($enregistrement, $pCompteur) {
	$trcolor = usecolor();
	//echo ' pCompteur ='.$pCompteur;
	//$trcolor = usecolorPlus($enregistrement['Date_mariage']);
	//echo '<!-- PERSONNE -->';
	if (strtotime(date('Y-m-d H:i:s')) >= strtotime($enregistrement['Date_mariage'])) {
		echo '<h6 style="display:none;"></h6><TR id="Filtrer_'.$pCompteur.'" style="display:table-row;">';
	} else {
		echo '<TR>';
	}
	echo '<TD width=40 bgcolor='.$trcolor.'><CENTER>';
	
	$NomFiance = $enregistrement['LUI_Prenom'].' '.$enregistrement['LUI_Nom'].' et<BR>'.$enregistrement['ELLE_Prenom'].' '.$enregistrement['ELLE_Nom'];
	$email=$enregistrement['LUI_email']."<BR>".$enregistrement['ELLE_email'];
	echo "<A HREF=\"javascript:showProfile('$NomFiance','$email')\"><img src=\"images/profile.gif\" border=0 alt='View Profile'></A>";
  
	if (file_exists("Photos/".$enregistrement['id'].".jpg"))
	{ 
		echo ' <A HREF='.$_SERVER['PHP_SELF'].'?action=edit&id='.$enregistrement['id'].' class="tooltip"><img src="images/edit.gif": border=0>';
		echo '<EM><SPAN></SPAN>';
		echo "<img src='Photos/".$enregistrement['id'].".jpg' height='100' border='1' alt='couple_".$enregistrement['id']."'>";
		echo '<BR><FONT face=verdana size=2>'.$enregistrement['LUI_Prenom'].' '.$enregistrement['LUI_Nom'].' et <BR>'.$enregistrement['ELLE_Prenom'].' '.$enregistrement['ELLE_Nom'].'</FONT>';
		echo '</EM></A>';
	} else {
		echo ' <A HREF='.$_SERVER['PHP_SELF'].'?action=edit&id='.$enregistrement['id'].'><img src="images/edit.gif": border=0>';
		echo '</A>';
	}
  
	echo '</CENTER></TD>';

	if ($_SESSION["Session"]=="All") {
		echo '<TD bgcolor='.$trcolor.'><FONT face=verdana size=2>'.$enregistrement['Session'].'</FONT></TD>';
	} else {
		//if ($enregistrement['Session'] == $_SESSION["Session"])
		//{
			echo '<TD bgcolor='.$trcolor.'><FONT face=verdana size=2>'.$enregistrement['Accompagnateur'].'</FONT></TD>';
		//} else {
		//	echo '<TD bgcolor='.$trcolor.'><FONT face=verdana size=2>Session '.$enregistrement['Session'].'</FONT></TD>';
		//}
	}
	if ($enregistrement['LUI_Extrait_Naissance'] == '1' && ($enregistrement['LUI_Extrait_Bapteme'] == '1' || $enregistrement['LUI_Confession'] == 'Sans') && $enregistrement['LUI_Lettre_Intention']) 
	{ 
		$fgcolor = "green"; 
	} else {
		$fgcolor = "black"; 
	}
	echo '<TD bgcolor='.$trcolor.'><FONT face=verdana size=1 color='.$fgcolor.'>'.$enregistrement['LUI_Prenom'].'</FONT></TD>';
	echo '<TD bgcolor='.$trcolor.'><FONT face=verdana size=2 color='.$fgcolor.'>'.$enregistrement['LUI_Nom'].'</FONT></TD>';
	
	if ($enregistrement['ELLE_Extrait_Naissance'] == '1' && ($enregistrement['ELLE_Extrait_Bapteme'] == '1' || $enregistrement['ELLE_Confession'] == 'Sans')  && $enregistrement['ELLE_Lettre_Intention']) 
	{ 
		$fgcolor = "green"; 
	} else { 
		$fgcolor = "black"; 
	}
	echo '<TD bgcolor='.$trcolor.'><FONT face=verdana size=1 color='.$fgcolor.'>'.$enregistrement['ELLE_Prenom'].'</FONT></TD>';
	echo '<TD bgcolor='.$trcolor.'><FONT face=verdana size=2 color='.$fgcolor.'>'.$enregistrement['ELLE_Nom'].'</FONT></TD>';
	echo '<TD bgcolor='.$trcolor.'><FONT face=verdana size=2 color='.$fgcolor.'>'.$enregistrement['LUI_Telephone']." ".$enregistrement['ELLE_Telephone'].'</FONT></TD>';
	
	$type_confession = "-";
	if ($enregistrement['ELLE_Confession'] == "Orthodoxe" || $enregistrement['LUI_Confession'] == "Orthodoxe" || $enregistrement['ELLE_Confession'] == "Protestant" || $enregistrement['LUI_Confession'] == "Protestant") 
	{
		$type_confession = "M";
	} else {
		if ($enregistrement['ELLE_Confession'] == "Musulman" || $enregistrement['LUI_Confession'] == "Musulman" || $enregistrement['ELLE_Confession'] == "Juif" || $enregistrement['LUI_Confession'] == "Juif" || $enregistrement['ELLE_Confession'] == "Bouddhiste" || $enregistrement['LUI_Confession'] == "Bouddhiste" || $enregistrement['ELLE_Confession'] == "Autre" || $enregistrement['LUI_Confession'] == "Autre" || $enregistrement['ELLE_Confession'] == "Sans" || $enregistrement['LUI_Confession'] == "Sans" )
		{
			$type_confession = "D";
		}
	}

	echo '<TD bgcolor='.$trcolor.'><FONT face=verdana size=1>'.$type_confession.'</FONT></TD>';
	echo '<TD bgcolor='.$trcolor.'><FONT face=verdana size=1>'.$enregistrement['Celebrant'].'</FONT></TD>';
	echo '<TD width=90 bgcolor='.$trcolor.'><FONT face=verdana size=1>';
	if (strftime("%d/%m/%y", fCOM_sqlDateToOut($enregistrement['Date_mariage'])) == "01/01/70" ) {
		//echo "<TD width=90 bgcolor=$trcolor><FONT face=verdana size=1>";
		echo '<FONT face=verdana size=1>-</FONT>';
	} else {
		//echo "<TD width=90 bgcolor=$trcolor><FONT face=verdana size=1>";
		echo strftime("%d/%m/%y %H:%M", fCOM_sqlDateToOut($enregistrement['Date_mariage']));
	}
	echo '</FONT></TD>';
	echo '<TD bgcolor='.$trcolor.'><FONT face=verdana size=1>'.$enregistrement['Lieu_mariage'].'</FONT></TD>';
	//echo "<td bgcolor=$trcolor><FONT face=verdana size=2>$row[ID]</FONT></TD>\n";
	echo '</TR>';
	//if (strtotime(now) >= strtotime($enregistrement['Date_mariage'])) {
	//	echo '</h6>';
	//}
	//echo '<!-- /PERSONNE -->';
}


function personne_list ($resultat, $order) {
	global $debug;
	$debug = false;
	require("Login/sqlconf.php");
	address_top(); 
	echo '<link rel="stylesheet" type="text/css" href="includes/Tooltip.css">';
	echo '<TABLE WIDTH="100%" BORDER="0" CELLSPACING="1" CELLPADDING="4" BGCOLOR="#FFFFFF">';
	echo '<TR BGCOLOR="#F7F7F7"><TD><FONT FACE="Verdana" SIZE="2"><B>Liste des Fianc�s</B><BR></TD></TR>';
	echo '<TR><TD BGCOLOR="#EEEEEE">';

	echo '<TABLE>';
	$trcolor = "#EEEEEE";
	echo '<TH></TH>';
	if ($_SESSION["Session"]=="All") {
		echo "<TH bgcolor=".$trcolor."><FONT face=verdana size=2><A HREF=\"" . $_SERVER['SCRIPT_NAME'] . "?criteria=Session&order=".$order."\">Session</A></FONT></TH>";
	} else {
		echo "<TH bgcolor=".$trcolor."><FONT face=verdana size=2><A HREF=\"" . $_SERVER['SCRIPT_NAME'] . "?criteria=Accompagnateur&order=".$order."\">Accompagn.</A></FONT></TH>";
	}
	echo "<TH bgcolor=".$trcolor."><FONT face=verdana size=2><A HREF=\"" . $_SERVER['SCRIPT_NAME'] . "?criteria=LUI_Prenom&order=".$order."\">LUI Pr�nom</A></FONT></TH>";
	echo "<TH bgcolor=".$trcolor."><FONT face=verdana size=2><A HREF=\"" . $_SERVER['SCRIPT_NAME'] . "?criteria=LUI_Nom&order=".$order."\">Nom</A></FONT></TH>";
	echo "<TH bgcolor=".$trcolor."><FONT face=verdana size=2><A HREF=\"" . $_SERVER['SCRIPT_NAME'] . "?criteria=ELLE_Prenom&order=".$order."\">ELLE Pr�nom</A></FONT></TH>";
	echo "<TH bgcolor=".$trcolor."><font face=verdana size=2><A HREF=\"" . $_SERVER['SCRIPT_NAME'] . "?criteria=ELLE_Nom&order=".$order."\">Nom</A></FONT></TH>";
	echo "<TH bgcolor=".$trcolor."><font face=verdana size=2><A HREF=\"" . $_SERVER['SCRIPT_NAME'] . "?criteria=ELLE_Nom&order=".$order."\">Telephone</A></FONT></TH>";
	echo '<TH bgcolor='.$trcolor.'><FONT face=verdana size=2> </FONT></TH>';
	echo "<TH bgcolor=".$trcolor."><FONT face=verdana size=2><A HREF=\"" . $_SERVER['SCRIPT_NAME'] . "?criteria=Celebrant&order=".$order."\">C�l�brant</A></FONT></TH>";
	
	echo "<TH bgcolor=".$trcolor."><FONT face=verdana size=2><A HREF=\"" . $_SERVER['SCRIPT_NAME'] . "?criteria=Date_mariage&order=".$order."\">Date</A></FONT>&nbsp&nbsp";
    echo '<input type="checkbox" onclick="FiltrerLine()"> <label for="Filter_old_fich"><FONT SIZE="2"></b></label>';
	echo '</TH>';
	echo "<TH bgcolor=".$trcolor."><FONT face=verdana size=2><A HREF=\"" . $_SERVER['SCRIPT_NAME'] . "?criteria=Lieu_mariage&order=".$order."\">Lieu</A></FONT><FONT face=verdana size=1> <A HREF=\"" . $_SERVER['SCRIPT_NAME'] . "?criteria=Lieu_nom&order=".$order."\">(nom)</A></FONT></TH>";
	
	global $debug;
	$debug=False;
	pCOM_DebugInit($debug);
	
	$compteur = 0;
	while( $enregistrement = mysqli_fetch_assoc( $resultat ))
	{
		if (strtotime(date('Y-m-d H:i:s')) >= strtotime($enregistrement['Date_mariage'])) {
			$compteur = $compteur + 1;
		}
		personne_line($enregistrement, $compteur);
	}
	echo '</TABLE>'; 

	fCOM_address_bottom();	

?>
<script type="text/javascript">
var current=null;
function FiltrerLine() {
	
	var nombreh6 = document.getElementsByTagName('h6').length; //nombre de tr a cacher
	for(var i=1; i<=nombreh6; i++)
	{
		var stockacacher = 'Filtrer_'+i;
		current = document.getElementById(stockacacher);
		
		if(current.style.display=='table-row')	{
			current.style.display='none';
		} else {
			current.style.display='table-row';
		}
	}
}
</script>
<?php

}


echo '<HTML><HEAD>';
echo '<TITLE>Database Mariage</TITLE>';
echo '</HEAD>';
echo '<BODY>';


Global $eCOM_db;
$debug = false;
$_SESSION["RetourPage"]=$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'];

if (isset($_GET['criteria'])) $criteria=$_GET['criteria'];
if (isset($_GET['order'])) $order=$_GET['order'];

if ($_SESSION["Session"]=="All") {
	if (!isset($_GET['criteria'])) $criteria='Session';
	if (!isset($_GET['order'])) $order='DESC';
} else {
	if (!isset($_GET['criteria'])) $criteria='Accompagnateur';
	if (!isset($_GET['order'])) $order='DESC';
}

if ($_SESSION["Session"]=="All")
{
	$extentionWhere='AND Accompagnateurs <> "Annul�/Report�" AND Accompagnateurs <> "CANA WE"';
	$SelectAccompagnateur='T0.`Session`';
} else {
	$extentionWhere='AND (T0.`Session` = '.$_SESSION["Session"].' OR ( T0.`Session` < '.$_SESSION["Session"].' AND Date_mariage >= CURDATE() AND Accompagnateurs <> "Annul�/Report�" AND Accompagnateurs <> "CANA WE" )) ';
	$SelectAccompagnateur='IF(T0.`Session`<'.$_SESSION["Session"].',Concat("Session ", T0.`Session`),IFNULL((SELECT Concat(GROUP_CONCAT( DISTINCT T6.`Nom`), " ",GROUP_CONCAT(T6.`Prenom` ORDER BY T6.`Sex` DESC SEPARATOR " et ")) FROM QuiQuoi T5 LEFT JOIN Individu T6 ON T5.`Individu_id`=T6.`id` WHERE T5.`Activite_id`=2 AND T5.`QuoiQuoi_id`=2 AND T0.`id`=T5.`Engagement_id` ), T0.`Status`))';
}
if ($criteria == "Celebrant") {
	$extentionOrder=', T0.`Date_mariage` ASC ';
	
} elseif ($criteria == "Lieu_mariage" ) {
	$extentionOrder=', T0.`Date_mariage` ASC ';
	
} elseif ($criteria == "Lieu_nom" ) {
	$criteria = "Lieu_mariage";
	$extentionOrder=', LUI_Nom ASC ';
	
} else {
	$extentionOrder='';
}

$requete = 'SELECT T0.`id`, T0.`LUI_Extrait_Naissance`, T0.`LUI_Extrait_Bapteme`, T0.`LUI_Lettre_Intention`, T0.`ELLE_Extrait_Naissance`, T0.`ELLE_Extrait_Bapteme`, T0.`ELLE_Lettre_Intention`, T0.`Lieu_mariage`, T0.`Date_mariage`, T0.`Session` AS Session, IFNULL((SELECT Concat(T4.`Prenom`, " ",T4.`Nom`) FROM QuiQuoi T3 LEFT JOIN Individu T4 ON T3.`Individu_id`=T4.`id` WHERE T3.`Activite_id`=2 AND T3.`QuoiQuoi_id`=5 AND T0.`id`=T3.`Engagement_id`), T0.`Celebrant`) as Celebrant, 
(SELECT T6.`Prenom` FROM QuiQuoi T7 LEFT JOIN Individu T6 ON T7.`Individu_id`=T6.`id` WHERE T7.`Activite_id`=2 AND T7.`QuoiQuoi_id`=1 AND T6.`Sex`="M" AND T0.`id`=T7.`Engagement_id`) AS LUI_Prenom, 
(SELECT T6.`Nom` FROM QuiQuoi T7 LEFT JOIN Individu T6 ON T7.`Individu_id`=T6.`id` WHERE T7.`Activite_id`=2 AND T7.`QuoiQuoi_id`=1 AND T6.`Sex`="M" AND T0.`id`=T7.`Engagement_id`) AS LUI_Nom, 
(SELECT T6.`Telephone` FROM QuiQuoi T7 LEFT JOIN Individu T6 ON T7.`Individu_id`=T6.`id` WHERE T7.`Activite_id`=2 AND T7.`QuoiQuoi_id`=1 AND T6.`Sex`="M" AND T0.`id`=T7.`Engagement_id`) AS LUI_Telephone, 
(SELECT T6.`Confession` FROM QuiQuoi T7 LEFT JOIN Individu T6 ON T7.`Individu_id`=T6.`id` WHERE T7.`Activite_id`=2 AND T7.`QuoiQuoi_id`=1 AND T6.`Sex`="M" AND T0.`id`=T7.`Engagement_id`) AS LUI_Confession, 
(SELECT T6.`Prenom` FROM QuiQuoi T7 LEFT JOIN Individu T6 ON T7.`Individu_id`=T6.`id` WHERE T7.`Activite_id`=2 AND T7.`QuoiQuoi_id`=1 AND T6.`Sex`="F" AND T0.`id`=T7.`Engagement_id`) AS ELLE_Prenom, 
(SELECT T6.`Nom` FROM QuiQuoi T7 LEFT JOIN Individu T6 ON T7.`Individu_id`=T6.`id` WHERE T7.`Activite_id`=2 AND T7.`QuoiQuoi_id`=1 AND T6.`Sex`="F" AND T0.`id`=T7.`Engagement_id`) AS ELLE_Nom, 
(SELECT T6.`Telephone` FROM QuiQuoi T7 LEFT JOIN Individu T6 ON T7.`Individu_id`=T6.`id` WHERE T7.`Activite_id`=2 AND T7.`QuoiQuoi_id`=1 AND T6.`Sex`="F" AND T0.`id`=T7.`Engagement_id`) AS ELLE_Telephone, 
(SELECT T6.`Confession` FROM QuiQuoi T7 LEFT JOIN Individu T6 ON T7.`Individu_id`=T6.`id` WHERE T7.`Activite_id`=2 AND T7.`QuoiQuoi_id`=1 AND T6.`Sex`="F" AND T0.`id`=T7.`Engagement_id`) AS ELLE_Confession, 
'.$SelectAccompagnateur.' As Accompagnateur
FROM Fianc�s T0
LEFT JOIN Individu T1 ON T1.`id`=T0.`LUI_id` 
LEFT JOIN Individu T2 ON T2.`id`=T0.`ELLE_id`
WHERE T0.`Actif`=1 '.$extentionWhere.'
ORDER BY '.$criteria.' '.$order.$extentionOrder.''; 

$requete = 'SELECT T0.`id`, T0.`LUI_Extrait_Naissance`, T0.`LUI_Extrait_Bapteme`, T0.`LUI_Lettre_Intention`, T0.`ELLE_Extrait_Naissance`, T0.`ELLE_Extrait_Bapteme`, T0.`ELLE_Lettre_Intention`, T0.`Lieu_mariage`, T0.`Date_mariage`, T0.`Session` AS Session, IFNULL((SELECT Concat(T4.`Nom`) FROM Individu T4 WHERE T4.`id`= T0.`Celebrant_id`), IF (T0.`Celebrant_id`=-1, "C�l�brant Ext�rieur", T0.`Celebrant`)) as Celebrant, 
(SELECT T6.`Prenom` FROM QuiQuoi T7 LEFT JOIN Individu T6 ON T7.`Individu_id`=T6.`id` WHERE T7.`Activite_id`=2 AND T7.`QuoiQuoi_id`=1 AND T6.`Sex`="M" AND T0.`id`=T7.`Engagement_id`) AS LUI_Prenom, 
(SELECT T6.`Nom` FROM QuiQuoi T7 LEFT JOIN Individu T6 ON T7.`Individu_id`=T6.`id` WHERE T7.`Activite_id`=2 AND T7.`QuoiQuoi_id`=1 AND T6.`Sex`="M" AND T0.`id`=T7.`Engagement_id`) AS LUI_Nom, 
(SELECT T6.`Telephone` FROM QuiQuoi T7 LEFT JOIN Individu T6 ON T7.`Individu_id`=T6.`id` WHERE T7.`Activite_id`=2 AND T7.`QuoiQuoi_id`=1 AND T6.`Sex`="M" AND T0.`id`=T7.`Engagement_id`) AS LUI_Telephone, 
(SELECT T6.`e_mail` FROM QuiQuoi T7 LEFT JOIN Individu T6 ON T7.`Individu_id`=T6.`id` WHERE T7.`Activite_id`=2 AND T7.`QuoiQuoi_id`=1 AND T6.`Sex`="M" AND T0.`id`=T7.`Engagement_id`) AS LUI_email, 
(SELECT T6.`Confession` FROM QuiQuoi T7 LEFT JOIN Individu T6 ON T7.`Individu_id`=T6.`id` WHERE T7.`Activite_id`=2 AND T7.`QuoiQuoi_id`=1 AND T6.`Sex`="M" AND T0.`id`=T7.`Engagement_id`) AS LUI_Confession, 
(SELECT T6.`Prenom` FROM QuiQuoi T7 LEFT JOIN Individu T6 ON T7.`Individu_id`=T6.`id` WHERE T7.`Activite_id`=2 AND T7.`QuoiQuoi_id`=1 AND T6.`Sex`="F" AND T0.`id`=T7.`Engagement_id`) AS ELLE_Prenom, 
(SELECT T6.`Nom` FROM QuiQuoi T7 LEFT JOIN Individu T6 ON T7.`Individu_id`=T6.`id` WHERE T7.`Activite_id`=2 AND T7.`QuoiQuoi_id`=1 AND T6.`Sex`="F" AND T0.`id`=T7.`Engagement_id`) AS ELLE_Nom, 
(SELECT T6.`Telephone` FROM QuiQuoi T7 LEFT JOIN Individu T6 ON T7.`Individu_id`=T6.`id` WHERE T7.`Activite_id`=2 AND T7.`QuoiQuoi_id`=1 AND T6.`Sex`="F" AND T0.`id`=T7.`Engagement_id`) AS ELLE_Telephone, 
(SELECT T6.`e_mail` FROM QuiQuoi T7 LEFT JOIN Individu T6 ON T7.`Individu_id`=T6.`id` WHERE T7.`Activite_id`=2 AND T7.`QuoiQuoi_id`=1 AND T6.`Sex`="F" AND T0.`id`=T7.`Engagement_id`) AS ELLE_email, 
(SELECT T6.`Confession` FROM QuiQuoi T7 LEFT JOIN Individu T6 ON T7.`Individu_id`=T6.`id` WHERE T7.`Activite_id`=2 AND T7.`QuoiQuoi_id`=1 AND T6.`Sex`="F" AND T0.`id`=T7.`Engagement_id`) AS ELLE_Confession, 
'.$SelectAccompagnateur.' As Accompagnateur
FROM Fianc�s T0
LEFT JOIN Individu T1 ON T1.`id`=T0.`LUI_id` 
LEFT JOIN Individu T2 ON T2.`id`=T0.`ELLE_id`
WHERE T0.`Actif`=1 '.$extentionWhere.'
ORDER BY '.$criteria.' '.$order.$extentionOrder.''; 


$debug=false;
//debug_plus($requete . "<BR>\n");

$resultat = mysqli_query($eCOM_db, $requete);
$NbEnregistrement = mysqli_num_rows($resultat);
pCOM_DebugAdd($debug, "Mariage - Enreg dans la table " .$NbEnregistrement);

pCOM_DebugAdd($debug, 'Mariage - Crit�re de tri: '.$criteria);
pCOM_DebugAdd($debug, 'Mariage - Crit�re d\'ordre: '.$order);

if(isset($order) and $order=="ASC"){
$order="DESC";
}else{$order="ASC";}

personne_list($resultat, $order);
mysqli_close($eCOM_db);
?>
  
</BODY>
</HTML>