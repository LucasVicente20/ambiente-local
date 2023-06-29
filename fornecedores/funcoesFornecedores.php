<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: funcoesFornecedores.php
# Objetivo: funções com regras do módulo Fornecedor
# Autor:    Ariston Cordeiro
#-----------------------
# Alterado: Heraldo Botelho
# Data: 11/9/2012
# Motivo : Alterar mensagem de critica do CNPJ do fornecedor
#---------------------------
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------
# Alterado: Pitang Agile TI - Caio Coutinho
# Data:     27/07/2018
# Objetivo: Tarefa Redmine 95900
# ------------------------------------------------------------------------
# Alterado: Ernesto Ferreira
# Data:		26/10/2018
# Objetivo: Tarefa Redmine 201223
# -----------------------------------------------------------------------------------------------------------------------------------------------

require_once("../funcoes.php");

# funcoes de materiais, usados no módulo de compras
require_once("../materiais/funcoesMateriais.php");

# constantes para o módulo fornecedores (usados nos programas)
ini_set('display_errors', 0);
 error_reporting(E_ALL ^ E_NOTICE);
# tipos de situacao do fornecedor
define("TIPO_SITUACAO_FORNECEDOR_CADASTRADO", 1);
define("TIPO_SITUACAO_FORNECEDOR_INABILITADO", 2);
define("TIPO_SITUACAO_FORNECEDOR_SUSPENSO", 3);
define("TIPO_SITUACAO_FORNECEDOR_CANCELADO", 4);
define("TIPO_SITUACAO_FORNECEDOR_EXCLUIDO", 5);
define("TIPO_SITUACAO_FORNECEDOR_INIDONEO", 6);

define("TIPO_CADASTRO_CPF", 1);
define("TIPO_CADASTRO_CNPJ", 2);

# Código do sistema imobiliário- verificacao de débito de CHF
define("RETORNO_CADASTRO_IMOBILIARIO_COM_DEBITO", 1);
define("RETORNO_CADASTRO_IMOBILIARIO_SEM_DEBITO", 2);
define("RETORNO_CADASTRO_IMOBILIARIO_ERRO_BANCO", 4);
define("RETORNO_CADASTRO_IMOBILIARIO_FALHA_COMUNICACAO", 99);
define("RETORNO_CADASTRO_IMOBILIARIO_ARQUIVO_NAO_ENCONTRADO", 100);

# retorno de erro da função checaSituacaoFornecedor()
define("RETORNO_VALIDACAO_FORNECEDOR_ERRO_NENHUM", 0);
define("RETORNO_VALIDACAO_FORNECEDOR_ERRO_SEM_NUMERO", 1);
define("RETORNO_VALIDACAO_FORNECEDOR_ERRO_TAMANHO_INVALIDO", 2);
define("RETORNO_VALIDACAO_FORNECEDOR_ERRO_NUMERO_INVALIDO", 3);
define("RETORNO_VALIDACAO_FORNECEDOR_ERRO_NAO_CADASTRADO", 4);
define("RETORNO_VALIDACAO_FORNECEDOR_ERRO_SISTEMA_MERCANTIL_INACESSIVEL", 100);

# Funções do módulo fornecedores ----------------------------------------------------------------------------------

# Retorna data mínima de validade de validade do balanço para fornecedores, em DateTime
function prazoUltimoBalanço(){
	#Ultimo balanço agora é validade do balanço, e não possui mais prazo, portanto é o dia corrente
	//$dataHoje = new DateTime();
	$data_menos_prazo = new DateTime();
	//$data_menos_prazo->setDate($dataHoje->format("Y")-1,$dataHoje->format("m")-6,$dataHoje->format("d"));
	return $data_menos_prazo;
}

# Retorna data mínima de validade da certidão neg. de falência e concordata para fornecedores, em DateTime
function prazoCertidaoNegDeFalencia(){
	$dataHoje = new DateTime();
	$data_menos_prazo = new DateTime();
	# Prazo é 150 dias
	$data_menos_prazo->setDate($dataHoje->format("Y"),$dataHoje->format("m"),$dataHoje->format("d")-150);
	return $data_menos_prazo;
}

# Verifica se o cadastro do fornecedor trata-se de um CPF ou CNPJ. retorna -1 caso não seja nenhum dos 2
function tipoCadastroFornecedor($CPFCNPJ){
	$CPFCNPJ = removeSimbolos($CPFCNPJ);
	assercao(!is_null($CPFCNPJ), "Parametro 'CPFCNPJ' é necessário");
	if( strlen($CPFCNPJ) == 11 ){
		return TIPO_CADASTRO_CPF;
	}elseif( strlen($CPFCNPJ) == 14 ){
		return TIPO_CADASTRO_CNPJ;
	}else{
		return -1;
	}
}

# recebe um cpf ou cnpj e retorna o sequencial do fornecedor
function getSequencialFromCpfCnpj($db, $CPFCNPJ){
	assercao(!is_null($db), "Variável do banco de dados Oracle não foi inicializada");
	assercao(!is_null($CPFCNPJ), "Parametro 'CPFCNPJ' é necessário");
	
	$CPFCNPJ = removeSimbolos($CPFCNPJ);
	$isCnpj = null;
	
	$tipoCadastro = tipoCadastroFornecedor($CPFCNPJ);
	if( $tipoCadastro == TIPO_CADASTRO_CPF ){
		$isCnpj = false;
	}else if( $tipoCadastro == TIPO_CADASTRO_CNPJ ){
		$isCnpj = true;
	}

	# recuperando informações do fornecedor
	$sql = "
		select f.aforcrsequ
		from SFPC.TBfornecedorcredenciado f
		where
	";
	if($isCnpj){
		$sql .= " aforcrccgc = '".$CPFCNPJ."'";
	}else{
		$sql .= " aforcrccpf = '".$CPFCNPJ."'";
	}
	$Linha = resultLinhaUnica( executarSQL($db, $sql ) );
	return $Linha[0];

}

# recebe dados do fornecedor a partir de seu sequencial
function getFornecedor($db, $idFornecedor){
	assercao(!is_null($db), "Variável do banco de dados Oracle não foi inicializada");
	assercao(!is_null($idFornecedor), "Parametro 'idFornecedor' é necessário");
	# recuperando informações do fornecedor
	$sql = "
		SELECT aforcrsequ, aprefosequ, aforcrccgc, aforcrccpf, aforcriden, nforcrorgu, 
		       nforcrsenh, nforcrrazs, nforcrfant, cceppocodi, eforcrlogr, aforcrnume, 
		       eforcrcomp, eforcrbair, nforcrcida, cforcresta, aforcrcddd, aforcrtels, 
		       aforcrnfax, nforcrmail, aforcrcpfc, nforcrcont, nforcrcarg, aforcrdddc, 
		       aforcrtelc, aforcrregj, dforcrregj, aforcrines, aforcrinme, aforcrinsm, 
		       vforcrcaps, vforcrcapi, vforcrpatl, vforcrinlc, vforcrinlg, dforcrultb, 
		       dforcrcnfc, aforcrentr, nforcrentp, dforcrvige, aforcrentt, dforcrgera, 
		       cgrempcodi, cusupocodi, fforcrcump, aforcrnten, dforcrexps, tforcrulat, 
		       cceloccodi, dforcranal, fforcrmepp, vforcrindi, ccomlicodi, vforcrinso, 
		       nforcrmai2, fforcrtipo, dforcrcont
		  FROM sfpc.tbfornecedorcredenciado
			WHERE aforcrsequ = ".$idFornecedor."
	";
	$obj = resultObjetoUnico( executarSQL($db, $sql ) );
	assercao(!is_null($obj), "id de fornecedor não existe.");	
	$resposta = array();
	$resposta['CPF'] = $obj->aforcrccpf;
	$resposta['CNPJ'] = $obj->aforcrccgc;
	if(is_null($resposta['CPF'])){
		$resposta['cpfCnpj'] = $obj->aforcrccgc;
		$resposta['tipoCpfCnpj'] = 'CNPJ';
	}else{
		$resposta['cpfCnpj'] = $obj->aforcrccpf;
		$resposta['tipoCpfCnpj'] = 'CPF';
	}
	$resposta['razaoSocial'] = $obj->nforcrrazs;
	$resposta['nomeFantasia'] = $obj->nforcrfant;

	$resposta['ddd'] = $obj->aforcrcddd;
	$resposta['telefone'] = $obj->aforcrtels;
	$resposta['fax'] = $obj->aforcrnfax;
	
	$resposta['telefoneFax'] = ""; // informa telefones e fax
	if(!is_null($resposta['telefone'])){
		$resposta['telefoneFax'] .=  "(".$resposta['ddd'].")".$resposta['telefone'];
	}
	if(!is_null($resposta['fax'])){
		if(!is_null($resposta['telefone'])){
			$resposta['telefoneFax'] .= " / ";
		}
		$resposta['telefoneFax'] .=  "(".$resposta['ddd'].")".$resposta['fax']." (FAX)";
	}

	$resposta['cep'] = $obj->cceppocodi;
	$resposta['logradouro'] = $obj->eforcrlogr;
	$resposta['numero'] = $obj->aforcrnume;
	$resposta['complemento'] = $obj->eforcrcomp;
	$resposta['bairro'] = $obj->eforcrbair;
	$resposta['cidade'] = $obj->nforcrcida;
	$resposta['estado'] = $obj->cforcresta;
	
	$resposta['endereço'] = $resposta['logradouro'];
	if(!is_null($resposta['complemento'])) $resposta['endereço'] .=" ".$resposta['complemento'];
	
	if(!is_null($resposta['numero'])) $resposta['endereço'] .=", ".$resposta['numero'];
	if(!is_null($resposta['bairro'])) $resposta['endereço'] .= " - ". $resposta['bairro'];
	if(!is_null($resposta['cidade'])) $resposta['endereço'] .= " - ".$resposta['cidade']."/".$resposta['estado'];
	
	$resposta['patrimonioLiquido'] = $obj->vforcrpatl;
	
	/* preencher depois resposta com todos os campos úteis */
	return $resposta;
	
}

# Verifica situacao do fornecedor no SICREF (necessário conexão com o banco)
# RETORNA: array com a resposta.
# 	$resposta["erro"] - código do erro encontrado.
#     0 (Nenhum erro), 1 (Numero nao encontrado), 2 (tamanho invalido), 3 (número inválido), 4 (não cadastrado)
#     5 (Problemas ao acessar o cadastro mercantil)
# 	$resposta["mensagem"] - mensagem sobre o que foi encontrado sobre o fornecedor
# 	$resposta["tipo"] - 'L'icitação, compra 'D'ireta, ou 'E'stoque
# 	$resposta["inabilitado"] - (Apenas para tipo licitação)

function checaSituacaoFornecedor($db, $CPFCNPJ, $exclusivo = false){
	$CPFCNPJ = removeSimbolos($CPFCNPJ);
	assercao(!is_null($db), "Variável do banco de dados Oracle não foi inicializada");
	assercao(!is_null($CPFCNPJ), "Parametro 'CPFCNPJ' é necessário");
	$resposta = array();
	$resposta["erro"] = -1;
	$resposta["mensagem"] = "";
	$resposta["tipo"] = -1;
	$resposta["razao"] = "";
	$resposta["situacao"] = -1;
	$resposta["inabilitado"] = false;
	$resposta["inabilitadoPorCertidaoVencida"] = false;
	$resposta["inabilitadoPorDataBalancoVencida"] = false;
	$resposta["inabilitadoPorCertidaoNegFalenciaVencida"] = false;
	$resposta["inabilitadoPorCHFVencido"] = false;
	$resposta["inabilitadoPorSituacao"] = false;
	$resposta["inabilitadoPorDebitoCadastroMercantil"] = false; //Fornecedor com irregularidade nos sistemas CI, CM e ITBI
	if(is_null($CPFCNPJ) or $CPFCNPJ==""){
		$resposta["erro"] = 1;
		$resposta["mensagem"] = "Número não informado";
	}else{
		$isCpfCnpjValido = false;
		$tipoCadastro = tipoCadastroFornecedor($CPFCNPJ);
		if( $tipoCadastro == TIPO_CADASTRO_CPF ){
			$isCnpj = false;
			$isCpfCnpjValido = valida_CPF($CPFCNPJ);
		}else if( $tipoCadastro == TIPO_CADASTRO_CNPJ ){
			$isCnpj = true;
			$isCpfCnpjValido = valida_CNPJ($CPFCNPJ);	
		}else{
			$resposta["erro"] = 2;
			$resposta["mensagem"] = "CNPJ/CPF do fornecedor com tamanho inválido";
		}
		if(!$isCpfCnpjValido and $resposta["erro"]==-1){
			$resposta["erro"] = 3;
			$resposta["mensagem"] = "CNPJ/CPF inválido";
		}
		if($resposta["erro"]==-1){
			$db = Conexao();

			# recuperando informações do fornecedor
			$sql = "
				select f.nforcrrazs, f.fforcrtipo, f.aforcrsequ, f.dforcrultb, f.dforcrcnfc
				from SFPC.TBfornecedorcredenciado f
				where
			";
			if($isCnpj){
				$sql .= " aforcrccgc = '".$CPFCNPJ."'";
			}else{
				$sql .= " aforcrccpf = '".$CPFCNPJ."'";
			}

			if($exclusivo) {
                $sql .= " AND fforcrmepp in ('1','2','3')";
            }

			$Linha = resultLinhaUnica( executarSQL( $db, $sql ) );
			$razaoSocial = $Linha[0];
			$tipo = $Linha[1];
			$sequencial = $Linha[2];
			$dataUltimoBalanco = $Linha[3];
			$dataCerticaoNegFalencia = $Linha[4];

			if($exclusivo && is_null($razaoSocial)) {
                $resposta["erro"] = 4;
                $resposta["mensagem"] = "<div id='clearInput' style='color: red; text-transform: uppercase; display: inline'>Fornecedor não cadastrado ou não está no grupo reservado</div>";
            } else if(is_null($razaoSocial)){
				$resposta["erro"] = 4;
				$resposta["mensagem"] = "Fornecedor não cadastrado";
			}else{
				$resposta["razao"] = $razaoSocial;
				$resposta["erro"] = 0;
				$resposta["tipo"] = $tipo;

				# recuperando situação do fornecedor
				$sql = "
					select cfortscodi
					from SFPC.TBfornsituacao
					where aforcrsequ = '".$sequencial."'
					order by tforsiulat DESC
					limit 1
				";
				$Linha = resultLinhaUnica( executarSQL( $db, $sql ) );
				$situacao = $Linha[0];


				$resposta["situacao"] = $situacao;
				$resposta["inabilitado"] = false;
				if($resposta["situacao"]!=TIPO_SITUACAO_FORNECEDOR_CADASTRADO){
					$resposta["inabilitado"] = true;
					$resposta["inabilitadoPorSituacao"] = true;
				}
				if($resposta["tipo"] =="L"){

					# verificando CHF 
					$sql = "
						select dforchvali from sfpc.tbfornecedorchf where aforcrsequ = '".$sequencial."'
					";
					$dataCHF = resultValorUnico( executarSQL( $db, $sql ));
					$datanow = date("Y-m-d");
					if($dataCHF < $datanow){
						$resposta["inabilitado"] = true;
						$resposta["inabilitadoPorCHFVencido"] = true;
					}
					
					# verificando data de balanço
					if ($dataUltimoBalanco < prazoUltimoBalanço()->format('Y-m-d')) {
						$resposta["inabilitado"] = true;
						$resposta["inabilitadoPorDataBalancoVencida"] = true;
					}
					# verificando data de balanço
					if ($dataCerticaoNegFalencia < prazoCertidaoNegDeFalencia()->format('Y-m-d')) {
						$resposta["inabilitado"] = true;
						$resposta["inabilitadoPorCertidaoNegFalenciaVencida"] = true;
					}

					# verificando certidões obrigatórias vencidas
					$sql = "
						SELECT C.DFORCEVALI
							FROM SFPC.TBTIPOCERTIDAO T, SFPC.TBFORNECEDORCERTIDAO C
							WHERE T.CTIPCECODI = C.CTIPCECODI
								AND C.AFORCRSEQU = '".$sequencial."'
					";
					$result = executarSQL( $db, $sql );
					
					while ($Linha = $result->fetchRow()) {
						$dataCertidao = $Linha[0];
						if ($dataCertidao < $datanow ) {
							$resposta["inabilitado"] = true;
							$resposta["inabilitadoPorCertidaoVencida"] = true;
						}
					}

					# Verificando irregularidade com a prefeitura
					# Pedido pela DLC para cancelar verificação
					/*$codigoResposta = checaDebitoImobiliarioMercantil($CPFCNPJ);
					if($codigoResposta!= RETORNO_CADASTRO_IMOBILIARIO_SEM_DEBITO){
						$resposta["inabilitado"] = true;
						$resposta["inabilitadoPorDebitoCadastroMercantil"] = true;
					}*/
				}

				if($resposta["inabilitado"]){
					if(!$resposta["inabilitadoPorDebitoCadastroMercantil"]){
						$resposta["mensagem"] = "Fornecedor com pendências no SICREF";
					}else{
						#irregularidade no sistema mercantil
						if($codigoResposta ==RETORNO_CADASTRO_IMOBILIARIO_ARQUIVO_NAO_ENCONTRADO){
							$resposta["mensagem"] = "Problemas ao consultar o sistema de Cadastro Imobiliário/Mercantil.<br/>Impossível adicionar fornecedor.<br/>Por favor, tente mais tarde ou contacte o administrador do sistema.";
							$resposta["erro"] = RETORNO_VALIDACAO_FORNECEDOR_ERRO_SISTEMA_MERCANTIL_INACESSIVEL;
							EmailErro("Arquivo do cadastro imobiliário não foi encontrado", __FILE__, __LINE__, "Arquivo do cadastro imobiliário não foi encontrado. (sistema de cadastros fora do ar?)", false);
						}else{
							$resposta["mensagem"] = "Fornecedor com irregularidade com a prefeitura";
						}
					}
				}
			}
		}
	}
	return $resposta;
}
# retorna true se o fornecedor informado fornece o mesmo grupo do material
function forneceMaterialServico($db, $fornecedor, $materialServico, $tipoMaterialServico){
	assercao(!is_null($db), "Variável do banco de dados Oracle não foi inicializada");
	assercao(!is_null($fornecedor), "Parametro 'fornecedor' é necessário");
	assercao(!is_null($materialServico), "Parametro 'materialServico' é necessário");
	assercao(!is_null($tipoMaterialServico), "Parametro 'tipoMaterialServico' é necessário");
	
	$grupoMaterial = getGrupoDeMaterialServico($db, $materialServico, $tipoMaterialServico);

	$sql = "
		select cgrumscodi
		from SFPC.TBgrupofornecedor gf
		where
			gf.aforcrsequ = ".$fornecedor." and gf.cgrumscodi = ".$grupoMaterial."
	";
	$res = executarSQL($db, $sql) ;
	$mesmoGrupo = false;
	while($linha = $res->fetchRow()){
		$grupoFornecedor = $linha[0];
		if($grupoFornecedor == $grupoMaterial ){
			$mesmoGrupo = true;
		}
	}
	return $mesmoGrupo;
}

# Verifica se um fornecedor possui débito imobiliário ou mercantil
# RETORNA: 1 (possui débito), 2 (Sem débito), 4 (erro no acesso ao banco), 99 (Falha de comunicação), 100 (Arquivo nao encontrado)
function checaDebitoImobiliarioMercantil($CNPJ){
	$link = 'http://sancho.recife/emprel_ci/servlet/cadastroImobiliarioSemSessao?service=EmitirXMLDebitoPorDocumento&codigoDocumento='.$CNPJ;

	$resposta = new DOMDocument();
  $resposta->load( $link );

	if($resposta==FALSE){
		$code = RETORNO_CADASTRO_IMOBILIARIO_ARQUIVO_NAO_ENCONTRADO;
	}else{
		
		$code = $resposta->getElementsByTagName("codigoRetorno" )->item(0)->getAttribute('codigo');
	}
	return $code;
}


//-----------------------------------------------------------------------------
// Função para testar formato do CNPJ ou CPF
// Retorna um array: 1o elemento ( true ou false ) se passou ou não no teste
//                   2o elemento ( mensagem de erro se 1o elemento = false ) 
//                   2o elemento ( CPF ou CNPJ formatado se 1o elemento = true )   
//------------------------------------------------------------------------------

function validaFormatoCNPJ_CPF($cnpj_cpf) {
	
	$indErro=false;
	$mensagem="Formato deve ser 999.999.999-99(CPF) ou 999.999.999/9999-99(CNPJ)"; 
	// remover formato
	$cnpj_cpf_aux=RemoveFormatoCPF_CNPJ($cnpj_cpf);

	// criticar se fora do padrao de tamanhos possiveis informando so numeros	
	$tam = strlen($cnpj_cpf_aux);	
	if ( $tam != 11 &&  $tam !=14  ) { 
  	    $retorno[0]=false;
  	    $retorno[1]=$mensagem; 
		return $retorno;
	}	
	
	// verificar se campo sem a mascara so temnumeros
	for ($i=0;$i<$tam;$i++) {
		if ( $cnpj_cpf_aux[$i]<'0' or $cnpj_cpf_aux[$i]> '9' ) {
  		    $retorno[0]=false;		
			$retorno[1]=$mensagem;
			return $retorno;			
		}
	}
	
	// verificar se campo informado tem mascara 
	$tam2 = strlen($cnpj_cpf);	
	$indMascara = false;	
	for ($i=0;$i<$tam2;$i++) {
		if ( $cnpj_cpf_aux[$i]<'0' or $cnpj_cpf_aux[$i]> '9' ) {
			$indMascara= true;
		}
	}
	
	//  verificar os tamanhos possives com mascaras 
	if ( $indMascara ) {
		if ( $tam2 != 14 &&  $tam2 !=18  ) { 
  		    $retorno[0]=false;
  		    $retorno[1]=$mensagem; 
			return $retorno;
		}
	}	
	
	
	// se tem mascara verificar se a mascara tá lugar correto
	if ( $indMascara ) {
		if (  $tam == 11  ) {
		  if ( substr($cnpj_cpf,3,1)!= '.'  || substr($cnpj_cpf,7,1)!= '.' ||  substr($cnpj_cpf,11,1)!= '-'    ) {
  			    $retorno[0]=false;		
				$retorno[1]=$mensagem;
				return $retorno;			
		  } 
		}

		if (  $tam == 14  ) {
		  if ( substr($cnpj_cpf,2,1)!= '.'  || substr($cnpj_cpf,6,1)!= '.' ||  substr($cnpj_cpf,10,1)!= '/' || substr($cnpj_cpf,15,1)!= '-'    ) {
  			    $retorno[0]=false;		
				$retorno[1]=$mensagem;
				return $retorno;			
	  		} 
		}
	}
	else {
		$cnpj_cpf=FormataCpfCnpj($cnpj_cpf_aux);
	}
	
	
	// reformatar e mover para o segundo elemento do retorno 
	$retorno[0]=true;
	$retorno[1]=$cnpj_cpf;
	return $retorno;
	
	
}


function getDescPorteEmpresa($indice){
	
	if ($indice=='1') {  
		return "MICROEMPRESA";
	}
	elseif ($indice=='2') {
		return "EMPRESA DE PEQUENO PORTE";
	}
	elseif ($indice=='3') {
		return "MICRO EMPREENDEDOR INDIVIDUAL";
	}
	else  {
		return "OUTROS";
	}
	
}

function getDescPorteEmpresaTitulo() {
  return "Microempresa, Peq. Porte ou Individual?";	
}

function comboBoxDescPorteEmpresa($porte) {
	 
	$porte = trim($porte);
	$vetorCod[0] = "";$vetorCod[1]="1";$vetorCod[2]="2";$vetorCod[3]="3"; 
	$vetorDesc[0] = "OUTROS";$vetorDesc[1]="MICROEMPRESA";$vetorDesc[2]="EMPRESA DE PEQUENO PORTE";$vetorDesc[3]="MICRO EMPREENDEDOR INDIVIDUAL";
	
 	echo	"<select name=\"MicroEmpresa\"  class=\"textonormal\"  onchange=\"document.CadGestaoFornecedor.submit()\" >";
    for ( $i=0;$i<=3;$i++) {
    	if ( $porte == $vetorCod[$i]) {
	    	 echo "<option value=\"".$vetorCod[$i]."\" selected>".$vetorDesc[$i];	
    	}
    	else
    	{
	    	 echo "<option value=\"".$vetorCod[$i]."\" >".$vetorDesc[$i];	
    	}
    } 	
	echo 	"</select>";
		
	
}

function selectPorteEmpresa($porte)
{
	$porte = trim($porte);
	$vetorCod[0] = "";$vetorCod[1]="1";$vetorCod[2]="2";$vetorCod[3]="3";
	$vetorDesc[0] = "OUTROS";$vetorDesc[1]="MICROEMPRESA";$vetorDesc[2]="EMPRESA DE PEQUENO PORTE";$vetorDesc[3]="MICRO EMPREENDEDOR INDIVIDUAL";

	echo	"<select name=\"MicroEmpresa\"  class=\"textonormal span12\"  onchange=\"document.CadGestaoFornecedor.submit()\" >";

	for ( $i=0;$i<=3;$i++) {
		if ( $porte == $vetorCod[$i]) {
			echo "<option value=\"".$vetorCod[$i]."\" selected>".$vetorDesc[$i];
		}
		else
		{
			echo "<option value=\"".$vetorCod[$i]."\" >".$vetorDesc[$i];
		}
	}

	echo 	"</select>";
}

function getDataUltimoBalanco($db, $seq){
	$sql =  " select dforcevali from sfpc.tbfornecedorcertidao ";
	$sql .= " where ";
	$sql .= " aforcrsequ = $seq ";
    $sql .= " and ctipcecodi = 11 ";
    
    $result = executarSQL($db, $sql);
    $row = $result->fetchRow(DB_FETCHMODE_OBJECT);
    return $row->dforcevali;

}