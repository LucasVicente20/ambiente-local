<?php

#------------------------------------------------------------------------------------
# Portal da DGCO
# Programa: ClaCancelamentoNota.php
# Autor:    Ariston Cordeiro
# Data:     03/09/2009
# Objetivo: Biblioteca de classes para manipulação de movimentações e cancelamentos, como objetos
#---------------------------------
# Alterado: Ariston Cordeiro
# Data:     03/09/2009 - substituindo funcões que começam com "add" para "adicionar", a fim de padronização. Ainda mantive "get" e "set" em inglês para nomenclatura das funções ser igual a set/get do Java. Funções que não são set e get devem ser em português.
#---------------------------------
# OBS.:     Tabulação 2 espaços
#------------------------------------------------------------------------------------

$NomePrograma = "ClaCancelamentoNota.php";

# CLASSES #############################################################

#Exceção que informa que não existe a posição procurada em um array
class ExceptionPosicaoNaoEncontrada extends Exception{};

#Exceção que informa que não existe o valor procurado em um array
class ExceptionValorNaoEncontrado extends Exception{};

#Exceção que informa que parâmetro informado é nulo
class ExceptionParametroNulo extends Exception{};


#Exceção que informa que duas instâncias de um Singleton estão tentando ser construídas
class ExceptionSegundaInstanciaEmSingleton extends Exception{};

# Representação em classe de uma sessão de Banco;
class Banco{
	#guarda como variável global uma sessão aberta para uso em outras classes
	static function guardarSessao($banco){
		$GLOBALS["bancoSessao"] = $banco;
	}
	static function getBanco(){
		return $GLOBALS["bancoSessao"];
	}
}

#classe que possui um tipo de movimentacao e seu respectivo cancelamento. Usado dentro da classe Cancelamentos.
class MovimentacaoCancelamento{
	var $pMovimentacao; //código da movimentação
	var $pCancelamentos; //código dos cancelamentos
	var $pMovimentacaoConclusao; //informa se o cancelamento deve ser feito quando a movimentação é concluida
	function __construct($movimentacao){
		$this->pMovimentacao = $movimentacao;
		$this->pCancelamentos = array();
		$this->pMovimentacaoConclusao = array();
	}
	#adiciona um cancelamento para a movimentação
	public function adicionarCancelamento($cancelamento, $movimentacaoConcluida){
		array_push($this->pCancelamentos,$cancelamento);
		array_push($this->pMovimentacaoConclusao,$movimentacaoConcluida);
		//echo "[".$this->pMovimentacao."; ".$cancelamento."; ".$movimentacaoConcluida."; ".$this->getFinalizado($cancelamento)."]";
	}
	#Retorna código da movimentacao
	public function getMovimentacao(){
		return $this->pMovimentacao;
	}
	#retorna Cancelamento na posição informada
	public function getCancelamento($posicao){
		if(!($posicao>=0 and $posicao<count($this->pCancelamentos))){
			throw new ExceptionPosicaoNaoEncontrada('posição inválida');
		}
		$result = $this->pCancelamentos[$posicao];
		return $result;
	}
	#retorna Cancelamentos
	public function getCancelamentos(){
		$result = $this->pCancelamentos;
		return $result;
	}
	#retorna a posicao do cancelamento no array
	public function getCancelamentoPosicao($cancelamento){
		$cancelamentos = $this->getCancelamentos();
		$index = array_search($cancelamento , $cancelamentos);
		if(is_null($index)){
			throw new ExceptionPosicaoNaoEncontrada('cancelamento não encontrado');
		}
		return $index;
	}
	#retorna se a movimentação necessita estar concluída para um dado cancelamento
	public function getFinalizado($cancelamento){
		$index = $this->getCancelamentoPosicao($cancelamento);
		$result = $this->pMovimentacaoConclusao[$index];
		/*if($this->pMovimentacao == 15){
			echo "[#".$this->pMovimentacao."]";
			echo "[##".$cancelamento."]";
			echo "[".$index."]";
			echo "[".$this->pMovimentacaoConclusao[$index]."]";
		}*/
		return $result;
	}
	#retorna o número de cancelamentos
	public function getNoCancelamentos(){
		return count($this->pCancelamentos);
	}
	#OBSOLETO. Usar adicionarCancelamento().
	public function addCancelamento($cancelamento, $movimentacaoConcluida){
		adicionarCancelamento($cancelamento, $movimentacaoConcluida);
	}

}

#classe que possui todos tipos de cancelamento
class Cancelamentos{

	static private $pInstancia;

	var $pMovimentacoesCancelamentos; // Guarda cancelamento por movimentação
	var $pDescricoes; // Guarda descricoes de Movimentacoes
	var $pDescricoesCod; // Guarda o código dos Movimentacoes, por ordem das descricoes

	private function __construct(){

		$this->pMovimentacoesCancelamentos = array();
		$this->pDescricoesCod = array();
		$this->pDescricoes = array();

 		$db   = $GLOBALS["bancoSessao"];
 		// Pega os cancelamentos de movimentações
		$Sql  = "
			SELECT
				CM.CTIPMVCODI, CM.CTIPMVCOD1, CM.FCANMVEFET, TM1.ETIPMVDESC, TM2.ETIPMVDESC
			FROM
				SFPC.TBCANCELAMENTOMOVIMENTACAO CM, SFPC.TBTIPOMOVIMENTACAO TM1, SFPC.TBTIPOMOVIMENTACAO TM2
			WHERE
				CM.CTIPMVCODI = TM1.CTIPMVCODI AND
				CM.CTIPMVCOD1 = TM2.CTIPMVCODI
			ORDER BY
				CM.CTIPMVCODI, CM.FCANMVEFET DESC, CM.CTIPMVCOD1
		";

		$res  = $db->query($Sql);
		if( PEAR::isError($res) ){
			$db->disconnect();
			EmailErroSQL($GLOBALS["NomePrograma"], __FILE__, __LINE__, "Erro em SQL no construtor da classe Cancelamentos", $Sql, $res);
			exit(0);
		}else{
			$ultimoCTIPMVCODI=-1;
			$rows = $res->numRows();
			$movimentacaoCancelamento=0;
			for($itr=0;$itr<$rows;$itr++){
				$Linha = $res->fetchRow();
				$CTIPMVCODI=$Linha[0];
				$CTIPMVCOD1=$Linha[1];
				$FCANMVEFET=$Linha[2];
				if($FCANMVEFET == "S"){
					$FCANMVEFET = true;
				}else{
					$FCANMVEFET = false;
				}
				$DescricaoMovimentacao=$Linha[3];
				$DescricaoCancelamento=$Linha[4];
				// guarda descricoes das movimentacoes e seus cancelamentos
				if(!in_array($CTIPMVCODI, $this->pDescricoesCod)){
					array_push($this->pDescricoesCod,$CTIPMVCODI);
					array_push($this->pDescricoes,$DescricaoMovimentacao);
				}
				if(!in_array($CTIPMVCOD1, $this->pDescricoesCod)){
					array_push($this->pDescricoesCod,$CTIPMVCOD1);
					array_push($this->pDescricoes,$DescricaoCancelamento);
				}

				if($ultimoCTIPMVCODI != $CTIPMVCODI){
					$ultimoCTIPMVCODI = $CTIPMVCODI;
					if($itr!=0){
						//adiciona movimentação da iteração anterior
						array_push($this->pMovimentacoesCancelamentos,$movimentacaoCancelamento);
					}
					//cria nova movimentacao
					$movimentacaoCancelamento = new MovimentacaoCancelamento($CTIPMVCODI);
				}
				//adiciona o cancelamento na movimentacao
				$movimentacaoCancelamento->adicionarCancelamento($CTIPMVCOD1, $FCANMVEFET);
			}
			#adiciona movimentação da ultima iteracao
			array_push($this->pMovimentacoesCancelamentos,$movimentacaoCancelamento);
			self::$pInstancia = $this;
		}
	}

	public static function singleton(){
     if (!isset(self::$pInstancia)) {
       $c = __CLASS__;
       self::$pInstancia = new $c;
     }
		return self::$pInstancia;
	}

	private function __clone(){
  	throw new ExceptionSegundaInstanciaEmSingleton("instância de Cancelamentos é singleton e não pode ser clonado.");
	}

	# adiciona uma MovimentacaoCancelamento
	public function adicionarMovimentacaoCancelamento($movimentacoesCancelamentos){
		array_push($this->pMovimentacoesCancelamentos,$movimentacoesCancelamentos);
	}

	# retorna um MovimentacaoCancelamento
	# Parametros:
	# 	$movimentacao - código do tipo de movimentação
	# Retorno:
	# 	MovimentacaoCancelamento contendo a MovimentacaoCancelamento relacionada ao tipo de movimentação informada
	# Excessões:
	#	ExceptionValorNaoEncontrado- quando não é encontrado uma movimentacao correspondente
	#		Ocorre caso a movimentação informada não exista, ou ela não pode ser cancelada.
	public function getMovimentacaoCancelamento($movimentacao){
		$result = null;
		for($itr=0;$itr<count($this->pMovimentacoesCancelamentos);$itr++){
			$movimentacaoCancelamento = $this->pMovimentacoesCancelamentos[$itr];
			if($movimentacaoCancelamento->getMovimentacao()==$movimentacao){
				$result = $this->pMovimentacoesCancelamentos[$itr];
				$itr = 99999;
			}
		}
		if($result ===null){
			throw new ExceptionValorNaoEncontrado('movimentação não encontrada');
		}
		return $result;
	}
	#retorna os cancelamentos dado uma movimentacao
	# Parametros:
	# 	$movimentacao - código do tipo de movimentação
	# Retorno:
	# 	array com todos cancelamentos do tipo de movimentacao $movimentacao
	# Excessões:
	#	ExceptionValorNaoEncontrado- quando não é encontrado uma movimentação correspondente.
	#		Ocorre caso a movimentação informada não exista, ou ela não pode ser cancelada.
	public function getTodosCancelamentos($movimentacao){
		try{
			$movimentacaoCancelamento = $this->getMovimentacaoCancelamento($movimentacao);
		} catch (ExceptionValorNaoEncontrado $e){
			//repassando excessão
			throw new ExceptionValorNaoEncontrado('movimentação não encontrada');
		}
		$result = $movimentacaoCancelamento->getCancelamentos();
		return $result;
	}
	#retorna o número de cancelamentos dado uma movimentacao e se esta movimentação foi finalizada
	# Parametros:
	# 	$movimentacao - código do tipo de movimentação
	# 	$finalizada - boolean informando situação da movimentação (se foi finalizada ou não)
	# Retorno:
	# 	número de todos cancelamentos para aquele tipo de Movimentacao com aquela situação
	# Excessões:
	#	ExceptionValorNaoEncontrado- quando não é encontrado uma movimentação correspondente.
	#		Ocorre caso a movimentação informada não exista, ou ela não pode ser cancelada.
	public function noCancelamentos($movimentacao, $finalizada){
		try{
			$movimentacaoCancelamento = $this->getMovimentacaoCancelamento($movimentacao);
		} catch (ExceptionValorNaoEncontrado $e){
			//repassando excessão
			throw new ExceptionValorNaoEncontrado('movimentação não encontrada');
		}
		$noCancelamentos = $movimentacaoCancelamento->getNoCancelamentos();
		$cnt=0;
		for($itr=0; $itr < $noCancelamentos ;$itr++){
			$cancelamento = $this->getCancelamentoPorPosicao($movimentacao,$itr);
			if($movimentacaoCancelamento->getFinalizado($cancelamento)==$finalizada){
				$cnt++;
			}
		}
		return $cnt;
	}
	#retorna o cancelamento dado uma movimentacao e uma posição
	# Parametros:
	# 	$movimentacao - código do tipo de movimentação
	# 	$posicao - número do cancelamento em relação a movimentação
	# Retorno:
	# 	tipo de movimentacao do cancelamento
	public function getCancelamentoPorPosicao($movimentacao, $posicao){
		$cancelamentos = $this->getTodosCancelamentos($movimentacao);
		$result = $cancelamentos[$posicao];
		return $result;
	}
	#retorna o cancelamento dado uma movimentacao, uma posição e a situação da movimentação (se foi finalizada ou não)
	# Parametros:
	# 	$movimentacao - código do tipo de movimentação
	# 	$finalizada - boolean informando situação da movimentação (se foi finalizada ou não)
	# 	$posicao - número do cancelamento em relação a movimentação
	# Retorno:
	# 	tipo de movimentacao do cancelamento
	# Excessões:
	#	ExceptionValorNaoEncontrado- quando não é encontrado uma movimentação correspondente.
	#		Ocorre caso a movimentação informada não exista, ou ela não pode ser cancelada.
	public function getCancelamento($movimentacao, $finalizada, $posicao){
		try{
			$movimentacaoCancelamento = $this->getMovimentacaoCancelamento($movimentacao);
		} catch (ExceptionValorNaoEncontrado $e){
			//repassando excessão
			throw new ExceptionValorNaoEncontrado('movimentação não encontrada');
		}
		$noCancelamentos = $movimentacaoCancelamento->getNoCancelamentos();
		$cnt=-1;
		$result = null;
		for($itr=0; $itr < $noCancelamentos ;$itr++){
			$cancelamento = $this->getCancelamentoPorPosicao($movimentacao,$itr);
			if($movimentacaoCancelamento->getFinalizado($cancelamento)==$finalizada){
				$cnt++;
				if($cnt==$posicao){
					$result = $cancelamento;
				}
			}
		}
		return $result;
	}
	#retorna a descrição de um cancelamento
	public function getDescricao($cancelamento){
		$index = array_search($cancelamento , $this->pDescricoesCod);
		$result = $this->pDescricoes[$index];
		return $result;
	}
	# OBSOLETO. Usar adicionarMovimentacaoCancelamento();
	public function addMovimentacaoCancelamento($movimentacoesCancelamentos){
		adicionarMovimentacaoCancelamento($movimentacoesCancelamentos);
	}

}

#Guarda estrutura de Nota Fiscal
class NotaFiscal{
	var $pAlmoxarifado;
	var $pAno;
	var $pCodigo;
	var $pNota;
	var $pSerie;
	var $pUlat;
	var $pCancelado;
	public function __construct($almoxarifado, $ano, $codigo, $nota, $serie, $cancelado, $ulat){
		$this->pAlmoxarifado = $almoxarifado;
		$this->pAno = $ano;
		$this->pCodigo = $codigo;
		$this->pNota = $nota;
		$this->pSerie = $serie;
		$this->pCancelado = $cancelado;
		$this->pUlat = $ulat;
	}
	public function getAlmoxarifado(){
		return $this->pAlmoxarifado;
	}
	public function getAno(){
		return $this->pAno;
	}
	public function getCodigo(){
		return $this->pCodigo;
	}
	public function getNota(){
		return $this->pNota;
	}
	public function getSerie(){
		return $this->pSerie;
	}
	public function getCancelado(){
		return $this->pCancelado;
	}
	public function getUlat(){
		return $this->pUlat;
	}
	# verifica se são a nota fiscal
	public function igual($nota){
		$result = false;
		if(is_null($nota)){
			$result = true;
		}else if(
			( $this->getAlmoxarifado() == $nota->getAlmoxarifado() ) and
			( $this->getAno() == $nota->getAno() ) and
			( $this->getCodigo() == $nota->getCodigo() )
		){
			$result = true;
		}
		return $result;
	}
}

class Requisicao{
	var $pSequencial;
	var $pAno;
	var $pCodigo; //código a partir do órgão
	public function __construct($sequencial, $ano,$codigo){
		$this->pSequencial = $sequencial;
		$this->pAno = $ano;
		$this->pCodigo = $codigo;
	}
	public function getSequencial(){
		return $this->pSequencial;
	}
	public function getAno(){
		return $this->pAno;
	}
	public function getCodigo(){
		return $this->pCodigo;
	}
	# verifica se é a mesma requisição
	function igual($requisicao){
		$result = false;
		if(is_null($requisicao)){
			$result = true;
		}else if(
			(!is_null($this->getSequencial())) and
			( $this->getSequencial() == $requisicao->getSequencial() )
		){
			$result = true;
		}
		return $result;
	}
}

# Guarda informações de um Movimentacao
class Movimentacao{
	var $pAlmoxarifado;
	var $pAno;
	var $pDataMovimentacao; //data que ocorreu o Movimentacao
	var $pCodMovimentacao; // Código do Movimentacao
	var $pMaterial;
	var $pQtdeMaterial;
	var $pQtdeMaterialCancelamento; //soma dos cancelamentos do material
	var $pValorMaterial;
	var $pEntradaSaida; // se é entrada ou saída
	var $pRequisicao;
	var $pNotaFiscal; //variável tipo NotaFiscal
	var $pNotaCancelada;
	var $pFinalizado;
	var $pCodMovimentacaoTipo; //Código da Movimentacao pelo tipo de movimentação
	var $pTipo; //código do tipo de movimentacao
	var $pOcultar; //se deve ocultar o Movimentacao para o usuário
	var $pCancelado; //se a movimentação foi cancelada
	var $pCodMovAssociada;
	var $pAnoMovAssociada;
	var $pAlmoxarifadoMovAssociada;

	function __construct(
		$Almoxarifado, $Ano, $CodMovimentacao, $CodMovimentacaoTipo, $Tipo,
		$DataMovimentacao, $EntradaSaida, $Material, $QtdeMaterial, $ValorMaterial,
		$Finalizado, $CodMovAssociada, $AnoMovAssociada, $AlmoxMovAssociada, $Requisicao = null,
		$Nota = null
	){
		$this->pAlmoxarifado = $Almoxarifado;
		$this->pAno = $Ano;
		$this->pCodMovimentacao = $CodMovimentacao;
		$this->pCodMovimentacaoTipo = $CodMovimentacaoTipo;
		$this->pTipo = $Tipo;
		$this->pDataMovimentacao = $DataMovimentacao;
		$this->pEntradaSaida = $EntradaSaida;
		$this->pMaterial = $Material;
		$this->pQtdeMaterial = $QtdeMaterial;
		$this->pQtdeMaterialCancelamento = 0;
		$this->pValorMaterial = $ValorMaterial;
		$this->pRequisicao = $Requisicao;
		$this->pOcultar = false;
		$this->pCancelado = false;
		$this->pFinalizado = $Finalizado;
		$this->pCodMovAssociada = $CodMovAssociada;
		$this->pAnoMovAssociada = $AnoMovAssociada;
		$this->pAlmoxarifadoMovAssociada = $AlmoxMovAssociada;
		//$this->pRequisicaoSequ = $RequisicaoSequ;
		$this->pNotaFiscal = $Nota;
		$this->pCancelado = false;
	}
	function getAlmoxarifado(){
		return $this->pAlmoxarifado;
	}
	function getAno(){
		return $this->pAno;
	}
	function getDataMovimentacao(){
		$result = $this->pDataMovimentacao;
		return $result;
	}
	function getCodigo(){
		return $this->pCodMovimentacao;
	}
	function getMaterial(){
		return $this->pMaterial;
	}
	function getQtdeMaterial(){
		return $this->pQtdeMaterial;
	}
	function getQtdeMaterialCancelamento(){
		return $this->pQtdeMaterialCancelamento;
	}
	function getValorMaterial(){
		return $this->pValorMaterial;
	}
	function getEntradaSaida(){
		return $this->pEntradaSaida;
	}
	function getRequisicao(){
		return $this->pRequisicao;
	}
	function getNotaFiscal(){
		return $this->pNotaFiscal;
	}
	function getCodigoTipo(){
		return $this->pCodMovimentacaoTipo;
	}
	function getFinalizado(){
		return $this->pFinalizado;
	}
	function getTipo(){
		return $this->pTipo;
	}
	function getOcultar(){
		return $this->pOcultar;
	}
	function setOcultar($boolean){
		$this->pOcultar = $boolean;
	}
	function setQtdeMaterialCancelamento($valor){
		$this->pQtdeMaterialCancelamento = $valor;
	}
	function getCancelado(){
		return $this->pCancelado;
	}
	function setCancelado($boolean){
		$this->pCancelado = $boolean;
	}
	function getCodMovAssociada(){
		return $this->pCodMovAssociada;
	}
	function getAnoMovAssociada(){
		return $this->pAnoMovAssociada;
	}
	function getAlmoxMovAssociada(){
		return $this->pAlmoxarifadoMovAssociada;
	}
	# verifica se possuem a mesma movimentação associada
	function mesmaMovAssociada($movimentacao){
		if(is_null($movimentacao)){
			throw new ExceptionPosicaoNaoEncontrada('movimentação enviada é um valor nulo');
		}

		$result = false;
		if(
			(!is_null($this->getCodMovAssociada())) and
			(!is_null($this->getAnoMovAssociada())) and
			( $this->getCodMovAssociada() == $movimentacao->getCodMovAssociada() ) and
			( $this->getAnoMovAssociada() == $movimentacao->getAnoMovAssociada() )
		){
			$result = true;
		}
		return $result;
	}
	# verifica se $movimentação é a movimentação associada
	function isMovAssociada($movimentacao){

	/*	if (($this->getCodigo() ==3337) ){
			echo "<br/>[@]";
			echo "[".$movimentacao->getCodigo()."]";
			echo "[".$this->getCodMovAssociada()." == ".$movimentacao->getCodigo()."]";
			echo "[".$this->getAnoMovAssociada()." == ".$movimentacao->getAno()."]";
		}*/


		if(is_null($movimentacao)){
			throw new ExceptionPosicaoNaoEncontrada('movimentação enviada é um valor nulo');
		}
		$result = false;

		if(
			(!is_null($this->getCodMovAssociada())) and
			(!is_null($this->getAnoMovAssociada())) and
			( $this->getCodMovAssociada() == $movimentacao->getCodigo() ) and
			( $this->getAnoMovAssociada() == $movimentacao->getAno() )
		){
			$result = true;
		}
		return $result;
	}
	function igual($movimentacao){
		$result = false;
		if(
			( !is_null($this->getAlmoxarifado()) )
			and ( !is_null($this->getAno()) )
			and ( !is_null($this->getCodigo()) )
			and ($this->getAlmoxarifado() == $movimentacao->getAlmoxarifado() )
			and ( $this->getAno() == $movimentacao->getAno() )
			and ( $this->getCodigo() == $movimentacao->getCodigo() )
		){
			$result = true;
		}
		return $result;
	}
}

# Guarda uma lista de movimentacoes
class Movimentacoes{
	var $pMovimentacoes;

	# compara 2 objetos do tipo Movimentacao e informa a ordem (1 quando a > b, -1 quando a < b). Usado pelo usort()
	public static function sortMovimentacaoPorData($m1, $m2){
		$m1Data = $m1->getDataMovimentacao();
		$m2Data = $m2->getDataMovimentacao();
		if( $m1Data > $m2Data ){
			return 1;
		}else if( $m1Data < $m2Data ){
			return -1;
		}else{
			return 0;
		}
	}

	function __construct(){
		$this->pMovimentacoes = array();
	}
	#adiciona um Movimentacao.
	function adicionarMovimentacao($movimentacao){
		array_push($this->pMovimentacoes, $movimentacao);
	}

	#verifica se existe um tipo de movimentação
	function existeTipoMovimentacao($tipoMovimentacao){
		$existeTipo = false;
  	foreach( $this->pMovimentacoes as $movimentacao ){
			if($movimentacao->getTipo()==$tipoMovimentacao){
				$existeTipo = true;
			}
		}
		return $existeTipo;
	}

	#inicia e adiciona um Movimentacao.
	function adicionarMovimentacaoPorValores(
		$Almoxarifado, $Ano, $CodMovimentacao, $CodMovimentacaoTipo, $Tipo,
		$DataMovimentacao, $EntradaSaida, $Material, $QtdeMaterial, $ValorMaterial,
		$Finalizado, $CodMovAssociada, $AnoMovAssociada, $AlmoxMovAssociada,
		$Requisicao = null, $Nota = null
		/*$RequisicaoSequ = null, $AnoRequisicao = null,
		$NotaNumero = null, $NotaSerie = null, $NotaAno = null, $NotaCodi = null, $NotaCancelada =null*/
	){

		$movimentacao = new Movimentacao (
			$Almoxarifado, $Ano, $CodMovimentacao, $CodMovimentacaoTipo, $Tipo,
			$DataMovimentacao, $EntradaSaida, $Material, $QtdeMaterial, $ValorMaterial,
			$Finalizado, $CodMovAssociada, $AnoMovAssociada, $AlmoxMovAssociada,
			$Requisicao, $Nota
			/*$RequisicaoSequ, $AnoRequisicao,
			$NotaNumero, $NotaSerie, $NotaAno, $NotaCodi, $NotaCancelada*/

		);
		$this->adicionarMovimentacao($movimentacao);
	}
	function getMovimentacao($posicao){
		return $this->pMovimentacoes[$posicao];
	}
	function getMovimentacoes(){
		return $this->pMovimentacoes;
	}
	function getNoMovimentacoes(){
		return count($this->pMovimentacoes);
	}
	function getNoMovimentacoesNaoOcultas(){
		$count = 0;
  	foreach( $this->pMovimentacoes as $movimentacao ){
			if(!$movimentacao->getOcultar()){
				$count++;
			}
		}
		return $count;
	}
	# Verifica e oculta um determinado Movimentacao e seus cancelamentos, caso ele foi cancelado.
	# $posicaoMovimentacao- posicão do Movimentacao que deve ser encontrado o cancelamento
	# $cancelamento- objeto tipo Cancelamento com todos os cancelamentos
	function ocultarCancelado($movimentacao){
		$cancelamentos = Cancelamentos::singleton();
		$tipoMovimentacao = $movimentacao->getTipo();
		$movimentacaoFinalizado = $movimentacao->getFinalizado();
		$semCancelamento = false;
		$noTiposCancelamentos = 0;
		try{
			$noTiposCancelamentos = $cancelamentos->noCancelamentos($tipoMovimentacao, $movimentacaoFinalizado);
		}catch(ExceptionValorNaoEncontrado $e){
			$semCancelamento = true;
		}
		$noMovimentacoes = $this->getNoMovimentacoes();
		$movimentacao2 = null;
		$tipoCancelamento = -1;
		$quantidadeMovimentacao = $movimentacao->getQtdeMaterial();
		$quantidadeCancelamento = 0;
		$cancelou =false;

		//echo "<br/><br/>[#".$tipoMovimentacao."]";

		if( $tipoMovimentacao == 9 ){
			$db = $GLOBALS["bancoSessao"];

			$Sql  = "
				select cmovmacod1, amovmaano1, calmpocod1
				from sfpc.tbmovimentacaomaterial
				where
					cmovmacodi = ".$movimentacao->getCodMovAssociada()."
					and amovmaanom = ".$movimentacao->getAnoMovAssociada()."
					and calmpocodi = ".$movimentacao->getAlmoxMovAssociada()."
				order by cmovmacodi
			";

			$res  = $db->query($Sql);
			if( PEAR::isError($res) ){
				$db->disconnect();
				EmailErroSQL($GLOBALS["NomePrograma"], __FILE__, __LINE__, "Erro em SQL na classe Movimentacoes, função ocultarCancelado.", $Sql, $res);
				exit();
			}
			$Linha = $res->fetchRow();
			$devolucaoCod = $Linha[0];
			$devolucaoAno = $Linha[1];
			$devolucaoAlmoxarifado = $Linha[2];
		}

		if(!$movimentacao->getCancelado() and (!$semCancelamento)){ //ha algum tipo que cancela?
		  for($itrCan = 0; $itrCan < $noTiposCancelamentos & !$cancelou ; $itrCan++){ //para cada tipo de cancelamento
				$tipoCancelamento = $cancelamentos->getCancelamento($tipoMovimentacao, $movimentacaoFinalizado, $itrCan);
				for($itrMov =$noMovimentacoes-1; $itrMov >=0 & !$cancelou; $itrMov--){

					$movimentacao2 = $this->getMovimentacao($itrMov);


					/*if(
					  ($movimentacao->getCodigo() ==3337) and
					  ($movimentacao2->getCodigo() ==3335)
					){
						echo "<br/>[".(!$movimentacao2->getCancelado())
							." + ".($movimentacao2->getTipo() == $tipoCancelamento )
							." + ".($movimentacao2->getAlmoxarifado() == $movimentacao->getAlmoxarifado())
							." + ".($movimentacao2->getMaterial() == $movimentacao->getMaterial())
							." + ".($movimentacao2->getQtdeMaterial() == $quantidadeMovimentacao)
							."]";
					}*/


					// verificando se $movimentacao2 é um cancelamento de $movimentacao
					if(
						// Se Movimentacao 2 não foi cancelado
						(!$movimentacao2->getCancelado()) and
						// Se Movimentacao 2 é do mesmo tipo do cancelamento do Movimentacao 1
						($movimentacao2->getTipo() == $tipoCancelamento ) and
						// Se almoxarifado é o mesmo
						($movimentacao2->getAlmoxarifado() == $movimentacao->getAlmoxarifado()) and
						// Se material é o mesmo
						($movimentacao2->getMaterial() == $movimentacao->getMaterial()) and
						// Se data do cancelamento não é menor que a data de Movimentacao 1
						/*($movimentacao2->getDataMovimentacao() >= $movimentacao->getDataMovimentacao()) and*/
						// Se quantidade movida é a mesma
						// (botar se Movimentacao 2 é menor ou igual a Movimentacao 1 caso queira que o relatório
						// reconheça várias movimentações cancelamento uma única movimentação)
						($movimentacao2->getQtdeMaterial() == $quantidadeMovimentacao)
					){
							if(
									// Regra de cancelamento de movimentações para o relatório de cancelamento de nota fiscal
									// Diferença desta regra para regra de cancelamento de movimentações no cancelamento de nota fiscal:
									// # Ao encontrar uma movimentação cancelada, cancela tanto a movimentação quanto cancelamentos
									// # Verifica apenas os cancelamentos de movimentações e não verifica se um cancelamento possui movimentação correspondente (pois o cancelamento é encontrado quando é detectado que a movimentação que ele cancela foi cancelado)
									// # Oculta cancelamento apenas quando a quantidade é a mesma da movimentação sendo cancelada. Isto se deve ao funcionamento do relatório (não aceita cancelamentos parciais)
									(
										// Se requisição é a mesma
										( $movimentacao->getRequisicao() != null) and
										( $movimentacao->getRequisicao()->igual($movimentacao2->getRequisicao()) ) and
										( in_array( $movimentacao2->getTipo() , array(4, 20, 22, 2, 18, 19, 21 ) ) )
									) or (
										// Se nota fiscal é a mesma
										( $movimentacao->getNotaFiscal() != null) and
										( $movimentacao->getNotaFiscal()->igual($movimentacao2->getNotaFiscal()) ) and
										( in_array( $movimentacao2->getTipo() , array(8, 3, 7 ) ) )
									) or (
										( $tipoMovimentacao == 6 ) and
										( $movimentacao2->getTipo() == 13 ) and
										( $movimentacao2->mesmaMovAssociada($movimentacao) )
									) or (
										// em cancelamentos como 26, 27, ou 31, a movimentação correspondente de Movimentacao 2 deve ser a movimentacao que esta sendo cancelada
										// 26 cancela 14, 16, 17, 23, 24, 37
										($movimentacao2->getTipo() == 26) and
										( in_array( $tipoMovimentacao , array(14,16,17,23,24,37) ) ) and
										( $movimentacao2->isMovAssociada($movimentacao) )
									) or (
										// 27 cancela 10, 26, 32
										($movimentacao2->getTipo() == 27) and
										( in_array( $tipoMovimentacao , array(10,26,32) ) ) and
										( $movimentacao2->isMovAssociada($movimentacao) )
									) or (
										// 31 cancela 12, 13, 15, 30
										($movimentacao2->getTipo() == 31) and
										( in_array( $tipoMovimentacao , array(12,13,15,30) ) ) and
										( $movimentacao2->isMovAssociada($movimentacao) )
									) or (
										// nenhum dos casos OR acima
										( $movimentacao->getRequisicao() == null) and
										( $movimentacao->getNotaFiscal() == null) and
										//(!in_array( $tipoMovimentacao , array(2, 3, 4, 6, 7, 8, 18, 19, 20, 21, 22, 26, 27, 31 ) ))
										(in_array( $tipoMovimentacao , array(11, 15, 29, 30 ) ))
									)
								){
									/*echo "<br/>[#1#".$movimentacao->getCodigo()."]";
									echo "[".$movimentacao->getTipo()."]";
									echo "[!".$movimentacao2->getCodigo()."]";
									echo "[".$movimentacao2->getTipo()."]";
									echo "[".$movimentacao2->getCancelado()."]";*/


									$quantidadeCancelamento = $movimentacao2->getQtdeMaterial();
									$movimentacao2->setOcultar(true);
									$movimentacao2->setCancelado(true);

									$movimentacao->setOcultar(true);
									$movimentacao->setCancelado(true);
									$cancelou = true;

									$movimentacao->setQtdeMaterialCancelamento($quantidadeCancelamento);
									$movimentacao2->setQtdeMaterialCancelamento(-$quantidadeCancelamento);

								} else if(
										( $tipoMovimentacao == 9 )
										and ( $movimentacao2->getTipo() == 12 )
										and ( $movimentacao2->getCodigo() == $devolucaoCod )
										and ( $movimentacao2->getAno() == $devolucaoAno )
										and ( $movimentacao2->getAlmoxarifado() == $devolucaoAlmoxarifado )
								){

									// verificar cancelamentos 9 e 12, em que 9 possui como associado uma movimentação 13 que possui como associado uma movimentação 12

									/*echo "<br/>[#2#".$movimentacao->getCodigo()."]";
									echo "[".$movimentacao->getTipo()."]";
									echo "[!".$movimentacao2->getCodigo()."]";
									echo "[".$movimentacao2->getTipo()."]";
									echo "[".$movimentacao2->getCancelado()."]";*/

									$quantidadeCancelamento = $movimentacao2->getQtdeMaterial();
									$movimentacao2->setOcultar(true);
									$movimentacao2->setCancelado(true);

									$movimentacao->setOcultar(true);
									$movimentacao->setCancelado(true);
									$cancelou = true;

									$movimentacao->setQtdeMaterialCancelamento($quantidadeCancelamento);
									$movimentacao2->setQtdeMaterialCancelamento(-$quantidadeCancelamento);

								}

						}
				}
		  }
		}

	}

	# Oculta movimentações canceladas
  function ocultarCanceladas(){
		foreach( $this->pMovimentacoes as $movimentacao ){
			$this->ocultarCancelado($movimentacao);
		}
  }

	# Oculta todos Movimentacoes de um determinado tipo
	public function ocultarMovimentacoesPorTipo($tipoMovimentacao){
  	foreach( $this->pMovimentacoes as $movimentacao ){
			if($movimentacao->getTipo()==$tipoMovimentacao){
				$movimentacao->setOcultar(true);
			}
		}
	}

	# sorteia movimentações por data
	public function sortMovimentacoesPorData(){
		usort($this->pMovimentacoes, self::sortMovimentacaoPorData);
	}

	# OBSOLETO. Usar adicionarMovimentacao().
	function addMovimentacao($movimentacao){
		$this->adicionarMovimentacao($movimentacao);
	}
}

# Guarda lista de Movimentações para serem desfeitas dado uma Nota Fiscal a ser cancelada e um material
class MovimentacoesCancelamentoNota extends Movimentacoes{
	var $pNotaFiscal;
	var $pMaterial;
	var $existeAntesDoInventario; //nota fiscal é de antes do último inventário?
	public function __construct($Almoxarifado, $AnoNF, $CodNF, $Material){
		parent::__construct();
		$this->_atualizaMovimentacoes($Almoxarifado, $AnoNF, $CodNF, $Material);
	}
	function getNotaFiscal(){
		return $this->pNotaFiscal;
	}

	function _atualizaMovimentacoes($Almoxarifado, $AnoNF, $CodNF, $Material){

		$this->pMaterial = $Material;

 		$db = $GLOBALS["bancoSessao"];

		# Pega os dados da nota fiscal #
		$Sql  = "
			select tentnfulat, aentnfnota, aentnfseri, fentnfcanc
			from sfpc.tbentradanotafiscal enf
			where
				enf.calmpocodi = ".$Almoxarifado."
				and enf.aentnfanoe = ".$AnoNF."
				and enf.centnfcodi = ".$CodNF."
		";
		$res  = $db->query($Sql);
		if( PEAR::isError($res) ){
			$db->disconnect();
			EmailErroSQL($GLOBALS["NomePrograma"], __FILE__, __LINE__, "Erro em SQL no construtor da classe MovimentacoesCancelamentoNota", $Sql, $res);
			exit();
		}
		$Linha = $res->fetchRow();

		$Ulat = $Linha[0];
		$NFNota = $Linha[1];
		$NFSerie = $Linha[2];
		$NFCancelada = $Linha[2];

		if($NFCancelada == "S"){
			$NFCancelada = true;
		}else{
			$NFCancelada = false;
		}

		$this->pNotaFiscal = new NotaFiscal($Almoxarifado, $AnoNF, $CodNF, $NFNota, $NFSerie, $NFCancelada, $Ulat );

		if(is_null($Ulat)){
			$db->disconnect();
			EmailErro($GLOBALS["NomePrograma"], __FILE__, __LINE__,"Nota Fiscal não foi encontrada.\n\nSQL: ".$Sql);
			exit();
		}

		# Pega os dados das Movimentações após a nota fiscal #
		$Sql  = "
				SELECT
					A.DMOVMAMOVI, A.CMOVMACODI, A.AMOVMAQTDM, A.VMOVMAVALO,
					C.FTIPMVTIPO, C.ETIPMVDESC, F.CREQMACODI, F.AREQMAANOR,
					G.AENTNFNOTA, G.AENTNFSERI, G.FENTNFCANC, A.CMOVMACODT,
					C.CTIPMVCODI, A.AMOVMAANOM,
					"/* movimentação finalizada ? */."
					CASE
						WHEN (
							( A.CTIPMVCODI <> 2 AND H.CTIPSRCODI = 5 ) "/* mov. baixada que não é devolução interna ?

								possui mov. correspondente ?
								NOTAR que movimentações 6 não necessitam ser finalizadas mas ficam com FMOVMACORR = 'S'.
								NOTAR que movimentações canceladas por 31 ficam com FMOVMACORR = 'S' tanto quando é finalizada quanto quando é cancelado
							*/."
							OR ( A.FMOVMACORR = 'S' AND A.CMOVMACOD1 IS NOT NULL AND A.CTIPMVCODI NOT IN (6, 12, 13, 15, 30) )
							OR (
								A.CTIPMVCODI IN (12, 13, 15, 30)
								AND A.FMOVMACORR = 'S'
								AND (A.CMOVMACODI, A.AMOVMAANOM, A.CALMPOCODI) NOT IN (
									SELECT MM1.CMOVMACOD1, MM1.AMOVMAANO1, MM1.CALMPOCOD1
									FROM SFPC.TBMOVIMENTACAOMATERIAL MM1
									WHERE
										MM1.CALMPOCODI = A.CALMPOCODI
										AND MM1.CTIPMVCODI = 31
								)
							)
						)
						THEN 'S'
						ELSE 'N'
						END
					AS FINALIZADO,
					A.CMOVMACOD1, A.AMOVMAANO1, G.AENTNFANOE, G.CENTNFCODI, F.CREQMASEQU, G.tentnfulat, A.CALMPOCOD1
				FROM
					SFPC.TBTIPOMOVIMENTACAO C,
					SFPC.TBARMAZENAMENTOMATERIAL B,
					SFPC.TBLOCALIZACAOMATERIAL LM,
					SFPC.TBMOVIMENTACAOMATERIAL A
						LEFT OUTER JOIN SFPC.TBENTRADANOTAFISCAL  G ON (
							A.CALMPOCODI = G.CALMPOCODI
							AND A.AENTNFANOE = G.AENTNFANOE
							AND A.CENTNFCODI = G.CENTNFCODI
						)
						LEFT OUTER JOIN SFPC.TBREQUISICAOMATERIAL F ON (
							A.CREQMASEQU = F.CREQMASEQU
						)
						LEFT OUTER JOIN SFPC.TBSITUACAOREQUISICAO H ON (
							F.CREQMASEQU = H.CREQMASEQU
							AND H.TSITREULAT = (
								SELECT MAX(TSITREULAT)
								FROM SFPC.TBSITUACAOREQUISICAO SIT
								WHERE SIT.CREQMASEQU = F.CREQMASEQU) AND CTIPSRCODI <> 6
							)
				WHERE
					A.CALMPOCODI = ".$Almoxarifado."
					AND A.CMATEPSEQU = ".$Material."
					AND (
						A.TMOVMAULAT >= '$Ulat'
						OR A.DMOVMAMOVI > '$Ulat' "/*
							# Verificar data de movimentacao para casos em que o Ulat é alterado
							(exemplo, na ferramenta de substituição de materiais).
							*/."
					)
					AND A.CALMPOCODI = LM.CALMPOCODI
					AND LM.CLOCMACODI = B.CLOCMACODI
					AND A.CMATEPSEQU = B.CMATEPSEQU
					AND A.CTIPMVCODI = C.CTIPMVCODI
					AND (A.FMOVMASITU IS NULL OR A.FMOVMASITU = 'A') "/*Apresentar só as movimentações ativas*/."
			ORDER BY A.DMOVMAMOVI DESC, A.CMOVMACODI DESC
		";

		$res  = $db->query($Sql);
		if( PEAR::isError($res) ){
			$db->disconnect();
			EmailErroSQL($GLOBALS["NomePrograma"], __FILE__, __LINE__, "Erro em SQL no construtor da classe MovimentacoesCancelamentoNota", $Sql, $res);
			exit();
		}
		$rows = $res->numRows();
		for($i=0; $i< $rows; $i++){
			$Linha = $res->fetchRow();
			$DataMovimentacao  = DataBarra($Linha[0]);
			$CodMovimentacao   = $Linha[1];
			$QtdeMovimentacao  = converte_quant(sprintf("%01.2f",str_replace(",",".",$Linha[2])));
			$ValorMovimentacao = converte_valor_estoques(sprintf("%01.4f",str_replace(",",".",$Linha[3])));
			$TipoMovimentacao  = $Linha[4];
			$DescMovimentacao = $Linha[5];
			$Requ    		= $Linha[6];
			$RequisicaoSequ  	= $Linha[19];
			$AnoRequisicao 		= $Linha[7];
			$NotaNumero    		= $Linha[8];
			$NotaSerie     		= $Linha[9];
			$NotaAno     		= $Linha[17];
			$NotaCod     		= $Linha[18];
			$NotaUlat     		= $Linha[20];

			$NotaCancelada 		= $Linha[10];
			if( $NotaCancelada == 'S'){
				$NotaCancelada = true;
			}else{
				$NotaCancelada = false;
			}
			$CodMovimentacaoT 		= $Linha[11];
			$CodTipoMov 		= $Linha[12];
			$Ano = $Linha[13];
			$Finalizado			= $Linha[14];
			if($Finalizado == "S"){
				$Finalizado = true;
			}else{
				$Finalizado = false;
			}
			$CodMovimentacaoCorresp	= $Linha[15];
			$AnoMovimentacaoCorresp	= $Linha[16];
			$AlmoxMovCorresp = $Linha[21];

			if(!is_null($NotaNumero)){
				$notaFiscal = new NotaFiscal($Almoxarifado, $NotaAno, $NotaCod, $NotaNumero, $NotaSerie, $NotaCancelada, $NotaUlat);
			}else{
				$notaFiscal = null;
			}
			if(!is_null($Requ)){
				$requisicao = new Requisicao($RequisicaoSequ, $AnoRequisicao ,$Requ);
			}else{
				$requisicao = null;
			}


			$this->adicionarMovimentacaoPorValores(
				$Almoxarifado, $Ano, $CodMovimentacao, $CodMovimentacaoT, $CodTipoMov ,
				$DataMovimentacao, $TipoMovimentacao, $Material, $QtdeMovimentacao, $ValorMovimentacao,
				$Finalizado, $CodMovimentacaoCorresp, $AnoMovimentacaoCorresp, $AlmoxMovCorresp,
				$requisicao, $notaFiscal
			);

		}
		//$this->ocultarCanceladas();

		//$this->removerOcultas();
	}
	# Verifica se a nota fiscal foi criada antes do último inventário. Se houver, cancelamento não deve ser permitido.
	public function notaFiscalAntesDoInventario(){

		if(is_null($this->existeAntesDoInventario)){
			$result = false;

			$db = $GLOBALS["bancoSessao"];

			$Sql  = "
				SELECT COUNT(B.TINVCOFECH)
				FROM SFPC.TBINVENTARIOCONTAGEM B
			 		INNER JOIN SFPC.TBLOCALIZACAOMATERIAL C
			    	ON
							B.CLOCMACODI = C.CLOCMACODI
			   			AND C.CALMPOCODI = ".$this->getNotaFiscal()->getAlmoxarifado()."
			 	WHERE
					(B.AINVCOANOB,B.AINVCOSEQU) = (
						SELECT DISTINCT A.AINVCOANOB ,MAX(A.AINVCOSEQU)
			      FROM SFPC.TBINVENTARIOCONTAGEM A
			      WHERE
							A.CLOCMACODI = B.CLOCMACODI
							AND A.AINVCOANOB = (
								SELECT MAX(AINVCOANOB)
			        	FROM SFPC.TBINVENTARIOCONTAGEM
			        	WHERE CLOCMACODI = B.CLOCMACODI
							)
						GROUP BY A.AINVCOANOB
					)
			   	AND B.TINVCOFECH >= '".$this->getNotaFiscal()->getUlat()."'
			";

			$res  = $db->query($Sql);
			if( PEAR::isError($res) ){
				$db->disconnect();
				EmailErroSQL($GLOBALS["NomePrograma"], __FILE__, __LINE__, "Erro em SQL na classe MovimentacoesCancelamentoNota, função notaFiscalAntesDoInventario.", $Sql, $res);
				exit();
			}
			$Linha = $res->fetchRow();
			$quantidadeInventarios = $Linha[0];

			return ($quantidadeInventarios!=0);
		} else {
			return $this->existeAntesDoInventario;
		}
	}
	# Verifica se almoxarifado, ano e material são iguais, e se as 2 movimentações são diferentes
	private function _almoxAnoMaterialIguais($movimentacao1, $movimentacao2){
		$result = false;
		if(
			( $movimentacao2->getAlmoxarifado() == $movimentacao1->getAlmoxarifado() ) and
			( $movimentacao2->getAno() == $movimentacao1->getAno() ) and
			( $movimentacao2->getMaterial() == $movimentacao1->getMaterial() ) and
			( !$movimentacao1->igual($movimentacao2) )
		){
			$result = true;
		}
		return $result;
	}

	# Verifica e oculta um determinado Movimentacao e seus cancelamentos, caso ele foi cancelado,
	# usando as regras da ferramenta de cancelamento de nota fiscal (CadNotaFiscalCancelar.php).
	function ocultarCanceladoNotaFiscal($movimentacao){
		$tipoMovimentacao = $movimentacao->getTipo();
		$noMovimentacoes = $this->getNoMovimentacoes();
		$movimentacao2 = null;
		$quantidadeMovimentacao = $movimentacao->getQtdeMaterial();
		$quantidadeMovimentaoChk = 0;
		$falhouChecagem = false;

		switch($tipoMovimentacao){
			case 3: case 7:
 				for($itrMov =$noMovimentacoes-1; $itrMov >=0; $itrMov--){
					$movimentacao2 = $this->getMovimentacao($itrMov);
					$tipoMovimentacao2 = $movimentacao2->getTipo();
					$quantidadeMovimentacao2 = $movimentacao2->getQtdeMaterial();
					if(
						( $movimentacao2->getTipo() == 8 ) and
						( $this->_almoxAnoMaterialIguais( $movimentacao , $movimentacao2 ) ) and
						( $movimentacao->getNotaFiscal()->igual( $movimentacao2->getNotaFiscal() ) )
					){
						$quantidadeMovimentaoChk += $quantidadeMovimentacao2;
					}
				}
				if( $quantidadeMovimentacao > $quantidadeMovimentaoChk){
					$falhouChecagem = true;
				}
				break;
			case 8:
				$quantidadeMovimentaoChk = 0;
				for($itrMov =$noMovimentacoes-1; $itrMov >=0; $itrMov--){
					$movimentacao2 = $this->getMovimentacao($itrMov);
					$tipoMovimentacao2 = $movimentacao2->getTipo();
					$quantidadeMovimentacao2 = $movimentacao2->getQtdeMaterial();
					if($movimentacao2->getTipo()==3){
					}
					if(
						(
							( $movimentacao2->getTipo() == 3 ) or
							( $movimentacao2->getTipo() == 7 )
						) and
						( $this->_almoxAnoMaterialIguais($movimentacao,$movimentacao2) ) and
						( $movimentacao->getNotaFiscal()->igual( $movimentacao2->getNotaFiscal() ) )
					){
						$quantidadeMovimentaoChk += $quantidadeMovimentacao2;
					}
				}
				if( $quantidadeMovimentacao > $quantidadeMovimentaoChk){
					$falhouChecagem = true;
				}
				break;
			case 4: case 20: case 22:
				$quantidadeMovimentaoChk = 0;
				for($itrMov =$noMovimentacoes-1; $itrMov >=0; $itrMov--){
					$movimentacao2 = $this->getMovimentacao($itrMov);
					$tipoMovimentacao2 = $movimentacao2->getTipo();
					$quantidadeMovimentacao2 = $movimentacao2->getQtdeMaterial();
					if(
						(
							( $movimentacao2->getTipo() == 4 ) or
							( $movimentacao2->getTipo() == 20 ) or
							( $movimentacao2->getTipo() == 22 ) or
							( $movimentacao2->getTipo() == 2 ) or
							( $movimentacao2->getTipo() == 18 ) or
							( $movimentacao2->getTipo() == 19 ) or
							( $movimentacao2->getTipo() == 21 )
						) and
						( $this->_almoxAnoMaterialIguais($movimentacao,$movimentacao2) ) and
						( $movimentacao->getRequisicao()->igual($movimentacao2->getRequisicao()) )
					){
						if(
							( $movimentacao2->getTipo() == 4 ) or
							( $movimentacao2->getTipo() == 20 ) or
							( $movimentacao2->getTipo() == 22 )
						){
							$quantidadeMovimentaoChk -= $quantidadeMovimentacao2;
						} else{
							$quantidadeMovimentaoChk += $quantidadeMovimentacao2;
						}
					}
				}
				if( $quantidadeMovimentacao > $quantidadeMovimentaoChk){
					$falhouChecagem = true;
				}
				break;
			case 2: case 19: case 21:
				$quantidadeMovimentaoChk = 0;
				for($itrMov =$noMovimentacoes-1; $itrMov >=0; $itrMov--){
					$movimentacao2 = $this->getMovimentacao($itrMov);
					$requisicaoCancelada = false;
					if(
						(
							( $movimentacao2->getTipo() == 4 ) or
							( $movimentacao2->getTipo() == 20 ) or
							( $movimentacao2->getTipo() == 22 ) or
							( $movimentacao2->getTipo() == 2 ) or
							( $movimentacao2->getTipo() == 18 ) or
							( $movimentacao2->getTipo() == 19 ) or
							( $movimentacao2->getTipo() == 21 )
						) and
						( $this->_almoxAnoMaterialIguais($movimentacao,$movimentacao2) ) and
						( $movimentacao->getRequisicao()->igual($movimentacao2->getRequisicao()) )
					){
						if(
							( $movimentacao2->getTipo() == 2 ) or
							( $movimentacao2->getTipo() == 19 ) or
							( $movimentacao2->getTipo() == 21 )
						){
							$quantidadeMovimentaoChk -= $movimentacao2->getQtdeMaterial();
						}else if ($movimentacao2->getTipo() == 18){
							$requisicaoCancelada = true;
						} else{
							$quantidadeMovimentaoChk += $movimentacao2->getQtdeMaterial();
						}
					}
				}
				if( ($quantidadeMovimentacao > $quantidadeMovimentaoChk) and (!$requisicaoCancelada) ){
					$falhouChecagem = true;
				}
				break;
			case 18:
				// movimentação é checada em CadNotaFiscalMaterialCancelar.php, mas a checagem é descartada e não é gerado erro
				break;
			case 6:
				$quantidadeMovimentaoChk = 0;
				for($itrMov =$noMovimentacoes-1; $itrMov >=0; $itrMov--){
					$movimentacao2 = $this->getMovimentacao($itrMov);
					$tipoMovimentacao2 = $movimentacao2->getTipo();
					$quantidadeMovimentacao2 = $movimentacao2->getQtdeMaterial();
					if(
						(
							( $movimentacao2->getTipo() == 31 ) or
							(
								($movimentacao2->getTipo() == 13) and
								( $movimentacao2->mesmaMovAssociada($movimentacao) )
							)
						) and
						( $this->_almoxAnoMaterialIguais($movimentacao,$movimentacao2) )
					){
						if(
							( $movimentacao2->getTipo() == 31 )
						){
							#checar se há movimentação 13 com a movimentação associada igual a da 31
							for($itrMov3 =$noMovimentacoes-1; $itrMov3 >=0; $itrMov3--){
								$movimentacao3 = $this->getMovimentacao($itrMov3);
								$tipoMovimentacao3 = $movimentacao3->getTipo();
								if(
									(
											($movimentacao3->getTipo() == 13) and
											($movimentacao3->getCodMovAssociada() == $movimentacao2->getCodMovAssociada())
									) and
									( $this->_almoxAnoMaterialIguais($movimentacao,$movimentacao3) )
								){
									if (
										( $movimentacao3->mesmaMovAssociada($movimentacao2) )
									){
										$quantidadeMovimentaoChk -= $quantidadeMovimentacao2;
									}else{
										# para replicar regra de negócio de CadNotaFiscalMaterialCancelar.php, a linha abaixo deveria ser
										# executada. porém, não faz sentido subtrair entrada (31) de entrada (6)
										//$quantidadeMovimentaoChk += $quantidadeMovimentacao2;
									}
								}
							}
						} else{
							$quantidadeMovimentaoChk += $quantidadeMovimentacao2;
						}
					}
				}
				if( $quantidadeMovimentacao > $quantidadeMovimentaoChk){
					$falhouChecagem = true;
				}
				break;
			case 13:
				$quantidadeMovimentaoChk = 0;
				for($itrMov =$noMovimentacoes-1; $itrMov >=0; $itrMov--){
					$movimentacao2 = $this->getMovimentacao($itrMov);
					if(
						(
							( $movimentacao2->getTipo() == 13 ) or
							( $movimentacao2->getTipo() == 6 ) or
							(
								($movimentacao2->getTipo() == 31) and
								( $movimentacao2->isMovAssociada($movimentacao) )
							)
						) and
						( $this->_almoxAnoMaterialIguais($movimentacao,$movimentacao2) )
					){
						if(
							( $movimentacao2->getTipo() == 13 )
						){
							$quantidadeMovimentaoChk -= $movimentacao2->getQtdeMaterial();
						} else{
							$quantidadeMovimentaoChk += $movimentacao2->getQtdeMaterial();
						}
					}
				}
				if($quantidadeMovimentacao > $quantidadeMovimentaoChk) {
					$falhouChecagem = true;
				}
				break;
			case 12:
				$quantidadeMovimentaoChk = 0;
				for($itrMov =$noMovimentacoes-1; $itrMov >=0; $itrMov--){
					$movimentacao2 = $this->getMovimentacao($itrMov);
					if(
						(
							( $movimentacao2->getTipo() == 9 ) or
							(
								($movimentacao2->getTipo() == 31) and
								( $movimentacao2->isMovAssociada($movimentacao) )
							)
						) and
						( $this->_almoxAnoMaterialIguais($movimentacao,$movimentacao2) )
					){
						if( $movimentacao2->getTipo() == 31 ){

							$quantidadeMovimentaoChk += $movimentacao2->getQtdeMaterial();
						} else if( $movimentacao2->getTipo() == 9 ){
							#checar se há movimentação 13 com a movimentação associada igual a da 31
							for($itrMov3 =$noMovimentacoes-1; $itrMov3 >=0; $itrMov3--){
								$movimentacao3 = $this->getMovimentacao($itrMov3);
								if(
									($movimentacao3->getTipo() == 13) and
									( $movimentacao->isMovAssociada($movimentacao3) ) and
									( $movimentacao3->isMovAssociada($movimentacao2) )
								){
										$quantidadeMovimentaoChk += $movimentacao2->getQtdeMaterial();
								}
							}
						}
					}
				}
				if ($quantidadeMovimentacao > $quantidadeMovimentaoChk){
					$falhouChecagem = true;
				}
				break;
			case 9:
				$quantidadeMovimentaoChk = 0;
				for($itrMov =$noMovimentacoes-1; $itrMov >=0; $itrMov--){
					$movimentacao2 = $this->getMovimentacao($itrMov);
					if(
						(
							( $movimentacao2->getTipo() == 9 ) or
							( $movimentacao2->getTipo() == 31 ) or
							( $movimentacao2->getTipo() == 12 )
						) and
						( $this->_almoxAnoMaterialIguais($movimentacao,$movimentacao2) )
					){
						if(
							( $movimentacao2->getTipo() == 9 )
						){
							$quantidadeMovimentaoChk -= $movimentacao2->getQtdeMaterial();
						}else if ($movimentacao2->getTipo() == 31){
							#checar se há movimentação 13 com a movimentação associada igual a da 31
							for($itrMov3 =$noMovimentacoes-1; $itrMov3 >=0; $itrMov3--){
								$movimentacao3 = $this->getMovimentacao($itrMov3);
								$tipoMovimentacao3 = $movimentacao3->getTipo();
								if(
									($movimentacao3->getTipo() == 12) and
									( $movimentacao3->mesmaMovAssociada($movimentacao2) ) and
									( $this->_almoxAnoMaterialIguais($movimentacao,$movimentacao3) )
								){
									$quantidadeMovimentaoChk -= $movimentacao2->getQtdeMaterial();
								}
							}
						} else{
							$quantidadeMovimentaoChk += $movimentacao2->getQtdeMaterial();
						}
					}
				}
				if($quantidadeMovimentacao > $quantidadeMovimentaoChk){
					$falhouChecagem = true;
				}
				break;
			case 10:
				$quantidadeMovimentaoChk = 0;
				$falhouChecagem = true;
				for($itrMov =$noMovimentacoes-1; $itrMov >=0; $itrMov--){
					$movimentacao2 = $this->getMovimentacao($itrMov);
					if(
						( $movimentacao2->getTipo() == 27 ) and
						( $movimentacao2->isMovAssociada($movimentacao)  )
					){
						$falhouChecagem = false;
					}
				}
				break;
			case 11:
				$quantidadeMovimentaoChk = 0;
				for($itrMov =$noMovimentacoes-1; $itrMov >=0; $itrMov--){
					$movimentacao2 = $this->getMovimentacao($itrMov);
					if(
						(
							( $movimentacao2->getTipo() == 11 ) or
							( $movimentacao2->getTipo() == 31 ) or
							( $movimentacao2->getTipo() == 15 )
						) and
						( $this->_almoxAnoMaterialIguais($movimentacao,$movimentacao2) )
					){
						if(
							( $movimentacao2->getTipo() == 11 )
						){
							$quantidadeMovimentaoChk -= $movimentacao2->getQtdeMaterial();
						}else if ($movimentacao2->getTipo() == 31){
							#checar se há movimentação 13 com a movimentação associada igual a da 31
							for($itrMov3 =$noMovimentacoes-1; $itrMov3 >=0; $itrMov3--){
								$movimentacao3 = $this->getMovimentacao($itrMov3);
								$tipoMovimentacao3 = $movimentacao3->getTipo();
								if(
									( $movimentacao3->getTipo() == 15 ) and
									( $movimentacao3->mesmaMovAssociada($movimentacao2) ) and
									( $this->_almoxAnoMaterialIguais($movimentacao,$movimentacao3) )
								){
									$quantidadeMovimentaoChk -= $movimentacao2->getQtdeMaterial();
								}
							}
						} else{
							$quantidadeMovimentaoChk += $movimentacao2->getQtdeMaterial();
						}
					}
				}
				if($quantidadeMovimentacao > $quantidadeMovimentaoChk){
					$falhouChecagem = true;
				}
				break;
			case 15:
				$quantidadeMovimentaoChk = 0;
				for($itrMov =$noMovimentacoes-1; $itrMov >=0; $itrMov--){
					$movimentacao2 = $this->getMovimentacao($itrMov);
					if(
						(
							( $movimentacao2->getTipo() == 15 ) or
							( $movimentacao2->getTipo() == 11 ) or
							(
								( $movimentacao2->getTipo() == 31 ) and
								( $movimentacao2->isMovAssociada($movimentacao) )
							)
						) and
						( $this->_almoxAnoMaterialIguais($movimentacao,$movimentacao2) )
					){
						if(
							( $movimentacao2->getTipo() == 15 )
						){
							$quantidadeMovimentaoChk -= $movimentacao2->getQtdeMaterial();
						} else{
							$quantidadeMovimentaoChk += $movimentacao2->getQtdeMaterial();
						}
					}
				}
				if($quantidadeMovimentacao > $quantidadeMovimentaoChk){
					$falhouChecagem = true;
				}
				break;
			case 14: case 16: case 17: case 23: case 24: case 37:
				$quantidadeMovimentaoChk = 0;
				$falhouChecagem = true;
				for($itrMov =$noMovimentacoes-1; $itrMov >=0; $itrMov--){
					$movimentacao2 = $this->getMovimentacao($itrMov);
					if(
						( $movimentacao2->isMovAssociada($movimentacao) )
					){
						$falhouChecagem = false;
					}
				}
				break;
			case 26:
				$quantidadeMovimentaoChk = 0;
				$falhouChecagem = true;
				for($itrMov =$noMovimentacoes-1; $itrMov >=0; $itrMov--){
					$movimentacao2 = $this->getMovimentacao($itrMov);
					if(
						( $movimentacao->isMovAssociada($movimentacao2) )
					){
						$falhouChecagem = false;
					}
				}
				break;
			case 27:
				$quantidadeMovimentaoChk = 0;
				$falhouChecagem = true;
				for($itrMov =$noMovimentacoes-1; $itrMov >=0; $itrMov--){
					$movimentacao2 = $this->getMovimentacao($itrMov);
					if(
						( $movimentacao->isMovAssociada($movimentacao2) )
					){
						$falhouChecagem = false;
					}
				}
				break;
			case 29:
				$quantidadeMovimentaoChk = 0;
				for($itrMov =$noMovimentacoes-1; $itrMov >=0; $itrMov--){
					$movimentacao2 = $this->getMovimentacao($itrMov);
					if(
						(
							( $movimentacao2->getTipo() == 29 ) or
							( $movimentacao2->getTipo() == 30 ) or
							( $movimentacao2->getTipo() == 31 )
						) and
						( $this->_almoxAnoMaterialIguais($movimentacao,$movimentacao2) )
					){
						if(
							( $movimentacao2->getTipo() == 29 )
						){
							$quantidadeMovimentaoChk -= $movimentacao2->getQtdeMaterial();
						}else if ($movimentacao2->getTipo() == 31){
							#checar se há movimentação 30 com a movimentação associada igual a da 31
							for($itrMov3 =$noMovimentacoes-1; $itrMov3 >=0; $itrMov3--){
								$movimentacao3 = $this->getMovimentacao($itrMov3);
								$tipoMovimentacao3 = $movimentacao3->getTipo();
								if(
									( $movimentacao3->getTipo() == 30 ) and
									( $movimentacao3->mesmaMovAssociada($movimentacao2) ) and
									( $this->_almoxAnoMaterialIguais($movimentacao,$movimentacao3) )
								){
									$quantidadeMovimentaoChk -= $movimentacao2->getQtdeMaterial();
								}
							}
						} else{
							$quantidadeMovimentaoChk += $movimentacao2->getQtdeMaterial();
						}
					}
				}
				if($quantidadeMovimentacao > $quantidadeMovimentaoChk){
					$falhouChecagem = true;
				}
				break;
			case 30:
				$quantidadeMovimentaoChk = 0;
				for($itrMov =$noMovimentacoes-1; $itrMov >=0; $itrMov--){
					$movimentacao2 = $this->getMovimentacao($itrMov);
					if(
						(
							( $movimentacao2->getTipo() == 29 ) or
							( $movimentacao2->getTipo() == 30 ) or
							(
								($movimentacao2->getTipo() == 31) and
								( $movimentacao2->isMovAssociada($movimentacao) )
							)
						) and
						( $this->_almoxAnoMaterialIguais($movimentacao,$movimentacao2) )
					){
						if( $movimentacao2->getTipo() == 30 ){
							$quantidadeMovimentaoChk -= $movimentacao2->getQtdeMaterial();
						}else{
							$quantidadeMovimentaoChk += $movimentacao2->getQtdeMaterial();
						}
					}
				}
				if ($quantidadeMovimentacao > $quantidadeMovimentaoChk){
					$falhouChecagem = true;
				}
				break;
			case 31:
				$quantidadeMovimentaoChk = 0;
				for($itrMov =$noMovimentacoes-1; $itrMov >=0; $itrMov--){
					$movimentacao2 = $this->getMovimentacao($itrMov);
					if(
						(
							( $movimentacao2->getTipo() == 31 ) or
							( $movimentacao2->getTipo() == 12 ) or
							( $movimentacao2->getTipo() == 13 ) or
							( $movimentacao2->getTipo() == 15 ) or
							( $movimentacao2->getTipo() == 30 )
						) and
						( $this->_almoxAnoMaterialIguais($movimentacao,$movimentacao2) )
					){
						if( $movimentacao2->getTipo() == 31 ){
							$quantidadeMovimentaoChk -= $movimentacao2->getQtdeMaterial();
						}else{
							$quantidadeMovimentaoChk += $movimentacao2->getQtdeMaterial();
						}
					}
				}
				if ($quantidadeMovimentacao > $quantidadeMovimentaoChk){
					$falhouChecagem = true;
				}
				break;
			case 32:
				$quantidadeMovimentaoChk = 0;
				$falhouChecagem = false;
				for($itrMov =$noMovimentacoes-1; $itrMov >=0; $itrMov--){
					$movimentacao2 = $this->getMovimentacao($itrMov);
					if(
						( $movimentacao2->getTipo() == 27 ) and
						( $movimentacao2->isMovAssociada($movimentacao)  )
					){
						$falhouChecagem = true;
					}
				}
				break;
			default:
				//ignorar para outros tipos
				break;
		}
		if(!$falhouChecagem){
			#checagem bem sucedida (movimentação foi cancelada e deve ser ocultada)
			$movimentacao->setOcultar(true);
		}
	}
	# Oculta movimentações canceladas usando regra de
  function ocultarCanceladasNotaFiscal(){
  	$noMovimentacoes = $this->getNoMovimentacoes();

		foreach( $this->pMovimentacoes as $movimentacao ){
			$this->ocultarCanceladoNotaFiscal($movimentacao);
		}
		# encontrar e remover movimentação da criação da nota fiscal
		foreach( $this->pMovimentacoes as $movimentacao ){
			if(
				(!is_null($movimentacao->getNotaFiscal()))
				and ( $this->getNotaFiscal()->getAlmoxarifado() == $movimentacao->getNotaFiscal()->getAlmoxarifado() )
				and ( $this->getNotaFiscal()->getCodigo() == $movimentacao->getNotaFiscal()->getCodigo() )
				and ( $this->getNotaFiscal()->getAno() == $movimentacao->getNotaFiscal()->getAno() )
				and ($movimentacao->getTipo() == 3)
			){
				$movimentacao->setOcultar(true);
			}
		}


  }

}

###############################################
?>
