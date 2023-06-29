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
 * @category   PortalDGCO
 *
 * @author     Pitang Agile TI <contato@pitang.com>
 * @copyright  2014 EMPRESA MUNICIPAL DE INFORMÁTICA - EMPREL
 * @license    http://www.php.net/license/3_01.txt PHP License 3.01
 * 
 * ---------------------------------------------------------------------
 * Alterado: Pitang Agile TI - Caio Coutinho
 * Data:     12/02/2019
 * Objetivo: Tarefa Redmine 210654
 * ---------------------------------------------------------------------
 * Alterado: Pitang Agile TI - Caio Coutinho
 * Data:     01/04/2019
 * Objetivo: Tarefa Redmine 214041
 * ---------------------------------------------------------------------
 * Alterado: Lucas Baracho
 * Data:     03/05/2019
 * Objetivo: Tarefa Redmine 216031
 * ---------------------------------------------------------------------
 * Alterado: Lucas Baracho
 * Data:     04/06/2019
 * Objetivo: Tarefa Redmine 217955
 * ---------------------------------------------------------------------
 * Alterado: Lucas Baracho
 * Data:     25/06/2019
 * Objetivo: Tarefa Redmine 219153
 * Observação: Foi retirada a validação da justificativa. Será feito um novo programa para esta funcionalidade
 * ---------------------------------------------------------------------
 * Alterado: Rossana Lira
 * Data:     14/10/2019
 * Objetivo: Tarefa Redmine 224965
 * ---------------------------------------------------------------------
 * Alterado: Eliakim Ramos
 * Data:     28/11/2019
 * Objetivo: Tarefa Redmine 224965
 * ---------------------------------------------------------------------
 * Alterado: Lucas Vicente
 * Data:     29/08/2022
 * Objetivo: Tarefa Redmine 224215
 * ---------------------------------------------------------------------
 * Alterado: João Madson
 * Data:     13/09/2022
 * Objetivo: Tarefa Redmine 268733
 * ---------------------------------------------------------------------
 * Alterado: Lucas Vicente
 * Data:     15/09/2022
 * Objetivo: Tarefa Redmine 268830
 * ---------------------------------------------------------------------
 */

 // 220038--

 require_once("../funcoes.php");
if (! @require_once dirname(__FILE__) . '/../bootstrap.php') {
    throw new Exception('Error Processing Request - Bootstrap', 1);
}

Seguranca();

/**
 *
 * @author jfsi
 *
 */
class RegistroPreco_CadRegistroPrecoIntencaoResponder_Adaptacao extends Adaptacao_Abstrata
{

    /**
     *
     * @param \DB_ $database
     */
    private function insereRespostaIntencaoDB($database, $entidade)
    {
        $dados = array(
            'cintrpsequ' => $entidade->cintrpsequ,
            'cintrpsano' => $entidade->cintrpsano,
            'corglicodi' => $entidade->corglicodi,
            'frinrpsitu' => $entidade->frinrpsitu,
            'trinrpdcad' => (string) date('Y-m-d H:i:s'),
            'cusupocodi' => $entidade->cusupocodi,
            'trinrpulat' => (string) date('Y-m-d H:i:s'),
            'xrinrpjust' => strtoupper($entidade->xrinrpjust)
        );
        //print_r($dados);
        $res = $database->autoExecute('sfpc.tbrespostaintencaorp', $dados, DB_AUTOQUERY_INSERT);

        ClaDatabasePostgresql::hasError($res);
    }

    /**
     *
     * @param \DB_ $database
     */
    private function updateRespostaIntencaoDB($database, $entidade)
    {
        $dados = array(
            'frinrpsitu' => $entidade->frinrpsitu,
            'xrinrpjust' => strtoupper($entidade->xrinrpjust),
            'trinrpdcad' => (string) date('Y-m-d H:i:s')
        );

        $conditionsSql = 'cintrpsequ = %d AND cintrpsano = %d AND corglicodi = %d';
        $conditions = sprintf($conditionsSql, $entidade->cintrpsequ, $entidade->cintrpsano, $entidade->corglicodi);

        $res = $database->autoExecute('sfpc.tbrespostaintencaorp', $dados, DB_AUTOQUERY_UPDATE, $conditions);

        ClaDatabasePostgresql::hasError($res);
    }

    /**
     *
     * @param \DB_ $database
     */
    private function insereRespostaIntencaoItemDB($database, $entidade)
    {
        $itens = count($entidade->listaItem);

        for ($i = 0; $i < $itens; ++ $i) {
            $item = $entidade->listaItem[$i];
            if($item->airirpqtpr==""){
                $item->airirpqtpr=0;
            }
            $dados = array(
                'cintrpsequ' => $item->cintrpsequ,
                'cintrpsano' => $item->cintrpsano,
                'corglicodi' => $item->corglicodi,
                'citirpsequ' => $item->citirpsequ,
                'airirpqtpr' => moeda2float($item->airirpqtpr),
                'tirirpdcad' => (string) date('Y-m-d H:i:s'),
                'cusupocodi' => $item->cusupocodi,
                'tirirpulat' => (string) date('Y-m-d H:i:s')
            );
            
            

            $res = $database->autoExecute('sfpc.tbitemrespostaintencaorp', $dados, DB_AUTOQUERY_INSERT);
            
            $temErro = ClaDatabasePostgresql::hasError($res);

            if ($temErro != false) {
                throw new Exception($temErro, 1);
            }
        }
        
        
    }
    

    /**
     *
     * @param \DB_ $database
     */
    private function updateRespostaIntencaoItemDB($database, $entidade)
    {
        $itens = count($entidade->listaItem);

        for ($i = 0; $i < $itens; $i ++) {
            $item = $entidade->listaItem[$i];
            $conditionSql = "cintrpsequ = %d AND cintrpsano = %d AND corglicodi = %d AND citirpsequ = %d";
            $conditions = sprintf($conditionSql, $item->cintrpsequ, $item->cintrpsano, $item->corglicodi, $item->citirpsequ);
            $qtde = ($item->airirpqtpr / 10000);
            $res = $database->autoExecute('sfpc.tbitemrespostaintencaorp', array(
                'airirpqtpr' => moeda2float($qtde),
                'cusupocodi' => $this->getUsuarioCodigo(),
                'tirirpulat' => (string) date('Y-m-d H:i:s')
            ), DB_AUTOQUERY_UPDATE, $conditions);
            if($qtde==NULL){
                $qtde='0,0000';
            }

            ClaDatabasePostgresql::hasError($res);
        }
        
    }

    /**
     *
     * @return bool
     */
    private function existeRespostaIntencao($entidade)
    {
        $sql = ClaRegistroPrecoIntencaoSQL::sqlExistRespostaIntencao();

        $database = ClaDatabasePostgresql::getConexao();

        $res = $database->getRow($sql, array(
            $entidade->cintrpsequ,
            $entidade->cintrpsano,
            $entidade->corglicodi
        ), DB_FETCHMODE_OBJECT);

        ClaDatabasePostgresql::hasError($res);

        return ! is_null($res) ? true : false;
    }

    /**
     * Mapping Post versus Entity.
     */
    private function bindIntencaoRegistroPreco(Negocio_ValorObjeto_IntencaoRPOrgao $voIRPOrgao, $flag = null)
    {
        $entidade = Negocio_Entidade::singleton('sfpc.tbrespostaintencaorp');
        $entidade->cintrpsequ = (int) $voIRPOrgao->getValorIntencaoRegistroPreco()->getCintrpsequ();
        $entidade->cintrpsano = (int) $voIRPOrgao->getValorIntencaoRegistroPreco()->getCintrpsano();

        $entidade->corglicodi = (int) $voIRPOrgao->getCorglicodi();
        $entidade->frinrpsitu = 'I';
        if ($flag != null) {
            $entidade->frinrpsitu = 'A';
        }
        $entidade->trinrpdcad = null;
        $entidade->cusupocodi = (int) $_SESSION['_cusupocodi_'];
        $entidade->trinrpulat = null;
        $entidade->xrinrpjust = $_POST['Justificativa'];

        $entidade->listaItem = array();

        $contador = count($_POST['RespostaItemCodigoSequencial']);
        for ($i = 0; $i < $contador; ++ $i) {
            $entidadeItem = Negocio_Entidade::singleton('sfpc.tbitemrespostaintencaorp');
            $entidadeItem->cintrpsequ = $entidade->cintrpsequ;
            $entidadeItem->cintrpsano = $entidade->cintrpsano;
            $entidadeItem->corglicodi = $entidade->corglicodi;
            $entidadeItem->citirpsequ = (integer) $_POST['RespostaItemCodigoSequencial'][$i];
            $entidadeItem->airirpqtpr = $_POST['RespostaItemQuantidade'][$i];
            $entidadeItem->tirirpdcad = null;
            $entidadeItem->cusupocodi = $entidade->cusupocodi;
            $entidadeItem->tirirpulat = null;

            $entidade->listaItem[] = $entidadeItem;
        }

        return $entidade;
    }

    /**
     * Consulta se o usuário possui mais de um orgão licitante.
     *
     * @param Negocio_ValorObjeto_IntencaoRegistroPreco $voIRP
     *
     * @return DB_common::getAll() array, string or numeric data to be added to the prepared statement.
     *         Quantity of items passed must match quantity of placeholders in the prepared statement:
     *         meaning 1 placeholder for non-array parameters or 1 placeholder per array element.
     */
    public function consultaOrgaosLicitantesUsuario(Negocio_ValorObjeto_IntencaoRegistroPreco $voIRP)
    {
        $sql = "
        SELECT
            DISTINCT c.CORGLICODI, ol.eorglidesc
        FROM
            SFPC.TBCENTROCUSTOPORTAL c
        INNER JOIN sfpc.tborgaolicitante ol
            ON ol.corglicodi = c.corglicodi
        INNER JOIN sfpc.tbintencaorporgao irpo
            ON irpo.corglicodi = c.corglicodi AND irpo.cintrpsano = %d AND irpo.cintrpsequ = %d
        WHERE c.CORGLICODI IS NOT NULL AND c.ACENPOANOE = %d
        AND c.FCENPOSITU <> 'I' AND c.CCENPOSEQU IN
        (SELECT USU.CCENPOSEQU FROM SFPC.TBUSUARIOCENTROCUSTO USU
            WHERE USU.CUSUPOCODI = %d AND USU.fusucctipo = 'C')
        ";

        return ClaDatabasePostgresql::getConexao()->getAll(sprintf($sql, $voIRP->getCintrpsano(), $voIRP->getCintrpsequ(), $voIRP->getCintrpsano(), (int) $_SESSION['_cusupocodi_']));
    }


    public function consultarOrgaoPorCodigo($codigoOrgao)
    {
        $sql = '
            SELECT
                DISTINCT ol.eorglidesc
            FROM
                SFPC.TBCENTROCUSTOPORTAL c
            INNER JOIN sfpc.tborgaolicitante ol
                ON ol.corglicodi = c.corglicodi            
            WHERE ol.corglicodi = ? 
        ';

        $database = ClaDatabasePostgresql::getConexao();

        $res = &$database->getOne($sql, $codigoOrgao);

        ClaDatabasePostgresql::hasError($res);

        return $res;
    }

    /**
     * Seleciona a situação da resposta da intenção de registro de preço
     * do respectivo orgão
     *
     * @param Negocio_ValorObjeto_IntencaoRegistroPreco $voIRP
     *
     * @return string 'RESPONDIDA' ou 'EM ABERTO';
     */
    public function selecionaSituacaoIntencaoRPGrupoOrgao(Negocio_ValorObjeto_IntencaoRegistroPreco $voIRP, $grupoCodigo)
    {
        $situacao = 'EM ABERTO';
        $sql = " SELECT rirp.cintrpsequ, rirp.cintrpsano, rirp.corglicodi, rirp.frinrpsitu, rirp.xrinrpjust
                    FROM sfpc.tbrespostaintencaorp rirp 
                        INNER JOIN sfpc.tbintencaorporgao irpo ON rirp.cintrpsequ = irpo.cintrpsequ AND rirp.cintrpsano = irpo.cintrpsano AND rirp.corglicodi = irpo.corglicodi 
                        INNER JOIN sfpc.tborgaolicitante ol ON irpo.corglicodi = ol.corglicodi 
                        WHERE rirp.cintrpsequ = %s AND rirp.corglicodi = %s AND rirp.cintrpsano = %s";
        $sql = sprintf($sql, $voIRP->getCintrpsequ(), $grupoCodigo, $voIRP->getCintrpsano());

        $database = ClaDatabasePostgresql::getConexao();

        $res = &$database->getAll($sql, array(), DB_FETCHMODE_OBJECT);

        ClaDatabasePostgresql::hasError($res);

        if(!empty($res)) {
            if($res[0]->frinrpsitu == 'A') {
                $situacao = 'RESPONDIDA';
            } else {
                $situacao = 'RASCUNHO';
            }
        }
        
        return $situacao;
    }

    /**
     * Seleciona todos itens da intenção Registro de Preço (IRP)
     *
     * @param Negocio_ValorObjeto_IntencaoRegistroPreco $voIRP
     *
     * @return DB_common::getAll() array, string or numeric data to be added to the prepared statement.
     *         Quantity of items passed must match quantity of placeholders in the prepared statement:
     *         meaning 1 placeholder for non-array parameters or 1 placeholder per array element.
     */
    public function selecionaTodosItensIntencaoRP(Negocio_ValorObjeto_IntencaoRegistroPreco $voIRP)
    {
        $sql = ClaRegistroPrecoIntencaoSQL::sqlAllIntencaoItem($voIRP->getCintrpsequ(), $voIRP->getCintrpsano());

        $database = ClaDatabasePostgresql::getConexao();

        $res = &$database->getAll($sql, array(), DB_FETCHMODE_OBJECT);

        ClaDatabasePostgresql::hasError($res);

        return $res;
    }

    public function selecionaTodosItensIntencaoRPOrgao(Negocio_ValorObjeto_IntencaoRPOrgao $voIRPOrgao)
    {
        $repositorio = new Negocio_Repositorio_ItemIntencaoRegistroPreco(ClaDatabasePostgresql::getConexao());
        return $repositorio->selecionaTodosItensIntencaoRPOrgao($voIRPOrgao);
    }

    /**
     * Seleciona quantidade informada no item de registro de preço
     *
     * @param Negocio_ValorObjeto_IntencaoRegistroPreco $voIRP
     * @param integer $corglicodi
     * @param integer $citirpsequ
     *
     * @return DB_common::getOne() array, string or numeric data to be added to the prepared statement.
     *         Quantity of items passed must match quantity of placeholders in the prepared statement:
     *         meaning 1 placeholder for non-array parameters or 1 placeholder per array element.
     */
    public function selecionaQuantidadeItemIRP(Negocio_ValorObjeto_IntencaoRPOrgao $voOrgaoIRP, $citirpsequ)
    {
        $sql = '
            SELECT airirpqtpr
            FROM sfpc.tbitemrespostaintencaorp
            WHERE cintrpsequ = ? AND cintrpsano = ?  AND corglicodi = ? AND citirpsequ = ?
        ';

        $database = ClaDatabasePostgresql::getConexao();

        $res = &$database->getOne($sql, array(
            $voOrgaoIRP->getValorIntencaoRegistroPreco()
                ->getCintrpsequ(),
            $voOrgaoIRP->getValorIntencaoRegistroPreco()
                ->getCintrpsano(),
            $voOrgaoIRP->getCorglicodi(),
            $citirpsequ
        ));

        ClaDatabasePostgresql::hasError($res);

        return $res;
    }

    /**
     * Seleciona Intenção de Registro de Preço
     *
     * @param int $sequencialIntencao
     * @param int $anoIntencao
     *
     * @return DB_common::query() array, string or numeric data to be added to the prepared statement.
     *         Quantity of items passed must match quantity of placeholders in the prepared statement:
     *         meaning 1 placeholder for non-array parameters or 1 placeholder per array element.
     */
    public function selecionaIntencaoRegistroPreco(Negocio_ValorObjeto_IntencaoRegistroPreco $voIRP)
    {
        $sql = ClaRegistroPrecoIntencaoSQL::sqlSelectIntencao((int) $voIRP->getCintrpsequ(), (int) $voIRP->getCintrpsano());
        return ClaDatabasePostgresql::executarSQL($sql);
    }

    /**
     * Salvar Resposta de Intenção de Registro de Preço (IRP)
     */
    public function salvarResposta(Negocio_ValorObjeto_IntencaoRPOrgao $voIRPOrgao, $flag = null)
    {
        $entidade = $this->bindIntencaoRegistroPreco($voIRPOrgao, $flag);

        // inserir no banco
        $database = ClaDatabasePostgresql::getConexao();
        $database->autoCommit(false);

        if (! $this->existeRespostaIntencao($entidade)) {
            $this->insereRespostaIntencaoDB($database, $entidade);
        } else {
            $this->updateRespostaIntencaoDB($database, $entidade);
        }

        $sqlDelete = '
            DELETE FROM sfpc.tbitemrespostaintencaorp
            WHERE cintrpsequ = ? AND cintrpsano = ? AND corglicodi = ?
        ';

        $res = $database->query($sqlDelete, array(
            $entidade->cintrpsequ,
            $entidade->cintrpsano,
            $entidade->corglicodi
        ));
        
        ClaDatabasePostgresql::hasError($res);

        

        $this->insereRespostaIntencaoItemDB($database, $entidade);
        $commited = $database->commit();

        if ($commited instanceof DB_error) {
            $database->rollback();

            return false;
        }

        return true;
    }

    public function selecionarRespostaIRP()
    {
        // Número intenção
        $numeroIntencao = explode('/', $_POST['NumeroIntencao']);

        $cintrpsequ = (isset($numeroIntencao[0]) && $numeroIntencao[0] != '') ? (int)$numeroIntencao[0] : null;
        $cintrpsano = (isset($numeroIntencao[1]) && $numeroIntencao[1] != '') ? (int)$numeroIntencao[1] : null;

        // Data inicial
        $dataInicioCadastro = $_POST['DataInicioCadastro'];
        $dataInicioCadastro = (! empty($dataInicioCadastro)) ? ClaHelper::converterDataBrParaBanco($dataInicioCadastro) : null;

        // Data final
        $dataFimCadastro = $_POST['DataFimCadastro'];
        $dataFimCadastro = (! empty($dataFimCadastro)) ? ClaHelper::converterDataBrParaBanco($dataFimCadastro) : null;

        $orgaoLicitante = filter_var($_POST['orgaoLicitante'], FILTER_SANITIZE_NUMBER_INT);
        $_SESSION['orgaoLicitante'] = $orgaoLicitante;
        // $sql = $codigoUsuario = $_SESSION['_cusupocodi_'];
        // $anoAtual = date('Y');

        $repositorio = new Negocio_Repositorio_IntencaoRegistroPreco();

        return $repositorio->getIntencaoByDataInicioAndDataFimAndGrupoUsuario($cintrpsequ, $cintrpsano, $dataInicioCadastro, $dataFimCadastro, $orgaoLicitante, 'A');
    }

    /**
     *
     * @param integer $orgaoLicitante
     */
    public function selecionaDescricaoOrgaoLicitante($orgaoLicitante)
    {
        $repositorio = new Negocio_Repositorio_OrgaoLicitante(ClaDatabasePostgresql::getConexao());
        $orgaoLicitante = end($repositorio->selecionaDescricaoOrgaoLicitante($orgaoLicitante));
        return $orgaoLicitante->eorglidesc;
    }

    public function validarSalvar($justificativa) {
        if ($justificativa == null) {
            $valido = false;
        } else {
            $valido = true;
        }

        return $valido;
    }

    public function documentosIntencao($cintrpsequ, $cintrpsano) {
        $sql = " SELECT cintrpsequ, encode(iintraarqu, 'base64') as iintraarqu, cintrpsequ, cintrpsano, eintranome 
                 FROM SFPC.TBINTENCAOREGISTROPRECOANEXO WHERE cintrpsequ = $cintrpsequ AND cintrpsano = $cintrpsano";
        $database = ClaDatabasePostgresql::getConexao();
        $res = &$database->getAll($sql, array(), DB_FETCHMODE_OBJECT);
        ClaDatabasePostgresql::hasError($res);

        return $res;
    }
}
//var_dump($res);die;




/**
 */
class RegistroPreco_CadRegistroPrecoIntencaoResponder_UI extends UI_Abstrata
{
    const CAMINHO_TEMPLATE = 'templates/CadRegistroPrecoIntencaoResponder.html';

    const MENSAGEM_TELA_PESQUISAR_LISTAR = '
        Preencha os dados abaixo para pesquisar uma Intenção de Registro de
        Preços e clique no número  desejado.
    ';

    const MENSAGEM_TELA_RESPONDER = '
        Preencha os dados abaixo e clique no botão ‘Salvar Rascunho‘ ou
        ‘Salvar Intenção’. Os itens obrigatórios estão com *.
    ';

    /**
     *
     * @return boolean
     */
    private function filtroPesquisaValido()
    {
        $dataInicio = filter_var($_POST['DataInicioCadastro'], FILTER_SANITIZE_STRING);
        $dataFim = filter_var($_POST['DataFimCadastro'], FILTER_SANITIZE_STRING);

        if (! empty($dataInicio) && ! ClaHelper::validationData($dataInicio)) {
            $this->getTemplate()->MENSAGEM_ERRO = 'Data de início não é válida';
            $this->getTemplate()->block('BLOCO_ERRO', true);

            return false;
        }

        if (! empty($dataFim) && ! ClaHelper::validationData($dataFim)) {
            $this->getTemplate()->MENSAGEM_ERRO = 'Data de fim não é válida';
            $this->getTemplate()->block('BLOCO_ERRO', true);

            return false;
        }

        return true;
    }

    /**
     *
     * @return Negocio_ValorObjeto_IntencaoRegistroPreco
     */

     //osmar
    public function AlterarDocumento(){
        $database = ClaDatabasePostgresql::getConexao();
        $voIRP =  $this->getNumeroIntencao();
        $oIntencao = $this->getAdaptacao()->selecionaIntencaoRegistroPreco($voIRP);
        $Linha = current($oIntencao);
        $database->query("DELETE FROM sfpc.tbintencaoregistroprecoanexo WHERE cintrpsequ = $Linha->cintrpsequ AND cintrpsano = $Linha->cintrpsano");
        $valorMax = 1;
        $tamanho = count($_SESSION['Arquivos_Upload']['nome']);
    
        $nomeTabela = 'sfpc.tbintencaoregistroprecoanexo';
        $entidade = ClaDatabasePostgresql::getEntidade($nomeTabela);
        for ($i = 0; $i < $tamanho; $i ++) {
            $entidade->cintrpsequ = (int) $Linha->cintrpsequ;
            $entidade->cintrpsano = (int) $Linha->cintrpsano;
            $entidade->cintrasequ = (int) $valorMax;
            $entidade->eintranome = $_SESSION['Arquivos_Upload']['nome'][$i];
            $entidade->iintraarqu = bin2hex($_SESSION['Arquivos_Upload']['conteudo'][$i]);
            $entidade->cusupocodi = (int) $_SESSION['_cusupocodi_'];
            $entidade->tintraulat = 'NOW()';
            $database->autoExecute($nomeTabela, (array) $entidade, DB_AUTOQUERY_INSERT);
            $valorMax ++;
        }
        unset($_SESSION['Arquivos_Upload']);
    }
    public function carregarIntencaoDocumentos() {
       
        $voIRP =  $this->getNumeroIntencao();
        $oIntencao = $this->getAdaptacao()->selecionaIntencaoRegistroPreco($voIRP);
        $Linha = current($oIntencao);
       
        if (!empty($Linha->cintrpsequ) && !empty($Linha->cintrpsano)) {
            $this->getTemplate()->VALOR_DOCUMENTOS_ATA = '';

            if (empty($_SESSION['Arquivos_Upload'])) {
                $documentos = $this->getAdaptacao()->documentosIntencao($Linha->cintrpsequ, $Linha->cintrpsano);
                if (!empty($documentos)) {
                    foreach ($documentos as $documento) {
                        $documentoHexDecodificado = base64_decode($documento->iintraarqu);
                        $documentoToBin = $this->hextobin($documentoHexDecodificado);

                        $_SESSION['Arquivos_Upload']['nome'][] = $documento->eintranome;
                        $_SESSION['Arquivos_Upload']['conteudo'][] = $documentoToBin;
                    }
                }
            }

            $this->coletarDocumentosAdicionados();
            $this->getTemplate()->block('BLOCO_FILE');
        }
    }
    private function getNumeroIntencao()
    {
        $numeroIntencaoRP = null;
        if (isset($_POST['NumeroIntencao'])) {
            $numeroIntencaoRP = explode('/', $_POST['NumeroIntencao']);
        } elseif (isset($_GET['numero'])) {
            $numeroIntencaoRP = explode('/', $_GET['numero']);
        }
        $cintrpsequ = (isset($numeroIntencaoRP[0]) && $numeroIntencaoRP[0] != '') ? $numeroIntencaoRP[0] : null;
        $cintrpsano = (isset($numeroIntencaoRP[1]) && $numeroIntencaoRP[1] != '') ? $numeroIntencaoRP[1] : null;

        return new Negocio_ValorObjeto_IntencaoRegistroPreco($cintrpsequ, $cintrpsano);
    }

    /**
     *
     * @param Negocio_ValorObjeto_IntencaoRPOrgao $voIRPOrgao
     * @param unknown $result
     */
    private function plotarItensIntencaoParaResponder(Negocio_ValorObjeto_IntencaoRPOrgao $voIRPOrgao, $result)
    {
        // Contar respostas na tabela de itemrespostaintencaorp
        $qtdRespostas = 0;
        foreach ($result as $key => $item) {
            if(!empty($item->citirpsequ)) {
                $qtdRespostas++;
            }
        }
        $cont = 0; // A variável só era setada no fim do foreach fazendo com que começasse com array[] ao inves de array[0]
        foreach ($result as $item) {
            $codigoReduzido = $item->cmatepsequ;
            $descricao = $item->ematepdesc;
            $descricaoDetalhada = $item->eitirpdescmat;
            $tipo = 'CADUM';
            $TipoGrupo = 'M';
            if (! $codigoReduzido) {
                $valorTRP = '---';
                $codigoReduzido = $item->cservpsequ;
                $descricaoDetalhada = $item->eitirpdescse;
                $descricao = $item->eservpdesc;
                $tipo = 'CADUS';
                $TipoGrupo = 'S';
            } else {
                // kim CR#224965
                $valorTRP = number_format(calcularValorTrp(Conexao(), 2, (int) $codigoReduzido),4,',','.');
            }

            if (isset($_POST['RespostaItemQuantidade'][$cont])){
                $qtdeAlterada = $_POST['RespostaItemQuantidade'][$cont];
            } else {
                $qtdeSaved = $this->getAdaptacao()->selecionaQuantidadeItemIRP($voIRPOrgao, $item->citirpsequ);
            }
            //var_dump($qtdeAlterada);die;
            
            if(empty($item->citirpsequ)) {
                $qtdRespostas++;
            }


            //$qtdeSaved = $qtdeSaved / 10000;
            $descricaoDetalhada = ! empty($descricaoDetalhada) ? $descricaoDetalhada : '---';
            $this->getTemplate()->VALOR_ITEM_ORD = $item->aitirporde;

            $this->getTemplate()->VALOR_TIPO_GRUPO = $TipoGrupo;
            $this->getTemplate()->VALOR_ITEM_CODIGO_MATERIAL_SERVICO = ($item->cservpsequ == null || $item->cservpsequ == '') ? $item->cmatepsequ : $item->cservpsequ ;

            $this->getTemplate()->VALOR_ITEM_CODIGO_SEQUENCIAL = !empty($item->citirpsequ) ? $item->citirpsequ : $qtdRespostas;
            $this->getTemplate()->VALOR_ITEM_DESCRICAO = strtoupper2($descricao);
            $this->getTemplate()->VALOR_ITEM_DESCRICAO_DETALHADA = strtoupper2($descricaoDetalhada);
            $this->getTemplate()->VALOR_ITEM_CODIGO_REDUZIDO = $codigoReduzido;
            $this->getTemplate()->VALOR_ITEM_TIPO = $tipo;
            $this->getTemplate()->VALOR_ITEM_UNITARIO = ($valorTRP);
            $this->getTemplate()->VALOR_ITEM_ESTIMADO = ($item->vitirpvues);
            ////(converte_valor_licitacao($qtdeSaved));
            $this->getTemplate()->VALOR_ITEM_QUANTIDADE_PREVISTA = empty($qtdeAlterada) ? converte_valor_estoques($qtdeSaved) : $qtdeAlterada;
            // item da intencao para resposta
            $this->getTemplate()->block('BLOCO_TELA_RESPONDER_ITENS_ROW');
            $cont++;
        }
        // end looping
        // header item da intencao para resposta
        $this->getTemplate()->block('BLOCO_TELA_RESPONDER_ITENS_HEADER');

        $situacao = $this->getAdaptacao()->selecionaSituacaoIntencaoRPGrupoOrgao($voIRPOrgao->getValorIntencaoRegistroPreco(), (integer) $_SESSION['_cgrempcodi_']);

        if (strtoupper2($situacao) == 'EM ABERTO') {
            $this->getTemplate()->block('BLOCO_EXIBE_BOTAO_RASCUNHO');
        }

        $this->getTemplate()->block('BLOCO_EXIBE_BOTAO_SALVAR');

        $this->getTemplate()->block('BLOCO_TELA_RESPONDER_ITENS');
    } 

    /**
     *
     *
     * }
     *
     * /**
     *
     * @return array
     */
    public static function filterSanitizePOST()
    {   
        return array(
            'Botao' => FILTER_SANITIZE_STRING,
            'RespostaItemCodigoSequencial' => array(
                'filter' => FILTER_SANITIZE_NUMBER_INT,
                'flags' => FILTER_REQUIRE_ARRAY
            ),
            'RespostaItemCodigoReduzido' => array(
                'filter' => FILTER_SANITIZE_NUMBER_INT,
                'flags' => FILTER_REQUIRE_ARRAY
            ),
            'RespostaItemQuantidade' => array(
                'filter' => FILTER_SANITIZE_NUMBER_FLOAT,
                'flags' => FILTER_REQUIRE_ARRAY
            ),
            'NumeroIntencao' => FILTER_SANITIZE_STRING,
            'DataInicioCadastro' => FILTER_SANITIZE_STRING,
            'DataFimCadastro' => FILTER_SANITIZE_STRING,
            'orgaoLicitante' => FILTER_SANITIZE_NUMBER_INT,
            'Justificativa'=> FILTER_SANITIZE_STRING
        );
    }

    /**
     */
    public function __construct()
    {
        $this->setAdaptacao(new RegistroPreco_CadRegistroPrecoIntencaoResponder_Adaptacao());
        $this->setTemplate(new TemplatePaginaPadrao(self::CAMINHO_TEMPLATE, 'Registro de Preço > Intenção > Responder'));

        $this->getTemplate()->VALOR_NUMERO_INTENCAO = '';
		$dataMesAno = DataMesAno();				
        $this->getTemplate()->VALOR_DATA_INICIO_CADASTRO = $dataMesAno[0];
        $this->getTemplate()->VALOR_DATA_FIM_CADASTRO = $dataMesAno[1];
        $this->getTemplate()->VALOR_MENSAGEM = self::MENSAGEM_TELA_PESQUISAR_LISTAR;		
		
        /* $dataMes = DataMes(); Trocados por datamesano() na CR 224965
        $this->getTemplate()->VALOR_DATA_INICIO_CADASTRO = $dataMes[0];
        $this->getTemplate()->VALOR_DATA_FIM_CADASTRO = $dataMes[1];
		*/
    }

    /**
     * Plotar a Tela Inicial do programa
     */
    public function plotarTelaInicial()
    {
        
        if (null !== $_POST['NumeroIntencao']) {
            $this->getTemplate()->VALOR_NUMERO_INTENCAO = filter_var($_POST['NumeroIntencao'], FILTER_SANITIZE_STRING);
        }

        if ($_POST['Botao'] != 'Limpar') {
            if (null !== $_POST['DataInicioCadastro']) {
                $this->getTemplate()->VALOR_DATA_INICIO_CADASTRO = filter_var($_POST['DataInicioCadastro'], FILTER_SANITIZE_STRING);
            }

            if (null !== $_POST['DataFimCadastro']) {
                $this->getTemplate()->VALOR_DATA_FIM_CADASTRO = filter_var($_POST['DataFimCadastro'], FILTER_SANITIZE_STRING);
            }
        }
        // Monta select
        $repositorioOL = new Negocio_Repositorio_OrgaoLicitante(ClaDatabasePostgresql::getConexao());
        $resultado = $repositorioOL->consultaOrgaosLicitantesUsuario();
        $select = '<select name="orgaoLicitante" class="textonormal">';
        $select .= "<option value='0'>Selecione o órgão</option>";
        $contador = count($resultado);
        foreach ($resultado as $orgao) {
            $ativo = '';
            if ($contador == 1 || $_POST['orgaoLicitante'] == $orgao->corglicodi) {
                $ativo = 'selected';
            }
            $select .= "<option value='" . $orgao->corglicodi . "' $ativo >" . $orgao->eorglidesc . "</option>";
        }
        $select .= "</select>";
        $this->getTemplate()->SELECT_ORGAO_USUARIO = $select;
        $this->getTemplate()->block('BLOCO_ORGAO_RASCUNHO');
        $this->getTemplate()->block('BLOCO_TELA_PESQUISA');
        $this->getTemplate()->block('BLOCO_TELA_PESQUISA_BOTAO');
    }

    public function coletarDocumentosAdicionados()
    {
        if (isset($_SESSION['Arquivos_Upload']['nome'])) {
            $lista = '';
            $qtdeDocumentos = sizeof($_SESSION['Arquivos_Upload']['nome']);

            for ($i = 0; $i < $qtdeDocumentos; $i ++) {
                $nomeDocumento = $_SESSION['Arquivos_Upload']['nome'][$i];
                $lista .= '<li>' . $nomeDocumento . ' <input type="button" name="remover[]" value="Remover" class="botao removerDocumento" doc="' . $i . '" /></li>';
            }

            $this->getTemplate()->VALOR_DOCUMENTOS_ATA = $lista;
        }
    }
    
    public function hextobin($hexstr)
    {
        $n = strlen($hexstr);
        $sbin="";
        $i=0;
        while($i<$n)
        {
            $a =substr($hexstr,$i,2);
            $c = pack("H*",$a);
            if ($i==0){$sbin=$c;}
            else {$sbin.=$c;}
            $i+=2;
        }
        return $sbin;
    }
    public function consultarDocumentos($cintrpsequ, $cintrpsano) {
        return $this->sqlConsultarDocumento($cintrpsequ, $cintrpsano);
    }

    public function sqlConsultarDocumento($cintrpsequ, $cintrpsano)
    {
        $sql = " SELECT cintrpsequ, cintrpsano, cintrasequ, encode(iintraarqu, 'base64') as iintraarqu, eintranome, cusupocodi, tintraulat 
                FROM sfpc.tbintencaoregistroprecoanexo irpa 
                WHERE irpa.cintrpsequ = %d AND irpa.cintrpsano = %d";
        $res = ClaDatabasePostgresql::executarSQL(sprintf($sql, $cintrpsequ, $cintrpsano));

        return $res;
    }

   

    /**
     */
    public function proccessPesquisar()
    {
        $this->plotarTelaInicial();
        if ($this->filtroPesquisaValido()) {
            $resultSet = $this->getAdaptacao()->selecionarRespostaIRP();
            $numeroLinhas = count($resultSet);
            $row = null;
            $orgaoLicitante = filter_var($_POST['orgaoLicitante'], FILTER_SANITIZE_STRING);

            foreach ($resultSet as $row) {
                $numeroIntencaoItem = substr($row->cintrpsequ + 10000, 1) . '/' . $row->cintrpsano;
                $this->getTemplate()->VALOR_NUMERO_INTENCAO = $numeroIntencaoItem;
                $tintrpdlim = new DateTime($row->tintrpdlim);
                $this->getTemplate()->VALOR_DATA_LIMITE = $tintrpdlim->format('d/m/Y');
                $tintrpdcad = new DateTime($row->tintrpdcad);
                $this->getTemplate()->VALOR_DATA_CADASTRO = $tintrpdcad->format('d/m/Y');
                $this->getTemplate()->VALOR_OBJETO = $row->xintrpobje;
                $voIRP = new Negocio_ValorObjeto_IntencaoRegistroPreco($row->cintrpsequ, $row->cintrpsano);
                $this->getTemplate()->VALOR_SITUACAO = $this->getAdaptacao()->selecionaSituacaoIntencaoRPGrupoOrgao($voIRP, (integer) $orgaoLicitante);
                $this->getTemplate()->block('BLOCO_LISTAGEM_ITEM');
            }

            if ($numeroLinhas > 0) {
                $this->getTemplate()->block('BLOCO_HEADER_LISTAGEM_ITEM');
                $this->getTemplate()->block('BLOCO_RESULTADO_PESQUISA');
                $this->getTemplate()->block('BLOCO_RESULTADO');

                return;
            }

            $this->getTemplate()->block('BLOCO_NAO_EXISTE_INTENCAO');
            $this->getTemplate()->block('BLOCO_RESULTADO');
        }
    }

    /**
     * Ação do botão Limpar
     *
     * Quando o usuário deseja limpar os filtros de pesquisa
     */
    public function proccessLimpar()
    {
        header('Location: CadRegistroPrecoIntencaoResponder.php');
        exit();
    }

    /**
     * Ação para a Tela Responder
     *
     * Quando o usuário deseja responder a intenção de registro de Preço (IRP)
     */
    public function proccessTelaResponder()
    {   
        $voIRP = $this->getNumeroIntencao();
        $this->getTemplate()->VALOR_NUMERO_INTENCAO = $voIRP->getNumeroIntencao();
        $this->setAdaptacao(new RegistroPreco_CadRegistroPrecoIntencaoResponder_Adaptacao());

        $oIntencao = $this->getAdaptacao()->selecionaIntencaoRegistroPreco($voIRP);
        $Linha = current($oIntencao);
        date_default_timezone_set('America/Recife');

        $tintrpdcad = new DateTime($Linha->tintrpdcad);
        $tintrpdlim = new DateTime($Linha->tintrpdlim);
        $this->getTemplate()->VALOR_RESPONDER_NUMERO_INTENCAO = $voIRP->getNumeroIntencao();
        $this->getTemplate()->VALOR_RESPONDER_DATA_CADASTRO = $tintrpdcad->format('d/m/Y');
        $this->getTemplate()->VALOR_RESPONDER_DATA_LIMITE = $tintrpdlim->format('d/m/Y');
        $this->getTemplate()->VALOR_RESPONDER_OBJETO = strtoupper2($Linha->xintrpobje);
        
        // Valor no array correspondente ao Objeto
        $this->getTemplate()->VALOR_RESPONDER_OBSERVACAO = strtoupper2($Linha->xintrpobse);
        
        // Valor no array correspondente à observação
        $this->getTemplate()->VALOR_MENSAGEM = self::MENSAGEM_TELA_RESPONDER;
        
        $situacao = $this->getAdaptacao()->selecionaSituacaoIntencaoRPGrupoOrgao($voIRP, (integer) $_SESSION['orgaoLicitante']);        
        if ($situacao != 'RESPONDIDA') {
            $this->getTemplate()->block('BLOCO_SITUACAO_RASCUNHO');
        }

        $orgaoLicitante = filter_var($_SESSION['orgaoLicitante'], FILTER_SANITIZE_NUMBER_INT);
        $voIRPOrgao = new Negocio_ValorObjeto_IntencaoRPOrgao($voIRP, $orgaoLicitante);

        $descOrgaoLicitante = $this->getAdaptacao()->consultarOrgaoPorCodigo($orgaoLicitante);
        
        $repositorioRIRP = new Negocio_Repositorio_RespostaIntencaoRP(ClaDatabasePostgresql::getConexao());
        $this->getTemplate()->VALOR_ORGAO_LICITANTE = $orgaoLicitante;
        $this->getTemplate()->DESC_ORGAO_LICITANTE = $descOrgaoLicitante;
        $existeResposta = $repositorioRIRP->selecionarRespostaIRP($voIRPOrgao);

        // Documentos
       

        // Justificativa
        $sqlJustificativa = ClaRegistroPrecoIntencaoSQL::sqlSelectRespostaIntencao($voIRP->getCintrpsequ(), $voIRP->getCintrpsano(), null, $orgaoLicitante);
        $valores = ClaDatabasePostgresql::executarSQL($sqlJustificativa);
        $valor = current($valores);

        $justificativa = strtoupper2(RetiraAcentos($valor->xrinrpjust));
        
        $this->carregarIntencaoDocumentos();
        
        $this->getTemplate()->CAMPO_JUSTIFICATIVA = $justificativa;
        if (! empty($_POST['Justificativa'])) {
            $this->getTemplate()->CAMPO_JUSTIFICATIVA = strtoupper2($_POST['Justificativa']);
        }

        $this->getTemplate()->VALOR_NCARACATERES0 = strlen($justificativa);

        if ($existeResposta) {
            $resultadoItens = $this->getAdaptacao()->selecionaTodosItensIntencaoRPOrgao($voIRPOrgao);
        } else {
            $resultadoItens = $this->getAdaptacao()->selecionaTodosItensIntencaoRP($voIRPOrgao->getValorIntencaoRegistroPreco());
        }
        // abaco
        $this->plotarItensIntencaoParaResponder($voIRPOrgao, $resultadoItens);
        $this->getTemplate()->block('BLOCO_TELA_RESPONDER');
        $this->getTemplate()->block('BLOCO_RESULTADO');
        
    }

    public function inserirDocumento()
    {
        $arquivoInformado = $_FILES['fileArquivo'];
        if ($arquivoInformado['size'] == 0) {
            $this->mensagemSistema('É preciso Informar um Arquivo', 0);
            return;
        }
      

        $arquivo = new Arquivo();
        $arquivo->setExtensoes('pdf');
        $arquivo->setTamanhoMaximo(2000000);

        $arquivo->configurarArquivo();

        if (isset($_SESSION['mensagemFeedback'])){
            $this->mensagemSistema($_SESSION['mensagemFeedback'], 0);
        }
        
        $this->proccessTelaResponder();
    }

    public function removerDocumento()
    {  
        $idDocumento = filter_var($_POST['documentoExcluir'], FILTER_VALIDATE_INT);
        
        if (!is_int($idDocumento)) {
            throw new Exception("Error Processing Request", 1);
        }

        unset($_SESSION['Arquivos_Upload']['conteudo'][$idDocumento]);
        unset($_SESSION['Arquivos_Upload']['nome'][$idDocumento]);
        $_SESSION['Arquivos_Upload']['nome'] = array_values($_SESSION['Arquivos_Upload']['nome']);
        $_SESSION['Arquivos_Upload']['conteudo'] = array_values($_SESSION['Arquivos_Upload']['conteudo']);

        $this->proccessTelaResponder();
    }

    /**
     * Ação do botão Salva Rascunho
     *
     * Quando o usuário deseja salva a sua resposta de intenção de Registro de Preço (IRP)
     * para depois continuar.
     */
    public function proccessSalvarRascunho()
    {
        $this->setAdaptacao(new RegistroPreco_CadRegistroPrecoIntencaoResponder_Adaptacao());
        $corglicodi = filter_var($_POST['orgaoLicitante'], FILTER_SANITIZE_NUMBER_INT);
        $justificativa = strtoupper2($_POST['Justificativa']);
        $voIRPOrgao = new Negocio_ValorObjeto_IntencaoRPOrgao($this->getNumeroIntencao(), (int) $corglicodi, $justificativa);
        // persistir no banco
        $salvou = $this->getAdaptacao()->salvarResposta($voIRPOrgao);
        $this->AlterarDocumento();
        if (!$salvou) {
            // retorna mensagem de sucesso
            $this->getTemplate()->MENSAGEM_ERRO = ExibeMensStr('Resposta da Intenção (Rascunho) não Salva', 0, 0);
        }
    
        // retorna mensagem de sucesso
        $this->getTemplate()->MENSAGEM_ERRO = ExibeMensStr('Resposta da Intenção (Rascunho) salva com sucesso', 1, 0);
        $this->getTemplate()->block('BLOCO_ERRO', true);
        $this->plotarTelaInicial();
        unset($_POST);
    }

    public function adicionarDocumento()
    {
        $_SESSION['arquivoIntencao'] = $_FILES['fileArquivo'];
        unset($_FILES['fileArquivo']);
        $_SESSION['requestDaVez'] = $_REQUEST;
        
        $uri = "CadRegistroPrecoIntencaoResponder.php?numero=".$_REQUEST['NumeroIntencao'];
        header('Location: ' . $uri);
        exit();
    }

    /**
     * Ação do botão Salva
     *
     * Quando o usuário deseja salva a sua resposta de intenção de Registro de Preço (IRP)
     * para depois continuar.
     */
    public function proccessSalvar() { // abaco
        $this->setAdaptacao(new RegistroPreco_CadRegistroPrecoIntencaoResponder_Adaptacao());

        $corglicodi = filter_var($_POST['orgaoLicitante'], FILTER_SANITIZE_NUMBER_INT);
        $justificativa = strtoupper2($_POST['Justificativa']);
        $this->AlterarDocumento();
        $voIRPOrgao = new Negocio_ValorObjeto_IntencaoRPOrgao($this->getNumeroIntencao(), (int) $corglicodi, $justificativa);

        if (strlen($justificativa) > 1000) {
            $this->getTemplate()->MENSAGEM_ERRO = ExibeMensStr('A justificativa deve ter menos de 1000 caracteres', 0, 0);
            $this->getTemplate()->block('BLOCO_ERRO', true);
            $this->proccessTelaResponder();
        }
        elseif ((strlen($justificativa) < 1)) {
            $this->getTemplate()->MENSAGEM_ERRO = ExibeMensStr('Informe a justificativa para esta intenção', 0, 0);
            $this->getTemplate()->block('BLOCO_ERRO', true);
            $this->proccessTelaResponder();
        }else {
           $valido = $this->getAdaptacao()->validarSalvar($justificativa);

            if (1==1) {
                // persistir no banco
                $salvou = $this->getAdaptacao()->salvarResposta($voIRPOrgao, true);

                if (!$salvou) {
                    // retorna mensagem de sucesso
                    $this->getTemplate()->MENSAGEM_ERRO = ExibeMensStr('Resposta da Intenção não Salva', 0, 0);
                }

                $_POST['NumeroIntencao'] = null;

                // retorna mensagem de sucesso
                $this->getTemplate()->MENSAGEM_ERRO = ExibeMensStr('Resposta da Intenção adicionada com sucesso', 1, 0);
                $this->getTemplate()->block('BLOCO_ERRO', true);
                $this->plotarTelaInicial();

                unset($_POST);
            } else {
                $this->getTemplate()->MENSAGEM_ERRO = ExibeMensStr('Informe a justificativa para esta intenção', 0, 0);
                $this->getTemplate()->block('BLOCO_ERRO', true);
                $this->proccessTelaResponder();
            }
        }
    }

    /**
     * Ação do botão Imprimir
     */
    public function proccessImprimirResumo()
    {
        $numeroIntencao = $this->getNumeroIntencao();
        $chaveIntencao = array();
        $chaveIntencao['sequencialIntencao'] = $numeroIntencao->getCintrpsequ();
        $chaveIntencao['anoIntencao'] = $numeroIntencao->getCintrpsano();
        $pdf = new PdfPrecoIntencaoResponder();
        $pdf->setChaveIntencao($chaveIntencao);
        $pdf->setOrgaoLicitante($_REQUEST['orgaoLicitante']);
        $pdf->gerarRelatorio();
    }
}

$gui = new RegistroPreco_CadRegistroPrecoIntencaoResponder_UI();

// Quando o programa for chamado
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $_GET = filter_var_array($_GET, FILTER_SANITIZE_STRING);

    if (isset($_GET['numero'])) {
        $gui->proccessTelaResponder();
    } else {
        unset($_SESSION['orgaoLicitante']);
        $gui->plotarTelaInicial();
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') { // ABACO
    
    switch ($_POST['Botao']) {
        case 'SalvarRascunho':
            $gui->proccessSalvarRascunho();
            break;
        case 'SalvarIntencao':
            $gui->proccessSalvar();
            break;
        case 'Pesquisar':
            $gui->proccessPesquisar();
            break;
        case 'InserirDocumento':
            $gui->inserirDocumento();
            break; 
        case 'RemoverDocumento':
            $gui->removerDocumento();
            break;
        case 'Limpar':
            $gui->proccessLimpar();
            break;
        case 'Imprimir':
            $gui->proccessImprimirResumo();
            break;
        case 'Voltar':
            unset($_SESSION['orgaoLicitante']);
            $gui->plotarTelaInicial();
            $gui->proccessLimpar();
            break;
    }
}

echo $gui->getTemplate()->show();
