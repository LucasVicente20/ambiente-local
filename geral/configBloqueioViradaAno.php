<?php

#------------------------------------------------------------------------
# Portal da DGCO
# Programa: configBloqueioViradaAno.php
# Objetivo: Novas variáveis para serem usadas para definir o período de bloqueio do sistema.
# Autor:    José Almir <jose.almir@pitang.com>
# Data:		12/11/2014
#------------------------------------------------------------------------

$conexaoParametros = Conexao();
$sqlParametros = "SELECT epargedati, epargedatf FROM SFPC.TBPARAMETROSGERAIS WHERE CPARGETBID = (SELECT MAX(CPARGETBID) FROM SFPC.TBPARAMETROSGERAIS)";
$resultadoParametros = executarSQL($conexaoParametros, $sqlParametros);
$DataInicialBloqueioViradaAno = "";
$DataFinalBloqueioViradaAno = "";

if ($linha = $resultadoParametros->fetchRow()) {
	$DataInicialBloqueioViradaAno = $linha[0];
	$DataFinalBloqueioViradaAno = $linha[1];
}

$InventarioDataInicial = $DataInicialBloqueioViradaAno;
$InventarioDataFinal = $DataFinalBloqueioViradaAno;
