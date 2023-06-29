<?php

/**
 * 
 * Nome: AtualizaDocumentosFornecedor.php
 * Alterado: Ernesto Ferreira
 * Data:     06/07/2015
 * ----------------------------------------------------------------------------
 * Alterado: Osmar Celestino
 * Data:     18/02/2022
 * Objetivo: Tarefa Redmine  #248924
 * -----------------------------------------------------------------------------
 * Alterado: Lucas Vicente
 * Data:     03/03/2023
 * Objetivo: Tarefa Redmine  #279834
 * -----------------------------------------------------------------------------
 * 
 */
if (!require_once dirname(__FILE__).'/TemplateAppPadrao.php') {
    throw new Exception('Error Processing Request - TemplateAppPadrao.php', 1);
}
require_once '../fornecedores/funcoesFornecedores.php';
require_once '../fornecedores/funcoesDocumento.php';

$tpl = new TemplateAppPadrao('templates/AtualizaDocumentosFornecedor.html', 'AtualizaDocumentosFornecedor');

// Adiciona páginas no MenuAcesso #
AddMenuAcesso('/fornecedores/ConsAcompFornecedorSenha.php');
AddMenuAcesso('/fornecedores/ConsAcompFornecedorSelecionar.php');
AddMenuAcesso('/fornecedores/RelAcompFornecedorPdf.php');
AddMenuAcesso('/fornecedores/CadGestaoFornecedorExcluido.php');
AddMenuAcesso('/oracle/fornecedores/RotDebitoCredorConsulta.php');

// Variáveis com o global off #
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $Irregularidade = $_GET['Irregularidade'];
    $Sequencial = $_GET['Sequencial'];
    $atualizacao= $_GET['atualizacao'];
    
} else {
    $Botao = $_POST['Botao'];
    $_SESSION['Mensagem'] =  $_POST['Mensagem'];
    $_SESSION['Situacao'] = $_REQUEST['Situacao'];
    $Sequencial = $_POST['Sequencial'];
    $DDocumento = $_POST['DDocumento'];
    $download = $_POST['CodDownload'];
}


// Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

// Redireciona o programa de acordo com o botão voltar #
if ($Botao == 'Voltar') {

    header('Location: AtualizaDocumentoSenha.php');
    exit();

} 
////($_SESSION['Arquivos_Upload']);

$db = Conexao();

$tpl->SEQUENCIAL = $Sequencial;
$tpl->ORIGEM = 'Fornecedor';


    // Pega os Dados do Fornecedor Cadastrado #
    $sql = "
        SELECT
            AFORCRSEQU, APREFOSEQU, AFORCRCCGC, AFORCRCCPF, AFORCRIDEN,
            NFORCRORGU, NFORCRRAZS, NFORCRFANT, CCEPPOCODI, CCELOCCODI,
            EFORCRLOGR, AFORCRNUME, EFORCRCOMP, EFORCRBAIR, NFORCRCIDA,
            CFORCRESTA, AFORCRCDDD, AFORCRTELS, AFORCRNFAX, NFORCRMAIL,
            AFORCRCPFC, NFORCRCONT, NFORCRCARG, AFORCRDDDC, AFORCRTELC,
            AFORCRREGJ, DFORCRREGJ, AFORCRINES, AFORCRINME, AFORCRINSM,
            VFORCRCAPS, VFORCRCAPI, VFORCRPATL, VFORCRINLC, VFORCRINLG,
            DFORCRULTB, DFORCRCNFC, NFORCRENTP, AFORCRENTR, AFORCRENTT,
            DFORCRVIGE, DFORCRGERA, FFORCRCUMP, ECOMLIDESC, DFORCRANAL,
            FFORCRMEPP, VFORCRINDI, VFORCRINSO, NFORCRMAI2, FFORCRTIPO,
            DFORCRCONT

                FROM
            SFPC.TBFORNECEDORCREDENCIADO FORN
                LEFT OUTER JOIN SFPC.TBCOMISSAOLICITACAO COM ON FORN.CCOMLICODI = COM.CCOMLICODI
        WHERE AFORCRSEQU = $Sequencial
    ";

    $result = $db->query($sql);
    if (PEAR::isError($result)) {
        ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
    } else {
        $Linha = $result->fetchRow();

        // Variáveis Formulário A #


        if ($Linha[3]) {
            $NOMECNPJCPF = 'CPF';
            $NOME_OR_RAZAO = 'Nome';
            $CPFCNPJ = $Linha[3];
        } else {
            $NOMECNPJCPF = 'CNPJ';
            $NOME_OR_RAZAO = 'Razão Social';
            $CPFCNPJ = $Linha[2];
        }


        $tpl->CPFCNPJDESCRICAOCAMPO = $NOMECNPJCPF;
        $tpl->CPFCNPJ               = $CPFCNPJ;
        $_SESSION['CPF_CNPJ']       = $CPFCNPJ;

        $tpl->NOME = $Linha[6];
        $tpl->DESCRCAMPRAZAOSOC     = $NOME_OR_RAZAO;
    }

if ($Botao == '') {
    $Mens = 0;
    $_SESSION['Mens']      = 0;
    $_SESSION['Mensagem'] = '';


    if($atualizacao==1){
        $_SESSION['Mens']      = 1;
        $_SESSION['Tipo']      = 1;
        $_SESSION['Mensagem'] = 'Alteração realizada com sucesso';
    }


    if($Linha){
        // carrega arquivos cadastrados
        $db = Conexao();
        $sql = "  SELECT doc.cfdocusequ, doc.aprefosequ, doc.aforcrsequ, doc.afdocuanoa, 
                    doc.cfdoctcodi, doc.efdocunome, doc.ifdocuarqu, doc.ffdocuforn, 
                    doc.tfdocuanex, doc.ffdocusitu, doc.cusupocodi, doc.tfdoctulat,
                    (SELECT h.cfdocscodi
                    FROM sfpc.tbfornecedordocumentohistorico h
                    where h.cfdocusequ = doc.cfdocusequ order by h.tfdochulat desc limit 1) as situacao, 
                    (SELECT h.efdochobse
                    FROM sfpc.tbfornecedordocumentohistorico h
                    where h.cfdocusequ = doc.cfdocusequ order by h.tfdochulat desc limit 1) as observacao, 
    
                    t.efdoctdesc, 
    
                    (SELECT h.cusupocodi
                    FROM sfpc.tbfornecedordocumentohistorico h
                    where h.cfdocusequ = doc.cfdocusequ order by h.tfdochulat desc limit 1) as usuarioUltimaAlt, 
                    (SELECT u.eusuporesp
                    FROM sfpc.tbfornecedordocumentohistorico h
                    join sfpc.tbusuarioportal u on h.cusupocodi = u.cusupocodi
                    where h.cfdocusequ = doc.cfdocusequ order by h.tfdochulat desc limit 1) as nomeUsuUltimaAlt, 
                    
                    (SELECT h.tfdochulat
                    FROM sfpc.tbfornecedordocumentohistorico h
                    where h.cfdocusequ = doc.cfdocusequ order by h.tfdochulat desc limit 1) as datahoraUltimaAlt,
                    (SELECT s.efdocsdesc
                        FROM sfpc.tbfornecedordocumentosituacao s
                        where s.cfdocscodi = (SELECT h.cfdocscodi
                        FROM sfpc.tbfornecedordocumentohistorico h
                        where h.cfdocusequ = doc.cfdocusequ order by h.tfdochulat desc limit 1)) as situacao_nome,
                    (SELECT u.eusuporesp
                    FROM sfpc.tbfornecedordocumentohistorico h
                    join sfpc.tbusuarioportal u on h.cusupocodi = u.cusupocodi
                    where h.cfdocusequ = doc.cfdocusequ order by h.tfdochulat asc limit 1) as nomeUsuAnex 
                FROM sfpc.tbfornecedordocumento doc
                join sfpc.tbfornecedordocumentotipo t ON t.cfdoctcodi = doc.cfdoctcodi
                WHERE aforcrsequ = " . $Linha[0];
                if($Linha[1]){
                    $sql .= " OR aprefosequ = " . $Linha[1];
                }
                $sql .= " AND ffdocusitu = 'A' order by tfdoctulat DESC";
        
    
                //print_r($sql);
                //die();
        $result = $db->query($sql);
        if (db :: isError($result)) {
            ExibeErroBD($_SESSION['ErroPrograma'] . "\nLinha: " . __LINE__ . "\nSql: $sql");
        } else {
            
            //die('ENtrou aqui na hora de atualizar...por isso não funcionou...');
            unset($_SESSION['Arquivos_Upload']);
            //$resultado = $result->fetchRow();
            while ($linha = $result->fetchRow()) {
    
                    //verifica se quem cadastrou foi PCR ou o próprio fornecedor
                    $nomeUsuAnex = '';
                    $nomeUsuUltAlt = '';
                    
                    if($linha[7] == 'S'){
    
                        if ($CPFCNPJ != "") {
                            if (strlen($CPFCNPJ) == 14) {
                                $nomeUsuAnex = substr($CPFCNPJ,0,2).".".substr($CPFCNPJ,2,3).".".substr($CPFCNPJ,5,3)."/".substr($CPFCNPJ,8,4)."-".substr($CPFCNPJ,12,2);
                            } else {
                                $nomeUsuAnex = substr($CPFCNPJ,0,3).".".substr($CPFCNPJ,3,3).".".substr($CPFCNPJ,6,3)."-".substr($CPFCNPJ,9,2);
                            }
                        }
    
                        //Usuário que fez a última alteração
                        if($linha[15]>0){
                            $nomeUsuUltAlt = 'PCR - '.$linha[16];
                        }else{
                            $nomeUsuUltAlt = $nomeUsuAnex;
                        }
    
                    }else{
                        $nomeUsuAnex = $linha[19];
    
                        //Usuário que fez a última alteração
                        $nomeUsuUltAlt = 'PCR - '.$linha[16];
    
                    }
    
                $_SESSION['Arquivos_Upload']['nome'][] = $linha[5];
                $_SESSION['Arquivos_Upload']['situacao'][] = 'existente'; // situacao pode ser: novo, existente, cancelado e excluido
                $_SESSION['Arquivos_Upload']['codigo'][] = $linha[0]; // como é um arquivo novo, ainda nao possui código
                $_SESSION['Arquivos_Upload']['tipoCod'][] = $linha[4]; 
                $_SESSION['Arquivos_Upload']['tipoDocumentoDesc'][] = $linha[14]; 
                $_SESSION['Arquivos_Upload']['observacao'][] = $linha[13]; 
                $_SESSION['Arquivos_Upload']['conteudo'][] = $linha[6]; 
                $_SESSION['Arquivos_Upload']['anoAnex'][] = $linha[3];
                $_SESSION['Arquivos_Upload']['dataHora'][] = formatarDataHora($linha[8]); 
    
                $_SESSION['Arquivos_Upload']['codUsuarioUltAlt'][] = $linha[15];
                $_SESSION['Arquivos_Upload']['usuarioUltAlt'][] = $nomeUsuUltAlt;
                $_SESSION['Arquivos_Upload']['usuarioAnex'][] = $nomeUsuAnex;
                $_SESSION['Arquivos_Upload']['externo'][] = $linha[7];
    
                $_SESSION['Arquivos_Upload']['dataHoraUltAlt'][] = formatarDataHora($linha[17]); 
                $_SESSION['Arquivos_Upload']['situacaoHist'][] = $linha[12]; 
                $_SESSION['Arquivos_Upload']['situacaoDesc'][] = $linha[18]; 
    
            }
        }



    }


}elseif ($Botao == 'Atualizar') {
    $Mens = 0;
    $_SESSION['Mens']      = 0;
    $_SESSION['Mensagem'] = '';

			// DOCUMENTOS

			if (count($_SESSION['Arquivos_Upload']) != 0) {
				for ($i=0; $i< count($_SESSION['Arquivos_Upload']); $i++) {

					$arquivo = $_SESSION['Arquivos_Upload'];
					if($arquivo['situacao'][$i] == 'novo'){
						// fazer sql para trazer o sequencial
						$sql = ' SELECT cfdocusequ FROM SFPC.tbfornecedordocumento WHERE  1=1 ORDER BY cfdocusequ DESC limit 1';
						$seqDocumento = resultValorUnico(executarTransacao($db, $sql)) + 1;

						$anexo =  bin2hex($arquivo['conteudo'][$i]);

                        $nomeDocumento = tratarNomeArquivo($arquivo['nome'][$i]);
                        
						$sqlAnexo = "INSERT INTO sfpc.tbfornecedordocumento
						(cfdocusequ, aprefosequ, aforcrsequ, afdocuanoa, cfdoctcodi, efdocunome, ifdocuarqu, ffdocuforn, tfdocuanex, ffdocusitu, cusupocodi, tfdoctulat)
						VALUES(".$seqDocumento.", NULL, ".$Sequencial.",".date('Y').", ".$arquivo['tipoCod'][$i].", '".$nomeDocumento."', decode('".$anexo."','hex'), 'S', now(), 'A', ".$_SESSION['_cusupocodi_'].", now());
						";

						//print_r($sqlAnexo);
                        
                        $resultAnexo = $db->query($sqlAnexo);
                        ////($resultAnexo);
                        //die();
                        //$resultAnexo = executarTransacao($db, $sqlAnexo);
                        

						if (PEAR::isError($resultAnexo)) {
							$db->query("ROLLBACK");
							ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlAnexo");
						}else{
							//insere a fase do documento
							$sqlHist = "INSERT INTO sfpc.tbfornecedordocumentohistorico
									(cfdocusequ, cfdocscodi, efdochobse, tfdochcada, cusupocodi, tfdochulat)
									VALUES(".$seqDocumento.", 2, '".$arquivo['observacao'][$i]."', now(), ".$_SESSION['_cusupocodi_'].", now());

							";

                            //print_r($sqlHist);


							$resultHist = $db->query($sqlHist);
							
							if (PEAR::isError($resultHist)) {
                                $db->query("ROLLBACK");
                                
								ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlHist");
							}else{
                                $_SESSION['Mens']      = 1;
                                $_SESSION['Tipo']      = 1;
                                $_SESSION['Mensagem'] .= "Alteração realizada com sucesso.";
                            }

						}

					}elseif($arquivo['situacao'][$i] == 'excluido'){

						// Exclui os documentos que foram marcados para isso
						$sqlDelete = 'Delete FROM SFPC.tbfornecedordocumento FD ';
						$sqlDelete .= ' where FD.cfdocusequ = '.$arquivo['codigo'][$i];
						$resultDel= $db->query($sqlDelete);
                            
                        // Exclui o histórico de documento do doc que foi escluido
						$sqlDelete = 'Delete FROM SFPC.tbfornecedordocumentohistorico FDH ';
						$sqlDelete .= ' where FDH.cfdocusequ = '.$arquivo['codigo'][$i];
						$resultDel= $db->query($sqlDelete);
                            


						if (PEAR::isError($resultDel)) {
							$db->query("ROLLBACK");
							ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlDelete");
						}else{
                            $_SESSION['Mens']      = 1;
                            $_SESSION['Tipo']      = 1;
                            $_SESSION['Mensagem'] .= "Documento excluído com sucesso.";
                        }
						
					}elseif($arquivo['situacao'][$i] == 'existente'){

						if(($_POST['situacaoDoc'.$i] != $arquivo['situacaoDoc'][$i]) ){

							//insere a fase do documento
							$sqlHist = "INSERT INTO sfpc.tbfornecedordocumentohistorico
									(cfdocusequ, cfdocscodi, efdochobse, tfdochcada, cusupocodi, tfdochulat)
									VALUES(".$arquivo['codigo'][$i].", '".$_POST['situacaoDoc'.$i]."', '".$_POST['obsDocumento'.$i]."', now(), ".$_SESSION['_cusupocodi_'].", now());

							";

							$resultHist = $db->query($sqlHist);
							
							if (PEAR::isError($resultHist)) {
								$db->query("ROLLBACK");
								ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlHist");
							}

						}


					}
                }
                $resultFinal = $db->query("COMMIT");

                if ( !(PEAR::isError($resultFinal)) ) {
                    header('Location: AtualizaDocumentosFornecedor.php?Sequencial='.$Sequencial.'&atualizacao=1');
                }

			}

}elseif ($Botao == 'Download'){

    //$docDown = getTramitacaoLicitacaoAnexos($licDown, $protDown, $seqDown);
    $docDown = $_SESSION['Arquivos_Upload'];

    $qtdup = count($docDown['conteudo']);
    for ($arqC = 0; $arqC < $qtdup; ++ $arqC) {

        if($download == $arqC){

            $arrNome = explode('.',$docDown['nome'][$arqC]);
            $extensao = $arrNome[1];
        
            $mimetype = 'application/octet-stream';
            
            header( 'Content-type: '.$mimetype ); 
            header( 'Content-Disposition: attachment; filename='.$docDown['nome'][$arqC] );   
            header( 'Content-Transfer-Encoding: binary' );
            header( 'Pragma: no-cache');
            
            echo pg_unescape_bytea($docDown['conteudo'][$arqC]);
        
            die();

        }

    }


		
		
	
}elseif ($Botao == "Limpar") {
    $Mens = 0;
    $_SESSION['Mens']      = 0;
    $_SESSION['Mensagem'] = '';
    $Botao = "";
    $_SESSION['tipoDoc'] = 0;
    $_SESSION['obsDocumento'] = "";

} elseif  ($Botao== 'IncluirDocumento') {

        $Mens = 0;
        $_SESSION['Mens']      = 0;
        $_SESSION['Mensagem'] = '';

					
		if ($_POST['tipoDoc'] == '0' ) {
			if ($_SESSION['Mens'] == 1) {
				$_SESSION['Mensagem'] .= ", ";
			}
			$_SESSION['Mens']      = 1;
			$_SESSION['Tipo']      = 2;
			$_SESSION['Mensagem'] .= "Tipo do documento deve ser preenchido";
		}else{

			$db = Conexao();
			$parametrosGerais = dadosParametrosGerais($db);
		
			$tamanhoArquivo = $parametrosGerais[4];
			$tamanhoNomeArquivo = $parametrosGerais[5];
			$extensoesArquivo = $parametrosGerais[6];
			

			//$Critica = 0;

			if ($_FILES['Documentacao']['tmp_name']) {
				$_FILES['Documentacao']['name'] = tratarNomeArquivo($_FILES['Documentacao']['name']);
		
				$extensoesArquivo .= ', .zip, .xlsm, .xls, .ods, .pdf';
		
		
				$extensoes = explode(',', strtolower2($extensoesArquivo));
				array_push($extensoes, '.zip', '.xlsm');
		
				$noExtensoes = count($extensoes);
				$isExtensaoValida = false;
				for ($itr = 0; $itr < $noExtensoes; ++ $itr) {
					if (preg_match('/\\' . trim($extensoes[$itr]) . '$/', strtolower2($_FILES['Documentacao']['name']))) {
						$isExtensaoValida = true;
					}
				}
				if (! $isExtensaoValida) {
					$_SESSION['Mens'] = 1;
					$_SESSION['Tipo'] = 2;
					$_SESSION['Mensagem'] .= 'Selecione somente documento com a(s) extensão(ões) ' . $extensoesArquivo;
				}
				if (strlen($_FILES['Documentacao']['name']) > $tamanhoNomeArquivo) {
					if ($_SESSION['Mens'] == 1) {
						$_SESSION['Mensagem'].= ', ';
					}
					$_SESSION['Mens'] = 1;
					$_SESSION['Tipo'] = 2;
					$_SESSION['Mensagem'] .= 'Nome do Arquivo com até ' . $tamanhoNomeArquivo . ' Caracateres ( atualmente com ' . strlen($_FILES['Documentacao']['name']) . ' )';
				}
				$Tamanho = 5120*1000;
				if (($_FILES['Documentacao']['size'] > $Tamanho) || ($_FILES['Documentacao']['size'] == 0)) {
					if ($_SESSION['Mens']  == 1) {
						$_SESSION['Mensagem'] .= ', ';
					}
					$Kbytes = $Tamanho;
					$Kbytes = (int) $Kbytes;
					$_SESSION['Mens']= 1;
					$_SESSION['Tipo'] = 2;
					$_SESSION['Mensagem'] .= "Este arquivo ou é muito grande ou está vazio. Tamanho Máximo: 5 MB";
				}
				if ($_SESSION['Mens'] == '') {
					if (! ($_SESSION['Arquivos_Upload']['conteudo'][] = file_get_contents($_FILES['Documentacao']['tmp_name']))) {
						$_SESSION['Mens']= 1;
						$_SESSION['Tipo'] = 2;
						$_SESSION['Mensagem'] = 'Caminho da Documentação Inválido';
					} else {
						$_SESSION['Arquivos_Upload']['nome'][] = $_FILES['Documentacao']['name'];
						$_SESSION['Arquivos_Upload']['situacao'][] = 'novo'; // situacao pode ser: novo, existente, cancelado e excluido
						$_SESSION['Arquivos_Upload']['codigo'][] = ''; // como é um arquivo novo, ainda nao possui código
						$_SESSION['Arquivos_Upload']['tipoCod'][] = $_POST['tipoDoc']; 
						$_SESSION['Arquivos_Upload']['tipoDocumentoDesc'][] = $_POST['tipoDocDesc']; 
						$_SESSION['Arquivos_Upload']['observacao'][] = strtoupper2($_POST['obsDocumento']); 
						$_SESSION['Arquivos_Upload']['dataHora'][] = date('d/m/Y H:i'); 

						$_SESSION['tipoDoc'] = 0;
						$_SESSION['obsDocumento'] = "";
					}
				}

			} else {
				$_SESSION['Mens'] = 1;
				$_SESSION['Tipo'] = 2;
				$_SESSION['Mensagem'] = 'Falta anexar o documento';
			}
		}
} elseif ($Botao == 'RetirarDocumento') {
        $Mens = 0;
        $_SESSION['Mens']      = 0;
        $_SESSION['Mensagem'] = '';

		if ($DDocumento){
            
			foreach ($DDocumento as $valor) {
	
				if ($_SESSION['Arquivos_Upload']['situacao'][$valor] == 'novo') {
					$_SESSION['Arquivos_Upload']['situacao'][$valor] = 'cancelado'; // cancelado- quando o usuário incluiu um arquivo novo mas desistiu
				} 
                // elseif ($_SESSION['Arquivos_Upload']['situacao'][$valor] == 'existente') {
				// 	$_SESSION['Arquivos_Upload']['situacao'][$valor] = 'existente'; // excluído- quando o arquivo já existe e deve ser excluido no sistema
				// }
	
			}

        }else{
			$_SESSION['Mens'] = 1;
			$_SESSION['Tipo'] = 2;
			$_SESSION['Mensagem'] = 'Selecione um anexo para ser retirado';
		}
}
	



function configurarBlocoDocumentos($tpl) {

    $tpl->obsDocumento               = $_SESSION['obsDocumento'];
    $tpl->tipoDocDesc               = $_SESSION['tipoDocDesc'];

    // Tipos de documento
    $htmlTipoDoc = '';

    $db = Conexao();																																			
    $sql = "SELECT CFDOCTCODI, EFDOCTDESC, ffdoctobri FROM 
            SFPC.TBFORNECEDORDOCUMENTOTIPO
            WHERE FFDOCTSITU = 'A' ORDER BY afdoctorde, EFDOCTDESC";
    $res = $db->query($sql);

    if (PEAR::isError($res)) {
        ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
    } else {
        
        while ($tipoDoc = $res->fetchRow()) {
            
            $docObrigatorio = '';
            if($tipoDoc[2] == 'S'){
                $docObrigatorio = ' (Obrigatório)';
            }

            if($tipoDoc[0] == $_SESSION['tipoDoc'] ){
                $htmlTipoDoc .= '<option value="'.$tipoDoc[0].'" selected>'.$tipoDoc[1].$docObrigatorio.'</option>';
            }else{
                $htmlTipoDoc .= '<option value="'.$tipoDoc[0].'">'.$tipoDoc[1].$docObrigatorio.'</option>';
            }	

        }
    }

    //Documentos Anexados até o momento
    $htmlDoc = '';
    $htmlBotaoRetirarDoc = '';
    ////($_SESSION['Arquivos_Upload']);
    //die();
    $qtd_anexo = 0;

        for ($j = 0; $j < count($_SESSION['Arquivos_Upload']['conteudo']) ; ++ $j) {
            if ($_SESSION['Arquivos_Upload']['situacao'][$j] == 'novo' ||  $_SESSION['Arquivos_Upload']['situacao'][$j] == 'existente') {
                $qtd_anexo++;
            }
        }

    if( $qtd_anexo > 0){
        $htmlDoc .= '<table border="1" cellpadding="3" cellspacing="0" bgcolor="#bfdaf2" bordercolor="#75ADE6" summary="" class="textonormal" width="100%">
        <tr>
            <td bgcolor="#75ADE6" class="textoabasoff" colspan="7" align="center">DOCUMENTOS ANEXADOS</td>
        </tr>
        <tr>
            <td bgcolor="#bfdaf2" align="center"><b>  </b></td>
            <td bgcolor="#bfdaf2" align="center"><b> Tipo do documento</b></td>
            <td bgcolor="#bfdaf2" align="center"><b> Nome</b></td>
            <td bgcolor="#bfdaf2" align="center"><b> Responsável anexação</b></td>
            <td bgcolor="#bfdaf2" align="center"><b> Data/Hora Anexação</b></td>
            <td bgcolor="#bfdaf2" align="center"><b> Situação</b></td>
            <td bgcolor="#bfdaf2" align="center"><b> Observação</b></td>
        </tr> ';
        


            
        $DTotal = count($_SESSION['Arquivos_Upload']['conteudo']);
        for ($Dcont = 0; $Dcont < $DTotal; ++ $Dcont) {
            if ($_SESSION['Arquivos_Upload']['situacao'][$Dcont] == 'novo') {
                $htmlDoc .= '<tr>
                    <td align="center" width="5%" bgcolor="#ffffff"><input type="checkbox" name="DDocumento['.$Dcont.']" value="'.$Dcont.'" ></td>
                    <td class="textonormal" bgcolor="#ffffff">
                        '.$_SESSION['Arquivos_Upload']['tipoDocumentoDesc'][$Dcont].'
                    </td>
                    <td class="textonormal" bgcolor="#ffffff">
                        <a href="javascript: baixarArquivo('.$Dcont.');">'.$_SESSION['Arquivos_Upload']['nome'][$Dcont].'</a>
                    </td>
                    <td class="textonormal" bgcolor="#ffffff" align="center">';

                    if($_SESSION['Arquivos_Upload']['situacao'][$Dcont]=='existente'){
                        $htmlDoc .= $_SESSION['Arquivos_Upload']['usuarioAnex'][$Dcont]; 
                    }else{
                        $htmlDoc .= $CPFCNPJ;
                    }

                $htmlDoc .=  '</td>
                    <td class="textonormal" bgcolor="#ffffff">
                        '.$_SESSION['Arquivos_Upload']['dataHora'][$Dcont].'
                    </td>
                    <td class="textonormal" bgcolor="#ffffff">
                    '.$_SESSION['Arquivos_Upload']['situacaoDesc'][$Dcont].'
                    </td>
                    <td class="textonormal" bgcolor="#ffffff">
                        '.$_SESSION['Arquivos_Upload']['observacao'][$Dcont].'
                    </td>
                    </tr>';
             

            }
            if($_SESSION['Arquivos_Upload']['situacao'][$Dcont] == 'existente'){
                $htmlDoc .= '<tr>
                    <td align="center" width="5%" bgcolor="#ffffff"><input type="hidden" name="DDocumento['.$Dcont.']" value="'.$Dcont.'" ></td>
                    <td class="textonormal" bgcolor="#ffffff">
                        '.$_SESSION['Arquivos_Upload']['tipoDocumentoDesc'][$Dcont].'
                    </td>
                    <td class="textonormal" bgcolor="#ffffff">
                        <a href="javascript: baixarArquivo('.$Dcont.');">'.$_SESSION['Arquivos_Upload']['nome'][$Dcont].'</a>
                    </td>
                    <td class="textonormal" bgcolor="#ffffff" align="center">';

                    if($_SESSION['Arquivos_Upload']['situacao'][$Dcont]=='existente'){
                        $htmlDoc .= $_SESSION['Arquivos_Upload']['usuarioAnex'][$Dcont]; 
                    }else{
                        $htmlDoc .= $CPFCNPJ;
                    }

                $htmlDoc .=  '</td>
                    <td class="textonormal" bgcolor="#ffffff">
                        '.$_SESSION['Arquivos_Upload']['dataHora'][$Dcont].'
                    </td>
                    <td class="textonormal" bgcolor="#ffffff">
                    '.$_SESSION['Arquivos_Upload']['situacaoDesc'][$Dcont].'
                    </td>
                    <td class="textonormal" bgcolor="#ffffff">
                        '.$_SESSION['Arquivos_Upload']['observacao'][$Dcont].'
                    </td>
                    </tr>';
            }
        }

        $htmlDoc .= '</table>';
        if($_FILES['Documentacao'] != null){
            $htmlBotaoRetirarDoc = '<div class="row-fluid">
                                 <div class="text-center">
                                    <input class="btn" type="button" value="Retirar Documento" onclick="javascript:enviarForm(\'RetirarDocumento\');">
                                </div>	
                            </div>';
        }
        
    }
    $tpl->htmlBotaoRetirarDoc   = $htmlBotaoRetirarDoc;
    $tpl->htmlDoc               = $htmlDoc;
    $tpl->htmlTipoDoc           = $htmlTipoDoc;

   // $tpl->block("bloco_dados");
}

//$tpl->block("BLOCO_TEXTO_DOCUMENTOS");

if($_SESSION['Mens']>0){
    $tpl->MENSAGEM = $_SESSION['Mensagem'];
    $tpl->TIPOALERT = ($_SESSION['Tipo']== 1) ? 'alert-info' : 'alert-error';
    $tpl->block('BLOCO_MENSAGEM');
}
configurarBlocoDocumentos($tpl);

$tpl->block("bloco_documentos");


$tpl->show();
$db->disconnect();
