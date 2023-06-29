<?php
# Autor:    Madson Felix
# Data:     28/04/2021
# CR #246939
# -------------------------------------------------------------------------

# Função de Navegação das Abas #
function NavegacaoAbas($Pri,$Seg,$Ter,$Qua,$Qui){
    $htm  = "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" bordercolor=\"#75ADE6\" summary=\"\">";
    $htm .= "	<tr>\n";
    $htm .= "		<td valign=\"bottom\">\n";
    $htm .=	"			<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" summary=\"\">\n";
    $htm .=	"				<tr>\n";
    $htm .=	"					<td background=\"../midia/aba_".$Pri."_e.gif\" width=\"10\" valign=\"center\">&nbsp;</td>\n";
    $htm .=	"					<td background=\"../midia/aba_".$Pri.".gif\" valign=\"center\" class=\"textoabason\"><a href=\"javascript:Submete('A');\" class=\"textoabas".$Pri."\">CONTRATO</a></td>\n";
    $htm .=	"					<td background=\"../midia/aba_".$Pri."_d.gif\" width=\"4\" valign=\"center\"></td>\n";
    $htm .=	"				</tr>\n";
    $htm .=	"			</table>\n";
    $htm .=	"		</td>\n";

    $htm .=	"		<td valign=\"bottom\">\n";
    $htm .=	"			<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" summary=\"\">\n";
    $htm .=	"		   	<tr>\n";
    $htm .=	"				<td background=\"../midia/aba_".$Seg."_e.gif\" width=\"10\" valign=\"center\">&nbsp;</td>\n";
    $htm .=	"			   	<td background=\"../midia/aba_".$Seg.".gif\" valign=\"center\" class=\"textoabasoff\"><a href=\"javascript:Submete('B', 'B');\" class=\"textoabas".$Seg."\">ITENS</a></td>\n";
    $htm .=	"			   	<td background=\"../midia/aba_".$Seg."_d.gif\" width=\"4\" valign=\"center\"></td>\n";
    $htm .=	"		   	</tr>\n";
    $htm .=	"			</table>\n";
    $htm .=	"		</td>\n";

    $htm .=	"		<td valign=\"bottom\">\n";
    $htm .=	"			<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" summary=\"\">\n";
    $htm .=	"				<tr>\n";
    $htm .=	"					<td background=\"../midia/aba_".$Ter."_e.gif\" width=\"10\" valign=\"center\">&nbsp;</td>\n";
    $htm .=	"					<td background=\"../midia/aba_".$Ter.".gif\" valign=\"center\" class=\"textoabasoff\"><a href=\"javascript:Submete('C');\" class=\"textoabas".$Ter."\">ADTIVOS</a></td>\n";
    $htm .=	"					<td background=\"../midia/aba_".$Ter."_d.gif\" width=\"4\" valign=\"center\"></td>\n";
    $htm .=	"				</tr>\n";
    $htm .=	"			</table>\n";
    $htm .=	"		</td>\n";

    $htm .=	"		</td>\n";
    $htm .=	"		<td valign=\"bottom\">\n";
    $htm .=	"			<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" summary=\"\">\n";
    $htm .=	"				<tr>\n";
    $htm .=	"					<td background=\"../midia/aba_".$Qua."_e.gif\" width=\"10\" valign=\"center\">&nbsp;</td>\n";
    $htm .=	"					<td background=\"../midia/aba_".$Qua.".gif\" valign=\"center\" class=\"textoabasoff\"><a href=\"javascript:Submete('D');\" class=\"textoabas".$Qua."\">APOSTILAMENTOS</a></td>\n";
    $htm .=	"					<td background=\"../midia/aba_".$Qua."_d.gif\" width=\"4\" valign=\"center\"></td>\n";
    $htm .=	"				</tr>\n";
    $htm .=	"			</table>\n";
    $htm .=	"		</td>\n";

    // $htm .=	"		<td valign=\"bottom\">\n";
    // $htm .=	"			<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" summary=\"\">\n";
    // $htm .=	"				<tr>\n";
    // $htm .=	"					<td background=\"../midia/aba_".$Qui."_e.gif\" width=\"10\" valign=\"center\">&nbsp;</td>\n";
    // $htm .=	"					<td background=\"../midia/aba_".$Qui.".gif\" valign=\"center\" class=\"textoabasoff\"><a href=\"javascript:Submete('E');\" class=\"textoabas".$Qui."\">&nbsp;ITENS&nbsp;</a></td>\n";
    // $htm .=	"					<td background=\"../midia/aba_".$Qui."_d.gif\" width=\"4\" valign=\"center\">&nbsp;</td>\n";
    // $htm .=	"				</tr>\n";
    // $htm .=	"			</table>\n";
    // $htm .=	"		</td>\n";
    $htm .= "	</tr>\n";
    $htm .= "</table>\n";
    return $htm;
}
// Função de navegação das abas de incluir
function NavegacaoAbasIncluir($Pri,$Seg){
    $htm  = "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" bordercolor=\"#75ADE6\" summary=\"\">";
    $htm .= "	<tr>\n";
    $htm .= "		<td valign=\"bottom\">\n";
    $htm .=	"			<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" summary=\"\">\n";
    $htm .=	"				<tr>\n";
    $htm .=	"					<td background=\"../midia/aba_".$Pri."_e.gif\" width=\"10\" valign=\"center\">&nbsp;</td>\n";
    $htm .=	"					<td background=\"../midia/aba_".$Pri.".gif\" valign=\"center\" class=\"textoabason\"><a href=\"javascript:Submete('A');\" class=\"textoabas".$Pri."\">CONTRATO</a></td>\n";
    $htm .=	"					<td background=\"../midia/aba_".$Pri."_d.gif\" width=\"4\" valign=\"center\"></td>\n";
    $htm .=	"				</tr>\n";
    $htm .=	"			</table>\n";
    $htm .=	"		</td>\n";

    $htm .=	"		<td valign=\"bottom\">\n";
    $htm .=	"			<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" summary=\"\">\n";
    $htm .=	"		   	<tr>\n";
    $htm .=	"				<td background=\"../midia/aba_".$Seg."_e.gif\" width=\"10\" valign=\"center\">&nbsp;</td>\n";
    $htm .=	"			   	<td background=\"../midia/aba_".$Seg.".gif\" valign=\"center\" class=\"textoabasoff\"><a href=\"javascript:Submete('B');\" class=\"textoabas".$Seg."\">ITENS</a></td>\n";
    $htm .=	"			   	<td background=\"../midia/aba_".$Seg."_d.gif\" width=\"4\" valign=\"center\"></td>\n";
    $htm .=	"		   	</tr>\n";
    $htm .=	"			</table>\n";
    $htm .=	"		</td>\n";

    $htm .= "	</tr>\n";
    $htm .= "</table>\n";
    return $htm;
}
// Função de navegação das abas de Manter
function NavegacaoAbasManter($Pri,$Seg){
    $htm  = "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" bordercolor=\"#75ADE6\" summary=\"\">";
    $htm .= "	<tr>\n";
    $htm .= "		<td valign=\"bottom\">\n";
    $htm .=	"			<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" summary=\"\">\n";
    $htm .=	"				<tr>\n";
    $htm .=	"					<td background=\"../midia/aba_".$Pri."_e.gif\" width=\"10\" valign=\"center\">&nbsp;</td>\n";
    $htm .=	"					<td background=\"../midia/aba_".$Pri.".gif\" valign=\"center\" class=\"textoabason\"><a href=\"javascript:Submete('A');\" class=\"textoabas".$Pri."\">CONTRATO</a></td>\n";
    $htm .=	"					<td background=\"../midia/aba_".$Pri."_d.gif\" width=\"4\" valign=\"center\"></td>\n";
    $htm .=	"				</tr>\n";
    $htm .=	"			</table>\n";
    $htm .=	"		</td>\n";

    $htm .=	"		<td valign=\"bottom\">\n";
    $htm .=	"			<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" summary=\"\">\n";
    $htm .=	"		   	<tr>\n";
    $htm .=	"				<td background=\"../midia/aba_".$Seg."_e.gif\" width=\"10\" valign=\"center\">&nbsp;</td>\n";
    $htm .=	"			   	<td background=\"../midia/aba_".$Seg.".gif\" valign=\"center\" class=\"textoabasoff\"><a href=\"javascript:Submete('B');\" class=\"textoabas".$Seg."\">ITENS</a></td>\n";
    $htm .=	"			   	<td background=\"../midia/aba_".$Seg."_d.gif\" width=\"4\" valign=\"center\"></td>\n";
    $htm .=	"		   	</tr>\n";
    $htm .=	"			</table>\n";
    $htm .=	"		</td>\n";

    $htm .= "	</tr>\n";
    $htm .= "</table>\n";
    return $htm;
}
function NavegacaoAbasConsolidado($Pri,$Seg){
    $htm  = "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" bordercolor=\"#75ADE6\" summary=\"\">";
    $htm .= "	<tr>\n";
    $htm .= "		<td valign=\"bottom\">\n";
    $htm .=	"			<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" summary=\"\">\n";
    $htm .=	"				<tr>\n";
    $htm .=	"					<td background=\"../midia/aba_".$Pri."_e.gif\" width=\"10\" valign=\"center\">&nbsp;</td>\n";
    $htm .=	"					<td background=\"../midia/aba_".$Pri.".gif\" valign=\"center\" class=\"textoabason\"><a href=\"javascript:Submete('A');\" class=\"textoabas".$Pri."\">CONTRATO CONSOLIDADO</a></td>\n";
    $htm .=	"					<td background=\"../midia/aba_".$Pri."_d.gif\" width=\"4\" valign=\"center\"></td>\n";
    $htm .=	"				</tr>\n";
    $htm .=	"			</table>\n";
    $htm .=	"		</td>\n";

    $htm .=	"		<td valign=\"bottom\">\n";
    $htm .=	"			<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" summary=\"\">\n";
    $htm .=	"		   	<tr>\n";
    $htm .=	"				<td background=\"../midia/aba_".$Seg."_e.gif\" width=\"10\" valign=\"center\">&nbsp;</td>\n";
    $htm .=	"			   	<td background=\"../midia/aba_".$Seg.".gif\" valign=\"center\" class=\"textoabasoff\"><a href=\"javascript:Submete('B');\" class=\"textoabas".$Seg."\">CONTRATO ORIGINAL</a></td>\n";
    $htm .=	"			   	<td background=\"../midia/aba_".$Seg."_d.gif\" width=\"4\" valign=\"center\"></td>\n";
    $htm .=	"		   	</tr>\n";
    $htm .=	"			</table>\n";
    $htm .=	"		</td>\n";

    $htm .= "	</tr>\n";
    $htm .= "</table>\n";
    return $htm;
}

function NavegacaoAbasConsolidadoComMedicao($Pri,$Seg,$Ter){
    $htm  = "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" bordercolor=\"#75ADE6\" summary=\"\">";
    $htm .= "	<tr>\n";
    $htm .= "		<td valign=\"bottom\">\n";
    $htm .=	"			<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" summary=\"\">\n";
    $htm .=	"				<tr>\n";
    $htm .=	"					<td background=\"../midia/aba_".$Pri."_e.gif\" width=\"10\" valign=\"center\">&nbsp;</td>\n";
    $htm .=	"					<td background=\"../midia/aba_".$Pri.".gif\" valign=\"center\" class=\"textoabason\"><a href=\"javascript:Submete('A');\" class=\"textoabas".$Pri."\">CONTRATO CONSOLIDADO</a></td>\n";
    $htm .=	"					<td background=\"../midia/aba_".$Pri."_d.gif\" width=\"4\" valign=\"center\"></td>\n";
    $htm .=	"				</tr>\n";
    $htm .=	"			</table>\n";
    $htm .=	"		</td>\n";

    $htm .=	"		<td valign=\"bottom\">\n";
    $htm .=	"			<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" summary=\"\">\n";
    $htm .=	"		   	<tr>\n";
    $htm .=	"				<td background=\"../midia/aba_".$Seg."_e.gif\" width=\"10\" valign=\"center\">&nbsp;</td>\n";
    $htm .=	"			   	<td background=\"../midia/aba_".$Seg.".gif\" valign=\"center\" class=\"textoabasoff\"><a href=\"javascript:Submete('B');\" class=\"textoabas".$Seg."\">CONTRATO ORIGINAL</a></td>\n";
    $htm .=	"			   	<td background=\"../midia/aba_".$Seg."_d.gif\" width=\"4\" valign=\"center\"></td>\n";
    $htm .=	"		   	</tr>\n";
    $htm .=	"			</table>\n";
    $htm .=	"		</td>\n";

    $htm .=	"		<td valign=\"bottom\">\n";
    $htm .=	"			<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" summary=\"\">\n";
    $htm .=	"		   	<tr>\n";
    $htm .=	"				<td background=\"../midia/aba_".$Ter."_e.gif\" width=\"10\" valign=\"center\">&nbsp;</td>\n";
    $htm .=	"			   	<td background=\"../midia/aba_".$Ter.".gif\" valign=\"center\" class=\"textoabasoff\"><a href=\"javascript:Submete('C');\" class=\"textoabas".$Ter."\">MEDIÇÃO</a></td>\n";
    $htm .=	"			   	<td background=\"../midia/aba_".$Ter."_d.gif\" width=\"4\" valign=\"center\"></td>\n";
    $htm .=	"		   	</tr>\n";
    $htm .=	"			</table>\n";
    $htm .=	"		</td>\n";

    $htm .= "	</tr>\n";
    $htm .= "</table>\n";
    return $htm;
}

function NavegacaoAbasConsolidadoComMedicaoEAditivo($Pri,$Seg,$Ter,$qtdMedicao,$Qua,$qtdAdit,$Quin,$qtdApost){
    $htm  = "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" bordercolor=\"#75ADE6\" summary=\"\">";
    $htm .= "	<tr>\n";
    $htm .= "		<td valign=\"bottom\">\n";
    $htm .=	"			<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" summary=\"\">\n";
    $htm .=	"				<tr>\n";
    $htm .=	"					<td background=\"../midia/aba_".$Pri."_e.gif\" width=\"10\" valign=\"center\">&nbsp;</td>\n";
    $htm .=	"					<td background=\"../midia/aba_".$Pri.".gif\" valign=\"center\" class=\"textoabason\"><a href=\"javascript:Submete('A');\" class=\"textoabas".$Pri."\">CONTRATO CONSOLIDADO</a></td>\n";
    $htm .=	"					<td background=\"../midia/aba_".$Pri."_d.gif\" width=\"4\" valign=\"center\"></td>\n";
    $htm .=	"				</tr>\n";
    $htm .=	"			</table>\n";
    $htm .=	"		</td>\n";

    $htm .=	"		<td valign=\"bottom\">\n";
    $htm .=	"			<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" summary=\"\">\n";
    $htm .=	"		   	<tr>\n";
    $htm .=	"				<td background=\"../midia/aba_".$Seg."_e.gif\" width=\"10\" valign=\"center\">&nbsp;</td>\n";
    $htm .=	"			   	<td background=\"../midia/aba_".$Seg.".gif\" valign=\"center\" class=\"textoabasoff\"><a href=\"javascript:Submete('B');\" class=\"textoabas".$Seg."\">CONTRATO ORIGINAL</a></td>\n";
    $htm .=	"			   	<td background=\"../midia/aba_".$Seg."_d.gif\" width=\"4\" valign=\"center\"></td>\n";
    $htm .=	"		   	</tr>\n";
    $htm .=	"			</table>\n";
    $htm .=	"		</td>\n";

    if(!empty($qtdMedicao)){
        $htm .=	"		<td valign=\"bottom\">\n";
        $htm .=	"			<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" summary=\"\">\n";
        $htm .=	"		   	<tr>\n";
        $htm .=	"				<td background=\"../midia/aba_".$Ter."_e.gif\" width=\"10\" valign=\"center\">&nbsp;</td>\n";
        $htm .=	"			   	<td background=\"../midia/aba_".$Ter.".gif\" valign=\"center\" class=\"textoabasoff\"><a href=\"javascript:Submete('C');\" class=\"textoabas".$Ter."\">MEDIÇÃO</a></td>\n";
        $htm .=	"			   	<td background=\"../midia/aba_".$Ter."_d.gif\" width=\"4\" valign=\"center\"></td>\n";
        $htm .=	"		   	</tr>\n";
        $htm .=	"			</table>\n";
        $htm .=	"		</td>\n";
    }
    if(!empty($qtdAdit)){
        $numAba = 1;
        for($i=0; $i < count($qtdAdit); $i++){
                $htm .=	"		<td valign=\"bottom\">\n";
                $htm .=	"			<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" summary=\"\">\n";
                $htm .=	"		   	<tr>\n";
                $htm .=	"				<td background=\"../midia/aba_".$Qua."_e.gif\" width=\"10\" valign=\"center\">&nbsp;</td>\n";
                $htm .=	"			   	<td background=\"../midia/aba_".$Qua.".gif\" valign=\"center\" class=\"textoabasoff\"><a href=\"javascript:Submete('D','".$i."');\" class=\"textoabas".$Qua."\">ADITIVO ".$qtdAdit[$i]->aaditinuad."</a></td>\n";
                $htm .=	"			   	<td background=\"../midia/aba_".$Qua."_d.gif\" width=\"4\" valign=\"center\"></td>\n";
                $htm .=	"		   	</tr>\n";
                $htm .=	"			</table>\n";
                $htm .=	"		</td>\n";
                $numAba++;
        }
    }
    if(!empty($qtdApost)){
        $numAba2 = 1;
        for($i=0; $i < count($qtdApost); $i++){
                $htm .=	"		<td valign=\"bottom\">\n";
                $htm .=	"			<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" summary=\"\">\n";
                $htm .=	"		   	<tr>\n";
                $htm .=	"				<td background=\"../midia/aba_".$Quin."_e.gif\" width=\"10\" valign=\"center\">&nbsp;</td>\n";
                $htm .=	"			   	<td background=\"../midia/aba_".$Quin.".gif\" valign=\"center\" class=\"textoabasoff\"><a href=\"javascript:Submete('E','','".$i."');\" class=\"textoabas".$Quin."\">APOSTILAMENTO ".$qtdApost[$i]->aapostnuap."</a></td>\n";
                $htm .=	"			   	<td background=\"../midia/aba_".$Quin."_d.gif\" width=\"4\" valign=\"center\"></td>\n";
                $htm .=	"		   	</tr>\n";
                $htm .=	"			</table>\n";
                $htm .=	"		</td>\n";
                $numAba2++;
        }
    }

    $htm .= "	</tr>\n";
    $htm .= "</table>\n";
    return $htm;
}
