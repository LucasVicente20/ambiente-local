<?php
// 220038--
/**
 *
 * @author jfsi
 */
class ClaRegistroPrecoIntencao extends AbstractApplication
{
    const MENSAGEM_INTENCAO_RESPOSTA_ATIVA = 'Intenção não pode ser excluída, pois já possui resposta ativa';

    const MENSAGEM_INTENCAO_EXCLUIDA_SUCESSO = 'Intenção excluída com sucesso';

    const MENSAGEM_INTENCAO_EXCLUIDA_ERRO = 'Falha ao excluir intenção';

    const MENSAGEM_INTENCAO_ATUALIZADA_SUCESSO = 'Intenção alterada com sucesso';

    const MENSAGEM_INTENCAO_RESPOSTA_ATIVA_ALTERAR = 'Intenção não pode ser alterada, pois já possui resposta ativa';

    /**
     * Front Controller.
     *
     * Implements flow application
     *
     * @see \Pitang\AbstractApplication::frontController()
     */
    protected function frontController()
    {
    }

    /**
     * Get Orgao Licitante Ativo.
     *
     * @return \DB_pgsql or \DB_Error
     */
    public function getOrgaosLicitantesAtivos()
    {
        $database = ClaDatabasePostgresql::getConexao();
        $sql = ClaRegistroPrecoIntencaoSQL::sqlSelectOrgaosLicitantesAtivos();
        $res = executarSQL($database, $sql);
        
        return $res;
    }

    /**
     * Check if Material Generico.
     *
     * @param int $codigo
     *            [description]
     *            
     * @return bool [description]
     */
    public function isMaterialGenerico($codigo)
    {
        ClaRegistroPrecoIntencaoSQL::validationIntencaoSequencial($codigo);
        
        $database = ClaDatabasePostgresql::getConexao();
        
        $sql = "
            SELECT cmatepsequ
            FROM sfpc.tbmaterialportal
            WHERE cmatepsequ = ?
                AND fmatepgene LIKE 'S'
        ";
        
        $res = &$database->query($sql, $codigo);
        
        ClaDatabasePostgresql::hasError($res);
        
        return ($res->numRows() > 0) ? true : false;
    }

    /**
     *
     * @param int $sequencialIntencao            
     * @param int $anoIntencao            
     *
     * @return \DB_result or \DB_error
     */
    public function getIntencao($sequencialIntencao, $anoIntencao)
    {
        $database = ClaDatabasePostgresql::getConexao();
        $sql = ClaRegistroPrecoIntencaoSQL::sqlSelectIntencao($sequencialIntencao, $anoIntencao);
        
        return executarSQL($database, $sql);
    }

    /**
     *
     * @param int $sequencialIntencao            
     * @param int $anoIntencao            
     *
     * @return \DB_result or \DB_error
     */
    public function getItensIntencao($sequencialIntencao, $anoIntencao)
    {
        $database = ClaDatabasePostgresql::getConexao();
        $sql = ClaRegistroPrecoIntencaoSQL::sqlSelectItemIntencao($sequencialIntencao, $anoIntencao);
        
        return executarSQL($database, $sql);
    }

    /**
     *
     * @param int $sequencialIntencao            
     * @param int $anoIntencao            
     *
     * @return \DB_result or \DB_error
     */
    public function getOrgaoLicitanteIntencao($sequencialIntencao, $anoIntencao)
    {
        $database = ClaDatabasePostgresql::getConexao();
        $sql = ClaRegistroPrecoIntencaoSQL::sqlSelectOrgaoLicitanteIntencao($sequencialIntencao, $anoIntencao);
        
        return $database->getCol($sql);
    }

    /**
     *
     * @param int $sequencialIntencao            
     * @param int $anoIntencao            
     * @param string $situacao            
     */
    public function updateSituacaoIntencao($sequencialIntencao, $anoIntencao, $situacao)
    {
        $database = ClaDatabasePostgresql::getConexao();
        $sql = ClaRegistroPrecoIntencaoSQL::sqlUpdateSituacaoIntencao($sequencialIntencao, $anoIntencao, strtolower2($situacao));
        
        executarSQL($database, $sql);
    }

    /**
     *
     * @return \DB_result or \DB_error
     */
    public function getRespostaAtivaIntencao($sequencialIntencao, $anoIntencao)
    {
        $database = ClaDatabasePostgresql::getConexao();
        $sql = ClaRegistroPrecoIntencaoSQL::sqlSelectRespostaIntencao($sequencialIntencao, $anoIntencao, 'A');
        
        return executarSQL($database, $sql);
    }

    /**
     *
     * @param int $sequencialIntencao            
     * @param int $anoIntencao            
     *
     * @return \DB_result or \DB_error
     */
    public function deleteIntencao($sequencialIntencao, $anoIntencao)
    {
        $sqlDeleteIntencao = ClaRegistroPrecoIntencaoSQL::sqlDeleteIntencao($sequencialIntencao, $anoIntencao);
        
        $database = ClaDatabasePostgresql::getConexao();
        $database->autoCommit(false);
        $database->query($sqlDeleteIntencao);
        
        $commited = $database->commit();
        if ($commited instanceof DB_error) {
            $database->rollback();
        }
        
        return $commited;
    }

    /**
     * Delete Itens.
     *
     * @param \DB_common $database            
     * @param unknown $sequencialIntencao            
     * @param unknown $anoSequencial            
     */
    public function deleteIntencaoItens(DB_common $database, $sequencialIntencao, $anoSequencial, $item = null)
    {
        $sqlDeleteIntencao = ClaRegistroPrecoIntencaoSQL::sqlDeleteItemIntencaoRegistroPreco($sequencialIntencao, $anoSequencial, $item);
        $database->query($sqlDeleteIntencao);
    }

    /**
     * Get Situacao da Intencao.
     *
     * @param int $sequencialIntencao            
     * @param int $anoIntencao            
     * @param int $grupoUsuario            
     *
     * @return string
     */
    public function getSituacaoIntencao($sequencialIntencao, $anoIntencao, $grupoUsuario)
    {
        $sql = "
            SELECT a.cintrpsequ, a.cintrpsano, a.corglicodi
            FROM sfpc.tbrespostaintencaorp a
            INNER JOIN sfpc.tbintencaorporgao b
                ON a.cintrpsequ = b.cintrpsequ AND a.cintrpsano = b.cintrpsano
            INNER JOIN sfpc.tborgaolicitante c
                ON b.corglicodi = c.corglicodi
            INNER JOIN sfpc.tbgrupoorgao d
                ON c.corglicodi = d.corglicodi AND d.cgrempcodi = ?
            WHERE a.cintrpsequ = ?
                AND a.cintrpsano = ?
                AND a.frinrpsitu LIKE 'A'
        ";
        
        $database = ClaDatabasePostgresql::getConexao();
        
        $res = $database->getOne($sql, array(
            $grupoUsuario,
            $sequencialIntencao,
            $anoIntencao
        ));
        
        ClaDatabasePostgresql::hasError($res);
        
        return ! is_null($res) ? 'RESPONDIDA' : 'EM ABERTO';
    }

    /**
     *
     * @param int $sequencialIntencao            
     * @param int $anoIntencao            
     * @param string $dataInicioCadastro            
     * @param string $dataFimCadastro            
     * @param int $grupoUsuario            
     */
    public function getIntencaoByDataInicioAndDataFimAndGrupoUsuario($sequencialIntencao, $anoIntencao, $dataInicioCadastro, $dataFimCadastro, $centroCusto = null)
    {
        $database = ClaDatabasePostgresql::getConexao();
        
        $sql = ClaRegistroPrecoIntencaoSQL::sqlSelectIntencaoByDataInicioAndDataFimAndGrupoUsuario($sequencialIntencao, $anoIntencao, $dataInicioCadastro, $dataFimCadastro, $centroCusto);
        
        return executarSQL($database, $sql);
    }

    /**
     * Get all item of intencao by intencaoSequencial and intencaoAno.
     *
     * @param int $intencaoSequencial            
     * @param int $intencaoAno            
     *
     * @return \DB_result if fail return \DB_error
     */
    public function getAllItemIntencao($intencaoSequencial, $intencaoAno)
    {
        $sql = ClaRegistroPrecoIntencaoSQL::sqlAllIntencaoItem($intencaoSequencial, $intencaoAno);
        
        $database = ClaDatabasePostgresql::getConexao();
        
        $res = &$database->getAll($sql, array(), DB_FETCHMODE_OBJECT);
        
        ClaDatabasePostgresql::hasError($res);
        
        return $res;
    }

    /**
     * Get Itens resposta por intencao, ano e orgao.
     *
     * @param int $sequencialIntencao            
     * @param int $anoIntencao            
     * @param int $orgaoCodigo            
     *
     * @return \DB_pgsql or \DB_Error;
     */
    public function getItensRespostaByIntencaoAnoOrgao($sequencialIntencao, $anoIntencao, $orgaoCodigo)
    {
        $sql = ClaRegistroPrecoIntencaoSQL::sqlItensRespostaByIntencaoAnoOrgao($sequencialIntencao, $anoIntencao, $orgaoCodigo);
        
        $database = ClaDatabasePostgresql::getConexao();
        
        $res = &$database->getAll($sql, array(), DB_FETCHMODE_OBJECT);
        
        ClaDatabasePostgresql::hasError($res);
        
        return $res;
    }
}
