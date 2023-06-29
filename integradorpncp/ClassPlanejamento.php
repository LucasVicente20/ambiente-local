<?php
/**
 * Portal de Compras
 * Programa: ConsIntegracaoManualPNCP.php
 * Autor: José Rodrigo
 * Data: 01/02/2023
 * Objetivo: Programa para consultar integração PNCP 
 * Tarefa Redmine: #277657
 * -------------------------------------------------------------------
 */

# Executa o controle de segurança	#
session_start();

# Acesso ao arquivo de funções #
require_once "../funcoes.php";

class Planejamento
{



    public $conexaoDb;

    public function __construct()
    {
        $this->conexaoDb = conexao();
    }

    //Função utilizada em CadIncluirDFD.php
    //Verifica se não é SQL Injection
    public function anti_injection($sql)
    {
        // remove palavras que contenham sintaxe sql
        preg_match("/(from|select|insert|delete|where|drop table|show tables|#|\*|--|\\\\)/", $sql, $matches);
        $sql = @preg_replace($matches, "", $sql);
        $sql = trim($sql); //limpa espaços vazio
        $sql = strip_tags($sql); //tira tags html e php
        $sql = addslashes($sql); //Adiciona barras invertidas a uma string
        return $sql;
    }


    /**
     * Função utilizada em ConsIntegracaoManualPNCP.php;
     * Lista um array com os sistemas de origem;
     */
    public function getSistemaOrigem()
    {
        $sqlSitOrigem = "select distinct (fpncpisist)  as sistema
                      from sfpc.tbpncpintegracao 
                      where fpncpisitu <> 'PR'";

        $resultado = executarSQL($this->conexaoDb, $sqlSitOrigem);
        $dadosSit = array();

        while ($resultado->fetchInto($retornoSit, DB_FETCHMODE_OBJECT)) {
            $dadosSit[] = $retornoSit;
        }

        return $dadosSit;
    }

    /**
     * Função utilizada em IntegracaoManualPNCP.php;
     * Gera um novo numero sequencial do DFD para orgão e ano;
     */
    function novoSequencialIntegracaoManualPNCP()
    {
        $sql = "select max(cintplsequ) from sfpc.tbintegracaoplano"; //"select max(cpldfdnumd) from sfpc.tbplanejamentodfd where corglicodi = $corglicodi and apldfdanod = $apldfdanod";
        $retorno = executarSQL($this->conexaoDb, $sql);
        $result = 0;
        $retorno->fetchInto($result, DB_FETCHMODE_OBJECT);
        $id = $result->max + 1;
        return $id;
    }

    function insertIntegracaoManualPNCP($dados)
    {
        $id = $this->novoSequencialIntegracaoManualPNCP();
        $sql = "
        insert into sfpc.tbintegracaoplano (
            cintplsequ,
 			cintagsequ,
            aintplcnpj,
            fintplsist,
            aintplnmsv,
            fintploper,
            fintplstre,
            tintpldtex,
            eintpljust,
            fintplstat,
            fintpltpit,
            cusupocodi,
            tintplulat,
            tintplincl,            
            dintpldtin, 
            dintpldtfi 
        ) values(
            " . $id . ",
        	null,
            " . $dados['cnpj'] . ",
            '" . $dados['selectSitOrigem'] . "',
            '" . $dados['servico'] . "', 
            '" . $dados['tipoOperacao'] . "', 
            '" . $dados['statusProcessamento'] . "', 
                now(), 
            '" . $dados['justificativa'] . "', 
            'AG',
            'M',
            3,
            now(),
            now(),
            " . $dados['dataInicial'] . ",
            " . $dados['dataFim'] . "
        )
        ";

        $inserirIntegracao = executarSQL($this->conexaoDb, $sql);
        return true;
    }


    /**
     * Função utilizada em IntegracaoAutomaticaPNCP.php;
     * Gera um novo numero sequencial do DFD para orgão e ano;
     */
    function novoSequencialIntegracaoAutomaticaPNCP()
    {
        $result = 0;
        $sql = "select max(cintagsequ)+1 as seq from sfpc.tbintegracaoagenda where cintagsequ is not null"; //"select max(cpldfdnumd) from sfpc.tbplanejamentodfd where corglicodi = $corglicodi and apldfdanod = $apldfdanod";
        $retorno = executarSQL($this->conexaoDb, $sql);
        $retorno->fetchInto($result, DB_FETCHMODE_OBJECT);
        $id = $result->seq;
        return $id;
    }
    public function insertIntegracaoAutomaticaPNCP($dados)
    {
        $id = $this->novoSequencialIntegracaoAutomaticaPNCP();
        $sql = "
        insert into sfpc.tbintegracaoagenda (
            cintagsequ, aintagdesc, aintagcnpj, fintagsist, aintagnmsv, fintagoper, fintagstat, 
            tintagdtin, tintagdtfi, 
            fintagsegd, fintagterc, fintagquar, 
            fintagquin, 
            fintagsext, 
            fintagsabd, 
            fintagdomi, 
            fintagquiz, 
            fintagtrin, 
            tintaghora, 
            tintagincl,
            cusupocodi, 
            tintagulat  
        )values(
            " . $id . ",
            '" . $dados['descricao'] . "',
            " . $dados['cnpj'] . ",
            '" . $dados['selectSitOrigem'] . "',
            '" . $dados['servico'] . "',
            '" . $dados['tipoOperacao'] . "',
            '" . $dados['statusProcessamento'] . "',
            " . $dados['dataInicial'] . ",
            " . $dados['dataFim'] . ",
            '" . $dados['segunda'] . "',
            '" . $dados['terca'] . "',
            '" . $dados['quarta'] . "',
            '" . $dados['quinta'] . "',
            '" . $dados['sexta'] . "',
            '" . $dados['sabado'] . "',
            '" . $dados['domingo'] . "',
            '" . $dados['_15dias'] . "',
            '" . $dados['_30dias'] . "',
            " . $dados['horario'] . ",
            now(),
            3,
            now()
        )
        ";

        $inserirIntegracao = executarSQL($this->conexaoDb, $sql);

        $this->inserirIntegracaoPlano($dados, $id);
        return true;
    }

    public function inserirIntegracaoPlano($dados, $idAgendamento)
    {

        $id = $this->novoSequencialIntegracaoManualPNCP();
        $sql = "
            insert into sfpc.tbintegracaoplano (
                cintplsequ,
                cintagsequ,
                aintplcnpj,
                fintplsist,
                aintplnmsv,
                fintploper,
                fintplstre,
                tintpldtex,
                dintpldtin, 
                dintpldtfi,
                fintplstat,                
                fintpltpit,
                cusupocodi,
                tintplulat,
                tintplincl 
            ) values(
                " . $id . ",
                " . $idAgendamento . ",
                " . $dados['cnpj'] . ",
                '" . $dados['selectSitOrigem'] . "',
                '" . $dados['servico'] . "', 
                '" . $dados['tipoOperacao'] . "', 
                '" . $dados['statusProcessamento'] . "', 
                    now(),                     
                " . $dados['dataInicial'] . ",
                " . $dados['dataFim'] . ",
                'AG',
                'A',
                3,
                now(),
                now()
            )
        ";

        $inserirIntegracao = executarSQL($this->conexaoDb, $sql);
    }

    /**
     * Função utilizada em ConsPesquisarDFD.php, CadManterDFD;
     * Pega todos os dados do DFD;
     */
    public function getDadosIntegracao($dados)
    {   
        //print_r($dados);exit;  
        // fazer dessa fforma vai dar uma sobrecarga na pesquisa, é mais rápido fazer uma sql modulável;
        $sql = "
            select 
                plano.*, 
                (select aintagdesc from sfpc.tbintegracaoagenda a where a.cintagsequ = plano.cintagsequ ) as descricao 
            from 
                sfpc.tbintegracaoplano plano 
        ";

        //Período
        if (!empty($dados["dataIni"])) {                    
            $sqlWhere .= " AND plano.dintpldtin >= '". $dados["dataIni"] ."' AND plano.dintpldtfi <= '".$dados["dataFim"]."'";
        }

        //Servico
        if (!empty($dados["servico"])) {                    
            $sqlWhere .= " AND plano.aintplnmsv = '" . $dados["servico"] . "'";
        }

        //Id da integracao
        if (!empty($dados["idIntegracao"])) {                    
            $sqlWhere .= " AND plano.cintplsequ  = '" . $dados["idIntegracao"] . "'";
        }

        //Tipo de operacao
        if (!empty($dados["tipoOperacao"])) {                    
            $sqlWhere .= " AND plano.fintploper  = '" . $dados["tipoOperacao"] . "'";
        }

        //Tipo de integracao
        if (!empty($dados["tipoIntegracao"])) {                    
            $sqlWhere .= " AND plano.fintpltpit = '" . $dados["tipoIntegracao"] . "'";
        }



        $sql .= " WHERE plano.fintplsist = 'PC' ";
        $sql .= $sqlWhere;
        $sql .= " ORDER BY plano.cintplsequ desc";

        //print_r($sql);exit;
        $resultado = executarSql($this->conexaoDb, $sql);
        $dadosSelectDFD = array();
        $countDFD = 0;

        while ($resultado->fetchInto($countDFD, DB_FETCHMODE_OBJECT)) {
            $dadosSelectDFD[] = $countDFD;
        }

        return $dadosSelectDFD;
    }

    /**
     * Função utilizada em ConsSelecionarManterDFD.php, 
     * Pega todos os dados do DFD encontrados e monta o sql para a página;
     */
    public function montaHTMLIntegracao($dadosIntegracao)
    {
        if (empty($dadosIntegracao)) {
            $html = '<tr>
                    <td align="center" bgcolor="#75ADE6" colspan="8" class="titulo3" width="auto">
                            Resultado da pesquisa
                        </td>
                    </tr>
                    <tr>
                        <td align="center" colspan="8" class="titulo3" width="900px">Pesquisa sem ocorrências.</td>
                    </tr>';
        } else {
            $html = '
                        <tr>
                            <td align="center" bgcolor="#75ADE6" colspan="8" class="titulo3">
                                Resultado da pesquisa
                            </td>
                        </tr>';

            $html .= '<tr>
                        <td>
                        <table class="tablePesquisa textonormal" style=" position:relative;  width :auto; font-size: 10px">
                        <thead>
                            <tr id="cabecalhos">
                                    <td width="2%"  id="cabIdDFD">ID</td>
                                    <td width="55px" id="cabAno">DESCRICAO</td>
                                    <td width="55px"  id="cabCodClasse">SIST. ORIGEM</td>
                                    <td width="290px"  id="cabDescClasse">DATA INT.</td>
                                    <td width="108px"  id="cabDataPrevistaConclusao">HORA INT.</td>
                                    <td width="114px"  id="cabTpProcesso">SERVIÇO</td>
                                    <td width="75px"  id="cabGrauPrioridade">TP. OPERAÇÃO</td>
                                    <td width="130px"  id="cabSituacao">TP. INTEGRAÇÃO</td>
                                    <td width="130px"  id="cabSituacao">SITUAÇÃO INTEGRAÇÃO</td>
                            </tr>
                        </thead>
                        <tbody>';


            foreach ($dadosIntegracao as $dados) {
                $situacao = $dados->fintplstat == "AG" ? "AGUARDANDO" : "EXECUTADO";
                $tipoIntegracao = $dados->fintpltpit == "A" ? "AUTOMÁTICA" : "MANUAL";
                $sistema = $dados->fintplsist == "PC" ? "PORTAL DE COMPRAS" : "OUTROS";
                $descricao = $dados->fintpltpit == "A" ? $dados->descricao : $descricao = $dados->eintpljust;

                if($dados->fintploper == "I"){
                    $operacao = "INCLUSÃO";
                }else if($dados->fintploper == "A"){
                    $operacao = "ALTERAÇÃO";
                }else if($dados->fintploper == "E"){
                    $operacao = "EXCLUSÃO";
                }





                $html .= '<tr id="resultados">
                                <td width="2%" height="3%" class="tdresult" id="resIdDFD">'.$dados->cintplsequ.'</td>
                                <td width="30%" height="3%"  class="tdresult" id="resAno">' . $descricao . '</td>
                                <td width="10%" height="3%"  class="tdresult" id="resCodClasse">' .  $sistema . '</td>
                                <td width="10%" height="3%"  class="tdresult" id="resDescClasse">'.date('d/m/Y', strtotime($dados->tintpldtex)) .  '</td>
                                <td width="8%" height="3%"  class="tdresult" id="resDataPrevistaConclusao">' .date('H:i', strtotime($dados->tintpldtex)) . '</td>
                                <td width="10%" height="3%"  class="tdresult" id="resTpProcesso">' . strtoupper($dados->aintplnmsv) . '</td>
                                <td width="10%" height="3%"  class="tdresult" id="resGrauPrioridade">' . $operacao . '</td>
                                <td width="10%" height="3%"  class="tdresult" id="resSituacao">' . $tipoIntegracao . '</td>
                                <td width="10%" height="3%"  class="tdresult" id="resSituacao">' . $situacao . '</td>
                    </tr>';
            }
            $html .= '</tbody></table></td></tr>';
        }
        return $html;
    }

}
?>
