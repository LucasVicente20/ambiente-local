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
 * @category  Pitang_Registro_Preco
 *
 * @author    Pitang Agile TI <contato@pitang.com>
 * @copyright 2014 EMPRESA MUNICIPAL DE INFORMÁTICA - EMPREL
 * @license   http://www.php.net/license/3_01.txt PHP License 3.01
 *
 * @version    GIT: EMPREL-SAD-PORTAL-COMPRAS-REGISTRO-PRECO-BL-FUNC-20160615-1735-2-g2b89f48
 */

/*
# Alterado: Pitang Agile TI - Caio Coutinho
# data: 28/06/2018
# Objetivo: Tarefa Redmine #197604
# Alterado: Pitang Agile TI - Caio Coutinho
# data: 11/02/2019
# Objetivo: Tarefa Redmine 210654
#---------------------------------------------------------------------
# Autores: João Madson e Marcello Albuquerque
# Data: 22/04/2021
# CR #247236
#---------------------------------------------------------------------
# Alterado : Osmar Celestino
# Data: 10/09/2021
# Objetivo: CR #252638 
#
#---------------------------------------------------------------------------
*/

// 220038--

if (! @require_once dirname(__FILE__) . '/../bootstrap.php') {
    throw new Exception('Error Processing Request - Bootstrap', 1);
}

Seguranca();

/**
 * A camada de Dados conterá o código que irá realizar todo o acesso aos dados.
 */
class RegistroPreco_Dados_CadRegistroPrecoIntencaoManter extends Dados_Abstrata
{

    /**
     *
     * @param array $item
     *
     * @return bool
     */
    private function itemExiste(array $item)
    {
        $sql = '
            SELECT *
            FROM sfpc.tbitemintencaoregistropreco
            WHERE
               cintrpsequ = %d
               AND cintrpsano = %d
               AND citirpsequ = %d
        ';
        $row = $this->getConexao()->getRow(sprintf($sql, $item['cintrpsequ'], $item['cintrpsano'], $item['citirpsequ']));

        return $row ? true : false;
    }

    /**
     *
     * @param Negocio_ValorObjeto_Cintrpsequ $cintrpsequ
     * @param Negocio_ValorObjeto_Cintrpsano $cintrpsano
     *
     * @return array
     */
    public function consultarItensIntencaoRP(Negocio_ValorObjeto_Cintrpsequ $cintrpsequ, Negocio_ValorObjeto_Cintrpsano $cintrpsano)
    {
        $sql = '
            select i.cintrpsequ,i.cintrpsano, i.citirpsequ,
                   i.cmatepsequ, m.ematepdesc, i.eitirpdescmat,
                   s.eservpdesc, i.cservpsequ, i.eitirpdescse,
                   i.aitirporde, i.vitirpvues, m.fmatepgene
              FROM sfpc.tbitemintencaoregistropreco i
                   full outer JOIN sfpc.tbmaterialportal m
                                ON i.cmatepsequ = m.cmatepsequ
                   full outer JOIN sfpc.tbservicoportal s
                                ON i.cservpsequ = s.cservpsequ
             WHERE 1 =1
                   AND i.cintrpsequ = %d
                   AND i.cintrpsano = %d
            ORDER BY i.aitirporde
        ';

        $sql = sprintf($sql, $cintrpsequ->getValor(), $cintrpsano->getValor());
        $res = $this->executarSQL($sql);
        $this->hasError($res);
        return $res;
    }

    /**
     * Insert Intencao Grupo DB.
     *
     * @param \DB_object $dao
     * @param int $orgao
     * @param int $intencaoId
     * @param int $intencaoAno
     *
     * @return \DB_result || \DB_error [description]
     */
    public function inserirGrupoIntencao($dao, $orgao, $intencaoId, $intencaoAno)
    {
        $nomeTabela = 'sfpc.tbintencaorporgao';
        $entidade = BaseEntidade::retornaEntidade($nomeTabela);
        $entidade->corglicodi = (int) $orgao;
        $entidade->cusupocodi = (int) $_SESSION['_cusupocodi_'];
        $entidade->tinrpoulat = 'NOW()';
        $entidade->cintrpsequ = (int) $intencaoId;
        $entidade->cintrpsano = (int) $intencaoAno;
        $entidade->finrpositu = "A";
        $res = $dao->autoExecute($nomeTabela, (array) $entidade, DB_AUTOQUERY_INSERT);

        $this->hasError($res);

        return $res;
    }

    /**
     * Alterar os Orgãos da Intencao.
     *
     * @param [type] $dao
     * @param [type] $intencao
     *
     * @return [type] [description]
     */
    public function alterarOrgaosIntencao($dao, $intencao)
    {
        // Verificar os orgãos
        $orgaos = $_POST['Orgaos'];
        $new_orgaos = array();
        $delete_orgaos = array();
        $sqlOrgaos = sprintf('SELECT * FROM sfpc.tbintencaorporgao WHERE cintrpsequ = %d AND cintrpsano = %d', $intencao->cintrpsequ, (int) $intencao->cintrpsano);
        $res = $this->executarSQL($sqlOrgaos);

        if(!empty($res)) {
            foreach($res as $key => $value) {
                if(!in_array($value->corglicodi, $orgaos)) {
                    $delete_orgaos[] = $value->corglicodi;
                } else {
                    $new_orgaos[] = $value->corglicodi;
                }
            }
        }

        $diff       = array_diff($orgaos, $new_orgaos);

        // Inativar os orgãos que foram retirados
        if(!empty($delete_orgaos)) {
            foreach ($delete_orgaos as $orgao) {
                //$dao->query(sprintf('DELETE FROM sfpc.tbitemrespostaintencaorp WHERE cintrpsequ = %d AND cintrpsano = %d AND corglicodi = %d', (int) $intencao->cintrpsequ, (int) $intencao->cintrpsano, $orgao));
                //$dao->query(sprintf('DELETE FROM sfpc.tbrespostaintencaorp WHERE cintrpsequ = %d AND cintrpsano = %d AND corglicodi = %d', (int) $intencao->cintrpsequ, (int) $intencao->cintrpsano, $orgao));
                //$dao->query(sprintf('DELETE FROM sfpc.tbintencaorporgao WHERE cintrpsequ = %d AND cintrpsano = %d AND corglicodi = %d', (int) $intencao->cintrpsequ, (int) $intencao->cintrpsano, $orgao));
                $dao->query(sprintf("UPDATE sfpc.tbintencaorporgao SET finrpositu = 'I' WHERE cintrpsequ = %d AND cintrpsano = %d AND corglicodi = %d", (int) $intencao->cintrpsequ, (int) $intencao->cintrpsano, $orgao));
            }
        }

        // Ativas os novos que estão inativos
        if(!empty($new_orgaos)) {
            foreach ($new_orgaos as $orgao) {
                $dao->query(sprintf("UPDATE sfpc.tbintencaorporgao SET finrpositu = 'A' WHERE cintrpsequ = %d AND cintrpsano = %d AND corglicodi = %d", (int) $intencao->cintrpsequ, (int) $intencao->cintrpsano, $orgao));
            }
        }

        // Insert dos orgãos novos
        if(!empty($diff)) {
            foreach ($diff as $orgao) {
                $this->inserirGrupoIntencao($dao, $orgao, (int) $intencao->cintrpsequ, (int) $intencao->cintrpsano);
            }
        }
    }

    /**
     * alterar Intencao DB.
     *
     * @param resource $dao
     * @param array $intencao
     *
     * @return resource
     */
    public function alterarIntencao(&$dao, $intencao)
    {
        $nomeTabela = 'sfpc.tbintencaoregistropreco';
        $entidade = RegistroPreco_Adaptacao_CadRegistroPrecoIntencaoManter::mapearEntidadeIntencao(BaseEntidade::retornaEntidade($nomeTabela));

        unset($entidade->cintrpsequ, $entidade->cintrpsano, $entidade->fintrpsitu, $entidade->tintrpdcad);

        $conditionsSql = 'cintrpsequ = %d AND cintrpsano = %d';
        $conditions = sprintf($conditionsSql, $intencao->cintrpsequ, $intencao->cintrpsano);
        $res = $dao->autoExecute($nomeTabela, (array) $entidade, DB_AUTOQUERY_UPDATE, $conditions);

        $this->hasError($res);

        return $res;
    }

    /**
     * Update Intencao Item DB.
     *
     * @param \DB_object $dao
     *            [description]
     * @param array $intencao
     *            [description]
     *
     * @return \DB_result || \DB_error [description]
     */
    public function alterarItemIntencao($dao, $intencao)
    {
        $countItem = count($_POST['CodigoReduzido']);
        $nomeTabela = 'sfpc.tbitemintencaoregistropreco';
        $entidade = BaseEntidade::retornaEntidade($nomeTabela);
        if ($countItem > 0) {
            for ($i = 0; $i < $countItem; ++ $i) {
                $item = RegistroPreco_Adaptacao_CadRegistroPrecoIntencaoManter::mapearItem($i);

                $entidade->cintrpsequ = (int) $intencao->cintrpsequ;
                $entidade->cintrpsano = (int) $intencao->cintrpsano;
                $entidade->citirpsequ = $i + 1;

                // converte_valor_estoques
                $entidade = RegistroPreco_Adaptacao_CadRegistroPrecoIntencaoManter::mapearItemIntencaoEntidade($item, $entidade);

                if (self::itemExiste((array) $entidade)) {
                    $conditions = sprintf('cintrpsequ = %d AND cintrpsano = %d AND citirpsequ = %d AND cintrpsequ = %d', $entidade->cintrpsequ, $entidade->cintrpsano, $entidade->citirpsequ, $entidade->cintrpsequ);
                    // remove o campo de cadastro
                    unset($entidade->tintrpdcad);

                    $res = $dao->autoExecute($nomeTabela, (array) $entidade, DB_AUTOQUERY_UPDATE, $conditions);
                } else {
                    $res = $dao->autoExecute($nomeTabela, (array) $entidade, DB_AUTOQUERY_INSERT);
                }
                ClaDatabasePostgresql::hasError($res);
            }
            $this->alterarItemIntencaoResposta($entidade);
        }
    }
    // |MADSON|MARCELLO
    // Esta função é chamada para inserir itens novos para todos os orgãos que já responderam a IRP 
    public function alterarItemIntencaoResposta($entidade){
        $db = Conexao();
        $orgaosResposta = array();
        $sql = "select distinct corglicodi
                            from sfpc.tbitemrespostaintencaorp
                            where cintrpsequ = $entidade->cintrpsequ and cintrpsano = $entidade->cintrpsano";
        
        $resultado = executarSQL($db, $sql);
        while($resultado->fetchInto($orgaoI, DB_FETCHMODE_OBJECT)){
            $orgaosResposta[] = $orgaoI;
        }
        // Os orgão que responderam são listados na query acima;
        // Abaixo é feita a busca pela quantidade de itens da IRP
        $sqlIM = "select max(citirpsequ) as itemmaximo from sfpc.tbitemintencaoregistropreco where cintrpsequ = $entidade->cintrpsequ and cintrpsano = $entidade->cintrpsano";

        $resultado = executarSQL($db, $sqlIM);
        $resultado->fetchInto($Item, DB_FETCHMODE_OBJECT);
        $ItemMaximo = intval($Item->itemmaximo);
        foreach($orgaosResposta as $orgao){
            // Os orgão são trabalhados individualmente
            // Esta query busca o ultimo sequencial de item da resposta do orgão trabalhado
            $sqlQuant = "select max(citirpsequ) as seqmaximo from sfpc.tbitemrespostaintencaorp where cintrpsequ = $entidade->cintrpsequ and cintrpsano = $entidade->cintrpsano and corglicodi = $orgao->corglicodi";
            $resultQuant = executarSQL($db, $sqlQuant);
            $resultQuant->fetchInto($seqItem, DB_FETCHMODE_OBJECT);
            $seqItem = intval($seqItem->seqmaximo);
            //Caso a resposta do orgão tenha menos itens do que a quantidade de itens da IRP ele insere os novos itens.
            if($ItemMaximo > $seqItem){
                $auxSeqitem = $seqItem;
                while($ItemMaximo > $auxSeqitem){
                    $auxSeqitem++;
                    $sqlInsert = "insert into sfpc.tbitemrespostaintencaorp (cintrpsequ, cintrpsano, corglicodi, citirpsequ, airirpqtpr, tirirpdcad, cusupocodi, tirirpulat) values ($entidade->cintrpsequ, $entidade->cintrpsano, $orgao->corglicodi, $auxSeqitem, 0, now(), ".$_SESSION['_cusupocodi_'].", now())";
                    executarSQL($db, $sqlInsert);
                }
            }
        }
    }

    public function alterarIntencaoDocumentos($dao, $intencao) {
        $cintrpsequ = $intencao->cintrpsequ;
        $cintrpsano = $intencao->cintrpsano;

        $dao->query("DELETE FROM sfpc.tbintencaoregistroprecoanexo WHERE cintrpsequ = $cintrpsequ AND cintrpsano = $cintrpsano");
        $valorMax = 1;
        $tamanho = count($_SESSION['Arquivos_Upload']['nome']);

        $nomeTabela = 'sfpc.tbintencaoregistroprecoanexo';
        $entidade = ClaDatabasePostgresql::getEntidade($nomeTabela);
        for ($i = 0; $i < $tamanho; $i ++) {
            $entidade->cintrpsequ = (int) $cintrpsequ;
            $entidade->cintrpsano = (int) $cintrpsano;
            $entidade->cintrasequ = (int) $valorMax;
            $entidade->eintranome = $_SESSION['Arquivos_Upload']['nome'][$i];
            $entidade->iintraarqu = bin2hex($_SESSION['Arquivos_Upload']['conteudo'][$i]);
            $entidade->cusupocodi = (int) $_SESSION['_cusupocodi_'];
            $entidade->tintraulat = 'NOW()';
            $dao->autoExecute($nomeTabela, (array) $entidade, DB_AUTOQUERY_INSERT);
            $valorMax ++;
        }
    }

    /**
     *
     * @param int $sequencialIntencao
     * @param int $anoIntencao
     * @param int $item
     */
    public function deleteIntencaoItensRespostas($dao, $sequencialIntencao, $anoIntencao, $item)
    {
        if (empty($sequencialIntencao) || empty($anoIntencao) || empty($item)) {
            return;
        }
        $sql = '
            DELETE FROM sfpc.tbitemrespostaintencaorp WHERE cintrpsequ = %d AND cintrpsano = %d AND citirpsequ = %d
        ';
        $sql = sprintf($sql, $sequencialIntencao, $anoIntencao, $item);
        $res = $dao->query($sql);
    }

    /**
     *
     * @param int $sequencialIntencao
     * @param int $anoIntencao
     * @param int $item
     */
    public function deleteIntencaoItensByItem($dao, $sequencialIntencao, $anoIntencao, $item)
    {
        if (empty($sequencialIntencao) || empty($anoIntencao) || empty($item)) {
            return;
        }
        $sql = '
            DELETE FROM sfpc.tbitemintencaoregistropreco WHERE cintrpsequ = %d AND cintrpsano = %d AND citirpsequ = %d
        ';
        $sql = sprintf($sql, $sequencialIntencao, $anoIntencao, $item);
        $res = $dao->query($sql);
    }

    /**
     * [setarRespostaParaRascunho description].
     *
     * @param [type] $dao
     *            [description]
     * @param [type] $intencao
     *            [description]
     *
     * @return [type] [description]
     */
    public function setarRespostaParaRascunho($dao, $intencao)
    {
        $entidade = array();
        $entidade['frinrpsitu'] = 'I';
        $entidade['trinrpulat'] = 'NOW()';
        $conditionsSql = 'cintrpsequ = %d AND cintrpsano = %d';
        $conditions = sprintf($conditionsSql, $intencao->cintrpsequ, $intencao->cintrpsano);
        $res = $dao->autoExecute('sfpc.tbrespostaintencaorp', $entidade, DB_AUTOQUERY_UPDATE, $conditions);

        ClaDatabasePostgresql::hasError($res);

        return $res;
    }

    /**
     * [consultarSCCIntencao description].
     *
     * @param [type] $intencao
     *            [description]
     *
     * @return [type] [description]
     */
    public function consultarSCCIntencao($intencao)
    {
        $sql = '
           SELECT
                scc.csolcosequ,
                scc.corglicodi,
                scc.asolcoanos,
                scc.ctpcomcodi,
                scc.tsolcodata,
                scc.ccenposequ,
                scc.cusupocodi,
                scc.cusupocod1,
                scc.tsolcoulat,
                scc.csitsocodi,
                sitscc.esitsonome,
                orglic.forglitipo,
                irp.tintrpdlim
            FROM
                sfpc.tbsolicitacaocompra scc
            INNER JOIN
                sfpc.tbsituacaosolicitacao sitscc
                    ON sitscc.csitsocodi = scc.csitsocodi
            INNER JOIN
                sfpc.tborgaolicitante orglic
                    ON orglic.corglicodi = scc.corglicodi
            INNER JOIN
                sfpc.tbintencaoregistropreco irp
                    ON irp.cintrpsequ = scc.cintrpsequ
                        AND irp.cintrpsano = scc.cintrpsano
            WHERE
                scc.cintrpsequ = %d
                AND scc.cintrpsano = %d
        ';

        $res = $this->executarSQL(sprintf($sql, $intencao->cintrpsequ, $intencao->cintrpsano));
        $this->hasError($res);
        return $res;
    }

    public function sqlConsultarDocumento($cintrpsequ, $cintrpsano)
    {
        $sql = " SELECT cintrpsequ, cintrpsano, cintrasequ, encode(iintraarqu, 'base64') as iintraarqu, eintranome, cusupocodi, tintraulat 
                FROM sfpc.tbintencaoregistroprecoanexo irpa 
                WHERE irpa.cintrpsequ = %d AND irpa.cintrpsano = %d";
        $res = $this->executarSQL(sprintf($sql, $cintrpsequ, $cintrpsano));

        return $res;
    }
}

/**
 * A camada de Negócio conterá o código que irá implementar todas as regras de negócio do sistema.
 *
 * Utiliza serviços da camada de Dados.
 */
class RegistroPreco_Negocio_CadRegistroPrecoIntencaoManter extends Negocio_Abstrata
{

    /**
     * [$intencao description].
     *
     * @var unknown
     */
    private $intencao;

    private $variavel;

    /**
     *
     * {@inheritdoc}
     *
     * @see Negocio_Abstrata::getDados()
     */
    public function getDados()
    {
        $this->setDados(new RegistroPreco_Dados_CadRegistroPrecoIntencaoManter());
        return parent::getDados();
    }

    /**
     * [getChaveIntencao description].
     *
     * @return [type] [description]
     */
    public function getChaveIntencao()
    {
        $chaveIntencao = array(
            'sequencialIntencao' => 0,
            'anoIntencao' => 0
        );
        $numeroIntencao = filter_var($_GET['numero'], FILTER_SANITIZE_STRING);

        if (empty($numeroIntencao)) {
            $numeroIntencao = $_POST['NumeroIntencaoAcessada'];
        }

        if (empty($numeroIntencao)) {
            throw new DomainException('Não foi possivel recuperar a Chave da Intenção');
        }

        $numeroIntencao = explode('/', $numeroIntencao);
        $sequencialIntencao = (isset($numeroIntencao[0]) && $numeroIntencao[0] != '') ? $numeroIntencao[0] : null;
        $anoIntencao = (isset($numeroIntencao[1]) && $numeroIntencao[1] != '') ? $numeroIntencao[1] : null;

        settype($sequencialIntencao, 'integer');
        settype($anoIntencao, 'integer');

        $chaveIntencao['sequencialIntencao'] = $sequencialIntencao;
        $chaveIntencao['anoIntencao'] = $anoIntencao;

        return $chaveIntencao;
    }

    /**
     * Resgata do banco de dados a intenção que possua o número recebido
     * via GET.
     *
     * @return [type] [description]
     */
    public function carregarIntencaoBancoDados()
    {
        $chaveIntencao = $this->getChaveIntencao();
        $rpIntencao = new ClaRegistroPrecoIntencao();
        if (! empty($chaveIntencao['sequencialIntencao']) && ! empty($chaveIntencao['anoIntencao'])) {
            $resultSet = $rpIntencao->getIntencao($chaveIntencao['sequencialIntencao'], $chaveIntencao['anoIntencao']);

            $this->intencao = $resultSet->fetchRow(DB_FETCHMODE_OBJECT);
        }

        if (is_null($this->intencao)) {
            $this->variavel['collectorMensagemErro'][] = 'Intenção não selecionada';
        }
    }

    public function getIntencao()
    {
        return $this->intencao;
    }

    /**
     * Validation data form.
     *
     * @return bool
     */
    private function validacaoFormulario()
    {
        $retorna = true;
        $dataLimite = filter_var($_POST['DataLimite'], FILTER_SANITIZE_STRING);
        if (! isset($dataLimite) || empty($dataLimite)) {
            $_SESSION['mensagemFeedback'][] = 'Data limite não informado';
            $retorna = false;
        }

        $objeto = filter_var($_POST['ObjetoIntencao'], FILTER_SANITIZE_STRING);
        if (! isset($objeto) || empty($objeto)) {
            $_SESSION['mensagemFeedback'][] = 'Objeto não informado';
            $retorna = false;
        }

        if (count($_POST['Orgaos']) < 1) {
            $_SESSION['mensagemFeedback'][] = "
                <a href='javascript:document.getElementById(\"Orgaos\").focus();' class='titulo2'>
                    Órgãos não informado
                </a>";

            $retorna = false;
        }

        $countItem = count($_SESSION['intencaoItem']);
        if ($countItem === 0) {
            $_SESSION['mensagemFeedback'][] = 'Nenhum item foi adicionado para a Intenção de Registro de Preço';

            $retorna = false;
        }

        if (isset($_POST['ValorUnitarioEstimado']) && is_array($_POST['ValorUnitarioEstimado'])) {
            foreach ($_POST['ValorUnitarioEstimado'] as $indice => $valor) {
                if (strlen($valor) > 17) {
                    $indice ++;
                    $_SESSION['mensagemFeedback'][] = "O tamanho máximo do valor do item ($indice) não pode ser maior que 17 dígitos";

                    $retorna = false;
                    break;
                }
            }
        }

        return $retorna;
    }

    /**
     * Repository Intencao Alterar.
     *
     * Define a regra de armazenamento no banco de dados
     *
     * @return bool
     */
    private function repositoryIntencaoAlterar($intencao)
    {
        $dao = ClaDatabasePostgresql::getConexao();
        $dao->autoCommit(false);
        $dados = $this->getDados();
        $dados->alterarIntencao($dao, $intencao);
        //$dados->setarRespostaParaRascunho($dao, $intencao);
        $dados->alterarOrgaosIntencao($dao, $intencao);
        $this->compareIntencao($dao, $intencao);
        $dados->alterarItemIntencao($dao, $intencao);
        $dados->alterarIntencaoDocumentos($dao, $intencao);
        $commited = $dao->commit();

        if ($commited instanceof DB_error) {
            $dao->rollback();

            return false;
        }

        return true;
    }

    /**
     * Check Regra de Negocio.
     *
     * Porém, o sistema só pode permitir alterar a intenção, se todas SCC´s relacionadas estejam na fase de 'Análise',
     * no caso do órgão solicitante ser da administração direta e 'para encaminhamento' no caso do órgão solicitante
     * administração indireta;
     *
     * @return bool
     */
    /*private function verificaRegraNegocio($intencao)
    {
        $dao = $this->getDados();
        $res = $dao->consultarSCCIntencao($intencao);
        $row = null;
        $retorno = true;
        foreach ($res as $row) {
            if ($row->forglitipo == 'D' && $row->csitsocodi >= 6) {
                $_SESSION['mensagemFeedback'][] = 'Existem SCC que não estão na fase ANALISE';
                $retorno = false;
                break;
            }

            if ($row->forglitipo == 'I' && $row->csitsocodi >= 7) {
                $_SESSION['mensagemFeedback'][] = 'Existem SCC que não estão na fase PARA ENCAMINHAMENTO';
                $retorno = false;
                break;
            }
        }
        return $retorno;
    }*/

    /**
     *
     * @param unknown $array1
     * @param unknown $array2
     * @return NULL[]|unknown[]
     */
    private function check_diff_multi($array1, $array2)
    {
        $result = array();
        foreach ($array1 as $key => $val) {
            if (isset($array2[$key])) {
                if (is_array($val) && $array2[$key]) {
                    $result[$key] = check_diff_multi($val, $array2[$key]);
                }
            } else {
                $result[$key] = $val;
            }
        }

        return $result;
    }

    /**
     * [compareIntencao description].
     *
     * @return [type] [description]
     */
    private function compareIntencao(DB_pgsql $dao, $intencao)
    {
        // como estava os dados no banco de dados antes da alteração
        // como está agora?
        // comparar o que mudou
        if (is_array($_SESSION['intencaoItemOld']) && is_array($_SESSION['intencaoItem'])) {
            $arrayDiff = $this->check_diff_multi($_SESSION['intencaoItemOld'], $_SESSION['intencaoItem']);
            if (count($arrayDiff) > 0) {
                // Porém ao excluir um item, também excluir todas as respostas relativas a este item..
                foreach ($arrayDiff as $codigoItem) {
                    $this->getDados()->deleteIntencaoItensRespostas($dao, (int) $intencao->cintrpsequ, (int) $intencao->cintrpsano, $codigoItem->citirpsequ);
                    $this->getDados()->deleteIntencaoItensByItem($dao, (int) $intencao->cintrpsequ, (int) $intencao->cintrpsano, $codigoItem->citirpsequ);
                }
            }
        }
        // se pode realizar a alteração
        unset($_SESSION['intencaoItemOld']);
    }

    public function alterar($intencao)
    {
        if ($this->validacaoFormulario()) {
            if ($this->repositoryIntencaoAlterar($intencao)) {
                return true;
            }
        }

        return false;
    }

    public function consultarDocumentos($cintrpsequ, $cintrpsano) {
        return $this->getDados()->sqlConsultarDocumento($cintrpsequ, $cintrpsano);
    }
}

/**
 * A camada de Adaptação e Transformação conterá o código que tratará a lógica de apresentação dos resultados
 * das requisições dos usuários e a troca de dados com sistemas externos.
 *
 * Utiliza serviços da camada de Negócio.
 */
class RegistroPreco_Adaptacao_CadRegistroPrecoIntencaoManter extends Adaptacao_Abstrata
{

    /**
     *
     * {@inheritdoc}
     *
     * @see Adaptacao_Abstrata::getNegocio()
     */
    public function getNegocio()
    {
        $this->setNegocio(new RegistroPreco_Negocio_CadRegistroPrecoIntencaoManter());
        return parent::getNegocio();
    }

    /**
     */
    public function acaoRetirar()
    {
        if (! isset($_POST['CheckItem'])) {
            $_SESSION['mensagemFeedback'] = 'Selecione um item antes de retirar';
            return;
        }
        ClaItem::removeItemLista($_POST['CheckItem'], 'intencaoItem');

        foreach ($_POST['CheckItem'] as $value) {
            $value = $value - 1;
            if (isset($_POST['Item'][$value])) {
                $_POST['Item'][$value] = null;
                unset($_POST['Item'][$value]);
            }
            if (isset($_POST['Material'][$value])) {
                $_POST['Material'][$value] = null;
                unset($_POST['Material'][$value]);
            }
            if (isset($_POST['Descricao'][$value])) {
                $_POST['Descricao'][$value] = null;
                unset($_POST['Descricao'][$value]);
            }
            if (isset($_POST['DescricaoDetalhada'][$value])) {
                $_POST['DescricaoDetalhada'][$value] = null;
                unset($_POST['DescricaoDetalhada'][$value]);
            }
            if (isset($_POST['Tipo'][$value])) {
                $_POST['Tipo'][$value] = null;
                unset($_POST['Tipo'][$value]);
            }
            if (isset($_POST['CodigoReduzido'][$value])) {
                $_POST['CodigoReduzido'][$value] = null;
                unset($_POST['CodigoReduzido'][$value]);
            }
            if (isset($_POST['ValorEstimadoTRP'][$value])) {
                $_POST['ValorEstimadoTRP'][$value] = null;
                unset($_POST['ValorEstimadoTRP'][$value]);
            }
            if (isset($_POST['ValorUnitarioEstimado'][$value])) {
                $_POST['ValorUnitarioEstimado'][$value] = null;
                unset($_POST['ValorUnitarioEstimado'][$value]);
            }
        }
        $novo = array();
        foreach ($_POST['Item'] as $value) {
            $novo['Item'][] = $value;
        }
        foreach ($_POST['Material'] as $value) {
            $novo['Material'][] = $value;
        }
        foreach ($_POST['Descricao'] as $value) {
            $novo['Descricao'][] = $value;
        }
        foreach ($_POST['DescricaoDetalhada'] as $value) {
            $novo['DescricaoDetalhada'][] = $value;
        }
        foreach ($_POST['Tipo'] as $value) {
            $novo['Tipo'][] = $value;
        }
        foreach ($_POST['CodigoReduzido'] as $value) {
            $novo['CodigoReduzido'][] = $value;
        }
        foreach ($_POST['ValorEstimadoTRP'] as $value) {
            $novo['ValorEstimadoTRP'][] = $value;
        }

        foreach ($_POST['ValorUnitarioEstimado'] as $value) {
            $novo['ValorUnitarioEstimado'][] = $value;
        }

        $_POST = $novo;
    }

    /**
     * Altera a situação da intenção.
     */
    public function acaoAlterarSituacao($gui)
    {
        $negocio = new RegistroPreco_Negocio_CadRegistroPrecoIntencaoManter();
        $negocio->carregarIntencaoBancoDados();
        $intencao = $negocio->getIntencao();
        $situacaoIntencao = filter_var($_POST['SituacaoAtualIntencao'], FILTER_SANITIZE_STRING);
        $situacao = ($situacaoIntencao == 'I') ? 'A' : 'I';
        $rpIntencao = new ClaRegistroPrecoIntencao();

        $rpIntencao->updateSituacaoIntencao((int) $intencao->cintrpsequ, (int) $intencao->cintrpsano, $situacao);
        $dados = new RegistroPreco_Dados_CadRegistroPrecoIntencaoManter();
        $dados->setarRespostaParaRascunho(ClaDatabasePostgresql::getConexao(), $this->intencao);
        $_SESSION['mensagemFeedback'] = 'Situação alterada com sucesso';
        header('Location: CadRegistroPrecoIntencaoPesquisar.php');
        exit();
    }

    /**
     * Process Voltar.
     */
    public function acaoVoltar()
    {
        // Flag que indica o botão voltar
        $_SESSION['voltarPesquisa'] = true;
        unset($_SESSION['intencaoItem']);
        header('Location: CadRegistroPrecoIntencaoPesquisar.php');
        exit();
    }

    /**
     */
    public function acaoExcluirIntencao($gui)
    {
        $negocio = new RegistroPreco_Negocio_CadRegistroPrecoIntencaoManter();
        $chaveIntencao = $negocio->getChaveIntencao();
        $rpIntencao = new ClaRegistroPrecoIntencao();
        $resultSetSelect = $rpIntencao->getIntencao($chaveIntencao['sequencialIntencao'], $chaveIntencao['anoIntencao']);
        unset($chaveIntencao);
        $intencaoSelecionada = $resultSetSelect->fetchRow(DB_FETCHMODE_OBJECT);
        if (! is_null($intencaoSelecionada)) {
            // Caso exista, carrega a resposta ativa da intenção
            $resultSet = $rpIntencao->getRespostaAtivaIntencao((int) $intencaoSelecionada->cintrpsequ, (int) $intencaoSelecionada->cintrpsano);

            $respostaAtiva = $resultSet->fetchRow(DB_FETCHMODE_OBJECT);

            $mensagemFeedback = ClaRegistroPrecoIntencao::MENSAGEM_INTENCAO_RESPOSTA_ATIVA;

            if (is_null($respostaAtiva)) {
                $commited = $rpIntencao->deleteIntencao((int) $intencaoSelecionada->cintrpsequ, (int) $intencaoSelecionada->cintrpsano);

                $mensagemFeedback = ClaRegistroPrecoIntencao::MENSAGEM_INTENCAO_EXCLUIDA_SUCESSO;

                if ($commited instanceof DB_error) {
                    $mensagemFeedback = ClaRegistroPrecoIntencao::MENSAGEM_INTENCAO_EXCLUIDA_ERRO;
                }

                $_SESSION['mensagemFeedback'] = $mensagemFeedback;
                header('Location: CadRegistroPrecoIntencaoPesquisar.php');
                exit();

                return;
            }

            $gui->getTemplate()->MENSAGEM_ERRO = ExibeMensStr($mensagemFeedback, 1, 0);
            $gui->getTemplate()->block('BLOCO_ERRO', true);
        }
    }

    /**
     * Proccess Alterar.
     */
    public function acaoAlterar($gui)
    {
        unset($_SESSION['mensagemFeedback']);
        $negocio = $this->getNegocio();
        $negocio->carregarIntencaoBancoDados();
        $entidade = $negocio->getIntencao();
        $mensagemFeedback = '';
        $atualizado = false;
        if ($negocio->alterar($entidade)) {
            $mensagemFeedback = ClaRegistroPrecoIntencao::MENSAGEM_INTENCAO_ATUALIZADA_SUCESSO;
            $atualizado = true;
        } else {
            $mensagemFeedback = implode(', ', $_SESSION['mensagemFeedback']);
        }

        $this->consultarItensIntencaoBancoDados($entidade);
        $_SESSION['mensagemFeedback'] = $mensagemFeedback;
        if ($atualizado) {
            unset($_SESSION['intencaoItem'], $_SESSION['intencaoItemOld'], $_SESSION['item']);
            header('Location: CadRegistroPrecoIntencaoPesquisar.php');
            exit();
        }
    }

    /**
     * Consultar Itens da Intenção.
     *
     * @param stdClass $intencao
     *            [description]
     */
    public function consultarItensIntencaoBancoDados($intencao)
    {
        unset($_SESSION['intencaoItem']);
        $_SESSION['intencaoItem'] = array();
        $_SESSION['intencaoItem'] = $this->getNegocio()
            ->getDados()
            ->consultarItensIntencaoRP(new Negocio_ValorObjeto_Cintrpsequ($intencao->cintrpsequ), new Negocio_ValorObjeto_Cintrpsano($intencao->cintrpsano));

        $_SESSION['intencaoItemOld'] = $_SESSION['intencaoItem'];
    }

    /**
     * Build Intencao Item.
     *
     * @param int $intencaoId
     *            [description]
     *
     * @return array [description]
     */
    public static function mapearItem($contador)
    {
        $item = array();
        if ('CADUM' == (string) $_POST['Tipo'][$contador]) {
            $item['cmatepsequ'] = $_POST['CodigoReduzido'][$contador];
            $item['eitirpdescmat'] = $_POST['DescricaoDetalhada'][$contador];
        } else {
            $item['cservpsequ'] = $_POST['CodigoReduzido'][$contador];
            $item['eitirpdescse'] = $_POST['DescricaoDetalhada'][$contador];
        }

        $item['aitirporde'] = $contador + 1;
        $item['vitirpvues'] = moeda2float($_POST['ValorUnitarioEstimado'][$contador]);

        return $item;
    }

    /**
     * Mapear Entidade intenção.
     *
     * @param stdClass $entidade
     *            [description]
     *
     * @return stdClass [description]
     */
    public static function mapearEntidadeIntencao($entidade)
    {
        $dataLimite = new DateTime(ClaHelper::converterDataBrParaBanco(filter_var($_POST['DataLimite'], FILTER_SANITIZE_STRING)));
        $dataFormatada = (string) $dataLimite->format('Y-m-d H:i:s');
        $entidade->tintrpdlim = $dataFormatada;
        $objeto = (string) $_POST['ObjetoIntencao'];
        $entidade->xintrpobje = strtoupper(html_entity_decode($objeto));
        $observacao = (string) $_POST['ObservacaoIntencao'];
        $entidade->xintrpobse = strtoupper(html_entity_decode($observacao));
        $entidade->cusupocodi = (int) $_SESSION['_cusupocodi_'];
        $entidade->tintrpulat = 'NOW()';

        return $entidade;
    }

    /**
     * Collector Registro Preco Intencao Item Entity.
     */
    public static function mapearItemIntencaoEntidade(array $item, $entidade)
    {
        $cmatepsequ = null;
        if (isset($item['cmatepsequ'])) {
            $cmatepsequ = $item['cmatepsequ'];
        }
        $entidade->cmatepsequ = $cmatepsequ;
        $cservpsequ = null;
        if (isset($item['cservpsequ'])) {
            $cservpsequ = $item['cservpsequ'];
        }
        $entidade->cservpsequ = $cservpsequ;
        $entidade->aitirporde = $item['aitirporde'];

        $entidade->vitirpvues = 0;
        if (! empty($item['vitirpvues'])) {
            $entidade->vitirpvues = $item['vitirpvues'];
        }

        $eitirpdescmat = null;
        if (isset($item['eitirpdescmat'])) {
            $eitirpdescmat = $item['eitirpdescmat'];
        }
        $entidade->eitirpdescmat = strtoupper2(html_entity_decode($eitirpdescmat));
        $eitirpdescse = null;
        if (isset($item['eitirpdescse'])) {
            $eitirpdescse = $item['eitirpdescse'];
        }
        $entidade->eitirpdescse = strtoupper2(html_entity_decode($eitirpdescse));
        $entidade->tintrpdcad = 'NOW()';
        $cusupocodi = $entidade->cusupocodi;
        $entidade->cusupocodi = (int) $cusupocodi;
        $entidade->titirpulat = 'NOW()';

        return $entidade;
    }
}

/**
 * A camada de Interface Gráfica com o Usuário é a camada que conterá o código que irá implementar a interação
 * do sistema com os usuários (telas, relatórios e troca de dados).
 *
 * Utiliza serviços da camada de Adaptação e Transformação.
 */
class RegistroPreco_UI_CadRegistroPrecoIntencaoManter extends UI_Abstrata
{

    /**
     * Initialize variable item.
     */
    private function inicializaValorItem()
    {
        $this->getTemplate()->VALOR_MATERIAL = '';
        $this->getTemplate()->VALOR_UNITARIO_ESTIMADO = '0,0000';
        $this->getTemplate()->VALOR_TIPO = null;
        $this->getTemplate()->VALOR_CODIGO_REDUZIDO = null;
        $this->getTemplate()->VALOR_DESCRICAO = '';
        $this->getTemplate()->VALOR_DESCRICAO_DETALHADA = '';
        $this->getTemplate()->VALOR_ESTIMADO_TRP = '---';
    }

    /**
     * build Select Orgao.
     */
    private function montarSelectOrgaos($intencao)
    {
        $rpIntencao = new ClaRegistroPrecoIntencao();
        $res = $rpIntencao->getOrgaosLicitantesAtivos();
        $row = null;
        $atual = $rpIntencao->getOrgaoLicitanteIntencao((int) $intencao->cintrpsequ, (int) $intencao->cintrpsano);

        if (isset($_POST['Orgaos'])) {
            $atual = $_POST['Orgaos'];
        }

        $notSelected = array();
        while ($res->fetchInto($row, DB_FETCHMODE_OBJECT)) {

            // verificando se a opção atual deve ter o atributo "selected"
            if (in_array($row->corglicodi, $atual)) {
                $this->getTemplate()->VALOR_ITEM_ORGAO = $row->corglicodi;
                $this->getTemplate()->ITEM_ORGAO = $row->eorglidesc;
                $this->getTemplate()->ITEM_ORGAO_SELECIONADO = 'selected';
                $this->getTemplate()->block('BLOCO_ITEM_ORGAO');
            } else {
                $notSelected[$row->corglicodi] = $row->eorglidesc;
                $this->getTemplate()->clear('ITEM_ORGAO_SELECIONADO');
            }
        }

        foreach($notSelected as $key => $value) {
            $this->getTemplate()->VALOR_ITEM_ORGAO = $key;
            $this->getTemplate()->ITEM_ORGAO = $value;
            $this->getTemplate()->block('BLOCO_ITEM_ORGAO_');
        }
    }

    /**
     * Monta os itens na lista da intenção.
     *
     * @param stdClass $intencao
     *            [description]
     *
     * @return [type] [description]
     */
    private function montarItensIncluidoIntencao($intencao)
    {
        $botao = isset($_POST['Botao']) ? $_POST['Botao'] : null;
        if (empty($_SESSION['intencaoItem']) && $botao != 'Retirar') {
            $this->getAdaptacao()->consultarItensIntencaoBancoDados($intencao);
        }

        $this->coletarListaItensIntencao();
    }

    /**
     *
     * @param unknown $citirpsequ
     * @return boolean
     */
    private function existeItemNaListaIRP($citirpsequ)
    {
        $existe = false;
        foreach ($_SESSION['intencaoItemOld'] as $item) {
            if ($item->citirpsequ == $citirpsequ) {
                $existe = true;
                break;
            }
        }
        return $existe;
    }

    /**
     */
    private function coletarListaItensIntencao()
    {
        global $SimboloConcatenacaoArray, $SimboloConcatenacaoDesc;
        $strIndexSession = 'intencaoItem';
        Servico_Item::collectorSessionItem($strIndexSession);

        $countItem = count($_SESSION['intencaoItem']);
        $contador = 0;
        if ($countItem > 0) {
            for ($i = 0; $i < $countItem; ++ $i) {
                $this->inicializaValorItem();
                if (! is_object($_SESSION['intencaoItem'][$i])) {
                    $dados = explode($SimboloConcatenacaoArray, $_SESSION['intencaoItem'][$i]);

                    $this->getTemplate()->VALOR_ITEM = ++ $contador;
                    $this->getTemplate()->VALOR_MATERIAL = $_SESSION['intencaoItem'][$i];

                    $descricao = explode($SimboloConcatenacaoDesc, $dados[0]);

                    $this->getTemplate()->VALOR_DESCRICAO = strtoupper2($descricao[0]);
                    $this->getTemplate()->VALOR_CODIGO_REDUZIDO = $dados[1];
                    $this->getTemplate()->VALOR_TIPO = 'CADUM';
                    if ($dados[3] == 'S') {
                        $this->getTemplate()->VALOR_TIPO = 'CADUS';
                        if (! empty($_POST['DescricaoDetalhada'][$i])) {
                            $this->getTemplate()->VALOR_DESCRICAO_DETALHADA = strtoupper2($_POST['DescricaoDetalhada'][$i]);
                        }
                        $this->getTemplate()->block('BLOCO_TEXTAREA_DESCRICAO_DETALHADA');
                    }
                    if ($this->getTemplate()->exists('VALOR_CODIGO_REDUZIDO') && $this->getTemplate()->VALOR_TIPO == 'CADUM') {
                        $valorTRP = calcularValorTrp(Conexao(), 2, (int) $this->getTemplate()->VALOR_CODIGO_REDUZIDO);
                        $this->getTemplate()->VALOR_ESTIMADO_TRP = empty($valorTRP) ? '0,0000' : converte_valor_estoques($valorTRP);
                        $rpIntencao = new ClaRegistroPrecoIntencao();
                        if (! $rpIntencao->isMaterialGenerico((int) $this->getTemplate()->VALOR_CODIGO_REDUZIDO)) {
                            $this->getTemplate()->VALOR_DESCRICAO_DETALHADA = '---';
                            $this->getTemplate()->block('BLOCO_SEM_DESCRICAO_DETALHADA');
                        } else {
                            if (! empty($_POST['DescricaoDetalhada'][$i])) {
                                $this->getTemplate()->VALOR_DESCRICAO_DETALHADA = strtoupper2($_POST['DescricaoDetalhada'][$i]);
                            }
                            $this->getTemplate()->block('BLOCO_TEXTAREA_DESCRICAO_DETALHADA');
                        }
                    }
                } else {
                    ++ $contador;
                    $row = $_SESSION['intencaoItem'][$i];
                    $this->getTemplate()->VALOR_ITEM = $contador;
                    $this->getTemplate()->VALOR_UNITARIO_ESTIMADO = converte_valor_estoques($row->vitirpvues);
                    $this->getTemplate()->VALOR_TIPO = 'CADUM';
                    $this->getTemplate()->VALOR_CODIGO_REDUZIDO = $row->cmatepsequ;
                    $this->getTemplate()->VALOR_DESCRICAO = strtoupper2($row->ematepdesc);
                    // Verifica se é serviço para obter do objeto atributos específicos
                    if (isset($row->cservpsequ)) {
                        $this->getTemplate()->VALOR_TIPO = 'CADUS';
                        $this->getTemplate()->VALOR_CODIGO_REDUZIDO = $row->cservpsequ;
                        $this->getTemplate()->VALOR_DESCRICAO = strtoupper2($row->eservpdesc);
                        $this->getTemplate()->VALOR_DESCRICAO_DETALHADA = strtoupper2($row->eitirpdescse);
                        if (! empty($_POST['DescricaoDetalhada'][$i])) {
                            $this->getTemplate()->VALOR_DESCRICAO_DETALHADA = strtoupper2($_POST['DescricaoDetalhada'][$i]);
                        }
                        $this->getTemplate()->block('BLOCO_TEXTAREA_DESCRICAO_DETALHADA');
                    } else {
                        // Se o material não for genérico não exibe descrição detalhada
                        if ($row->fmatepgene == 'S') {
                            $this->getTemplate()->VALOR_DESCRICAO_DETALHADA = strtoupper2($row->eitirpdescmat);
                            if (! empty($_POST['DescricaoDetalhada'][$i])) {
                                $this->getTemplate()->VALOR_DESCRICAO_DETALHADA = strtoupper2($_POST['DescricaoDetalhada'][$i]);
                            }
                            $this->getTemplate()->block('BLOCO_TEXTAREA_DESCRICAO_DETALHADA');
                        } else {
                            $this->getTemplate()->VALOR_DESCRICAO_DETALHADA = '---';
                            $this->getTemplate()->block('BLOCO_SEM_DESCRICAO_DETALHADA');
                        }
                        $valorTRP = calcularValorTrp(Conexao(), 2, $this->getTemplate()->VALOR_CODIGO_REDUZIDO);
                        $this->getTemplate()->VALOR_ESTIMADO_TRP = converte_valor_estoques($valorTRP);
                    }
                }

                if (isset($_POST['ValorUnitarioEstimado'][$i])) {
                    $this->getTemplate()->VALOR_UNITARIO_ESTIMADO = $_POST['ValorUnitarioEstimado'][$i];
                }

                $this->getTemplate()->block('BLOCO_LISTAGEM_ITEM');
            }
            $this->getTemplate()->block('BLOCO_HEADER_LISTAGEM_ITEM');
            $this->getTemplate()->block('BLOCO_BOTAO_RETIRAR_ITEM');
        }
    }

    /**
     *
     * @param unknown $intencao
     */
    private function configuracaoInicial($intencao)
    {
        $tpl = $this->getTemplate();

        $tpl->VALOR_NUMERO_INTENCAO = substr($intencao->cintrpsequ + 10000, 1) . '/' . $intencao->cintrpsano;
        $tpl->VALOR_DATA_CADASTRAMENTO_INTENCAO = ClaHelper::converterDataBancoParaBr($intencao->tintrpdcad);
        $tpl->VALOR_DATA_LIMITE_INTENCAO = ClaHelper::converterDataBancoParaBr($intencao->tintrpdlim);
        if (! empty($_POST['DataLimite'])) {
            $tpl->VALOR_DATA_LIMITE_INTENCAO = $_POST['DataLimite'];
        }
        $tpl->VALOR_OBJETO_INTENCAO = $intencao->xintrpobje;
        if (! empty($_POST['ObjetoIntencao'])) {
            $tpl->VALOR_OBJETO_INTENCAO = strtoupper2($_POST['ObjetoIntencao']);
        }
        $tpl->VALOR_OBSERVACAO_INTENCAO = $intencao->xintrpobse;
        if (! empty($_POST['ObservacaoIntencao'])) {
            $tpl->VALOR_OBSERVACAO_INTENCAO = strtoupper2($_POST['ObservacaoIntencao']);
        }
        $tpl->VALOR_SITUACAO_ATUAL_INTENCAO = $intencao->fintrpsitu;
        $tpl->VALOR_TAMANHO_MAX_OBJETO = Dados_ParametrosGerais::consultarParametrosGerais()->qpargetmaobjeto;
        $situacao = ($intencao->fintrpsitu == 'A') ? 'INATIVAR' : 'ATIVAR';
        $tpl->block('BLOCO_BOTAO_' . $situacao);
    }

    public function __construct()
    {
        $template = new TemplatePaginaPadrao('templates/CadRegistroPrecoIntencaoManter.html', 'Registro de Preço > Intenção > Manter');
        $this->setTemplate($template);
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see UI_Abstrata::getAdaptacao()
     */
    public function getAdaptacao()
    {
        $this->setAdaptacao(new RegistroPreco_Adaptacao_CadRegistroPrecoIntencaoManter());
        return parent::getAdaptacao();
    }

    /**
     * Plotar Tela Inicial.
     *
     * @param stdClass $intencao
     *            [description]
     */
    public function plotarTelaInicial()
    {
        $this->imprimeBlocoMensagem();

        if ($_SERVER['REQUEST_METHOD'] == 'GET') {
            $this->garbageCollection();
        }

        $negocio = $this->getAdaptacao()->getNegocio();
        $negocio->carregarIntencaoBancoDados();
        $this->carregarIntencaoDocumentos();
        $this->configuracaoInicial($negocio->getIntencao());
        $this->montarSelectOrgaos($negocio->getIntencao());
        $this->montarItensIncluidoIntencao($negocio->getIntencao());
    }

    public function carregarIntencaoDocumentos() {
        $negocio = $this->getAdaptacao()->getNegocio();
        $chaveIntencao = $negocio->getChaveIntencao();
        if (! empty($chaveIntencao['sequencialIntencao']) && ! empty($chaveIntencao['anoIntencao'])) {
            $this->getTemplate()->VALOR_DOCUMENTOS_ATA = '';

            if (empty($_SESSION['Arquivos_Upload'])) {
                $documentos = $negocio->consultarDocumentos($chaveIntencao['sequencialIntencao'], $chaveIntencao['anoIntencao']);

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

        $this->plotarTelaInicial();
    }

    public function removerDocumento()
    {
        $idDocumento = filter_var($_POST['documentoExcluir'], FILTER_VALIDATE_INT);
        if (! is_int($idDocumento)) {
            throw new Exception("Error Processing Request", 1);
        }

        unset($_SESSION['Arquivos_Upload']['conteudo'][$idDocumento]);
        unset($_SESSION['Arquivos_Upload']['nome'][$idDocumento]);
        $_SESSION['Arquivos_Upload']['nome'] = array_values($_SESSION['Arquivos_Upload']['nome']);
        $_SESSION['Arquivos_Upload']['conteudo'] = array_values($_SESSION['Arquivos_Upload']['conteudo']);

        $this->plotarTelaInicial();
    }
}

$gui = new RegistroPreco_UI_CadRegistroPrecoIntencaoManter();

if (isset($_POST) && ! empty($_POST)) {
    $_POST = filter_var_array($_POST, FILTER_SANITIZE_SPECIAL_CHARS);
}

if (isset($_GET) && ! empty($_GET)) {
    $_GET = filter_var_array($_GET, FILTER_SANITIZE_SPECIAL_CHARS);
}

if (isset($_REQUEST) && ! empty($_REQUEST)) {
    $_REQUEST = filter_var_array($_REQUEST, FILTER_SANITIZE_SPECIAL_CHARS);
}

$botao = isset($_REQUEST['Botao']) ? filter_var($_REQUEST['Botao'], FILTER_SANITIZE_STRING) : null;

switch ($botao) {
    case 'Alterar':
        $gui->getAdaptacao()->acaoAlterar($gui);
        $gui->plotarTelaInicial();
        break;
    case 'AlterarSituacao':
        $gui->getAdaptacao()->acaoAlterarSituacao($gui);
        // $gui->plotarTelaInicial();
        break;
    case 'Excluir':
        $gui->getAdaptacao()->acaoExcluirIntencao($gui);
        $gui->plotarTelaInicial();
        break;
    case 'Voltar':
        $gui->getAdaptacao()->acaoVoltar();
        break;
    case 'Retirar':
        $gui->getAdaptacao()->acaoRetirar();
        $gui->plotarTelaInicial();
        break;
    case 'InserirDocumento':
        $gui->inserirDocumento();
        break;
    case 'RemoverDocumento':
        $gui->removerDocumento();
        break;
    default:
        $gui->plotarTelaInicial();
        break;
}

echo $gui->getTemplate()->show();