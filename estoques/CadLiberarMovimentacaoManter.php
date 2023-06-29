<?php

#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadLiberarMovimentacaoManter.php
# Objetivo: Programa de manutenção de liberação de movimentação durante o período de bloqueio.
# Autor:    José Almir <jose.almir@pitang.com>
# Data:     17/11/2014
#-------------------------------------------------------------------------

require_once '../funcoes.php';
require_once 'AbstractCadLiberarMovimentacao.php';

class CadLiberarMovimentacaoManter extends AbstractCadLiberarMovimentacao
{
	private $template;

	public function __construct($template)
	{
		parent::__construct();
		$this->template = $template;
	}

	protected function getTemplate()
	{
		return $this->template;
	}

	private function run()
	{
		$this->frontController();
		return $this->getTemplate()->show();
	}

	private function dadosValidos()
	{
		$dataInicial = $this->variables['post']['DataIni'];
		$dataFinal = $this->variables['post']['DataFim'];
		$sequencialOrgao = $this->variables['post']['Orgao'];

		if (empty($sequencialOrgao)) {
			$GLOBALS['Mensagem'] = ExibeMensStr("<a href='javascript:document.liberarMovimentacaoPesquisar.Orgao.focus();' class='titulo2'>Selecione um órgão</a>", 2, 0);
			return false;
		}

		$MensErro = ValidaData($dataInicial);
		if ($MensErro != "") {
			$GLOBALS['Mensagem'] = ExibeMensStr("<a href='javascript:document.liberarMovimentacaoPesquisar.DataIni.focus();' class='titulo2'>Data inicial inválida</a>", 2, 0);
			return false;
		}

		$MensErro = ValidaData($dataFinal);
		if ($MensErro != "") {
			$GLOBALS['Mensagem'] = ExibeMensStr("<a href='javascript:document.liberarMovimentacaoPesquisar.DataFim.focus();' class='titulo2'>Data final inválida</a>", 2, 0);
			return false;
		}

		if (ValidaData($dataInicial) == '' && ValidaData($dataFinal) == '') {
			$MensErro = ValidaPeriodo($dataInicial, $dataFinal, 0, "liberarMovimentacaoPesquisar");
			if ($MensErro != "") {
				$GLOBALS['Mensagem'] = ExibeMensStr("<a href='javascript:document.liberarMovimentacaoPesquisar.DataIni.focus();' class='titulo2'>Data Final igual ou maior que Data Inicial</a>", 2, 0);
				return false;
			}
		}
		
		if (!$this->dataDentroPeriodoBloqueio($dataInicial, $dataFinal)) {
			$GLOBALS['Mensagem'] = ExibeMensStr("<a href='javascript:document.liberarMovimentacaoPesquisar.DataIni.focus();' class='titulo2'>Período informado está fora do período de bloqueio</a>", 2, 0);
			return false;
		}
		
		if ($this->orgaoComLiberacaoCadastrada($sequencialOrgao)) {
			$GLOBALS['Mensagem'] = ExibeMensStr("<a href='javascript:document.liberarMovimentacaoPesquisar.Orgao.focus();' class='titulo2'>O órgão informado já possui uma liberação cadastrada</a>", 2, 0);
			return false;
		}

		$GLOBALS['Mensagem'] = "";
		return true;
	}

	private function frontController()
	{
		$botao = $this->variables['post']['Botao'];

		switch ($botao) {
			case 'Pesquisar':
				$this->pesquisar();
				break;
			case 'Manter':
				$this->manter();
				break;
			case 'Alterar':
				$this->alterar();
				break;
			case 'Excluir':
				$this->excluir();
				break;
			case 'Voltar':
				$this->voltar();
				break;
			case 'Principal':
			default:
				$this->principal();
		}
	}

	private function voltar()
	{
		$this->variables['post']['Orgao'] = $_SESSION['Orgao'];
		$this->variables['post']['DataIni'] = $_SESSION['DataIni'];
		$this->variables['post']['DataFim'] = $_SESSION['DataFim'];

		$this->pesquisar();
	}

	private function manter()
	{
		$sequencialLiberacao = (int)$this->variables['post']['SequencialLiberacao'];

		$db = Conexao();
		$sql  = " SELECT A.* ";
		$sql .= " FROM SFPC.TBLIBERACAOMOVIMENTACAO A ";
		$sql .= " WHERE A.CLIBMOSEQU = $sequencialLiberacao ";

		$resultado = executarSQL($db, $sql);
		$item = resultObjetoUnico($resultado);

		$dataInicial = new DataHora($item->tlibmodini);
		$dataInicial = $dataInicial->formata('d/m/Y');
		$dataFinal = new DataHora($item->tlibmodfin);
		$dataFinal = $dataFinal->formata('d/m/Y');
		
		if (!empty($GLOBALS['Mensagem'])) {
			$dataInicial = $this->variables['post']['DataIni'];
			$dataFinal = $this->variables['post']['DataFim'];
		}

		$_SESSION['sequencialOrgaoManter'] = $item->corglicodi;
		
		$this->carregarListaOrgaos('BLOCO_LISTA_ORGAO_MANTER', $item->corglicodi);
		$this->iniciarDatas($dataInicial, $dataFinal);
		$this->getTemplate()->SEQUENCIAL_LIBERACAO = $item->clibmosequ;
		$this->getTemplate()->block('BLOCO_MANTER');
	}

	private function alterar()
	{
		$mensagem = '';

		if ($this->dadosValidos()) {
			$sequencialOrgao = $this->variables['post']['Orgao'];
			$dataAlteracao = date('Y-m-d H:i:s');
			$sequencialUsuario = $_SESSION['_cusupocodi_'];
			$sequencialLiberacao = (int)$this->variables['post']['SequencialLiberacao'];

			$dataInicial = new DataHora($this->variables['post']['DataIni']);
			$dataInicial = $dataInicial->formata('Y-m-d') . ' 00:00:00';
			$dataFinal = new DataHora($this->variables['post']['DataFim']);
			$dataFinal = $dataFinal->formata('Y-m-d') . ' 23:59:59';

			$db = Conexao();
			$sql  = " UPDATE SFPC.TBLIBERACAOMOVIMENTACAO ";
			$sql .= " SET CORGLICODI = $sequencialOrgao, TLIBMODINI = '$dataInicial', TLIBMODFIN = '$dataFinal', ";
			$sql .= " 	  CUSUPOCODI = '$sequencialUsuario', TLIBMOULAT = '$dataAlteracao' ";
			$sql .= " WHERE CLIBMOSEQU = $sequencialLiberacao ";

			$resultado = executarSQL($db, $sql);
			$mensagem = ExibeMensStr("Liberação alterada com sucesso", 1, 0);

			$this->variables['post'] = array();
			
			$this->getTemplate()->MENSAGEM_ERRO = $mensagem;
			$this->getTemplate()->block('BLOCO_ERRO', true);
			$this->principal();
		} else {
			$mensagem = $GLOBALS['Mensagem'];

			$this->getTemplate()->MENSAGEM_ERRO = $mensagem;
			$this->getTemplate()->block('BLOCO_ERRO', true);
			$this->manter();
		}
	}

	private function excluir()
	{
		$sequencialLiberacao = (int)$this->variables['post']['SequencialLiberacao'];

		if (!empty($sequencialLiberacao)) {
			$db = Conexao();
			$sql  = " DELETE FROM SFPC.TBLIBERACAOMOVIMENTACAO ";
			$sql .= " WHERE CLIBMOSEQU = $sequencialLiberacao ";

			$resultado = executarSQL($db, $sql);
			$mensagem = ExibeMensStr("Liberação excluída com sucesso", 1, 0);

			$this->variables['post'] = array();
		} else {
			$mensagem = 'Selecione uma liberação para ser excluída';
		}

		$this->getTemplate()->MENSAGEM_ERRO = $mensagem;
		$this->getTemplate()->block('BLOCO_ERRO', true);
		$this->principal();
	}

	private function dadosPesquisaValidos()
	{
		$validos = true;

		$dataInicial = $this->variables['post']['DataIni'];
		$dataFinal = $this->variables['post']['DataFim'];

		$MensErro = ValidaData($dataInicial);
		if ($MensErro != "") {
			mostrarMensagemErroUnica("<a href='javascript:document.liberarMovimentacaoPesquisar.DataIni.focus();' class='titulo2'>Data inicial inválida</a>");
			return false;
		}

		$MensErro = ValidaData($dataFinal);
		if ($MensErro != "") {
			mostrarMensagemErroUnica("<a href='javascript:document.liberarMovimentacaoPesquisar.DataFim.focus();' class='titulo2'>Data final inválida</a>");
			return false;
		}

		if (ValidaData($dataInicial) == '' && ValidaData($dataFinal) == '') {
			$MensErro = ValidaPeriodo($dataInicial, $dataFinal, 0, "liberarMovimentacaoPesquisar");
			if ($MensErro != "") {
				mostrarMensagemErroUnica("<a href='javascript:document.liberarMovimentacaoPesquisar.DataIni.focus();' class='titulo2'>Data Final igual ou maior que Data Inicial</a>");
				return false;
			}
		}

		return $validos;
	}

	private function pesquisar()
	{
		$dadosValidos = $this->dadosPesquisaValidos();

		if ($dadosValidos) {
			$_SESSION['Orgao'] = $this->variables['post']['Orgao'];
			$_SESSION['DataIni'] = $this->variables['post']['DataIni'];
			$_SESSION['DataFim'] = $this->variables['post']['DataFim'];

			$sequencialOrgao = $this->variables['post']['Orgao'];

			$dataInicial = new DataHora($this->variables['post']['DataIni']);
			$dataInicial = $dataInicial->formata('Y-m-d') . ' 00:00:00';
			$dataFinal = new DataHora($this->variables['post']['DataFim']);
			$dataFinal = $dataFinal->formata('Y-m-d') . ' 23:59:59';

			$db = Conexao();
			$sql  = " SELECT A.*, B.EORGLIDESC ";
			$sql .= " FROM SFPC.TBLIBERACAOMOVIMENTACAO A ";
			$sql .= " INNER JOIN SFPC.TBORGAOLICITANTE B ON A.CORGLICODI = B.CORGLICODI ";
			$sql .= " WHERE A.TLIBMODINI >= '$dataInicial' AND A.TLIBMODFIN <= '$dataFinal' ";

			if (!empty($sequencialOrgao)) {
				$sql .= " AND A.CORGLICODI = $sequencialOrgao ";
			}

			$sql .= " ORDER BY A.TLIBMODINI ";

			$resultado = executarSQL($db, $sql);

			while ($resultado->fetchInto($item, DB_FETCHMODE_OBJECT)) {
				$dataInicio = new DataHora($item->tlibmodini);
				$dataInicio = $dataInicio->formata('d/m/Y');
				$dataFim = new DataHora($item->tlibmodfin);
				$dataFim = $dataFim->formata('d/m/Y');
				$periodo = $dataInicio . ' a ' . $dataFim;
					
				$this->getTemplate()->ID_LIBERACAO = $item->clibmosequ;
				$this->getTemplate()->NOME_ORGAO = $item->eorglidesc;
				$this->getTemplate()->PERIODO_LIBERACAO = $periodo;

				$this->getTemplate()->block("BLOCO_ITEM_RESULTADO");
			}

			$bloco = "BLOCO_RESULTADO_PESQUISA";

			if ($resultado->numRows() <= 0) {
				$bloco = "BLOCO_PESQUISA_SEM_RESULTADO";
			}

			$this->getTemplate()->block($bloco);
		} else {
			$this->getTemplate()->MENSAGEM_ERRO = $GLOBALS['Mensagem'];
			$this->getTemplate()->block('BLOCO_ERRO', true);
		}

		$this->principal();
	}

	private function principal()
	{
		$this->carregarListaOrgaos('BLOCO_LISTA_ORGAO', $this->variables['post']['Orgao']);
		$this->iniciarDataLiberacao();
		$this->getTemplate()->block("BLOCO_PESQUISA");
	}

	public static function bootstrap()
	{
		session_start();

		$template = new TemplatePaginaPadrao(
			"templates/CadLiberarMovimentacaoManter.template.html",
			"Estoques > Inventário > Liberar Movimentação > Manter"
		);

		$app = new CadLiberarMovimentacaoManter($template);
		echo $app->run();
		unset($app);
	}
}

CadLiberarMovimentacaoManter::bootstrap();
