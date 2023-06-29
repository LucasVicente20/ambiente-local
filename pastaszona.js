/*
-------------------------------------------------------------------------
 Portal da DGCO
 Programa: pastaszona.js
 Autor:    Ronaldo Castro / Wagner Barros
 Data:     --/--/2006
 Objetivo: Janelas Pop-up da página /institucional/ConsOrganograma.php
 Alterado: Carlos Abreu
 Data:     11/09/2006 - Retirada de href para RotEmailPadrao.php
-------------------------------------------------------------------------
*/

/***********************************************
* Pop-it menu- © Dynamic Drive (www.dynamicdrive.com)
* This notice MUST stay intact for legal use
* Visit http://www.dynamicdrive.com/ for full source code
***********************************************/

var defaultMenuWidth="260px" //set default menu width.

var linkset=new Array()
//SPECIFY MENU SETS AND THEIR LINKS. FOLLOW SYNTAX LAID OUT

linkset[0]='<font class="titulo">Diretoria de Licitações e Compras - DLC</font>'
linkset[0]+='<hr>' //Separador
linkset[0]+='<b>RESPONSÁVEL</b><br>'
linkset[0]+='Nome: Hélio Max de Carvalho Figueredo <br>'
linkset[0]+='Fone: 3232-8374 <br>'
linkset[0]+='Email:heliomax@recife.pe.gov.br'

linkset[1]='<font class="titulo">Assistencia Técnica</font>'
linkset[1]+='<hr>' //Optional Separator
linkset[1]+='<b>Assistente Técnico  Executivo </b><br>'
linkset[1]+='Nome: André Bruno  de Oliveira Barros <br>'
linkset[1]+='Fone: 3232-8374 <br>'
linkset[1]+='Email: andre.barros@recife.pe.gov.br'

linkset[2]='<font class="titulo">Assistencia Técnica</font>'
linkset[2]+='<hr>' //Optional Separator
linkset[2]+='<b>Assistente Técnico Jurídico </b><br>'
linkset[2]+='Nome: Vesta Pires Magalhães Filha <br>'
linkset[2]+='Email: vesta@recife.pe.gov.br'

linkset[3]='<font class="titulo">Assistencia Técnica</font>'
linkset[3]+='<hr>' //Optional Separator
linkset[3]+='<b>Assistente Técnico de Análise de Processos e Preços </b><br>'
linkset[3]+='Nome: Giovanna de Lima Grangeiro <br>'
linkset[3]+='Email: giovanna@recife.pe.gov.br'

linkset[4]='<font class="titulo">Assistência Técnica II</font>'
linkset[4]+='<hr>' //Optional Separator

linkset[5]='<font class="titulo">Supervisores</font>'
linkset[5]+='<hr>' //Optional Separator
linkset[5]+='<b>Supervisão de Credenciamento de Fornecedores</b><br>'
linkset[5]+='Nome: Heli Liz Machado <br>'
linkset[5]+='Fone: 3232-8275 <br>'
linkset[5]+='<b>Supervisão de Apoio às Licitações</b><br>'
linkset[5]+='Nome: Silvio Homero Francisco da Silva <br>'
linkset[5]+='Fone: 3232-8235'

linkset[6]=' <font class="titulo">Comissão Permanente de Licitação de Serviços - CPLS </font>'
linkset[6]+='<hr>' //Optional Separator
linkset[6]+='<b>Assessor Técnico 1 </b><br>'
linkset[6]+='Nome: Yoneide Bezerra do Espirito Santo <br>'
linkset[6]+='Fone: 3232-8577 <br>'
linkset[6]+='Email: yoneide@recife.pe.gov.br'

linkset[7]=' <font class="titulo">Comissão Permanente de Licitação de Matériais - CPLM </font>'
linkset[7]+='<hr>' //Optional Separator
linkset[7]+='<b>Assessor Técnico 1 </b><br>'
linkset[7]+='Nome: Virgínia Maria Ferraz de Oliveira <br>'
linkset[7]+='Fone: 3232-8698<br>'
linkset[7]+='Email: virginiam@recife.pe.gov.br'

linkset[8]=' <font class="titulo">Comissão Permanente de Licitação de Obras e Serviços de Engenharia - CPLOSE </font>'
linkset[8]+='<hr>' //Optional Separator
linkset[8]+='<b>Assessor Técnico 1</b><br>'
linkset[8]+='Nome: Antonio de Pádua Souza Mendes Cruz<br>'
linkset[8]+='Fone: 3232-8459<br>'
linkset[8]+='Email: apadua@recife.pe.gov.br'

linkset[9]=' <font class="titulo">Comissão Permanente de Licitação de Educação - CPLE</font>'
linkset[9]+='<hr>' //Optional Separator
linkset[9]+='<b>Assessor Técnico 1</b><br>'
linkset[9]+='Nome: Tiago Alves Muniz<br>'
linkset[9]+='Fone: 3232-8707<br>'
linkset[9]+='Email: tiagoalves@recife.pe.gov.br'

linkset[10]=' <font class="titulo">Comissão Permanente de Licitação de Saúde - CPLSA </font>'
linkset[10]+='<hr>' //Optional Separator
linkset[10]+='<b>Assessor Técnico 1</b><br>'
linkset[10]+='Nome: Edmar Alves Duarte Cruz<br>'
linkset[10]+='Fone: 3232-8471<br>'
linkset[10]+='Email: edmar@recife.pe.gov.br'

linkset[11]=' <font class="titulo">Sub-Comissão de Saúde</font>'
linkset[11]+='<hr>' //Optional Separator
linkset[11]+='<b>2ª Pregoeira</b><br>'
linkset[11]+='Nome:  Célia Lúcia Alencar<br>'

linkset[12]=' <font class="titulo">Gerencia de Planejamento - GP </font>'
linkset[12]+='<hr>' //Optional Separator
linkset[12]+='<b>Gerente de Planejamento</b><br>'
linkset[12]+='Nome:  Eduardo Alcântara de Siqueira<br>'
linkset[12]+='Fone: 3232-8216<br>'
linkset[12]+='Email: eduardo.alcantara@recife.pe.gov.br'

linkset[13]=' <font class="titulo">Gerencia de Relações Comerciais - GRC </font>'
linkset[13]+='<hr>' //Optional Separator
linkset[13]+='<b>Gerente de Relações Comerciais</b><br>'
linkset[13]+='Nome: Fernando Vieira da Costa <br>'
linkset[13]+='Fone: 3232-8229<br>'
linkset[13]+='Email: fernandodurand@recife.pe.gov.br'

linkset[14]=' <font class="titulo">Gerencia de Suporte a Licitações e Contratos - GSLC  </font>'
linkset[14]+='<hr>' //Optional Separator
linkset[14]+='<b>Gerente de Suporte a Licitações e Contratos</b><br>'
linkset[14]+='Name: Américo Leite Júnior<br>'
linkset[14]+='Fone: 3232-8275<br>'
linkset[14]+='Email: americo@recife.pe.gov.br'

linkset[15]=' <font class="titulo">Gerencia de Logistica e Materiais - GLM </font>'
linkset[15]+='<hr>' //Optional Separator
linkset[15]+='<b>Gerente de Logística de Materiais</b><br>'
linkset[15]+='Nome: Thames Oliveira do Nascimento<br>'
linkset[15]+='Fone: 3232-6074<br>'
linkset[15]+='Email: thames@recife.pe.gov.br'

linkset[16]=' <font class="titulo">Gerência de Serviços de Análise Econôm.-financ. de Revisões Contratuais  - GSRC </font>'
linkset[16]+='<hr>' //Optional Separator
linkset[16]+='<b>Gerente de Análise Econôm.-financ. de Revisões Contratuais </b><br>'
linkset[16]+='Nome: Gustavo José do Nascimento Guimarães <br>'
linkset[16]+='Fone: 3232-8374 <br>'
linkset[16]+='Email: gustavo.guimaraes@recife.pe.gov.br'

linkset[17]=' <font class="titulo">Gerência de Serviços de Planejamento de Compras - GSPC </font>'
linkset[17]+='<hr>' //Optional Separator
linkset[17]+='<b>Gerente de Serviços de Planejamento de Compras</b><br>'
linkset[17]+='Nome: Tatianne Ulisses Sampaio Cabral<br>'
linkset[17]+='Fone: 3232-8374<br>'
linkset[17]+='Email: tatianne@recife.pe.gov.br'

linkset[18]=' <font class="titulo">GRS - Supervisor 2  </font>'
linkset[18]+='<hr>' //Optional Separator
linkset[18]+=''

linkset[19]=' <font class="titulo">Gerência de Serviços de Supervisão às Licitações - GSSL  </font>'
linkset[19]+='<hr>' //Optional Separator
linkset[19]+='<b>Gerente de Suporte às Licitações</b><br>'
linkset[19]+='Nome: José Claudio de Oliveira<br>'
linkset[19]+='Fone: 3232-8235<br>'

linkset[20]=' <font class="titulo">Gerência Serviços de Credenciamento de Fornecedores - GSCF </font>'
linkset[20]+='<hr>' //Optional Separator
linkset[20]+='<b>Gerente de Credenciamento de Fornecedores </b><br>'
linkset[20]+='Nome: José Raimundo da Costa Neto <br>' 
linkset[20]+='Fone: 3232-8275 <br>'
linkset[20]+='Email: joseraimundo@recife.pe.gov.br'

linkset[21]=' <font class="titulo">Gerência de Serviços de Monitoramento de Contratos - GSMC  </font>'
linkset[21]+='<hr>' //Optional Separator
linkset[21]+='<b>Gerente de Monitoramento de Contratos </b><br>'
linkset[21]+='Nome: Daniele Henriques Símplicio <br>'
linkset[21]+='Fone: 3232-8235 <br>'
linkset[21]+='Email: daniele@recife.pe.gov.br'

linkset[22]=' <font class="titulo">Gerência de Serviços de Monitoramento de Estoques - GSME </font>'
linkset[22]+='<hr>' //Optional Separator
linkset[22]+='Nome: Paulo Bartolomeu Toscano Fernandes <br>'


linkset[23]=' <font class="titulo">Gerência de Serviços de Recebimento e Expedição de Materiais - GSREM </font>'
linkset[23]+='<hr>' //Optional Separator
linkset[23]+='<b>Gerente de Recebimento e Expedição de Materiais</b><br>'
linkset[23]+='Nome: Jair Ferreira da Silva<br>'
linkset[23]+='Fone: 3232-6074<br>'
linkset[23]+='Email: jairsefin@recife.pe.gov.br'

linkset[24]=' <font class="titulo">Gerência de Serviços de Controle de Compras - GSCC </font>'
linkset[24]+='<hr>' //Optional Separator
linkset[24]+='<b>Gerente de Controle de Compras</b><br>'
linkset[24]+='Nome: Juliana Leite Lira<br>'
linkset[24]+='Fone: 3232-8229<br>'
linkset[24]+='Email: julianna@recife.pe.gov.br '

linkset[25]=' <font class="titulo">Gerência de Serviços de Especificações de Bens e Serviços - GSEB </font>'
linkset[25]+='<hr>' //Optional Separator
linkset[25]+='<b>Gerente de Especificações de Bens e Serviços </b><br>'
linkset[25]+='Nome: Cybelle Maria de Lima Lacerda'
linkset[25]+='Fone: 3232-8229<br>'
linkset[25]+='Email: Cybellelacerda@recife.pe.gov.br'

linkset[26]=' <font class="titulo">Gerência de Serviços de Cadastro e Controle de Preços - GSCCP </font>'
linkset[26]+='<hr>' //Optional Separator
linkset[26]+='<b>Gerente de Cadastro e Controle de Preços </b><br>'
linkset[26]+='Nome: Fernando Henrique de Lima Durand <br>'
linkset[26]+='Fone: 3232-8374'

linkset[27]=' <font class="titulo">Gerência Serviços de Cotações - GSC </font>'
linkset[27]+='<hr>' //Optional Separator
linkset[27]+='<b>Gerente de Cotações </b><br>'
linkset[27]+='Nome: Dilza Maria dos Santos <br>'
linkset[27]+='Fone: 3232-8374 <br>'
linkset[27]+='Email: dilza@recife.pe.gov.br'

linkset[28]=' <font class="titulo">Supervisor 2 </font>'
linkset[28]+='<hr>' //Optional Separator

////No need to edit beyond here

var ie5=document.all && !window.opera
var ns6=document.getElementById

if (ie5||ns6)
document.write('<div id="popitmenu" onMouseover="clearhidemenu();" onMouseout="dynamichide(event)"></div>')

function iecompattest(){
return (document.compatMode && document.compatMode.indexOf("CSS")!=-1)? document.documentElement : document.body
}

function showmenu(e, which, optWidth){
if (!document.all&&!document.getElementById)
return
clearhidemenu()
menuobj=ie5? document.all.popitmenu : document.getElementById("popitmenu")
menuobj.innerHTML=which
menuobj.style.width=(typeof optWidth!="undefined")? optWidth : defaultMenuWidth
menuobj.contentwidth=menuobj.offsetWidth
menuobj.contentheight=menuobj.offsetHeight
eventX=ie5? event.clientX : e.clientX
eventY=ie5? event.clientY : e.clientY
//Find out how close the mouse is to the corner of the window
var rightedge=ie5? iecompattest().clientWidth-eventX : window.innerWidth-eventX
var bottomedge=ie5? iecompattest().clientHeight-eventY : window.innerHeight-eventY
//if the horizontal distance isn't enough to accomodate the width of the context menu
if (rightedge<menuobj.contentwidth)
//move the horizontal position of the menu to the left by it's width
menuobj.style.left=ie5? iecompattest().scrollLeft+eventX-menuobj.contentwidth+"px" : window.pageXOffset+eventX-menuobj.contentwidth+"px"
else
//position the horizontal position of the menu where the mouse was clicked
menuobj.style.left=ie5? iecompattest().scrollLeft+eventX+"px" : window.pageXOffset+eventX+"px"
//same concept with the vertical position
if (bottomedge<menuobj.contentheight)
menuobj.style.top=ie5? iecompattest().scrollTop+eventY-menuobj.contentheight+"px" : window.pageYOffset+eventY-menuobj.contentheight+"px"
else
menuobj.style.top=ie5? iecompattest().scrollTop+event.clientY+"px" : window.pageYOffset+eventY+"px"
menuobj.style.visibility="visible"
return false
}

function contains_ns6(a, b) {
//Determines if 1 element in contained in another- by Brainjar.com
while (b.parentNode)
if ((b = b.parentNode) == a)
return true;
return false;
}

function hidemenu(){
if (window.menuobj)
menuobj.style.visibility="hidden"
}

function dynamichide(e){
if (ie5&&!menuobj.contains(e.toElement))
hidemenu()
else if (ns6&&e.currentTarget!= e.relatedTarget&& !contains_ns6(e.currentTarget, e.relatedTarget))
hidemenu()
}

function delayhidemenu(){
delayhide=setTimeout("hidemenu()",500)
}

function clearhidemenu(){
if (window.delayhide)
clearTimeout(delayhide)
}

if (ie5||ns6)
document.onclick=hidemenu
