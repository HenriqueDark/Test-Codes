<?php

	session_start(); 
	if(!isset($_SESSION["login"]) || !isset($_SESSION["senha"])) { 
		// Usuário não logado! Redireciona para a página de login 
		header("location: ../../view/index/index.php");
	}
	include_once('../../includes/confdb.php');	
	include_once('../../includes/funcoes.php');
	

	$pagina = 1;
    $itensPag = 20;

	if ($_GET['pagina'])
        $itensPag = $_GET['pagina'] * 20;

	$grupo = '01';
	if ($_GET['grupo'])
		$grupo = $_GET['grupo'];

	$nome = $_GET['nome'];
	$busca = '';
	if ($nome) {
		$busca = ' AND ( upper("Referencia"."desReferencia") like upper('."'%".$nome."%'".') 
		or upper("Referencia"."palavraChave") like upper('."'%".$nome."%'".') 
		or upper("Referencia"."codReferencia") like upper('."'%".$nome."%'".') )';
		
		//$grupo = '01';
	}
	
	//--- filtros
	$filtroSQL = '';
	$filtro = $_GET['filtro'];
	$ordem = '';
	if ($filtro == 'M') {
		$ordem = '"desCompleta" desc, ';
	} else if ($filtro == 'N') {
		$ordem = '"desCompleta" asc, ';
	} else if ($filtro == 'PRO') {
		$filtroSQL = ' AND "RP"."xPromocional" in ('."'A', 'S', 'P'".')';
	} else if ($filtro == 'LAN') {
		$filtroSQL = ' AND "RP"."xPromocional" in ('."'A', 'L'".')';
	}
	//--- fim filtros
	$conn = connectdb();
	$sql = 'SELECT "Referencia"."codReferencia" as ref, "Referencia"."desReferencia" as "desCompleta", "Referencia"."desGrade", 
	(Select "ProdutoFotoOnline"."nomArquivo" from "ProdutoFotoOnline" where "ProdutoFotoOnline"."codProduto" = "Referencia"."codReferencia" order by "ProdutoFotoOnline".seq limit 1) as "nomArquivo", 
	"Referencia"."codGrupo", "Referencia"."descritivo" as "descritivo", "Referencia"."codUnidade", 
	coalesce( (select "RP"."xPromocional" from "ReferenciaProduto" "RP" where "RP"."codReferencia" = "Referencia"."codReferencia" and "RP"."xPromocional" <> \'N\' limit 1) , \'N\' ) as "xPromocional"
	FROM "Referencia" WHERE 0 = 0 '; 
	$sql .= ' AND EXISTS(SELECT 1 FROM "TabelaPrecoReferencia" "TPR" WHERE "TPR"."codReferencia" = "Referencia"."codReferencia" AND "TPR"."codTabelaPreco" in ('.$_SESSION["codTabelaPreco"].')) 
	AND "Referencia"."codGrupo" like \''.$grupo.'%\' ';
	/*AND EXISTS(SELECT 1 FROM "ReferenciaProduto" "RP" WHERE "RP"."codReferencia" = "Referencia"."codReferencia" 
	AND EXISTS(SELECT 1 FROM "TabelaPrecoReferencia" "TPR" WHERE "TPR"."codTabelaPreco" = '.$_SESSION["codTabelaPreco"].' 
	AND "TPR"."codProduto" = "RP"."codProduto" AND "TPR"."codReferencia" = "RP"."codReferencia" AND "TPR"."codSubgrade" = \'\') '.$filtroSQL.'  )  
	AND "Referencia"."codGrupo" like \''.$grupo.'%\' '.$busca.' 
	AND (SELECT SUM("qtdEstoque" + (CASE WHEN "xVendaDisponivel" = \'S\' THEN 0 ELSE 1 END)) 
	FROM "ReferenciaProduto" WHERE "ReferenciaProduto"."codReferencia" = "Referencia"."codReferencia") > 0 */
	$sql .= ' ORDER BY  '.$ordem.' "Referencia"."codReferencia" limit '.$itensPag.' OFFSET ('.$pagina.' - 1) * '.$itensPag;

	$qry = pg_query($conn, $sql) or die('Erro ao obter dados da tabela Produto - '.$sql);
	$count = 1;
	while ($row = pg_fetch_assoc($qry)) {
			if ($count == 3){
				$tmp = 'last-grid';
				$count = 1;
			} else {
				$tmp = '';
				$count ++;
			}

			$precoMin = 0;
			$precoMax = 0;
			$sqlpreco = 'SELECT DISTINCT max("TPR"."preco"), min("TPR"."preco") FROM "ReferenciaDetalhe" "RD", "Detalhe" "D", "DetalheItem" "DI",
                        "ReferenciaProduto" "RP", "GradeTamanho" "GT", "GradeSubgradeTamanho" "GST", "TabelaPrecoReferencia" "TPR"
                        where "D"."codDetalhe" = "DI"."codDetalhe"
                        and "RD"."codDetalhe" = "DI"."codDetalhe"
                        and "RD"."codDetalheItem" = "DI"."codDetalheItem"
                        and "RD"."codReferencia" = '."'".$row['ref']."'".'
                        and "RD"."codDetalhe" = '."'CR'".'
                        and "RP"."codGrade" = "GT"."codGrade"
                        and "RP"."codTamanho" = "GT"."codTamanho"
                        and "TPR"."codReferencia" = "RP"."codReferencia"
                        and "GST"."codTamanho" = "GT"."codTamanho"
                        and "GST"."codSubgrade" = "TPR"."codSubgrade"
                        and "DI"."codDetalheItem" = "RP"."codCor"
                        and "RD"."codReferencia" = "RP"."codReferencia"
                        and "TPR"."codTabelaPreco" in ('.$_SESSION["codTabelaPreco"]. ')';

			$qrypreco = pg_query($conn, $sqlpreco) or die('N&atilde;o foi poss&iacute;vel obter os precos.');
			$resultpreco = pg_fetch_assoc($qrypreco);
			if ($resultpreco) { 
                $precoMin = str_replace(',', '.', $resultpreco['min']);
                $precoMax = str_replace(',', '.', $resultpreco['max']);
			}

			$strTipo = '';
			if ($row['xPromocional'] == 'A') {
				$strTipo = 'Promo&ccedil;&atilde;o - Lan&ccedil;amento';
			} else if ($row['xPromocional'] == 'S' or $row['xPromocional'] == 'P') {
				$strTipo = 'Promo&ccedil;&atilde;o';
			} else if ($row['xPromocional'] == 'L') {
				$strTipo = 'Lan&ccedil;amento';
			}
			
		    if ($row['nomArquivo']) {
		        $imagem = $row['nomArquivo'];
		    } else {
		        $imagem = '../../../public/images/logopro.jpg';
		    }			
			//<a href="#"><img width="172" height="172" src="assets/teste300/'.$row['nomArquivo'].'" title="product-name" /></a>
/*          SELECT DISTINCT "D"."codDetalhe", "D"."descricao" as "desDetalhe"
            FROM "ReferenciaDetalhe" "RD", "Detalhe" "D", "DetalheItem" "DI"
            where "D"."codDetalhe" = "DI"."codDetalhe" and "RD"."codDetalhe" = "DI"."codDetalhe" and "RD"."codDetalheItem" = "DI"."codDetalheItem" and "RD"."codReferencia" = '416000'
            ORDER BY "D"."codDetalhe" DESC

            SELECT DISTINCT "D"."descricao" as "desDetalhe", "DI"."desDetalheItem" as "desDetalheItem", "DI"."codDetalheItem",
            (SELECT "nomArquivo" FROM "DetalheItemFoto" "DIF" WHERE "DIF"."codDetalhe" = "DI"."codDetalhe" AND "DIF"."codDetalheItem" = "DI"."codDetalheItem"
            AND ("DIF"."codReferencia" = "RD"."codReferencia" OR "DIF"."codReferencia" = '')
            ORDER BY "DIF"."codReferencia" DESC LIMIT 1) AS "nomArquivo", '' as "xPromocional" FROM "ReferenciaDetalhe" "RD", "Detalhe" "D", "DetalheItem" "DI",
            "ReferenciaProduto" "RP", "GradeTamanho" "GT", "GradeSubgradeTamanho" "GST", "TabelaPrecoReferencia" "TPR" where "D"."codDetalhe" = "DI"."codDetalhe"
            and "RD"."codDetalhe" = "DI"."codDetalhe" and "RD"."codDetalheItem" = "DI"."codDetalheItem" and "RD"."codReferencia" = '416000' and "RD"."codDetalhe" = 'CR'
            and "RP"."codGrade" = "GT"."codGrade" and "RP"."codTamanho" = "GT"."codTamanho" and "TPR"."codReferencia" = "RP"."codReferencia"
            and "GST"."codTamanho" = "GT"."codTamanho" and "GST"."codSubgrade" = "TPR"."codSubgrade" and "DI"."codDetalheItem" = "RP"."codCor"
            and "RD"."codReferencia" = "RP"."codReferencia"


            SELECT  "RP"."xPromocional", "RP"."valCusto", "GST"."codGrade", "TPR"."codTabelaPreco", "TPR"."codReferencia", "TPR"."codUnidade", "TPR"."preco", "TPR"."perDescontoMaximo",
            "TPR"."codProduto" as "codProdutoTPR"
            FROM "ReferenciaProduto" "RP", "GradeTamanho" "GT", "GradeSubgradeTamanho" "GST", "TabelaPrecoReferencia" "TPR"
            WHERE "RP"."codGrade" = "GT"."codGrade" and "RP"."codTamanho" = "GT"."codTamanho" and "TPR"."codReferencia" = "RP"."codReferencia"
            and "GST"."codTamanho" = "GT"."codTamanho" and "GST"."codSubgrade" = "TPR"."codSubgrade" and "TPR"."codTabelaPreco" = 107
            and "RP"."codReferencia" = '416000' and "codCor" = 'L31' ORDER BY "GT"."numOrdem"

SELECT  max("TPR"."preco"), min("TPR"."preco")
FROM "ReferenciaProduto" "RP", "GradeTamanho" "GT", "GradeSubgradeTamanho" "GST", "TabelaPrecoReferencia" "TPR"
WHERE "RP"."codGrade" = "GT"."codGrade" and "RP"."codTamanho" = "GT"."codTamanho" and "TPR"."codReferencia" = "RP"."codReferencia"
and "GST"."codTamanho" = "GT"."codTamanho" and "GST"."codSubgrade" = "TPR"."codSubgrade" and "TPR"."codTabelaPreco" = 107
and "RP"."codReferencia" = '416000'
and "codCor" in
(
SELECT DISTINCT "DI"."codDetalheItem" FROM "ReferenciaDetalhe" "RD", "Detalhe" "D", "DetalheItem" "DI",
"ReferenciaProduto" "RP", "GradeTamanho" "GT", "GradeSubgradeTamanho" "GST", "TabelaPrecoReferencia" "TPR" where "D"."codDetalhe" = "DI"."codDetalhe"
and "RD"."codDetalhe" = "DI"."codDetalhe" and "RD"."codDetalheItem" = "DI"."codDetalheItem" and "RD"."codReferencia" = '416000' and "RD"."codDetalhe" = 'CR'
and "RP"."codGrade" = "GT"."codGrade" and "RP"."codTamanho" = "GT"."codTamanho" and "TPR"."codReferencia" = "RP"."codReferencia"
and "GST"."codTamanho" = "GT"."codTamanho" and "GST"."codSubgrade" = "TPR"."codSubgrade" and "DI"."codDetalheItem" = "RP"."codCor"
and "RD"."codReferencia" = "RP"."codReferencia"
)

*/

                if ($precoMin == $precoMax){
                    $preco = '<span>R&#36; '.$precoMin.'</span>';
                } else {
                    $preco = '<span>R&#36; '.$precoMin.'</span> - <span>R&#36; '.$precoMax.'</span>';
                }


                echo '<div class="item col-lg-3 col-md-3 col-sm-4 col-xs-6">
                    <div class="product">
                        <a class="add-fav tooltipHere" data-toggle="tooltip" data-original-title="Add to Wishlist"
                           data-placement="left">
                            <i class="glyphicon glyphicon-heart"></i>
                        </a>

                        <div class="image">
                            <div class="quickview">
                                <a title="Quick View" class="btn btn-xs  btn-quickview" onclick="callmodal('.$row['ref'].')"> Compra Rapida </a>
								   
                            </div>
                            <a href="../../view/index/product-details.php?referencia='.$row['ref'].'"><img src="'.$imagem.'" alt="'.substr($row['desCompleta'],0,40).'"
                                                                class="img-responsive"></a>

                            <div class="promotion"><span class="new-product"> '.$strTipo.'</span> <span
                                    class="discount">15% OFF</span></div>
                        </div>
                        <div class="description">
                            <h4><a href="../../view/index/product-details.php?referencia='.$row['ref'].'">'.substr($row['desCompleta'],0,40).'</a></h4>

                            <p>Referencia: '.$row['ref'].'</p>
                            <span class="size">'.substr($row['desGrade'],0,40).' </span></div>
                        <div class="price">'.$preco.'</div>
                        <div class="action-control"><a class="btn btn-primary"> <span class="add2cart"><i
                                class="glyphicon glyphicon-shopping-cart"> </i> Adicionar ao Carrinho </span> </a></div>
                    </div>
                </div>';

                //---- inicio modal ----//
                //---- inicio modal ----//
                //---- inicio modal ----//
                echo '<div class="modal fade" id="product-details-modal" tabindex="-1" role="dialog" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">';
									//---teste
									echo '<div id="carrega-modal">
										  </div>
                                    <div class="clear"></div>';
                                echo '</div>
                            </div>
                        </div>';
	}
	disconnectdb($conn);
?>



<!-- Le javascript
================================================== -->


<!-- include carousel slider plugin  -->

<!-- include custom script for only homepage  -->
<!-- include custom script for site  -->
<script src="../../assets/js/scriptteste.js"></script>

