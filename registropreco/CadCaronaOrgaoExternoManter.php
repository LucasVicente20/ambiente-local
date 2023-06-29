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
 * @version   Git: v1.8.0-46-g35cfc81
 */
# Alterado: Pitang Agile TI - Caio Coutinho
# Data: 09/11/2018
# Objetivo: Tarefa Redmine 205803
#-----------------------------------------------
# Alterado: Pitang Agile TI - Caio Coutinho
# Data: 20/11/2018
# Objetivo: Tarefa Redmine 205798
#-----------------------------------------------

// 220038--

if (! @require_once dirname(__FILE__) . "/../bootstrap.php") {
    throw new Exception("Error Processing Request - Bootstrap", 1);
}

Seguranca();

// require_once './funcoesRegistroPreco.php';

/**
 * A camada de Dados conterá o código que irá realizar todo o acesso aos dados.
 *
 * @author jfsi
 *
 */
class MockUp
{
    public static function obterItemCaronaDTO()
    {
        $resultados = array();
        $itemCarona->citarpsequ = 11;
        $itemCarona->aitarporde = 1;
        $itemCarona->cservpsequ = 1;
        $itemCarona->eitarpdescse = "Seriviço Social";
        $itemCarona->vitarpvori = 200;
        $itemCarona->citarpnuml = "Lote 01";
        $itemCarona->aitarpqtor = 36;
        $itemCarona->citarpnuml = 45;
        $itemCarona->aitarpqtat = 25;
        $itemCarona->cmatepsequ = null;
        $itemCarona->acoeitqtat = 2;
        $itemCarona->vitarpvatu = 20;

        array_push($resultados, $itemCarona);

        $itemCaronaOutro->citarpsequ = 22;
        $itemCaronaOutro->aitarporde = 2;
        $itemCaronaOutro->cservpsequ = null;
        $itemCaronaOutro->eitarpdescse = null;
        $itemCaronaOutro->vitarpvori = 200;
        $itemCaronaOutro->citarpnuml = "Lote 01";
        $itemCaronaOutro->aitarpqtor = 36;
        $itemCaronaOutro->citarpnuml = 45;
        $itemCaronaOutro->aitarpqtat = 25;
        $itemCaronaOutro->cmatepsequ = 35;
        $itemCaronaOutro->acoeitqtat = 3;
        $itemCaronaOutro->vitarpvatu = 25;

        array_push($resultados, $itemCaronaOutro);
        return $resultados;
    }
}

class RegistroPreco_Dados_CadCaronaOrgaoExternoManter extends Dados_Abstrata
{
    public function sqlQuantidadeUsadaCarona($ata, $item)
    {
        $sql = "SELECT sum(coei.acoeitqtat)";
        $sql .= " FROM sfpc.tbataregistropreconova arpn";
        $sql .= " INNER JOIN sfpc.tbcaronaorgaoexterno coe";
        $sql .= " ON coe.carpnosequ = arpn.carpnosequ";
        $sql .= " INNER JOIN sfpc.tbcaronaorgaoexternoitem coei";
        $sql .= " ON coei.ccaroesequ = coe.carpnosequ";
        $sql .= " WHERE arpn.carpnosequ = " . $ata;
        $sql .= " and coei.citarpsequ =" . $item;

        return $sql;
    }

    public function sqlQuantidadeUsadaCaronaInterna($item, $orgao, $ano, $processo, $ata)
    {
        $sql = "select sum(isc.aitescqtso) as qtd from sfpc.tbsolicitacaocompra sc";
        $sql .= " left join sfpc.tbataregistropreconova arpn";
        $sql .= " on arpn.carpnosequ = sc.carpnosequ";
        $sql .= " left join sfpc.tbitemsolicitacaocompra isc";
        $sql .= " on isc.csolcosequ = sc.csolcosequ";
        $sql .= " and isc.cmatepsequ = " . $item;
        $sql .= " or isc.cservpsequ = " . $item;
        $sql .= " where sc.corglicod1 = " . $orgao;
        $sql .= " and sc.alicpoanop = " . $ano;
        $sql .= " and sc.carpnosequ = " . $ata;
        $sql .= " and sc.fsolcorpcp = 'C'";
        $sql .= " and sc.clicpoproc = " . $processo;

        return $sql;
    }

    public function sqlUpdateItemCarona($entidade)
    {
       
        $sql = "
            UPDATE sfpc.tbcaronaorgaoexternoitem
            SET acoeitqtat =  ".$entidade->acoeitqtat." , 
                vcoeitvuti =  ".$entidade->vcoeitvuti." , 
                tcoeitulat='NOW()' 
            WHERE ccaroesequ = ".$entidade->ccaroesequ." AND citarpsequ =  " . $entidade->citarpsequ;

        return $sql;
    }

    public function sqlUpdateCarona($ccaroesequ, $carpnosequ, $orgaoExterno, $dataAutorizacao)
    {
        $sql = "
        UPDATE sfpc.tbcaronaorgaoexterno
        SET ecaroeorgg = '%s', 
        tcaroedaut = '".$dataAutorizacao."',
        tcaroeulat = 'NOW()'
        WHERE ccaroesequ = %d AND carpnosequ = %d";
        
        $sql = sprintf($sql, $orgaoExterno, $ccaroesequ, $carpnosequ, $dataAutorizacao);
        return $sql;
    }

    public static function sqlConsultarCaronaOrgaoExterno($ata, $carona)
    {
        $sql = "select * from sfpc.tbcaronaorgaoexterno coe";
        $sql .= " where coe.ccaroesequ =" . $carona;
        $sql .= " and coe.carpnosequ =" . $ata;

        return $sql;
    }

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

     public function consultarParametroLimiteMaximoCarona()
     {
        $sql = "
            SELECT qpargecaro AS limite FROM sfpc.tbparametrosgerais
        ";

        $db = Conexao();
        $res = executarSQL($db, sprintf($sql));

        while ($res->fetchInto($value, DB_FETCHMODE_OBJECT)) {
            $parametro = $value;
        }

        return $parametro->limite;
     }

     public function consultarItensCarona($carpnosequ, $ccaroesequ)
     {

        $sql = "
        SELECT
            iarpn.citarpsequ,
            iarpn.carpnosequ,
            ca.ccaroesequ,
            iarpn.citarpsequ,
            iarpn.aitarporde,
            iarpn.cmatepsequ,
            m.ematepdesc,
            um.eunidmsigl,
            iarpn.eitarpdescmat,
            iarpn.cservpsequ,
            s.eservpdesc,
            iarpn.eitarpdescse,
            iarpn.aitarpqtor,
            iarpn.aitarpqtat,
            iarpn.vitarpvori,
            iarpn.vitarpvatu,
            ca.acoeitqtat,
            iarpn.citarpnuml,
            iarpn.eitarpmarc,
            iarpn.eitarpmode,
            ca.vcoeitvuti,
            m.fmatepgene
        FROM sfpc.tbcaronaorgaoexternoitem ca
            INNER JOIN sfpc.tbitemataregistropreconova iarpn ON iarpn.citarpsequ = ca.citarpsequ AND iarpn.carpnosequ = ca.carpnosequ
            LEFT JOIN sfpc.tbmaterialportal m ON iarpn.cmatepsequ = m.cmatepsequ
            LEFT JOIN sfpc.tbservicoportal s ON iarpn.cservpsequ = s.cservpsequ
            LEFT JOIN sfpc.tbunidadedemedida um ON um.cunidmcodi = m.cunidmcodi
        WHERE
            ca.ccaroesequ = %d
            AND iarpn.carpnosequ = %d
        ORDER BY iarpn.aitarporde
        ";

         $db = Conexao();
         $res = executarSQL($db, sprintf($sql, $ccaroesequ, $carpnosequ));
 
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

    public function existeCaronaPorAtaOrgao($ata, $orgao) {
        $sql = "select count(*) from sfpc.tbcaronaorgaoexterno where carpnosequ= %d  and ecaroeorgg = '%s'";

        $db = Conexao();
        $res = executarSQL($db, sprintf($sql, $ata, $orgao));

        $caronas = array();
        while ($res->fetchInto($carona, DB_FETCHMODE_OBJECT)) {
            $caronas[] = $carona;
        }
        if (PEAR::isError($res)) {
            ExibeErroBD(__FILE__ . "\nLinha: ".__LINE__."\nSql: " . $database->getMessage());
        }
        $db->disconnect();

        return $caronas[0]->count > 0;
    } 
    
    public function sqlQuantidadeItemCaronaInterna($item, $orgaoExterno, $field) {
        $sql = " SELECT SUM(COALESCE(coei.$field,0)) as $field FROM sfpc.tbcaronaorgaoexterno coe
                 LEFT JOIN sfpc.tbcaronaorgaoexternoitem coei 
                    ON coei.ccaroesequ = coe.ccaroesequ
                    AND coei.carpnosequ = coe.carpnosequ
                 WHERE coei.carpnosequ = ".$item->carpnosequ."
                    AND coei.citarpsequ = ".$item->citarpsequ."
                    AND coe.ecaroeorgg like '".$orgaoExterno."' ";

        $db = Conexao();
        $res = executarSQL($db, $sql);
        while ($res->fetchInto($item, DB_FETCHMODE_OBJECT)) {
            $soma = $item;
        }
        
        return $soma;
    }    

    public static function sqlAtaPorchave($processo, $orgao, $ano, $chaveAta)
    {
        $ano = filter_var($ano, FILTER_SANITIZE_NUMBER_INT);
        $processo = filter_var($processo, FILTER_SANITIZE_NUMBER_INT);
        $orgao = filter_var($orgao, FILTER_SANITIZE_NUMBER_INT);
        $chaveAta = filter_var($chaveAta, FILTER_SANITIZE_NUMBER_INT);
        
        $sql = "
            SELECT a.carpnosequ, a.aarpinpzvg, to_char(a.tarpindini, 'DD/MM/YYYY') AS tarpindini, a.cgrempcodi, a.cusupocodi, f.nforcrrazs,
                   f.aforcrccgc, f.aforcrccpf, f.nforcrfant, d.edoclinome, a.alicpoanop, a.carpnoseq1, a.corglicodi, e.farpnotsal
              FROM sfpc.tbataregistroprecointerna a ";
        $sql .= " LEFT OUTER JOIN sfpc.tbfornecedorcredenciado f";
        $sql .= " ON f.aforcrsequ = a.aforcrsequ";
        $sql .= " LEFT OUTER JOIN sfpc.tbdocumentolicitacao d";
        $sql .= " ON d.clicpoproc =a.clicpoproc";
        $sql .= " AND d.clicpoproc = %d";
        $sql .= " AND d.corglicodi = %d";
        $sql .= " AND d.alicpoanop = %d";
        $sql .= " LEFT JOIN sfpc.tbataregistropreconova e";
        $sql .= " ON e.carpnosequ = a.carpnosequ";
        $sql .= " WHERE a.carpnosequ = %d ";
        $sql .= " AND e.carpnotiat = 'I' ";

        return sprintf($sql, $processo, $orgao, $ano, $chaveAta);
    }

    public function sqlConsultarDocumento($carpnosequ, $ccaroesequ)
    {
        $sql = " SELECT 
            carpnosequ,
            ccaroesequ,
            cusupocodi,
            tdcaroulat,
            cdcarosequ,
            edcaronome,
            encode(idcaroanex, 'base64') as idcaroanex
            FROM sfpc.tbdocumentocaronaexternorp WHERE carpnosequ = %d AND ccaroesequ = %d";
        return sprintf($sql, $carpnosequ, $ccaroesequ);
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
class RegistroPreco_Negocio_CadCaronaOrgaoExternoManter extends Negocio_Abstrata
{
    public function getDados()
    {
        $this->setDados(new RegistroPreco_Dados_CadCaronaOrgaoExternoManter());
        return parent::getDados();
    }


    public function consultarCaronaOrgaoExterno($ata, $carona)
    {
        $sql = $this->getDados()->sqlConsultarCaronaOrgaoExterno($ata, $carona);
        $res = ClaDatabasePostgresql::executarSQL($sql);
        ClaDatabasePostgresql::hasError($res);
        return $res;
    }

    public function consultarLimiteMaximoCarona()
    {
        return $this->getDados()->consultarParametroLimiteMaximoCarona();
    }

    /**
     *
     * @param integer $qtdCarona
     * @param integer $qtdSaldoCarona
     * @param integer $qtdAta
     */
    public function validacao()
    {
        $tipoControle    = $_POST['tipoControle'];
        $orgaoCarona     = filter_var($_POST['orgaoCarona'], FILTER_SANITIZE_STRING);
        $numeroItens     = count($_POST['atual']);
        $ordemItem       = $_POST['ordemItem'];
        $qtdOriginal     = $_POST['QtdAta'];
        $qtdUtilizada    = ($tipoControle == 1 ) ? $_POST['atualValor'] : $_POST['atual'];
        $qtdSaldoCarona  = ($tipoControle == 1 ) ? $_POST['saldoValor'] : $_POST['saldoCarona'];
        $dataAutorizacao = $_POST['dataAutorizacao'];

        if (empty($orgaoCarona)) {
            $msgErro = "Informe: O Órgão Externo Solicitante da Carona";
        } else if ($_SESSION['caronaNomeInicial'] != $orgaoCarona){
            $existeCaronaOrgao = $this->getDados()->existeCaronaPorAtaOrgao($_POST['ata'], $orgaoCarona);
            
            if ($existeCaronaOrgao){
                $msgErro .= "<br />Já existe uma carona para este Órgão Externo Solicitante";
            }
        }

        if(empty($dataAutorizacao)) {
            $msgErro .= "<br />Data de autorização inválida";
        }

        for ($i = 0; $i < $numeroItens; $i ++) {
            if(moeda2float($qtdUtilizada[$i]) > moeda2float($qtdOriginal[$i])){
                $msgErro .= "<br />A Quantidade Utilizada do Item de Ordº " . $ordemItem[$i] . " não pode ser superior a Quantidade Total do Item";
            }

            if (moeda2float($qtdUtilizada[$i]) > moeda2float($qtdSaldoCarona[$i])){
                $msgErro .= "<br />A quantidade utilizada do Item de Ordº " . $ordemItem[$i] . " não pode ser superior ao Saldo total do Item";
            }
        }

        return $msgErro;
    }

    public function consultarDadosAta($processo, $orgao, $ano, $numeroAta)
    {   
        $ata = null;
        $db = Conexao();
        $sql = $this->getDados()->sqlAtaPorchave($processo, $orgao, $ano, $numeroAta);        
        $res = executarSQL($db, $sql);
        
        ClaDatabasePostgresql::hasError($res);

        while ($res->fetchInto($item, DB_FETCHMODE_OBJECT)) {
            $ata = $item;
        }

        return $ata;
    }

    public function consultarDocumento($carpnosequ, $ccaroesequ)
    {
        $database = Conexao();
        $sql = $this->getDados()->sqlConsultarDocumento($carpnosequ, $ccaroesequ);
        $resultado = executarSQL($database, $sql);
        $documentos = array();
        $documento = null;

        while ($resultado->fetchInto($documento, DB_FETCHMODE_OBJECT)) {
            $documentos[] = $documento;
        }
        return $documentos;
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
class RegistroPreco_Adaptacao_CadCaronaOrgaoExternoManter extends Adaptacao_Abstrata
{
    public function getNegocio()
    {
        $this->setNegocio(new RegistroPreco_Negocio_CadCaronaOrgaoExternoManter());
        return parent::getNegocio();
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
class RegistroPreco_UI_CadCaronaOrgaoExternoManter extends UI_Abstrata
{

    /**
     *
     * {@inheritDoc}
     *
     * @see UI_Abstrata::getAdaptacao()
     */
    public function getAdaptacao()
    {
        $this->setAdaptacao(new RegistroPreco_Adaptacao_CadCaronaOrgaoExternoManter());
        return parent::getAdaptacao();
    }

    /**
     * [__construct description]
     */
    public function __construct()
    {
        $this->setTemplate(new TemplatePaginaPadrao("templates/CadCaronaOrgaoExternoManter.html", "Registro de Preço > Ata Interna > Carona Órgão Externo > Manter"));
    }

    public function acaoVoltar()
    {
        $uri = "CadCaronaOrgaoExternoManterSelecionar.php";
        header('Location: ' . $uri);
        exit();
    }

    /**
     * [plotarTelaPrincipal description]
     *
     * @return [type] [description]
     */
    public function plotarTelaPrincipal($carona, $itensCarona)
    {
        $carona     = current($carona);
        $processo   = $_REQUEST['processo'];
        $ano        = $_REQUEST['ano'];
        $orgao      = $_REQUEST['orgao'];
        $ata        = $_REQUEST['ata'];
        $valorSumarizadoTotal = 0;
        $processoNumero = explode('-', $processo);

        // Buscar o tipo de controle da ata
        $tipoControle = $this->getAdaptacao()->getNegocio()->consultarDadosAta($processoNumero[0], $orgao, $ano,$ata);
        $tipoControle = $tipoControle->farpnotsal;
        $this->getTemplate()->VALOR_TIPO_CONTROLE = $tipoControle; 
        $field = 'acoeitqtat';
        // Ajustar exibição da tabela
        $this->getTemplate()->EXIBIR_TD_VALOR = 'display:none';
        $this->getTemplate()->EXIBIR_TD_QUANTIDADE = '';
        if($tipoControle == 1) {
            $field = 'vcoeitvuti';
            $this->getTemplate()->EXIBIR_TD_VALOR = '';
            $this->getTemplate()->EXIBIR_TD_QUANTIDADE = 'display:none';
        }
        $this->getTemplate()->block("BLOCO_TR_RESULTADO_ATAS");

        $this->plotarBlocoDocumentos($ata, $carona);

        if ($itensCarona == null) {
            return;
        }

        $dataAutorizacao = '';
        if(!empty($_POST['dataAutorizacao'])) {
            $dataAutorizacao = $_POST['dataAutorizacao'];
        } else if(!empty($carona->tcaroedaut)) {
            $data = explode('-', $carona->tcaroedaut);
            $dataAutorizacao = $data[2].'/'.$data[1].'/'.$data[0];
        }

        $data = !empty($_POST['dataAutorizacao']) ? $_POST['dataAutorizacao'] : $carona->tcaroedaut;
        $caronaNome = empty($_POST) ? strtoupper2($carona->ecaroeorgg) : strtoupper2(filter_var($_POST['orgaoCarona'], FILTER_SANITIZE_STRING));
        $this->getTemplate()->CARONA  = $_REQUEST['carona'];
        $this->getTemplate()->ATA     = $ata;

        if (!empty($_POST['processoLicitatorio'])) {
            $this->getTemplate()->PROCESSO_LICITATORIO = $_POST['processoLicitatorio'];
        }
        
        $this->getTemplate()->DATA_AUTORIZACAO = $dataAutorizacao;
        $this->getTemplate()->ORGAO_EXTERNO    = $caronaNome;
        $_SESSION['caronaNomeInicial']         = $caronaNome;
        
        $limiteCarona = $this->getAdaptacao()->getNegocio()->consultarLimiteMaximoCarona();
        $iterador = 0;

        foreach ($itensCarona as $item) {
            $qtdValorCarona  = empty($_POST) ? $item->acoeitqtat : moeda2float($_POST['atual'][$iterador]);
            $valorSolicitado = empty($_POST) ? $item->vcoeitvuti : moeda2float($_POST['atualValorBd'][$iterador]);
            $itemCodigo = $item->cservpsequ == null ? $item->cmatepsequ : $item->cservpsequ;
            $resultado = $this->getAdaptacao()
                ->getNegocio()
                ->getDados()
                ->sqlQuantidadeItemAtaCarona($ata, $itemCodigo);
            $buscarCaronaOrgao = $this->getAdaptacao()
                ->getNegocio()
                ->getDados()
                ->sqlQuantidadeItemCaronaInterna($item, $caronaNome, $field);
                $buscarCaronaOrgao->{$field} = converte_valor_estoques($buscarCaronaOrgao->{$field});

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

            $saldoCarona =  converte_valor_estoques(($fatorMaxCarona * $qtdItem) - $total);
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
            $valorDescricaoDetalhada = ' - ';
            $valorDescricao = $item->ematepdesc;
            if ($tipo === 'CADUS') {
                $valorDescricao = $item->eservpdesc;
                $valorDescricaoDetalhada = $item->eitarpdescse;
            }

            if($item->fmatepgene == 'S') {
                $valorDescricaoDetalhada = $item->eitarpdescmat;
            }

            $valorQtdOriginal = ($item->aitarpqtat != 0) ? $item->aitarpqtat : $item->aitarpqtor;
            $valorOriginal    = ($item->vitarpvatu != 0) ? $item->vitarpvatu : $item->vitarpvori;
            $saldoQtdCarona   = ($saldoCarona < $valorQtdOriginal) ? $saldoCarona : $valorQtdOriginal;
            $saldoValorCarona = 0;
            if(!empty($buscarCaronaOrgao)) {
                $saldoQtdCarona = ($buscarCaronaOrgao->acoeitqtat < $saldoQtdCarona) ? $buscarCaronaOrgao->acoeitqtat  : $saldoQtdCarona;
            }

            // Valor total do item
            $valorTotalItem = converte_valor_estoques($qtdValorCarona * $item->vitarpvatu);

            // Ajustar exibição da tabela pelo controle
            $this->getTemplate()->EXIBIR_TD_VALOR = 'display:none';
            $this->getTemplate()->EXIBIR_TD_QUANTIDADE = '';
            if($tipoControle == 1) {
                $z = str_replace('.', '', $saldoCarona) * 100;
                $y = converte_valor_estoques($valorOriginal) * 100;
                $saldoValorCarona = ($z < $y) ? $saldoCarona : converte_valor_estoques($valorOriginal);
                if(!empty($buscarCaronaOrgao->vcoeitvuti)) {
                    $saldoValorCarona = converte_valor_estoques($valorOriginal - str_replace('.', '', $buscarCaronaOrgao->vcoeitvuti));
                }

                $this->getTemplate()->EXIBIR_TD_VALOR = '';
                $this->getTemplate()->EXIBIR_TD_QUANTIDADE = 'display:none';
            }

            $this->getTemplate()->VALOR_ORDEM             = $item->aitarporde;
            $this->getTemplate()->VALOR_TIPO              = $tipo;
            $this->getTemplate()->CODIGO_ITEM             = $item->citarpsequ;
            $this->getTemplate()->VALOR_CODIGO_REDUZIDO   = $valorCodigo;
            $this->getTemplate()->VALOR_UND               = $item->eunidmsigl;
            $this->getTemplate()->VALOR_DESCRICAO         = $valorDescricao;
            $this->getTemplate()->VALOR_DESCRICAO_DET     = $valorDescricaoDetalhada;
            $this->getTemplate()->VALOR_QTD_ORIGINAL      =  converte_valor_licitacao($valorQtdOriginal);
            $this->getTemplate()->VALOR_ORIGINAL          = converte_valor_licitacao($valorOriginal);
            $this->getTemplate()->VALOR_TOTAL_ORIGINAL    = converte_valor_licitacao($valorQtdOriginal * $valorOriginal);
            $this->getTemplate()->SALDO_QUANTIDADE_CARONA = converte_valor_estoques($saldoQtdCarona);
            $this->getTemplate()->SALDO_VALOR_CARONA      = $saldoValorCarona;
            $this->getTemplate()->VALOR_QTD_CARONA        = converte_valor_estoques($qtdValorCarona);
            $this->getTemplate()->VALOR_SOLICITADO        = converte_valor_estoques($valorSolicitado);
            $this->getTemplate()->VALOR_LOTE              = $item->citarpnuml;
            $this->getTemplate()->VALOR_MARCA             = $item->eitarpmarc;
            $this->getTemplate()->VALOR_MODELO            = $item->eitarpmode;
            $this->getTemplate()->VALOR_TOTAL_ITEM        = $valorTotalItem;

            $valorSumarizadoTotal += ($qtdValorCarona * $item->vitarpvatu);
            
            $this->getTemplate()->INDEX = $iterador;
            $iterador++;

            $this->getTemplate()->block('BLOCO_RESULTADO_ATAS');
        }

        $this->getTemplate()->VALOR_TOTAL_ATUAL = converte_valor_licitacao($valorSumarizadoTotal);
    }

    public function retirarDocumento()
    {
        $idDocumento = filter_var($_POST['documentoExcluir'], FILTER_VALIDATE_INT);
        if (! is_int($idDocumento)) {
            throw new Exception("Error Processing Request", 1);
        }

        unset($_SESSION['Arquivos_Upload']['conteudo'][$idDocumento]);
        unset($_SESSION['Arquivos_Upload']['nome'][$idDocumento]);
        $_SESSION['Arquivos_Upload']['nome'] = array_values($_SESSION['Arquivos_Upload']['nome']);
        $_SESSION['Arquivos_Upload']['conteudo'] = array_values($_SESSION['Arquivos_Upload']['conteudo']);

    }

    public function adicionarDocumento() {
        $arquivo = new Arquivo();
        $arquivo->setExtensoes('doc,odt,pdf');
        $arquivo->setTamanhoMaximo(20000000000000000);
        $arquivo->configurarArquivo();
        unset($_FILES['fileArquivo']);
    }

    public function plotarBlocoDocumentos($carpnosequ, $carona)
    {   
        if (isset($carpnosequ) && isset($carona->ccaroesequ)) {
            $documentos = $this->getAdaptacao()->getNegocio()->consultarDocumento($carpnosequ, $carona->ccaroesequ);
            if (!empty($documentos) && !isset($_SESSION['Arquivos_Upload'])) {                
                foreach ($documentos as $documento) {
                    $documentoHexDecodificado = base64_decode($documento->idcaroanex);
                    $documentoToBin = $this->hextobin($documentoHexDecodificado);
                    $_SESSION['Arquivos_Upload']['nome'][] = $documento->edcaronome;
                    $_SESSION['Arquivos_Upload']['conteudo'][] = $documentoToBin;
                }                
            }
        }

        $this->coletarDocumentosAdicionados();
        $this->getTemplate()->block('BLOCO_FILE');
    }

    function hex2bin($hexdata) {
        $bindata="";
        for ($i=0;$i<strlen($hexdata);$i+=2) {
           $bindata.=chr(hexdec(substr($hexdata,$i,2)));
        }
     
        return $bindata;
     }

    function hextobin($hexstr){ 
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
            //unset($_SESSION['Arquivos_Upload']);
        }
    }
    
}

class CadCaronaOrgaoExternoManter extends ProgramaAbstrato
{

    /**
     */
    private function insertDados()
    {
        $mensagem = $this->getUI()->getAdaptacao()->getNegocio()->validacao();
        if (!empty($mensagem)) {
            $this->getUI()->mensagemSistema($mensagem, 1, 0);
            $this->exibePaginaInicial();
            return;
        }
        
        $db = Conexao();
        $db->autoCommit(false);
        $db->query("BEGIN TRANSACTION");

        try {
            $orgaoCarona     = strtoupper2(filter_var($_POST['orgaoCarona'], FILTER_SANITIZE_STRING));
            $codigoItem      = $_POST['CodigoItem'];
            $ordemItem       = $_POST['ordemItem'];
            $quantAta        = $_POST['QtdAta'];
            $atual           = $_POST['atual'];
            $atualValor      = $_POST['atualValor'];
            $valoresItensBd  = $_REQUEST['atualValorBd'];
            $saldoValor       = isset($_REQUEST['saldoValor']) ? $_REQUEST['saldoValor'] : null;
            $ordens          = $_REQUEST['ordemItem'];
            $saldoCarona     = $_POST['saldoCarona'];
            $dataAutorizacao = $_POST['dataAutorizacao'];
            $tamanho         = sizeof($saldoCarona);
            $tipoCoontrole   = $_POST['tipoControle'];

            $ata = $_POST['ata'];
            $carona = $_POST['carona'];

            if(is_null($dataAutorizacao)) {
                $dataAutorizacao = date('Y-m-d');
            } else {
                $data = explode('/', $dataAutorizacao);
                $dataAutorizacao = $data[2].'-'.$data[1].'-'.$data[0];
            }
            
            // Verificar saldo
            $verificarSaldo = false;
            if($tipoCoontrole == 1) {
                $verificarSaldo = $this->verificarSaldo($saldoValor, $atualValor, $valoresItensBd, $ordens);
            }

            if($verificarSaldo) {                                
                $this->exibePaginaInicial();
                return false;
            }

            // Salvar documento
            $this->inserirDocumentoAta($db, $ata, $carona);

            for ($i = 0; $i < $tamanho; $i ++) {
                if (empty($atual[$i])){
                    $atual[$i] = 0;
                }

                $qtdAtual   = !empty($atual[$i]) ? moeda2float($atual[$i]) : 0;
                $valorAtual = !empty($atualValor[$i]) ? moeda2float($atualValor[$i]) : 0;                

                $entidade->ccaroesequ = (int) $carona;
                $entidade->citarpsequ = (int) $codigoItem[$i];
                $entidade->acoeitqtat = $qtdAtual;
                $entidade->tcoeitulat = 'NOW()';
                $entidade->carpnosequ = (int) $ata;
                $entidade->vcoeitvuti = (int) $valorAtual;

                $sqlItemCarona = $this->getUI()->getAdaptacao()->getNegocio()->getDados()->sqlUpdateItemCarona($entidade);
                executarTransacao($db, $sqlItemCarona);

                $sqlCarona = $this->getUI()->getAdaptacao()->getNegocio()->getDados()->sqlUpdateCarona((int) $carona, (int) $ata, $orgaoCarona, $dataAutorizacao);
                executarTransacao($db, $sqlCarona);
            }

            unset($_SESSION['Arquivos_Upload']);
            
            $db->query("COMMIT");
            $db->query("END TRANSACTION");

            $_SESSION['mensagemFeedback'] = 'Atualizado com sucesso';
            unset($_SESSION['caronaNomeInicial']);
            unset($_SESSION['processoLicitatorio']);

        } catch (Exception $e) {
            $db->query("ROLLBACK");
            $_SESSION['mensagemFeedback'] = 'Erro ao salvar dados';
            ExibeErroBD(self::$erroPrograma . "\nLinha: " . __LINE__ . "\nSql: " . $e->getMessage());
        }

        $db->disconnect();

        $uri = 'CadCaronaOrgaoExternoManterSelecionar.php';
        header('Location: ' . $uri);
        exit();
    }

    public function verificarSaldo($saldo, $valor, $valorDb, $ordem) {
        $erro = false;
        foreach ($saldo as $key => $value) {
            if(!empty($valor[$key]) && $valor[$key] != $valorDb[$key] && ($value * 1) < ($valor[$key] * 1) ) {
                $_SESSION['mensagemFeedback'][] = "Verifique o valor solicitado do item de ordem " . $ordem[$key];
                $erro = true;
            }
        }

        return $erro;
    }

    private function inserirDocumentoAta($conexao, $carpnosequ, $ccaroesequ)
    {
        $conexao->query(sprintf("DELETE FROM sfpc.tbdocumentocaronaexternorp WHERE carpnosequ = %d AND ccaroesequ = %d", $carpnosequ, $ccaroesequ));
        $valorMax = 1;
        $tamanho = count($_SESSION['Arquivos_Upload']['nome']);

        $nomeTabela = 'sfpc.tbdocumentocaronaexternorp';
        $entidade = ClaDatabasePostgresql::getEntidade($nomeTabela);

        for ($i = 0; $i < $tamanho; $i++) {
            $entidade->carpnosequ = (int)$carpnosequ;
            $entidade->ccaroesequ = (int)$ccaroesequ;
            $entidade->cdcarosequ = (int)$valorMax; // Sequancial do documento
            $entidade->edcaronome = $_SESSION['Arquivos_Upload']['nome'][$i];  
     
            $entidade->idcaroanex = bin2hex($_SESSION['Arquivos_Upload']['conteudo'][$i]);
            
            $entidade->cusupocodi = (int)$_SESSION['_cusupocodi_'];
            $entidade->tdcaroulat = 'NOW()';
            $conexao->autoExecute($nomeTabela, (array)$entidade, DB_AUTOQUERY_INSERT);

            if (ClaDatabasePostgresql::hasError($resultado)) {
                $conexao->rollback();
                return;
            }
            $valorMax++;
        }
    }

    private function exibePaginaInicial()
    {
        $ata = $_REQUEST['ata'];

        if ($_SERVER['REQUEST_METHOD'] == 'GET') {
            $_SESSION['carona'] = isset($_GET['carona']) ? filter_var($_GET['carona'], FILTER_SANITIZE_NUMBER_INT) : 0;
        } 
        
        if (!empty($_SESSION['mensagemFeedback'])) {
            $this->getUI()->mensagemSistema(implode("", $_SESSION['mensagemFeedback']), 0, 1);
            unset($_SESSION['mensagemFeedback']);
        }

        $caronaOrgao = $this->getUI()
            ->getAdaptacao()
            ->getNegocio()
            ->consultarCaronaOrgaoExterno($ata, $_REQUEST['carona']);

        $itens = $this->getUI()
            ->getAdaptacao()
            ->getNegocio()
            ->getDados()
            ->consultarItensCarona($ata, $_REQUEST['carona']);
        $this->getUI()->plotarTelaPrincipal($caronaOrgao, $itens);
    }

    /**
     *
     * {@inheritDoc}
     *
     * @see ProgramaAbstrato::configuracao()
     */
    protected function configuracao()
    {
        $codProcesso = explode('-',$_GET['processo']);

        $this->setUI(new RegistroPreco_UI_CadCaronaOrgaoExternoManter());
        $this->getUI()->getTemplate()->ACAO_SALVAR          = 'Atualizar';
        $this->getUI()->getTemplate()->SUPER_TITULO         = "MANTER - CARONA ÓRGÃO EXTERNO";
        $this->getUI()->getTemplate()->SEQUENCIAL_ATA       = $_SESSION['numAtaFormatado'];
        $this->getUI()->getTemplate()->PROCESSO_LICITATORIO = str_pad($codProcesso[0], 4, '0', STR_PAD_LEFT) . "/" . $_SESSION['ano'];
        $this->getUI()->getTemplate()->NOME_PROGRAMA        = "CadCaronaOrgaoExternoManter";
        $this->getUI()->getTemplate()->ANO_URL              = $_GET['ano'];
        $this->getUI()->getTemplate()->PROCESSO_URL         = $_GET['processo'];
        $this->getUI()->getTemplate()->ORGAO_URL            = $_GET['orgao'];
        $this->getUI()->getTemplate()->ATA_URL              = $_GET['ata'];
        $this->getUI()->getTemplate()->CARONA_URL           = $_GET['carona'];
    }

    /**
     *
     * {@inheritDoc}
     *
     * @see ProgramaAbstrato::frontController()
     */
    protected function frontController()
    {
        $botao = isset($_POST['Botao']) ? filter_input(INPUT_POST, 'Botao', FILTER_SANITIZE_STRING) : 'Principal';
        switch ($botao) {
            case 'Atualizar':
                $this->insertDados();
                break;
            case 'RetirarDocumento':
                $this->getUI()->RetirarDocumento();
                $this->exibePaginaInicial();
                break;
            case 'Inserir':
                $this->getUI()->adicionarDocumento(); 
                $this->exibePaginaInicial();
                break;
            case 'Voltar':
                $this->getUI()->acaoVoltar();
                break;
            case 'Principal':
            default:
                $this->exibePaginaInicial();
                break;
        }
    }
}

ProgramaAbstrato::iniciar(new CadCaronaOrgaoExternoManter());
