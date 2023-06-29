<?php

# Função de Navegação das Abas #
function NavegacaoAbas($Pri,$Seg,$Ter,$Qua,$Qui){
    $htm  = "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" bordercolor=\"#75ADE6\" summary=\"\">";
    $htm .= "	<tr>\n";
    $htm .= "		<td valign=\"bottom\">\n";
    $htm .=	"			<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" summary=\"\">\n";
    $htm .=	"				<tr>\n";
    $htm .=	"					<td background=\"../midia/aba_".$Pri."_e.gif\" width=\"10\" valign=\"center\">&nbsp;</td>\n";
    $htm .=	"					<td background=\"../midia/aba_".$Pri.".gif\" valign=\"center\" class=\"textoabason\"><a href=\"javascript:Submete('A');\" class=\"textoabas".$Pri."\">&nbsp;MEMBRO&nbsp;COMISSÃO&nbsp; </a></td>\n";
    $htm .=	"					<td background=\"../midia/aba_".$Pri."_d.gif\" width=\"4\" valign=\"center\">&nbsp;</td>\n";
    $htm .=	"				</tr>\n";
    $htm .=	"			</table>\n";
    $htm .=	"		</td>\n";

    $htm .=	"		<td valign=\"bottom\">\n";
    $htm .=	"			<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" summary=\"\">\n";
    $htm .=	"		   	<tr>\n";
    $htm .=	"				<td background=\"../midia/aba_".$Seg."_e.gif\" width=\"10\" valign=\"center\">&nbsp;</td>\n";
    $htm .=	"			   	<td background=\"../midia/aba_".$Seg.".gif\" valign=\"center\" class=\"textoabasoff\"><a href=\"javascript:Submete('B');\" class=\"textoabas".$Seg."\">&nbsp;CREDENCIAMENTO&nbsp; </a></td>\n";
    $htm .=	"			   	<td background=\"../midia/aba_".$Seg."_d.gif\" width=\"4\" valign=\"center\">&nbsp;</td>\n";
    $htm .=	"		   	</tr>\n";
    $htm .=	"			</table>\n";
    $htm .=	"		</td>\n";

    $htm .=	"		<td valign=\"bottom\">\n";
    $htm .=	"			<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" summary=\"\">\n";
    $htm .=	"				<tr>\n";
    $htm .=	"					<td background=\"../midia/aba_".$Ter."_e.gif\" width=\"10\" valign=\"center\">&nbsp;</td>\n";
    $htm .=	"					<td background=\"../midia/aba_".$Ter.".gif\" valign=\"center\" class=\"textoabasoff\"><a href=\"javascript:Submete('C');\" class=\"textoabas".$Ter."\">&nbsp;FORNECEDORES&nbsp;CREDENCIADOS&nbsp;</a></td>\n";
    $htm .=	"					<td background=\"../midia/aba_".$Ter."_d.gif\" width=\"4\" valign=\"center\">&nbsp;</td>\n";
    $htm .=	"				</tr>\n";
    $htm .=	"			</table>\n";
    $htm .=	"		</td>\n";

    $htm .=	"		</td>\n";
    $htm .=	"		<td valign=\"bottom\">\n";
    $htm .=	"			<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" summary=\"\">\n";
    $htm .=	"				<tr>\n";
    $htm .=	"					<td background=\"../midia/aba_".$Qua."_e.gif\" width=\"10\" valign=\"center\">&nbsp;</td>\n";
    $htm .=	"					<td background=\"../midia/aba_".$Qua.".gif\" valign=\"center\" class=\"textoabasoff\"><a href=\"javascript:Submete('D');\" class=\"textoabas".$Qua."\">&nbsp;CLASSIFICAÇÃO&nbsp;</a></td>\n";
    $htm .=	"					<td background=\"../midia/aba_".$Qua."_d.gif\" width=\"4\" valign=\"center\">&nbsp;</td>\n";
    $htm .=	"				</tr>\n";
    $htm .=	"			</table>\n";
    $htm .=	"		</td>\n";

    $htm .=	"		<td valign=\"bottom\">\n";
    $htm .=	"			<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" summary=\"\">\n";
    $htm .=	"				<tr>\n";
    $htm .=	"					<td background=\"../midia/aba_".$Qui."_e.gif\" width=\"10\" valign=\"center\">&nbsp;</td>\n";
    $htm .=	"					<td background=\"../midia/aba_".$Qui.".gif\" valign=\"center\" class=\"textoabasoff\"><a href=\"javascript:Submete('E');\" class=\"textoabas".$Qui."\">&nbsp;ITENS&nbsp;</a></td>\n";
    $htm .=	"					<td background=\"../midia/aba_".$Qui."_d.gif\" width=\"4\" valign=\"center\">&nbsp;</td>\n";
    $htm .=	"				</tr>\n";
    $htm .=	"			</table>\n";
    $htm .=	"		</td>\n";
    $htm .= "	</tr>\n";
    $htm .= "</table>\n";
    return $htm;
}
