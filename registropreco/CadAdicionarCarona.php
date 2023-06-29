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
 * @version   GIT: EMPREL-SAD-PORTAL-COMPRAS-REGISTRO-PRECO-BL-FUNC-20160601-1550
 */

 // 220038----

if (!@require_once dirname(__FILE__)."/../bootstrap.php") {
    throw new Exception("Error Processing Request - Bootstrap", 1);
}

class CadAdicionarCarona
{
    /**
     * [$template description]
     * @var \TemplatePaginaPadrao
     */
    private $template;
    /**
     * [$variables description]
     * @var \ArrayObject
     */
    private $variables;

     /**
     * Gets the value of template.
     *
     * @return mixed
     */

    private $files;

    private function getTemplate()
    {
        return $this->template;
    }

    /**
     * Sets the value of template.
     *
     * @param TemplatePaginaPadrao $template the template
     *
     * @return self
     */
    private function setTemplate($template)
    {
        $this->template = $template;
        return $this;
    }

   /**
     * [proccessPrincipal description]
     * @param  [type] $variablesGlobals [description]
     * @return [type] [description]
     */
    private function proccessPrincipal()
    {
        $ata = $_REQUEST['ata'];
        unset($_SESSION['caronas']);
        $this->getTemplate()->ATA       = $ata;
        $this->getTemplate()->VALOR_ATA = $ata;
        if (empty($_SESSION['caronas'])) {
            $caronas    = $this->selecionarCaronas($ata);
            $itens      = $this->consultarItemAta($ata);

            foreach ($caronas as $carona) {
                $itemNovo =  $this->transformarItem($carona, $itens);
                $_SESSION['caronas'][] = $itemNovo;
            }
        }

        if (empty($_SESSION['caronas'])) {
            $this->getTemplate()->block('BLOCO_NENHUM_RESULTADO');
            $this->getTemplate()->block("BLOCO_ITEM_TOTAL");
            $this->getTemplate()->block("BLOCO_BOTAO");
            return;
        }

        $this->getTemplate()->BLOCK('BLOCO_ATA');
        $this->plotarBlocoItemAta($_SESSION['caronas'], $ata);

        $this->getTemplate()->block('BLOCO_BOTAO');
        $this->getTemplate()->block('BLOCO_RESULTADO_PEQUISA');
    }

    private function selecionarCaronas($ata)
    {
        $db = Conexao();
        $resultados = array();

        $sql  = "select * from sfpc.tbcaronaorgaoexterno coe";
        $sql .= " where coe.carpnosequ = ".$ata;
        $sql .= " order by coe.ccaroesequ";

        $resultado = executarSQL($db, $sql);

        while ($resultado->fetchInto($carona, DB_FETCHMODE_OBJECT)) {
            $resultados[] = $carona;
        }
        return $resultados;
    }

    private function consultarItemAta($numeroAta)
    {
        $resultados = array();
        $db = Conexao();
        $sql = $this->sqlItemAtaNova($numeroAta);
        $resultado = executarSQL($db, $sql);

        while ($resultado->fetchInto($item, DB_FETCHMODE_OBJECT)) {
            $resultados[] =$item;
        }

        return $resultados;
    }

    private function transformarItem($carona, $itens)
    {
        $itensTempArray = array();

        foreach ($itens as $item) {
            if ($item->ccaroesequ == $carona->ccaroesequ) {
                $itensTemp = new stdClass();

                // $itensTemp->descricao = ($item->cmatepsequ == null) ? $item->eservpdesc : $item->ematepdesc;
                $itensTemp->descricao           = ($item->cmatepsequ == null) ? $item->eitarpdescse : $item->eitarpdescmat;
                $itensTemp->ordem               = $item->aitarporde;
                $itensTemp->tipo                = ($item->cmatepsequ == null) ? 'CADUS' : 'CADUM';
                $itensTemp->codigo              = ($item->cmatepsequ == null) ? $item->cservpsequ : $item->cmatepsequ;
                $itensTemp->lote                = $item->citarpnuml;
                $itensTemp->und                 = ($item->cmatepsequ == null || empty($item->cmatepsequ)) ? 'UN' : $item->eunidmsigl;
                $itensTemp->quantidade          = $item->aitarpqtor;
                $itensTemp->qtd_maxima_carona   = $item->acoeitqtat;
                $itensTempArray[]               = $itensTemp;
            }
        }

        // echo '<pre>';
        // print_r($itensTempArray);
        // die;

        $caronaNova->itensCarona    = $itensTempArray;
        $caronaNova->orgao          = $carona->ecaroeorgg;

        return $caronaNova;
    }

    private function plotarBlocoBotao($processo, $ano, $ata)
    {
        $this->getTemplate()->VALOR_ANO_SESSAO = $ano;
        //$this->getTemplate()->VALOR_ORGAO_SESSAO = 'Externo';
        $this->getTemplate()->VALOR_PROCESSO_SESSAO = $processo;
        $this->getTemplate()->VALOR_ATA_SESSAO = $ata;
        $this->getTemplate()->block("BLOCO_BOTAO");
    }

    private function getNumeroAtaInterna($numeroAta)
    {
        $valoresExploded = explode(".", $numeroAta);
        $valorUnidadeOrc = substr($valoresExploded[0], 2, 2);

        $valorAta = str_pad($ata->corglicodi, 2, '0', STR_PAD_LEFT);
        $valorAta .= $valorUnidadeOrc . '.';
        $valorAta .= str_pad($numeroAta, 4, '0', STR_PAD_LEFT) . '/';
        $valorAta .= $ata->alicpoanop;

        return $valorAta;
    }

    private function plotarBlocoItemAta($caronas, $ata)
    {
        $orgaos = $_SESSION['Carona']['orgaos'];

        if ($caronas == null) {
            return;
        }

        //todos deveriam está no mesmo lote
        $ultimoLote = null;

        // echo '<pre>';
        // print_r($caronas);
        // die;

        $this->getTemplate()->TD_COLSPAN = 12 + count($orgaos);

        $contador =1;
        foreach ($caronas as $carona) {
            //$this->getTemplate()->VALOR_ORGAO = $carona->orgao;
            $this->getTemplate()->block("BLOCO_ITEM_TOTAL");

            $itens = $carona->itensCarona;

            if (isset($orgaos) === true && empty($orgaos) === false) {
                foreach ($orgaos as $key => $value) {
                    $this->getTemplate()->VALOR_ORGAO_ID    = $key;
                    $this->getTemplate()->VALOR_ORGAO_NOME  = $value;
                    $this->getTemplate()->block("BLOCK_ORGAO_TITLE");
                }
            }

            foreach ($itens as $item) {
                $ultimoLote = empty($ultimoLote) ? $item->lote : $ultimoLote;
                $this->getTemplate()->VALOR_ORDEM               = $item->ordem;
                $this->getTemplate()->VALOR_DESCRICAO_ITEM      = $item->descricao;
                $this->getTemplate()->VALOR_TIPO                = $item->tipo;
                $this->getTemplate()->VALOR_CODIGO_REDUZIDO     = $item->codigo;
                $this->getTemplate()->VALOR_LOTE                = $item->lote;
                $this->getTemplate()->VALOR_UND                 = $item->und;
                $this->getTemplate()->VALOR_QTD_TOTAL           = $item->quantidade;
                $this->getTemplate()->VALOR_QTD_MAXIMA_CARONA   = $item->qtd_maxima_carona;

                if (isset($orgaos) === true && empty($orgaos) === false) {
                    foreach ($orgaos as $key => $value) {
                        $this->getTemplate()->block("BLOCK_ORGAO");
                    }
                }

                $this->getTemplate()->block("BLOCO_ORGAO_ITEM");
            }
            $this->getTemplate()->block("BLOCO_RESULTADO_ATAS");
        }
        $this->getTemplate()->block("BLOCO_ITEM_TOTAL");
    }


    private function retirarItem()
    {
        $itens = $_POST['idItem'];
        $itens_Sessao = $_SESSION['itens'];

        $provisorio = array();



        $contador_Sessao = count($itens_Sessao);
        for ($i =0;$i < $contador_Sessao;$i++) {
            if ($itens_Sessao[$i]->codigoReduzido != $itens) {
                $provisorio[] = $itens_Sessao[$i];
            }
        }

        $_SESSION['itens'] = $provisorio;
    }

    private function consultarAtaPorProcessoExterno($processo, $ano)
    {
        $sql  = "select arpe.carpnosequ from sfpc.tbataregistroprecoexterna arpe";
        $sql .= " where arpe.earpexproc =".$processo;
        $sql .= " and arpe.aarpexanon =".$ano;

        $database = Conexao();
        $resultado = $database->query($sql);

        $Linha = $res->fetchRow();
        return $Linha->carpnosequ;
    }

    private function validarQuantidades($db, $ata, $item, $participante)
    {
        $colunaTipoItem = null;

        if ($item->tipo == 'CADUM') {
            $colunaTipoItem = 'cmatepsequ';
        } else {
            $colunaTipoItem = 'cservpsequ';
        }

        $sql = "SELECT
				    isc.$colunaTipoItem, isc.aitescqtso
				FROM
				    sfpc.tbsolicitacaocompra sc
				    INNER JOIN sfpc.tbitemsolicitacaocompra isc
				    	ON isc.csolcosequ = sc.csolcosequ
				    	AND isc.$colunaTipoItem = $item->sequencial
				WHERE
				    sc.carpnosequ = $ata
				    AND sc.fsolcorpcp IS NOT NULL
					AND sc.csitsocodi != 10
					AND sc.corglicodi = $participante->sequencial";

        $resultado = $db->query($sql);
    }


    private function inserirParticipante($db, $ata, $participante, $codigoUsuario)
    {
        $sequencialOrgao = $participante->sequencial;
        $excluido = $participante->inativo;

        $sql = "INSERT INTO
    				sfpc.tbparticipanteatarp
					(carpnosequ, corglicodi, fpatrpexcl, cusupocodi, tpatrpulat)
				VALUES
    				($ata, $sequencialOrgao, $excluido, $codigoUsuario, now())";

        $resultado = $db->query($sql);
    }

    private function atualizarParticipante($db, $ata, $participante, $codigoUsuario)
    {
        $sequencialOrgao = $participante->sequencial;
        $excluido = $participante->inativo;

        $sql = "UPDATE
    				sfpc.tbparticipanteatarp
				SET
					fpatrpexcl='$excluido', cusupocodi=$codigoUsuario, tpatrpulat=now()
				WHERE
    				carpnosequ=$ata AND corglicodi=$sequencialOrgao";

        $resultado = $db->query($sql);
    }



    private function salvarItemAta($db, $ata, $item)
    {
        $itemNoBanco = $this->consultarItemAta($db, $ata, $item->codigoReduzido, $item->tipo);
        $resultado = null;

        if ($itemNoBanco == null) {
            $resultado = $this->inserirItem($db, $ata, $item);
        } else {
            $resultado = $this->atualizarItem($db, $ata, $item);
        }

        if (PEAR::isError($resultado)) {
            throw new RuntimeException($resultado->getMessage());
        }
    }



    private function inserirItem($db, $ata, $item)
    {
        /*
    	$sql = "INSERT INTO...";
    	$resultado = $db->query($sql);
    	*/
    }

    private function atualizarItem($db, $ata, $item)
    {
        /*
    	$sql = "UPDATE...";
    	$resultado = $db->query($sql);
    	*/
    }

    private function salvar()
    {
        $db = Conexao();
        $ano = $this->variables['get']['ano'];
        $processo = $this->variables['get']['processo'];
        $orgao = $this->variables['get']['orgao'];
        $ata = $this->variables['get']['ata'];
        foreach ($_SESSION['itens'] as $item) {
            foreach ($item->participantes as $participante) {
                $this->validarQuantidades($db, $ata, $item, $participante);
            }
        }

        $db = Conexao();
        $db->autoCommit(false);
        $db->query("BEGIN TRANSACTION");

        try {
            foreach ($_SESSION['itens'] as $item) {
                $this->salvarItemAta($db, $ata, $item);
                $this->salvarParticipante($db, $ata, $item);
            }


            $db->query("COMMIT");
            $db->query("END TRANSACTION");

            $_SESSION['mensagemFeedback'] = 'Dados salvos com sucesso';
        } catch (Exception $e) {
            $db->query("ROLLBACK");
            $_SESSION['mensagemFeedback'] = 'Erro ao salvar dados';
            ExibeErroBD(self::$erroPrograma . "\nLinha: ".__LINE__."\nSql: " . $e->getMessage());
        }

        $this->redirecionarParaInicio();

        $db->disconnect();
    }

    private function listarOrgaosPorItem($sequencialAta, $sequencialItemAta)
    {
        $database = Conexao();
        $sql = $this->sqlSelectOrgaosPorItem($sequencialAta, $sequencialItemAta);
        $resultado = executarSQL($database, $sql);
        $participantes = array();
        $orgao = null;

        while ($resultado->fetchInto($orgao, DB_FETCHMODE_OBJECT)) {
            $participantes[] = $orgao;
        }
        return $participantes;
    }

    private function sqlSelectOrgaosPorItem($sequencialAta, $sequencialItemAta)
    {
        $sql = "SELECT
				    ol.eorglidesc as descricao,
				    piarp.apiarpqtat as quantidade
				FROM
				    sfpc.tbparticipanteitematarp piarp
				    INNER JOIN sfpc.tborgaolicitante ol
				    	ON piarp.corglicodi = ol.corglicodi
				    INNER JOIN sfpc.tbataregistroprecoexterna arpe
				    	ON piarp.carpnosequ = arpe.carpnosequ
				WHERE
				    piarp.carpnosequ = %d
				    AND piarp.citarpsequ = %d";

        return sprintf($sql, $sequencialAta, $sequencialItemAta);
    }

    private function sqlAtaParametrosTela($processo, $ano)
    {
        $sql ="select arpe.carpnosequ from sfpc.tbataregistroprecoexterna arpe";
        $sql .=" where arpe.earpexproc ='".$processo."'";
        $sql .=" and arpe.aarpexanon =".$ano;

        return $sql;
    }





    private function sqlItemAtaNova($numeroAta)
    {
        $sql  = "select";
        $sql .= " iarpn.*,";
        $sql .= " coei.ccaroesequ,";
        $sql .= " coei.acoeitqtat,";
        $sql .= " mat.cunidmcodi,";
        $sql .= " und.eunidmsigl";
        $sql .= " from sfpc.tbcaronaorgaoexternoitem coei";
        $sql .= " inner join sfpc.tbitemataregistropreconova iarpn";
        $sql .= " left join sfpc.tbmaterialportal mat on mat.cmatepsequ = iarpn.cmatepsequ";
        $sql .= " left join sfpc.tbunidadedemedida und on und.cunidmcodi = mat.cunidmcodi";
        $sql .= " on iarpn.citarpsequ = coei.citarpsequ";
        $sql .= " where coei.carpnosequ = %d";
        $sql .= "order by coei.ccaroesequ";

        // echo '<pre>';
        // print_r(sprintf($sql, $numeroAta));
        // die;

        return sprintf($sql, $numeroAta);
    }

    private function processVoltar()
    {
        $uri  = 'CadMigracaoAta.php';
        header('location: ' . $uri);
    }

    private function verificarSarPCarona($ata, $orgao)
    {
        $database = Conexao();

        $sql = "select count(*) as quantidade from sfpc.tbsolicitacaocompra sc";
        $sql .= " where sc.fsolcorpcp =  'C'";
        $sql .= " and sc.carpnosequ = ".$ata;
        $sql .= " and sc.corglicod1 =".$orgao;
        $sql .= " and sc.csitsocodi != 10";

        $resultado = executarSQL($database, $sql);
        $resultado->fetchInto($quantidades, DB_FETCHMODE_OBJECT);

        return intval($quantidade->quantidade) > 0;
    }
    private function deletarItensCarona($carona)
    {
        $sql = "delete from sfpc.tbcaronaorgaoexternoitem";
        $sql .= " where ccaroesequ=".$carona;

        $resultado = $db->query($sql);
    }

    public function deletarCaronaPropriamenteDita($carona)
    {
        $sql = "delete from sfpc.tbcaronaorgaoexterno";
        $sql .= " where ccaroesequ=".$carona;

        $resultado = $db->query($sql);
    }

    private function deletarCarona($carona)
    {
        $db = Conexao();
        $db->autoCommit(false);
        $db->query("BEGIN TRANSACTION");

        $this->deletarItensCarona($carona);
        $this->deletarCaronaPropriamenteDita($carona);

        $db->query("COMMIT");
        $db->query("END TRANSACTION");
    }
    private function obterCaronaOrgaoAta($ata, $orgaoCarona)
    {
        $sql  ="select coe.ccaroesequ from sfpc.tbcaronaorgaoexterno coe";
        $sql .= " where coe.ecaroeorgg =".$orgaoCarona;
        $sql .= " where coe.carpnosequ =".$ata;

        $resultado = executarSQL($database, $sql);
        $resultado->fetchInto($quantidades, DB_FETCHMODE_OBJECT);
    }

    private function inativarCarona()
    {
        $ata = $_REQUEST['ata'];
        $orgaoCarona = $_REQUEST['orgao'];
        if (!$this->verificarSarPCarona($ata, $orgaoCarona)) {
            $carona = $this->obterCaronaOrgaoAta($ata, $orgaoCarona);
            $this->deletarCarona();
        } else {
            $_SESSION['mensagemFeedback'] = 'Carona Possuie SCC do tipo Sarp associada!';
        }
    }

  /**
     * [frontController description]
     * @return [type] [description]
     */
    private function frontController()
    {
        $botao = isset($this->variables['post']['Botao']) ? $this->variables['post']['Botao'] : 'Principal';

        switch (true) {
            case ($botao == 'Voltar'):
                $this->processVoltar();
                break;
            case($botao == 'novaCarona'):
                $this->processNovaCarona();
            case ($botao == 'Salvar'):
                    $this->salvar();
                    break;
            case ($botao == 'RetirarCarona'):
                    $this->retirarItem();
                    $this->proccessPrincipal();
                    break;
            case ($botao == 'InativarCarona'):
                    $this->inativarCarona();
                    $this->proccessPrincipal();
                    break;
            case (is_numeric($botao)):
                    $this->baixarDocumento();
            case ($botao == 'Principal'):
            default:
                $this->proccessPrincipal();
        }
    }

    /**
     * [__construct description]
     * @param TemplatePaginaPadrao $template [description]
     * @param ArrayObject          $session  [description]
     */
    public function __construct(TemplatePortal $template, ArrayObject $variablesGlobals)
    {
        $this->setTemplate($template);
        $this->variables = $variablesGlobals;

        $this->frontController();
    }

    /**
     * Running the application
     */
    public function run()
    {
        /**
         * Rendering the application
         */
        return $this->getTemplate()->show();
    }
}

/**
 * Bootstrap application
 */
function bootstrap()
{
    global $SimboloConcatenacaoArray, $SimboloConcatenacaoDesc;

    $template = new TemplatePortal("templates/CadAdicionarCarona.html");

    $arrayGlobals = new ArrayObject();
    $arrayGlobals['session'] = $_SESSION;
    $arrayGlobals['server'] = $_SERVER;
    $arrayGlobals['separatorArray'] = $SimboloConcatenacaoArray;
    $arrayGlobals['separatorDesc'] = $SimboloConcatenacaoDesc;

    if ($arrayGlobals['server']['REQUEST_METHOD'] == "POST") {
        $arrayGlobals['post'] = $_POST;
    }

    if ($arrayGlobals['server']['REQUEST_METHOD'] == 'GET') {
        $arrayGlobals['get'] = $_GET;
    }

    $app = new CadAdicionarCarona($template, $arrayGlobals);
    echo $app->run();

    unset($app, $template, $arrayGlobals);
}

bootstrap();
