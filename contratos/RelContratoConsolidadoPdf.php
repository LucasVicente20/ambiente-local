<?php
#-----------------------------------------------------------------------------
# Portal da DGCO
# Programa: RelContratoVencerVencidoPdf.php
# Autor:    Edson Dionisio
# Data:     30/09/2020
# Objetivo: Programa que irá gerar o relatório em pdf
#			de contratos
# OBS.:     Tabulação 2 espaços
################################################
#-----------------------------------------------------------------------------
# Autor: João Madson
# Data : 22/12/2021
# CR 248928
#-----------------------------------------------------------------------------
# Autor: Osmar Celestino
# Data : 14/06/2022
# CR 264543
#-----------------------------------------------------------------------------
# Autor: Lucas Vicente
# Data:  15/02/2023
# Objetivo: CR #275671
# -------------------------------------------------------------------------

include "../funcoes.php";
require_once "../import/phpqrcode/qrlib.php";
// include "../qr_img0.50j/php/qr_img.php";

// require_once "ClassRelContratoConsolidado.php";
require_once "funcoesContrato.php";
require_once "ClassContratosFuncoesGerais.php";
	
# Executa o controle de segurança #
ini_set("session.auto_start", 0);
session_cache_limiter('private_no_expire');
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso('/contratos/RelContratoConsolidadoPdf.php');

function MascarasCPFCNPJ($valor){
	$checaSeFormatado = strripos($valor, "-");
	if($checaSeFormatado == true){
		return $valor;
	}
	if(strlen($valor) == 11){
		$mascara = "###.###.###-##";
		for($i =0; $i <= strlen($mascara)-1; $i++){
			if($mascara[$i] == "#"){
				if(isset($valor[$k])){
				   $maskared .= $valor[$k++];
				}
			}else{
				$maskared .= $mascara[$i];
			}
		}
		return $maskared;
	}
	if(strlen($valor) == 14){
		$mascara = "##.###.###/####-##";
		for($i =0; $i <= strlen($mascara)-1; $i++){
			if($mascara[$i] == "#"){
				if(isset($valor[$k])){
				   $maskared .= $valor[$k++];
				}
			}else{
				$maskared .= $mascara[$i];
			}
		}
		return $maskared;
	}
}
#função auxiliar
function max_cell($array1, $array2,$pdf){
	$array_temp = array();
	
	foreach($array1 as $key => $cell){
		$array_temp[] = ceil($pdf->GetStringWidth($cell)/$array2[$key]);
	}
	
	return max($array_temp);
}

function limitar($str, $limita = 100, $limpar = true){
    if($limpar = true){
        $str = strip_tags($str);
    }
    if(strlen($str) <= $limita){
        return $str;
    }
    $limita_str = substr($str, 0, $limita);
    $ultimo = strrpos($limita_str, ' ');
    return substr($limita_str, 0, $ultimo).'...';
}

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "GET"){
	$Orgao			= $_GET['Orgao'];
}

$arrayTirar  = array('.',',','-','/');


if( $_SERVER['REQUEST_METHOD'] == "POST"){
	$Orgao			= $_POST['Orgao'];
}

$_GET['orgao'] = $_POST['Orgao'];

if(empty($_SESSION['Orgao'])){
	$orgao_selecionado = $_POST['Orgao'];
	$_SESSION['Orgao'] = $orgao_selecionado;
}else{
	$orgao_selecionado = $_SESSION['Orgao'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = "RelContratoConsolidadoPdf.php";
	
//Inicio da criação das partes fixas dos pdf
# Função exibe o Cabeçalho e o Rodapé #
CabecalhoRodapePaisagem();


if(empty($_SESSION['resumidoCompleto'])){
	$resumidoCompleto = $_POST['resumido-completo'];
	$_SESSION['resumidoCompleto'] = $resumidoCompleto;
}else{
	$resumidoCompleto = $_SESSION['resumidoCompleto'];
}
# Informa o Título do Relatário #
if($resumidoCompleto == 'completo'){
	$TituloRelatorio = "Relatório Espelho do Contrato";
}else{
	$TituloRelatorio = "Resumo do Contrato";
}
	
# Cria o objeto PDF, o Default o formato Retrato, A4  e a medida em milémetros #
$pdf = new PDF("L","mm","A4");
	
# Define um apelido para o número total de páginas #
$pdf->AliasNbPages();

# Define as cores do preenchimentos que serão usados #
$pdf->SetFillColor(220,220,220);

# Adiciona uma página no documento #
$pdf->AddPage();

# Seta as fontes que serão usadas na impressão de strings #
$pdf->SetFont("Arial","B",16);
//final das partes fixas
$height_of_cell = 65; // mm
$page_height = 230; // mm (portrait letter)
$bottom_margin = 0; // mm

$db = Conexao();

$objFuncoes = new funcoesContrato();
$objFuncoesGerais = new ContratosFuncoesGerais();

if($_POST['cpf-cnpj'] == 'CNPJ'){
	$fornecedor = str_replace($arrayTirar,'',$_POST['cnpj']);
	$sql_forn = " and forn.aforcrccgc = '" . $fornecedor ."'";
	$sql_desc_forn = " aforcrccgc = '" . $fornecedor ."'";
}else{
	$fornecedor = str_replace($arrayTirar,'',$_POST['cpf']);
	$sql_forn = " and forn.aforcrccpf = '" . $fornecedor ."'";
	$sql_desc_forn = " aforcrccpf = '" . $fornecedor ."'";
}

$array_orgaos = explode(',', $orgao_selecionado);
$Orgao_sel = '';
if(count($array_orgaos) > 1){
	$Orgao_sel = "TODOS";
	$sql_orgao = ""; //" and orlic.CORGLICODI in (" . $orgao_selecionado . ") ";
}else{
	$Orgao = $array_orgaos;
	$Orgao_sel = $array_orgaos;
	
	$sql_orgao = " orgli.corglicodi = " . $orgao_selecionado;
	if(!empty($Orgao[0])){
		$sql_org = "select scc.esolcoobje, orgli.eorglidesc
					from sfpc.tbsolicitacaocompra as scc
					inner join sfpc.tborgaolicitante as orgli 
					on orgli.corglicodi = scc.corglicodi
					where $sql_orgao";
					
		$resultado_orgao = executarSQL($db, $sql_org);
		$resultado_orgao->fetchInto($retorno_orgao, DB_FETCHMODE_OBJECT);
	}
}
if(empty($_SESSION['registro'])){
	$id_registro = $_POST['idregistro'];
	$_SESSION['registro'] = $id_registro;
}elseif(!empty($_SESSION['registro'])){
	$id_registro = $_SESSION['registro']; 
}
$retorno = $objFuncoes->GetDadosContratoSelecionado($id_registro);
if(!empty($retorno->categoriaprocesso)){
	$categoriaProcesso = $objFuncoes->GetDadosCategoriaProcesso($retorno->categoriaprocesso);
}
$gestorAltApostilamento = $objFuncoes->GetgestorAlteradoApostilamento($retorno->seqdocumento);
$repAltAditivo = $objFuncoes->GetRepresentateAlteradoAdtivo($retorno->seqdocumento);
$fornAltAditivo = $objFuncoes->GetFornecedorAlteradoAdtivo($retorno->seqdocumento);

$fornecedorContrato = $objFuncoes->GetFornecedorContrato($retorno->seqdocumento);
$dadosGarantia = $objFuncoes->GetListaGarantiaDocumento();

$dadosItensContrato = $objFuncoes->GetItensContrato($retorno->seqdocumento);

$TipoCOmpra = $objFuncoes->GetTipoCompra($retorno->codicompra);

$dataAditivos = $objFuncoes->PegaAlteracaoDataContratoPorAditivo($retorno->seqdocumento);
	
$Apostilamento = $objFuncoes->GetApostilamento($retorno->seqdocumento);
$Medicao = $objFuncoes->GetMedicao($retorno->seqdocumento);
$valororiginal = $objFuncoesGerais->valorOriginal($id_registro);
$valor_global_aditivo_apostilamento = $objFuncoesGerais->valorGlobal($id_registro);
$valor_executado_acumulado = $objFuncoesGerais->valorExecutado($id_registro);
$saldo_a_executar = $objFuncoesGerais->saldoAExecutar($id_registro);

$situacaoContrato = $objFuncoes->GetSituacaoContrato($retorno->codsequfasedoc,$retorno->codsequsituacaodoc);
$DadosDocAnexo = $objFuncoes->GetDocumentosAnexos($retorno->seqdocumento);

$DadosDocAnexoAdt = $objFuncoes->GetDocumentosAnexosAdtivo($retorno->seqdocumento);
$dadosAlteradosDoc = $objFuncoes->GetDocumentosAnexosApostilamentoAlterado($retorno->seqdocumento);

if(!empty($dadosAlteradosDoc)){
	$DadosDocAnexoApost = $objFuncoes->GetDocumentosAnexosApostilamentoAlterado($retorno->seqdocumento);
}else{
	$DadosDocAnexoApost = $objFuncoes->GetDocumentosAnexosApostilamento($retorno->seqdocumento);
}

$situacaoContrato = $objFuncoes->GetSituacaoContrato($retorno->codsequfasedoc,$retorno->codsequsituacaodoc);  //colocar quando achar uma situação que se encaxe 
$fiscais = $objFuncoes->getDocumentosFicaisEFical($retorno->seqdocumento);
$fiscaisAlterado = $objFuncoes->getDocumentosFiscaisEFiscalAlterado($retorno->seqdocumento);


$valor_medicao = "select COALESCE(sum(vmedcovalm),0.000) as valor_medicao from sfpc.tbmedicaocontrato where cdocpcsequ = ". $retorno->seqdocumento;
$valor_med = executarSQL($db, $valor_medicao);
$valor_med->fetchInto($soma_valor_med, DB_FETCHMODE_OBJECT);


$QtdAtendidaTeste = count($retorno);
				
if($QtdAtendidaTeste != 0){
	$FlagItemAtendido = 1;
}


	$sql_verifica_se_existe_aditivo_contrato = "select count(*) as qtd_aditivos from sfpc.tbaditivo as ad where ad.cdocpcseq1 = " . $retorno->seqdocumento;
	$existe_aditivo_contrato = executarSQL($db, $sql_verifica_se_existe_aditivo_contrato);
	$existe_aditivo_contrato->fetchInto($existe_aditivo_contrato, DB_FETCHMODE_OBJECT);

	if($existe_aditivo_contrato->qtd_aditivos > 0){
		$sql_case_aditivo = "and ad.aaditinuad = (CASE 
			WHEN ad.cdocpcseq1 IS NOT NULL THEN (select MAX(aaditinuad) from sfpc.tbaditivo where cdocpcseq1 = ad.cdocpcseq1 and ctpadisequ = 11 and doc.cfasedsequ = 4 and doc.ctidocsequ = 2 and doc.csitdcsequ = 1)
		END)";
	}else{
		$sql_case_aditivo = "";
	}
	$case = !empty($data_ini_exec) ? $sql_case_aditivo : "";

	$sql_datas_aditivo_contrato = "
		select ad.aaditiapea as prazo, ad.aaditinuad, ad.daditiinvg, ad.daditifivg, ad.daditiinex as data_ini_exec, ad.aaditinfev, ad.daditifiex as data_fim_execucao, con.cctrpcopex, ad.aaditiapea as prazo 
		from sfpc.tbaditivo as ad
		inner join sfpc.tbcontratosfpc as con on (con.cdocpcsequ = ad.cdocpcseq1 and ad.faditialpz ='SIM')
		left join sfpc.tbdocumentosfpc doc on (ad.cdocpcsequ = doc.cdocpcsequ)
		where ad.cdocpcseq1 = $retorno->seqdocumento
		$case
		and ad.faditialpz ='SIM' and doc.cfasedsequ = 4 and doc.ctidocsequ = 2 and doc.csitdcsequ = 1
		order  by ad.cdocpcsequ desc limit 1
	";

	$sql_datas_aditivo = executarSQL($db, $sql_datas_aditivo_contrato);
	$sql_datas_aditivo->fetchInto($sql_datas_aditivo, DB_FETCHMODE_OBJECT);

	if(empty($sql_datas_aditivo->data_fim_execucao)){
		if($retorno->opexeccontrato == 'M'){
			$periodo = " +".$sql_datas_aditivo->prazo." months";
		}else{
			$periodo = " +".$sql_datas_aditivo->prazo." days";
		}
		
		$data_execucao_final = date('d/m/Y', strtotime($periodo, strtotime($sql_datas_aditivo->data_ini_exec))-1);
	}else{
		$data_execucao_final = DataBarra($sql_datas_aditivo->data_fim_execucao);
	}

	$ultimo_aditivo = "select MAX(aaditinuad) as ultimo_aditivo from sfpc.tbaditivo where cdocpcseq1 = ". $retorno->seqdocumento;
	$ultimo_adit = executarSQL($db, $ultimo_aditivo);
	$ultimo_adit->fetchInto($ultimo_adtvo, DB_FETCHMODE_OBJECT);
	
	$ultimo_apostilamento = "select MAX(aapostnuap) as ultimo_apostilamento from sfpc.tbapostilamento where cdocpcseq2 = ". $retorno->seqdocumento;
	$ultimo_apost = executarSQL($db, $ultimo_apostilamento);
	$ultimo_apost->fetchInto($ultimo_apostmento, DB_FETCHMODE_OBJECT);

	$retorno_ult_aditivo = $ultimo_adtvo->ultimo_aditivo;
	$retorno_ult_apost = $ultimo_apostmento->ultimo_apostilamento;

	// $SaldoExec = 0;
	// $vtAd = floatval( $vtAditivo[0]->vtaditivo);
	// $vtAp = floatval($vtApost[0]->vtapost);
	// $valorTotal = $retorno->valortotalcontrato;
	// $vtOgAdAp = ((floatval($valororiginal) + $vtAd) + $vtAp);
	
	if(!empty($fornecedorContrato)){
		foreach($fornecedorContrato as $fonec){
				$listaFornecedor[] = (object) array(
													'aforcrsequ'=>$fonec->aforcrsequ,
													'nforcrrazs'=>$fonec->nforcrrazs,
													'eforcrlogr'=>$fonec->eforcrlogr,
													'aforcrnume'=>$fonec->aforcrnume,
													'eforcrcomp'=>$fonec->eforcrcomp,
													'eforcrbair'=>$fornec->eforcrbair,
													'aforcrccpf'=>MascarasCPFCNPJ($fonec->aforcrccpf),
													'aforcrccgc'=>MascarasCPFCNPJ($fonec->aforcrccgc),
													'cforcresta'=>$fonec->cforcresta,
													'remover'=>'N'
												);
		}
	}

			$dadosAnexoAux = array();
            $dadosAposAnex = array();
            $dadosMedicaoAnex = array();
            $DadosDocAnexoAditivo = array();
            $apsAux = array();
            $medAux = array();
            $auxDados = array();
                
                foreach($DadosDocAnexo as $doc){
				
                    if(!empty($doc->nomearquivo)){
                        $dadosAnexoAux[]  = (object) array(
                                                    'nome_arquivo'       =>$doc->nomearquivo,
                                                    'arquivo'           => $doc->arquivo,
                                                    'sequdoc'     => $doc->sequdocumento,
                                                    'sequarquivo'     => $doc->sequdocanexo,
                                                    'data_inclusao'  => $doc->datacadasarquivo,
                                                    'usermod'           => $doc->usermod,
                                                    "origem"            =>"CONTRATO",
                                                    'ativo'             => 'S'
                                                );
                    }
				}
				
                foreach($DadosDocAnexoAdt as $docadt){
				
                    if(!empty($docadt->nomearquivo)){
                        $DadosDocAnexoAditivo[]  = (object) array(
                                                'nome_arquivo'       =>$docadt->nomearquivo,
                                                 'arquivo'           => $docadt->arquivo,
                                                 'sequdoc'     => $docadt->sequdocumento,
                                                 'sequarquivo'     => $docadt->sequdocanexo,
                                                'data_inclusao'  => $docadt->datacadasarquivo,
                                                'usermod'           => $docadt->usermod,
                                                "origem"            => "ADTIVO",
                                                'ativo'             => 'S'
                                            );
                                            $qtdKeyArray++; 
                    }                    
                }

                foreach($DadosDocAnexoApost as $docapost){
                    if(!empty($docapost->nomearquivo)){
                        $dadosAposAnex[]  = (object) array(
                                                'nome_arquivo'       =>$docapost->nomearquivo,
                                                'arquivo'           => $docapost->arquivo,
                                                'sequdoc'     => $docapost->sequdocumento,
                                                'sequarquivo'     => $docapost->sequdocanexo,
                                                'data_inclusao'  => $docapost->datacadasarquivo,
                                                'usermod'           => $docapost->usermod,
                                                "origem"            => "APOSTILAMENTO",
                                                'ativo'             => 'S'
                                            );
                    }

                }
                $DadosDocAnexoMed = $objFuncoes->GetDocumentosAnexosMedicao($retorno->seqdocumento);
                foreach($DadosDocAnexoMed as $docmed){
                    if(!empty($docmed->nomearquivo)){
                        $dadosMedicaoAnex[]  = (object) array(
                                                'nome_arquivo'       =>$docmed->nomearquivo,
                                                'arquivo'           => $docmed->arquivo,
                                                'sequdoc'     => $docmed->sequdocumento,
                                                'sequarquivo'     => $docmed->sequdocanexo,
                                                'data_inclusao'  => $docmed->datacadasarquivo,
                                                'usermod'           => $docmed->usermod,
                                                "origem"            => "MEDICAO",
                                                'ativo'             => 'S'
                                            );
                    }

                }
                
                $apsAux = array_merge($dadosAnexoAux,$DadosDocAnexoAditivo);
                $medAux =  array_merge($apsAux,$dadosAposAnex);
                $DadosDocAnexo = (object) array_merge($medAux,$dadosMedicaoAnex);

            if(db::isError($resultado) ){
				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
			}else{
			
				$num_contrato = $retorno->ncontrato;
				$ano_contrato = $retorno->anocontrato;
				$objetivo_contrato = $retorno->objetivocontrato;
				$razao_contrato = empty($fornAltAditivo[0]->razao)?$retorno->razao:$fornAltAditivo[0]->razao;
				$cep_contrato = empty($fornAltAditivo[0]->cep)?$retorno->cep:$fornAltAditivo[0]->cep;
				$endereco_fornecedor = empty($fornAltAditivo[0]->rua)?$retorno->endereco:$fornAltAditivo[0]->rua;
				$cnpj_fornecedor = empty($fornAltAditivo[0]->cnpj)?MascarasCPFCNPJ($retorno->cnpj):MascarasCPFCNPJ($fornAltAditivo[0]->cnpj);
				$cpf_fornecedor =  empty($fornAltAditivo[0]->cpf)?MascarasCPFCNPJ($retorno->cpf):MascarasCPFCNPJ($fornAltAditivo[0]->cpf);
				$cep_fornecedor = $retorno->cepfornecedor;
				$num_fornecedor = empty($fornAltAditivo[0]->numero)?$retorno->numerofornecedor:$fornAltAditivo[0]->numero;
				$complemento_fornecedor = empty($fornAltAditivo[0]->complemento)?$retorno->complementofornecedor:$fornAltAditivo[0]->complemento;
				$bairro_fornecedor = empty($fornAltAditivo[0]->bairro)?$retorno->bairrofornecedor:$fornAltAditivo[0]->bairro;
				$cidade_fornecedor = empty($fornAltAditivo[0]->cidade)?$retorno->cidadefornecedor:$fornAltAditivo[0]->cidade;
				$uf_fornecedor = empty($fornAltAditivo[0]->estado)?$retorno->uffornecedor:$fornAltAditivo[0]->estado;
				$tel_fornecedor = $retorno->tel1fornecedor;
				$tipo_fornecedor = $retorno->tipofornecedor;
				$obra = $retorno->obra;
				$continuo = $retorno->econtinuo;
				$consorcio = $retorno->consocio;
				$convenio = $retorno->nconvenio;
				//|Madson checagem de alteração via aditivo do representante; repAltAditivo empty($repAltAditivo[0]) ? $dadosContratos->nomerepresenlegal : $repAltAditivo[0]->naditinmrl
				$nome_representante_legal = empty($repAltAditivo[0]->naditinmrl) ? strtoupper($retorno->nomerepresenlegal) : strtoupper($repAltAditivo[0]->naditinmrl);
				$cpf_representante_legal = empty($repAltAditivo[0]->eaditicpfr) ? MascarasCPFCNPJ($retorno->cpfrepresenlegal) : MascarasCPFCNPJ($repAltAditivo[0]->eaditicpfr);
				$email_representante_legal = empty($repAltAditivo[0]->naditimlrl) ? $retorno->emailrepresenlegal : $repAltAditivo[0]->naditimlrl;
				$cargo_representante_legal = empty($repAltAditivo[0]->eaditicgrl) ? strtoupper($retorno->cargorepresenlegal) : strtoupper($repAltAditivo[0]->eaditicgrl);
				$tel_representante_legal = empty($repAltAditivo[0]->eadititlrl) ? $retorno->telrepresenlegal : $repAltAditivo[0]->eadititlrl;
				$identidade_representante_legal = empty($repAltAditivo[0]->eaditiidrl) ? strtoupper($retorno->identidaderepreslegal) : strtoupper($repAltAditivo[0]->eaditiidrl);
				$orgaoexp_representante_legal = empty($repAltAditivo[0]->naditioerl) ? strtoupper($retorno->orgaoexpedrepreselegal) : strtoupper($repAltAditivo[0]->naditioerl);
				$cidade_representante_legal = empty($repAltAditivo[0]->naditicdrl) ? strtoupper($retorno->cidadedomrepresenlegal) : strtoupper($repAltAditivo[0]->naditicdrl);
				$estado_representante_legal = empty($repAltAditivo[0]->naditiufrl) ? strtoupper($retorno->estdomicrepresenlegal) : strtoupper($repAltAditivo[0]->naditiufrl);
				$nacionalidade_representante_legal = empty($repAltAditivo[0]->naditinarl) ? strtoupper($retorno->naciorepresenlegal) : strtoupper($repAltAditivo[0]->naditinarl);
				$uf_representante_legal = empty($repAltAditivo[0]->naditiedrl) ? strtoupper($retorno->ufrgrepresenlegal) : strtoupper($repAltAditivo[0]->naditiedrl);
				$profissao_representante_legal =  empty($repAltAditivo[0]->naditiprrl) ? strtoupper($retorno->profirepresenlegal) : strtoupper($repAltAditivo[0]->naditiprrl);

				$dadosContratos->estacivilrepresenlegal = empty($repAltAditivo[0]->caditiecrl) ? $retorno->estacivilrepresenlegal : $repAltAditivo[0]->caditiecrl;
				switch ($retorno->estacivilrepresenlegal) {
					case 'S':
						$estado_civil_representante_legal =  strtoupper("Solteiro");
					break;
					case 'C':
						$estado_civil_representante_legal =  strtoupper("Casado");
					break;
					case 'D':
						$estado_civil_representante_legal =  strtoupper("Divorciado");
					break;
					case 'V':
						$estado_civil_representante_legal =  strtoupper("Viúvo");
					break;
					case 'O':
						$estado_civil_representante_legal =  strtoupper("Outros");
					break;
				}

				$nome_gestor = empty($gestorAltApostilamento[0]->nomegestor) ? strtoupper($retorno->nomegestor) : strtoupper($gestorAltApostilamento[0]->nomegestor);
				$cpf_gestor = empty($gestorAltApostilamento[0]->cpfgestor) ? MascarasCPFCNPJ($retorno->cpfgestor) : MascarasCPFCNPJ($gestorAltApostilamento[0]->cpfgestor);
				$matricula_gestor = empty($gestorAltApostilamento[0]->matgestor) ? $retorno->matgestor : $gestorAltApostilamento[0]->matgestor;
				$email_gestor = empty($gestorAltApostilamento[0]->emailgestor) ? $retorno->emailgestor : $gestorAltApostilamento[0]->emailgestor;
				$tel_gestor = empty($gestorAltApostilamento[0]->fonegestor) ? $retorno->fonegestor : $gestorAltApostilamento[0]->fonegestor;
				$orgao = empty($gestorAltApostilamento[0]->orgao) ? $retorno->orgao : $gestorAltApostilamento[0]->orgao;
				$orgao_contratante = empty($gestorAltApostilamento[0]->orgaocontratante) ? $retorno->orgaocontratante : $gestorAltApostilamento[0]->orgaocontratante;
				
				$modo_fornecimento = $retorno->regexecoumodfornec;
				$opcao_execucao_contrato = strtoupper($retorno->opexeccontrato == "D" ? 'Dias' : 'Meses');
				$prazo_execucao_contrato = $retorno->prazoexec;



				if(!empty($retorno->orgao) && !empty($retorno->unidade) && !empty($retorno->codisolicitacao) && !empty($retorno->anos)){
					$SCC       = sprintf('%02s', $retorno->orgao) . sprintf('%02s', $retorno->unidade) . '.' . sprintf('%04s', $retorno->codisolicitacao) . '/' . $retorno->anos;
				}
			}

if($resumidoCompleto == 'completo'){

	$dados_contrato = $objFuncoes->getContrato($retorno->seqdocumento);
	$ectrpcnumf = $dados_contrato[0]->ectrpcnumf;
	$objetivo = $dados_contrato[0]->ectrpcobje;
	

	$aditivos_contrato = $objFuncoes->getAditivosContrato($retorno->seqdocumento);
	

	$apostilamentos_contrato = $objFuncoes->getApostilamentosContrato($retorno->seqdocumento);
	foreach ($apostilamentos_contrato as $key => $apostilamento) {
		$cod_apostilamento = $apostilamento->cdocpcsequ; //cod_apostilamento
		$cod_tipo_apostilamento = $apostilamento->ctpaposequ; // Código do tipo de apostilamento
		$sequ_apostilamento = $apostilamento->aapostnuap; // cod. sequencial de apostilamento
		$data_apostilamento = $apostilamento->dapostcada; // cod. contrato
		$valor_apostilamento = $apostilamento->vapostvtap; // Valor apostilamento

		// Apostilamento
		$apostilamento = $objFuncoes->getApostilamentoRCCPDF($cod_apostilamento);
		$tipo_apostilamento = $objFuncoes->getApostilamentoNome($cod_tipo_apostilamento);
		$fiscais = $objFuncoes->getDocumentosFicaisEFical($retorno->seqdocumento);
		$fiscaisAlterado = $objFuncoes->getDocumentosFiscaisEFiscalAlterado($retorno->seqdocumento);
		
		$tipo = $tipo_apostilamento[0]->etpapodesc;

		$cdocpcseq2 = $apostilamento[0]->cdocpcseq2;
		$data_apostilamento = $apostilamento[0]->dapostcada;
		$valor_apostilamento = $apostilamento[0]->vapostvtap;
		$valor_retro_apostilamento = $apostilamento[0]->vapostretr;
		$num_apostilamento = $apostilamento[0]->aapostnuap;
		$nome_gestor_apostilamento = $apostilamento[0]->napostnmgt;
		$cpf_gestor_apostilamento = $apostilamento[0]->napostcpfg;
		$tel_gestor_apostilamento = $apostilamento[0]->eaposttlgt;
		$mat_gestor_apostilamento = $apostilamento[0]->napostmtgt;
		$email_gestor_apostilamento = $apostilamento[0]->napostmlgt;
		$cpf_gestor_apostilamento = $apostilamento[0]->napostcpfg;
		$cpf_gestor_apostilamento = $apostilamento[0]->napostcpfg;
		$codContrato_apostilamento = $objFuncoes->getContrato($cdocpcseq2);
	}

	$medicoes_contrato = $objFuncoes->getMedicoesContrato($retorno->seqdocumento);	
}

$db->disconnect();

# Início do Cabeçalho Móvel #

if($FlagItemAtendido == 1){
	$db   = Conexao();
	
	if( db::isError($res) ){
			ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
	}else{

		$h = 4;
		$hm = 0;
		$h1 = $pdf->GetStringHeight(113, $h, 'Período', "L");
		$h2 = $pdf->GetStringHeight(105, $h, 'orgão', "L");
		$hm = $h1;
		if ($hm < $h2)
			$hm = $h2;
		$h1 = $hm / ($h1 / $h);
		$h2 = $hm / ($h2 / $h);

		if ($hm < 6) {
			$h1 = 6;
			$h2 = 6;
			$hm = 6;
		}
		
		$pdf->SetFont("Arial", "B", 9);
		// $pdf->Cell(50, 6, "  Número do Contrato/Ano ", 1, 0, "L", 1);
		
		// $x = $pdf->GetX() + 150;
		// $y = $pdf->GetY();
		// $pdf->MultiCell(150, $h1, !empty($_POST['numerocontratoano']) ?  $_POST['numerocontratoano'] : "", 1, "L", 0);
		// $pdf->SetXY($x, $y);
		// $pdf->Cell(48, $hm, "  Número da Solicitação (SCC) ", 1, 0, "L", 1);
		// $pdf->MultiCell(32, $h2, !empty($_POST['numeroScc']) ?  $_POST['numeroScc'] : "", 1, "L", 0, false, 1);
		
		// $pdf->Cell(50, 6, "  Orgão ", 1, 0, "L", 1);		
		// $x = $pdf->GetX() + 150;
		// $y = $pdf->GetY();
		// // $pdf->SetFont("Arial", "B", 7.5);
		// $pdf->MultiCell(150, $h1, (empty($_POST['Orgao'])) ? 'TODOS' : $retorno_orgao->eorglidesc, 1, "L", 0);
		// $pdf->SetXY($x, $y);
		// $pdf->Cell(48, $hm, "  Tipo de Contrato ", 1, 0, "L", 1);
		// $pdf->MultiCell(32, $h2, strtoupper($_POST['tipo']), 1, "L", 0, false, 1);

		// $pdf->SetFont("Arial", "B", 8);
		// $pdf->Cell(50, 6, "  Fornecedor ", 1, 0, "L", 1);		
		// $x = $pdf->GetX() + 230;
		// $y = $pdf->GetY();
		// $pdf->MultiCell(230, $h1, (!empty($_POST['cnpj']) ? $_POST['cnpj'] : $_POST['cpf']), 1, "L", 0);
		// $pdf->SetXY($x, $y);
		// $pdf->Ln();
		
		// $pdf->Cell(280, 5, " ", 0, 1, "C", 0);
		if($resumidoCompleto == 'completo'){
			$tipo_contrato = "COMPLETO";
		}else{
			$tipo_contrato = "RESUMIDO";
		}
		
		$pdf->Cell(280, 5, "CONTRATO CONSOLIDADO - " . $tipo_contrato, 1, 1, "C", 1);
		
		$pdf->SetFont("Arial", "B", 8);

		$pdf->Cell(80, 6, "  Número do Contrato/Ano ", 1, 0, "L", 1);
		$pdf->Cell(200, 6, $num_contrato, 1, 1, "L", 0);
		$pdf->Cell(80, 6, "  Solicitação de Compra/Contratação-SCC ", 1, 0, "L", 1);
		$pdf->Cell(200, 6, $SCC, 1, 1, "L", 0);$informacoes_fornecedor = 'CNPJ do Contratado :	' . $cnpj_fornecedor . '<br>Razão Social : ' . $razao_contrato . '<br>Logradouro : ' . $endereco_fornecedor . ', Número: ' . $num_fornecedor . (!empty($complemento_fornecedor) ? '<br>Complemento : ' .	$complemento_fornecedor . ', ' : "<br>") . 'Bairro : ' . $bairro_fornecedor . ', Cidade : ' . $cidade_fornecedor . ' - UF : ' . $uf_fornecedor;
		$texto_separado = explode('<br>', $informacoes_fornecedor, 4);
		$texto = "$texto_separado[0]\n$texto_separado[1]\n$texto_separado[2]\n$texto_separado[3]";
	
		$pdf->Rect(10, 74, 80, 24, 'DF');
		$pdf->SetFillColor(220);
	
		$x = $pdf->GetX() + 80;
		$y = $pdf->GetY();
		$pdf->MultiCell(80, 24, " Contratado ", 1, "L", 0);
		$pdf->SetXY($x, $y);
		$x = $pdf->GetX();
		$y = $pdf->GetY() + 24;
		$pdf->MultiCell(200, 6, $texto, 1, "L", 0);
		$pdf->SetXY($x, $y);
		$pdf->Ln();
		$pdf->Cell(80, 6, "  Origem ", 1, 0, "L", 1);
		$pdf->Cell(200, 6, $TipoCOmpra->etpcomnome, 1, 1, "L", 0);
		$pdf->Cell(80, 6, "  Orgão Contratante Responsável ", 1, 0, "L", 1);
		$pdf->Cell(200, 6, !empty($orgLicitante) ? $orgLicitante : $orgao_contratante, 1, 1, "L", 0);
		// var_dump($objetivo_contrato);exit;
		if(strlen($objetivo_contrato) < 130){
			$pdf->Cell(80, 6, "  Objeto ", 1, 0, "L", 1);
			$pdf->Cell(200, 6, $objetivo_contrato, 1, 1, "L", 0);

			$informacoes_fornecedor = 'CNPJ do Contratado :	' . $cnpj_fornecedor . '<br>Razão Social : ' . $razao_contrato . '<br>Logradouro : ' . $endereco_fornecedor . ', Número: ' . $num_fornecedor . (!empty($complemento_fornecedor) ? '<br>Complemento : ' .	$complemento_fornecedor . ', ' : "<br>") . 'Bairro : ' . $bairro_fornecedor . ', Cidade : ' . $cidade_fornecedor . ' - UF : ' . $uf_fornecedor;
			$texto_separado = explode('<br>', $informacoes_fornecedor, 4);
			$texto = "$texto_separado[0]\n$texto_separado[1]\n$texto_separado[2]\n$texto_separado[3]";
		
			$pdf->Rect(10, 74, 80, 24, 'DF');
			$pdf->SetFillColor(220);
			
			$x = $pdf->GetX() + 80;
			$y = $pdf->GetY();
			$pdf->MultiCell(80, 24, " Contratado ", 1, "L", 0);
			$pdf->SetXY($x, $y);
			$x = $pdf->GetX();
			$y = $pdf->GetY() + 24;
			$pdf->MultiCell(200, 6, $texto, 1, "L", 0);
			$pdf->SetXY($x, $y);
			$pdf->Ln();
		}else{
			$loop = strlen($objetivo_contrato);
			$loop = round($loop / 130);
			// var_dump($loop);
			// $loop++;
			// var_dump($loop);
			$adaptaTamamnho = $loop * 6;
			$adaptaTamLbl = 6 + $adaptaTamamnho;
			$x = $pdf->GetX() + 80;
			$y = $pdf->GetY();
			$pdf->MultiCell(80, $adaptaTamLbl, "  Objeto ", 1, "L", 1);
			$pdf->SetXY($x, $y);
			$x = $pdf->GetX();
			$y = $pdf->GetY() + $adaptaTamamnho;
			if($loop > 1){
				$pdf->SetFontSize(7.4);
				$fontSetada = true;
			}
			$pdf->MultiCell(200, 6, "$objetivo_contrato", 1, "J", 0);
			if($fontSetada == true){
				$pdf->SetFontSize(9);
			}
			$pdf->SetXY($x, $y);
			$pdf->Ln();

			$informacoes_fornecedor = '<br>CNPJ do Contratado :	 ' . $cnpj_fornecedor . '<br>Razão Social : ' . $razao_contrato . '<br>Logradouro : ' . $endereco_fornecedor . ' Número: ' . $num_fornecedor . (!empty($complemento_fornecedor) ? '<br>Complemento : ' .	$complemento_fornecedor  : "") . 'Bairro : ' . $bairro_fornecedor . ' Cidade : ' . $cidade_fornecedor . ' UF : ' . $uf_fornecedor;
			$texto_separado = explode('<br>', $informacoes_fornecedor, 4);
			$texto = "$texto_separado[0]\n$texto_separado[1]\n$texto_separado[2]\n$texto_separado[3]";
		
			$pdf->Rect(10, 74+$adaptaTamamnho, 80, 24, 'DF');
			$pdf->SetFillColor(220);
			
			$x = $pdf->GetX() + 80;
			$y = $pdf->GetY();
			$pdf->MultiCell(80, 24, " Contratado ", 1, "L", 0);
			$pdf->SetXY($x, $y);
			$x = $pdf->GetX();
			$y = $pdf->GetY() + 24;
			$pdf->MultiCell(200, 6, $texto, 1, "L", 0);
			$pdf->SetXY($x, $y);
			$pdf->Ln();
		}

	


				
		$pdf->Cell(80, 6, "  Consórcio / Matriz-Filial / Publicidade ? ", 1, 0, "L", 1);
		$pdf->Cell(200, 6, $consorcio, 1, 1, "L", 0);

		if($consorcio == 'SIM' && !empty($listaFornecedor)){
			$pdf->Cell(280, 5, " ", 0, 1, "C", 0);
			$pdf->Cell(280, 5, " FORNECEDORES ", 1, 1, "C", 1);

			$x = $pdf->GetX() + 140;
			$y = $pdf->GetY();
			$pdf->MultiCell(140, 6, "  IDENTIFICADOR DO CONTRATADO  ", 1, "C", 1);
			$pdf->SetXY($x, $y);

			$x = $pdf->GetX() + 140;
			$y = $pdf->GetY();
			$pdf->MultiCell(140, 6, " RAZÃO SOCIAL ", 1, "C", 1);
			$pdf->SetXY($x, $y);

			$pdf->Ln();
		
			foreach ($listaFornecedor as $fornecedor) {
				$x = $pdf->GetX() + 140;
				$y = $pdf->GetY();
				$pdf->MultiCell(140, 6, $fornecedor->nforcrrazs, 1, "C", 0);
				$pdf->SetXY($x, $y);

				$x = $pdf->GetX() + 140;
				$y = $pdf->GetY();
				$pdf->MultiCell(140, 6, (!empty($fornecedor->aforcrccgc)) ? $fornecedor->aforcrccgc : $fornecedor->aforcrccpf, 1, "C", 0);
				$pdf->SetXY($x, $y);
				$pdf->Ln();
			}
			$pdf->Cell(280, 5, " ", 0, 1, "C", 0);
		}
		
		$pdf->Cell(80, 6, "  Contínuo ", 1, 0, "L", 1);
		$pdf->Cell(200, 6, $continuo, 1, 1, "L", 0);

		$pdf->Cell(80, 6, "  Obra  ", 1, 0, "L", 1);
		$pdf->Cell(200, 6, $obra, 1, 1, "L", 0);
		$pdf->Cell(80, 6, "  Modo de Fornecimento  ", 1, 0, "L", 1);
		$pdf->Cell(200, 6, $modo_fornecimento, 1, 1, "L", 0);
		$pdf->Cell(80, 6, "  Opção de Execução de Contrato  ", 1, 0, "L", 1);
		$pdf->Cell(200, 6, $opcao_execucao_contrato, 1, 1, "L", 0);
		$pdf->Cell(80, 6, "  Prazo de Execução de Contrato  ", 1, 0, "L", 1);
		$pdf->Cell(200, 6, $prazo_execucao_contrato, 1, 1, "L", 0);
		$pdf->Cell(80, 6, "  Categoria do processo ", 1, 0, "L", 1);
		$pdf->Cell(200, 6, (!empty($categoriaProcesso->epnccpnome)) ? $categoriaProcesso->epnccpnome : ' - ', 1, 1, "L", 0);
		$pdf->Cell(80, 6, "  Data de Publicação no DOM ", 1, 0, "L", 1);
		$pdf->Cell(200, 6, (!empty($retorno->datapublic)) ? DataBarra($retorno->datapublic) : ' - ', 1, 1, "L", 0);

		$pdf->Cell(280, 5, " ", 0, 1, "C", 0);
		
		if($consorcio == 'SIM'){
			$pdf->Ln(10);
		}else{
			$pdf->Ln(2);
		}
		if(round($pdf->GetY()) >= 165){
			// echo oi;
			$x = $pdf->GetX();
			$y = $pdf->GetY() + 20;
			$pdf->SetXY($x, $y);
		}
		$pdf->Cell(280, 5, " PERÍODOS ", 1, 1, "C", 1);

		$x = $pdf->GetX() + 80;
		$y = $pdf->GetY();
		$pdf->MultiCell(80, 6, "  ", 1, "C", 1);
		$pdf->SetXY($x, $y);

		$x = $pdf->GetX() + 100;
		$y = $pdf->GetY();
		$pdf->MultiCell(100, 6, " DATA DE INÍCIO ", 1, "C", 1);
		$pdf->SetXY($x, $y);

		$x = $pdf->GetX() + 100;
		$y = $pdf->GetY();
		$pdf->MultiCell(100, 6, " DATA DE TÉRMINO ", 1, "C", 1);
		$pdf->SetXY($x, $y);
		$pdf->Ln();

		// Datas ///////////////////////////////////////////////////////
		$data_ini_vigencia = DataBarra($retorno->datainivige); //empty($dataAditivos->daditiinvg) ? DataBarra($retorno->datainivige) : DataBarra($dataAditivos->daditiinvg);
		$data_fim_vigencia = empty($sql_datas_aditivo->daditifivg) ? DataBarra($retorno->datafimvige) : DataBarra($sql_datas_aditivo->daditifivg);
		$data_ini_exec = DataBarra($retorno->datainiexec); //empty($sql_datas_aditivo->daditiinex) ? DataBarra($retorno->datainiexec) : DataBarra($sql_datas_aditivo->daditiinex);
		if($existe_aditivo_contrato->qtd_aditivos > 0 && !empty($sql_datas_aditivo->data_ini_exec)){
			$data = explode("/", $data_execucao_final);
			$data_fim_exec = $data[0]."-".$data[1]."-".$data[2];
			$dataFimExec = new DateTime($data_fim_exec);
			$dataFimExec->modify('-1 day');
			$data_fim_exec = $dataFimExec->format('d/m/Y');
		}else{
			$data_fim_exec = DataBarra($retorno->datafimexec);
		}

		$x = $pdf->GetX() + 80;
		$y = $pdf->GetY();
		$pdf->MultiCell(80, 6, " Vigência ", 1, "C", 1);
		$pdf->SetXY($x, $y);

		$x = $pdf->GetX() + 100;
		$y = $pdf->GetY();
		$pdf->MultiCell(100, 6, $data_ini_vigencia, 1, "C", 0);
		$pdf->SetXY($x, $y);

		$x = $pdf->GetX() + 100;
		$y = $pdf->GetY();
		$pdf->MultiCell(100, 6, $data_fim_vigencia, 1, "C", 0);
		$pdf->SetXY($x, $y);
		$pdf->Ln();

		$x = $pdf->GetX() + 80;
		$y = $pdf->GetY();
		$pdf->MultiCell(80, 6, " Execução ", 1, "C", 1);
		$pdf->SetXY($x, $y);

		$x = $pdf->GetX() + 100;
		$y = $pdf->GetY();
		$pdf->MultiCell(100, 6,$data_ini_exec, 1, "C", 0);
		$pdf->SetXY($x, $y);
		
		$x = $pdf->GetX() + 100;
		$y = $pdf->GetY();
		$pdf->MultiCell(100, 6, $data_fim_exec, 1, "C", 0);
		$pdf->SetXY($x, $y);
		$pdf->Ln();
		$pdf->Cell(280, 5, " ", 0, 1, "C", 0);

		//////////////////////////////////////////////////////////////////

		$pdf->Cell(80, 6, "  Valor Original ", 1, 0, "L", 1);
		$pdf->Cell(200, 6,  $valororiginal, 1, 1, "L", 0);
		$pdf->Cell(80, 6, "  Valor Global com Aditivos/Apostilamentos ", 1, 0, "L", 1);
		$pdf->Cell(200, 6,  $valor_global_aditivo_apostilamento, 1, 1, "L", 0);

		$pdf->Cell(80, 6, "  Valor Executado Acumulado ", 1, 0, "L", 1);
		$pdf->Cell(200, 6,  $valor_executado_acumulado, 1, 1, "L", 0);

		$pdf->Cell(80, 6, "  Saldo a Executar ", 1, 0, "L", 1);
		$pdf->Cell(200, 6,  $saldo_a_executar, 1, 1, "L", 0);
	
		$pdf->Cell(80, 6, "  Garantia ", 1, 0, "L", 1);
		if(!empty($retorno->codisequtipogarantia)){		
		foreach($dadosGarantia as $garantia){

			if(!empty($retorno->codisequtipogarantia) && $garantia->codgarantia == $retorno->codisequtipogarantia){
				$pdf->Cell(200, 6, ($garantia->codgarantia == $retorno->codisequtipogarantia) ? $garantia->descricaogarantia : '', 1, 1, "L", 0);
			}
		 }
		}else{
			$pdf->Cell(200, 6, 'SEM GARANTIA', 1, 1, "L", 0);
		}

		$pdf->Cell(80, 6, "  Número de Parcelas ", 1, 0, "L", 1);
		$pdf->Cell(200, 6,$retorno->numerodeparcelas , 1, 1, "L", 0);
		$pdf->Cell(80, 6, "  Valor das Parcelas ", 1, 0, "L", 1);
		$pdf->Cell(200, 6,number_format($retorno->valordaparcela,4,',','.') , 1, 1, "L", 0);

		if(!empty($retorno_ult_aditivo)){
			$pdf->Cell(80, 6, "  Número do Último Aditivo ", 1, 0, "L", 1);
			$pdf->Cell(200, 6,  $retorno_ult_aditivo, 1, 1, "L", 0);
		}

		if(!empty($retorno_ult_apost)){
			$pdf->Cell(80, 6, "  Número do Último Apostilamento ", 1, 0, "L", 1);
			$pdf->Cell(200, 6,  $retorno_ult_apost, 1, 1, "L", 0);
		}

		$pdf->Cell(280, 5, " ", 0, 1, "C", 0);

		$pdf->Cell(280, 5, " REPRESENTANTE LEGAL ", 1, 1, "C", 1);
		$pdf->Cell(80, 6, "  Nome  ", 1, 0, "L", 1);
		$pdf->Cell(200, 6, $nome_representante_legal, 1, 1, "L", 0);
		$pdf->Cell(80, 6, "  CPF  ", 1, 0, "L", 1);
		$pdf->Cell(200, 6, $cpf_representante_legal, 1, 1, "L", 0); // Verificar se é CNPJ ou CPF
		$pdf->Cell(80, 6, "  Cargo ", 1, 0, "L", 1);
		$pdf->Cell(200, 6, $cargo_representante_legal, 1, 1, "L", 0);
		$pdf->Cell(80, 6, " Identidade ", 1, 0, "L", 1);
		$pdf->Cell(200, 6, $identidade_representante_legal, 1, 1, "L", 0);
		$pdf->Cell(80, 6, " Orgão Emissor ", 1, 0, "L", 1);
		$pdf->Cell(200, 6, $orgaoexp_representante_legal, 1, 1, "L", 0);
		$pdf->Cell(80, 6, " UF da Identidade ", 1, 0, "L", 1);
		$pdf->Cell(200, 6, $uf_representante_legal, 1, 1, "L", 0);
		$pdf->Cell(80, 6, " Cidade de Domicílio ", 1, 0, "L", 1);
		$pdf->Cell(200, 6, $cidade_representante_legal, 1, 1, "L", 0);
		$pdf->Cell(80, 6, " Estado de Domicílio ", 1, 0, "L", 1);
		$pdf->Cell(200, 6, $estado_representante_legal, 1, 1, "L", 0);
		$pdf->Cell(80, 6, " Nacionalidade ", 1, 0, "L", 1);
		$pdf->Cell(200, 6, $nacionalidade_representante_legal, 1, 1, "L", 0);
		$pdf->Cell(80, 6, " Estado Civil ", 1, 0, "L", 1);
		$pdf->Cell(200, 6, $estado_civil_representante_legal, 1, 1, "L", 0);
		$pdf->Cell(80, 6, " Profissão ", 1, 0, "L", 1);
		$pdf->Cell(200, 6, $profissao_representante_legal, 1, 1, "L", 0);
		$pdf->Cell(80, 6, " E-mail ", 1, 0, "L", 1);
		$pdf->Cell(200, 6, $email_representante_legal, 1, 1, "L", 0);
		$pdf->Cell(80, 6, " Telefone (s) ", 1, 0, "L", 1);
		$pdf->Cell(200, 6, $tel_representante_legal, 1, 1, "L", 0);

		$pdf->Cell(280, 5, " ", 0, 1, "C", 0);
		$pdf->Cell(280, 5, " GESTOR ", 1, 1, "C", 1);
		
		$pdf->Cell(80, 6, " Nome ", 1, 0, "L", 1);
		$pdf->Cell(200, 6, $nome_gestor, 1, 1, "L", 0);
		$pdf->Cell(80, 6, " Matrícula ", 1, 0, "L", 1);
		$pdf->Cell(200, 6, $matricula_gestor, 1, 1, "L", 0);
		$pdf->Cell(80, 6, " CPF ", 1, 0, "L", 1);
		$pdf->Cell(200, 6, $cpf_gestor, 1, 1, "L", 0);
		$pdf->Cell(80, 6, " E-mail ", 1, 0, "L", 1);
		$pdf->Cell(200, 6, $email_gestor, 1, 1, "L", 0);
		$pdf->Cell(80, 6, " Telefone (s) ", 1, 0, "L", 1);
		$pdf->Cell(200, 6, $tel_gestor, 1, 1, "L", 0);

		$pdf->Cell(280, 5, " ", 0, 1, "C", 0);
		$pdf->Cell(280, 5, " FISCAL ", 1, 1, "C", 1);

		$pdf->SetFont("Arial", "B", 7.5);

		$x = $pdf->GetX() + 20;
		$y = $pdf->GetY();
		$pdf->MultiCell(20, 6, " TIPO FISCAL ", 1, "C", 1);
		$pdf->SetXY($x, $y);

		$x = $pdf->GetX() + 54;
		$y = $pdf->GetY();
		$pdf->MultiCell(54, 6, " NOME ", 1, "C", 1);
		$pdf->SetXY($x, $y);

		$x = $pdf->GetX() + 25;
		$y = $pdf->GetY();
		$pdf->MultiCell(25, 6, " MATRÍCULA ", 1, "C", 1);
		$pdf->SetXY($x, $y);

		$x = $pdf->GetX() + 30;
		$y = $pdf->GetY();
		$pdf->MultiCell(30, 6, " CPF ", 1, "C", 1);
		$pdf->SetXY($x, $y);

		$x = $pdf->GetX() + 50;
		$y = $pdf->GetY();
		$pdf->MultiCell(50, 6, "  ENT. COMPET. ", 1, "C", 1);
		$pdf->SetXY($x, $y);

		$x = $pdf->GetX() + 33;
		$y = $pdf->GetY();
		$pdf->MultiCell(33, 6, " REGISTRO OU INSC. ", 1, "C", 1);
		$pdf->SetXY($x, $y);
		
		$x = $pdf->GetX() + 50;
		$y = $pdf->GetY();
		$pdf->MultiCell(50, 6, " E-MAIL ", 1, "C", 1);
		$pdf->SetXY($x, $y);
		
		$x = $pdf->GetX() + 18;
		$y = $pdf->GetY();
		$pdf->MultiCell(18, 6, " TELEFONE ", 1, "C", 1);
		$pdf->SetXY($x, $y);
		$pdf->Ln();
		$pdf->SetFont("Arial", "B", 7);

		if(!empty($fiscaisAlterado)){
			foreach ($fiscaisAlterado as $fiscal) {
				//    var_dump(strlen($fiscal->fiscalnome));
	
				if(strlen($fiscal->ent) >= 56){
	
					$altura_linha = 13;
					// $altura_linha_nome = 8.8;
				}else if(strlen($fiscal->ent) >= 30 && strlen($fiscal->fiscalnome) >= 35){
	
					$altura_linha = 19.5;
					$altura_linha_nome = 9.8;
				}else if(strlen($fiscal->ent) >= 29 && strlen($fiscal->fiscalnome) >= 26){
					$altura_linha = 13.1;
					$altura_linha_nome = 13;
				}else{
					$altura_linha = 6.5;
					$altura_linha_nome = 6.5;
				}
			
				$x = $pdf->GetX() + 20;
				$y = $pdf->GetY();
				$pdf->MultiCell(20, $altura_linha, strtoupper($fiscal->tipofiscal), 1, "C", 0);
				$pdf->SetXY($x, $y);
	
				$x = $pdf->GetX() + 54;
				$y = $pdf->GetY();
				$pdf->MultiCell(54, $altura_linha_nome, strtoupper($fiscal->fiscalnome), 1, "C", 0);
				$pdf->SetXY($x, $y);
				
				$x = $pdf->GetX() + 25;
				$y = $pdf->GetY();
				$pdf->MultiCell(25, $altura_linha, $fiscal->fiscalmatricula, 1, "C", 0);
				$pdf->SetXY($x, $y);
				
				$x = $pdf->GetX() + 30;
				$y = $pdf->GetY();
				$pdf->MultiCell(30, $altura_linha, MascarasCPFCNPJ($fiscal->fiscalcpf), 1, "C", 0);
				$pdf->SetXY($x, $y);
	
				$x = $pdf->GetX() + 50;
				$y = $pdf->GetY();
				$pdf->MultiCell(50, 6.5, strtoupper($fiscal->ent), 1, "C", 0);
				$pdf->SetXY($x, $y);
	
				$x = $pdf->GetX() + 33;
				$y = $pdf->GetY();
				$pdf->MultiCell(33, $altura_linha, strtoupper($fiscal->registro), 1, "C", 0);
				$pdf->SetXY($x, $y);
				
				// $pdf->SetFont("Arial", "B", 6.5);
				$x = $pdf->GetX() + 50;
				$y = $pdf->GetY();
				$pdf->MultiCell(50, $altura_linha, $fiscal->fiscalemail, 1, "C", 0);
				$pdf->SetXY($x, $y);
				
				$pdf->SetFont("Arial", "B", 7);
				$x = $pdf->GetX() + 18;
				$y = $pdf->GetY();
				$pdf->MultiCell(18, $altura_linha, $fiscal->fiscaltel, 1, "C", 0);
				$pdf->SetXY($x, $y);
				$pdf->Ln();
			}
		}else{
			
			foreach ($fiscais as $fiscal) {
		
				if(strlen($fiscal->ent) >= 56){
	
					$altura_linha = 13;
				}else if(strlen($fiscal->ent) >= 30 && strlen($fiscal->fiscalnome) >= 35){
	
					$altura_linha = 19.5;
					$altura_linha_nome = 9.8;
				}else if(strlen($fiscal->ent) >= 29 && strlen($fiscal->fiscalnome) >= 26){
					$altura_linha = 13.1;
					$altura_linha_nome = 13;
				}else{
					$altura_linha = 6.5;
					$altura_linha_nome = 6.5;
				}
			
				$x = $pdf->GetX() + 20;
				$y = $pdf->GetY();
				$pdf->MultiCell(20, $altura_linha, strtoupper($fiscal->tipofiscal), 1, "C", 0);
				$pdf->SetXY($x, $y);
	
				$x = $pdf->GetX() + 54;
				$y = $pdf->GetY();
				$pdf->MultiCell(54, $altura_linha_nome, strtoupper($fiscal->fiscalnome), 1, "C", 0);
				$pdf->SetXY($x, $y);
				
				$x = $pdf->GetX() + 25;
				$y = $pdf->GetY();
				$pdf->MultiCell(25, $altura_linha, $fiscal->fiscalmatricula, 1, "C", 0);
				$pdf->SetXY($x, $y);
				
				$x = $pdf->GetX() + 30;
				$y = $pdf->GetY();
				$pdf->MultiCell(30, $altura_linha, MascarasCPFCNPJ($fiscal->fiscalcpf), 1, "C", 0);
				$pdf->SetXY($x, $y);
	
				$x = $pdf->GetX() + 50;
				$y = $pdf->GetY();
				$pdf->MultiCell(50, 6.5, strtoupper($fiscal->ent), 1, "C", 0);
				$pdf->SetXY($x, $y);
	
				$x = $pdf->GetX() + 33;
				$y = $pdf->GetY();
				$pdf->MultiCell(33, $altura_linha, strtoupper($fiscal->registro), 1, "C", 0);
				$pdf->SetXY($x, $y);
				
				// $pdf->SetFont("Arial", "B", 6.5);
				$x = $pdf->GetX() + 50;
				$y = $pdf->GetY();
				$pdf->MultiCell(50, $altura_linha, $fiscal->fiscalemail, 1, "C", 0);
				$pdf->SetXY($x, $y);
				
				$pdf->SetFont("Arial", "B", 7);
				$x = $pdf->GetX() + 18;
				$y = $pdf->GetY();
				$pdf->MultiCell(18, $altura_linha, $fiscal->fiscaltel, 1, "C", 0);
				$pdf->SetXY($x, $y);
				$pdf->Ln();
			}
		}

		$pdf->SetFont("Arial", "B", 8);
		$pdf->Cell(80, 6, " Situação do contrato ", 1, 0, "L", 1);
		$pdf->Cell(200, 6, $situacaoContrato->esitdcdesc, 1, 1, "L", 0);

		$pdf->SetFont("Arial", "B", 8.5);
		$pdf->Cell(280, 5, " ARQUIVOS ANEXOS ", 1, 1, "C", 1);
		
		foreach ($DadosDocAnexo as $documento) {
			$pdf->Cell(280, 6, $documento->nome_arquivo, 1, 1, "L", 0);	
		}

		$url = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
		
		$parametros = "idregistro=".$retorno->seqdocumento."&internet=true";
		
		if($resumidoCompleto == 'completo'){
			$pdf->Ln();
			$y = $pdf->GetY();
			if (($y + $height_of_cell) >= 147) {
				$pdf->AddPage();
			}
			
			$pdf->SetFont("Arial", "B", 8.5);
			$pdf->Cell(280, 5, " ITENS DO CONTRATO ", 1, 1, "C", 1);
			$pdf->SetFont("Arial", "B", 7.5);

			$x = $pdf->GetX() + 20;
			$y = $pdf->GetY();
			$pdf->MultiCell(20, 6, " ORD ", 1, "C", 1);
			$pdf->SetXY($x, $y);

			$x = $pdf->GetX() + 110;
			$y = $pdf->GetY();
			$pdf->MultiCell(110, 6, " DESC ITEM ", 1, "C", 1);
			$pdf->SetXY($x, $y);

			$x = $pdf->GetX() + 30;
			$y = $pdf->GetY();
			$pdf->MultiCell(30, 6, " COD REDUZIDO ", 1, "C", 1);
			$pdf->SetXY($x, $y);

			$x = $pdf->GetX() + 15;
			$y = $pdf->GetY();
			$pdf->MultiCell(15, 6, " UND ", 1, "C", 1);
			$pdf->SetXY($x, $y);

			$x = $pdf->GetX() + 20;
			$y = $pdf->GetY();
			$pdf->MultiCell(20, 6, "  QTD. ", 1, "C", 1);
			$pdf->SetXY($x, $y);

			$x = $pdf->GetX() + 33;
			$y = $pdf->GetY();
			$pdf->MultiCell(33, 6, " VALOR UNITÁRIO ", 1, "C", 1);
			$pdf->SetXY($x, $y);
			
			$x = $pdf->GetX() + 30;
			$y = $pdf->GetY();
			$pdf->MultiCell(30, 6, " VALOR TOTAL ", 1, "C", 1);
			$pdf->SetXY($x, $y);
			
			$x = $pdf->GetX() + 22;
			$y = $pdf->GetY();
			$pdf->MultiCell(22, 6, " TIPO ", 1, "C", 1);
			$pdf->SetXY($x, $y);
			$pdf->Ln();			
			
			foreach($dadosItensContrato as $itens){

				$codeMaterial = !empty($itens->codreduzidomat)?$itens->codreduzidomat:$itens->codreduzidoserv;
				$tipoGrupo = !empty($itens->codreduzidomat)?'M':"S";
				$ordem = $itens->ord;
				if(!empty($itens->codreduzidomat)){
					$descricaoMaterial = $objFuncoes->GetDescricaoMaterial($itens->codreduzidomat);
					$descricao_mat_item = $descricaoMaterial[0]->ematepdesc;
				}
				if(!empty($itens->codreduzidoserv)){
					$descricaoServico = $objFuncoes->GetDescricaoServicos($itens->codreduzidoserv);
					$descricao_mat_item = $descricaoServico[0]->eservpdesc;
				}

				if(strlen($descricao_mat_item) >= 80){
					$altura_linha = 9;
				}else{
					$altura_linha = 4.5;
				}

				$cod_reduzido = !empty($itens->codreduzidoserv) ? $itens->codreduzidoserv : $itens->codreduzidomat;
				$cod_reduzido_und = !empty($itens->codreduzidoserv) ? "" : 'und';
				$qtd_itens = number_format($itens->qtd,4,',','.');
				$valor_unit = 'R$ '. number_format(floatval($itens->valorunitario), 4, ',', '.');
				$valor = 'R$ '. number_format(floatval($itens->qtd) * floatval($itens->valorunitario), 4, ',', '.');
				$valor_total_soma += floatval($itens->qtd) * floatval($itens->valorunitario);
				$valorFullTotal = 'R$ '. number_format($valor_total_soma, 4, ',', '.');
				
				$valor_item = 'R$ '. number_format(floatval($valor), 4, ',', '.');
				$marca = $itens->marca;
				$modelo = $itens->modelo;
				$cod_serv_reduzido = !empty($itens->codreduzidoserv) ? "Serviço" : "Material";
				$valor_total_itens = 'R$ '. number_format(floatval($valorFullTotal), 4, ',', '.');
				
				$x = $pdf->GetX() + 20;
				$y = $pdf->GetY();
				$pdf->MultiCell(20, $altura_linha, $ordem, 1, "C", 0);
				$pdf->SetXY($x, $y);

				$pdf->SetFont("Arial", "B", 7);
				$x = $pdf->GetX() + 110;
				$y = $pdf->GetY();
				$pdf->MultiCell(110, 4.5, strtoupper($descricao_mat_item), 1, "C", 0);
				$pdf->SetXY($x, $y);
				
				$x = $pdf->GetX() + 30;
				$y = $pdf->GetY();
				$pdf->MultiCell(30, $altura_linha, $cod_reduzido, 1, "C", 0);
				$pdf->SetXY($x, $y);
				
				$x = $pdf->GetX() + 15;
				$y = $pdf->GetY();
				$pdf->MultiCell(15, $altura_linha, strtoupper($cod_reduzido_und), 1, "C", 0);
				$pdf->SetXY($x, $y);

				$x = $pdf->GetX() + 20;
				$y = $pdf->GetY();
				$pdf->MultiCell(20, $altura_linha, strtoupper($qtd_itens), 1, "C", 0);
				$pdf->SetXY($x, $y);

				$x = $pdf->GetX() + 33;
				$y = $pdf->GetY();
				$pdf->MultiCell(33, $altura_linha, $valor_unit, 1, "C", 0);
				$pdf->SetXY($x, $y);
				
				$x = $pdf->GetX() + 30;
				$y = $pdf->GetY();
				$pdf->MultiCell(30, $altura_linha, $valor, 1, "C", 0);
				$pdf->SetXY($x, $y);
				
				$pdf->SetFont("Arial", "B", 7.5);
				$x = $pdf->GetX() + 22;
				$y = $pdf->GetY();
				$pdf->MultiCell(22, $altura_linha, $cod_serv_reduzido, 1, "C", 0);
				$pdf->SetXY($x, $y);
				$pdf->Ln();
				for($i = 0; $i <= 126; $i++){
					$block=floor($i/6);
				
					$space_left = $page_height - ($pdf->GetY() + $bottom_margin); // Espaço a esquerda
					
					if ($i/6 == floor($i/6) && $height_of_cell > $space_left) {
		
						$pdf->AddPage(); // page break
					}
				}
			}
			
			$pdf->Cell(245, 6, " VALOR TOTAL DA SOLICITAÇÃO ", 1, 0, "L", 1);
			$pdf->Cell(35, 6, $valorFullTotal, 1, 1, "L", 0);
		
		$validaAditivo = false;
		for($i=0;$i<count($aditivos_contrato);$i++){
			if($aditivos_contrato[$i]->esitdcdesc == "EM EXECUÇÃO"){
				$validaAditivo = true;
			}
		}
		if($validaAditivo == true){
			$pdf->Ln(10);
			$pdf->SetFont("Arial", "B", 8.5);
			$pdf->Cell(280, 5, " ADITIVOS DO CONTRATO ", 1, 1, "C", 1);
			foreach ($aditivos_contrato as $key => $adt_contrato) {
				if($adt_contrato->esitdcdesc == "EM EXECUÇÃO"){
					$aditivo = $objFuncoes->getAditivoContrato($adt_contrato->cdocpcseq1, $adt_contrato->cdocpcsequ);
					$situacaoAditivo = $objFuncoes->situacaoAditivo();

					$justificativa = strtoupper($aditivo->xaditijust);
					$registro_base = $aditivo->aaditinuad;
					$situacao = $adt_contrato->esitdcdesc; //$aditivo[0]->esitdcdesc;
					
					$data_inicio_vigencia = $aditivo->daditiinvg;
					if(EMPTY($data_inicio_vigencia)){
						$data_inicio_vigencia = "";
					}
					
					$data_fim_vigencia = $aditivo->daditifivg;
					$data_fim = $aditivo->data_fim;
					$data_inicio_execucao = $aditivo->daditiinex;
					
					$valor_aditivo = $aditivo->vaditivalr;
					$prazo = $aditivo->aaditiapea;
					$tipo_aditivo = $aditivo->ctpadisequ;

					$tipoAditivoDesc = $objFuncoes->getTiposAditivoPorCod($tipo_aditivo);
					$desc_tipo_aditivo = $tipoAditivoDesc[0]->etpadidesc;
					
					$se_alteracao_prazo = $aditivo->faditialpz;
					$se_alteracao_valor = $aditivo->faditialvl;
					$tipo_alteracao_valor = $aditivo->cadititalv;

					if($tipo_alteracao_valor == "RP"){
						$tipo_alteracao_valor_desc =  strtoupper("REPACTUAÇÃO");
					}else if($tipo_alteracao_valor == "RQ"){
							$tipo_alteracao_valor_desc =  strtoupper("REEQUILÍBRIO");
					}else if($tipo_alteracao_valor == "QT"){
							$tipo_alteracao_valor_desc =  strtoupper("QUANTITATIVO");
					}else if($tipo_alteracao_valor == "QRP"){
							$tipo_alteracao_valor_desc =  strtoupper("QUANTITATIVO COM REPACTUAÇÃO");
					
					}else{
							$tipo_alteracao_valor_desc =  strtoupper("QUANTITATIVO COM REEQUILÍBRIO");
					}
					
					
					$valor_retroativo = $aditivo->vaditireqc;
					$valor_total_aditivo = $aditivo->vaditivtad;
					
					$observacao = $aditivo->xaditiobse;
					$nome_representante = $aditivo->naditinmrl;
					
					$cargo_representante = $aditivo->eaditicgrl;
					$cpf_representante = $aditivo->eaditicpfr;
					$rg_representante = $aditivo->eaditiidrl;
					$orgao_exp_representante = $aditivo->naditioerl;
					$uf_nat_representante = $aditivo->naditiufrl;
					$cidade_representante = $aditivo->naditicdrl;
					$estado_representante = $aditivo->naditiedrl;
					$nacionalidade_representante = $aditivo->naditinarl;
					$estado_civil_representante = $aditivo->caditiecrl;
					$profissao_representante = $aditivo->naditiprrl;
					$email_representante = $aditivo->naditimlrl;
					$telefone_representante = $aditivo->eadititlrl;
					$data_fim_execucao = $aditivo->data_fim;
					$fase = $aditivo->fase;

					$data_ini_vigencia = DataBarra($adt_contrato->daditiinvg); // data ini vigência
					$data_fim_vigencia_aditivo = DataBarra($adt_contrato->daditifivg);
					$data_ini_exec = DataBarra($adt_contrato->daditiinex);
					//data fim de execução do aditivo
					$data = explode("/",  $adt_contrato->data_fim);
					$data_fim_exec = $data[0]."-".$data[1]."-".$data[2];
					$data_fim_exec = str_replace('--','',$data_fim_exec);
					$dataFimExec = new DateTime($data_fim_exec);
					$dataFimExec->modify('-1 day');
					$data_fim_exec = $dataFimExec->format('d/m/Y');
					$data_fim_exec_aditivo = $data_fim_exec;

					$valor_aditivo = 'R$ '. number_format(floatval($valor_total_aditivo), 4, ',', '.'); // valor
					$acrescimo_prazo_aditivo = $adt_contrato->aaditiapea; // acréscimo de prazo
					$valor_retroativo_aditivo = 'R$ '. number_format(floatval($valor_retroativo), 4, ',', '.'); // valor retroativo
					$valor_total_aditivo = 'R$ '. number_format(floatval($adt_contrato->vaditivtad), 4, ',', '.'); // valor total aditivo
					$pdf->Ln(4);
					$pdf->SetFont("Arial", "B", 7.5);
					$pdf->Cell(280, 5, " ADITIVO Nº $registro_base", 1, 1, "C", 1);
					// $pdf->Cell(80, 6, " Código do Aditivo ", 1, 0, "L", 1);
					// $pdf->Cell(200, 6, $cod_aditivo, 1, 1, "L", 0);
					$pdf->Cell(80, 6, " Número do Aditivo ", 1, 0, "L", 1);
					$pdf->Cell(200, 6, $registro_base, 1, 1, "L", 0);
					$pdf->Cell(80, 6, " Tipo do Aditivo ", 1, 0, "L", 1);
					$pdf->Cell(200, 6, $desc_tipo_aditivo, 1, 1, "L", 0);
					$pdf->Cell(80, 6, " Situação do Aditivo ", 1, 0, "L", 1);
					$pdf->Cell(200, 6, $situacao, 1, 1, "L", 0);
					$pdf->Cell(80, 6, " Justificativa do Aditivo ", 1, 0, "L", 1);
					$pdf->Cell(200, 6, $justificativa, 1, 1, "L", 0);
					$pdf->Cell(80, 6, " Há Alteração de Prazo ", 1, 0, "L", 1);
					$pdf->Cell(200, 6, $se_alteracao_prazo, 1, 1, "L", 0);
					$pdf->Cell(80, 6, " Há Alteração de Valor ", 1, 0, "L", 1);
					$pdf->Cell(200, 6, $se_alteracao_valor, 1, 1, "L", 0);
					$pdf->Cell(80, 6, " Tipo de Alteração de Valor ", 1, 0, "L", 1);
					
					$pdf->Cell(200, 6, $tipo_alteracao_valor_desc, 1, 1, "L", 0);
					$pdf->Cell(80, 6, " Valor Retroativo Repactuação/Reequilíbrio ", 1, 0, "L", 1);
					$pdf->Cell(200, 6, $valor_retroativo_aditivo, 1, 1, "L", 0);
					$pdf->Cell(80, 6, " Valor Total do Aditivo ", 1, 0, "L", 1);
					$pdf->Cell(200, 6, $valor_aditivo, 1, 1, "L", 0);
					$pdf->Cell(80, 6, " Acréscimo de Prazo ", 1, 0, "L", 1);
					$pdf->Cell(200, 6, $acrescimo_prazo_aditivo, 1, 1, "L", 0);


					if($tipo_aditivo == 13){
						if(!empty($aditivo->aforcrsequ)){

							$dadosFornecedor = $objFuncoes->GetFornecedorByCod($aditivo->aforcrsequ);

							$cpf_cnpj = !empty($dadosFornecedor->aforcrccpf)?$dadosFornecedor->aforcrccpf:$dadosFornecedor->aforcrccgc;
							if(strlen($cpf_cnpj) == 11){
								$cpf_cnpjprefix = 'CPF';
							}else{
								$cpf_cnpjprefix = 'CNPJ';
							}
							$cpf_cnpj = $objFuncoes->MascarasCPFCNPJ($cpf_cnpj);
							$razao = !is_null($dadosFornecedor->nforcrrazs)? $dadosFornecedor->nforcrrazs:''; 
							$logradouro = !is_null($dadosFornecedor->eforcrlogr)? $dadosFornecedor->eforcrlogr:''; 
							$complemento = !is_null($dadosFornecedor->eforcrcomp)? $dadosFornecedor->eforcrcomp:''; 
							$bairro = !is_null($dadosFornecedor->eforcrbair)? $dadosFornecedor->eforcrbair:''; 
							$cidade = !is_null($dadosFornecedor->nforcrcida)? $dadosFornecedor->nforcrcida:''; 
							$uf = !is_null($dadosFornecedor->cforcresta)? $dadosFornecedor->cforcresta:''; 

							$pdf->Cell(280, 6, " CONTRATADO ", 1, 0, "C", 1);
							$pdf->Ln();
							$pdf->Cell(80, 6,  $cpf_cnpjprefix." DO CONTRATADO ", 1, 0, "L", 1);
							$pdf->Cell(200, 6, $cpf_cnpj, 1, 1, "L", 0);
							$pdf->Cell(80, 6, " RAZÃO SOCIAL ", 1, 0, "L", 1);
							$pdf->Cell(200, 6, $razao, 1, 1, "L", 0);
							$pdf->Cell(80, 6, " LOGRADOURO ", 1, 0, "L", 1);
							$pdf->Cell(200, 6, $logradouro, 1, 1, "L", 0);
							$pdf->Cell(80, 6, " COMPLEMENTO ", 1, 0, "L", 1);
							$pdf->Cell(200, 6, $complemento, 1, 1, "L", 0);
							$pdf->Cell(80, 6, " BAIRRO ", 1, 0, "L", 1);
							$pdf->Cell(200, 6, $bairro, 1, 1, "L", 0);
							$pdf->Cell(80, 6, " CIDADE ", 1, 0, "L", 1);
							$pdf->Cell(200, 6, $cidade, 1, 1, "L", 0);
							$pdf->Cell(80, 6, " UF ", 1, 0, "L", 1);
							$pdf->Cell(200, 6, $uf, 1, 1, "L", 0);
						}
					}
					
					if($consorcio == 'SIM'){
						$pdf->Ln(2);
					}else{
						$pdf->Ln(6);
					}
					
					$pdf->Cell(280, 5, " PERÍODOS ", 1, 1, "C", 1);
					$x = $pdf->GetX() + 80;
					$y = $pdf->GetY();
					$pdf->MultiCell(80, 6, "  ", 1, "C", 1);
					$pdf->SetXY($x, $y);

					$x = $pdf->GetX() + 100;
					$y = $pdf->GetY();
					$pdf->MultiCell(100, 5, " DATA DE INÍCIO ", 1, "C", 1);
					$pdf->SetXY($x, $y);

					$x = $pdf->GetX() + 100;
					$y = $pdf->GetY();
					$pdf->MultiCell(100, 5, " DATA DE TÉRMINO ", 1, "C", 1);
					$pdf->SetXY($x, $y);
					$pdf->Ln();

					$x = $pdf->GetX() + 80;
					$y = $pdf->GetY();
					$pdf->MultiCell(80, 5, " Vigência ", 1, "C", 1);
					$pdf->SetXY($x, $y);
			
					$x = $pdf->GetX() + 100;
					$y = $pdf->GetY();
					$pdf->MultiCell(100, 5, $data_ini_vigencia, 1, "C", 0);
					$pdf->SetXY($x, $y);
			
					$x = $pdf->GetX() + 100;
					$y = $pdf->GetY();
					$pdf->MultiCell(100, 5, $data_fim_vigencia_aditivo, 1, "C", 0);
					$pdf->SetXY($x, $y);
					$pdf->Ln();
			
					$x = $pdf->GetX() + 80;
					$y = $pdf->GetY();
					$pdf->MultiCell(80, 5, " Execução ", 1, "C", 1);
					$pdf->SetXY($x, $y);
			
					$x = $pdf->GetX() + 100;
					$y = $pdf->GetY();
					$pdf->MultiCell(100, 5, $data_ini_exec, 1, "C", 0);
					$pdf->SetXY($x, $y);
					
					$x = $pdf->GetX() + 100;
					$y = $pdf->GetY();
					$pdf->MultiCell(100, 5, $data_fim_exec_aditivo, 1, "C", 0);
					$pdf->SetXY($x, $y);
					$pdf->Ln();
					$pdf->Cell(80, 6, " Observação ", 1, 0, "L", 1);
					$pdf->Cell(200, 6, $observacao, 1, 1, "L", 0);
					$pdf->Ln();

					$pdf->SetFont("Arial", "B", 7.5);
					$pdf->Cell(280, 5, " REPRESENTANTE LEGAL", 1, 1, "C", 1);
					$pdf->Cell(80, 6, " Nome ", 1, 0, "L", 1);
					$pdf->Cell(200, 6, $nome_representante, 1, 1, "L", 0);
					$pdf->Cell(80, 6, " CPF ", 1, 0, "L", 1);
					$pdf->Cell(200, 6, $cpf_representante, 1, 1, "L", 0);
					$pdf->Cell(80, 6, " Cargo ", 1, 0, "L", 1);
					$pdf->Cell(200, 6, $cargo_representante, 1, 1, "L", 0);
					$pdf->Cell(80, 6, " Identidade ", 1, 0, "L", 1);
					$pdf->Cell(200, 6, $rg_representante, 1, 1, "L", 0);
					$pdf->Cell(80, 6, " Orgão Emissor ", 1, 0, "L", 1);
					$pdf->Cell(200, 6, $orgao_exp_representante, 1, 1, "L", 0);
					$pdf->Cell(80, 6, " UF da Identidade ", 1, 0, "L", 1);
					$pdf->Cell(200, 6, $uf_nat_representante, 1, 1, "L", 0);
					$pdf->Cell(80, 6, " Cidade de Domicílio ", 1, 0, "L", 1);
					$pdf->Cell(200, 6, $cidade_representante, 1, 1, "L", 0);
					$pdf->Cell(80, 6, " Nacionalidade ", 1, 0, "L", 1);
					$pdf->Cell(200, 6, $nacionalidade_representante, 1, 1, "L", 0);
					$pdf->Cell(80, 6, " Estado Civil ", 1, 0, "L", 1);
					$pdf->Cell(200, 6, $estado_civil_representante, 1, 1, "L", 0);
					$pdf->Cell(80, 6, " Profissão ", 1, 0, "L", 1);
					$pdf->Cell(200, 6, $profissao_representante, 1, 1, "L", 0);
					$pdf->Cell(80, 6, " E-mail ", 1, 0, "L", 1);
					$pdf->Cell(200, 6, $email_representante, 1, 1, "L", 0);
					$pdf->Cell(80, 6, " Telefone (s) ", 1, 0, "L", 1);
					$pdf->Cell(200, 6, $telefone_representante, 1, 1, "L", 0);
					// $pdf->Ln();
					$pdf->Cell(280, 5, " ", 0, 1, "C", 0);

					
					$y = $pdf->GetY();
					if (($y + $height_of_cell) >= 147) {
						$pdf->AddPage();
					}

				}
			}

		}
		
		$validaApostilamento = false;
		for($i=0;$i<count($apostilamentos_contrato);$i++){
			if($apostilamentos_contrato[$i]->situacao == "IMPLANTADO"){
				$validaApostilamento = true;
			}
		}

		if(!empty($validaApostilamento)){
			// $pdf->Ln();
			$pdf->SetFont("Arial", "B", 8.5);
			$pdf->Cell(280, 5, " APOSTILAMENTOS DO CONTRATO ", 1, 1, "C", 1);
			foreach ($apostilamentos_contrato as $key => $apost) {
				
				$apostilamento = $objFuncoes->getApostilamentoRCCPDF($apost->cdocpcsequ);
				$situacao = $apost->situacao;

					$num_tipo_apostilamento = $apostilamento[0]->ctpaposequ;
					$tipo_apostilamento = $objFuncoes->getApostilamentoNome($num_tipo_apostilamento);
					$tipo = $tipo_apostilamento[0]->etpapodesc;

					$cdocpcseq2 = $apostilamento[0]->cdocpcseq2;
					$data_apostilamento = $apostilamento[0]->dapostcada;
					$valor_apostilamento = 'R$ '. number_format(floatval($apostilamento[0]->vapostvtap), 4, ',', '.');
					$valor_retro_apostilamento = 'R$ '. number_format(floatval($apostilamento[0]->vapostretr), 4, ',', '.');
					$num_apostilamento = $apostilamento[0]->aapostnuap;
				

				$nome_gestor = $apostilamento[0]->napostnmgt;
				$cpf_gestor = $apostilamento[0]->napostcpfg;
				$tel_gestor = $apostilamento[0]->eaposttlgt;
				$mat_gestor = $apostilamento[0]->napostmtgt;
				$email_gestor = $apostilamento[0]->napostmlgt;
				$cpf_gestor = $apostilamento[0]->napostcpfg;
				$cpf_gestor = $apostilamento[0]->napostcpfg;

				if($situacao == "IMPLANTADO"){
					$pdf->SetFont("Arial", "B", 7.5);
					$pdf->Cell(280, 5, " APOSTILAMENTO Nº $num_apostilamento", 1, 1, "C", 1);
					
					// $pdf->Cell(80, 6, " Nº do Contrato/Ano ", 1, 0, "L", 1);
					// $pdf->Cell(200, 6, $ectrpcnumf, 1, 1, "L", 0);

					$pdf->Cell(80, 6, " Tipo de Apostilamento ", 1, 0, "L", 1);
					$pdf->Cell(200, 6, $tipo, 1, 1, "L", 0);

					$pdf->Cell(80, 6, " Valor Retroativo Apostilamento ", 1, 0, "L", 1);
					$pdf->Cell(200, 6, $valor_retro_apostilamento, 1, 1, "L", 0);
					$pdf->Cell(80, 6, " Valor do Apostilamento ", 1, 0, "L", 1);
					$pdf->Cell(200, 6, $valor_apostilamento, 1, 1, "L", 0);
					$pdf->Cell(80, 6, " Data do Apostilamento ", 1, 0, "L", 1);
					$pdf->Cell(200, 6, DataBarra($data_apostilamento), 1, 1, "L", 0);
					$pdf->Cell(80, 6, " Situação ", 1, 0, "L", 1);
					$pdf->Cell(200, 6, $situacao, 1, 1, "L", 0);

					if($num_tipo_apostilamento == 5){

						

						if(!empty($apost->aforcrsequ)){
							$dadosFornecedor = $objFuncoes->GetFornecedorByCod($apost->aforcrsequ);
							$cpf_cnpj = !empty($dadosFornecedor->aforcrccpf)?$dadosFornecedor->aforcrccpf:$dadosFornecedor->aforcrccgc;
							if(strlen($cpf_cnpj) == 11){
								$cpf_cnpjprefix = 'CPF';
							}else{
								$cpf_cnpjprefix = 'CNPJ';
							}
							$cpf_cnpj = $objFuncoes->MascarasCPFCNPJ($cpf_cnpj);
							$razao = !is_null($dadosFornecedor->nforcrrazs)? $dadosFornecedor->nforcrrazs:''; 
							$logradouro = !is_null($dadosFornecedor->eforcrlogr)? $dadosFornecedor->eforcrlogr:''; 
							$complemento = !is_null($dadosFornecedor->eforcrcomp)? $dadosFornecedor->eforcrcomp:''; 
							$bairro = !is_null($dadosFornecedor->eforcrbair)? $dadosFornecedor->eforcrbair:''; 
							$cidade = !is_null($dadosFornecedor->nforcrcida)? $dadosFornecedor->nforcrcida:''; 
							$uf = !is_null($dadosFornecedor->cforcresta)? $dadosFornecedor->cforcresta:''; 
					}

						$pdf->Cell(280, 6, " CONTRATADO ", 1, 0, "C", 1);
						$pdf->Ln();
						$pdf->Cell(80, 6,  $cpf_cnpjprefix." DO CONTRATADO ", 1, 0, "L", 1);
						$pdf->Cell(200, 6, $cpf_cnpj, 1, 1, "L", 0);
						$pdf->Cell(80, 6, " RAZÃO SOCIAL ", 1, 0, "L", 1);
						$pdf->Cell(200, 6, $razao, 1, 1, "L", 0);
						$pdf->Cell(80, 6, " LOGRADOURO ", 1, 0, "L", 1);
						$pdf->Cell(200, 6, $logradouro, 1, 1, "L", 0);
						$pdf->Cell(80, 6, " COMPLEMENTO ", 1, 0, "L", 1);
						$pdf->Cell(200, 6, $complemento, 1, 1, "L", 0);
						$pdf->Cell(80, 6, " BAIRRO ", 1, 0, "L", 1);
						$pdf->Cell(200, 6, $bairro, 1, 1, "L", 0);
						$pdf->Cell(80, 6, " CIDADE ", 1, 0, "L", 1);
						$pdf->Cell(200, 6, $cidade, 1, 1, "L", 0);
						$pdf->Cell(80, 6, " UF ", 1, 0, "L", 1);
						$pdf->Cell(200, 6, $uf, 1, 1, "L", 0);
					}

					$pdf->Cell(280, 6, " GESTOR ", 1, 0, "C", 1);
					$pdf->Ln();
					$pdf->Cell(80, 6, " Nome ", 1, 0, "L", 1);
					$pdf->Cell(200, 6, $nome_gestor, 1, 1, "L", 0);
					$pdf->Cell(80, 6, " Matrícula ", 1, 0, "L", 1);
					$pdf->Cell(200, 6, $mat_gestor, 1, 1, "L", 0);
					$pdf->Cell(80, 6, " CPF ", 1, 0, "L", 1);
					$pdf->Cell(200, 6, $cpf_gestor, 1, 1, "L", 0);
					$pdf->Cell(80, 6, " E-mail ", 1, 0, "L", 1);
					$pdf->Cell(200, 6, $email_gestor, 1, 1, "L", 0);
					$pdf->Cell(80, 6, " Telefone ", 1, 0, "L", 1);
					$pdf->Cell(200, 6, $tel_gestor, 1, 1, "L", 0);

				
					$pdf->Ln();
					$y = $pdf->GetY();
					
					if (($y + $height_of_cell) >= 147) {
						$pdf->AddPage();
					}
				}

			}
		}
		
			if(!empty($medicoes_contrato)){
				$y = $pdf->GetY();
			
				if (($y + $height_of_cell) >= 147) {
					$pdf->AddPage();
				}
				// $pdf->Ln();
				$pdf->SetFont("Arial", "B", 8.5);
				$pdf->Cell(280, 5, " MEDIÇÕES DO CONTRATO ", 1, 1, "C", 1);
				
				foreach ($medicoes_contrato as $key => $medicao) {
					$numero_medicao = $medicao->amedconume;
					$seq_medicao = $medicao->cmedcosequ;
					$data_ini_medicao = $medicao->dmedcoinic;
					$data_final_medicao = $medicao->dmedcofinl;
					$valor_medicao = 'R$ '. number_format(floatval($medicao->vmedcovalm), 4, ',', '.');
					// $data_pre_aprovacao_medicao = $medicao->dmedcopapr; //Data de pré-aprovação
	
					// Medição
					$registro_medicao = $objFuncoes->getMedicaoContrato($retorno->seqdocumento, $seq_medicao);
					$observacao = $registro_medicao[0]->emedcoobse;
					
					$valorContrato = $objFuncoes->getValorGlobalContrato($retorno->seqdocumento);
					$valorGlobaladt = $valores_contrato->vctrpcglaa;
					$valorContratoAntigo = $valores_contrato->vctrpcsean;
					
					$vtAditivo = $objFuncoes->GetValorTotalAdtivo($retorno->seqdocumento);
					$vtApost = $objFuncoes->GetValorTotalApostilamento($retorno->seqdocumento);
					$valorTotalMedicao = $objFuncoes->getValorTotalMedicao($retorno->seqdocumento);
	
					$pdf->Ln();
					$pdf->SetFont("Arial", "B", 7.5);
					$pdf->Cell(280, 5, " MEDIÇÃO Nº $numero_medicao", 1, 1, "C", 1);
					
					// $pdf->Cell(80, 6, " Nº do Contrato/Ano ", 1, 0, "L", 1);
					// $pdf->Cell(200, 6, $ectrpcnumf, 1, 1, "L", 0);
	
					$pdf->Cell(80, 6, " Objeto ", 1, 0, "L", 1);
					$pdf->Cell(200, 6, $objetivo, 1, 1, "L", 0);
	
					// $pdf->Cell(80, 6, " Saldo a Executar ", 1, 0, "L", 1);
					// $pdf->Cell(200, 6, $saldo_a_executar, 1, 1, "L", 0);
					$pdf->Cell(80, 6, " Número da Medição ", 1, 0, "L", 1);
					$pdf->Cell(200, 6, $numero_medicao, 1, 1, "L", 0);
					$pdf->Cell(80, 6, " Valor da Medição ", 1, 0, "L", 1);
					$pdf->Cell(200, 6, $valor_medicao, 1, 1, "L", 0);
					$pdf->Cell(80, 6, " Período da Medição ", 1, 0, "L", 1);
					$pdf->Cell(200, 6, DataBarra($data_ini_medicao) .' à ' . DataBarra($data_final_medicao), 1, 1, "L", 0);
					$pdf->Cell(80, 6, " Observação ", 1, 0, "L", 1);
					$pdf->Cell(200, 6, $observacao, 1, 1, "L", 0);
					$pdf->Ln();
					for($i = 0; $i <= 126; $i++){
						$block=floor($i/6);
					
						$space_left = $page_height - ($pdf->GetY() + $bottom_margin); // Espaço a esquerda
						
						if ($i/6 == floor($i/6) && $height_of_cell > $space_left) {
	
							$pdf->AddPage(); // page break
						}
					}
				}

			}
		}
		

		$pdf->Ln(10);
		$pdf->Cell(200, 46, $observacao, 0, 1, "L", 0);
		
		$y = $pdf->GetY();

		for($i = 0; $i <= 126; $i++){
			$block=floor($i/6);
			
			$space_left = $page_height - ($pdf->GetY() + $bottom_margin); // Espaço a esquerda
			
			if ($i/6 == floor($i/6) && $height_of_cell > $space_left) {
				
				$pdf->AddPage(); // page break
			}
		}

		if($resumidoCompleto == 'resumido'){
			$site_producao = "http://www.recife.pe.gov.br/contratos-emprel/paginaspublicas/ConsultaContratoConsolidado.jsf";
			$site_local = $url."/portalcompras/contratos/CadContratoConsolidado.php?".$parametros;
			// var_dump($site_local);
			
			QRcode::png($site_local,"QrContratoConsolidado.png");
			$pdf->Image("QrContratoConsolidado.png", 20, 150, 40, 40, "png");
		}

		
	}
}else{
	$Mensagem = "Nenhum Item Atendido nesta Requisição";
	$Url = "RelContratoVencerVencido.php?Mensagem=".urlencode($Mensagem)."&Mens=1&Tipo=1";
	if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
	header("location: ".$Url);
	exit;
}

header('Expires: 0');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
$pdf->Output('Capa do contrato.pdf', 'I');


function formatCnpjCpf($value)
{
$cnpj_cpf = preg_replace("/\D/", '', $value);

if (strlen($cnpj_cpf) === 11) {
	return preg_replace("/(\d{3})(\d{3})(\d{3})(\d{2})/", "\$1.\$2.\$3-\$4", $cnpj_cpf);
}	
	return preg_replace("/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/", "\$1.\$2.\$3/\$4-\$5", $cnpj_cpf);
}

?>
							$pdf->AddPage(); // page break
						}
					}
				}

			}
		}
		

		$pdf->Ln(10);
		$pdf->Cell(200, 46, $observacao, 0, 1, "L", 0);
		
		$y = $pdf->GetY();

		for($i = 0; $i <= 126; $i++){
			$block=floor($i/6);
			
			$space_left = $page_height - ($pdf->GetY() + $bottom_margin); // Espaço a esquerda
			
			if ($i/6 == floor($i/6) && $height_of_cell > $space_left) {
				
				$pdf->AddPage(); // page break
			}
		}

		if($resumidoCompleto == 'resumido'){
			$site_producao = "http://www.recife.pe.gov.br/contratos-emprel/paginaspublicas/ConsultaContratoConsolidado.jsf";
			$site_local = $url."/portalcompras/contratos/CadContratoConsolidado.php?".$parametros;
			// var_dump($site_local);die;
			
			QRcode::png($site_local,"test.png");
			$pdf->Image("test.png", 20, 150, 40, 40, "png");
		}

		
	}
}else{
	$Mensagem = "Nenhum Item Atendido nesta Requisição";
	$Url = "RelContratoVencerVencido.php?Mensagem=".urlencode($Mensagem)."&Mens=1&Tipo=1";
	if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
	header("location: ".$Url);
	exit;
}

header('Expires: 0');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
$pdf->Output('Capa do contrato.pdf', 'I');


function formatCnpjCpf($value)
{
$cnpj_cpf = preg_replace("/\D/", '', $value);

if (strlen($cnpj_cpf) === 11) {
	return preg_replace("/(\d{3})(\d{3})(\d{3})(\d{2})/", "\$1.\$2.\$3-\$4", $cnpj_cpf);
}	
	return preg_replace("/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/", "\$1.\$2.\$3/\$4-\$5", $cnpj_cpf);
}

?>