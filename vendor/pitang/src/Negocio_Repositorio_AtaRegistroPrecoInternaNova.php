<?php
// 220038--
/**
 * @author jfsi
 *
 */
class Negocio_Repositorio_AtaRegistroPrecoInternaNova
{
    /**
     * Nome da tabela no Schema
     *
     * @var string
     */
    const NOME_TABELA = 'sfpc.tbataregistroprecointernanova';

    /**
     *
     * @param integer $processo            
     * @param integer $orgao            
     * @param integer $ano            
     * @param integer $numeroAta            
     */
    public function consultarAtaPorChave($processo, $orgao, $ano, $numeroAta)
    {
        $sql = Dados_Sql_AtaRegistroPrecoNova::sqlAtaPorchave($processo, $orgao, $ano, $numeroAta);
        $resultado = ClaDatabasePostgresql::executarSQL($sql);
        
        ClaDatabasePostgresql::hasError($resultado);
        
        return $resultado;
    }

    /**
     *
     * @param Negocio_ValorObjeto_Cintrpsequ $cintrpsequ            
     * @param Negocio_ValorObjeto_Cintrpsano $cintrpsano            
     * @param Negocio_ValorObjeto_Corglicodi $corglicodi            
     */
    public function consultarLicitacaoAtaInterna(Negocio_ValorObjeto_Cintrpsequ $cintrpsequ, Negocio_ValorObjeto_Cintrpsano $cintrpsano, Negocio_ValorObjeto_Corglicodi $corglicodi)
    {
        $sql = Dados_Sql_AtaRegistroPrecoNova::sqlLicitacaoAtaInterna($cintrpsano->getValor(), $cintrpsequ->getValor(), $corglicodi->getValor());
        $resultado = ClaDatabasePostgresql::executarSQL($sql);
        
        ClaDatabasePostgresql::hasError($resultado);
        
        return $resultado;
    }
}
