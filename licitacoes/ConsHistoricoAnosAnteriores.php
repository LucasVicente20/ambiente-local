<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: ConsHistoricoAnosAnteriores.php
# Autor:    Rossana Lira
# Data:     06/05/03
# Objetivo: Programa de Seleção para Historico da Licitação (Anos Anteriores)
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/licitacoes/ConsHistoricoPesquisar.php' );

$Selecao = 1;
$Url = "ConsHistoricoPesquisar.php?Selecao=$Selecao";
if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
header("location: ".$Url );
exit();
?>
