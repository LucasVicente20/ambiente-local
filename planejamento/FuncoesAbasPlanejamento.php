<?php
/**
 * Portal de Compras
 * Programa: FuncoesAbasPlanejamento.php
 * Autor: Diógenes Dantas | João Madson
 * Data: 01/12/2022
 * Objetivo: Programa de controle das abas das telas de PlanejamentoDFD
 * Tarefa Redmine: #275345
 * -------------------------------------------------------------------
 * Alterado:
 * Data:
 * Tarefa:
 * -------------------------------------------------------------------
 */

function NavegacaoAbasConsultaDFD($Pri,$Seg) {
    $htm  = "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" bordercolor=\"#75ADE6\" summary=\"\">";
    $htm .= "	<tr>\n";
    $htm .= "		<td valign=\"bottom\">\n";
    $htm .=	"			<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" summary=\"\">\n";
    $htm .=	"				<tr>\n";
    $htm .=	"					<td background=\"../midia/aba_".$Pri."_e.gif\" width=\"10\" valign=\"center\">&nbsp;</td>\n";
    $htm .=	"					<td background=\"../midia/aba_".$Pri.".gif\" valign=\"center\" class=\"textoabason\"><a href=\"javascript:Submete('A');\" class=\"textoabas".$Pri."\">Informações</a></td>\n";
    $htm .=	"					<td background=\"../midia/aba_".$Pri."_d.gif\" width=\"4\" valign=\"center\"></td>\n";
    $htm .=	"				</tr>\n";
    $htm .=	"			</table>\n";
    $htm .=	"		</td>\n";

    $htm .=	"		<td valign=\"bottom\">\n";
    $htm .=	"			<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" summary=\"\">\n";
    $htm .=	"		   	<tr>\n";
    $htm .=	"				<td background=\"../midia/aba_".$Seg."_e.gif\" width=\"10\" valign=\"center\">&nbsp;</td>\n";
    $htm .=	"			   	<td background=\"../midia/aba_".$Seg.".gif\" valign=\"center\" class=\"textoabasoff\"><a href=\"javascript:Submete('B');\" class=\"textoabas".$Seg."\">Histórico</a></td>\n";
    $htm .=	"			   	<td background=\"../midia/aba_".$Seg."_d.gif\" width=\"4\" valign=\"center\"></td>\n";
    $htm .=	"		   	</tr>\n";
    $htm .=	"			</table>\n";
    $htm .=	"		</td>\n";

    $htm .= "	</tr>\n";
    $htm .= "</table>\n";
    return $htm;
}
function NavegacaoAbasConsultaDFDPopUp($Pri,$Seg) {
    $htm  = "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" bordercolor=\"#75ADE6\" summary=\"\">";
    $htm .= "	<tr>\n";
    $htm .= "		<td valign=\"bottom\">\n";
    $htm .=	"			<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" summary=\"\">\n";
    $htm .=	"				<tr>\n";
    $htm .=	"					<td background=\"../midia/aba_".$Pri."_e.gif\" width=\"10\" valign=\"center\">&nbsp;</td>\n";
    $htm .=	"					<td background=\"../midia/aba_".$Pri.".gif\" valign=\"center\" class=\"textoabason\"><a href=\"javascript:Submete('A');\" class=\"textoabas".$Pri."\">Informações</a></td>\n";
    $htm .=	"					<td background=\"../midia/aba_".$Pri."_d.gif\" width=\"4\" valign=\"center\"></td>\n";
    $htm .=	"				</tr>\n";
    $htm .=	"			</table>\n";
    $htm .=	"		</td>\n";

    $htm .=	"		<td valign=\"bottom\">\n";
    $htm .=	"			<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" summary=\"\">\n";
    $htm .=	"		   	<tr>\n";
    $htm .=	"				<td background=\"../midia/aba_".$Seg."_e.gif\" width=\"10\" valign=\"center\">&nbsp;</td>\n";
    $htm .=	"			   	<td background=\"../midia/aba_".$Seg.".gif\" valign=\"center\" class=\"textoabasoff\"><a href=\"javascript:Submete('B');\" class=\"textoabas".$Seg."\">Histórico</a></td>\n";
    $htm .=	"			   	<td background=\"../midia/aba_".$Seg."_d.gif\" width=\"4\" valign=\"center\"></td>\n";
    $htm .=	"		   	</tr>\n";
    $htm .=	"			</table>\n";
    $htm .=	"		</td>\n";

    $htm .= "	</tr>\n";
    $htm .= "</table>\n";
    return $htm;
}
?>
