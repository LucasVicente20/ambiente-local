<?php

/**
 * Portal da DGCO.
 *
 * PHP version 5.2.5
 *
 * LICENSE: This source file is subject to version 3.01 of the PHP license
 * that is available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_01.txt. If you did not receive a copy of
 * the PHP License and are unable to obtain it through the web, please
 * send a note to license@php.net so we can mail you a copy immediately.
 *
 * @author     Pitang Agile TI <contato@pitang.com>
 * @copyright  2014 EMPRESA MUNICIPAL DE INFORMÁTICA - EMPREL
 * @license    http://www.php.net/license/3_01.txt PHP License 3.01
 *
 * @version   GIT: v1.30.4
 */

#---------------------------------------------------------------------
# Alterado: Pitang Agile TI - Caio Coutinho
# data: 12/03/2019
# Objetivo: Tarefa Redmine 212543
#---------------------------------------------------------------------

// 220038--

if (! require_once dirname(__FILE__) . '/../bootstrap.php') {
    throw new Exception('Error Processing Request - Bootstrap', 1);
}

Seguranca();

AddMenuAcesso('/estoques/CadIncluirItem.php');
AddMenuAcesso('/estoques/CadItemDetalhe.php');

class RegistroPreco_CadRegistroPrecoIntencaoAcompanhar_Dados extends Dados_Abstrata
{

    /**
     *
     * @param Negocio_ValorObjeto_IntencaoRegistroPreco $voIRP
     * @param string $dataInicio
     * @param string $dataFim
     *
     * @return string
     */
    private function sqlSelectIntencaoWithParams(Negocio_ValorObjeto_IntencaoRegistroPreco $voIRP, $dataInicio = null, $dataFim = null)
    {
        $sql = '
            SELECT DISTINCT a.cintrpsequ, a.cintrpsano, a.tintrpdlim, a.xintrpobje, a.fintrpsitu, a.tintrpdcad,
                a.cusupocodi
            FROM
                sfpc.tbintencaoregistropreco a
            INNER JOIN sfpc.tbintencaorporgao b ON a.cintrpsequ = b.cintrpsequ AND a.cintrpsano = b.cintrpsano
            INNER JOIN sfpc.tborgaolicitante c ON b.corglicodi = c.corglicodi
            INNER JOIN sfpc.tbgrupoorgao d ON c.corglicodi = d.corglicodi
            WHERE
                1 = 1
        ';
        
        if ($voIRP->getCintrpsequ() > 0) {
            $sql .= ' AND a.cintrpsequ = ' . $voIRP->getCintrpsequ();
        }
        
        if ($voIRP->getCintrpsano() > 0) {
            $sql .= ' AND a.cintrpsano = ' . $voIRP->getCintrpsano();
        }
        
        if (! is_null($dataInicio) && is_null($dataFim)) {
            $sql .= " AND to_char(a.tintrpdcad, 'YYYY-MM-DD') >= '$dataInicio' ";
        }
        
        if (! is_null($dataFim) && is_null($dataInicio)) {
            $sql .= " AND to_char(a.tintrpdcad, 'YYYY-MM-DD') <= '$dataFim' ";
        }
        
        if (! is_null($dataInicio) && ! is_null($dataFim)) {
            $sql .= " AND to_char(a.tintrpdcad, 'YYYY-MM-DD') >= '$dataInicio' ";
            $sql .= " AND to_char(a.tintrpdcad, 'YYYY-MM-DD') <= '$dataFim' ";
        }
        
        $sql .= ' ORDER BY a.cintrpsano DESC, a.cintrpsequ ASC ';
        
        return $sql;
    }

    /**
     *
     * @param Negocio_ValorObjeto_IntencaoRegistroPreco $voIRP
     * @param string $dataInicio
     * @param string $dataFim
     */
    public function consultarIntencao(Negocio_ValorObjeto_IntencaoRegistroPreco $voIRP, $dataInicio, $dataFim)
    {
        $database = ClaDatabasePostgresql::getConexao();
        $sql = $this->sqlSelectIntencaoWithParams($voIRP, $dataInicio, $dataFim);
        
        return executarSQL($database, $sql);
    }
}

class RegistroPreco_CadRegistroPrecoIntencaoAcompanhar_Adaptacao extends Adaptacao_Abstrata
{

    /**
     *
     * @param int $cintrpsequ
     * @param int $cintrpsano
     * @param string $dataInicio
     * @param string $dataFim
     */
    public function consultarIntencao($cintrpsequ, $cintrpsano, $dataInicio, $dataFim)
    {
        $voIRP = new Negocio_ValorObjeto_IntencaoRegistroPreco($cintrpsequ, $cintrpsano);
        $dao = new RegistroPreco_CadRegistroPrecoIntencaoAcompanhar_Dados();
        
        return $dao->consultarIntencao($voIRP, $dataInicio, $dataFim);
    }
}

class RegistroPreco_CadRegistroPrecoIntencaoAcompanhar_UI extends UI_Abstrata
{
    const CAMINHO_TEMPLATE = 'templates/CadRegistroPrecoIntencaoAcompanhar.html';

    /**
     * [filtroPesquisaValido description].
     *
     * @return [type] [description]
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
     *
     * @param int $sequencialIntencao
     * @param int $anoIntencao
     * @param string $dataInicioCadastro
     * @param string $dataFimCadastro
     */
    private function mapperDataTemplate($sequencialIntencao, $anoIntencao, $dataInicioCadastro, $dataFimCadastro)
    {
        $resultSet = $this->getAdaptacao()->consultarIntencao($sequencialIntencao, $anoIntencao, $dataInicioCadastro, $dataFimCadastro);
        $row = null;
        while ($resultSet->fetchInto($row, DB_FETCHMODE_OBJECT)) {
            $this->getTemplate()->VALOR_NUMERO_INTENCAO_ITEM = substr($row->cintrpsequ + 10000, 1) . '/' . $row->cintrpsano;
            $this->getTemplate()->VALOR_DATA_CADASTRO_ITEM = ClaHelper::converterDataBancoParaBr($row->tintrpdcad);
            $this->getTemplate()->VALOR_DATA_LIMITE_ITEM = ClaHelper::converterDataBancoParaBr($row->tintrpdlim);
            $this->getTemplate()->VALOR_OBJETO_ITEM = $row->xintrpobje;
            $this->getTemplate()->VALOR_SITUACAO_ITEM = ($row->fintrpsitu == 'I') ? 'INATIVA' : 'ATIVA';
            
            $this->getTemplate()->block('BLOCO_LISTAGEM_ITEM');
        }
        
        if ($resultSet->numRows() > 0) {
            $this->getTemplate()->block('BLOCO_HEADER_LISTAGEM_ITEM');
            $this->getTemplate()->block('BLOCO_RESULTADO_PEQUISA');
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
    public function __construct()
    {
        $this->setAdaptacao(new RegistroPreco_CadRegistroPrecoIntencaoAcompanhar_Adaptacao());
        $this->setTemplate(new TemplatePaginaPadrao(self::CAMINHO_TEMPLATE, 'Registro de Preço > Intenção > Acompanhar'));
    }

    /**
     */
    public function plotarTelaInicial()
    {
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
     * [proccessPrincipal description].
     *
     * @param [type] $variablesGlobals
     *            [description]
     *
     * @return [type] [description]
     */
    public function proccessPrincipal()
    {
        $this->plotarTelaInicial();
        if (isset($_POST['NumeroIntencao'])) {
            $this->getTemplate()->VALOR_NUMERO_INTENCAO = $_POST['NumeroIntencao'];
        }
        
        if (isset($_POST['DataInicioCadastro'])) {
            $this->getTemplate()->VALOR_DATA_INICIO_CADASTRO = $_POST['DataInicioCadastro'];
        }
        
        if (isset($_POST['DataFimCadastro'])) {
            $this->getTemplate()->VALOR_DATA_FIM_CADASTRO = $_POST['DataFimCadastro'];
        }
        
        if (isset($_SESSION['mensagemFeedback'])) {
            $this->getTemplate()->MENSAGEM_ERRO = ExibeMensStr($_SESSION['mensagemFeedback'], 1, 0);
            $this->getTemplate()->block('BLOCO_ERRO', true);
            
            unset($_SESSION['mensagemFeedback']);
        }
    }

    /**
     * [proccessPesquisar description].
     *
     * @return [type] [description]
     */
    public function proccessPesquisar()
    {
        if ($this->filtroPesquisaValido()) {
            // Número intenção
            $numeroIntencao = explode('/', $_POST['NumeroIntencao']);
            $sequencialIntencao = (isset($numeroIntencao[0]) && $numeroIntencao[0] != '') ? $numeroIntencao[0] : null;
            $anoIntencao = (isset($numeroIntencao[1]) && $numeroIntencao[1] != '') ? $numeroIntencao[1] : null;
            
            // Data inicial
            $dataInicioCadastro = $_POST['DataInicioCadastro'];
            $dataInicioCadastro = (! empty($dataInicioCadastro)) ? ClaHelper::converterDataBrParaBanco($dataInicioCadastro) : null;
            
            // Data final
            $dataFimCadastro = $_POST['DataFimCadastro'];
            $dataFimCadastro = (! empty($dataFimCadastro)) ? ClaHelper::converterDataBrParaBanco($dataFimCadastro) : null;
            
            $this->mapperDataTemplate($sequencialIntencao, $anoIntencao, $dataInicioCadastro, $dataFimCadastro);
        }
    }
}

$gui = new RegistroPreco_CadRegistroPrecoIntencaoAcompanhar_UI();

// Quando o programa for chamado
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    unset($_SESSION['mensagemFeedback']);
    $_GET = filter_var_array($_GET, FILTER_SANITIZE_SPECIAL_CHARS);
    $gui->plotarTelaInicial();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $_POST = filter_var_array($_POST, FILTER_SANITIZE_SPECIAL_CHARS);
    switch ($_POST['Botao']) {
        case 'Pesquisar':
            $gui->proccessPesquisar();
            $gui->proccessPrincipal();
            break;
        case 'Principal':
        default:
            $gui->proccessPrincipal();
            break;
    }
}

echo $gui->getTemplate()->show();
