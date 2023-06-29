<?php
/**
 * Portal da DGCO
 *
 * PHP version 5.2.5
 *
 * LICENSE: This source file is subject to version 3.01 of the PHP license
 * that is available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_01.txt. If you did not receive a copy of
 * the PHP License and are unable to obtain it through the web, please
 * send a note to license@php.net so we can mail you a copy immediately.
 *
 * @category  Pitang Registro Preço
 * @package   registropreco
 * @author    Pitang Agile TI <contato@pitang.com>
 * @copyright 2014 EMPRESA MUNICIPAL DE INFORMÁTICA - EMPREL
 * @license   http://www.php.net/license/3_01.txt PHP License 3.01
 *
 * @version    GIT: EMPREL-SAD-PORTAL-COMPRAS-REGISTRO-PRECO-BL-FUNC-20160603-1050
 *
 */

 // 220038--
 
if (! @require_once dirname(__FILE__) . "/../bootstrap.php") {
    throw new Exception("Error Processing Request - Bootstrap", 1);
}

Seguranca();

AddMenuAcesso('/estoques/CadIncluirItem.php');
AddMenuAcesso('/estoques/CadItemDetalhe.php');

class RegistroPreco_UI_CadIncluirIntencaoRegistroPreco extends UI_Abstrata
{

    /**
     */
    private function filtroPesquisaValido()
    {
        $retorna = true;

        $dataInicio = $_POST['DataInicioCadastro'];
        $dataFim = $_POST['DataFimCadastro'];

        if (! empty($dataInicio) && ! ClaHelper::validationData($dataInicio)) {
            $this->getTemplate()->MENSAGEM_ERRO = 'Data de início não é válida';
            $this->getTemplate()->block('BLOCO_ERRO', true);
            $retorna = false;
        }

        if (! empty($dataFim) && ! ClaHelper::validationData($dataFim)) {
            $this->getTemplate()->MENSAGEM_ERRO = 'Data de fim não é válida';
            $this->getTemplate()->block('BLOCO_ERRO', true);
            $retorna = false;
        }

        return $retorna;
    }

    /**
     */
    public function __construct()
    {
        $template = new TemplatePortal("templates/CadIncluirIntencaoRegistroPreco.html");
        $this->setTemplate($template);

        $dataMes = DataMes();

        $this->getTemplate()->VALOR_NUMERO_INTENCAO = '';
        $this->getTemplate()->VALOR_DATA_INICIO_CADASTRO = $dataMes[0];
        $this->getTemplate()->VALOR_DATA_FIM_CADASTRO = $dataMes[1];

        // Caso o usuário clicou em voltar na tela de manter recarrega a pesquisa
        if (isset($_SESSION['voltarPesquisa'])) {
            $ultimoFiltro = $_SESSION['ultimoFiltro'];

            $_POST['NumeroIntencao'] = $ultimoFiltro['NumeroIntencao'];
            $_POST['DataInicioCadastro'] = $ultimoFiltro['DataInicioCadastro'];
            $_POST['DataFimCadastro'] = $ultimoFiltro['DataFimCadastro'];

            unset($_SESSION['voltarPesquisa']);

            $this->proccessPesquisar();
        }
    }

    /**
     */
    public function proccessPesquisar()
    {
        if (! $this->filtroPesquisaValido()) {
            $this->getTemplate()->MENSAGEM_ERRO = ExibeMensStr('Não existem dados para os filtros informados', 1, 1);
            $this->getTemplate()->block('BLOCO_ERRO', true);
            return;
        }
        $sequencialIntencao = null;
        $anoIntencao = null;
        if (isset($_POST['NumeroIntencao']) && ! empty($_POST['NumeroIntencao'])) {
            // Número intenção
            $numeroIntencao = explode('/', $_POST['NumeroIntencao']);
            $sequencialIntencao = (isset($numeroIntencao[0]) && $numeroIntencao[0] != "") ? (int)$numeroIntencao[0] : null;
            $anoIntencao = (isset($numeroIntencao[1]) && $numeroIntencao[1] != "") ? $numeroIntencao[1] : null;
        }

        // Data inicial
        $dataInicioCadastro = (! empty($_POST['DataInicioCadastro'])) ? ClaHelper::converterDataBrParaBanco($_POST['DataInicioCadastro']) : null;

        // Data final
        $dataFimCadastro = (! empty($_POST['DataFimCadastro'])) ? ClaHelper::converterDataBrParaBanco($_POST['DataFimCadastro']) : null;
        $repositorio = new Negocio_Repositorio_IntencaoRegistroPreco();

        // $resultSet = $repositorio->getIntencaoByDataInicioAndDataFimAndGrupoUsuario($sequencialIntencao, $anoIntencao, $dataInicioCadastro, $dataFimCadastro, $_SESSION['CentroCusto']);
        $cintrpsequ = is_null($sequencialIntencao) ? $sequencialIntencao : new Negocio_ValorObjeto_Cintrpsequ($sequencialIntencao);
        $cintrpsano = is_null($anoIntencao) ? $anoIntencao : new Negocio_ValorObjeto_Cintrpsano($anoIntencao);
        $resultSet = $repositorio->listarTodasIRPRespondidas($dataInicioCadastro, $dataFimCadastro, $cintrpsequ, $cintrpsano, $_SESSION['CentroCusto']);
        $row = null;
        foreach ($resultSet as $row) {
            $this->getTemplate()->VALOR_NUMERO_INTENCAO_ITEM = str_pad($row->cintrpsequ, 4, '0', STR_PAD_LEFT) . '/' . $row->cintrpsano;
            $this->getTemplate()->VALOR_DATA_CADASTRO_ITEM = ClaHelper::converterDataBancoParaBr($row->tintrpdcad, true);
            $this->getTemplate()->VALOR_OBJETO_ITEM = $row->xintrpobje;
            $this->getTemplate()->VALOR_PROGRAMA_ORIGEM = $_GET['ProgramaOrigem'];

            $this->getTemplate()->block("BLOCO_LISTAGEM_ITEM");
        }

        if (count($resultSet) > 0) {
            $this->getTemplate()->block("BLOCO_HEADER_LISTAGEM_ITEM");
            $this->getTemplate()->block("BLOCO_RESULTADO_PEQUISA");
        } else {
            $this->getTemplate()->MENSAGEM_ERRO = ExibeMensStr('Não existem dados para os filtros informados', 1, 1);
            $this->getTemplate()->block('BLOCO_ERRO', true);
        }

        // Último filtro realizado
        $_SESSION['ultimoFiltro'] = array(
            'NumeroIntencao' => $_POST['NumeroIntencao'],
            'DataInicioCadastro' => $_POST['DataInicioCadastro'],
            'DataFimCadastro' => $_POST['DataFimCadastro']
        );
    }

    /**
     */
    public function proccessPrincipal()
    {
        $_SESSION['CentroCusto'] = isset($_GET['CentroCusto']) ? filter_var($_GET['CentroCusto'], FILTER_SANITIZE_NUMBER_INT) : null;

        if (! empty($_POST['NumeroIntencao'])) {
            $this->getTemplate()->VALOR_NUMERO_INTENCAO = $_POST['NumeroIntencao'];
        }

        if (! empty($_POST['DataInicioCadastro'])) {
            $this->getTemplate()->VALOR_DATA_INICIO_CADASTRO = $_POST['DataInicioCadastro'];
        }

        if (! empty($_POST['DataFimCadastro'])) {
            $this->getTemplate()->VALOR_DATA_FIM_CADASTRO = $_POST['DataFimCadastro'];
        }

        if (! empty($_SESSION['mensagemFeedback'])) {
            $this->getTemplate()->MENSAGEM_ERRO = ExibeMensStr($_SESSION['mensagemFeedback'], 1, 0);
            $this->getTemplate()->block('BLOCO_ERRO', true);

            unset($_SESSION['mensagemFeedback']);
        }
    }
}

$gui = new RegistroPreco_UI_CadIncluirIntencaoRegistroPreco();

$botao = ! empty($_POST['Botao']) ? $_POST['Botao'] : 'Principal';

switch ($botao) {
    case 'Pesquisar':
        $gui->proccessPesquisar();
        $gui->proccessPrincipal();
        break;
    case 'Principal':
    default:
        $gui->proccessPrincipal();
        break;
}

echo $gui->getTemplate()->show();
