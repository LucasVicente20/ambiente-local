<?php
#-----------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadItemDetalhe.php
# Autor:    Roberta Costa
# Data:     09/06/2005
# Objetivo: Programa de Detalhamento de Itens da Requisição de Material
#-----------------------------------------
# Alterado: Carlos Abreu
# Data:     14/06/2006
# Alterado: Ariston Cordeiro
# Data:     25/08/2008	- Se o material for substituido por outro, mostra um link para o substituto
# 											- Agora lista todas as solicitações de compra que o material está relacionado
# Alterado: Rodrigo Melo
# Data:     21/09/2009 	- Alteração para inserir o cadastro de serviços
# Alterado: Ariston Cordeiro
# Data:     26/05/2010 	- Alteração para que o tipo de grupo material ("M") seja o padrão, para permitir
#													compatibilidade com ferramentas antigas que só trabalham com material (e não
#													infomrmam o tipo do grupo).
# Alterado: Luiz Alves
# Data:     01/07/2011  - Demanda Redmine: #427, #428 - Alteração para os almoxarifados, agora somente os gestores de almoxarifados são visualizados.
#
# Alterado: Heraldo
# Data:     30/05/2012  - Alterar Valor da TRP
#
#-------------------------------------------
# OBS.:			- Tabulação 2 espaços
#						- $TipoGrupo, se não for informado, é por padrão "M" (material). Se a ferramenta trabalha que
#							chama esta também com serviços, lembre-se de enviar o valor correto de "TipoGrupo".
#-----------------------------------------------------------------------------
#----------------------------------------------------------------------------
# Alterado: Pitang Agile TI
# Data:     06/08/2015
# Objetivo: CR Redmine 73653 - Materiais > TRP - Diversas funcionalidades
# Versão:   v1.23.0-6-ga19f938
# ---------------------------------------------------------------------------
# Alterado: Pitang Agile TI - Caio Coutinho
# Data: 21/11/2018
# Objetivo: Tarefa Redmine 205798
#-----------------------------------------------
# Alterado: Pitang Agile TI - Caio Coutinho
# Data: 02/12/2018
# Objetivo: Tarefa Redmine 207361
#-----------------------------------------------
# Alterado: João Madson F. B. de Carvalho
# Data: 01/07/2020
# Objetivo: CR #235191
#-----------------------------------------------

// 220038--

# Acesso ao arquivo de funções #
include "../funcoes.php";
include "../compras/funcoesCompras.php";


if (! @require_once dirname(__FILE__) . "/../bootstrap.php") {
    throw new Exception("Error Processing Request - Bootstrap", 1);
}

# Executa o controle de segurança	#
session_start();
Seguranca();

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD']	== "POST" ){
    $Botao    			= $_POST['Botao'];
    $TipoMaterial 	    = $_POST['TipoMaterial'];
    $Grupo   			= $_POST['Grupo'];
    $GrupoDescricao     = strtoupper2(trim($_POST['GrupoDescricao']));
    $Classe    			= $_POST['Classe'];
    $ClasseDescricao    = strtoupper2(trim($_POST['ClasseDescricao']));
    $Subclasse    	    = $_POST['Subclasse'];
    $SubclasseDescricao = strtoupper2(trim($_POST['SubclasseDescricao']));
    $Material  			= $_POST['Material']; // Material ou Serviço
    $MaterialDescricao  = strtoupper2(trim($_POST['MaterialDescricao']));
    $Pesquisa           = $_POST['Pesquisa'];
    $Palavra            = $_POST['Palavra'];
    $Resultado          = $_POST['Resultado'];
    $ProgramaOrigem     = $_POST['ProgramaOrigem'];
}else{
    $ProgramaOrigem	= $_GET['ProgramaOrigem'];
    $Material       = $_GET['Material'];
    $TipoGrupo      = $_GET['TipoGrupo']; // M ou NULL para Material e S para serviço
}

$ata = $_REQUEST['ata'];

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

if( $Botao == "Voltar" ){
		$Enviar = "S";
}

if($TipoGrupo == "S"){
	$Descricao = "Serviço";
} else {
	$TipoGrupo = "M"; // Tipo do grupo é material por padrão
	$Descricao = "Material";
}

function consultarDocumento($carpnosequ, $ccaroesequ) {
    //Madson CR#235191
    $documentos = array();
    $sql = " SELECT carpnosequ, ccaroesequ, cusupocodi, tdcaroulat, cdcarosequ, edcaronome, encode(idcaroanex, 'base64') as idcaroanex
             FROM sfpc.tbdocumentocaronaexternorp 
             WHERE carpnosequ = %d AND ccaroesequ = %d";

    $db = Conexao();
    $res = executarSQL($db, sprintf($sql, $carpnosequ, $ccaroesequ));
    while ($res->fetchInto($item, DB_FETCHMODE_OBJECT)) {
        $item->idcaroanex = base64_decode($item->idcaroanex);
        $documentos[] = $item;
    }
    return $documentos;
}

    function sqlConsultarCaronaOrgaoExterno($ata, $carona)
    {
        $sql = " SELECT coe.*, arpn.farpnotsal FROM sfpc.tbcaronaorgaoexterno coe 
                 LEFT JOIN sfpc.tbataregistropreconova arpn
                 ON coe.carpnosequ = arpn.carpnosequ
                 WHERE coe.ccaroesequ = " . $carona . "
                 AND coe.carpnosequ = " . $ata;

        return $sql;
    }

    function sqlConsultarCentroDeCustoUsuario($cgrempcodi, $cusupocodi, $corglicodi)
    {
        $sql = " SELECT ccp.ccenpocorg, ccp.ccenpounid, ccp.corglicodi
                 FROM sfpc.tbcentrocustoportal ccp
                 WHERE 1=1 ";

        if ($corglicodi != null || $corglicodi != "") {
            $sql .= " AND ccp.corglicodi = %d";
        }

        return sprintf($sql, $corglicodi);
    }

    function sqlConsultarProcurarAta($carpnosequ)
    {
        $sql = " SELECT * FROM sfpc.tbataregistroprecointerna WHERE carpnosequ = %d ";

        return sprintf($sql, $carpnosequ);
    }

    function sqlQuantidadeItemAtaCarona($ata, $item)
    {
        $sql = " SELECT SUM(COALESCE(coe.acoeitqtat,0) + COALESCE(cia.aitcrpqtat,0)) AS qtdTotalOrgao
                 FROM sfpc.tbcaronaorgaoexternoitem coe 
                 INNER JOIN sfpc.tbitemataregistropreconova iarpn 
                    ON iarpn.carpnosequ = coe.carpnosequ AND iarpn.citarpsequ = coe.citarpsequ 
                 LEFT OUTER join sfpc.tbitemcaronainternaatarp cia 
                    ON coe.carpnosequ = cia.carpnosequ and coe.citarpsequ = cia.citarpsequ
                 WHERE coe.carpnosequ = %d
                        AND iarpn.cmatepsequ = %d
                        OR iarpn.cservpsequ = %d ";
        
        $db = Conexao();
        $res = executarSQL($db, sprintf($sql, $ata, $item, $item));

        while ($res->fetchInto($item, DB_FETCHMODE_OBJECT)) {
            $soma = $item;
        }

        return $soma->qtdtotalorgao;
    }	
    
    function sqlQuantidadeItemCaronaInterna($item, $orgaoExterno, $field) {
        $sql = " SELECT SUM(COALESCE(coei.$field,0)) as $field FROM sfpc.tbcaronaorgaoexterno coe
                 LEFT JOIN sfpc.tbcaronaorgaoexternoitem coei 
                    ON coei.ccaroesequ = coe.ccaroesequ
                    AND coei.carpnosequ = coe.carpnosequ
                 WHERE coei.carpnosequ = ".$item->carpnosequ."
                    AND coei.citarpsequ = ".$item->citarpsequ."
                    AND coe.ecaroeorgg like '".$orgaoExterno."' ";

        $db = Conexao();
        $res = executarSQL($db, $sql);
        while ($res->fetchInto($item, DB_FETCHMODE_OBJECT)) {
            $soma = $item;
        }
        
        return $soma;
    }  

    function consultarItensCarona($carpnosequ, $ccaroesequ)
    {

        $sql = " SELECT iarpn.citarpsequ, iarpn.carpnosequ, ca.ccaroesequ, iarpn.citarpsequ, iarpn.aitarporde, iarpn.cmatepsequ, m.ematepdesc, um.eunidmsigl, iarpn.eitarpdescmat,
                        iarpn.cservpsequ, s.eservpdesc, iarpn.eitarpdescse, iarpn.aitarpqtor, iarpn.aitarpqtat, iarpn.vitarpvori, iarpn.vitarpvatu, ca.acoeitqtat, iarpn.citarpnuml
                 FROM sfpc.tbcaronaorgaoexternoitem ca
                 INNER JOIN sfpc.tbitemataregistropreconova iarpn ON iarpn.citarpsequ = ca.citarpsequ AND iarpn.carpnosequ = ca.carpnosequ
                 LEFT JOIN sfpc.tbmaterialportal m ON iarpn.cmatepsequ = m.cmatepsequ
                 LEFT JOIN sfpc.tbservicoportal s ON iarpn.cservpsequ = s.cservpsequ
                 LEFT JOIN sfpc.tbunidadedemedida um ON um.cunidmcodi = m.cunidmcodi
                 WHERE ca.ccaroesequ = %d
                        AND iarpn.carpnosequ = %d
                 ORDER BY iarpn.aitarporde ";

        $db = Conexao();
        $res = executarSQL($db, sprintf($sql, $ccaroesequ, $carpnosequ));

        $itens = array();
        $item = null;
        
        while ($res->fetchInto($item, DB_FETCHMODE_OBJECT)) {
            $itens[] = $item;
        }
        
        if (PEAR::isError($res)) {
            ExibeErroBD(__FILE__ . "\nLinha: ".__LINE__."\nSql: " . $database->getMessage());
        }

        $db->disconnect();
        
        return $itens;
    }

    function procurarAtaInterna($carpnosequ)
    {   
        $db    = Conexao();
        $sql   = sqlConsultarProcurarAta($carpnosequ);        
        $res   = executarSQL($db, $sql);        
        $itens = array();
        $item  = null;
        
        while ($res->fetchInto($item, DB_FETCHMODE_OBJECT)) {
            $itens[] = $item;
        }

        if (PEAR::isError($res)) {
            ExibeErroBD(__FILE__ . "\nLinha: ".__LINE__."\nSql: " . $database->getMessage());
        }
        
        $db->disconnect();
        
        return $itens;
    }

    function consultarCaronaOrgaoExterno($ata, $carona)
    {
        $sql = sqlConsultarCaronaOrgaoExterno($ata, $carona);
        $res = ClaDatabasePostgresql::executarSQL($sql);
        ClaDatabasePostgresql::hasError($res);

        return $res;
    }

    function consultarDCentroDeCustoUsuario($cgrempcodi, $cusupocodi, $corglicodi)
    {   
        $db = Conexao();
        $sql = sqlConsultarCentroDeCustoUsuario($cgrempcodi, $cusupocodi, $corglicodi);        
        $res = executarSQL($db, $sql);        
        $itens = array();
        $item = null;

        while ($res->fetchInto($item, DB_FETCHMODE_OBJECT)) {
            $itens[] = $item;
        }
        
        if (PEAR::isError($res)) {
            ExibeErroBD(__FILE__ . "\nLinha: ".__LINE__."\nSql: " . $database->getMessage());
        }
        
        $db->disconnect();
        
        return $itens;
    }

    $ataInterna         = current(procurarAtaInterna((int)$ata));
    $dto                = consultarDCentroDeCustoUsuario($ataInterna->cgrempcodi, $ataInterna->cusupocodi, $ataInterna->corglicodi);
    $objeto             = current($dto);
    $numeroAtaFormatado = $objeto->ccenpocorg . str_pad($objeto->ccenpounid, 2, '0', STR_PAD_LEFT);
    $numeroAtaFormatado .= "." . str_pad($ataInterna->carpincodn, 4, "0", STR_PAD_LEFT) . "/" . $ataInterna->aarpinanon;
    $caronaOrgao        = consultarCaronaOrgaoExterno($ata, $_REQUEST['SeqSolicitacao']);
    $itens              = consultarItensCarona($ata, $_REQUEST['SeqSolicitacao']);
    $processo           = str_pad($ataInterna->clicpoproc, 4, '0', STR_PAD_LEFT) . "/" . $ataInterna->alicpoanop;
    $nomeCarona         = $caronaOrgao[0]->ecaroeorgg;
    $tipoControle       = $caronaOrgao[0]->farpnotsal;

    $field = ($tipoControle == 1) ? 'vcoeitvuti' : 'acoeitqtat';
?>

<html>
<head>
    <title>Portal de Compras - Detalhes do <?php echo $Descricao; ?></title>
    <script language="javascript" src="../import/jquery/jquery.js" type="text/javascript"></script>
    <script language="javascript" src="../import/jquery/jquery.maskmoney.js" type="text/javascript"></script>
    <script language="javascript" src="../import/jquery/jquery.maskedinput.js" type="text/javascript"></script>
    <script language="javascript" src="../funcoes.js" type="text/javascript"></script>

    <script language="javascript" type="text/javascript">
        function enviar(valor){
            document.CadItemDetalhe.Botao.value = valor;
            document.CadItemDetalhe.submit();
        }
        function clean_hex(input) {
            input = input.toUpperCase();
            input = input.replace(/[^A-Fa-f0-9]/g, "");

            return input;
        }
        function Convert(id, nomePdf) {
            var binValue    = $('#'+id).val();
            var filename    = nomePdf;
            var cleaned_hex = clean_hex(binValue);
            var ia = new Array();
                
            for (var i=0; i<cleaned_hex.length/2; i++) {
                var h = cleaned_hex.substr(i*2, 2);
                ia[i] = parseInt(h,16);
            }

            var byteArray = new Uint8Array(ia);

            // create a download anchor tag
            var downloadLink      = document.createElement('a');
            downloadLink.target   = '_blank';
            downloadLink.download = nomePdf;

            // convert downloaded data to a Blob
            var blob = new Blob([byteArray], { type: 'application/pdf' }); 

            // create an object URL from the Blob
            var URL = window.URL || window.webkitURL;
            var downloadUrl = URL.createObjectURL(blob);

            // set object URL as the anchor's href
            downloadLink.href = downloadUrl;

            // append the anchor to document body
            document.body.appendChild(downloadLink);

            // fire a click event on the anchor
            downloadLink.click();

            // cleanup: remove element and revoke object URL
            document.body.removeChild(downloadLink);
            URL.revokeObjectURL(downloadUrl);
        }
        
        $(document).ready(function() {
            $('a').click(function() {
                var nomePdf   = $(this).html().replace(/\<br>/g, '');
                var nomeClass = $(this).prop('class');
                Convert(nomeClass, nomePdf);
            });
        });
    </script>

    <link rel="stylesheet" type="text/css" href="../estilo.css">
</head>
<body background="../midia/bg.jpg" marginwidth="0" marginheight="0">
<form action="CadItemDetalhe.php" method="post" name="CadItemDetalhe">
	<table cellpadding="0" border="0" summary="">
		<tr>
		    <td align="left" colspan="2">
				<?php if( $Mens != 0 ){ ExibeMens($Mensagem,$Tipo,1);	}?>
		 	</td>
		</tr>
		<tr>
			<td class="textonormal">
				<table border="0" cellspacing="0" cellpadding="3" summary="" width="100%">
					<tr>
		      	        <td class="textonormal">
                            <table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" width="100%" class="textonormal" bgcolor="#FFFFFF" summary="">
                                <input type="hidden" name="ProgramaOrigem" value="<?php echo $ProgramaOrigem; ?>">

                                <tr>
                                    <td bgcolor="#75ADE6" valign="middle" align="center" colspan="12" class="titulo3">CARONA ÓRGÃO EXTERNO</td>
                                </tr>
                                <tr>
                                    <td colspan="12" class="textonormal">
                                        <p align="justify"> Para fechar a janela clique no no botão "Voltar". </p>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="12">
                                        <table border="0" width="100%" summary="">
                                            <tbody>
                                                <tr>
                                                    <td bgcolor="#DCEDF7" width="30%" height="20" class="textonormal">Nº da Ata Interna</td>
                                                    <td align="left"  valign="middle" class="titulo3"><?php echo $numeroAtaFormatado ?></td>
                                                </tr>

                                                <tr>
                                                    <td bgcolor="#DCEDF7" width="30%" height="20" class="textonormal">Processo Licitatório</td>
                                                    <td align="left"  valign="middle" class="titulo3"><?php echo $processo  ?></td>
                                                </tr>

                                                <tr>
                                                    <td bgcolor="#DCEDF7" width="30%" height="20" class="textonormal">Órgão Externo Solicitante da Carona</td>
                                                    <td align="left"  valign="middle" class="titulo3"> <?php echo $nomeCarona; ?></td>
                                                </tr>
                                                <tr>
                                                    <td bgcolor="#DCEDF7" width="30%" height="20" class="textonormal">Documentos</td>
                                                    <td align="left"  valign="middle" class="titulo3">
                                                        <?php 
                                                            $documentos = consultarDocumento($_GET['ata'], $_GET['SeqSolicitacao']);
                                                            foreach($documentos as $key => $value) { 
                                                        ?>
                                                            <input type="hidden" value="<?php echo $value->idcaroanex; ?>" id="documento<?php echo $key; ?>">
                                                            <a href="#" class="documento<?php echo $key; ?>"><?php echo $value->edcaronome; ?></a><br>
                                                        <?php } ?>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </td>
                                </tr>

                                <tr>
                                    <td bgcolor="#75ADE6" valign="middle" align="center" class="titulo3 itens_material" colspan="12">ITENS DA ATA</td>
                                </tr>

                                <tr class="head_principal">
                                    <td class="textoabason" bgcolor="#DCEDF7" width="40px" align="center">ORD.</td>
                                    <td class="textoabason" bgcolor="#DCEDF7" align="center">TIPO</td>
                                    <td class="textoabason" bgcolor="#DCEDF7" align="center">COD. REDUZIDO</td>
                                    <td class="textoabason" bgcolor="#DCEDF7" width="300px" align="center">DESCRIÇÃO</td>
                                    <td class="textoabason" bgcolor="#DCEDF7" width="50px" align="center">UND.</td>
                                    <td class="textoabason" bgcolor="#DCEDF7" width="55px" align="center">QUANTIDADE</td>
                                    <td class="textoabason" bgcolor="#DCEDF7" width="50px" align="center">VALOR UNITÁRIO</td>
                                    <td class="textoabason" bgcolor="#DCEDF7" width="50px" align="center">VALOR TOTAL</td>
                                    <td class="textoabason" bgcolor="#DCEDF7" width="40px" align="center">LOTE</td>
                                    <?php if($tipoControle == 1) { ?>
                                        <td class="textoabason" bgcolor="#DCEDF7" width="50px" align="center">SALDO VALOR CARONA</td>
                                        <td class="textoabason" bgcolor="#DCEDF7" width="50px" align="center">VALOR SOLICITADO</td>
                                        <?php } else { ?>
                                            <td class="textoabason" bgcolor="#DCEDF7" width="50px" align="center">SALDO QTDE CARONA</td>
                                            <td class="textoabason" bgcolor="#DCEDF7" width="50px" align="center">QUANTIDADE SOLICITADA</td>
                                    <?php } ?>
                                    
                                    <td class="textoabason" bgcolor="#DCEDF7" width="50px" align="center">VALOR TOTAL ATUAL</td>
                                </tr>
                                
                                <?php foreach ($itens as $key => $item) { 

                                    $itemCodigo = $item->cservpsequ == null ? $item->cmatepsequ : $item->cservpsequ;
                                    
                                    $resultado = sqlQuantidadeItemAtaCarona($ata, $itemCodigo);
                                    $buscarCaronaOrgao = sqlQuantidadeItemCaronaInterna($item, $caronaNome, $field);
                                    $buscarCaronaOrgao->{$field} = converte_valor_estoques($buscarCaronaOrgao->{$field});

                                    $quantidadeSolicitadaCarona = 0;
                                    if ($resultado > 0) {
                                        $quantidadeSolicitadaCarona = converte_valor_estoques($resultado);
                                    }

                                    $db = Conexao();
                                    $totalCaronaInterna = getQtdTotalOrgaoCaronaInterna($db, null, $item->carpnosequ, $item->citarpsequ);
                                    $totalCaronaInternaID = getQtdTotalOrgaoCaronaInternaInclusaoDireta($db, $item->carpnosequ, $item->citarpsequ);
                                    $totalCaronaExterna = getQtdTotalOrgaoCaronaExterna($db, $item->carpnosequ, $item->citarpsequ);
                                    $fatorMaxCarona = getFatorQtdMaxCarona($db);
                                    $db->disconnect();

                                    $total = $totalCaronaInterna + $totalCaronaInternaID + $totalCaronaExterna;
                                    $qtdItem = ($item->aitarpqtat != 0) ? $item->aitarpqtat : $item->aitarpqtor;            
                                    if($tipoControle == 1) {
                                        $qtdItem = ($item->vitarpvatu != 0) ? $item->vitarpvatu : $item->vitarpvori;
                                    }

                                    $saldoCarona =  converte_valor_estoques(($fatorMaxCarona * $qtdItem) - $total);
                                    if ($saldoCarona < 0) {
                                        $saldoCarona = 0;
                                    }

                                    $valorQtdOriginal = ($item->aitarpqtat != 0) ? $item->aitarpqtat : $item->aitarpqtor;
                                    $valorOriginal    = ($item->vitarpvatu != 0) ? $item->vitarpvatu : $item->vitarpvori;
                                    $saldoQtdCarona   = ($saldoCarona < $valorQtdOriginal) ? $saldoCarona : $valorQtdOriginal;
                                    $saldoValorCarona = 0;
                                    if(!empty($buscarCaronaOrgao)) {
                                        $saldoQtdCarona = ($buscarCaronaOrgao->acoeitqtat < $saldoQtdCarona) ? $buscarCaronaOrgao->acoeitqtat  : $saldoQtdCarona;
                                    }
                                    // var_dump($saldoQtdCarona);

                                    if($tipoControle == 1) {
                                        $z = str_replace('.', '', $saldoCarona) * 100;
                                        $y = converte_valor_estoques($valorOriginal) * 100;
                                        $saldoValorCarona = ($z < $y) ? $saldoCarona : converte_valor_estoques($valorOriginal);
                                        if(!empty($buscarCaronaOrgao->vcoeitvuti)) {
                                            $saldoValorCarona = converte_valor_estoques($valorOriginal - str_replace('.', '', $buscarCaronaOrgao->vcoeitvuti));
                                        }                    
                                    }
                                    // CADUM = material e CADUS = serviço
                                    $tipo = 'CADUM';
                                    if (is_null($item->cmatepsequ) == true) {
                                        $tipo = 'CADUS';
                                    }

                                    // Código do item
                                    $valorCodigo = $item->cmatepsequ;
                                    if ($tipo == 'CADUS') {
                                        $valorCodigo = $item->cservpsequ;
                                    }

                                    // Descrição do item
                                    $valorDescricao = $item->ematepdesc;
                                    if ($tipo === 'CADUS') {
                                        $valorDescricao = $item->eservpdesc;
                                    }	
                                    
                                ?>							

                                <tr>
                                    <!--  Coluna 1 = Codido-->
                                    <!-- BEGIN BLOCO_RESULTADO_ATAS -->
                                    <td align="center" style="text-align: center" class="textonormal">								
                                        <?php echo $item->aitarporde ?>
                                    </td>
                                    <td align="center" style="text-align: center" class="textonormal"><?php echo $tipo ?></td>
                                    <td align="center" style="text-align: center" class="textonormal">
                                        <?php echo $valorCodigo ?>								
                                    </td>
                                    <td class="textonormal" width="300px" align="center"><?php echo $valorDescricao ?>	</td>
                                    <td class="textonormal" width="50px" align="center"><?php echo $item->eunidmsigl ?></td>
                                    <td class="textonormal" width="55px" align="center">
                                    <?php $valor_qtd = ($item->aitarpqtat != 0) ? $item->aitarpqtat : $item->aitarpqtor; ?>
                                        <?php echo converte_valor_licitacao($valor_qtd) ?>
                                    </td>
                                    <td class="textonormal" width="50px" align="center">
                                        <?php $valor_unitario = ($item->vitarpvatu != 0) ? $item->vitarpvatu : $item->vitarpvori; ?>
                                        <?php echo converte_valor_licitacao($valor_unitario) ?>
                                    </td>
                                    <td class="textonormal" width="50px" align="center"><?php echo converte_valor_licitacao($valor_unitario * $valor_qtd) ?></td>
                                    <td class="textonormal" width="40px" align="center"><?php echo $item->citarpnuml ?></td>
                                    <?php if($tipoControle == 1) { ?>
                                    <td class="textonormal" width="50px" align="center"> <?php echo converte_valor_estoques($saldoQtdCarona) ?> </td>                                    
                                    <td class="textonormal" width="50px" align="center"> <?php echo converte_valor_estoques($item->acoeitqtat); ?> </td>
                                    <?php  } else { ?>
                                    <td class="textonormal" width="50px" align="center"> <?php echo converte_valor_estoques($saldoCarona) ?> </td>                                    
                                    <td class="textonormal" width="50px" align="center"> <?php echo converte_valor_estoques($item->vcoeitvuti); ?> </td>
                                    <?php  } ?>
                                    <td class="textonormal" width="50px" align="center"><?php echo converte_valor_estoques($item->acoeitqtat * $valor_unitario) ?></td>
                                </tr>
                                <?php } ?>                                                        
                                
                                <tr>
                                    <td colspan="12" align="right">                                    
                                        <input type="button" value="Voltar" class="botao" onclick="javascript:self.close();">
                                        <input type="hidden" name="Botao" value="">
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
	</table>
</form>

<script language="javascript" type="">
    window.focus();
</script>
</body>
</html>
