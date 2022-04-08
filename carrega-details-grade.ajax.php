<script src="../../assets/js/scriptteste.js"></script>
    <tbody>
        <div class="container-fluid">
            <div class="row">
                <div class="panel-group" id="panel-detais">

                    <?php
                    session_start();
                    if(!isset($_SESSION["login"]) || !isset($_SESSION["senha"])) {
                        // Usuário não logado! Redireciona para a página de login
                        header("Location: ../../view/index/index.php");
                    }
                    include_once('../../includes/confdb.php');
                    include_once('../../includes/calcularValorProduto.php');
                    $conn = connectdb();

                    $referencia = $_GET['referencia'];
                    $indFin = 0;
                    $tabFin = 0;

                    //monta a array com as imagens do slide da coluna esquerda
                    //essa sequencia é para identificar
                    $sqlSlide = ' select
                                    distinct dif."nomArquivo" as "nomArquivo", di."desDetalheItem",
                                    (select rdao."nomArquivo" from "ProdutoFotoOnline" rdao where rdao."codProduto" = "ReferenciaProduto"."codReferencia" and rdao.seq = 1 limit 1) as destaque
                                    from
                                    "ReferenciaProduto"
                                    left outer join "DetalheItem" d on (
                                        "ReferenciaProduto"."codCor" = d."codDetalheItem"
                                    )
                                    left outer join "DetalheItemFotoOnline" dif on (
                                        dif."codDetalheItem" = d."codDetalheItem"
                                        and dif."codDetalhe" = d."codDetalhe"
                                        and dif."codReferencia" = "ReferenciaProduto"."codReferencia"
                                    )
                                    left outer join "DetalheItem" dI on (
                                        "ReferenciaProduto"."codCor" = dI."codDetalheItem"
                                    )
                                    where
                                    "ReferenciaProduto"."codReferencia" = \''.$referencia.'\' 
                                    order by
                                    di."desDetalheItem",
                                    dif."nomArquivo"';
                    $arrSlide = [];
                    $countSlide = 1; //começa em 1 e ja +1 pq o primeiro slide é imagem de destaque e para o countSlide ter o total de slides
                    $qrySlide = pg_query($conn, $sqlSlide) or die('Erro ao obter fotos');
                    while ($rowSlide = pg_fetch_assoc($qrySlide)) {
                        $countSlide++;
                        $arrSlide[$countSlide] = $rowSlide['nomArquivo'];
                    }


                    //$sqlCor  = 'select distinct "ReferenciaProduto"."codTamanho", "ReferenciaProduto"."qtdPeca" as "descTamanho" from "TabelaPrecoReferencia"
                    $sqlCor  = 'select distinct "ReferenciaProduto"."codTamanho", gt."descricaoTamanho"
                    from "TabelaPrecoReferencia"
                    join "TabelaPrecoRepresentante" on "TabelaPrecoRepresentante"."codTabelaPreco" = "TabelaPrecoReferencia"."codTabelaPreco"
                    left join "ReferenciaProduto" on ("ReferenciaProduto"."codProduto" = "TabelaPrecoReferencia"."codProduto" and "ReferenciaProduto"."codReferencia" = "TabelaPrecoReferencia"."codReferencia")
                    left join "Referencia" on "Referencia"."codReferencia" = "ReferenciaProduto"."codReferencia"
                    left outer join "DetalheItem" d on ("ReferenciaProduto"."codCor" = d."codDetalheItem")
                    left outer join "DetalheItemFotoOnline" dif on (dif."codDetalheItem" = d."codDetalheItem" and dif."codDetalhe" = d."codDetalhe" and dif."codReferencia" = "Referencia"."codReferencia")
                    left outer join "PedidoVendaItemTemp" pvt on (pvt."codCliente" = '.$_SESSION["codCliente"].' and pvt."codRepresentante" = '.$_SESSION["codRepresentante"].'
                                            and pvt."codClone" = '.$_SESSION["codClone"].'
                                            and pvt."codReferencia" = "Referencia"."codReferencia" and pvt."codProduto" = "ReferenciaProduto"."codProduto")
                    left outer join "GradeTamanho" gt on ("ReferenciaProduto"."codGrade" = gt."codGrade" and "ReferenciaProduto"."codTamanho" = gt."codTamanho")
                    where "TabelaPrecoRepresentante"."codRepresentante" = '.$_SESSION["codRepresentante"].' and "TabelaPrecoReferencia"."codProduto" in
                    ( select "codProduto" from "ReferenciaProduto" where "codReferencia" like '."'%".$referencia."%'".')
                    and "TabelaPrecoReferencia"."codTabelaPreco" = '.$_SESSION["codTabelaPreco"].' order by "ReferenciaProduto"."codTamanho"';
                    $qryCor = pg_query($conn, $sqlCor) or die('Erro ao obter cores'.$sqlCor);
                    $count = 0;
                    while ($rowCor = pg_fetch_assoc($qryCor)) {
                        $tituloTamanho = (!empty($rowCor["descricaoTamanho"])) ? $rowCor["descricaoTamanho"] : $rowCor["codTamanho"];
                        $count++;
                        echo '<div class="panel panel-default">
                                <div class="panel-heading" data-toggle="collapse" data-parent="#panel-details" href="#panel-element-'.$count.'" style="cursor: pointer">
                                    <a class="panel-title collapsed" data-toggle="collapse" data-parent="#panel-details" href="#panel-element-'.$count.'">
                                        <img src="" />  '.$tituloTamanho.'
                                    </a>
                                </div>';

                                if ($count == 1)
                                    echo '<div id="panel-element-'.$count.'" class="panel-collapse collapse in">';
                                else
                                    echo '<div id="panel-element-'.$count.'" class="panel-collapse collapse">';

                                 echo '<div class="panel-body">';
                                echo '<table class="cartTable table-responsive" style="width:100%">

                                        <tr class="CartProduct cartTableHeader">
                                            <td style="width:2%; text-align: left; font-size: 10px;"> </td>
                                            <td style="width:18%; text-align: left; font-size: 10px;"> Cor</td>
                                            <td style="width:12%; text-align: center; font-size: 10px;"> Est.</td>
                                            <td style="width:7%; text-align: center; font-size: 10px;"> R$ Unit.</td>
                                            <td style="width:7%; text-align: center; font-size: 10px;"> % IPI</td>
                                            <td style="width:7%; text-align: center; font-size: 10px;"> R$ IPI</td>
                                            <td style="width:7%; text-align: center; font-size: 10px;"> R$ ST</td>
                                            <td style="width:10%; text-align: center; font-size: 10px;"> R$ Total</td>
                                            <td style="width:30%; text-align: center; font-size: 10px;"> Quantidade</td>
                                        </tr>';
                                        //--------------------------------//
                                        //--- lista produtos -------------//
                                        //--------------------------------//
                                        /*
                                        $sqlPVI = 'select distinct pvt."qtdPedido" as "qtdGravada", d."desDetalheItem", "TabelaPrecoReferencia".*, dif."nomArquivo" as imgcor, dif."codDetalheItem",
                                        "TabelaPrecoReferencia"."preco" * (coalesce( (  SELECT "T"."perIndice" FROM "TabelaIndiceFinan" "T" WHERE "T"."codIndiceFinanceiro"  = '.$indFin.' AND "T"."codTabelaFinanceiro" = '.$tabFin.' ) , 1 )) "precoComIndice",
                                        "ReferenciaProduto"."codProduto", "Referencia"."desReferencia", "Referencia"."codGrupo", "ReferenciaProduto"."xPromocional", "ReferenciaProduto"."descritivo", f_parte_texto("ReferenciaProduto"."descritivo", 1, '."'|'".') as "desCompleta", "ReferenciaProduto"."qtdPeca", "ReferenciaProduto"."codTamanho", "ReferenciaProduto"."codCor",
                                        "ReferenciaProduto"."codReferencia", "ReferenciaProduto"."perIpi",
                                        round(cast("ReferenciaProduto"."fatorMultiVenda" as integer), 0) as "fatorMultiVenda", gt."numOrdem", gtrib."perICMSST",
										coalesce((select sum("qtdPrevista") from "ReferenciaProdutoEstoqueFuturo" where "ReferenciaProdutoEstoqueFuturo"."codReferencia" = "ReferenciaProduto"."codReferencia" 
												and "ReferenciaProdutoEstoqueFuturo"."codProduto" = "ReferenciaProduto"."codProduto"), 0) as "qtdEstoque", \'S\' as "xVendaDisponivel"
                                        from "TabelaPrecoReferencia"
                                        left join "TabelaPrecoRepresentante" on "TabelaPrecoReferencia"."codTabelaPreco" = "TabelaPrecoReferencia"."codTabelaPreco"
                                        left join "ReferenciaProduto" on ("ReferenciaProduto"."codProduto" = "TabelaPrecoReferencia"."codProduto" and "ReferenciaProduto"."codReferencia" = "TabelaPrecoReferencia"."codReferencia")
                                        left join "Referencia" on "Referencia"."codReferencia" = "ReferenciaProduto"."codReferencia"
                                        left outer join "DetalheItem" d on ("ReferenciaProduto"."codCor" = d."codDetalheItem")
                                        left outer join "DetalheItemFotoOnline" dif on (dif."codDetalheItem" = d."codDetalheItem" and dif."codDetalhe" = d."codDetalhe" and dif."codReferencia" = "Referencia"."codReferencia")
                                        left outer join "PedidoVendaItemTemp" pvt on (pvt."codCliente" = '.$_SESSION["codCliente"].' and pvt."codRepresentante" = '.$_SESSION["codRepresentante"].' and pvt."codClone" = '.$_SESSION["codClone"].' and pvt."codReferencia" = "Referencia"."codReferencia" and pvt."codProduto" = "ReferenciaProduto"."codProduto")
                                        left outer join "GradeTamanho" gt on ("ReferenciaProduto"."codGrade" = gt."codGrade" and "ReferenciaProduto"."codTamanho" = gt."codTamanho")
                                        left outer join "GrupoTributacao" gtrib on (gtrib."codGrTribProduto" = "ReferenciaProduto"."codGrTribProduto" and gtrib."UF" = '."'".$_SESSION["UF"]."'".' and gtrib."codGrTribCliente" = '."'".$_SESSION["codGrTribCliente"]."'".' )
                                        where "TabelaPrecoRepresentante"."codRepresentante" = '.$_SESSION["codRepresentante"].'
                                        and "TabelaPrecoReferencia"."codProduto" in ( select "codProduto" from "ReferenciaProduto" where "codReferencia" like '."'%".$referencia."%'".' and "codTamanho" = '."'".$rowCor["codTamanho"]."'".')
                                        and "TabelaPrecoReferencia"."codTabelaPreco" = '.$_SESSION["codTabelaPreco"].'
                                        order by  d."desDetalheItem", "desCompleta", "ReferenciaProduto"."codReferencia", gt."numOrdem", "desCompleta", "ReferenciaProduto"."codProduto"';
                                        */
                                        
                                        $sqlPVI = ' select distinct pvt."qtdPedido" as "qtdGravada",
                                                        d."desDetalheItem",
                                                        dif."nomArquivo" as imgcor,
                                                        dif."codDetalheItem",
                                                        "ReferenciaProduto"."codProduto",
                                                        "Referencia"."desReferencia",
                                                        "Referencia"."codGrupo",
                                                        "ReferenciaProduto"."xPromocional",
                                                        "ReferenciaProduto"."descritivo",
                                                        f_parte_texto("ReferenciaProduto"."descritivo", 1, \'|\') as "desCompleta",
                                                        "ReferenciaProduto"."qtdPeca",
                                                        "ReferenciaProduto"."codTamanho",
                                                        "ReferenciaProduto"."codCor",
                                                        "ReferenciaProduto"."codReferencia",
                                                        "ReferenciaProduto"."perIpi",
                                                        round(
                                                            cast("ReferenciaProduto"."fatorMultiVenda" as integer),
                                                            0
                                                        ) as "fatorMultiVenda",
                                                        gt."numOrdem",
                                                        coalesce(
                                                            (
                                                                select sum("qtdPrevista")
                                                                from "ReferenciaProdutoEstoqueFuturo"
                                                                where "ReferenciaProdutoEstoqueFuturo"."codReferencia" = "ReferenciaProduto"."codReferencia"
                                                                    and "ReferenciaProdutoEstoqueFuturo"."codProduto" = "ReferenciaProduto"."codProduto"
                                                            ),
                                                            0
                                                        ) as "qtdEstoque",
                                                        \'S\' as "xVendaDisponivel",
                                                        "codDisplay"

                                                    from "ReferenciaProduto"
                                                        join "TabelaPrecoReferencia" on ("TabelaPrecoReferencia"."codTabelaPreco" = '.$_SESSION["codTabelaPreco"].' and "TabelaPrecoReferencia"."codProduto" = "ReferenciaProduto"."codProduto") 
                                                        join "TabelaPrecoRepresentante" on "TabelaPrecoRepresentante"."codTabelaPreco" = "TabelaPrecoReferencia"."codTabelaPreco"
                                                        left join "Referencia" on "Referencia"."codReferencia" = "ReferenciaProduto"."codReferencia"
                                                        left outer join "DetalheItem" d on (
                                                            "ReferenciaProduto"."codCor" = d."codDetalheItem"
                                                        )
                                                        left outer join "DetalheItemFotoOnline" dif on (
                                                            dif."codDetalheItem" = d."codDetalheItem"
                                                            and dif."codDetalhe" = d."codDetalhe"
                                                            and dif."codReferencia" = "Referencia"."codReferencia"
                                                        )
                                                        left outer join "PedidoVendaItemTemp" pvt on (
                                                            pvt."codCliente" = '.$_SESSION["codCliente"].'
                                                            and pvt."codRepresentante" = '.$_SESSION["codRepresentante"].'
                                                            and pvt."codClone" = '.$_SESSION["codClone"].'
                                                            and pvt."codReferencia" = "Referencia"."codReferencia"
                                                            and pvt."codProduto" = "ReferenciaProduto"."codProduto"
                                                        )
                                                        left outer join "GradeTamanho" gt on (
                                                            "ReferenciaProduto"."codGrade" = gt."codGrade"
                                                            and "ReferenciaProduto"."codTamanho" = gt."codTamanho"
                                                        )
                                                        
                                                    where "ReferenciaProduto"."codReferencia" like \'%'.$referencia.'%\' 
                                                        and "ReferenciaProduto"."codTamanho" = \''.$rowCor["codTamanho"].'\'
                                                        and "TabelaPrecoRepresentante"."codRepresentante" = '.$_SESSION["codRepresentante"].'
                                                    order by d."desDetalheItem",
                                                        "desCompleta",
                                                        "ReferenciaProduto"."codReferencia",
                                                        gt."numOrdem",
                                                        "desCompleta",
                                                        "ReferenciaProduto"."codProduto"';

                                        //selecao de slide da coluna esquerda, +1 pq a primeira imagem é do grupo
                                        $selecaoSlide = 1;

										$qryPVI = pg_query($conn, $sqlPVI) or die('Erro ao obter fotos'.$sqlPVI);
                                        while ($rowPVI = pg_fetch_assoc($qryPVI)) {


                                            if ($rowPVI['fatorMultiVenda'] < 1)
                                                $fatorMultiVenda = 1;
                                            else
                                                $fatorMultiVenda = $rowPVI['fatorMultiVenda'];

                                            $qtdMinima = 0;

                                            $sqlFMV = 'SELECT * from "Cliente" where "codCliente" = '.$_SESSION["codCliente"];
                                            $qryFMV = pg_query($conn, $sqlFMV) or die('N&atilde;o foi poss&iacute;vel buscar o cliente');
                                            $resultFMV = pg_fetch_assoc($qryFMV);
                                            if ($resultFMV['xUsaQtdMultipla'] == 'N') {
                                                $fatorMultiVenda = 1;
                                            }
                                            

                                            /*
                                            $precoTabelaPreco = $rowPVI['preco'];
                                            $preco = $rowPVI['preco'];

                                            //-- recalcula item se mudou o tipo frete --//
                                            if ($xAplicaFrete == 'SMAIS') {
                                                $preco = ($preco + (($preco * $perFreteCIF) /100));
                                            } if ($xAplicaFrete == 'SMENOS'){
                                                $preco = ($preco - (($preco * $perFreteCIF) /100));
                                            }

                                            $xAplicaFrete = 'SMAIS';
                                            $perFreteCIF = $resultfrete['perFreteCIF'];
                                            //------------------- fim ------------------//

                                            $precoItem = $preco;
                                            $precoIPI = 0;
                                            $perIPI = 0;
                                            if ($rowPVI['perIpi'] > 0) {
                                                if ($_SESSION["calculaValorIPIProduto"] = 'S'){
                                                    //die('PerIPI:'.$resultIPI['perIpi'].' Produto:'.$row['ref']);
                                                    //die($_SESSION['codTabelaPreco']);
                                                    $perIPI = $rowPVI['perIpi'];
                                                    $precoIPI = ($preco * ($rowPVI['perIpi']/100));
                                                    $precoItem = $preco + $precoIPI;
                                                }
                                            }

                                            //---- valor de icms-st -----//
                                            $precoICMSST = 0;
                                            if ($rowPVI['perICMSST']){
                                                $precoICMSST = ($precoItem * ($rowPVI['perICMSST']/100));
                                                $precoItem = $precoItem + $precoICMSST;
                                            }

                                            /*if ([[HorrGlobal sharedGlobal] ICMSSTnoPreco]){
                                                NSNumber *valicmsst = [[HorrGlobal sharedGlobal] calcICMSSTModoSQL:dict[@"codProduto"] Referencia:detailDict[@"codReferencia"] codCliente:[[[HorrGlobal sharedGlobal] pedidoVenda] codCliente] Valor:[dict[@"valPrecoFinal"] doubleValue]];

                                                dict[@"valST"] = valicmsst;
                                                dict[@"valBCST"] = dict[@"valPrecoFinal"];
                                                dict[@"valPrecoFinal"] = @([dict[@"valPrecoFinal"] doubleValue] + [valicmsst doubleValue]);
                                            }*/

                                            //---- final valor icms-st --//

                                            $parametros = calcularValorProduto($conn, $rowPVI['codReferencia'], $rowPVI['codProduto']);
                                            

                                            if ($rowPVI["imgcor"]) {
                                                // if (substr($rowPVI["imgcor"], 0,4) == 'http') {
                                                //     $imagem = $rowPVI["imgcor"];
                                                // } else {
                                                //     if (file_exists(substr($_SERVER['DOCUMENT_ROOT'],0,strpos($_SERVER['DOCUMENT_ROOT'],'applications')).'docroot/'.$rowPVI["imgcor"])){
                                                //         $imagem = '../../../../../../'.$rowPVI["imgcor"];
                                                //     } else {
                                                //         $imagem = '../../images/nofoto.png';
                                                //     }
                                                // }
                                                $imagem = '../../fotos-produtos/thumb/'.$rowPVI["imgcor"];
                                                
                                            } else {
                                                $imagem = '../../images/nofoto.png';
                                            }

                                            //busca na arry de imagens o slide referente a imagemSlide
                                            foreach($arrSlide as $key => $imagemSlide) {
                                                if($imagemSlide == $rowPVI["imgcor"]) {
                                                    $selecaoSlide = $key;
                                                    break;
                                                }
                                            }


                                            if(!file_exists($imagem)){
                                                $imagem = '../../images/nofoto.png';
                                            }


                                            $multiploXembalagem = (empty($rowPVI['qtdPeca'])) ? ''
                                                : '<br><span class="badge" style="font-size: 9px; background-color: '.((strpos($rowPVI['qtdPeca'], 'Embalagem') === false) ? "#778899" : "#4682B4").'">'.str_replace(' = ', ' de ', $rowPVI['qtdPeca']).'</span>';

                                            echo '<tr class="CartProduct">';

                                            /*Removido o zoom da imagem para ser colocado o link para exibir na coluna da esquerda
                                            echo '<td style="text-align: center; font-size: 10px; color: #000;">
                                                        <a class="image-link" href="'.$imagem.'"><img style="border-radius: 50%; width: 60px;" data-large="'.$imagem.'" alt="img" class="" src="'.$imagem.'" ></a>
                                                    </td>';
                                            */
                                            
                                            echo '<td style="text-align: center; font-size: 10px; color: #000;">
                                                        <img style="border-radius: 50%; width: 60px;" class="cursor-hand selecionarImagem" src="'.$imagem.'" slide="'.$selecaoSlide.'" />
                                                    </td>';

                                            // echo '<td style="text-align: left;"><input type="image" id="Voltar" name="Voltar" src="'.$imagem.'" style="position: relative; height:24px; width:24px;" onClick="carregamainimage('."'CR',"."'".$rowPVI['codReferencia']."', '".$rowPVI['codDetalheItem']."'".')" />AAA</td>

                                            $showEstoque = (empty($row['codDisplay'])) ? number_format($rowPVI['qtdEstoque'], 0, ',', '') : $row['codDisplay'];

                                            echo '  <td style="text-align: left; font-size: 10px; color: #000;">'.$rowPVI['desDetalheItem'].$multiploXembalagem.'</td>
                                                    <td style="text-align: center; font-size: 10px; color: #000;"><font color="green">'.$showEstoque.'</font></td>
                                                    <td style="text-align: center; font-size: 10px; color: #000;">'.number_format($parametros['valUnitarioAntesIPI'], 2, ',', '').'</td>
                                                    <td style="text-align: center; font-size: 10px; color: #000;">'.number_format($parametros['perIPI'], 2, ',', '').'</td>
                                                    <td style="text-align: center; font-size: 10px; color: #000;">'.number_format($parametros['valIPI'], 2, ',', '').'</td>
                                                    <td style="text-align: center; font-size: 10px; color: #000;">'.number_format($parametros['valICMSST'], 2, ',', '').'</td>
                                                    <td style="text-align: center; font-size: 12px; color: #000;"><b>'.number_format($parametros['valUnitario'], 2, ',', '').'</b></td>
                                                    <td style="text-align: left; font-size: 10px;">
                                                        <div class="input-group bootstrap-touchspin">
                                                            <!--<span class="input-group-btn"><button class="btn btn-link bootstrap-touchspin-down" type="button"></button></span>-->
                                                            <input class="quanitySniper form-control"
                                                                    type="text"
                                                                    value="'.number_format($rowPVI['qtdGravada'], 0, ',', '').'"
                                                                    name="quanitySniper"
                                                                    id="pro'.$rowPVI['codProduto'].'"
                                                                    onchange="verifica('.$rowPVI['codProduto'].','.
                                                                                        $fatorMultiVenda.','.
                                                                                        $qtdMinima.', '.
                                                                                        number_format($parametros['valUnitarioAntesIPI'], 2, '.', '').', '.
                                                                                        "'".$rowPVI['codReferencia']."', '', '".
                                                                                        $rowPVI['xVendaDisponivel']."', ".
                                                                                        $rowPVI['qtdEstoque'].', '.
                                                                                        number_format($parametros['valUnitario'], 2, '.', '').', '.
                                                                                        number_format($parametros['perIPI'], 2, '.', '').','.
                                                                                        number_format($parametros['valUnitarioTabelaPreco'], 2, '.', '').','.
                                                                                        number_format($parametros['valICMSST'], 2, '.', '').')">
                                                            <!--<span class="input-group-btn"><button class="btn btn-link bootstrap-touchspin-up" type="button"></button></span>-->
                                                        </div>
                                                    </td>
													<td style="text-align: left; font-size: 6px;" hidden>
														<input disabled class="form-control" type="text" value="'.number_format($rowPVI['qtdGravada'], 0, ',', '').'" name="qtd-pro'.$rowPVI['codProduto'].'" id="qtd-pro'.$rowPVI['codProduto'].'">
													</td>

                                                 </tr>';
                                        }
                                        //--------------------------------//
                                        //--------------------------------//
                                        //--------------------------------//
                                echo '</table>
                               </div>
                            </div>
                        </div>';
                    }
                    ?>

                </div>
            </div>
        </div>
    </tbody>
<script>

    $(document).ready(function () {

        $('.image-link').magnificPopup({type:'image', backdrop: false});

    });

</script>