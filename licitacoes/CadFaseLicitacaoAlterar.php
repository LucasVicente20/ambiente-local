<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadFaseLicitacaoAlterar.php
# Autor:    Rossana Lira
# Data:     02/05/03
# Objetivo: Programa de Alteração da Fase de Licitação
# OBS.:     Tabulação 2 espaços
#
#
# Manutenções
# ----------------------------------------
# Autor:    Heraldo Botelho
# Data:     18/09/2012
# Objetivo: Implementa a Fase da Licitação Com Licitação(ões) Associada
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include '../funcoes.php';
require_once 'funcoesLicitacoes.php';

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/licitacoes/CadFaseLicitacaoExcluir.php' );
AddMenuAcesso( '/licitacoes/CadFaseLicitacaoSelecionar.php' );
AddMenuAcesso( '/oracle/licitacoes/RotValidaBloqueio.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST"){
		$Botao                     = $_POST['Botao'];
		$Processo                  = $_POST['Processo'];
		$ProcessoAno               = $_POST['ProcessoAno'];
		$ComissaoCodigo            = $_POST['ComissaoCodigo'];
		$ComissaoDescricao         = $_POST['ComissaoDescricao'];
		$OrgaoLicitanteCodigo      = $_POST['OrgaoLicitanteCodigo'];
		$ModalidadeCodigo          = $_POST['ModalidadeCodigo'];
		$RegistroPreco             = $_POST['RegistroPreco'];
		$FaseCodigo                = $_POST['FaseCodigo'];
		$FaseDescricao             = trim($_POST['FaseDescricao']);
		$DataFase                  = $_POST['DataFase'];
		if( $DataFase != "" ){ $DataFase = FormataData($DataFase); }
		$FaseLicitacaoDetalhe      = strtoupper2(trim($_POST['FaseLicitacaoDetalhe']));
		$FaseLicitacaoUltAlteracao = $_POST['FaseLicitacaoUltAlteracao'];
		$ValorHomologado           = $_POST['ValorHomologado'];
		$ValorHomologadoAntes      = $_POST['ValorHomologadoAntes'];
		$FlagValorHomologado       = $_POST['FlagValorHomologado'];
		$TotalGeralEstimado        = $_POST['TotalGeralEstimado'];
		$BloqueiosDot              = $_POST['BloqueiosDot'];
		$critica             		= $_POST['critica'];
}else{
		$Processo                  = $_GET['Processo'];
		$ProcessoAno               = $_GET['ProcessoAno'];
		$ComissaoCodigo            = $_GET['ComissaoCodigo'];
		$OrgaoLicitanteCodigo      = $_GET['OrgaoLicitanteCodigo'];
		$ModalidadeCodigo          = $_GET['ModalidadeCodigo'];
		$RegistroPreco             = $_GET['RegistroPreco'];
		$FaseCodigo                = $_GET['FaseCodigo'];
		$AlteraValorHomologadoBlo  = $_GET['AlteraValorHomologadoBlo'];
}
$codigoUsuario = $_SESSION["_cperficodi_"];







# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

//--------------- bloco inserido por Heraldo----------------------
//------------------------------------------------------
//
// Verificar se licitacao possui  solicitacao
//
//------------------------------------------------------

if (!empty($ProcessoAno)) {

	$licitacao_possui_solicitacao= false;
	$db     = Conexao();
	$sql    = "SELECT COUNT(*) FROM SFPC.tbsolicitacaolicitacaoportal ";
	$sql   .= " WHERE CLICPOPROC = $Processo AND ALICPOANOP = $ProcessoAno ";
	$sql   .= "   AND CGREMPCODI = ".$_SESSION['_cgrempcodi_']." AND CCOMLICODI = $ComissaoCodigo ";
	$sql   .= "   AND CORGLICODI = $OrgaoLicitanteCodigo ";


	$result = $db->query($sql);
	if( PEAR::isError($result) ){
	    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
	}else{
		$Linha = $result->fetchRow();
	}
	if ($Linha[0] > 0 ) $licitacao_possui_solicitacao= true;

    if ( $licitacao_possui_solicitacao ) $existeSolicitacao="SIM"; else $existeSolicitacao="NAO";


//------------------------------------------------------
//
// fazer calculo de TotalGeralEstimado e ValorHomologado
//
//------------------------------------------------------
if ( $exiteSolicitacao=="SIM" ) {



  $TotalGeralEstimado=0;
  $ValorHomologado=0;

  // total geral estimado
  $sql    = "SELECT SUM( vitelpunit * aitelpqtso ) FROM SFPC.tbitemlicitacaoportal ";
  $sql   .= " WHERE CLICPOPROC = $Processo AND ALICPOANOP = $ProcessoAno ";
  $sql   .= "   AND CGREMPCODI = ".$_SESSION['_cgrempcodi_']." AND CCOMLICODI = $ComissaoCodigo ";
  $sql   .= "   AND CORGLICODI = $OrgaoLicitanteCodigo ";
  $sql   .= "   AND fitelplogr = 'S' ";
  $result = $db->query($sql);
  if( PEAR::isError($result) ){
      ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
  }else{
  	$Linha = $result->fetchRow();
  	$TotalGeralEstimado = $Linha[0];
  }

  // Valor Homologado
  $sql    = "SELECT SUM( vitelpvlog * aitelpqtso ) FROM SFPC.tbitemlicitacaoportal ";
  $sql   .= " WHERE CLICPOPROC = $Processo AND ALICPOANOP = $ProcessoAno ";
  $sql   .= "   AND CGREMPCODI = ".$_SESSION['_cgrempcodi_']." AND CCOMLICODI = $ComissaoCodigo ";
  $sql   .= "   AND CORGLICODI = $OrgaoLicitanteCodigo ";
  $sql   .= "   AND fitelplogr='S' ";
  $result = $db->query($sql);
  if( PEAR::isError($result) ){
      ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
  }else{
  	$Linha = $result->fetchRow();
  	$ValorHomologado = $Linha[0];
  }

  $TotalGeralEstimado =  sprintf("%01.2f",str_replace(".",",",$TotalGeralEstimado));
  $TotalGeralEstimado =  converte_valor($TotalGeralEstimado);

  $ValorHomologado = sprintf("%01.2f",str_replace(".",",",$ValorHomologado));
  $ValorHomologado =  converte_valor($ValorHomologado);

  // Verifica se existe presolicitacao importada pelo sofin
  $sql =  " select a.tpresoimpo as dataimportacao ";
  $sql .= " from ";
  $sql .= " sfpc.tbpresolicitacaoempenho a ";
  $sql .= " where ";
  $sql .= " a.clicpoproc      =  $Processo ";
  $sql .= " and a.alicpoanop  =  $ProcessoAno ";
  $sql .= " and a.cgrempcodi  =  ".$_SESSION['_cgrempcodi_'];
  $sql .= " and a.ccomlicodi  =  $ComissaoCodigo ";
  $sql .= " and a.corglicodi  =  $OrgaoLicitanteCodigo ";



  $result = executarSQL($db, $sql ) ;
  $row = $result->fetchRow(DB_FETCHMODE_OBJECT);
  $dataimportacao =  $row->dataimportacao;


  //-----------------------------------------
  // Verifica se houve resultado da licitacao
  //-----------------------------------------
  $sql = " select l.FLICPORESU as resultado  from   sfpc.tblicitacaoportal l  ";
  $sql .= " where  ";
  $sql .= " l.clicpoproc=$Processo ";
  $sql .= " and l.alicpoanop=$ProcessoAno ";
  $sql .= " and l.cgrempcodi=".$_SESSION['_cgrempcodi_'];
  $sql .= " and l.ccomlicodi=$ComissaoCodigo ";
  $sql .= " and l.corglicodi=$OrgaoLicitanteCodigo ";
  $result = executarSQL($db, $sql);
  $row = $result->fetchRow(DB_FETCHMODE_OBJECT);
  $resultado = $row->resultado;
}

  $db->disconnect();


}







# Se excl
if  (( $Botao == "Excluir" ) &&  ( $FaseCodigo=="11" || $FaseCodigo=="12" || $FaseCodigo=="17" ) && $codigoUsuario != 6 && $codigoUsuario != 2) {
$Mens      = 1;
		$Tipo      = 2;
		$Mensagem  = "não pode excluir as fases: revogação, anulação ou cancelamento";
}

if  ( ( $Botao == "Excluir" ) &&  ( $FaseCodigo=="13" ) and !empty($dataimportacao)  ) {
		$Mens      = 1;
		$Tipo      = 2;
		$Mensagem  = "não pode excluir fase homologação, já foi importada pelo SOFIN ";
}


# Redireciona para a página de excluir #
elseif ( $Botao == "Excluir" ){
		$Url = "CadFaseLicitacaoExcluir.php?Processo=$Processo&ProcessoAno=$ProcessoAno&ComissaoCodigo=$ComissaoCodigo&OrgaoLicitanteCodigo=$OrgaoLicitanteCodigo&ModalidadeCodigo=$ModalidadeCodigo&RegistroPreco=$RegistroPreco&FaseCodigo=$FaseCodigo";
		if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
	  header("location: $Url");
	  exit();
}elseif( $Botao == "Voltar" ){
	  header("location: CadFaseLicitacaoSelecionar.php");
	  exit();
}



 if ( $licitacao_possui_solicitacao and $critica==1    ) {
     $Botao = "Alterar";
 }


if( $Botao == "AlterarHomologacao"   ) {


		$Mens                 = 0;
		$Mensagem             = "Informe: ";
		if( $ValorHomologado == "" ){
				if( $Mens == 1 ){ $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.FaseLicitacaoAlterar.ValorHomologado.focus();\" class=\"titulo2\">Valor Homologado</a>";
		}else{
				//if( ! SoNumVirg($ValorHomologado) ){
				//		if ( $Mens == 1 ) { $Mensagem .= ", "; }
				//		$Mens      = 1;
				//		$Tipo      = 2;
				//		$Mensagem .= "<a href=\"javascript:document.FaseLicitacaoAlterar.ValorHomologado.focus();\" class=\"titulo2\">Valor Homologado Válido</a>";
				//}else{
						if( $FlagValorHomologado == "N" and $ValorHomologadoAntes != $ValorHomologado and $FaseCodigo == 13 ){
								$Mens      = 1;
								$Tipo      = 2;
								$Virgula   = 2;
								$Mensagem  = "Valor Homologado não pode ser Alterado, pois as informações do(s) bloqueio(s) já foram ajustadas no SOFIN";
						}else{
								$ValorHomologadoAtual = sprintf("%01.2f",str_replace(",",".",$ValorHomologado));
								$ValorHomologadoAtual = str_replace(".",",",$ValorHomologadoAtual);
								if( $ValorHomologadoAntes != $ValorHomologadoAtual ){
										# Pega o Valor estimado desse Processo Licitatório #
										$db     = Conexao();
										$sql    = "SELECT VLICPOVALE FROM SFPC.TBLICITACAOPORTAL ";
										$sql   .= " WHERE CLICPOPROC = $Processo AND ALICPOANOP = $ProcessoAno ";
										$sql   .= "   AND CGREMPCODI = ".$_SESSION['_cgrempcodi_']." ";
										$sql   .= "   AND CCOMLICODI = $ComissaoCodigo AND CORGLICODI = $OrgaoLicitanteCodigo ";
										$result = $db->query($sql);
										if( PEAR::isError($result) ){
										    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
										}else{
												$Linha = $result->fetchRow();
												if( $Linha[0] == "" ){
														$ValorEstimado = "0,00";
												}else{
														$ValorEstimado = str_replace(".",",",$Linha[0]);
												}
										}
										if( $ValorHomologado == "" or $ValorHomologado == 0 ){
												$ValorHomologadoAtual = "0,00";
										}else{
												$ValorHomologadoAtual = sprintf("%01.2f",str_replace(",",".",$ValorHomologado));
												$ValorHomologadoAtual = str_replace(".",",",$ValorHomologadoAtual);
										}

										if( $ValorHomologadoAtual != $ValorEstimado ){
												$Homologacao = "S";
										}else{
												$Botao = "Alterar";
										}
								}else{
										$Botao = "Alterar";
								}
						}
				}
		}
//}







if( $Botao == "Alterar" ) {

		# Critica dos Campos #
		$Mens     = 0;
    $Mensagem = "Informe: ";
		if( $DataFase == "" ){
				if( $Mens == 1 ){ $Mensagem.=", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript: document.FaseLicitacaoAlterar.DataFase.focus();\" class=\"titulo2\">Data da Fase</a>";
		}else{
				$MensErro = ValidaData($DataFase);
				if( $MensErro != "" ){
						if( $Mens == 1 ){ $Mensagem.=", "; }
						$Mens      = 1;
				  	$Tipo      = 2;
						$Mensagem .= "<a href=\"javascript:document.FaseLicitacaoAlterar.DataFase.focus();\" class=\"titulo2\">Data da Fase Válida</a>";
				}
				else {
				//Pegar mensagem de erro na comparacao da data da fase como : Dt. Ultima Fase , Dt. Ultima Solicitacao;
                 // $ret =  verificaUltimasDatas($DataFase,$Processo,$ProcessoAno,$_SESSION['_cgrempcodi_'],$ComissaoCodigo,$OrgaoLicitanteCodigo,$db,1);
                 // if ( !empty($ret) )   {
                 //     $Mensagem.=  $ret;
  				//	  $Mens      = 1;
				 //     $Tipo      = 2;
                 // }

				}

		}

         //--Bloco adiconado por Heraldo-------------------
		if ( $licitacao_possui_solicitacao ) {


				if( $DataFase == "" ){
						if( $Mens == 1 ){ $Mensagem.=", "; }
						$Mens      = 1;
						$Tipo      = 2;
						$Mensagem .= "<a href=\"javascript: document.FaseLicitacaoDetalhe.DataFase.focus();\" class=\"titulo2\">Data da Fase</a>";
				}else{
						$MensErro = ValidaData($DataFase);
						if( $MensErro != "" ){
								if( $Mens == 1 ){ $Mensagem.=", "; }
								$Mens      = 1;
						  	    $Tipo      = 2;
								$Mensagem .= "<a href=\"javascript:document.FaseLicitacaoDetalhe.DataFase.focus();\" class=\"titulo2\">Data da Fase Válida</a>";
						}

						else {
    						$dataAux=substr($DataFase,6,4)."-".substr($DataFase,3,2)."-".substr($DataFase,0,2);

						    if ( $dataAux > date("Y-m-d") ) {
								if( $Mens == 1 ){ $Mensagem.=", "; }
								$Mens      = 1;
						  	    $Tipo      = 2;
								$Mensagem .= "<a href=\"javascript:document.FaseLicitacaoAlterar.DataFase.focus();\" class=\"titulo2\">Data da Fase deve ser menor ou igual a hoje</a>";
						    }




						}

				}


		}
		//--- fim Bloco adiconado por Heraldo------------

        $FaseLicitacaoDetalhe=trim($FaseLicitacaoDetalhe);
	    if ( empty($FaseLicitacaoDetalhe) &&  ( $FaseCodigo=="11" || $FaseCodigo=="12" || $FaseCodigo=="17"   )   ) {
		   	$Mens      = 1;
		  	$Tipo      = 2;
		   	$Mensagem .= "<a href=\"javascript: document.FaseLicitacaoAlterar.FaseLicitacaoDetalhe.focus();\" class=\"titulo2\">Esta fase exige que seja informado o detalhe</a>";
	    }




		if( strlen($FaseLicitacaoDetalhe) > 200 ){
				if ( $Mens == 1 ) { $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "Detalhamento da Fase com até 200 Caracteres ( atualmente com ". strlen($FaseLicitacaoDetalhe) ." )";
		}




		if( $FaseCodigo == 13 ){
					 	//if( $ValorHomologado > $TotalGeralEstimado){
						//		if( $Mens == 1 ){ $Mensagem .= ", "; }
						//		$Mens      = 1;
						//		$Tipo      = 2;
						//		$Mensagem = "<a href=\"javascript:document.FaseLicitacao.ValorHomologadoo.focus();\" class=\"titulo2\">Valor Homologado não pode maior que total estimado</a>";
					 	//}


					 	if( $ValorHomologado == "0,00" ){
								if( $Mens == 1 ){ $Mensagem .= ", "; }
								$Mens      = 1;
								$Tipo      = 2;
								$Mensagem = "<a href=\"javascript:document.FaseLicitacao.ValorHomologadoo.focus();\" class=\"titulo2\">Valor Homologado não pode ser zero</a>";
					 	}

					 	if( $TotalGeralEstimado == "0,00" ){
								if( $Mens == 1 ){ $Mensagem .= ", "; }
								$Mens      = 1;
								$Tipo      = 2;
								$Mensagem = "<a href=\"javascript:document.FaseLicitacao.TotalGeralEstimado.focus();\" class=\"titulo2\">Total Geral Estimado não pode ser zero</a>";
					 	}


//					    if ( $resultado<>"S" )	{
//					   		if( $Mens == 1 ){ $Mensagem.=", "; }
//    						 $Mens      = 1;
//   				        	 $Tipo      = 2;
//    						 $Mensagem .= "<a href=\"javascript: document.FaseLicitacao.ProcessoAnoComissaoOrgao.focus();\" class=\"titulo2\">Licitação sem resultado informado</a>";
//					    }


						if( $ValorHomologado == "" ){
								if( $Mens == 1 ){ $Mensagem .= ", "; }
								$Mens      = 1;
								$Tipo      = 2;
								$Mensagem .= "<a href=\"javascript:document.FaseLicitacao.ValorHomologado.focus();\" class=\"titulo2\">Valor Homologado</a>";
						}else{
								//if( ! validaMonetario($ValorHomologado) ){
								//		if ( $Mens == 1 ) { $Mensagem .= ", "; }
								//		$Mens        = 1;
								//		$Tipo        = 2;
								//		$Mensagem   .= "<a href=\"javascript:document.FaseLicitacao.ValorHomologado.focus();\" class=\"titulo2\">Valor Homologado Válido</a>";
								//		$Homologacao = "";
								//}
						}
					 	if( $TotalGeralEstimado == "" ){
								if( $Mens == 1 ){ $Mensagem .= ", "; }
								$Mens      = 1;
								$Tipo      = 2;
								$Mensagem .= "<a href=\"javascript:document.FaseLicitacao.TotalGeralEstimado.focus();\" class=\"titulo2\">Total Geral Estimado</a>";
						}else{
								//if( ! validaMonetario($TotalGeralEstimado) ){
								//		if ( $Mens == 1 ) { $Mensagem .= ", "; }
								//		$Mens        = 1;
								//		$Tipo        = 2;
								//		$Mensagem   .= "<a href=\"javascript:document.FaseLicitacao.TotalGeralEstimado.focus();\" class=\"titulo2\">Total Geral Estimado Válido</a>";
						        //}
						}
		  		}




		if( $Mens == 0 ) {




				$Data   = date("Y-m-d H:i:s");
				# Atualiza Fase de Licitação #
				$db     = Conexao();
				$db->query("BEGIN TRANSACTION");
				$sql    = "UPDATE SFPC.TBFASELICITACAO ";
				$sql   .= "   SET EFASELDETA = '$FaseLicitacaoDetalhe', TFASELDATA = '".DataInvertida($DataFase)."', ";
				$sql   .= "       CUSUPOCODI = ".$_SESSION['_cusupocodi_'].", TFASELULAT = '$Data' ";
				$sql   .= " WHERE CLICPOPROC = $Processo AND ALICPOANOP = $ProcessoAno ";
				$sql   .= "   AND CCOMLICODI = $ComissaoCodigo AND CGREMPCODI = ".$_SESSION['_cgrempcodi_']." ";
				$sql   .= "   AND CORGLICODI = $OrgaoLicitanteCodigo AND CFASESCODI = $FaseCodigo";
				$result = $db->query($sql);
				if( PEAR::isError($result) ){
						$db->query("ROLLBACK");
				    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
				}else{
						if( $FaseCodigo == 13 ){
								if( $FlagValorHomologado == "N" and $ValorHomologadoAntes != $ValorHomologado and $FaseCodigo == 13 ){
										$Mens      = 1;
										$Tipo      = 2;
										$Virgula   = 2;
										$Mensagem  = "Valor Homologado não pode ser Alterado, pois as informações do(s) bloqueio(s) já foram ajustadas no SOFIN";
								}else{


								        $ValorHomologadoAux    = str_replace(".","",$ValorHomologado);
								        $TotalGeralEstimadoAux = str_replace(".","",$TotalGeralEstimado);
								        $ValorHomologadoAux    = str_replace(",",".",$ValorHomologadoAux);
							          	$TotalGeralEstimadoAux = str_replace(",",".",$TotalGeralEstimadoAux);




										$sql    = "UPDATE SFPC.TBLICITACAOPORTAL ";
										$sql   .= "   SET VLICPOVALH = $ValorHomologadoAux, VLICPOTGES = $TotalGeralEstimadoAux, ";
										$sql   .= "   		CUSUPOCODI = ".$_SESSION['_cusupocodi_'].", TLICPOULAT = '$Data' ";
										$sql   .= " WHERE CLICPOPROC = $Processo AND ALICPOANOP = $ProcessoAno ";
										$sql   .= "   AND CCOMLICODI = $ComissaoCodigo AND CGREMPCODI = ".$_SESSION['_cgrempcodi_']." ";
										$sql   .= "   AND CORGLICODI = $OrgaoLicitanteCodigo ";
										$result = $db->query($sql);
										if( PEAR::isError($result) ){
												$db->query("ROLLBACK");
										    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
										}
								}
						}
						$db->query("COMMIT");
						$db->query("END TRANSACTION");
						$db->disconnect();

		        # Envia mensagem para página selecionar #
		        $Mensagem = urlencode("Fase de Licitação Alterada com Sucesso");
		        $Url = "CadFaseLicitacaoSelecionar.php?Mensagem=$Mensagem&Mens=1&Tipo=1";
						if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
		        header("location: $Url");
		        exit;
				}
				$db->disconnect();
		}
}


if( $Botao == "" ){
		# Busca descrição da comissão #
		$db     = Conexao();
		$sql    = "SELECT A.ECOMLIDESC FROM SFPC.TBCOMISSAOLICITACAO A WHERE A.CCOMLICODI = $ComissaoCodigo";
		$result = $db->query($sql);
		if( PEAR::isError($result) ){
		    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}else{
				$Linha             = $result->fetchRow();
				$ComissaoDescricao = $Linha[0];
		}

		# Busca descrição da Fase #
		$sql    = "SELECT A.EFASESDESC FROM SFPC.TBFASES A WHERE A.CFASESCODI = $FaseCodigo";
		$result = $db->query($sql);
		if( PEAR::isError($result) ){
		    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}else{
				$Linha         = $result->fetchRow();
				$FaseDescricao = $Linha[0];
		}

		# Busca o detalhamento da Fase da Licitação #
		$sql    = "SELECT EFASELDETA, TFASELDATA, TFASELULAT FROM SFPC.TBFASELICITACAO ";
		$sql   .= " WHERE CLICPOPROC = $Processo AND ALICPOANOP = $ProcessoAno ";
		$sql   .= "   AND CCOMLICODI = $ComissaoCodigo AND CGREMPCODI = ".$_SESSION['_cgrempcodi_'];
		$sql   .= "   AND CORGLICODI = $OrgaoLicitanteCodigo AND CFASESCODI = $FaseCodigo";
		$result = $db->query($sql);
		if( PEAR::isError($result) ){
		    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}else{
				$Linha                     = $result->fetchRow();
				$FaseLicitacaoDetalhe      = trim($Linha[0]);
				$NCaracteres               = strlen($Linha[0]);
				$DataFase                  = DataBarra($Linha[1]);
				$FaseLicitacaoUltAlteracao = DataBarra($Linha[2])." ".substr($Linha[2],11,8);
		}

		if( $FaseCodigo == 13 ){
				# Busca o Valor Homologado do Processo #
				$sql    = "SELECT VLICPOVALH, VLICPOTGES FROM SFPC.TBLICITACAOPORTAL ";
				$sql   .= " WHERE CLICPOPROC = $Processo AND ALICPOANOP = $ProcessoAno";
				$sql   .= "   AND CCOMLICODI = $ComissaoCodigo AND CGREMPCODI = ".$_SESSION['_cgrempcodi_']."";
				$sql   .= "   AND CORGLICODI = $OrgaoLicitanteCodigo ";


				//exit;

				$result = $db->query($sql);
				if( PEAR::isError($result) ){
				    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
				}else{
						$Linha           = $result->fetchRow();
						if( $Linha[0] == "" ){


								$ValorHomologado = "0,00";
						}else{
								$ValorHomologado = str_replace(".",",",$Linha[0]);
						}
						if( $Linha[1] == "" ){
								$TotalGeralEstimado = "0,00";
						}else{
								$TotalGeralEstimado = str_replace(".",",",$Linha[1]);
						}
						$ValorHomologadoAntes =	$ValorHomologado;
				}

				# Busca os Dados dos do Bloqueio #
				$sql    = "SELECT TUNIDOEXER, CUNIDOORGA, CUNIDOCODI, ALICBLSEQU ";
				$sql   .= "  FROM SFPC.TBLICITACAOBLOQUEIOORCAMENT";
				$sql   .= " WHERE CLICPOPROC = $Processo AND ALICPOANOP = $ProcessoAno ";
				$sql   .= "   AND CCOMLICODI = $ComissaoCodigo ";
				$sql   .= "   AND CGREMPCODI = ".$_SESSION['_cgrempcodi_']."";
				$sql   .= " ORDER BY ALICBLSEQU";
				$result = $db->query($sql);
				if( PEAR::isError($result) ){
				    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
				}else{
		    		$Rows = $result->numRows();
		    		for( $i=0; $i < $Rows;$i++ ){
								$Linha             = $result->fetchRow();
								$ExercicioBloq[$i] = $Linha[0];
								$ExercicioDot      = $ExercicioDot."_".$ExercicioBloq[$i];
								$Orgao[$i]         = $Linha[1];
								$OrgaoDot          = $OrgaoDot."_".$Orgao[$i];
								$Unidade[$i]       = $Linha[2];
								$UnidadeDot        = $UnidadeDot."_".$Unidade[$i];
								$Bloqueios[$i]     = $Linha[3];
								$BloqueiosDot      = $BloqueiosDot."_".$Bloqueios[$i];
						}
				}
				$db->disconnect();
				if( $BloqueiosDot != "" ){
						if( $AlteraValorHomologadoBlo == "" ){
								# Redireciona para a RotValidaBloqueio para Pegar o número de Bloqueio #
								$Url = "licitacoes/RotValidaBloqueio.php?NomePrograma=".urlencode("CadFaseLicitacaoAlterar.php")."&BloqueiosDot=$BloqueiosDot&ExercicioDot=$ExercicioDot&OrgaoLicitanteCodigo=$OrgaoLicitanteCodigo&Processo=$Processo&ProcessoAno=$ProcessoAno&ComissaoCodigo=$ComissaoCodigo&OrgaoDot=$OrgaoDot&UnidadeDot=$UnidadeDot&FaseCodigo=$FaseCodigo";
								if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
								Redireciona("$Url");
								exit;
						}else{
								$AlteraValorHomologado = explode("_",$AlteraValorHomologadoBlo);
								for( $j=1; $j < count($AlteraValorHomologado);$j++ ){
										if( $AlteraValorHomologado[$i] == "N" ){
												$FlagValorHomologado = "N";
										}
								}
						}
				}else{
						# Para registro de preço e modalidade diferente de leilão, exigir número de bloqueio #
						/*
						if( $RegistroPreco != "S" and $ModalidadeCodigo != 4){
								$Mens     = 1;
								$Tipo     = 2;
								$Mensagem = "Não foi informado nenhum Número de Bloqueio para este Processo Licitatório";
						}
						*/
				}
		}
}
?>
<html>
<?php
# Carrega o layout padrão #
layout();
?>
<script language="javascript" src="../janela.js" type="text/javascript"></script>
<script language="javascript" type="">
<!--
function enviar(valor){
	document.FaseLicitacaoAlterar.Botao.value=valor;
	document.FaseLicitacaoAlterar.submit();
}
function ncaracteres(valor){
		document.FaseLicitacaoAlterar.NCaracteres.value = '' +  document.FaseLicitacaoAlterar.FaseLicitacaoDetalhe.value.length;
		if( navigator.appName == 'Netscape' && valor ) {  //Netscape Only
			document.FaseLicitacaoAlterar.NCaracteres.focus();
		}
}
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="CadFaseLicitacaoAlterar.php" method="post" name="FaseLicitacaoAlterar">
<br><br><br><br><br>
<table cellpadding="3" border="0">
	<!-- Caminho -->
	<tr>
		<td width="150"><img border="0" src="../midia/linha.gif"></td>
		<td align="left" class="textonormal" colspan="2">
			<font class="titulo2">|</font>
			<a href="../index.php"><font color="#000000">Página Principal</font></a> > Licitações > Fase Licitação > Manter
		</td>
	</tr>
	<!-- Fim do Caminho-->

	<!-- Erro -->
	<?php if ( $Mens == 1 ) {?>
  <tr>
  	<td width="150"></td>
		<td align="left" colspan="2"><?php ExibeMens($Mensagem,$Tipo,$Virgula); ?></td>
	</tr>
	<?php } ?>
	<!-- Fim do Erro -->

	<!-- Corpo -->
	<tr>
		<td width="150"></td>
		<td class="textonormal">
		<table width="100%" border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal" bgcolor="#FFFFFF">
        <tr>
          <td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">
	           MANTER - FASE DE LICITAÇÃO

          </td>
        </tr>
        <tr>
          <td class="textonormal">
          	<p align="justify">
	            Para atualizar a Fase de Licitação, preencha os dados abaixo e clique no botão "Alterar". Para apagar a Fase de Licitação clique no botão "Excluir".<br>
	  	    		<?php if( $FaseCodigo == 13 ){ ?>
	  	    		O Total Geral Estimado (itens que lograram êxito) é obtido através do somatório do produto do preço unitário dos itens que lograram êxito pelo seus respectivos quantitativos.
	  	    		<?php } ?>
		        </p>
          </td>
        </tr>
        <tr>
          <td>
            <table border="0">
              <tr>
	              <td class="textonormal" bgcolor="#DCEDF7" height="20" width="30%">Comissão </td>
	              <td class="textonormal"><?php echo $ComissaoDescricao; ?></td>
	            </tr>
 							<tr>
	              <td class="textonormal" bgcolor="#DCEDF7" height="20">Processo </td>
	              <td class="textonormal"><?php echo $Processo; ?></td>
	            </tr>
	            <tr>
	              <td class="textonormal" bgcolor="#DCEDF7" height="20">Ano </td>
	              <td class="textonormal"><?php echo $ProcessoAno; ?></td>
	            </tr>
							<tr>
	              <td class="textonormal" bgcolor="#DCEDF7" height="20">Fase </td>
	              <td class="textonormal"><?php echo $FaseDescricao; ?></td>
	            </tr>
		          <tr>
	              <td class="textonormal" bgcolor="#DCEDF7">Data da Fase* </td>
	              <td class="textonormal">
									<?php $URL = "../calendario.php?Formulario=FaseLicitacaoAlterar&Campo=DataFase";?>
									<input type="text" name="DataFase" size="10" maxlength="10" value="<?php echo $DataFase ?>" class="textonormal">
									<a href="javascript:janela('<?php echo $URL ?>','Calendario',220,170,1,0)"><img src="../midia/calendario.gif" border="0" alt=""></a>
									<font class="textonormal">dd/mm/aaaa</font>
		      			</td>
	            </tr>
							<tr>
	              <td class="textonormal" bgcolor="#DCEDF7" height="20">Última Alteração </td>
	              <td class="textonormal"><?php echo $FaseLicitacaoUltAlteracao; ?></td>
	            </tr>
        	   	<tr>
	              <td class="textonormal" bgcolor="#DCEDF7">Detalhe </td>
	              <td class="textonormal">
	                <font class="textonormal">máximo de 200 caracteres</font>
									<input type="text" name="NCaracteres" disabled size="3" value="<?php echo $NCaracteres ?>" class="textonormal"><br>
									<textarea name="FaseLicitacaoDetalhe" cols="39" rows="5" OnKeyUp="javascript:ncaracteres(1)" OnBlur="javascript:ncaracteres(0)" OnSelect="javascript:ncaracteres(1)"><?php echo $FaseLicitacaoDetalhe; ?></textarea>
			          </td>
	            </tr>
	            <?php
	               if( $FaseCodigo == 13 and $licitacao_possui_solicitacao  ){ ?>
		           	<tr>
		              <td class="textonormal" bgcolor="#DCEDF7">Total Geral Estimado*<br>(Itens que Lograram Êxito)</td>
		              <td class="textonormal">
		                <input type="text" name="TotalGeralEstimado" size="17" maxlength="16" value="<?php echo $TotalGeralEstimado; ?>" class="dinheiro" readonly >
				      </td>
		            </tr>
		           	<tr>
		              <td class="textonormal" bgcolor="#DCEDF7">Valor Homologado*<br>(Itens que Lograram Êxito)</td>
		              <td class="textonormal">
		                <input type="text" name="ValorHomologado" size="17" maxlength="16" value="<?php echo $ValorHomologado; ?>" class="dinheiro" readonly >
			          </td>
		            </tr>
				<?php } ?>

	            <?php if( $FaseCodigo == 13 and !$licitacao_possui_solicitacao  ){ ?>
	           	<tr>
	              <td class="textonormal" bgcolor="#DCEDF7">Total Geral Estimado*<br>(Itens que Lograram Êxito)</td>
	              <td class="textonormal">
	                <input type="text" name="TotalGeralEstimado" size="17" maxlength="16" value="<?php echo $TotalGeralEstimado; ?>" class="dinheiro" >
			          </td>
	            </tr>
	           	<tr>
	              <td class="textonormal" bgcolor="#DCEDF7">Valor Homologado*<br>(Itens que Lograram Êxito)</td>
	              <td class="textonormal">
	                <input type="text" name="ValorHomologado" size="17" maxlength="16" value="<?php echo $ValorHomologado; ?>" class="dinheiro" >
			          </td>
	            </tr>
			    <?php } ?>



  	      	</table>
        	</td>
      	</tr>
        <tr>
 	        <td class="textonormal" align="right">
					  <input type="hidden" name="FaseDescricao" value="<?php echo $FaseDescricao; ?>">
					  <input type="hidden" name="ComissaoDescricao" value="<?php echo $ComissaoDescricao; ?>">
					  <input type="hidden" name="FaseLicitacaoUltAlteracao" value="<?php echo $FaseLicitacaoUltAlteracao; ?>">
						<input type="hidden" name="Processo" value="<?php echo $Processo?>">
						<input type="hidden" name="ProcessoAno" value="<?php echo $ProcessoAno?>">
						<input type="hidden" name="ComissaoCodigo" value="<?php echo $ComissaoCodigo?>">
						<input type="hidden" name="OrgaoLicitanteCodigo" value="<?php echo $OrgaoLicitanteCodigo?>">
						<input type="hidden" name="ModalidadeCodigo" value="<?php echo $ModalidadeCodigo?>">
						<input type="hidden" name="RegistroPreco" value="<?php echo $RegistroPreco?>">
						<input type="hidden" name="FaseCodigo" value="<?php echo $FaseCodigo?>">
					  <input type="hidden" name="Homologacao" value="<?php echo $Homologacao; ?>">
					  <input type="hidden" name="FlagValorHomologado" value="<?php echo $FlagValorHomologado; ?>">
	  	    	<input type="hidden" name="ValorHomologadoAntes" value="<?php echo $ValorHomologadoAntes; ?>">
					  <input type="hidden" name="BloqueiosDot" value="<?php echo $BloqueiosDot; ?>">
	  	      <?php if( $FaseCodigo == 13 ){ ?>
 		        	<input type="button" value="Alterar" class="botao" onclick="javascript:enviar('AlterarHomologacao');">
            <?php }else{ ?>
	          	<input type="button" value="Alterar" class="botao" onclick="javascript:enviar('Alterar');">
  	      	<?php } ?>
  					<input type="button" value="Excluir" class="botao" onclick="javascript:enviar('Excluir');">
            <input type="button" value="Voltar" class="botao" onclick="javascript:enviar('Voltar');">
            <input type="hidden" name="Botao" value="">
            <input type="hidden" name="critica" value="1">



          </td>
        </tr>
      </table>
		</td>
	</tr>
	<!-- Fim do Corpo -->
</table>


</form>



</body>
</html>



<script language="javascript" text="">
<!--
document.FaseLicitacaoAlterar.FaseLicitacaoDetalhe.focus();
if( document.FaseLicitacaoAlterar.Homologacao.value == 'S' ){
	<?php
	$Url = "CadFaseLicitacaoConfirmar.php?ProgramaOrigem=FaseLicitacaoAlterar&ValorHomologado=$ValorHomologado&Processo=$Processo&ProcessoAno=$ProcessoAno&ComissaoCodigo=$ComissaoCodigo&OrgaoLicitanteCodigo=$OrgaoLicitanteCodigo&ModalidadeCodigo=$ModalidadeCodigo&RegistroPreco=$RegistroPreco";
	if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }

	if ( !$licitacao_possui_solicitacao) { ?>
	window.open('<?php=$Url?>','pagina','status=no,scrollbars=no,left=270,top=150,width=375,height=220');

	<?php } ?>
}
//-->
</script>
