//-------------------------------------------------------------------------
// Portal da DGCO
// Programa: CadSolicitacaoCompraIncluirManterExcluir.js
// Autor:    Ariston Cordeiro
// Data:     10/08/2011
// Objetivo: parte em javascript de CadSolicitacaoCompraIncluirManterExcluir.php
//-------------------------------------------------------------------------

TIPO_ITEM_MATERIAL = 1;
TIPO_ITEM_SERVICO = 2;

TIPO_COMPRA_LICITACAO = 2;


var nomeMaterialServico = new Array(); //array pra converter número do tipo de material para nome
nomeMaterialServico[TIPO_ITEM_MATERIAL] = 'Material';
nomeMaterialServico[TIPO_ITEM_SERVICO] = 'Servico';

TIPO_RESERVA_BLOQUEIO = 1;
TIPO_RESERVA_DOTACAO = 2;

var nomeReserva = new Array(); //array pra converter número do tipo de reserva orçamentária para nome
var nomeReservas = new Array(); //array pra converter número do tipo de reserva orçamentária para nome
nomeReserva[TIPO_RESERVA_BLOQUEIO] = 'Bloqueio';
nomeReserva[TIPO_RESERVA_DOTACAO] = 'Dotação Orçamentária';
nomeReservas[TIPO_RESERVA_BLOQUEIO] = 'Bloqueios';
nomeReservas[TIPO_RESERVA_DOTACAO] = 'Dotações Orçamentárias';

function iniciarBloqueios(){
	bloqueios = new Array();
}

iniciarBloqueios();

function enviar(valor){
	enviarBotao(formulario, valor);
}
function atualizar(valor){
	atualizarPagina(formulario, valor);
}
function AbreJanela(url,largura,altura){
	window.open(url,'paginadetalhe','status=no,scrollbars=yes,left=90,top=150,width='+largura+',height='+altura);
}

function AbreJanelaItem(url,largura,altura){
	window.open(url,'paginaitem','status=no,scrollbars=yes,left=90,top=150,width='+largura+',height='+altura);
}
function CaracteresJustificativa(valor){
	formulario.NCaracteresJustificativa.value = '' +  formulario.Justificativa.value.length;
}

// Atualiza o valor total de um material Material ou serviço, e recalcula o total de todos os itens
function AtualizarValorTotal(linha, tipoItem){
	materialServico = nomeMaterialServico[tipoItem];
	qtde = moeda2float(document.getElementById(materialServico+'Quantidade['+linha+']').value);
	valor = moeda2float(document.getElementById(materialServico+'ValorEstimado['+linha+']').value);
	totalItem = qtde * valor;
	document.getElementById(materialServico+'ValorTotal['+linha+']').innerHTML = float2moeda(totalItem);
	total =0;
	qtdeItens =0;
	if(materialServico == 'Servico'){
		qtdeItens = qtdeServicos;
	}else if(materialServico == 'Material'){
		qtdeItens = qtdeMateriais;
	}
	for( itr=0; itr< qtdeItens; itr++){
		total += moeda2float(document.getElementById(materialServico+'ValorTotal['+itr+']').innerHTML);
	}

	document.getElementById(materialServico+'Total').innerHTML = float2moeda(total);
}

// Atualiza o valor dos demais exercicios
function AtualizarDemaisExercicios(linha, tipoItem){
	if(campoExercicioExiste){
		materialServico = nomeMaterialServico[tipoItem];

		elementoTotalExercicio = document.getElementById(materialServico+'TotalExercicio['+linha+']');
		elementoTotalExercicioValor = document.getElementById(materialServico+'TotalExercicioValor['+linha+']');
		elementoDemaisExercicios = document.getElementById(materialServico+'TotalDemaisExercicios['+linha+']');

		elementoTotalExercicioValor.value = elementoTotalExercicio.value; //atualizar valor do campo hidden

		qtde = moeda2float(document.getElementById(materialServico+'Quantidade['+linha+']').value); // quantidade
		valor = moeda2float(document.getElementById(materialServico+'ValorEstimado['+linha+']').value); // valor estimado
		qtdeExercicio = moeda2float(document.getElementById(materialServico+'QuantidadeExercicioValor['+linha+']').value);
		valorExercicio = moeda2float(elementoTotalExercicioValor.value);
		totalItem = qtde * valor; // valor total
		//totalItemExercicio = qtdeExercicio * valor; // valor no exercício
		totalItemExercicio = valorExercicio; // valor no exercício
		demaisExercicios = totalItem - totalItemExercicio; // valor nos demais exercícios
		demaisExerciciosMoeda = float2moeda(demaisExercicios);
		/*if(demaisExercicios<0){
			demaisExercicios=0;
		}*/
		elementoDemaisExercicios.innerHTML = demaisExerciciosMoeda;
	}
}
function AtualizarQuantidadeExercicio(linha, tipoItem){
	if(campoExercicioExiste){
		materialServico = nomeMaterialServico[tipoItem];
		qtde = moeda2float(document.getElementById(materialServico+'Quantidade['+linha+']').value);
		valor = moeda2float(document.getElementById(materialServico+'ValorEstimado['+linha+']').value);
		campoQuantidadeExercicio = document.getElementById(materialServico+'QuantidadeExercicio['+linha+']');
		campoQuantidadeExercicioValor = document.getElementById(materialServico+'QuantidadeExercicioValor['+linha+']');
		if(qtde==1 && (qtdeMateriais + qtdeServicos) ==1 ){
			campoQuantidadeExercicio.disabled = true;
			campoQuantidadeExercicio.value = float2moeda(1);
			campoQuantidadeExercicioValor.value = float2moeda(1);
		}else{
			campoQuantidadeExercicio.disabled = false;
			campoQuantidadeExercicioValor.value = campoQuantidadeExercicio.value;
		}
		AtualizarValorExercicio(linha, tipoItem); //quantidade altera valor
		AtualizarDemaisExercicios(linha, tipoItem); //altera total
	}
}
function AtualizarValorExercicio(linha, tipoItem){
	if(campoExercicioExiste){
		materialServico = nomeMaterialServico[tipoItem];
		qtde = moeda2float(document.getElementById(materialServico+'Quantidade['+linha+']').value);
		valor = moeda2float(document.getElementById(materialServico+'ValorEstimado['+linha+']').value);
		qtdeExercicio =  moeda2float(document.getElementById(materialServico+'QuantidadeExercicioValor['+linha+']').value);
		campoTotalExercicio = document.getElementById(materialServico+'TotalExercicio['+linha+']');
		campoTotalExercicioValor = document.getElementById(materialServico+'TotalExercicioValor['+linha+']');
		if(qtde==1 && (qtdeMateriais + qtdeServicos) ==1 ){
			campoTotalExercicio.disabled = false;
			campoTotalExercicio.value = float2moeda(valor);
			campoTotalExercicioValor.value = float2moeda(valor);
		}else{
			totalExercicio = qtdeExercicio * valor;
			campoTotalExercicio.value = float2moeda(totalExercicio);
			campoTotalExercicioValor.value = float2moeda(totalExercicio);
			campoTotalExercicio.disabled = true;
		}
		AtualizarDemaisExercicios(linha, tipoItem); //altera total
	}
}
// Atualiza o valor de um campo de um item de material serviço (com o nome tipo materialQuantidade[1]),
// pelo valor guardado em um campo Valor (item HTML com nome tipo materialQuantidadeValor[1])
function AtualizarCampoValor(linha, nomeCampo, tipoItem){
	materialServico = nomeMaterialServico[tipoItem];
	campo = document.getElementById(materialServico+nomeCampo+'['+linha+']');
	campoValor = document.getElementById(materialServico+nomeCampo+'Valor['+linha+']');
	campoValor.value = campo.value;
}

function AtualizarFornecedorValor(linha, tipoItem){
	AtualizarCampoValor(linha, 'Fornecedor', tipoItem);
}

// Recupera os dados do fornecedor ou informa erro
function validaFornecedor(nomeCampoCpfCnpj,nomeCampoResposta, materialServico, tipoMaterialServico){
	cpfCnpj = limpaCPFCNPJ(document.getElementById(nomeCampoCpfCnpj).value);
	carregamentoDinamico("RotDadosFornecedor.php","CPFCNPJ="+cpfCnpj+"&materialServicoFornecido="+materialServico+"&tipoMaterialServico="+tipoMaterialServico, nomeCampoResposta);
	document.getElementById(nomeCampoCpfCnpj).value = formataCpfCnpj(cpfCnpj);
}

function RetirarDoc(Linha){
	RetirarDocs=Linha+"|";
}

//apaga campo fornecedor da SCC em todos itens de material/servico
function limparFornecedorNosItens(){
			document.getElementById('CnpjFornecedor').value="";
			document.getElementById('CnpjFornecedorNome').innerHTML="";

			qtdeItens = qtdeMateriais;
			for( var itr=0; itr< qtdeItens; itr++){
				document.getElementById('MaterialFornecedorNome['+itr+']').innerHTML = "";
				document.getElementById('MaterialFornecedor['+itr+']').value = "";
			}
			qtdeItens = qtdeServicos;
			for( var itr=0; itr< qtdeItens; itr++){
				document.getElementById('ServicoFornecedorNome['+itr+']').innerHTML = "";
				document.getElementById('ServicoFornecedor['+itr+']').value = "";
			}
}

//Inclui o fornecedor da SCC em todos itens de material/servico
function incluirFornecedorNosItens(){
	qtdeItens = qtdeMateriais;
	valor = document.getElementById('CnpjFornecedor').value;
	numero = removerCaracteresEspeciais(valor);
			nome = document.getElementById('CnpjFornecedorNome').innerHTML;
			for( var itr=0; itr< qtdeItens; itr++){
				document.getElementById('MaterialFornecedorNome['+itr+']').innerHTML = nome;
				document.getElementById('MaterialFornecedor['+itr+']').value = valor;
				document.getElementById('MaterialFornecedorValor['+itr+']').value = valor;
			}
			qtdeItens = qtdeServicos;
			for( var itr=0; itr< qtdeItens; itr++){
				document.getElementById('ServicoFornecedorNome['+itr+']').innerHTML = nome;
				document.getElementById('ServicoFornecedor['+itr+']').value = valor;
				document.getElementById('ServicoFornecedorValor['+itr+']').value = valor;
			}
}

// Muda de dotacao para bloqueio e vice-versa
function mudarBloqueioDotacao(tipoReserva){
	nomeTipoReserva = nomeReserva[tipoReserva];
	nomeTipoReservas = nomeReservas[tipoReserva];
	document.getElementById('BloqueioTitulo').innerHTML = nomeTipoReservas.toUpperCase();
	document.getElementById('BloqueioLabel').innerHTML = nomeTipoReserva;

	//document.getElementById('MaterialBloqueioTitulo').innerHTML = nomeTipoReserva.toUpperCase();
	//document.getElementById('ServicoBloqueioTitulo').innerHTML = nomeTipoReserva.toUpperCase();
	if(tipoReserva==TIPO_RESERVA_DOTACAO){
		$(".bloqueioDotacao").mask(MASCARA_DOTACAO, {placeholder:"_"});
	}else{
		$(".bloqueioDotacao").mask(MASCARA_BLOQUEIO, {placeholder:"_"});
	}
	tipoReservaAntigo = document.getElementById('TipoReservaOrcamentaria').value;
	document.getElementById('TipoReservaOrcamentaria').value = tipoReserva;
}
// Executado quando valor estimado é mudado
function onChangeValorEstimadoItem(linha, tipoItem){

	materialServico = nomeMaterialServico[tipoItem];
		if(materialServico=="Material"){
			valor = moeda2float(document.getElementById(materialServico+'ValorEstimado['+linha+']').value);
			trp = moeda2float(document.getElementById(materialServico+'Trp['+linha+']').value);

			if(valor>trp){
				if(!confirm("O valor estimado informado ultrapassa o valor TRP (Tabela Referencial de Preços). Deseja continuar?")){
					campo = document.getElementById('MaterialValorEstimado['+linha+']');
					campo.value = "";
  			    }
			}
		}
}
function onChangeItemQuantidade(linha, tipoItem){
	AtualizarValorTotal(linha, tipoItem);
	AtualizarQuantidadeExercicio(linha, tipoItem);
}
function onChangeItemValor(linha, tipoItem){
	AtualizarValorTotal(linha, tipoItem);
	AtualizarValorExercicio(linha, tipoItem);
}
function onChangeItemQuantidadeExercicio(linha, tipoItem){
	AtualizarCampoValor(linha, 'QuantidadeExercicio', tipoItem);
	AtualizarValorExercicio(linha, tipoItem);
}
function onChangeItemValorExercicio(linha, tipoItem){
	AtualizarCampoValor(linha, 'TotalExercicio', tipoItem);
	AtualizarDemaisExercicios(linha, tipoItem);
}
function isObrasStr(){
	retorna = 'false';
	materialServico= nomeMaterialServico[TIPO_ITEM_MATERIAL];
	qtdeItens = qtdeMateriais;
	for( var itr=0; itr< qtdeItens; itr++){
		isObras = document.getElementById(materialServico+'IsObras_'+itr).value;
		if(isObras=='true'){
			retorna = 'true';
		}
	}
	materialServico= nomeMaterialServico[TIPO_ITEM_SERVICO];
	qtdeItens = qtdeServicos;
	for( var itr=0; itr< qtdeItens; itr++){
		//alert(materialServico+'IsObras['+itr+']');
		isObras = document.getElementById(materialServico+'IsObras_'+itr).value;
		if(isObras=='true'){
			retorna = 'true';
		}
	}
	return retorna;
}
function checaLimiteDespesa(){
	tipoCompraCampo = document.getElementById('TipoCompra');
	administracaoCampo = document.getElementById('administracao');
	isConfirmado = true;
	if (tipoCompraCampo==null || administracaoCampo==null){
		//isConfirmado = true;
	}else{
		tipoCompra = tipoCompraCampo.value;
		administracao = administracaoCampo.value;
		isObras = isObrasStr();
		valorTotal = moeda2float(document.getElementById('MaterialTotal').innerHTML)+moeda2float(document.getElementById('ServicoTotal').innerHTML);
		if(tipoCompra  == TIPO_COMPRA_LICITACAO){
			// não checar para licitação
		}else if( limiteCompra == null){
			//isConfirmado = true;
		}else if (limiteCompra[tipoCompra] == null){
			//isConfirmado = true;
		}else if (limiteCompra[tipoCompra][administracao] == null){
			//isConfirmado = true;
		}else if (limiteCompra[tipoCompra][administracao][isObras] == null ){
			//isConfirmado = true;
		}else{
			limite = limiteCompra[tipoCompra][administracao][isObras];
			if(valorTotal>limite){
				isConfirmado = confirm("Valor da solicitação de compra ("+float2moeda(valorTotal)+") ultrapassa limite de compra ("+float2moeda(limite)+")"+". Deseja mesmo continuar?");
			}
		}
	}
	return isConfirmado;
}
function onButtonIncluir(){
	if(checaLimiteDespesa()){
		enviar('Incluir');
	}
}
function onButtonManter(){
	if(checaLimiteDespesa()){
		enviar('Manter');
	}
}
function onButtonVoltar(){
	enviar('Voltar');
}

function incluirBloqueio(){
	enviar('IncluirBloqueio');
}
function retirarBloqueio(){
	enviar('RetirarBloqueio');
}
