<?php
session_start();
$isLogged = true;
if(!isset($_SESSION["login"]) || !isset($_SESSION["senha"])) {
    // Usuário não logado! Redireciona para a página de login
    //header("location: ../../view/index/index.php");
    $isLogged = false;
}
include_once('../../includes/confdb.php');
include_once('../../includes/funcoes.php');
include_once('../../includes/calcularValorProduto.php');
$conn = connectdb();
$_SESSION["xAcessoCompraRapida"] = 'PRODUCTS';

$pagina = 1;
$itensPag = 16;
$temItens = 'N';
$vpg = 1;


if($_GET['pagina']) {

    if(!empty($_SESSION['vpg'])) {
        $itensPag = $_SESSION['vpg'] * 16;
        unset($_SESSION["vpg"]);
    } else{
        $itensPag = $_GET['pagina'] * 16;
    }
}

$vpg = $itensPag / 16; //numero da pagina para fazer o retorno da product-detail carregando a quantidade certa de produtos


$grupo = '';
if ($_GET['grupo'] != '')
    $grupo = $_GET['grupo'];

$filtroPesquisa = filter_var($_GET['filtro'], FILTER_SANITIZE_STRING);
$checarGrupo = false;

$busca = '';
if ($filtroPesquisa) {

    $filtroPesquisa = trim($filtroPesquisa);
    $filtroPesquisa = str_replace(' ', '%', $filtroPesquisa);
    $filtroPesquisa = str_replace('.', '', $filtroPesquisa);
//    $filtroPesquisa = '%'.$filtroPesquisa.'%';

    
    $busca = ' AND ( replace(upper("Referencia"."desReferencia"), \'.\', \'\') like upper('."'%".$filtroPesquisa."%'".')
            or replace(upper("Referencia"."palavraChave"), \'.\', \'\') like upper('."'%".$filtroPesquisa."%'".')
            or replace(upper("Referencia"."codReferencia"), \'.\', \'\') like upper('."'%".$filtroPesquisa."%'".')
            or exists (select 1 from "ReferenciaProduto" rpi
                        join "DetalheItem" dii ON dii."codDetalheItem" = rpi."codCor"
                        where rpi."codReferencia" = "Referencia"."codReferencia" and replace(dii."desDetalheItem", \'.\', \'\') LIKE upper('."'%".$filtroPesquisa."%'".')) 
            )';

    //verifica se o termo de pesquisa é so numero, para evidenciar se a pesquisa foi por uma referencia ou cod produto
    $auxFiltro = str_replace('%', '', $filtroPesquisa);
    $checarGrupo = (is_numeric($auxFiltro)) ? true : false;

    /*
    $busca = ' AND CONCAT(
                    upper("Referencia"."desReferencia"), \' \',
                    upper("Referencia"."palavraChave"), \' \',
                    upper("Referencia"."codReferencia"), \' \',
                    upper((select dii."desDetalheItem" from "ReferenciaProduto" rpi
                            join "DetalheItem" dii ON dii."codDetalheItem" = rpi."codCor"
                            where rpi."codReferencia" = "Referencia"."codReferencia" limit 1))
                ) LIKE \''.$filtroPesquisa.'\'';*/

    //$grupo = '01';
}



$wish = '';
if ($_GET['wish'])
    $wish = $_GET['wish'];

if ($wish == 'S' and $isLogged){
    $wish = ' and exists (select 1 from "PedidoVendaItemTempWishList" where "codCliente" = '.$_SESSION["codCliente"].' and "codRepresentante" = '.$_SESSION["codRepresentante"].' and "codReferencia" = "Referencia"."codReferencia" and "codClone" = '.$_SESSION["codClone"].' and login = \''.$_SESSION["login"].'\') ';
} else if ($wish == 'O'){
    $wish = ' and exists (select 1 from "ReferenciaProduto" where "codReferencia" = "Referencia"."codReferencia" and "ReferenciaProduto"."xPromocional" = \'O\' and "ReferenciaProduto"."xVendaDisponivel" = \'S\' and "ReferenciaProduto"."qtdEstoque" > 0) ';
} else {

    if ($grupo != '')
        $wish = ' and not exists (select 1 from "ReferenciaProduto" where "codReferencia" = "Referencia"."codReferencia" and "ReferenciaProduto"."xPromocional" = \'O\') ';
}


//--- filtros
$filtroSQL = '';

$ordem = '';
if ($filtro == 'M') {
    $ordem = '"desCompleta" desc, ';
} else if ($filtro == 'N') {
    $ordem = '"desCompleta" asc, ';
} else if ($filtro == 'PRO') {
    $filtroSQL = ' AND "RP"."xPromocional" in ('."'A', 'S', 'P'".')';
} else if ($filtro == 'LAN') {
    $filtroSQL = ' AND "RP"."xPromocional" in ('."'A', 'L'".')';
} else {
    $filtroSQL = ' AND (upper("Referencia"."desReferencia") like upper('."'%".$filtro."%'".') or upper("RP"."desProduto") like upper('."'%".$filtro."%'".') or upper("Referencia"."palavraChave") like upper('."'%".$filtro."%'".') )';
}
//--- fim filtros
//and exists (Select 1 from "ProdutoFotoOnline" where "ProdutoFotoOnline"."codProduto" = "Referencia"."codReferencia")


if($isLogged) {


    $sql = 'SELECT "Referencia"."codReferencia" as ref, "Referencia"."desReferencia" as "desCompleta", "Referencia"."desGrade",
        (Select "ProdutoFotoOnline"."nomArquivo" from "ProdutoFotoOnline" where "ProdutoFotoOnline"."codProduto" = "Referencia"."codReferencia" order by "ProdutoFotoOnline".seq limit 1) as "nomArquivo",
        "Referencia"."codGrupo", "Referencia"."descritivo" as "descritivo", "Referencia"."codUnidade",
        coalesce( (select "RP"."xPromocional" from "ReferenciaProduto" "RP" where "RP"."codReferencia" = "Referencia"."codReferencia" and "RP"."xPromocional" <> \'N\' limit 1) , \'N\' ) as "xPromocional"
        FROM "Referencia" WHERE 0 = 0
        '.$wish.'
        AND EXISTS(SELECT 1 FROM "ReferenciaProduto" "RP" WHERE "RP"."codReferencia" = "Referencia"."codReferencia"
        AND EXISTS(SELECT 1 FROM "TabelaPrecoReferencia" "TPR" WHERE "TPR"."codTabelaPreco" = '.$_SESSION["codTabelaPreco"].'
        AND "TPR"."codProduto" = "RP"."codProduto" AND "TPR"."codReferencia" = "RP"."codReferencia" AND "TPR"."codSubgrade" = \'\') '.$filtroSQL.'  )
        AND ("Referencia"."codGrupo" like \''.$grupo.'%\' 
		or exists (select 1 from "ReferenciaGrupoProduto" rgp where rgp."codReferencia" = "Referencia"."codReferencia" and rgp."codGrupoProduto" like \''.$grupo.'%\') ) 
        '.$busca.'
        AND (SELECT SUM("qtdEstoque" + (CASE WHEN "xVendaDisponivel" = \'S\' THEN 0 ELSE 1 END))
        FROM "ReferenciaProduto" WHERE "ReferenciaProduto"."codReferencia" = "Referencia"."codReferencia") > 0 ORDER BY  '.$ordem.' "Referencia"."codReferencia" limit '.$itensPag.' OFFSET ('.$pagina.' - 1) * '.$itensPag;

} else {
    
    $sql = 'SELECT "Referencia"."codReferencia" as ref, "Referencia"."desReferencia" as "desCompleta", "Referencia"."desGrade",
        (Select "ProdutoFotoOnline"."nomArquivo" from "ProdutoFotoOnline" where "ProdutoFotoOnline"."codProduto" = "Referencia"."codReferencia" order by "ProdutoFotoOnline".seq limit 1) as "nomArquivo",
        "Referencia"."codGrupo", "Referencia"."descritivo" as "descritivo", "Referencia"."codUnidade",
        coalesce( (select "RP"."xPromocional" from "ReferenciaProduto" "RP" where "RP"."codReferencia" = "Referencia"."codReferencia" and "RP"."xPromocional" <> \'N\' limit 1) , \'N\' ) as "xPromocional"
        FROM "Referencia" WHERE 0 = 0
        '.$wish.'
        AND EXISTS(SELECT 1 FROM "ReferenciaProduto" "RP" WHERE "RP"."codReferencia" = "Referencia"."codReferencia"
        AND EXISTS(SELECT 1 FROM "TabelaPrecoReferencia" "TPR" WHERE "TPR"."codTabelaPreco" = '.$_SESSION["codTabelaPreco"].'
        AND "TPR"."codProduto" = "RP"."codProduto" AND "TPR"."codReferencia" = "RP"."codReferencia" AND "TPR"."codSubgrade" = \'\') '.$filtroSQL.'  )
        AND ("Referencia"."codGrupo" like \''.$grupo.'%\' 
		or exists (select 1 from "ReferenciaGrupoProduto" rgp where rgp."codReferencia" = "Referencia"."codReferencia" and rgp."codGrupoProduto" like \''.$grupo.'%\') ) 
        '.$busca.'
        AND (SELECT SUM("qtdEstoque" + (CASE WHEN "xVendaDisponivel" = \'S\' THEN 0 ELSE 1 END))
        FROM "ReferenciaProduto" WHERE "ReferenciaProduto"."codReferencia" = "Referencia"."codReferencia") > 0 ORDER BY  '.$ordem.' "Referencia"."codReferencia" limit '.$itensPag.' OFFSET ('.$pagina.' - 1) * '.$itensPag;

}

$qry = pg_query($conn, $sql) or die('Erro ao obter dados da tabela Produto - '.$sql);
$numRows = pg_num_rows($qry);



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

    if($isLogged) {



    }


    /*if ($precoMin == $precoMax){

        $calcPrecoMax = calcularValorProduto($paramPrecoMax['codReferencia'], $paramPrecoMax['codProduto']);
        $preco = '<span> '.number_format($calcPrecoMax["valPrecoFinal"], 2, ',', ' ').'</span>';

    } else {

        $calcPrecoMax = calcularValorProduto($paramPrecoMax['codReferencia'], $paramPrecoMax['codProduto']);
        $calcPrecoMin = calcularValorProduto($paramPrecoMin['codReferencia'], $paramPrecoMin['codProduto']);
        $preco = '<span> '.number_format($calcPrecoMin["valPrecoFinal"], 2, ',', ' ').'</span> - <span>R&#36; '.number_format($calcPrecoMax["valPrecoFinal"], 2, ',', ' ').'</span>';
    }
*/
	$strPromo = '';
	if ($strTipo != ''){
		$strPromo = '<div class="promotion"><span class="new-product"> '.$strTipo.'</span></div>';
	}

    if ($row['wish'] == 1){
        $cor = 'red';
    } else{
        $cor = 'gray';
    }


    if($isLogged) {


        $precos = calcularValorProduto($conn, $row['ref']);
        $precoMin = $precos['precoMin'];
        $precoMax = $precos['precoMax'];

        $strTipo = '';
        if ($row['xPromocional'] == 'A') {
            $strTipo = 'Promo&ccedil;&atilde;o - Lan&ccedil;amento';
        } else if ($row['xPromocional'] == 'S' or $row['xPromocional'] == 'P') {
            $strTipo = 'Promo&ccedil;&atilde;o';
        } else if ($row['xPromocional'] == 'L') {
            $strTipo = 'Lan&ccedil;amento';
        }

        $temItens = 'S';

        if ($row['nomArquivo']) {
            if (substr($row['nomArquivo'], 0,4) == 'http'){
                $imagem = $row['nomArquivo'];
               
            } else {
                /*
                if(!file_exists(substr($_SERVER['DOCUMENT_ROOT'],0,strpos($_SERVER['DOCUMENT_ROOT'],'applications')).'docroot/thumb/'.$row['nomArquivo'])){
                    if(file_exists(substr($_SERVER['DOCUMENT_ROOT'],0,strpos($_SERVER['DOCUMENT_ROOT'],'applications')).'docroot/'.$row['nomArquivo'])){

                        make_thumb(substr($_SERVER['DOCUMENT_ROOT'],0,strpos($_SERVER['DOCUMENT_ROOT'],'applications')).'docroot/'.$row['nomArquivo'],
                            substr($_SERVER['DOCUMENT_ROOT'],0,strpos($_SERVER['DOCUMENT_ROOT'],'applications')).'docroot/thumb/'.$row['nomArquivo'],
                            400);
                    }
                }
                if(file_exists(substr($_SERVER['DOCUMENT_ROOT'],0,strpos($_SERVER['DOCUMENT_ROOT'],'applications')).'docroot/thumb/'.$row['nomArquivo'])){
                    $imagem = '../../../../../../thumb/'.$row['nomArquivo'];
                } else {
                    // $imagem = '../../../../../../noPicture.png';
                    $imagem = '../../images/nofoto.png';
                }
                */
                $imagem = '../../fotos-produtos/thumb/'.$row['nomArquivo'];
                if(!file_exists($imagem)){
                    $imagem = '../../images/nofoto.png';
                }
            }


        } else {
            $imagem = '../../images/nofoto.png';
            // $imagem = '../../../../../../noPicture.png';
        }

        
        $precoMin = number_format($precoMin, 2, ',', ' ');
        $precoMax = number_format($precoMax, 2, ',', ' ');

        if ($precoMin == $precoMax){
            $preco = '<span>R&#36; '.$precoMin.'</span>';
        } else {
            $preco = '<span>R&#36; '.$precoMin.'</span> - <span>R&#36; '.$precoMax.'</span>';
        }


        $strWish = '<a class="add-fav tooltipHere" data-toggle="tooltip" data-original-title="Adicionar a lista de desejo"
                            data-placement="left" style="background-color: white;" onclick="gravawish('."'".$row['ref']."'".')">
                                <i class="glyphicon glyphicon-heart" style="color: '.$cor.';" id="'.$row['ref'].'" ></i>
                            </a>';
        /*retirado o botao do compra rapida
        <div class="quickview">
            <a title="Quick View" class="btn btn-xs btn-quickview" onclick="callmodal('."'".$row['ref']."'".', '."'".$row['desCompleta']."','".$row['desCompleta']."'".')"> Compra Rapida </a>
        </div>
        */

        echo '
            <div class="item col-lg-3 col-md-3 col-sm-4 col-xs-6">
                <a name="item'.$row['ref'].'" id="item'.$row['ref'].'"></a>
                <div class="product">
                    <div class="image">
                       
                        
                        <a href="../../view/product/product-details.php?referencia='.$row['ref'].'&vpg='.$vpg.'"><img src="'.$imagem.'" alt="'.utf8_encode(substr($row['desCompleta'],0,45)).'" class="img-responsive"></a>
                        '.$strPromo.'
                        '.$strWish.'
                        
                    </div>
                    <div class="cursor-hand goToProduct" url="../../view/product/product-details.php?referencia='.$row['ref'].'&vpg='.$vpg.'">
                        <div class="description"">
                            <h4 style="font-size: 15px !important">'.mb_substr($row['desCompleta'],0,200).'</h4>
                            <span class="size">'.mb_substr($row['desGrade'],0,100).' </span>
                        </div>
                        <div class="price-products" style="clear: both">'.$preco.'</div>
                    </div>
                </div>
            </div>';

    } else {


        if ($row['nomArquivo']) {

            if (substr($row['nomArquivo'], 0,4) == 'http'){
                $imagem = $row['nomArquivo'];
               
            } else {
                /*
                if(!file_exists(substr($_SERVER['DOCUMENT_ROOT'],0,strpos($_SERVER['DOCUMENT_ROOT'],'applications')).'docroot/thumb/'.$row['nomArquivo'])){
                    if(file_exists(substr($_SERVER['DOCUMENT_ROOT'],0,strpos($_SERVER['DOCUMENT_ROOT'],'applications')).'docroot/'.$row['nomArquivo'])){

                        make_thumb(substr($_SERVER['DOCUMENT_ROOT'],0,strpos($_SERVER['DOCUMENT_ROOT'],'applications')).'docroot/'.$row['nomArquivo'],
                            substr($_SERVER['DOCUMENT_ROOT'],0,strpos($_SERVER['DOCUMENT_ROOT'],'applications')).'docroot/thumb/'.$row['nomArquivo'],
                            400);
                    }
                }
                if(file_exists(substr($_SERVER['DOCUMENT_ROOT'],0,strpos($_SERVER['DOCUMENT_ROOT'],'applications')).'docroot/thumb/'.$row['nomArquivo'])){
                    $imagem = '../../../../../../thumb/'.$row['nomArquivo'];
                } else {
                    // $imagem = '../../../../../../noPicture.png';
                    $imagem = '../../images/nofoto.png';
                }
                */

                $imagem = '../../fotos-produtos/thumb/'.$row['nomArquivo'];
                if(!file_exists($imagem)){
                    $imagem = '../../images/nofoto.png';
                }

            }


        } else {
            $imagem = '../../images/nofoto.png';
            // $imagem = '../../../../../../noPicture.png';
        }

        $temItens = 'S';
        
        echo '<div class="item col-lg-3 col-md-3 col-sm-4 col-xs-6">
        <a name="item'.$row['ref'].'" id="item'.$row['ref'].'"></a>
        <div class="product">
        <div class="image">
             <a href="../../view/product/product-details.php?referencia='.$row['ref'].'&vpg='.$vpg.'">
                <img src="'.$imagem.'" alt="'.substr($row['desCompleta'],0,45).'">
            </a>
            '.$strPromo.'
        </div>
        <div class="description">
            <h4 style="font-size: 15px !important"><a href="../../view/product/product-details.php?referencia='.$row['ref'].'&vpg='.$vpg.'">'.mb_substr($row['desCompleta'],0,200).'</a></h4>

            <span class="size">'.substr($row['desGrade'],0,100).' </span></div>
        <div class="action-control btn-price-products"><a class="btn btn-primary"  href="../../view/product/product-details.php?referencia='.$row['ref'].'&vpg='.$vpg.'"> <span class="add2cart">consulte o preço</span> </a></div>
        
        </div>
        </div>';


    }

}

if ($temItens == 'N'){
    ?>
    <div class="row">
        <div class="col-md-12" style="padding-left: 30px; padding-right: 20px">
            <div class="alert alert-info" style="margin-bottom: 0px;">
                <h4 class="text-center" style="padding-bottom: 0px">Produto n&atilde;o encontrado</h4>               
            </div>
        </div>
    </div>
    <?php
}

//disconnectdb($conn);
?>


<?php

    //gera a model apenas se for em outlet
    if(isset($_GET['wish']) and $_GET['wish'] == 'O') {

        ?>
        <div class="modal fade" id="filtro-outlet-modal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog">
                <form action="../../view/index/index.php" method="get">
                    <input type="hidden" name="grupo" value="<?=$grupo?>">
                    <input type="hidden" name="wish" value="O">
                    <div class="modal-content">
                        <div class="col-sm-12">
                            <button aria-hidden="true" data-dismiss="modal" class="close" type="button">x</button>
                            <h2>Filtro Outlet</h2>
                            <h4>Selecione uma ou mais opções: (<small class="text-muted">As opções abaixo listam apenas o que tem disponível</small>)</h4>
                        </div>
                                    
                        <div class="col-md-6 col-xs-12">
                            <p><strong>Tamanho:</strong> </p>
                            <select name="filtrotamanho[]" class="form-control" style="height: 120px" multiple>
                                <?php
                                    //pega os tamanhos disponiveis em outlet
                                    $sqlTamanho = 'select distinct("codTamanho") as "codTamanho" from "ReferenciaProduto" where "xPromocional" = \'O\' and "qtdEstoque" > 0 order by "codTamanho" asc';
                                    $qryTamanho = pg_query($conn, $sqlTamanho) or die('N&atilde;o foi poss&iacute;vel obter os Tamanho.');
                                    while($resultTamanho = pg_fetch_assoc($qryTamanho)) {
                                        //checa se o item esta selecionado
                                        $selectedTamanho = '';
                                        if(isset($_GET['filtrotamanho'])) {
                                            foreach($_GET['filtrotamanho'] as $v) {
                                                if($v == $resultTamanho['codTamanho']) {
                                                    $selectedTamanho = 'selected';
                                                    break;
                                                }
                                            }

                                        }
                                        print '<option value="'.$resultTamanho['codTamanho'].'" '.$selectedTamanho.'>'.$resultTamanho['codTamanho'].'</option>';
                                    }
                                ?>
                            </select>
                        </div>   
                        <div class="col-md-6 col-xs-12">
                            <p><strong>Cor:</strong><p>
                            <select name="filtrocor[]" class="form-control" style="height: 120px" multiple>
                                <?php
                                    // //pega os tamanhos disponiveis em outlet
                                    // $sqlCor = 'select distinct(di."codDetalheItem"), di."desDetalheItem"
                                    //             from "ReferenciaProduto" rp 
                                    //             join "DetalheItem" di ON di."codDetalheItem" = rp."codCor"
                                    //             where rp."xPromocional" = \'O\' and rp."qtdEstoque" > 0 
                                    //             order by di."desDetalheItem" asc';
                                    // $qryCor = pg_query($conn, $sqlCor) or die('N&atilde;o foi poss&iacute;vel obter  Cor.');
                                    // while($resultCor = pg_fetch_assoc($qryCor)) {
                                    //     //checa se o item esta selecionado
                                    //     $selectedCor = '';
                                    //     if(isset($_GET['filtrocor'])) {
                                    //         foreach($_GET['filtrocor'] as $v) {
                                    //             if($v == $resultCor['codDetalheItem']) {
                                    //                 $selectedCor = 'selected';
                                    //                 break;
                                    //             }
                                    //         }

                                    //     }
                                    //     print '<option value="'.$resultCor['codDetalheItem'].'" '.$selectedCor.'>'.$resultCor['desDetalheItem'].'</option>';
                                    // }

                                    //pega os tamanhos disponiveis em outlet
                                    $sqlCor = 'select distinct(di."codAgrupamento")
                                    from "ReferenciaProduto" rp 
                                    join "DetalheItem" di ON di."codDetalheItem" = rp."codCor"
                                    where rp."xPromocional" = \'O\' and rp."qtdEstoque" > 0 and di."codAgrupamento" != \'\'
                                    order by di."codAgrupamento" asc';
                                    $qryCor = pg_query($conn, $sqlCor) or die('N&atilde;o foi poss&iacute;vel obter  Cor.');
                                    while($resultCor = pg_fetch_assoc($qryCor)) {
                                        //checa se o item esta selecionado
                                        $selectedCor = '';
                                        if(isset($_GET['filtrocor'])) {
                                            foreach($_GET['filtrocor'] as $v) {
                                                if($v == $resultCor['codAgrupamento']) {
                                                    $selectedCor = 'selected';
                                                    break;
                                                }
                                            }

                                        }
                                        print '<option value="'.$resultCor['codAgrupamento'].'" '.$selectedCor.'>'.$resultCor['codAgrupamento'].'</option>';
                                    }
                                ?>
                            </select>
                        </div>   
                        
                    </div>
                    <div style="clear:both; margin-bottom: 20px"></div>
                    <div class="modal-footer">
                        <small class="text-muted float-left hidden-xs" style="margin-top: 5px;">Pressione CTRL para selecionar mais de uma opção</small>
                        <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i> Pesquisar</button>
                    </div>
                </form>
            </div>
        </div>
        <?php
    }

?>




<script type="text/javascript">

    $(document).ready(function(){

        $('.goToProduct').on('click', function(e) {
            e.preventDefault();
            window.location.href = $(this).attr('url');
            //console.log($(this).attr('url'));
        });
    

        <?php
            
            //se nao houver mais itens para listar
            if($numRows < $itensPag) {

                ?>
                $('#btnMaisProdutos').hide();
                $('#btnVoltarTelaInicial').show();
                <?php

            //se tiver mias intens para listar
            } else {

                ?>
                $('#btnMaisProdutos').show();
                $('#btnVoltarTelaInicial').hide();
                <?php

            }

        ?>

    });

    function abrirFiltroOutletModal() {
        $('#filtro-outlet-modal').modal('show');
    }

    function gravawish(ref){
        if (document.getElementById(ref).style.color == "red"){
            document.getElementById(ref).style.color="gray";
            getContext('#mostra','../../ajax/index/wish.ajax.php?referencia='+ref+'&acao=d');
        } else {
            document.getElementById(ref).style.color="red";
            getContext('#mostra','../../ajax/index/wish.ajax.php?referencia='+ref+'&acao=i');
        }
    }


</script>

<div id="mostra"></div>
<!-- Le javascript
================================================== -->


<!-- include carousel slider plugin  -->

<!-- include custom script for only homepage  -->
<!-- include custom script for site  -->
