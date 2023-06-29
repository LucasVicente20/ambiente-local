<?php
include "../funcoes.php";
$db	    = Conexao();
$sql    = "SELECT EUSUPOMAIL FROM SFPC.TBUSUARIOPORTAL";
$result = $db->query($sql);
if( PEAR::isError($result) ){
   	ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
}else{
		?>
		<html>
		<table class="textonormal" border="0" width="100%">
		 	<?php
		 	while( $cols = $result->fetchRow() ){
		 			$Email = $cols[0].",";
			?>
			<tr>
				<td colspan="2" class="textonormal"><?php echo $Email; ?></td>
			</tr>
			<?php } ?>
		</table>
		</html>
<?php } ?>		
<?php $db->disconnect(); ?>
		
