<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadPregaoPresencialSessaoPublica.php
# Autor:    Hélio Miranda
# Data:     16/08/2016
# Objetivo: Programa de Pregão Presencial
#-------------------------------------------------------------------------
# Alterado: Lucas Baracho
# Data:		23/05/2018
# Objetivo: Tarefa Redmine 194641
#-------------------------------------------------------------------------
# Alterado: Lucas Baracho
# Data:		26/06/2018
# Objetivo: Tarefa Redmine 197389
#-------------------------------------------------------------------------
# Alterado: Lucas Baracho
# Data:		27/08/2018
# Objetivo: Tarefa Redmine 202388
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";
include "funcoesFornecedores.php";
include "funcoesPregaoPresencial.php";
# Abas
include "AbaMembroComissao.php";
include "AbaClassificacao.php";
include "AbaFornecedor.php";
include "AbaFornecedorCredenciado.php";
include "AbaItemPregao.php";

# Executa o controle de segurança	#
session_start();
Seguranca();

# Variáveis com o global off #

if ($_SERVER['REQUEST_METHOD'] == "POST") {
	$Origem            	= $_POST['Origem'];
	$Destino           	= $_POST['Destino'];
	$IdMembro		   	= $_POST['Membro'];
	$IdFornecedorInsc  	= $_POST['IdFornecedorInsc'];
	$SituacaoFornecedor = $_POST['SituacaoFornecedor'];
	$CodLoteSelecionado = $_POST['CodLoteSelecionado'];
	
	$_SESSION['Botao'] = $_POST['Botao'];
} else {
	$Origem            = $_GET['Origem'];
	$Destino           = $_GET['Destino'];
} 

$Processo             = $_SESSION['Processo'];
$ProcessoAno          = $_SESSION['ProcessoAno'];
$ComissaoCodigo       = $_SESSION['ComissaoCodigo'];
$OrgaoLicitanteCodigo = $_SESSION['OrgaoLicitanteCodigo'];
$PregaoCod			= $_SESSION['PregaoCod'];
																				
$_SESSION['CodFornecedorSelecionadoClassificacao'] 	= null;
$_SESSION['CodLoteSelecionadoClassificacao'] 		= null;
$_SESSION['CodSituacaoSelecionadoClassificacao'] 	= null;
    
$db = Conexao();

$sql  = "SELECT	LIC.CCOMLICODI,
				COM.ECOMLIDESC,
				LIC.CLICPOPROC,
				LIC.ALICPOANOP,
				LIC.CMODLICODI,
				MOD.EMODLIDESC,
        		LIC.FLICPOREGP,
				LIC.CLICPOCODL,
				LIC.ALICPOANOL,
				LIC.XLICPOOBJE,
				LIC.CORGLICODI,
				LIC.FLICPOVFOR,
				OL.EORGLIDESC
		FROM    SFPC.TBLICITACAOPORTAL LIC
        		INNER JOIN SFPC.TBCOMISSAOLICITACAO COM		ON LIC.CCOMLICODI = COM.CCOMLICODI
        		INNER JOIN SFPC.TBMODALIDADELICITACAO MOD	ON LIC.CMODLICODI = MOD.CMODLICODI
				INNER JOIN SFPC.TBORGAOLICITANTE OL			ON LIC.CORGLICODI = OL.CORGLICODI
    	WHERE   LIC.CLICPOPROC = $Processo
				AND LIC.ALICPOANOP = $ProcessoAno
      			AND LIC.CCOMLICODI = $ComissaoCodigo
				AND LIC.corglicodi = $OrgaoLicitanteCodigo
    	ORDER BY LIC.CCOMLICODI ASC";

$res = $db->query($sql);

if ( PEAR::isError($res) ) {
  $CodErroEmail  = $res->getCode();
  $DescErroEmail = $res->getMessage();
  var_export($DescErroEmail);
  ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
} else {
  $Linha = $res->fetchRow();
  
  $_SESSION['CodigoComissao']			= $Linha[0];
  $_SESSION['NomeComissao']				= $Linha[1];
  $_SESSION['NumeroDoProcesso']			= $Linha[2];
  $_SESSION['AnoDoExercicio']			= $Linha[3];
  $_SESSION['Modalidade']				= $Linha[5]; 
  $_SESSION['RegistroPreco'] 			= $Linha[6];
  $_SESSION['Licitação']				= $Linha[7];
  $_SESSION['AnoLicitação']				= $Linha[8];
  $_SESSION['Objeto']					= $Linha[9];
  $_SESSION['TratamentoDiferenciado']	= $Linha[11];
  $_SESSION['OrgaoDemandante']			= $Linha[12];
  
  $membrosComissao = array();
}

  
# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

if( $_SESSION['Botao'] == "Voltar" ){
    $_SESSION['Botao'] = "";
    $Url = "CadPregaoPresencialSelecionar.php";
    if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
    header("location: ".$Url);
    exit;
}

//Alterar Pregão Tipo
if( $_SESSION['Botao'] == "AlterarPregaoTipo" ){
	$_SESSION['Botao'] = "";
	
	if($_SESSION['PregaoTipo'] <> $_POST['PregaoTipoSelecionado'])
	{
		$db = Conexao();
		$sql = " UPDATE		sfpc.tbpregaopresencial SET 	fpregatipo ='".$_POST['PregaoTipoSelecionado']."' WHERE 	cpregasequ =".$_SESSION['PregaoCod'] ;
		$resultUpdate = $db->query($sql);
		$sqlSolicitacoes = " SELECT		fpregatipo FROM 	sfpc.tbpregaopresencial pp WHERE 	pp.cpregasequ  =". $_SESSION['PregaoCod'] ;
		$result= $db->query($sqlSolicitacoes);
		$Linha = $result->fetchRow();

		if( PEAR::isError($result) ){
			ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlSolicitacoes");
		}	

		$_SESSION['PregaoTipo'] = $Linha[0];
		
		$db->disconnect();

		$_SESSION['Mens'] = 1;
		$_SESSION['Tipo'] = 1;
		$_SESSION['Mensagem'] .= "- Tipo de Pregão Presencial alterado com sucesso";
	} else {
		$_SESSION['Mens'] = 1;
		$_SESSION['Tipo'] = 2;
		$_SESSION['Mensagem'] .= "- O Pregão Presenciado já está associado ao tipo selecionado";	
	}	
}

//Remover Membro
if( $_SESSION['Botao'] == "RemoverMembro" ){
	$_SESSION['Botao'] = "";
	
	if(isset($_POST['Membro']))
	{
		$db     = Conexao();
		$sql = " DELETE FROM sfpc.tbpregaopresencialmembro WHERE 	cpregmsequ  = $IdMembro";
		$result = $db->query($sql);

		if( PEAR::isError($result) ){
			ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlSolicitacoes");
		}	

		$db->disconnect();

		$_SESSION['Mens'] = 1;
		$_SESSION['Tipo'] = 1;
		$_SESSION['Mensagem'] .= "- Membro de Comissão removido com sucesso";
	} else {
		$_SESSION['Mens'] = 1;
		$_SESSION['Tipo'] = 2;
		$_SESSION['Mensagem'] .= "- Nenhum Membro de Comissão selecionado";	
	}	
}

//Marcar como Pregoeiro
if( $_SESSION['Botao'] == "MarcarPregoeiro" ){
	$_SESSION['Botao'] = "";
	
	if(isset($_POST['Membro']))
	{
		$db = Conexao();
		$sql = "SELECT		pm.cpregasequ FROM 		sfpc.tbpregaopresencialmembro pm WHERE 		pm.cpregmsequ  = $IdMembro";
		$result = $db->query($sql);

		if( PEAR::isError($result) ){
			ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlSolicitacoes");
		}
		
		$Linha = $result->fetchRow();
		$sql = "UPDATE	sfpc.tbpregaopresencialmembro SET		epregmtipo = 'M' WHERE	cpregasequ  = $Linha[0]";
		$result = $db->query($sql);
		$sql = "UPDATE	sfpc.tbpregaopresencialmembro SET		epregmtipo = 'P' WHERE	cpregmsequ  = $IdMembro";

		$result = $db->query($sql);				
		
		$_SESSION['Mens'] = 1;
		$_SESSION['Tipo'] = 1;
		$_SESSION['Mensagem'] .= "- Membro de Comissão marcado como Pregoeiro";		
		
		$db->disconnect();					
	} else {
		$_SESSION['Mens'] = 1;
		$_SESSION['Tipo'] = 2;
		$_SESSION['Mensagem'] .= "- Nenhum Membro de Comissão selecionado";	
	}
}

//Remover Fornecedor Inscrito
if( $_SESSION['Botao'] == "RemoverFornecedor" ){
	$_SESSION['Botao'] = "";
	
	$PregaoCod = $_SESSION['PregaoCod'];
	
	if(isset($_POST['IdFornecedorInsc'])) {
		$db     = Conexao();
		
		$sqlLote    = "SELECT COUNT(cl.cpregtsequ) FROM	sfpc.tbpregaopresencialclassificacao cl WHERE cl.cpregfsequ = $IdFornecedorInsc";
		$result = $db->query($sqlLote);
		$Linha = $result->fetchRow();
		$intQuantidade = $result->numRows();
		
		if($intQuantidade > 0) {
			# Deleta Classificação #
			$sql  = "DELETE FROM 		sfpc.tbpregaopresencialclassificacao WHERE cpregfsequ = $IdFornecedorInsc";
			
			$res  = $db->query($sql);
			if( PEAR::isError($res) ){
				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlSolicitacoes");
			}						
			
			# Deleta Preço Inicial #
			$sql  = "DELETE FROM 		sfpc.tbpregaopresencialprecoinicial WHERE cpregfsequ = $IdFornecedorInsc";
			$res  = $db->query($sql);
			
			if( PEAR::isError($res) ){
				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlSolicitacoes");
			}			
		}		

		$sql = " DELETE FROM sfpc.tbpregaopresencialfornecedor WHERE 	cpregfsequ  = $IdFornecedorInsc";
		$result = $db->query($sql);

		if( PEAR::isError($result) ){
			ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlSolicitacoes");
		}	

		$sql = "UPDATE sfpc.tbpregaopresencialprecoinicial SET fpregpalan = 0, vpregpvali = 0.00 
                WHERE cpregfsequ IN (
                  SELECT DISTINCT cpregfsequ 
                  FROM sfpc.tbpregaopresencialfornecedor fr, tbpregaopresencial pp 
                  WHERE pp.cpregasequ = fr.cpregasequ 
                        AND pp.cpregasequ = $PregaoCod)";
		$res = $db->query($sql);

		if( PEAR::isError($res) ){
			ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlSolicitacoes");
		}			
		
		$db->disconnect();

		$_SESSION['Mens'] = 1;
		$_SESSION['Tipo'] = 1;
		$_SESSION['Mensagem'] .= "- Fornecedor removido com sucesso!";
	} else {
		$_SESSION['Mens'] = 1;
		$_SESSION['Tipo'] = 2;
		$_SESSION['Mensagem'] .= "- Nenhum Fornecedor selecionado!";	
	}	
}

//Salvar descrição do Lote 
if ($_SESSION['Botao'] == "SalvarDescricaoLote") {
	$_SESSION['Botao'] = "";
	
	if ($_POST['DescricaoLoteSelecionado'] <> "") {
		$Encoding = 'UTF-8';
		$_SESSION['DescricaoLoteSelecionado'] = mb_strtoupper($_POST['DescricaoLoteSelecionado'], $Encoding);
		
		$DescricaoLoteSelecionado = $_SESSION['DescricaoLoteSelecionado'];
		$CodLoteSelecionadoSql    = $_SESSION['CodLoteSelecionado'];
		
		$db = Conexao();
		
		$sql = "UPDATE SFPC.TBPREGAOPRESENCIALLOTE SET    EPREGTDESC = '$DescricaoLoteSelecionado' WHERE  CPREGTSEQU = $CodLoteSelecionadoSql ";
		$result = $db->query($sql);
		
		if (PEAR::isError($result)) {
			ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}
		
		$_SESSION['Mens'] = 1;
		$_SESSION['Tipo'] = 1;
		$_SESSION['Mensagem'] .= "- Descrição do Lote alterada com sucesso!";	

		$db->disconnect();		
	} else {
		$_SESSION['Mens'] = 1;
		$_SESSION['Tipo'] = 2;
		$_SESSION['Mensagem'] .= "- Nenhuma descrição foi escrita para o Lote selecionado!";	
	}	
}

//Selecionar Lote Classificacao

if( $_SESSION['Botao'] == "SelecionarLoteClassificacao" ){
	$_SESSION['Botao'] = "";
	
	if(isset($_POST['CodLoteSelecionado']))
	{
		$_SESSION['CodLoteSelecionado'] = $_POST['CodLoteSelecionado'];
		$db = Conexao();
		
		$sql = "SELECT	pl.cpregtnuml, pl.epregtdesc, sl.epreslnome
                FROM 	sfpc.tbpregaopresenciallote pl,			
                        sfpc.tbpregaopresencialsituacaolote	sl
                WHERE 	pl.cpregtsequ = ".$_SESSION['CodLoteSelecionado']." 
                    AND pl.cpreslsequ = sl.cpreslsequ";
		
		$result = $db->query($sql);
		
		if( PEAR::isError($result) ){
			ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlSolicitacoes");
		}
		
		$LinhaLote = $result->fetchRow();
		
		$_SESSION['NumeroLoteSelecionado'] = $LinhaLote[0];
		$_SESSION['DescricaoLoteSelecionado'] =  $LinhaLote[1];
		$_SESSION['SituacaoLoteSelecionado'] =  $LinhaLote[2];
		
		$db->disconnect();
	} else {
		$_SESSION['Mens'] = 1;
		$_SESSION['Tipo'] = 2;
		$_SESSION['Mensagem'] .= "- Nenhum Lote selecionado!";	
	}	
}

//Fornecedor Selecionado Classificacao

if( $_SESSION['Botao'] == "FornecedorSelecionadoClassificacao" ){
	$_SESSION['Botao'] = "";
	
	if(isset($_POST['CodFornecedorSelecionadoClassificacao']))
	{
		$Cod = $_POST['CodFornecedorSelecionadoClassificacao'];
	}
	
	if(	isset($_POST['CodFornecedorSelecionadoClassificacao']) and 
		isset($_POST['CodLoteSelecionadoClassificacao'.$Cod]) and 
		isset($_POST['CodSituacaoSelecionadoClassificacao'.$Cod]))
	{	
		$_SESSION['CodFornecedorSelecionadoClassificacao'] 	= $_POST['CodFornecedorSelecionadoClassificacao'];
		$_SESSION['CodLoteSelecionadoClassificacao'] 		= $_POST['CodLoteSelecionadoClassificacao'.$Cod];
		$_SESSION['CodSituacaoSelecionadoClassificacao'] 	= $_POST['CodSituacaoSelecionadoClassificacao'.$Cod];
	}
}

//Selecionar Lote

if( $_SESSION['Botao'] == "SelecionarLote" ){
	$_SESSION['Botao'] = "";
	
	if(isset($_POST['CodLoteSelecionado']) and $_POST['CodLoteSelecionado'] > 0) {
        $db = Conexao();
        $_SESSION['CodLoteSelecionado'] = null;
        $_SESSION['CodLoteSelecionado'] = $_POST['CodLoteSelecionado'];

		$sql = "SELECT	pl.cpregtnuml, pl.epregtdesc, pl.vpregtvalv, pl.vpregtvalr, pl.cpregfsequ, sl.epreslnome, sl.cpreslsequ
                FROM 	sfpc.tbpregaopresenciallote	pl, sfpc.tbpregaopresencialsituacaolote	sl
                WHERE 	pl.cpregtsequ = ".$_SESSION['CodLoteSelecionado']." 
						AND pl.cpreslsequ = sl.cpreslsequ";
		
		$result = $db->query($sql);
		
		if( PEAR::isError($result) ){
			ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlSolicitacoes");
		}
		
		$LinhaLote = $result->fetchRow();
		
		if($LinhaLote[4] <> '')
		{
			$sql = "SELECT	pf.npregfrazs, pf.apregfccgc, pf.apregfccpf
                    FROM 	sfpc.tbpregaopresenciallote	pl, sfpc.tbpregaopresencialfornecedor pf
                    WHERE 	pl.cpregtsequ = ".$_SESSION['CodLoteSelecionado']." 
							AND pl.cpregfsequ = pf.cpregfsequ";
			
			$result = $db->query($sql);
			
			if( PEAR::isError($result) ){
				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlSolicitacoes");
			}
			
			$Linha = $result->fetchRow();
			if($Linha[1] == '') {
				$_SESSION['FornecedorVencedorLoteSelecionado'] = (substr($Linha[2], 0, 3).'.'.substr($Linha[2], 3, 3).'.'.substr($Linha[2], 6, 3).'-'.substr($Linha[2], 9, 2));
			} else {
				$_SESSION['FornecedorVencedorLoteSelecionado'] = (substr($Linha[1], 0, 2).'.'.substr($Linha[1], 2, 3).'.'.substr($Linha[1], 5, 3).'/'.substr($Linha[1], 8, 4).'-'.substr($Linha[1], 12, 2));	
			}
			
			$_SESSION['FornecedorVencedorLoteSelecionado'] .=" - ".$Linha[0];
		}
		
		$_SESSION['NumeroLoteSelecionado'] = $LinhaLote[0];
		$_SESSION['DescricaoLoteSelecionado'] =  $LinhaLote[1];
		$_SESSION['ValorLoteSelecionado'] =  $LinhaLote[2];
		$_SESSION['ValorRenegociadoLoteSelecionado'] =  $LinhaLote[3];
		$_SESSION['SituacaoLoteSelecionado'] =  $LinhaLote[5];
		$_SESSION['CodSituacaoLoteSelecionado'] =  $LinhaLote[6];
		$_SESSION['CodFornecedorVencedorLoteSelecionado'] =  $LinhaLote[4];
		
		$db->disconnect();
	} else {
		$_SESSION['Mens'] = 1;
		$_SESSION['Tipo'] = 2;
		$_SESSION['Mensagem'] .= "- Nenhum Lote selecionado!";	
	}	
}

//Salvar Descrição do Lote

if( $_SESSION['Botao'] == "SalvarDescricaoLoteSelecionado" ){
	$_SESSION['Botao'] = "";
	
	if(isset($_POST['CodLoteSelecionado']))
	{
		$_SESSION['CodLoteSelecionado'] = $_POST['CodLoteSelecionado'];
		$db = Conexao();
		$sql = "UPDATE		pl.cpregtnuml, pl.epregtdesc, pl.vpregtvalv, pl.vpregtvalr, pl.cpregfsequ, ps.epreslnome
					FROM 	sfpc.tbpregaopresenciallote			pl,			
							sfpc.tbpregaopresencialsituacaolote		ps
					WHERE 	pl.cpregtsequ  						= ".$_SESSION['CodLoteSelecionado']." 
						AND pl.cpreslsequ						= ps.cpreslsequ"; 		
		
		$sql = "SELECT		pl.cpregtnuml, pl.epregtdesc, pl.vpregtvalv, pl.vpregtvalr, pl.cpregfsequ, ps.epreslnome
					FROM 	sfpc.tbpregaopresenciallote			pl,			
							sfpc.tbpregaopresencialsituacaolote		ps
					WHERE 	pl.cpregtsequ  						= ".$_SESSION['CodLoteSelecionado']." 
						AND pl.cpreslsequ						= ps.cpreslsequ"; 
		
		$result = $db->query($sql);
		
		if( PEAR::isError($result) ){
			ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlSolicitacoes");
		}
		
		$LinhaLote = $result->fetchRow();
		
		$_SESSION['NumeroLoteSelecionado'] = $LinhaLote[0];
		$_SESSION['DescricaoLoteSelecionado'] =  $LinhaLote[1];
		$_SESSION['ValorLoteSelecionado'] =  $LinhaLote[2];
		$_SESSION['ValRenegociadoLoteSelecionado'] =  $LinhaLote[3];
		$_SESSION['SituacaoLoteSelecionado'] =  $LinhaLote[5];
		
		$db->disconnect();
	} else {
		$_SESSION['Mens'] = 1;
		$_SESSION['Tipo'] = 2;
		$_SESSION['Mensagem'] .= "- Nenhum Lote selecionado!";	
	}	
}

//Alterar Situação do Lote

if( $_SESSION['Botao'] == "AplicarSituacaoLote" ){
	$_SESSION['Botao'] = "";
	
	if(isset($_SESSION['CodLoteSelecionado']) and isset($_POST['SituacaoLote']) and $_POST['SituacaoLote'] > 0)
	{
        $db = Conexao();
        $CodLoteSelecionado = $_SESSION['CodLoteSelecionado'];
        $SituacaoLote 		= $_POST['SituacaoLote'];

		if($SituacaoLote == 4) {
			$sql = "UPDATE sfpc.tbpregaopresenciallote SET cpreslsequ = $SituacaoLote WHERE cpregasequ = ".$_SESSION['PregaoCod'];
		} else {
			$sql = "UPDATE sfpc.tbpregaopresenciallote SET cpreslsequ = $SituacaoLote WHERE cpregtsequ = $CodLoteSelecionado";
		}
		
		$res = $db->query($sql);
		
		if( PEAR::isError($res) ){
			ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlSolicitacoes");
		}
		
		$_SESSION['Mens'] = 1;
		$_SESSION['Tipo'] = 1;							
		$_SESSION['Mensagem'] .= "- Situação do Lote alterada com Sucesso! <br />";		

		$sqlB = "SELECT		pl.cpregtnuml, pl.epregtdesc, pl.vpregtvalv, pl.vpregtvalr, pl.cpregfsequ, ps.epreslnome, ps.cpreslsequ
					FROM 	sfpc.tbpregaopresenciallote				pl,			
							sfpc.tbpregaopresencialsituacaolote		ps
					WHERE 	pl.cpregtsequ  							= $CodLoteSelecionado 
						AND pl.cpreslsequ							= ps.cpreslsequ"; 

		$result = $db->query($sqlB);
		
		if( PEAR::isError($result) ){
			ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlSolicitacoes");
		}
		
		$LinhaLote = $result->fetchRow();
		
		$_SESSION['NumeroLoteSelecionado'] = $LinhaLote[0];
		$_SESSION['DescricaoLoteSelecionado'] =  $LinhaLote[1];
		$_SESSION['ValorLoteSelecionado'] =  $LinhaLote[2];
		$_SESSION['ValRenegociadoLoteSelecionado'] =  $LinhaLote[3];
		$_SESSION['SituacaoLoteSelecionado'] =  $LinhaLote[5];
		$_SESSION['CodSituacaoLoteSelecionado'] =  $LinhaLote[6];		
		
		$db->disconnect();		
	} else {
		$_SESSION['Mens'] = 1;
		$_SESSION['Tipo'] = 2;
		$_SESSION['Mensagem'] .= "- Nenhuma Situação selecionada para o Lote!";	
	}	
}


# Aba de Membro da Comissão  - Formulário A #
if( $Origem == "A" or $Origem == "" ){
    // Verifica sem tem Pregoeiro associado ao pregão
    $db = Conexao();
    $sql = "SELECT		COUNT(pm.cpregmsequ)
            FROM 		sfpc.tbpregaopresencialmembro pm
            WHERE 		pm.cpregasequ  = $PregaoCod
                AND		pm.epregmtipo = 'P'";

    $result = $db->query($sql);

    if( PEAR::isError($result) ){
        ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlSolicitacoes");
    }

    $Linha = $result->fetchRow();

    //Validação Aba A

    if( $_SESSION['Botao'] == "A" ){
            $Destino = "B";
    }
    if($Linha[0] > 0)
    {
        // Verifica sem tem Fornecedor Aguardando
        $db = Conexao();
        $sql = "SELECT COUNT(cpregfsequ) FROM sfpc.tbpregaopresencialfornecedor WHERE cpregasequ = $PregaoCod AND epregfsitu = 'A'";
        $result = $db->query($sql);

        if( PEAR::isError($result) ){
            ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlSolicitacoes");
        }

        $Linha = $result->fetchRow();

        //Validação Aba B
        if( $_SESSION['Botao'] == "B"){
            $Destino = "C";
        }

        if($Linha[0] == 0) {
            ExibeAbas($Destino);
        } else if ($Destino == "C" or $Destino == "D" or $Destino == "E") {
            if($Origem == "A" and $_SESSION['Mens'] == 0) {
                $_SESSION['Mens'] = 1;
                $_SESSION['Tipo'] = 2;
                $_SESSION['Mensagem'] .= "Para avançar, é necessário que todos os Fornecedores estejam com situação diferente de AGUARDANDO";
            }

            ExibeAbas($Origem);
        } else if($_SESSION['Botao'] == "B" or $Destino == "B") {
            $Destino = "B";
            ExibeAbas($Destino);
        } else {
            ExibeAbas($Origem);
        }
    } else {
        if($Origem == "A" and $_SESSION['Mens'] == 0) {
            $_SESSION['Mens'] = 1;
            $_SESSION['Tipo'] = 2;
            $_SESSION['Mensagem'] .= "Para avançar, é necessário que exista um Membro de Comissão marcado como Pregoeiro";
        }

        ExibeAbas($Origem);
    }
}

# Aba de Fornecedores Inscritos - Formulário B #
if( $Origem == "B" ){
    // Verifica sem tem Fornecedor Aguardando
    $db = Conexao();
    $sql = "SELECT COUNT(cpregfsequ) FROM sfpc.tbpregaopresencialfornecedor WHERE cpregasequ = $PregaoCod AND epregfsitu = 'A'";
    $result = $db->query($sql);

    if( PEAR::isError($result) ){
        ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlSolicitacoes");
    }

    $Linha = $result->fetchRow();

    //Validação Aba B
    if( $_SESSION['Botao'] == "B"){
            $Destino = "C";
    }

    if($Linha[0] == 0) {
        ExibeAbas($Destino);
    } else if ($Destino == "C" or $Destino == "D" or $Destino == "E") {
        if($Origem == "B" and $_SESSION['Mens'] == 0) {
            $_SESSION['Mens'] = 1;
            $_SESSION['Tipo'] = 2;
            $_SESSION['Mensagem'] .= "Para avançar, é necessário que todos os Fornecedores estejam com situação diferente de AGUARDANDO";
        }

        ExibeAbas($Origem);
    } else if($_SESSION['Botao'] == "A" or $Destino == "A") {
        $Destino = "A";
        ExibeAbas($Destino);
    } else {
        ExibeAbas($Origem);
    }
}

# Aba de Fornecedores Credenciados - Formulário C #
if( $Origem == "C" ){
    if( $_SESSION['Botao'] == "C" ){
            $Destino = "D";
    }
    ExibeAbas($Destino);
}

# Aba de Itens - Formulário D #
if( $Origem == "D" ){
    if( $_SESSION['Botao'] == "D" ){
            $Destino = "E";
    }

    ExibeAbas($Destino);
}

# Aba de Itens - Formulário D #
if( $Origem == "E" ){
    if( $_SESSION['Botao'] == "E" ){
            $Destino = "A";
    }

    ExibeAbas($Destino);
}

# Função para Chamada do Formulário de cada Aba #
function ExibeAbas($Destino){
	if( $Destino == "A" or $Destino == "" ){
        ExibeAbaMembroComissao();
	} else if( $Destino == "B" ){
        ExibeAbaFornecedor();
	} else if( $Destino == "C" ){
        ExibeAbaFornecedorCredenciado();
	} else if( $Destino == "D" ){
        ExibeAbaClassificacao();
	} else if( $Destino == "E" ){
        ExibeAbaItemPregao();
	}
}

?>
