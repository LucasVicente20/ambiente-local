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
 * @version   Git: $Id:$
 */
#-----------------------------------------------
# Alterado: Pitang Agile TI - Caio Coutinho
# Data: 29/06/2018
# Objetivo: Tarefa Redmine #198228
#-----------------------------------------------
# Alterado: Pitang Agile TI - Caio Coutinho
# Data: 11/07/2018
# Objetivo: Tarefa Redmine #198983
#-----------------------------------------------
# Alterado: Pitang Agile TI - Caio Coutinho
# Data: 24/08/2018
# Objetivo: Tarefa Redmine #200289
#-----------------------------------------------
# Alterado: Pitang Agile TI - Caio Coutinho
# Data: 27/08/2018
# Objetivo: Tarefa Redmine 201665
#-----------------------------------------------

if (! @require_once dirname(__FILE__) . "/../bootstrap.php") {
    throw new Exception("Error Processing Request - Bootstrap", 1);
}

/**
 * A camada de Dados conterá o código que irá realizar todo o acesso aos dados.
 *
 * @author jfsi
 *
 */
class RegistroPreco_Dados_CadAtaRegistroPrecoCaronaAtaExternaIncluir extends Dados_Abstrata
{
    /**
     *
     * @param integer $ata
     * @param integer $item
     * @return string
     */
    public function sqlQuantidadeItemAtaCarona($ata, $item)
    {
        $sql = "
            SELECT SUM(COALESCE(coe.acoeitqtat,0) + COALESCE(cia.aitcrpqtat,0)) AS qtdTotalOrgao
            FROM sfpc.tbcaronaorgaoexternoitem coe 
                INNER JOIN sfpc.tbitemataregistropreconova iarpn 
                    ON iarpn.carpnosequ = coe.carpnosequ AND iarpn.citarpsequ = coe.citarpsequ 
                LEFT OUTER join sfpc.tbitemcaronainternaatarp cia 
                    ON coe.carpnosequ = cia.carpnosequ and coe.citarpsequ = cia.citarpsequ
            WHERE coe.carpnosequ = %d
                AND iarpn.cmatepsequ = %d
                OR iarpn.cservpsequ = %d
        ";

        $db = Conexao();
        $res = executarSQL($db, sprintf($sql, $ata, $item, $item));

        while ($res->fetchInto($item, DB_FETCHMODE_OBJECT)) {
            $soma = $item;
        }

        return $soma->qtdtotalorgao;
    }

    public function sqlQuantidadeItemCaronaInterna($item, $orgaoExterno) {
        $sql = " SELECT SUM(COALESCE(coei.acoeitqtat,0)) as acoeitqtat FROM sfpc.tbcaronaorgaoexterno coe
                 LEFT JOIN sfpc.tbcaronaorgaoexternoitem coei 
                    ON coei.ccaroesequ = coe.ccaroesequ
                    AND coei.carpnosequ = coe.carpnosequ
                 INNER JOIN sfpc.tbataregistroprecointerna arpi ON
                    coei.carpnosequ = arpi.carpnosequ
                    AND coe.carpnosequ = arpi.carpnosequ
                 WHERE (coei.carpnosequ = ".$item->carpnosequ." OR arpi.carpnoseq1 = ".$item->carpnosequ.")
                    AND coei.citarpsequ = ".$item->citarpsequ."
                    AND coe.ecaroeorgg like '".strtoupper2($orgaoExterno)."' ";

        $db = Conexao();
        $res = executarSQL($db, $sql);

        while ($res->fetchInto($item, DB_FETCHMODE_OBJECT)) {
            $soma = $item;
        }
        
        return $soma;
    }

    public function sqlProximoCarona($ata)
    {
        $sql = "select max(ccaroesequ) as atual from sfpc.tbcaronaorgaoexterno where carpnosequ= %d";

        $db = Conexao();
        $res = executarSQL($db, sprintf($sql, $ata));

        while ($res->fetchInto($item, DB_FETCHMODE_OBJECT)) {
            $max = $item;
        }

        return $max->atual;
    }

    public function sqlConsultaCarona($ata, $orgao) {
        $sql = "select count(*) from sfpc.tbcaronaorgaoexterno where carpnosequ=" . $ata . " and ecaroeorgg = '" . $orgao ."'";
        return $sql;
    }

    public function sqlAtualizarAta($ata)
    {
        $sql = "UPDATE sfpc.tbataregistroprecointerna";
        $sql .= " SET tarpinulat = now()";
        $sql .= " WHERE carpnosequ = %d";

        $sql = sprintf($sql, $ata);
        return $sql;
    }

    public function sqlInserCarona($seqCarona, $ata, $orgaoExternoCarona, $orgao)
    {
        $sql = "INSERT INTO sfpc.tbcaronaorgaoexterno";
        $sql .= " (ccaroesequ, carpnosequ, ecaroeorgg, tcaroeincl, cusupocodi, tcaroeulat)";
        $sql .= " VALUES(%d, %d, '%s', clock_timestamp(), %d, now())";

        $sql = sprintf($sql, $seqCarona, $ata, $orgaoExternoCarona, $_SESSION['_cusupocodi_']);

        return $sql;
    }

    public function sqlInserCaronaItens($seqCarona, $seqitem, $quantidade, $ata, $valor)
    {
        $sql = "INSERT INTO sfpc.tbcaronaorgaoexternoitem";
        $sql .= " (ccaroesequ, citarpsequ, acoeitqtat, cusupocodi, tcoeitulat, carpnosequ, vcoeitvuti)";
        $sql .= " VALUES(".$seqCarona.", ".$seqitem.", ".$quantidade.", ".$_SESSION['_cusupocodi_'].", now(), ".$ata.",".$valor.")";
        //$sql = sprintf($sql, $seqCarona, $seqitem, $quantidade, $_SESSION['_cusupocodi_'], $ata);
        return $sql;
    }

    /**
     * [sqlItemAtaNova description]
     *
     * @param [type] $sequencialAta
     *            [description]
     * @return [type] [description]
     */
    private function sqlItemAtaNova($sequencialAta)
    {
        $sql = "
        SELECT
            i.aitarporde,
            i.eitarpdescmat,
            i.eitarpdescse,
            i.cmatepsequ,
            i.cservpsequ,
            i.citarpnuml,
            u.eunidmdesc,
            i.citarpsequ,
            i.aitarpqtor,
            i.vitarpvatu
        FROM
            sfpc.tbitemataregistropreconova i
        LEFT OUTER JOIN
            sfpc.tbmaterialPortal m
            ON m.cmatepsequ = i.cmatepsequ
        LEFT OUTER JOIN
            sfpc.tbunidadedeMedida u
            ON m.cunidmcodi = u.cunidmcodi
                AND m.cmatepsequ = i.cmatepsequ
        WHERE i.carpnosequ= %d ";

        return sprintf($sql, $sequencialAta);
    }
 
    private function sqlAtaConsultarLimiteCarona()
    {
        $sql  = "select qpargecaro ";
        $sql .= " from sfpc.tbparametrosgerais pa limit 1";

        return $sql;
    }

    public function consultarDadosAta($processo, $orgao, $ano, $numeroAta   )
    {
        $repositorio = new Negocio_Repositorio_AtaRegistroPrecoNova();
        return $repositorio->consultarAtaPorChave($processo, $orgao, $ano, $numeroAta);
    }


    public function consultarLicitacaoAtaInterna($ano, $processo, $orgaoUsuario, $comissao, $grupo)
    {
        $sql = Dados_Sql_AtaRegistroPrecoNova::sqlLicitacaoAtaInternaNova($ano, $processo, $orgaoUsuario, $comissao, $grupo);
        $resultado = ClaDatabasePostgresql::executarSQL($sql);
        ClaDatabasePostgresql::hasError($resultado);

        return $resultado;
    }
    
    /**
     *
     * @param integer $carpnosequ
     * @return NULL
     */
    public function consultarItemAta($carpnosequ)
    {
        $db = Conexao();
        $sql = Dados_Sql_ItemAtaRegistroPrecoNova::sqlFind(new Negocio_ValorObjeto_Carpnosequ($carpnosequ));
        $res = executarSQL($db, $sql);

        $itens = array();
        $item = null;
        while ($res->fetchInto($item, DB_FETCHMODE_OBJECT)) {
            $itens[] = $item;
        }
        if (PEAR::isError($res)) {
            ExibeErroBD(__FILE__ . "\nLinha: ".__LINE__."\nSql: " . $database->getMessage());
        }
        $db->disconnect();
        return $itens;
    }

    public function consultarLimiteCarona()
    {	
        $db = Conexao();
        $sql = $this->sqlAtaConsultarLimiteCarona();
		
        $res = executarSQL($db, $sql);
        $res->fetchInto($res, DB_FETCHMODE_OBJECT);
        $this->hasError($res);
        $db->disconnect();

        return $res;
    }

    public function sqlConsultarCentroDeCustoUsuario($cgrempcodi, $cusupocodi, $corglicodi)
    {
        $sql = "
            SELECT
                   ccp.ccenpocorg, ccp.ccenpounid, ccp.corglicodi
              FROM sfpc.tbcentrocustoportal ccp
             WHERE 1=1
        ";

        if ($corglicodi != null || $corglicodi != "") {
          $sql .= " AND ccp.corglicodi = %d";
        }

        return sprintf($sql, $corglicodi);
    }

    public function sqlConsultarProcurarAta($carpnosequ)
    {
        $sql = "
            SELECT * FROM sfpc.tbataregistroprecointerna WHERE carpnosequ = %d             
        ";

        return sprintf($sql, $carpnosequ);
    }
}

/**
 * A camada de Negócio conterá o código que irá implementar todas as regras de negócio do sistema.
 *
 * Utiliza serviços da camada de Dados.
 *
 * @author jfsi
 *
 */
class RegistroPreco_Negocio_CadAtaRegistroPrecoCaronaAtaExternaIncluir extends Negocio_Abstrata
{
    /**
     *
     * {@inheritDoc}
     *
     * @see Negocio_Abstrata::getDados()
     */
    public function getDados()
    {
        $this->setDados(new RegistroPreco_Dados_CadAtaRegistroPrecoCaronaAtaExternaIncluir());
        return parent::getDados();
    }

    /**
     *
     * @param unknown $ata
     * @return number|unknown
     */
    public function recuperarSequencialAta($ata)
    {
        $numeroAta = $ata;
        $codigo = $ata;

        if (strpos($ata, ".")) {
            $numeroAtaExp = explode(".", $numeroAta);
            $numeroAtaCodi = explode("/", $numeroAtaExp[1]);
            $codigo = (int) $numeroAtaCodi;
        }

        return $codigo;
    }



    public function validacao()
    {
        $mensagem = array();
        $orgaoCarona = filter_var($_POST['orgaoCarona'], FILTER_SANITIZE_STRING);
        $elem = new Element('a');
        $elem->set('class', 'titulo2');

        if (empty($orgaoCarona)) {
            $elem->set('text', 'Informe: O Órgão Externo Solicitante da Carona');
            $mensagem[] = $elem->build();
        }

        $sqlExisteCaronaOrgao = $this->getDados()->sqlConsultaCarona($_SESSION['seqAta'], $_POST['orgaoCarona']);
        /*$jaExisteCaronaParaOrgaoExterno   = ClaDatabasePostgresql::executarSQL($sqlExisteCaronaOrgao);
        if ($jaExisteCaronaParaOrgaoExterno[0]->count){
            $elem->set('text', '<br />Já existe uma carona para este Órgão Externo Solicitante');
            $mensagem[] = $elem->build();
        }*/

        $numeroItens = count($_POST['atual']);
        $qtdOriginal = $_POST['QtdAta'];
        $valoresItens = $_POST['atual'];
        $saldoItem = $_POST['saldo'];

        $limiteMaximoCarona = $this->getDados()->consultarLimiteCarona();

        for ($i = 0; $i < $numeroItens; $i ++) {
            $qtdUtilizada = $valoresItens[$i]; 
            $saldoCarona = $saldoItem[$i];

            if ($qtdUtilizada == null && $saldoCarona > 0){
                // $elem->set('text', '<br />A Quantidade Utilizada ou Quantidade Total do Item de Ordº ' . $_POST['ordemItem'][$i] . ' não pode ser nulo');
                // $mensagem[] = $elem->build();
            } else {
                if(moeda2float($qtdUtilizada) > moeda2float($qtdOriginal[$i])){
                    $elem->set('text', '<br />A Quantidade Utilizada do Item de Ordº ' .$_POST['ordemItem'][$i] . ' não pode ser superior a Quantidade Total do Item');
                    $mensagem[] = $elem->build();
                }

                if (moeda2float($qtdUtilizada) > moeda2float($saldoCarona)){
                    $elem->set('text', '<br />O saldo Total do Item de Ordº ' . $_POST['ordemItem'][$i] . ' não pode ser superior que a Quantidade Total da Ata');
                    $mensagem[] = $elem->build();
                }
            }
        }

        return $mensagem;
    }

    private function existeCarona($carpnosequ, $orgaoExterno)
    {
        $db = Conexao();
        $sql = $this->getDados()->sqlConsultaCarona($_SESSION['ata'], $_POST['orgaoCarona']);
        $res = ClaDatabasePostgresql::executarSQL($sql);

        $caronas = array();
        $carona = null;
        while ($res->fetchInto($item, DB_FETCHMODE_OBJECT)) {
            $itens[] = $item;
        }
        if (PEAR::isError($res)) {
            ExibeErroBD(__FILE__ . "\nLinha: ".__LINE__."\nSql: " . $database->getMessage());
        }
        $db->disconnect();
        return $itens;
    }

    public function salvarDadosCarona()
    {
        /*
         * Valida as condições necessárias.
         * Caso a mensagem diferente de vazia
         * Há um Erro
         */
        $mensagem = $this->validacao();
        if (!empty($mensagem)) {
            $_SESSION['mensagemFeedback'] = $mensagem;
            return false;
        }

        $tipoControle    = $_REQUEST['tipoControle'];
        $orgao           = $_REQUEST['orgaoCarona'];
        $valoresQtdItens = $_REQUEST['atual'];
        $valoresItens    = $_REQUEST['atualValor'];
        $ata             = (int)$_SESSION['seqAta'];
        $orgaoExternoCarona = $_POST['orgaoCarona'];
        $orgaoExternoCarona = mb_strtoupper($orgaoExternoCarona, 'UTF-8');

        $numeroAta      = $this->recuperarSequencialAta($ata);
        $itens          = $this->getDados()->consultarItemAta($numeroAta);
        $resultado      = $this->getDados()->sqlProximoCarona($ata);
        $seqCarona      = $resultado + 1;

        $db = Conexao();
        $db->autoCommit(false);
        $db->query("BEGIN TRANSACTION");

        $sqlAtualizarAta = $this->getDados()->sqlAtualizarAta($ata, $orgao);
        executarTransacao($db, $sqlAtualizarAta);

        $sqlCarona = $this->getDados()->sqlInserCarona($seqCarona, $ata, $orgaoExternoCarona, $orgao);
        executarTransacao($db, $sqlCarona);

        $iterator = 0;
        foreach ($itens as $item) {            
            if( ($valoresQtdItens[$iterator] == null || $valoresQtdItens[$iterator] == 0) && ($valoresItens[$iterator] == null || $valoresItens[$iterator] == 0) ){
                $iterator ++;
                continue;
            }

            $valoresQtdItens[$iterator] = (empty($valoresQtdItens[$iterator])) ? 0 : $valoresQtdItens[$iterator];
            $valoresItens[$iterator]    = (empty($valoresItens[$iterator])) ? 0 : $valoresItens[$iterator]; 

            $sqlItensCarona = $this->getDados()->sqlInserCaronaItens($seqCarona, $item->citarpsequ, moeda2float($valoresQtdItens[$iterator], 4), $ata, moeda2float($valoresItens[$iterator], 4));                
            executarTransacao($db, $sqlItensCarona);
            $iterator ++;
            
        }
        $db->query("COMMIT");
        $db->query("END TRANSACTION");

        if ($commited instanceof DB_error) {
            $db->rollback();
            $_SESSION['mensagemFeedback'] = 'Erro ao atualizar os dados';
            ExibeErroBD(self::$erroPrograma . "\nLinha: " . __LINE__ . "\nSql: " . $e->getMessage());
            return false;
        }

        $db->disconnect();
        return true;
    }

    public function consultarLimiteCarona()
    {
        $limiteMaximoCarona = $this->getDados()->consultarLimiteCarona();

        if($limiteMaximoCarona != null){
            $limiteMaximoCarona = $limiteMaximoCarona->qpargecaro;
        }

        return $limiteMaximoCarona;
    }

    public function consultarDCentroDeCustoUsuario($cgrempcodi, $cusupocodi, $corglicodi)
    {   
        $db = Conexao();
        $sql = $this->getDados()->sqlConsultarCentroDeCustoUsuario($cgrempcodi, $cusupocodi, $corglicodi);
        
        $res = executarSQL($db, $sql);
        
        $itens = array();
        $item = null;
        while ($res->fetchInto($item, DB_FETCHMODE_OBJECT)) {
            $itens[] = $item;
        }
        if (PEAR::isError($res)) {
            ExibeErroBD(__FILE__ . "\nLinha: ".__LINE__."\nSql: " . $database->getMessage());
        }
        $db->disconnect();
        return $itens;
    }

    public function procurar($carpnosequ)
    {   
        $db = Conexao();
        $sql = $this->getDados()->sqlConsultarProcurarAta($carpnosequ);
        
        $res = executarSQL($db, $sql);
        
        $itens = array();
        $item = null;
        while ($res->fetchInto($item, DB_FETCHMODE_OBJECT)) {
            $itens[] = $item;
        }
        if (PEAR::isError($res)) {
            ExibeErroBD(__FILE__ . "\nLinha: ".__LINE__."\nSql: " . $database->getMessage());
        }
        $db->disconnect();
        return $itens;
    }
}

/**
 * A camada de Adaptação e Transformação conterá o código que tratará a lógica de apresentação dos resultados
 * das requisições dos usuários e a troca de dados com sistemas externos.
 *
 * Utiliza serviços da camada de Negócio.
 *
 * @author jfsi
 *
 */
class RegistroPreco_Adaptacao_CadAtaRegistroPrecoCaronaAtaExternaIncluir extends Adaptacao_Abstrata
{
    /**
     *
     * {@inheritDoc}
     *
     * @see Adaptacao_Abstrata::getNegocio()
     */
    public function getNegocio()
    {
        $this->setNegocio(new RegistroPreco_Negocio_CadAtaRegistroPrecoCaronaAtaExternaIncluir());
        return parent::getNegocio();
    }

    public function consultarLimiteCarona()
    {
        return $this->getNegocio()->consultarLimiteCarona();

    }
}

/**
 * A camada de Interface Gráfica com o Usuário é a camada que conterá o código que irá implementar a interação
 * do sistema com os usuários (telas, relatórios e troca de dados).
 *
 * Utiliza serviços da camada de Adaptação e Transformação.
 *
 * @author jfsi
 *
 */
class RegistroPreco_UI_CadAtaRegistroPrecoCaronaAtaExternaIncluir extends UI_Abstrata
{
    /**
     *
     * {@inheritDoc}
     *
     * @see UI_Abstrata::getAdaptacao()
     */
    public function getAdaptacao()
    {
        $this->setAdaptacao(new RegistroPreco_Adaptacao_CadAtaRegistroPrecoCaronaAtaExternaIncluir());
        return parent::getAdaptacao();
    }

    /**
     * [__construct description]
     */
    public function __construct()
    {
        $tipoTela = $_REQUEST['tela'];

        $this->setTemplate(new TemplatePaginaPadrao("templates/CadAtaRegistroPrecoCaronaAtaExternaIncluir.html", 'Registro de Preço > Ata Interna > Carona Órgão Externo > Incluir'));

        if (! empty($tipoTela) && $tipoTela = 'popup') {
            $this->setTemplate(new TemplatePaginaPadrao("templates/CadAtaRegistroPrecoCaronaAtaExternaIncluir.html", 'Registro de Preço > Ata Interna > Carona Órgão Externo > Incluir'));
        }
    }

    /**
     *
     * @param integer $ata
     * @param array $itens
     */
    public function plotarBlocoItemAta($ata, array $itens, $tipoControle = 0)
    {
        if ($itens == null) {
            return;
        }

        // Ajustar exibição da tabela
        $this->getTemplate()->EXIBIR_TD_VALOR = 'display:none';
        $this->getTemplate()->EXIBIR_TD_QUANTIDADE = '';
        if($tipoControle == 1) {
            $this->getTemplate()->EXIBIR_TD_VALOR = '';
            $this->getTemplate()->EXIBIR_TD_QUANTIDADE = 'display:none';
        }
        $this->getTemplate()->block("BLOCO_TR_RESULTADO_ATAS");

        $iterador = 0;
        foreach ($itens as $item) {
            $buscarCaronaOrgao = null;
            $itemCodigo = $item->cservpsequ == null ? $item->cmatepsequ : $item->cservpsequ;
            $resultado = $this->getAdaptacao()
                ->getNegocio()
                ->getDados()
                ->sqlQuantidadeItemAtaCarona($ata, $itemCodigo);

            if(!empty($_SESSION['orgaoExterno'])) {
                $buscarCaronaOrgao = $this->getAdaptacao()
                ->getNegocio()
                ->getDados()
                ->sqlQuantidadeItemCaronaInterna($item, $_SESSION['orgaoExterno']);
            }

            $quantidadeSolicitadaCarona = 0;
            if ($resultado > 0) {
                $quantidadeSolicitadaCarona = converte_valor_estoques($resultado);
            }

            $db = Conexao();
            $totalCaronaInterna = getQtdTotalOrgaoCaronaInterna($db, null, $item->carpnosequ, $item->citarpsequ);
            $totalCaronaInternaID = getQtdTotalOrgaoCaronaInternaInclusaoDireta($db, $item->carpnosequ, $item->citarpsequ);
            $totalCaronaExterna = getQtdTotalOrgaoCaronaExterna($db, $item->carpnosequ, $item->citarpsequ);
            $fatorMaxCarona = getFatorQtdMaxCarona($db);
            $db->disconnect();
            
            $total = $totalCaronaInterna + $totalCaronaInternaID + $totalCaronaExterna;
            $qtdItem = ($item->aitarpqtat != 0) ? $item->aitarpqtat : $item->aitarpqtor;
            if($tipoControle == 1) {
                $qtdItem = ($item->vitarpvatu != 0) ? $item->vitarpvatu : $item->vitarpvori;
            }

            $saldoCarona =  ($fatorMaxCarona * $qtdItem) - $total;
            if ($saldoCarona < 0) {
                $saldoCarona = 0;
            }

            // CADUM = material e CADUS = serviço
            $tipo = 'CADUM';
            if (is_null($item->cmatepsequ) == true) {
                $tipo = 'CADUS';
            }

            // Código do item
            $valorCodigo = $item->cmatepsequ;
            if ($tipo == 'CADUS') {
                $valorCodigo = $item->cservpsequ;
            }

            // Descrição do item
            $valorDescricao = $item->ematepdesc;
            if ($tipo === 'CADUS') {
                $valorDescricao = $item->eservpdesc;
            }
            
            $valorQtdOriginal = ($item->aitarpqtat != 0) ? $item->aitarpqtat : $item->aitarpqtor;
            $valorOriginal    = ($item->vitarpvatu != 0) ? $item->vitarpvatu : $item->vitarpvori;            
            $saldoValorCarona = 0;
            $saldoQtdCarona   = ($saldoCarona < $valorQtdOriginal) ? $saldoCarona : $valorQtdOriginal;
            if(!empty($buscarCaronaOrgao)) {
                $saldoQtdCarona = ($buscarCaronaOrgao->acoeitqtat < $saldoQtdCarona) ? $buscarCaronaOrgao->acoeitqtat  : $saldoQtdCarona;
            }
            
            // Ajustar exibição da tabela pelo controle
            $this->getTemplate()->EXIBIR_TD_VALOR = 'display:none';
            $this->getTemplate()->EXIBIR_TD_QUANTIDADE = '';
            if($tipoControle == 1) {
                $saldoQtdCarona = 0;
                $saldoValorCarona = ($saldoCarona < $valorOriginal) ? $saldoCarona : $valorOriginal;
                if(!empty($buscarCaronaOrgao)) {
                    $saldoValorCarona = ($buscarCaronaOrgao->vcoeitvuti < $saldoValorCarona) ? $buscarCaronaOrgao->vcoeitvuti  : $saldoValorCarona;
                }
                $this->getTemplate()->EXIBIR_TD_VALOR = '';
                $this->getTemplate()->EXIBIR_TD_QUANTIDADE = 'display:none';

            }

            $qdtSolicitada = $item->acoeitqtat;
            $valorSolicitado = $item->vcoeitvuti;;
            if (!empty($_REQUEST['atual'][$iterador]) && $tipoControle != 1){
                $qdtSolicitada = $_REQUEST['atual'][$iterador];
            } else if(!empty($_REQUEST['atualValor'][$iterador]) && $tipoControle == 1) {
                $valorSolicitado = $_REQUEST['atualValor'][$iterador];
            }

            $this->getTemplate()->ORDEM = $item->aitarporde;
            $this->getTemplate()->TIPO = $tipo;
            $this->getTemplate()->DESCRICAO = $valorDescricao;
            $this->getTemplate()->VALOR_CODIGO_REDUZIDO = $valorCodigo;
            $this->getTemplate()->UND = "UN";            
            $this->getTemplate()->QTD_ORIGINAL = converte_valor_estoques($valorQtdOriginal);
            $this->getTemplate()->VALOR_ORIGINAL = converte_valor_estoques($valorOriginal);
            $this->getTemplate()->VALOR_TOTAL_ORIGINAL = converte_valor_estoques($valorQtdOriginal * $valorOriginal);
            $this->getTemplate()->LOTE = $item->citarpnuml;

            $this->getTemplate()->VALOR_MARCA = strtoupper($item->eitarpmarc);
            $this->getTemplate()->VALOR_MODELO = strtoupper($item->eitarpmode);

            $this->getTemplate()->SALDO_QUANTIDADE_CARONA = converte_valor_estoques($saldoQtdCarona);
            $this->getTemplate()->SALDO_VALOR_CARONA = converte_valor_estoques($saldoValorCarona);
            $this->getTemplate()->VALOR_TOTAL_ATUAL = ($item->vitarpvatu * $item->acoeitqtat);
            $this->getTemplate()->INDEX = $iterador;                        
            $this->getTemplate()->QTD_SOLICITADA = $qdtSolicitada;
            $this->getTemplate()->VALOR_SOLICITADO = $valorSolicitado;

            $this->getTemplate()->block("BLOCO_RESULTADO_ATAS");
            $iterador ++;

        }
    }

    /**
     *
     * @param unknown $processo
     * @param unknown $ata
     */
    public function plotarBlocoFormulario($processo, $ata, $orgaoCarona, $dadosAta, $licitacao)
    {
        $this->getTemplate()->ATA                 = $this->getNumeroAtaInterna($dadosAta);
        $this->getTemplate()->PROCESSO            = str_pad($processo, 4, "0", STR_PAD_LEFT) . '/' . $_SESSION["ano"];
        $this->getTemplate()->ORGAO_GESTOR        = $licitacao->eorglidesc;
        $this->getTemplate()->COMISSAO_LICITACAO  = $licitacao->ecomlidesc;
		$this->getTemplate()->ORGAO_CARONA        = mb_strtoupper($orgaoCarona, 'UTF-8');
    }

    private function getNumeroAtaInterna($ata)
    {
        
        $dto = $this->getAdaptacao()->getNegocio()->consultarDCentroDeCustoUsuario($ata->cgrempcodi, $ata->cusupocodi, $ata->corglicodi);
        $objeto = current($dto);
        $ataInterna = current($this->getAdaptacao()->getNegocio()->procurar((int)$ata->carpnosequ));

        $numeroAtaFormatado = $objeto->ccenpocorg . str_pad($objeto->ccenpounid, 2, '0', STR_PAD_LEFT);
        $numeroAtaFormatado .= "." . str_pad($ataInterna->carpincodn, 4, "0", STR_PAD_LEFT) . "/" . $ataInterna->aarpinanon;

        return $numeroAtaFormatado;
    }
}

class CadAtaRegistroPrecoCaronaAtaExternaIncluir extends ProgramaAbstrato
{
    private $orgaoExterno;
    
    /**
     * [proccessVoltar description]
     *
     * @return [type] [description]
     */
    private function proccessVoltar()
    {
        $orgao = $_SESSION["orgao"];
        $ano = $_SESSION["ano"];
        $processo = $_SESSION["processo"];

        $uri = 'CadAtaRegistroPrecoCaronaAtaExternaListar.php?ano=' . $ano . '&processo=' . $processo . '&orgao=' . $orgao;
        header('Location: ' . $uri);
        exit();
    }

    /**
     */
    private function processSalvar()
    {
        $feedback = $this->getUI()
            ->getAdaptacao()
            ->getNegocio()
            ->salvarDadosCarona();

        if (!$feedback) {
            $this->getUI()->mensagemSistema(implode("", $_SESSION['mensagemFeedback']), 0, 1);
            $this->proccessPrincipal();
            return;
        }

        $orgao = $_SESSION["orgao"];
        $ano = $_SESSION["ano"];
        $processo = $_SESSION["processo"];
        
        $_SESSION['mensagemFeedback'] = 'Dados salvos com sucesso';
        
        $uri = 'CadAtaRegistroPrecoCaronaAtaExternaListar.php?ano=' . $ano . '&processo=' . $processo . '&orgao=' . $orgao;
        header('Location: ' . $uri);
        exit();
    }

    /**
     */
    private function proccessPrincipal()
    {
        $orgao        = isset($_REQUEST['orgao']) ? (int) filter_var($_REQUEST['orgao'], FILTER_SANITIZE_NUMBER_INT) : (int)$_SESSION['orgao'];
        $ano          = isset($_REQUEST['ano']) ? (int) filter_var($_REQUEST['ano'], FILTER_SANITIZE_NUMBER_INT) : (int)$_SESSION['ano'];
        $processo     = isset($_REQUEST['processo']) ? (int) filter_var($_REQUEST['processo'], FILTER_SANITIZE_NUMBER_INT) : (int)$_SESSION['processo'];
        $ata          = isset($_REQUEST['ata']) ? (int)filter_var($_REQUEST['ata'], FILTER_SANITIZE_NUMBER_INT) : (int)$_SESSION['seqAta'];
        $fullProcesso = isset($_GET['processo']) ? $_GET['processo'] : $_REQUEST['fullprocesso'];
        $_SESSION['seqAta'] = $ata;
        
        $this->getUI()->getTemplate()->ANO_SESSAO      = $ano;   
        $this->getUI()->getTemplate()->ORGAO_SESSAO    = $orgao;   
        $this->getUI()->getTemplate()->PROCESSO_SESSAO = $processo;   
        $this->getUI()->getTemplate()->FULL_PROCESSO   = $fullProcesso;   
        
        $dadosAta = $this->getUI()->getAdaptacao()->getNegocio()->getDados()->consultarDadosAta(
            $processo, $orgao, $ano, $ata
        );
        
        $dadosAta = current($dadosAta);        
        $fullProcesso = explode('-', $fullProcesso);
        $licitacao = $this->getUI()->getAdaptacao()->getNegocio()->getDados()->consultarLicitacaoAtaInterna(
            $ano, $processo, $orgao, $fullProcesso[3], $fullProcesso[2]
        );
        $licitacao = current($licitacao);
        
        $orgaoCarona = $_REQUEST['orgaoCarona'];
        $this->getUI()->getTemplate()->VALOR_TIPO_CONTROLE = $dadosAta->farpnotsal; 
        $this->getUI()->plotarBlocoFormulario($processo, $ata, $orgaoCarona, $dadosAta, $licitacao);
        $itens = $this->getUI()->getAdaptacao()->getNegocio()->getDados()->consultarItemAta($ata);
        $this->getUI()->plotarBlocoItemAta($ata, $itens, $dadosAta->farpnotsal);
        if(!empty($_SESSION['orgaoExterno'])) {
            $this->getUI()->getTemplate()->BOTAO_SALVAR = '<input type="button" name="Salvar" value="Salvar" class="botao" onclick="javascript:enviar(\'salvar\')" />';
        }
    }

    /**
     *
     * {@inheritDoc}
     *
     * @see ProgramaAbstrato::configuracao()
     */
    protected function configuracao()
    {
        $this->setUI(new RegistroPreco_UI_CadAtaRegistroPrecoCaronaAtaExternaIncluir());
    }

    /**
     *
     * {@inheritDoc}
     *
     * @see ProgramaAbstrato::frontController()
     */
    protected function frontController()
    {
        $acao = filter_var($_POST['Botao'], FILTER_SANITIZE_STRING);
        $orgaoExterno = null;

        if(isset($_POST['orgaoCarona'])) {
           $_SESSION['orgaoExterno'] = $_POST['orgaoCarona'];
        }
        
        switch ($acao) {
            case 'Pesquisar':
                $this->proccessPesquisar();
                $this->proccessPrincipal();
                break;
            case 'salvar':
                $this->processSalvar();
                break;
            case 'voltar':
                $this->proccessVoltar();
                break;
            case 'Principal':
            default:
                $this->proccessPrincipal();
                break;
        }
    }
}

ProgramaAbstrato::iniciar(new CadAtaRegistroPrecoCaronaAtaExternaIncluir());
if(isset($_SESSION['orgaoExterno'])) {
    unset($_SESSION['orgaoExterno']);
}