<?php

#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: ConsRegistroPrecoDownloadDoc.php
# Autor:    Pitang
# Data:     19/02/2015
# Objetivo: Programa de Download dos Documentos da Licitação
#           Apaga arquivo temporário anterior apenas se ele
#           foi criado a mais de 10 minutos
#-------------------------------------------------------------------------

$grupoCodigo = $_GET['GrupoCodigo'];
$processo = $_GET['Processo'];
$processoAno = $_GET['ProcessoAno'];
$comissaoCodigo = $_GET['ComissaoCodigo'];
$orgaoLicitanteCodigo = $_GET['OrgaoLicitanteCodigo'];
$docCodigo = $_GET['DocCodigo'];

$uri  = "../registropreco/ConsRegistroPrecoDownloadDoc.php?";
$uri .= "GrupoCodigo=$grupoCodigo";
$uri .= "&Processo=$processo";
$uri .= "&ProcessoAno=$processoAno";
$uri .= "&ComissaoCodigo=$comissaoCodigo";
$uri .= "&OrgaoLicitanteCodigo=$orgaoLicitanteCodigo";
$uri .= "&DocCodigo=$docCodigo";

header("location: $uri");