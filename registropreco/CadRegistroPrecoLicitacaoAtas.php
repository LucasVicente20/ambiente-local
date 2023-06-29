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
 * @author    Pitang Agile TI <contato@pitang.com>
 * @copyright 2014 EMPRESA MUNICIPAL DE INFORMÁTICA - EMPREL
 * @license   http://www.php.net/license/3_01.txt PHP License 3.01
 */

#-------------------------------------------------------------------------
# Alterado: Caio Coutinho - Pitang Agile TI
# Data:     23/08/2018
# Objetivo: Tarefa Redmine 201632
#-------------------------------------------------------------------------
# Alterado: Caio Coutinho - Pitang Agile TI
# Data:     06/02/2019
# Objetivo: Tarefa Redmine 210590
#-------------------------------------------------------------------------
# Alterado: Caio Coutinho - Pitang Agile TI
# Data:     13/02/2019
# Objetivo: Tarefa Redmine 211018
#-------------------------------------------------------------------------
# Alterado: Caio Coutinho - Pitang Agile TI
# Data:     18/03/2019
# Objetivo: Tarefa Redmine 212600
#-------------------------------------------------------------------------
# Alterado: Lucas André
# Data:     26/06/2023
# Objetivo: Tarefa Redmine 285229
#-------------------------------------------------------------------------
# Alterado: Lucas André
# Data:     26/06/2023
# Objetivo: Tarefa Redmine 285230
#-------------------------------------------------------------------------
# Alterado: Lucas Vicente
# Data:     26/06/2023
# Objetivo: Tarefa Redmine 285232
#-------------------------------------------------------------------------

// 220038--

if (! @include_once dirname(__FILE__) . "/../bootstrap.php") {
    throw new Exception("Error Processing Request - Bootstrap", 1);
}

Seguranca();

class RegistroPreco_Dados_CadRegistroPrecoLicitacaoAtas extends Dados_Abstrata
{

    /**
     *
     * @param array $processo
     * @return string
     */
    public function sqlVerificarSCCDoTipoSARP($atas)
    {
        $sql = "SELECT * FROM sfpc.tbsolicitacaocompra sc
                WHERE 1 = 1
                        AND sc.carpnosequ IN (%d)
                ";
        return sprintf($sql, $atas);
    }

    public function sqlConsutarParticipantesProcesso($processo, $ano, $orgao, $comissao, $grupo, $ata)
    {
        $SITUACAO_SCC_CANCELADA = 10;

        $sql = " select ol.corglicodi,";
        $sql .= " ilp.aitelpqtso,iarpn.citarpsequ, iarpn.carpnosequ from  sfpc.tbsolicitacaocompra sc";
        $sql .= " inner join sfpc.tbsolicitacaolicitacaoportal slp";
        $sql .= " on slp.csolcosequ = sc.csolcosequ";
        $sql .= " inner join sfpc.tbitemlicitacaoportal ilp";
        $sql .= " on ilp.clicpoproc = slp.clicpoproc";
        $sql .= " and ilp.alicpoanop = slp.alicpoanop";
        $sql .= " and ilp.cgrempcodi = slp.cgrempcodi";
        $sql .= " and ilp.corglicodi = slp.corglicodi";
        $sql .= " and ilp.ccomlicodi = slp.ccomlicodi";
        $sql .= " JOIN sfpc.tborgaolicitante ol";
        $sql .= " ON ol.corglicodi = slp.corglicodi";
        $sql .= " inner join sfpc.tbitemataregistropreconova iarpn";
        $sql .= " on iarpn.carpnosequ =" . $ata;
        $sql .= " where slp.clicpoproc =" . $processo;
        $sql .= " and slp.alicpoanop =" . $ano;
        $sql .= " and slp.cgrempcodi =" . $grupo;
        $sql .= " and slp.corglicodi =" . $orgao;
        $sql .= " and slp.ccomlicodi =" . $comissao;
        $sql .= " and sc.fsolcorgpr = 'S'";
        $sql .= " and sc.csitsocodi != " . $SITUACAO_SCC_CANCELADA;

        return $sql;
    }

    /**
     *
     * @param array $processo
     * @param unknown $ano
     * @param unknown $sequencialFornecedor
     */
    public function selecionarItensDoProcesso($processo, $ano, $sequencialFornecedor, $ccomlicodi, $corglicodi, $cgrempcodi, $citelpnuml)
    {
        $item = null;

        $sql = " SELECT * FROM sfpc.tbitemlicitacaoportal
                 WHERE clicpoproc = %d
                    AND alicpoanop = %d
                    AND aforcrsequ = %d
                    AND ccomlicodi = %d
                    AND corglicodi = %d
                    AND cgrempcodi = %d
                    AND citelpnuml IN (".$citelpnuml.") ";

        $db = Conexao(); 
        $resultado = executarSQL($db, sprintf($sql, $processo, $ano, $sequencialFornecedor, $ccomlicodi, $corglicodi, $cgrempcodi, $citelpnuml));
        // $resultado = executarSQL(ClaDatabasePostgresql::getConexao(), sprintf($sql, $processo, $ano, $sequencialFornecedor));

        // ClaDatabasePostgresql::hasError($resultado);

        $arrayItens = array();
        while ($resultado->fetchInto($item, DB_FETCHMODE_OBJECT)) {
            array_push($arrayItens, $item);
        }
        if (PEAR::isError($res)) {
            ExibeErroBD(__FILE__ . "\nLinha: ".__LINE__."\nSql: " . $database->getMessage());
        }
        //$db->disconnect();

        return $arrayItens;
    }



     /**
     *
     * @param array $processo
     * 
     */
    public function selecionarItensDaSolicitacao($processo)
    {
        $item = null;

        $valores = explode("-", $processo);

        $sql = "
        select
            lic.corglicodi, 
            org.eorglidesc, 
            item.citescsequ, 
            item.aitescqtso,
            item.cmatepsequ,
            item.cservpsequ

        from
            sfpc.tblicitacaoportal lic 
            
            inner join sfpc.tbsolicitacaolicitacaoportal sol on
            lic.clicpoproc = sol.clicpoproc
            and lic.alicpoanop = sol.alicpoanop
            and lic.cgrempcodi = sol.cgrempcodi
            and lic.ccomlicodi = sol.ccomlicodi
            and lic.corglicodi = sol.corglicodi 
            
            inner join sfpc.tbitemsolicitacaocompra item on
            sol.csolcosequ = item.csolcosequ
            
            inner join sfpc.tborgaolicitante org on
            org.corglicodi = lic.corglicodi
            
            
        where
            true
            and lic.clicpoproc = $valores[0]
            and lic.alicpoanop = $valores[1]
            and lic.cgrempcodi = $valores[2]
            and lic.ccomlicodi = $valores[3]
            and lic.corglicodi = $valores[4]            
        ";

        $db = Conexao(); 
        $resultado = executarSQL($db, $sql);
        
        $arrayItens = array();
        while ($resultado->fetchInto($item, DB_FETCHMODE_OBJECT)) {
            array_push($arrayItens, $item);
        }
        if (PEAR::isError($res)) {
            ExibeErroBD(__FILE__ . "\nLinha: ".__LINE__."\nSql: " . $database->getMessage());
        }
        $db->disconnect();

        return $arrayItens;
    }

    /**
     *
     * @param array $processo
     * @param unknown $fornecedorCredenciado
     * @param unknown $valorChave
     * @return string
     */
    public function configurarGerarAtas($processo, $fornecedorCredenciado, $valorChave, $obje, $numero, $ano, $ataCorporativa)
    {
        date_default_timezone_set('America/Recife');
        $valorAnoNumeracao = $ano;
        $valor = explode('-', $processo);
        $valorOrgao = $valor[4];        

        $valorFornecedor = is_null($fornecedorCredenciado) ? "'NULL'" : (int) $fornecedorCredenciado;
        $valorPrazoVigencia = 12;

        if ($_SESSION['_cperficodi_'] == 2) {
            $valorSituacao = "'A'";
        }else{
            $valorSituacao = "'I'";
        }

        $seguencialOutro = 'NULL';
        $valorUsuario = $_SESSION['_cusupocodi_'];

        $valores = $valorChave . ',' . $valor[0] . ',';
        $valores .= $valor[1] . ',' . $valor[2] . ',' . $valor[3] . ',';
        $valores .= $valor[4] . ',';
        $valores .= !empty($obje) ? "'".$obje."'" . ',' : " 0" . ',';
        $valores .= $valorAnoNumeracao . ',';
        //$count = count($ataInterna) + 1;        

        $valores .= $numero . ',' . $valorFornecedor . ',' . "now()" . ',';
        $valores .= $valorPrazoVigencia . ',' . $valorSituacao . ',' . $seguencialOutro . ',';
        $valores .= $valorUsuario . ',' . "now()" . ',' . $numero.',';
        $valores .= "'$ataCorporativa'";
         


        return $valores;
    }

    /**
     *
     * @param unknown $valorChave
     */
    public function configurarGerarAtaRegistroPrecoNova($valorChave)
    {
        $valores = $valorChave . "," . "'I'" . "," . "now()" . "," . $_SESSION['_cusupocodi_'] . "," . "now()";

        return $valores;
    }

    /**
     *
     * @param unknown $itens
     * @param unknown $sequencialAta
     */
    public function sqlImportacaoItens($itens, $sequencialAta)
    {
        $sql = "INSERT INTO sfpc.tbitemataregistropreconova
                (
                 carpnosequ, citarpsequ, aitarporde, cmatepsequ, cservpsequ, aitarpqtor, aitarpqtat,
                 vitarpvori, vitarpvatu, citarpnuml, eitarpmarc, eitarpmode, eitarpdescmat, eitarpdescse,
                 fitarpsitu, fitarpincl, fitarpexcl, titarpincl, cusupocodi, citarpitel
                )
                VALUES ";

        $totalItens = count($itens);
        $sequencialItemAta = 1;

        foreach ($itens as $item) {
          
            $sql .= '(';
            $sql .= $sequencialAta . ',';
            $sql .= $sequencialItemAta . ',';
            $sql .= (empty($item->aitelporde) ? 'null' : $item->aitelporde) . ',';
            $sql .= (empty($item->cmatepsequ) ? 'null' : $item->cmatepsequ) . ',';
            $sql .= (empty($item->cservpsequ) ? 'null' : $item->cservpsequ) . ',';
            $sql .= (empty($item->aitelpqtso) ? 0 : $item->aitelpqtso) . ',';
            $sql .= (empty($item->aitelpqtex) ? 0 : $item->aitelpqtex) . ',';
            $sql .= (empty($item->vitelpvlog) ? 0 : $item->vitelpvlog) . ',';
            $sql .=  '0,';
            $sql .= (empty($item->citelpnuml) ? 0 : $item->citelpnuml) . ',';
            $sql .= (empty($item->eitelpmarc) ? 'null' : "'" . $item->eitelpmarc . "'") . ',';
            $sql .= (empty($item->eitelpmode) ? 'null' : "'" . $item->eitelpmode . "'") . ',';
            $sql .= (empty($item->eitelpdescmat) ? 'null' : "'" . $item->eitelpdescmat . "'") . ',';
            $sql .= (empty($item->eitelpdescse) ? 'null' : "'" . $item->eitelpdescse . "'") . ',';
            $sql .= "'A',";
            $sql .= "'S',";
            $sql .= "'N',";
            $sql .= "'" . date('Y-m-d H:i:s') . "',";
            $sql .= $_SESSION['_cusupocodi_'] . ", ";
            $sql .= (empty($item->citelpsequ) ? 'null' : $item->citelpsequ);
            $sql .= ')';

            if ($sequencialItemAta < $totalItens) {
                $sql .= ',';
            }

            ++ $sequencialItemAta;
        }
        $_SESSION['sequencialItemAta'] = $sequencialItemAta;
        return $sql;
    }



    /**
     *
     * @param unknown $itens
     * @param unknown $sequencialAta
     */
    public function sqlImportacaoItensDoParticipante($itens, $sequencialAta)
    {
        $sql = "INSERT INTO sfpc.tbparticipanteitematarp
                (
                 carpnosequ, citarpsequ, corglicodi, apiarpqtat, fpiarpsitu, cusupocodi, tpiarpulat,
                 apiarpqtut
                )
                VALUES ";

        $totalItens = count($itens);
        $sequencialItemAta = 1;
        foreach ($itens as $item) {          
            $sql .= '(';
            $sql .= $sequencialAta . ',';
            $sql .= $sequencialItemAta . ',';
            $sql .= (empty($item->corglicodi) ? 'null' : $item->corglicodi) . ',';
            $sql .= $item->aitelpqtso . ',';
            $sql .= "'A',";
            $sql .= $_SESSION['_cusupocodi_'] . ',';
            $sql .= "'" . date('Y-m-d H:i:s') . "',";
            $sql .= 0;            
            $sql .= ')';

            if ($sequencialItemAta < $totalItens) {
                $sql .= ',';
            }

            ++ $sequencialItemAta;
        }
        $_SESSION['sequencialItemAta'] = $sequencialItemAta;

        return $sql;
    }



    /**
     *
     * @param unknown $itens
     * @param unknown $sequencialAta
     */
    public function sqlImportacaoParticipantes($itens, $sequencialAta)
    {
        $sql = "INSERT INTO sfpc.tbparticipanteatarp (
                carpnosequ, corglicodi, cusupocodi, tpatrpulat, fpatrpsitu 
            )
            VALUES ";

        $totalItens = count($itens);
        $sequencialItemAta = 1;
        $vlorOrgaoVez = 0;

        foreach ($itens as $key => $item) {
            
            if($vlorOrgaoVez != $item->corglicodi){

                $sql .= '(';
                $sql .= $sequencialAta . ',';            
                $sql .= (empty($item->corglicodi) ? 'null' : $item->corglicodi) . ',';
                $sql .= $_SESSION['_cusupocodi_'] . ',';
                $sql .= "'" . date('Y-m-d H:i:s') . "',";
                $sql .= "'A'";            
                $sql .= ')';
                $sql .= ',';

                ++ $sequencialItemAta;
            }
            
            $vlorOrgaoVez = $item->corglicodi;
        }
        
        $sql = substr_replace($sql, '', -1);
        $_SESSION['sequencialItemAta'] = $sequencialItemAta;
        
        return $sql;
    }




    /**
     *
     * @param array $processo
     */
    public function sqlNumeroAtaGeradaPeloOrgao($processo)
    {
        		$valor = explode('-', $processo);
        return sprintf(" SELECT *
                FROM SFPC.TBATAREGISTROPRECOINTERNA A
               WHERE 1 = 1
                     AND A.CLICPOPROC = %d
                     AND A.ALICPOANOP = %d
                     AND A.CGREMPCODI = %d
                     AND A.CCOMLICODI = %d
                     AND A.CORGLICODI = %d
                ORDER BY carpnosequ ASC 
            ", $valor[0], $valor[1], $valor[2], $valor[3], $valor[4]);
    }


    public function sqlExisteCaronaExterna($ataCod)
    {
        $sql  = " SELECT count(*) ";
        $sql .= " FROM sfpc.tbcaronaorgaoexterno car ";

        $sql .= " WHERE 1=1 ";
        $sql .= " AND car.carpnosequ = ".$ataCod;

        return $sql;
    }

    public function sqlExisteScc($ataCod)
    {
        $sql  = " SELECT count(*) ";
        $sql .= " FROM sfpc.tbsolicitacaocompra sol ";

        $sql .= " WHERE 1=1 ";
        $sql .= " and sol. carpnosequ = ".$ataCod;
        $sql .= "   and sol.csitsocodi <> 10 ";

        return $sql;
    }
    

    /**
     *
     * @param unknown $ata
     * @param unknown $orgao
     */
    public function sqlInsercaoOrgaoParticipante($ata, $orgao)
    {
        $usuario = $_SESSION["_cusupocodi_"];
        $sql = "INSERT INTO sfpc.tbparticipanteatarp";
        $sql .= "(carpnosequ, corglicodi, fpatrpexcl, cusupocodi, tpatrpulat)";
        $sql .= "VALUES($ata, $orgao, 'N', $usuario, now())";

        return $sql;
    }

    /**
     *
     * @param unknown $ata
     * @param unknown $orgao
     * @param unknown $itemAta
     * @param unknown $qtdSCC
     */
    public function sqlInsercaoItemParticipante($ata, $orgao, $itemAta, $qtdSCC)
    {
        $usuarioLogado = $_SESSION['_cusupocodi_'];
        $sql = "INSERT INTO sfpc.tbparticipanteitematarp";
        $sql .= "(carpnosequ, corglicodi, citarpsequ, apiarpqtat, fpiarpsitu, cusupocodi, tpiarpulat)";
        $sql .= "VALUES($ata, $orgao, $itemAta, $qtdSCC, 'A', $usuarioLogado, now())";

        return $sql;
    }

    /**
     *
     * @param unknown $ata
     */
    public function excluirAtasProcesso($ata)
    {
        $condicao = "WHERE carpnosequ = $ata";
        $dbase = ClaDatabasePostgresql::getConexao();
        $dbase->autoCommit(false);
        /**
         *
         * @todo logicca precisa ser implementada
         */
        // $dbase->query("DELETE FROM sfpc.tbcaronaorgaoexternoitem " . $condicao);
        // $dbase->query("DELETE FROM sfpc.tbcaronaorgaoexterno " . $condicao);
        $dbase->query("DELETE FROM  sfpc.tbdocumentoatarp " . $condicao);
        $dbase->query("DELETE FROM  sfpc.tbataregistroprecointerna " . $condicao);
        $dbase->query("DELETE FROM  sfpc.tbitemataregistropreconova " . $condicao);
        $dbase->query("DELETE FROM  sfpc.tbataregistropreconova " . $condicao);

        $commited = $dbase->commit();

        if ($commited instanceof DB_error) {
            $dbase->rollback();

            return false;
        }

        return true;
    }

    /**
     *
     * @return string
     */
    public function sqlConsultarMaiorAta()
    {
        $sql = "select max(a.carpnosequ) from sfpc.tbataregistropreconova a";
        return $sql;
    }

    /**
     * SQL
     *
     * @param string $processo
     * @return string
     */
    public function sqlConsultaFornecedoresLicitacao($processo)
    {
        $valores = explode('-', $processo);
        $sql = "SELECT DISTINCT i.aforcrsequ, fc.nforcrrazs
                FROM sfpc.tbitemlicitacaoportal i
                    RIGHT JOIN sfpc.tbfaselicitacao fl
                            ON fl.clicpoproc = i.clicpoproc
                                AND fl.alicpoanop = i.alicpoanop
                                AND fl.cgrempcodi = i.cgrempcodi
                                AND fl.ccomlicodi = i.ccomlicodi
                                AND fl.corglicodi = i.corglicodi                    
                    INNER JOIN sfpc.tbfornecedorcredenciado fc
                        ON i.aforcrsequ = fc.aforcrsequ
                WHERE i.clicpoproc = %d
                    AND i.alicpoanop = %d
                    AND i.cgrempcodi = %d
                    AND i.ccomlicodi = %d
                    AND i.corglicodi = %d ";

        return sprintf($sql, $valores[0], $valores[1], $valores[2], $valores[3], $valores[4]);
    }

    public function sqlConsultarProcessosItensAtas($processo) {
        $valores = explode('-', $processo);
        $sql = "SELECT ilp.citelpsequ, ilp.citelpnuml, ilp.aforcrsequ, ata.citarpitel, fc.nforcrrazs, ata.carpnosequ,
                        ata.clicpoproc, ata.alicpoanop, ata.carpincodn, ata.aarpinanon, ata.citarpnuml
                    FROM sfpc.tbitemlicitacaoportal ilp LEFT JOIN 
                        (SELECT arpi.clicpoproc, iarpn.cmatepsequ, arpi.alicpoanop, arpi.ccomlicodi, arpi.corglicodi, 
                                arpi.cgrempcodi, iarpn.citarpitel, arpi.carpnosequ, arpi.aarpinanon, iarpn.citarpnuml,
                                arpi.carpincodn
                            FROM sfpc.tbitemataregistropreconova iarpn
                            LEFT JOIN sfpc.tbataregistroprecointerna arpi ON 
                                iarpn.carpnosequ = arpi.carpnosequ
                            WHERE arpi.clicpoproc = ".$valores[0] ."
                                AND arpi.alicpoanop = ".$valores[1] ."
                                AND arpi.ccomlicodi = ".$valores[3] ."
                                AND arpi.corglicodi = ".$valores[4] ."
                                AND arpi.cgrempcodi = ".$valores[2] .") AS ata ON 
                                    ata.citarpitel = ilp.citelpsequ
                                    AND ata.clicpoproc = ilp.clicpoproc 
                                    AND ata.alicpoanop = ilp.alicpoanop 
                                    AND ata.cgrempcodi = ilp.cgrempcodi 
                                    AND ata.ccomlicodi = ilp.ccomlicodi 
                                    AND ata.corglicodi = ilp.corglicodi
                    INNER JOIN sfpc.tbfornecedorcredenciado fc
                        ON ilp.aforcrsequ = fc.aforcrsequ
                WHERE ilp.clicpoproc = %d
                AND ilp.alicpoanop = %d
                AND ilp.cgrempcodi = %d
                AND ilp.ccomlicodi = %d
                AND ilp.corglicodi = %d";        
         return sprintf($sql, $valores[0], $valores[1], $valores[2], $valores[3], $valores[4]);
    }

    /**
     *
     * @param array $processo
     * @return string
     */
    public function sqlAtasProcessoLicitario($processo)
    {
        $valores = explode('-', $processo);
        $sql = "
            SELECT
                arpi.carpnosequ,
                arpi.clicpoproc,
                arpi.alicpoanop,
                arpi.aforcrsequ,
                arpi.cgrempcodi,
                fc.nforcrrazs,
                arpi.aarpinanon
            FROM
                sfpc.tbataregistroprecointerna arpi
                INNER JOIN sfpc.tbfornecedorcredenciado fc
                    ON arpi.aforcrsequ = fc.aforcrsequ
            WHERE 1 = 1
                AND arpi.clicpoproc = %d
                AND arpi.alicpoanop = %d
                AND arpi.cgrempcodi = %d
                AND arpi.ccomlicodi = %d
                AND arpi.corglicodi = %d
           ORDER BY
                arpi.carpnosequ
        ";

        return sprintf($sql, $valores[0], $valores[1], $valores[2], $valores[3], $valores[4]);
    }

    /**
     * Get Orgao Licitante Codigo pelo Grupo Codigo do usuário logado
     *
     * @param integer $grupoCodigo
     * @return integer
     */
    public function consultarOrgaoLicitanteCodigo($grupoCodigo)
    {
        $sql = "SELECT x.corglicodi FROM sfpc.tbgrupoorgao x WHERE x.cgrempcodi =" . $grupoCodigo;
        $resultado = executarSQL(ClaDatabasePostgresql::getConexao(), $sql);
        $resultado->fetchInto($orgao, DB_FETCHMODE_OBJECT);

        return intval($orgao->corglicodi);
    }

    /**
     * [sqlLicitacaoAtaInterna description]
     *
     * @param integer $ano
     *            [description]
     * @param integer $processo
     *            [description]
     * @return string [description]
     */
    public function sqlLicitacaoAtaInterna($processo)
    {
        if (empty($processo)) {
            throw new Exception("Error Processing Request", 1);
        }

        $valores = explode('-', $processo);
        $sql = "
            SELECT DISTINCT l.clicpoproc, l.alicpoanop, l.xlicpoobje, l.ccomlicodi, c.ecomlidesc, o.corglicodi,
                o.eorglidesc, m.emodlidesc, l.clicpocodl, l.alicpoanol, l.cgrempcodi
            FROM sfpc.tblicitacaoportal l
            INNER JOIN sfpc.tborgaolicitante o
                ON l.corglicodi = o.corglicodi
            INNER JOIN sfpc.tbcomissaolicitacao c
                ON l.ccomlicodi = c.ccomlicodi
            INNER JOIN sfpc.tbmodalidadelicitacao m
                ON l.cmodlicodi = m.cmodlicodi
            WHERE l.clicpoproc = %d AND l.alicpoanop = %d AND l.cgrempcodi = %d AND l.ccomlicodi = %d AND l.corglicodi = %d
            ";
        $sql = sprintf($sql, $valores[0], $valores[1], $valores[2], $valores[3], $valores[4]);
        
        return $sql;
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

    /**
     *
     * @param unknown $valores
     */
    public function sqlGerarAtasLicitacao($valores)
    {
       
        $sql = "INSERT INTO sfpc.tbataregistroprecointerna";
        $sql .= "(carpnosequ, clicpoproc, alicpoanop, cgrempcodi,";
        $sql .= "ccomlicodi, corglicodi, earpinobje, aarpinanon,";
        $sql .= "carpincodn, aforcrsequ, tarpindini, aarpinpzvg,";
        $sql .= "farpinsitu, carpnoseq1, cusupocodi, tarpinulat, earpinumf, farpincorp)";
        $sql .= " VALUES(" . $valores . ")";
        
        return $sql;
    }

    /**
     *
     * @param unknown $valores
     */
    public function sqlGerarAtasRegistroPrecoNova($valores)
    {
        $sql = "INSERT INTO sfpc.tbataregistropreconova";
        $sql .= "(carpnosequ, carpnotiat, tarpnoincl, cusupocodi,";
        $sql .= "tarpnoulat)";
        $sql .= " VALUES(" . $valores . ")";

        return $sql;
    }   

    /**
     *
     * @param unknown $ata
     */
    public function sqlRemoveParticipanteItemAta($ata)
    {
        $sql = "DELETE FROM  sfpc.tbparticipanteitematarp";
        $sql .= " WHERE carpnosequ=" . $ata;
        return $sql;
    }

    /**
     *
     * @param unknown $ata
     */
    public function sqlRemoveCaronaItemExternaAta($ata)
    {
        $sql = "DELETE FROM  sfpc.tbcaronaorgaoexternoitem";
        $sql .= " WHERE carpnosequ=" . $ata;
        return $sql;
    }

    /**
     *
     * @param unknown $ata
     */
    public function sqlRemoveCaronaExternaAta($ata)
    {
        $sql = "DELETE FROM  sfpc.tbcaronaorgaoexterno";
        $sql .= " WHERE carpnosequ=" . $ata;
        return $sql;
    }

    /**
     *
     * @param unknown $ata
     */
    public function sqlRemoveCaronaItemInternaAta($ata)
    {
        $sql = "DELETE FROM  sfpc.tbitemcaronainternaatarp";
        $sql .= " WHERE carpnosequ=" . $ata;
        return $sql;
    }

    /**
     *
     * @param unknown $ata
     */
    public function sqlRemoveCaronaInternaAta($ata)
    {
        $sql = "DELETE FROM  sfpc.tbcaronainternaatarp";
        $sql .= " WHERE carpnosequ=" . $ata;
        return $sql;
    }

    /**
     *
     * @param unknown $ata
     */
    public function sqlRemoveParticipanteAta($ata)
    {
        $sql = "DELETE FROM  sfpc.tbparticipanteatarp";
        $sql .= " WHERE carpnosequ=" . $ata;
        return $sql;
    }

    public function sqlExisteAtaInternaAnoNumeracaoOrgao($ataCod, $orgao, $ano, $numeracao)
    {
        $sql  = " SELECT count(*) ";
        $sql .= " FROM sfpc.tbataregistroprecointerna atai ";

        $sql .= " WHERE 1=1 ";
        $sql .= " AND atai.aarpinanon = ".$ano;
        $sql .= " AND atai.carpincodn = ".$numeracao;
        $sql .= " AND atai.corglicodi = ".$orgao;

        $sql .= " AND atai.carpnosequ <> ".$ataCod;

        return $sql;
    }
}

/**
 *
 * @author jfsi
 *
 */
class RegistroPreco_Negocio_CadRegistroPrecoLicitacaoAtas extends Negocio_Abstrata
{

    /**
     *
     * {@inheritdoc}
     *
     * @see Negocio_Abstrata::getDados()
     */
    public function getDados()
    {
        $this->setDados(new RegistroPreco_Dados_CadRegistroPrecoLicitacaoAtas());
        return parent::getDados();
    }

    /**
     *
     * @param array $processo
     * @param unknown $valorAtual
     * @return boolean
     */
    public function verficarUltimaNumeracaAtaGerada($processo, $valorAtual)
    {
        $valor = explode('-', $processo);

        $sql = sprintf("SELECT MAX(arpi.CARPNOSEQU)
               FROM SFPC.TBATAREGISTROPRECOINTERNA arpi
              where 1 =1
                    AND arpi.clicpoproc = %d
                    and arpi.alicpoanop = %d
                    and arpi.cgrempcodi = %d
                    and arpi.ccomlicodi = %d
                    and arpi.corglicodi = %d", $valor[0], $valor[1], $valor[2], $valor[3], $valor[4]);

        $resultado = $this->getDados()->executarSQL($sql);
        $this->getDados()->hasError($resultado);
        $valorMaximo = end($resultado);
        return $valorMaximo->max == $valorAtual;
    }


     /**
     *
     * @param array $processo
     * @param unknown $valorAtual
     * @return boolean
     */
    public function verficarUltimaNumeracaSequenciaAta($processo, $valorAtual)
    {
        $db = Conexao();
        $valor = explode('-', $processo);

        $sql = sprintf("SELECT max(arpi.carpincodn)
               FROM SFPC.TBATAREGISTROPRECOINTERNA arpi
              where 1 =1                    
                    and arpi.aarpinanon = %d
                    and arpi.corglicodi = %d", Date('Y'), $valor[4]);

        $res = executarSQL($db, $sql);

        $itens = array();
        $item = null;
        while ($res->fetchInto($item, DB_FETCHMODE_OBJECT)) {
            $itens[] = $item;
        }

        if (PEAR::isError($res)) {
            ExibeErroBD(__FILE__ . "\nLinha: ".__LINE__."\nSql: " . $database->getMessage());
        }
        
        return end($itens);
    }


    /**
     *
     * @param integer $ata
     * @throws InvalidArgumentException
     */
    public function removerParticipanteAta($ata)
    {
        if (is_null($ata)) {
            throw new InvalidArgumentException('Carpnosequ deve ser informado');
        }
        $sqlItem = $this->getDados()->sqlRemoveParticipanteItemAta($ata);
        $sql = $this->getDados()->sqlRemoveParticipanteAta($ata);
        executarTransacao(ClaDatabasePostgresql::getConexao(), $sqlItem);
        executarTransacao(ClaDatabasePostgresql::getConexao(), $sql);
    }


    /**
     *
     * @param integer $ata
     * @throws InvalidArgumentException
     */
    public function removerCaronaInternaExternaAta($ata)
    {
        if (is_null($ata)) {
            throw new InvalidArgumentException('Carpnosequ deve ser informado');
        }
        $sqlItemExterna = $this->getDados()->sqlRemoveCaronaItemExternaAta($ata);
        $sqlItemInterna = $this->getDados()->sqlRemoveCaronaItemInternaAta($ata);
        $sqlExterna = $this->getDados()->sqlRemoveCaronaExternaAta($ata);
        $sqlInterna = $this->getDados()->sqlRemoveCaronaInternaAta($ata);
        executarTransacao(ClaDatabasePostgresql::getConexao(), $sqlItemExterna);
        executarTransacao(ClaDatabasePostgresql::getConexao(), $sqlItemInterna);
        executarTransacao(ClaDatabasePostgresql::getConexao(), $sqlExterna);
        executarTransacao(ClaDatabasePostgresql::getConexao(), $sqlInterna);
    }



    public function consultarAtasProcessoLicitario($processo)
    {
        //$sql = $this->getDados()->sqlAtasProcessoLicitario($processo);
        $sql = $this->getDados()->sqlConsultarProcessosItensAtas($processo);
        $resultado = ClaDatabasePostgresql::executarSQL($sql);

        ClaDatabasePostgresql::hasError($resultado);

        return $resultado;
    }

    public function incluirOrgaoParticipante($dto)
    {
        $sql = $this->getDados()->sqlInsercaoOrgaoParticipante($dto->carpnosequ, $dto->corglicodi);
        executarTransacao(ClaDatabasePostgresql::getConexao(), $sql);
    }

    public function incluirItemOrgaoParticipante($dto)
    {
        $sql = $this->getDados()->sqlInsercaoItemParticipante($dto->carpnosequ, $dto->corglicodi, $dto->citarpsequ, $dto->aitelpqtso);
        executarTransacao(ClaDatabasePostgresql::getConexao(), $sql);
    }

    public function consultarLicitacaoAtaInterna($processo)
    {
        $sql = $this->getDados()->sqlLicitacaoAtaInterna($processo);

        $resultado = ClaDatabasePostgresql::executarSQL($sql);

        ClaDatabasePostgresql::hasError($resultado);

        return $resultado;
    }

    public function consultarDCentroDeCustoUsuario($cgrempcodi, $cusupocodi, $corglicodi)
    {   
        $db  = Conexao();      
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
        $db  = Conexao();      
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



    public function gerarAtasLicitacao($corporativo = false)
    {
        $msg = 'Nenhuma ata selecionada';         
        $processo       = filter_var($_POST['processo'], FILTER_SANITIZE_STRING);
        $obje           = filter_var($_POST['obje'], FILTER_SANITIZE_STRING);
        $ano            = filter_var($_POST['ano'], FILTER_SANITIZE_NUMBER_INT);
        $preAta         = $_POST['pre_ata'];
        $anoAta         = $_POST['ano_ata'];
        $numAta         = $_POST['numero_ata'];
        $loteAta        = $_POST['lote'];
        $incluirGestor  = filter_var($_POST['incluir_gestor'], FILTER_SANITIZE_NUMBER_INT);
        $fornecedores   = $this->consultarAtasProcessoLicitario($processo);        
        $fornecedores   = $this->prepararDados($fornecedores);
        $gerar_atas     = $_POST['gerar_ata'];
        $ataCorporativa = $_POST['ataCorporativa'];

        if (empty($fornecedores['atas_nao_geradas'])) {
            $_SESSION['mensagemFeedback'] = ExibeMensStr('Nenhum fornecedor foi encontrado', 0, 1);
            return;
        }

        $carpnosequ = 0;
        $itens      = array();
        $dados = explode('-', $processo);

        if(!empty($gerar_atas) && (count($numAta) == count(array_unique($numAta)))) {
            $db = Conexao(); # Abrindo a Conexão

            // Remover fornecedor que não vai criar a ata
            foreach($fornecedores['atas_nao_geradas'] as $key => $value) {
                if(!in_array($key, $gerar_atas)){
                    unset($fornecedores['atas_nao_geradas'][$key]);
                }
            }

            $carpnosequ = $this->obterProximoNumeroAta();
            $incremento = 0;
            foreach ($fornecedores['atas_nao_geradas'] as $key => $fornecedor) {
                $msg = 'Numeração da ata gerada com sucesso';                
                $atualKey = array_search($key, $gerar_atas);
                $pre    = $preAta[$atualKey];
                $ano_   = $anoAta[$atualKey];
                $numero = $numAta[$atualKey];
                $lote   = $loteAta[$atualKey];
                
                // Verificar número da ata
                $sqlVerificarNumeroAta = $this->getDados()->sqlExisteAtaInternaAnoNumeracaoOrgao($carpnosequ, $dados[4], $ano_, $numero);
                $resultadoNumeroAta = executarSQL($db, $sqlVerificarNumeroAta);
                $resultadoNumeroAta->fetchInto($resultadoCountNumeroAta, DB_FETCHMODE_OBJECT);

                if($resultadoCountNumeroAta->count != 0){
                    $msg = 'A ata  já está cadastrada';  
                    cancelarTransacao($db);
                    break;
                }

                $carpnosequ += $incremento;
                $valoresNova    = $this->getDados()->configurarGerarAtaRegistroPrecoNova($carpnosequ);
                
                if (is_null($key)) {
                    continue;
                }

                $valores    = $this->getDados()->configurarGerarAtas($processo, $key, $carpnosequ, $obje, $numero, $ano_, $ataCorporativa);
                $sqlAtaNova = $this->getDados()->sqlGerarAtasRegistroPrecoNova($valoresNova);
                $sql        = $this->getDados()->sqlGerarAtasLicitacao($valores);
                $itens      = $this->getDados()->selecionarItensDoProcesso($processo, $ano, $key,  $dados[3], $dados[4], $dados[2], $lote);
                $sqlItens   = $this->getDados()->sqlImportacaoItens($itens, $carpnosequ);                            
                
                $resultadoAtaNova       = executarTransacao($db, $sqlAtaNova);            
                $resultadoAtaInterna    = executarTransacao($db, $sql);
                $resultadoImportacao    = executarTransacao($db, $sqlItens);

                if( ($incluirGestor && $_SESSION['_fperficorp_'] == 'S' ) || $_SESSION['_fperficorp_'] != 'S') {
                    $sqlParticipantes = $this->getDados()->sqlImportacaoParticipantes($itens, $carpnosequ);
                    $sqlItensParticipantes = $this->getDados()->sqlImportacaoItensDoParticipante($itens, $carpnosequ);

                    $resultadoParticipantes = executarTransacao($db, $sqlParticipantes);
                    $resultadoItensParticipantes = executarTransacao($db, $sqlItensParticipantes);

                    if( PEAR::isError($resultadoParticipantes) ){
                        $CodErroEmail  = $resultadoParticipantes->getCode();
                        $DescErroEmail = $resultadoParticipantes->getMessage();
                        var_export($DescErroEmail);
                        ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlItens\n\n$DescErroEmail ($CodErroEmail)");
                        cancelarTransacao($db);
                        }

                    if( PEAR::isError($resultadoItensParticipantes) ){
                        $CodErroEmail  = $resultadoItensParticipantes->getCode();
                        $DescErroEmail = $resultadoItensParticipantes->getMessage();
                        var_export($DescErroEmail);
                        ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlItens\n\n$DescErroEmail ($CodErroEmail)");
                        cancelarTransacao($db);
                    }

                }

                if( PEAR::isError($resultadoAtaNova) ){
                    $CodErroEmail  = $resultadoAtaNova->getCode();
                    $DescErroEmail = $resultadoAtaNova->getMessage();
                    var_export($DescErroEmail);
                    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlAtaNova\n\n$DescErroEmail ($CodErroEmail)");
                    cancelarTransacao($db);
                }

                if( PEAR::isError($resultadoAtaInterna) ){
                    $CodErroEmail  = $resultadoAtaInterna->getCode();
                    $DescErroEmail = $resultadoAtaInterna->getMessage();
                    var_export($DescErroEmail);
                    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
                    cancelarTransacao($db);
                }

                if( PEAR::isError($resultadoImportacao) ){
                    $CodErroEmail  = $resultadoImportacao->getCode();
                    $DescErroEmail = $resultadoImportacao->getMessage();
                    var_export($DescErroEmail);
                    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlItens\n\n$DescErroEmail ($CodErroEmail)");
                    cancelarTransacao($db);
                }            

                $incremento++;
            }      

            finalizarTransacao($db);            
        } else if(!empty($gerar_atas) && (count($numAta) != count(array_unique($numAta)))) {
            $msg = 'Numeração da ata repetida';
        }
        
        $_SESSION['mensagemFeedback'] = $msg;
        $_SESSION['mensagemFeedbackTipo'] = 1;
        $uri = "CadRegistroPrecoLicitacaoAtas.php?processo=".$processo."&ano=".$ano;
        header('Location: ' . $uri);
        exit();
    }

    /**
     * Função para separar os tipos de atas
     * para exibição
     * 
     * @param $atas
     * @return array
     */
    public function prepararDados($atas) {
        $dados = array(
            'atas_geradas' => array(), 
            'atas_nao_geradas' => array()
        );

        if(!empty($atas)) {
            foreach($atas as $key => $value) {
                // Verificar se tem numeração
                if(empty($value->citarpitel)) {
                    $dados['atas_nao_geradas'][$value->aforcrsequ]['nforcrrazs'] = $value->nforcrrazs;
                    $dados['atas_nao_geradas'][$value->aforcrsequ]['lotes'][] = $value->citelpnuml;
                } else {
                    $dados['atas_geradas'][$value->aforcrsequ . $value->carpincodn]['nforcrrazs'] = $value->nforcrrazs;
                    $dados['atas_geradas'][$value->aforcrsequ . $value->carpincodn]['carpnosequ'] = $value->carpnosequ;
                    $dados['atas_geradas'][$value->aforcrsequ . $value->carpincodn]['alicpoanop'] = $value->alicpoanop;
                    $dados['atas_geradas'][$value->aforcrsequ . $value->carpincodn]['aforcrsequ'] = $value->aforcrsequ;
                    $dados['atas_geradas'][$value->aforcrsequ . $value->carpincodn]['carpincodn'] = $value->carpincodn;
                    $dados['atas_geradas'][$value->aforcrsequ . $value->carpincodn]['aarpinanon'] = $value->aarpinanon;
                    $dados['atas_geradas'][$value->aforcrsequ . $value->carpincodn]['lotes'][] = $value->citarpnuml;  
                }
            }
        }        
        
        return $dados;
    }

    public function consultarAtaGeradaPeloOrgao($processo)
    {
        if (! $processo) {
            throw new InvalidaArgumentException('Processo não informado');
        }
        $sql = $this->getDados()->sqlNumeroAtaGeradaPeloOrgao($processo);

        $resultado = $this->getDados()->executarSQL($sql);
        $this->getDados()->hasError($resultado);
        return $resultado;
    }


    public function consultarExisteSccOuCaronaExterna($chaveAtaCod)
    {
        $retorno = false;
        $sql =  $this->getDados()->sqlExisteCaronaExterna($chaveAtaCod);
        $resultado = executarSQL(ClaDatabasePostgresql::getConexao(), $sql);
        $resultado->fetchInto($resultadoCountCarona, DB_FETCHMODE_OBJECT);

        $sql =  $this->getDados()->sqlExisteScc($chaveAtaCod);
        $resultado = executarSQL(ClaDatabasePostgresql::getConexao(), $sql);
        $resultado->fetchInto($resultadoCountScc, DB_FETCHMODE_OBJECT);
               
        if(($resultadoCountCarona->count != 0) || ( $resultadoCountScc->count != 0)){
            $retorno = true;
        }

        return $retorno;
    }


    public function validarAtaDesfazerNumeracao($carpnosequ){ 
       
        $retorno = true;                
        $mensagemPassada = "";       
        
        $contemCaronaExternaOuScc = $this->consultarExisteSccOuCaronaExterna($carpnosequ);
        if($contemCaronaExternaOuScc){
            $erros = true;
            $mensagemPassada = 'Não é possível desfazer a numeração, pois esta ata já está relacionda com uma Solicitação de Compra do tipo SARP ou Carona Externa';
            
            $_SESSION['mensagemFeedback'] = $mensagemPassada;
            $_SESSION['mensagemFeedbackTipo'] = 2;
            $retorno = false;
        }            

        return $retorno;
    }



    public function recuperarLicitacaoAtas()
    {
        $processo = $_POST['processo'];

        $licitacoes = $this->consultarLicitacaoAtaInterna($processo);

        return $licitacoes;
    }

    public function recuperarFornecedoresLicitacao($processo)
    {
        $db = Conexao();
        $sql = $this->getDados()->sqlConsultarProcessosItensAtas($processo);        
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

    /**
     *
     * @todo Repositorio
     */
    public function verificarSCCDoTipoSARP($atas)
    {
        $sql = $this->getDados()->sqlVerificarSCCDoTipoSARP($atas);

        $resultado = ClaDatabasePostgresql::executarSQL($sql);

        ClaDatabasePostgresql::hasError($resultado);

        return ($resultado != null) ? true : false;
    }

    public function obterProximoNumeroAta()
    {
        $sql = $this->getDados()->sqlConsultarMaiorAta();
        $resultado = executarSQL(Conexao(), $sql);
        $resultado->fetchInto($valorMaximo, DB_FETCHMODE_OBJECT);

        $valorAtual = intval($valorMaximo->max) + 1;

        return $valorAtual;
    }

    public function excluirAtasProcesso($atas)
    {
        return $this->getDados()->excluirAtasProcesso($atas);
    }

    public function consultarOrgaoParticipantesProcesso($processo, $ano, $orgao, $comissao, $grupo, $ata)
    {
        $sql = $this->getDados()->sqlConsutarParticipantesProcesso($processo, $ano, $orgao, $comissao, $grupo, $ata);

        $resultado = ClaDatabasePostgresql::executarSQL($sql);

        ClaDatabasePostgresql::hasError($resultado);

        return $resultado;
    }
}

/**
 *
 * @author jfsi
 *
 */
class RegistroPreco_Adaptacao_CadRegistroPrecoLicitacaoAtas extends Adaptacao_Abstrata
{

    /**
     *
     * {@inheritdoc}
     *
     * @see Adaptacao_Abstrata::getNegocio()
     */
    public function getNegocio()
    {
        $this->setNegocio(new RegistroPreco_Negocio_CadRegistroPrecoLicitacaoAtas());
        return parent::getNegocio();
    }

    /**
     *
     * @param unknown $atas
     */
    public function plotarBlocoNumeroAtas($gui, $atas)
    {
        $_SESSION['processo_selecionado'] = $_REQUEST['processo'];        
        
        $dados = $this->getNegocio()->prepararDados($atas);
        $perfilAdmin = in_array($_SESSION['_cperficodi_'], array(2,6));
        $dto          = $this->getNegocio()->consultarDCentroDeCustoUsuario($_SESSION['_cgrempcodi_'], $_SESSION['_cusupocodi_'], end(explode("-", $_REQUEST['processo'])));
        $objeto       = current($dto);        
        $processo     = $_REQUEST['processo'];
        $ano          = $_REQUEST['ano'];
        $forn         = new RegistroPreco_Negocio_CadRegistroPrecoLicitacaoAtas();
        $valorAtual   = "";
        $contadorSequencialAta = $forn->verficarUltimaNumeracaSequenciaAta($processo, $valorAtual);

        $cont = 0;
        $id = 0;
        if ($contadorSequencialAta->max == null) {
            $cont = 1;
        }else{
            $cont = $contadorSequencialAta->max + 1;
        }
        
        // Atas sem numeração
        if(!empty($dados['atas_nao_geradas'])) {
            foreach ($dados['atas_nao_geradas'] as $key => $fornecedor) {     
                $pre  = $numeroAtaFormatado = $objeto->ccenpocorg . str_pad($objeto->ccenpounid, 2, '0', STR_PAD_LEFT);
                $num  = str_pad($cont, 4, "0", STR_PAD_LEFT);
                $cont += 1;                
                $ano_ = date("Y");
                $gui->getTemplate()->VALOR_ID                   = $id;
                $gui->getTemplate()->VALOR_PRE                  = $pre;
                $gui->getTemplate()->VALOR_NUMERO               = $num;
                $gui->getTemplate()->VALOR_ANO                  = $ano_;
                $gui->getTemplate()->VALOR_LOTE                 = implode(',', array_unique($fornecedor['lotes']));
                $gui->getTemplate()->VALOR_FORNECEDOR           = $fornecedor['nforcrrazs'];
                $gui->getTemplate()->VALOR_ID_FORNECEDOR        = $key;
                $gui->getTemplate()->block("BLOCO_RESULTADO_ATAS_NAO_GERADAS");
                $id++;
            } 

            $gui->getTemplate()->block('BLOCO_RESULTADO_ATAS_NAO_GERADAS_TITULO');
            $gui->getTemplate()->block("BLOCO_BOTAO_GERAR");
        }
        
        // Ata com numeração
        if(!empty($dados['atas_geradas'])) {
            foreach ($dados['atas_geradas'] as $key => $ata) {
                $ataInterna = $this->getNegocio()->procurar($ata['carpnosequ']);
                $dto        = $this->getNegocio()->consultarDCentroDeCustoUsuario($ataInterna[0]->cgrempcodi, $ataInterna[0]->cusupocodi, $ataInterna[0]->corglicodi);
                $objeto     = current($dto);
                $processo_ = explode('-', $processo);
                            
                $uri = 'CadRegistroPrecoLicitacaoNumeracaoAtas.php?';
                $uri .= 'processo=' . $processo_[0] . '&';
                $uri .= 'ano=' . $processo_[1] . '&';
                $uri .= 'fornecedor=' . $key;
                $uri .= '&ata=' . $ataInterna[0]->carpincodn;
                $uri .= '&grupo=' . $ataInterna[0]->cgrempcodi;
                $uri .= '&ccenpocorg=' . $objeto->ccenpocorg;
                $uri .= '&ccenpounid=' . $objeto->ccenpounid;
                $uri .= '&seqAta=' . $ata['carpnosequ'];
                
                $numeroAtaFormatado = $objeto->ccenpocorg . str_pad($objeto->ccenpounid, 2, '0', STR_PAD_LEFT);                            
                $numeroAtaFormatado .= "." . str_pad($ataInterna[0]->carpincodn, 4, "0", STR_PAD_LEFT) . "/" . $ata['aarpinanon'];

                $gui->getTemplate()->VALOR_ENDERECO_DETALHE_ATA = '<a href="'.$uri.'">'.$numeroAtaFormatado.'</a>';                
                $gui->getTemplate()->VALOR_NUMERO_ATA_HIDDEN = $numeroAtaFormatado;
                $gui->getTemplate()->VALOR_FORNECEDOR = $ata['nforcrrazs'];
                $gui->getTemplate()->VALOR_LOTE = implode(',', array_unique($ata['lotes']));
                $gui->getTemplate()->VALOR_CARPNOSEQU = $ata['carpnosequ'];
                $gui->getTemplate()->DISPLAY_CHECKBOX_DESFAZER = ($perfilAdmin) ? '' : 'display:none';
                $gui->getTemplate()->block("BLOCO_RESULTADO_ATAS");
            }

            $gui->getTemplate()->block('BLOCO_RESULTADO_ATAS_TITULO'); 
            
            if($perfilAdmin) {
                $gui->getTemplate()->block("BLOCO_BOTAO_DESFAZER");
            }
        }           
    }    
}

class RegistroPreco_UI_CadRegistroPrecoLicitacaoAtas extends UI_Abstrata
{

    /**
     * [$template description]
     *
     * @var \TemplatePaginaPadrao
     */
    private $template;

    /**
     * Gets the value of template.
     *
     * @return mixed
     */
    private $licitacao;
    
    private $corporativo;

    private function plotarLicitacao($licitacoes)
    {       
        function ConsultaAta($processo, $ano, $grupo, $orgao, $comissao)
        {
            
            $sql = "
                SELECT * FROM sfpc.tbataregistroprecointerna 
                WHERE clicpoproc =  $processo AND alicpoanop = $ano AND cgrempcodi = $grupo AND corglicodi = $orgao AND ccomlicodi = $comissao   
            ";
            $res = executarSQL(Conexao(), $sql);
            $res->fetchInto($resultado, DB_FETCHMODE_OBJECT);
            return $resultado;
        }


        $licitacoes = current($licitacoes);
        // var_dump($licitacoes->clicpoproc, $licitacoes->alicpoanop, $licitacoes->cgrempcodi, $licitacoes->corglicodi, $licitacoes->ccomlicodi);die;
        $ata = ConsultaAta($licitacoes->clicpoproc, $licitacoes->alicpoanop, $licitacoes->cgrempcodi, $licitacoes->corglicodi, $licitacoes->ccomlicodi);
        
        $this->getTemplate()->VALOR_COMISSSAO = $licitacoes->ecomlidesc;
        $this->getTemplate()->VALOR_PROCESSO = str_pad($licitacoes->clicpoproc, 4, '0', STR_PAD_LEFT);
        $this->getTemplate()->VALOR_ANO = $licitacoes->alicpoanop;
        $this->getTemplate()->VALOR_MODALIDADE = $licitacoes->emodlidesc;
        $this->getTemplate()->VALOR_LICITACAO = str_pad($licitacoes->clicpocodl, 4, '0', STR_PAD_LEFT);
        $this->getTemplate()->VALOR_ANO_LICITACAO = $licitacoes->alicpoanol;
        $this->getTemplate()->VALOR_ORG_LIMITE = $licitacoes->eorglidesc;

        $exibirGestor = 'display:none';
        $valorGestor = "0";
        $valorAtaCorp = 'display:none';
        $exibirAtaCorp = '';
        $perfilUsuario = $_SESSION['_cperficodi_'];
        
        $ataCorp = empty($ata->farpincorp)?$_POST['ataCorporativa']:$ata->farpincorp;

        if ($this->corporativo && ($ataCorp == "N" || $ataCorp == NULL)) {
            $exibirGestor = '';
            $valorGestor = '<select name="incluir_gestor">';
            $valorGestor .= '<option value="1">Sim</option>';
            $valorGestor .= '<option value="0">Não</option>';
            $valorGestor .= '</select>';
        }

        if(empty($ata->farpincorp)){
            $marcado_N = ($_POST['ataCorporativa'] == "N") ? 'checked':'';
            $marcado_S = ($_POST['ataCorporativa'] == "S") ? 'checked':'';
        }else{
            if($ata->farpincorp == "N"){
                $marcado_N = 'checked';
            }else{
                $marcado_S = 'checked';
            }
        }

        
        if($perfilUsuario == 2){
            $exibirAtaCorp = '';
            $valorAtaCorp = '<input type="radio" name="ataCorporativa" id="ataCorporativa_N" value="N" checked '.$marcado_N.' onChange="submit();">';
            $valorAtaCorp .= '<label for="ataCorporativa_N">NÃO</label>';
            $valorAtaCorp .= '<input type="radio" name="ataCorporativa" id="ataCorporativa_S" value="S" '.$marcado_S.' onChange="submit();">';
            $valorAtaCorp .= '<label for="ataCorporativa_S">SIM</label>';
        }
        
        $this->getTemplate()->VALOR_EXIBIR_GESTOR = $valorGestor;
        $this->getTemplate()->VALOR_ATA_CORP = $valorAtaCorp;
        $this->getTemplate()->EXIBIR_GESTOR = $exibirGestor;
        $this->getTemplate()->EXIBIR_ATA_CORP = $exibirAtaCorp;

        $this->getTemplate()->VALOR_OBJETO = $licitacoes->xlicpoobje;

        $this->getTemplate()->block("BLOCO_LICITACAO");
        $this->getTemplate()->block("BLOCO_RESULTADO_PESQUISA");
    }

    /**
     * [__construct description]
     */
    public function __construct()
    {
        $template = new TemplatePaginaPadrao("templates/CadRegistroPrecoLicitacaoAtas.html", "Registro Preço > Ata Interna > Gerar Numeração");
        $this->setTemplate($template);
        $this->corporativo = ($_SESSION['_fperficorp_'] == 'S') ? true : false;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see UI_Abstrata::getAdaptacao()
     */
    public function getAdaptacao()
    {
        $this->setAdaptacao(new RegistroPreco_Adaptacao_CadRegistroPrecoLicitacaoAtas());
        return parent::getAdaptacao();
    }

    /**
     * [proccessPrincipal description]
     *
     * @param [type] $variablesGlobals
     *            [description]
     * @return [type] [description]
     */
    public function proccessPrincipal()
    {
        // unset($_SESSION['mensagemFeedback']);
        $this->imprimeBlocoMensagem();

        $ano = filter_var($_REQUEST['ano'], FILTER_SANITIZE_NUMBER_INT);
        $processo = filter_var($_REQUEST['processo'], FILTER_SANITIZE_NUMBER_INT);
        
        // Consulta e carrega para a tela os dados da licitação
        $licitacoes = $this->getAdaptacao()->getNegocio()->consultarLicitacaoAtaInterna($processo);
        if (is_null($licitacoes)) {
            header('Location: CadAtaRegistroPrecoGerarNumeracao.php');
            exit();
        }

        $this->plotarLicitacao($licitacoes);

        $licitacao = $licitacoes[0];
        $fullProcesso = $licitacao->clicpoproc . '-' . $licitacao->alicpoanop . '-' . $licitacao->cgrempcodi . '-' . $licitacao->ccomlicodi . '-' . $licitacao->corglicodi;

        // Consulta e carrega para a tela os dados das atas
        $atas = $this->getAdaptacao()->getNegocio()->consultarAtasProcessoLicitario($fullProcesso);
        $this->getAdaptacao()->plotarBlocoNumeroAtas($this, $atas);

        $this->getTemplate()->BOTAOANO = $ano;
        $this->getTemplate()->BOTAOPROCESSO = $processo;
        $this->getTemplate()->BOTAOOBJE = $licitacao->xlicpoobje;

        $this->getTemplate()->block("BLOCO_BOTOES");
    }

    public function gerarAtasLicitacao()
    {
        $this->getAdaptacao()
            ->getNegocio()
            ->gerarAtasLicitacao();
        $this->proccessPrincipal();
    }

    /**
     * [desfazerAtasLicitacao description]
     *
     * @param [type] $variablesGlobals
     *            [description]
     * @return [type] [description]
     */
    public function desfazerAtasLicitacao()
    {
        $processo = array();
        // @todo refatorar a validação
        if (! isset($_POST['processo'])) {
            return;
        }
        $processo      = $_POST['processo'];
        $ano           = $_POST['ano'];
        $desfazer_atas = $_POST['desfazer_atas'];
        $sccTipoSarp   = $this->getAdaptacao()->getNegocio()->verificarSCCDoTipoSARP($desfazer_atas);

        // Se não for do SCC do tipo SARP, pode deletar
        if ($sccTipoSarp) {
            $_SESSION['mensagemFeedback'] = ExibeMensStr(" Valor do SCC é do tipo SARP", 1, 0);

            $uri = "CadRegistroPrecoLicitacaoAtas.php?processo=$processo&ano=" . $ano;
            header("Location: " . $uri);
            exit();
        }

        $atas = $this->getAdaptacao()->getNegocio()->consultarAtaGeradaPeloOrgao($processo);

        if (!is_null($atas)) {
            $ataAtual = end($atas);
            $valorAtual = $ataAtual->carpnosequ;
        }        

        // Verifica se é a última numeração da ata
        $ultimaAtaGerada = $this->getAdaptacao()->getNegocio()->verficarUltimaNumeracaAtaGerada($processo, $valorAtual);

        if (! $ultimaAtaGerada) {
            $_SESSION['mensagemFeedback'] = ExibeMensStr(" Numeração Atual não é a última gerada", 1, 0);
            $uri = "CadRegistroPrecoLicitacaoAtas.php?processo=" . $_POST['processo'] . "&ano=" . $ano;
            header("Location: " . $uri);
            exit();
        } else {
            if(!empty($desfazer_atas)) {
                // Remover as atas que não vão ser desfeitas
                foreach($atas as $key_ => $value) {
                    if(!in_array($value->carpnosequ, $desfazer_atas)) {
                        unset($atas[$key_]);
                    }
                }
            
                foreach ($atas as $key => $ata) {
                    $valorAtualDeletar = $ata->carpnosequ;
                    $validarSccAtaECaronaExterna = true;
                    $validarSccAtaECaronaExterna = $this->getAdaptacao()->getNegocio()->validarAtaDesfazerNumeracao($valorAtualDeletar);
                    if(!$validarSccAtaECaronaExterna){
                        $uri = "CadRegistroPrecoLicitacaoAtas.php?processo=" . $_POST['processo'] . "&ano=" . $ano;
                        header("Location: " . $uri);
                        exit();
                    }
                    
                    $this->getAdaptacao()->getNegocio()->removerCaronaInternaExternaAta($valorAtualDeletar);
                    $this->getAdaptacao()->getNegocio()->removerParticipanteAta($valorAtualDeletar);
                    $excluido = $this->getAdaptacao()->getNegocio()->excluirAtasProcesso($valorAtualDeletar);
                }
                
                if (!$excluido) {
                    $msg = 'Ocorreu um erro ao tentar desfazer a numerção da ata';
                    $tipoMsg = 2;
                }else{
                    $msg = 'Numeração desfeita com sucesso';
                    $tipoMsg = 1;
                }
            } else {
                $msg = 'Nenhuma ata selecionada';
            }

            $_SESSION['mensagemFeedback'] = $msg;
            $_SESSION['mensagemFeedbackTipo'] = 1;
            $uri = "CadRegistroPrecoLicitacaoAtas.php?processo=".$processo."&ano=".$ano;
            header('Location: ' . $uri);
            exit();
        }
    }

    /**
     * Sets the value of template.
     *
     * @param TemplatePaginaPadrao $template
     *            the template
     *
     * @return self
     */
    public function setNegocio(RegistroPreco_Negocio_CadRegistroPrecoLicitacaoAtas $negocio)
    {
        $this->negocio = $negocio;
    }

    public function processVoltar()
    {
        $uri = 'CadAtaRegistroPrecoGerarNumeracao.php';
        header('Location: ' . $uri);
        exit();
    }
}
$app = new RegistroPreco_UI_CadRegistroPrecoLicitacaoAtas();

$botao = isset($_POST['Botao']) ? $_POST['Botao'] : 'Principal';

switch ($botao) {
    case 'Voltar':
        $app->processVoltar();
        break;
    case 'Desfazer':
        $app->desfazerAtasLicitacao();
        break;
    case 'Gerar':
        $app->gerarAtasLicitacao();
        break;
    case 'Principal':
    default:
        $app->proccessPrincipal();
        break;
}

$app->getTemplate()->show();
