<?php

$ini = 'select \'G\' as tipo, "GrupoProduto"."codGrupoProduto" as "codGrupoProduto","GrupoProduto"."descricao" as "desGrupoProduto","GrupoProdutoFoto"."nomArquivo" as "nomArquivoGrupoProduto",0 as "codProduto",
   \'\' as "desProduto", \'\' as "nomArquivoProduto",\'N\' as "xLancamento",\'N\' as "xPromocional",\'N\' as "xBonificacao",\'N\' as "xVendaSuspensa" from "GrupoProduto"
         left join "GrupoProdutoFoto" on "GrupoProduto"."codGrupoProduto" = "GrupoProdutoFoto"."codGrupoProduto"';
   $var = ' where length("GrupoProduto"."codGrupoProduto") > 8
         and length("GrupoProduto"."codGrupoProduto") <= 16
         and "GrupoProduto"."codGrupoProduto" like \'%\' ';
   $fim = ' AND EXISTS(SELECT 1 FROM "ReferenciaGrupoProduto" "R", "ReferenciaProduto" "RP" WHERE "R"."codReferencia" = "RP"."codReferencia" and EXISTS(SELECT 1 FROM "TabelaPrecoReferencia" "TPR" WHERE "TPR"."codReferencia" = "R"."codReferencia"
            AND "TPR"."codTabelaPreco" in ('.$_SESSION["codTabelaPreco"].')
            AND substr( "R"."codGrupoProduto", 1, length( "GrupoProduto"."codGrupoProduto" )  ) = "GrupoProduto"."codGrupoProduto" ) 
            and exists (SELECT 1 FROM "ReferenciaProduto" rp WHERE rp."codReferencia" = "R"."codReferencia" AND rp."xAtivo" <> \'N\' 
            and case when "xVendaDisponivel" = \'S\' then "qtdEstoque" else 1 end > 0) )
            Order by "GrupoProduto"."codGrupoProduto"'; // limit 4';// limit 10';

   $sql = $ini.$var.$fim;

   $menu = '';
   $menuaux = '';
   $categoria = '';
   //$menu = $menu. '<li class="active"><a href="../../view/index/index.php?grupo=0001"> Home </a></li>';

   $qry = pg_query($conn, $sql) or die('Erro ao obter dados da tabela Produto - '.$sql);
   while ($row = pg_fetch_assoc($qry)) {
		$desGrupoProduto = $row['desGrupoProduto'];
		if ($desGrupoProduto == 'GERAL')
			$desGrupoProduto = 'PRODUTOS';

		//menu normal
		$menu = $menu. '<li class="dropdown megamenu-fullwidth hidden-xs"><a data-toggle="dropdown" class="dropdown-toggle" onclick="window.location.href=\'../../view/index/index.php?grupo='.$row['codGrupoProduto'].'\'"  href="../../view/index/index.php?grupo='.$row['codGrupoProduto'].'"> '.utf8_encode($desGrupoProduto).' <b class="caret"> </b> </a>';
			$menu = $menu. '<ul class="dropdown-menu">';
				$menu = $menu. '<li class="megamenu-content ">';
					$menu = $menu. '<ul class="col-lg-3  col-sm-3 col-md-3 unstyled noMarginLeft newCollectionUl">';

						$var = ' where length("GrupoProduto"."codGrupoProduto") > 16
						and length("GrupoProduto"."codGrupoProduto") <= 24
						and "GrupoProduto"."codGrupoProduto" like \''.$row['codGrupoProduto'].'%\' ';
                       $fim = ' AND EXISTS(SELECT 1 FROM "ReferenciaGrupoProduto" "R", "ReferenciaProduto" "RP" WHERE "R"."codReferencia" = "RP"."codReferencia" and EXISTS(SELECT 1 FROM "TabelaPrecoReferencia" "TPR" WHERE "TPR"."codReferencia" = "R"."codReferencia"
                                AND "TPR"."codTabelaPreco" in ('.$_SESSION["codTabelaPreco"].')
                                AND substr( "R"."codGrupoProduto", 1, length( "GrupoProduto"."codGrupoProduto" )  ) = "GrupoProduto"."codGrupoProduto" ) 
                                and exists (SELECT 1 FROM "ReferenciaProduto" rp WHERE rp."codReferencia" = "R"."codReferencia" AND rp."xAtivo" <> \'N\' 
                                and case when "xVendaDisponivel" = \'S\' then "qtdEstoque" else 1 end > 0) )
                                Order by "GrupoProduto"."codGrupoProduto"'; // limit 4';// limit 10';
						$sql2 = $ini.$var.$fim;

						$count = 0;
						$qry2 = pg_query($conn, $sql2) or die('Erro ao obter dados da tabela Produto - '.$sql2);
						while ($row2 = pg_fetch_assoc($qry2)) {
							if ($row['codGrupoProduto'] == '00010011')
								$aux = 1;
							else
								$aux = 5;

							if ($count == $aux){
								$menu = $menu. '</ul>'; //-- div da quebra
								$menu = $menu. '<ul class="col-lg-3  col-sm-3 col-md-3 unstyled noMarginLeft newCollectionUl">';
								$count = 0;
							}
							
							$menu = $menu. '<li><a href="../../view/index/index.php?grupo='.$row2['codGrupoProduto'].'"> '.utf8_encode($row2['desGrupoProduto']).' </a></li>';
							$count++;
							
							//-- terceiro nivel
							/*$var = ' where length("GrupoProduto"."codGrupoProduto") > 24
							and length("GrupoProduto"."codGrupoProduto") <= 32
							and "GrupoProduto"."codGrupoProduto" like \''.$row2['codGrupoProduto'].'%\' ';
						   $fim = ' AND EXISTS(SELECT 1 FROM "ReferenciaGrupoProduto" "R", "ReferenciaProduto" "RP" WHERE "R"."codReferencia" = "RP"."codReferencia" and EXISTS(SELECT 1 FROM "TabelaPrecoReferencia" "TPR" WHERE "TPR"."codReferencia" = "R"."codReferencia"
									AND "TPR"."codTabelaPreco" in ('.$_SESSION["codTabelaPreco"].')
									AND substr( "R"."codGrupoProduto", 1, length( "GrupoProduto"."codGrupoProduto" )  ) = "GrupoProduto"."codGrupoProduto" ) 
									and exists (SELECT 1 FROM "ReferenciaProduto" rp WHERE rp."codReferencia" = "R"."codReferencia" AND rp."xAtivo" <> \'N\' 
									and case when "xVendaDisponivel" = \'S\' then "qtdEstoque" else 1 end > 0) )
									and "GrupoProduto"."codGrupoProduto" not in ('."'".$row2['codGrupoProduto']."'".')
									Order by "GrupoProduto"."codGrupoProduto"'; // limit 4';// limit 10';
							$sql3 = $ini.$var.$fim;
							$qry3 = pg_query($conn, $sql3) or die('Erro ao obter dados da tabela Produto - '.$sql3);
							while ($row3 = pg_fetch_assoc($qry3)) {
								$menu = $menu. '<li><a href="../../view/index/index.php?grupo='.$row3['codGrupoProduto'].'" style="font-size: 10px; font-style: italic;"> &nbsp;&nbsp;&nbsp;&nbsp;'.utf8_encode($row3['desGrupoProduto']).' </a></li>';
							}*/
						}
						$menu = $menu. '<li><a href="../../view/index/index.php?grupo='.$row['codGrupoProduto'].'"> TODOS </a></li>';
					$menu = $menu. '</ul>'; //-- div da quebra
					//$menu = $menu. '<ul class="col-lg-4  col-sm-4 col-md-4 unstyled noMarginLeft newCollectionUl">';
					//	$menu = $menu. '<li><a href="../../view/index/index.php?grupo='.$row['codGrupoProduto'].'"> Todos </a></li>';
					//$menu = $menu. '</ul>'; //-- div da quebra

			$menu = $menu. '</li>';
			$menu = $menu. '</ul>';
		$menu = $menu. '</li>';
		
		//menu mobile
		$menu = $menu. '<li class="dropdown megamenu-fullwidth visible-xs"><a data-toggle="dropdown" class="dropdown-toggle" href="../../view/index/index.php?grupo='.$row['codGrupoProduto'].'"> '.$desGrupoProduto.' <b class="caret"> </b> </a>';
			$menu = $menu. '<ul class="dropdown-menu">';
				$menu = $menu. '<li class="megamenu-content ">';
					$menu = $menu. '<ul class="col-lg-3  col-sm-3 col-md-3 unstyled noMarginLeft newCollectionUl">';

						$var = ' where length("GrupoProduto"."codGrupoProduto") > 8
						and length("GrupoProduto"."codGrupoProduto") <= 16
						and "GrupoProduto"."codGrupoProduto" like \''.$row['codGrupoProduto'].'%\' ';
                       $fim = ' AND EXISTS(SELECT 1 FROM "ReferenciaGrupoProduto" "R", "ReferenciaProduto" "RP" WHERE "R"."codReferencia" = "RP"."codReferencia" and EXISTS(SELECT 1 FROM "TabelaPrecoReferencia" "TPR" WHERE "TPR"."codReferencia" = "R"."codReferencia"
                                AND "TPR"."codTabelaPreco" in ('.$_SESSION["codTabelaPreco"].')
                                AND substr( "R"."codGrupoProduto", 1, length( "GrupoProduto"."codGrupoProduto" )  ) = "GrupoProduto"."codGrupoProduto" ) 
                                and exists (SELECT 1 FROM "ReferenciaProduto" rp WHERE rp."codReferencia" = "R"."codReferencia" AND rp."xAtivo" <> \'N\' 
                                and case when "xVendaDisponivel" = \'S\' then "qtdEstoque" else 1 end > 0) )
                                Order by "GrupoProduto"."codGrupoProduto"'; // limit 4';// limit 10';
						$sql2 = $ini.$var.$fim;

						$count = 0;
						$qry2 = pg_query($conn, $sql2) or die('Erro ao obter dados da tabela Produto - '.$sql2);
						while ($row2 = pg_fetch_assoc($qry2)) {
							if ($row['codGrupoProduto'] == '00010011')
								$aux = 1;
							else
								$aux = 5;

							if ($count == $aux){
								$menu = $menu. '</ul>'; //-- div da quebra
								$menu = $menu. '<ul class="col-lg-3  col-sm-3 col-md-3 unstyled noMarginLeft newCollectionUl">';
								$count = 0;
							}
							
							$menu = $menu. '<li><a href="../../view/index/index.php?grupo='.$row2['codGrupoProduto'].'"> '.utf8_encode($row2['desGrupoProduto']).' </a></li>';
							$count++;
							
							//-- terceiro nivel
							$var = ' where length("GrupoProduto"."codGrupoProduto") > 16
							and length("GrupoProduto"."codGrupoProduto") <= 24
							and "GrupoProduto"."codGrupoProduto" like \''.$row2['codGrupoProduto'].'%\' ';
						   $fim = ' AND EXISTS(SELECT 1 FROM "ReferenciaGrupoProduto" "R", "ReferenciaProduto" "RP" WHERE "R"."codReferencia" = "RP"."codReferencia" and EXISTS(SELECT 1 FROM "TabelaPrecoReferencia" "TPR" WHERE "TPR"."codReferencia" = "R"."codReferencia"
									AND "TPR"."codTabelaPreco" in ('.$_SESSION["codTabelaPreco"].')
									AND substr( "R"."codGrupoProduto", 1, length( "GrupoProduto"."codGrupoProduto" )  ) = "GrupoProduto"."codGrupoProduto" ) 
									and exists (SELECT 1 FROM "ReferenciaProduto" rp WHERE rp."codReferencia" = "R"."codReferencia" AND rp."xAtivo" <> \'N\' 
									and case when "xVendaDisponivel" = \'S\' then "qtdEstoque" else 1 end > 0) )
									and "GrupoProduto"."codGrupoProduto" not in ('."'".$row2['codGrupoProduto']."'".')
									Order by "GrupoProduto"."codGrupoProduto"'; // limit 4';// limit 10';
							$sql3 = $ini.$var.$fim;
							$qry3 = pg_query($conn, $sql3) or die('Erro ao obter dados da tabela Produto - '.$sql3);
							while ($row3 = pg_fetch_assoc($qry3)) {
								$menu = $menu. '<li><a href="../../view/index/index.php?grupo='.$row3['codGrupoProduto'].'" style="font-size: 10px; font-style: italic;"> &nbsp;&nbsp;&nbsp;&nbsp;'.utf8_encode($row3['desGrupoProduto']).' </a></li>';
							}
						}
						$menu = $menu. '<li><a href="../../view/index/index.php?grupo='.$row['codGrupoProduto'].'"> TODOS </a></li>';
					$menu = $menu. '</ul>'; //-- div da quebra

					//$menu = $menu. '<ul class="col-lg-4  col-sm-4 col-md-4 unstyled noMarginLeft newCollectionUl">';
					//	$menu = $menu. '<li><a href="../../view/index/index.php?grupo='.$row['codGrupoProduto'].'"> Todos </a></li>';
					//$menu = $menu. '</ul>'; //-- div da quebra

			$menu = $menu. '</li>';
			$menu = $menu. '</ul>';
		$menu = $menu. '</li>';
		
	}

    //MENU OUTLET NORMAL
/*   	$menu = $menu. '<li class="dropdown megamenu-fullwidth hidden-xs"><a data-toggle="dropdown" class="dropdown-toggle" onclick="window.location.href=\'../../view/index/index.php?grupo=&wish=O\'" href="../../view/index/index.php?grupo=&wish=O"> OUTLET <b class="caret"> </b> </a>';
      $menu = $menu. '<ul class="dropdown-menu">';
         $menu = $menu. '<li class="megamenu-content ">';
               $menu = $menu. '<ul class="col-lg-2  col-sm-2 col-md-2 unstyled noMarginLeft newCollectionUl">';

                  $var = ' where length("GrupoProduto"."codGrupoProduto") > 2
                  and length("GrupoProduto"."codGrupoProduto") <= 4
                  and "GrupoProduto"."codGrupoProduto" like \''.$row['codGrupoProduto'].'%\' ';
               $fim = ' AND EXISTS(SELECT 1 FROM "Referencia" "R" WHERE EXISTS(SELECT 1 FROM "TabelaPrecoReferencia" "TPR" WHERE "TPR"."codReferencia" = "R"."codReferencia"
                        AND "TPR"."codTabelaPreco" in ('.$_SESSION["codTabelaPreco"].')
                        AND substr( "R"."codGrupo", 1, length( "GrupoProduto"."codGrupoProduto" )  ) = "GrupoProduto"."codGrupoProduto" ) 
                        and exists (SELECT 1 FROM "ReferenciaProduto" rp WHERE rp."codReferencia" = "R"."codReferencia" AND rp."xAtivo" <> \'N\' 
                        and case when "xVendaDisponivel" = \'S\' then "qtdEstoque" else 1 end > 0) )
                        Order by "GrupoProduto"."codGrupoProduto"'; // limit 4';// limit 10';
                  $sql2 = $ini.$var.$fim;

                  $qry2 = pg_query($conn, $sql2) or die('Erro ao obter dados da tabela Produto - '.$sql2);
                  while ($row2 = pg_fetch_assoc($qry2)) {
                     $menu = $menu. '<li><a href="../../view/index/index.php?grupo='.$row2['codGrupoProduto'].'&wish=O"> '.utf8_encode($row2['desGrupoProduto']).' </a></li>';

                     $var = ' where length("GrupoProduto"."codGrupoProduto") > 4
                     and length("GrupoProduto"."codGrupoProduto") <= 8
                     and "GrupoProduto"."codGrupoProduto" like \''.$row2['codGrupoProduto'].'%\' ';
                   $fim = ' AND EXISTS(SELECT 1 FROM "Referencia" "R" WHERE EXISTS(SELECT 1 FROM "TabelaPrecoReferencia" "TPR" WHERE "TPR"."codReferencia" = "R"."codReferencia"
                            AND "TPR"."codTabelaPreco" in ('.$_SESSION["codTabelaPreco"].')
                            AND substr( "R"."codGrupo", 1, length( "GrupoProduto"."codGrupoProduto" )  ) = "GrupoProduto"."codGrupoProduto" ) 
                            and exists (SELECT 1 FROM "ReferenciaProduto" rp WHERE rp."codReferencia" = "R"."codReferencia" AND rp."xAtivo" <> \'N\' 
                            and case when "xVendaDisponivel" = \'S\' then "qtdEstoque" else 1 end > 0) )
                            Order by "GrupoProduto"."codGrupoProduto"'; // limit 4';// limit 10';
                     $sql3 = $ini.$var.$fim;
                     $qry3 = pg_query($conn, $sql3) or die('Erro ao obter dados da tabela Produto - '.$sql3);
                     while ($row3 = pg_fetch_assoc($qry3)) {
                        $menu = $menu. '<li><a href="../../view/index/index.php?grupo='.$row3['codGrupoProduto'].'&wish=O"> <font size="1;">'.utf8_encode($row3['desGrupoProduto']).'</font> </a></li>';
                     }

                     $menu = $menu. '</ul>'; //-- div da quebra
                     $menu = $menu. '<ul class="col-lg-2  col-sm-2 col-md-2 unstyled noMarginLeft newCollectionUl">';

                  }
                  $menu = $menu. '<li><a href="../../view/index/index.php?grupo=&wish=O"> TODOS </a></li>';
               $menu = $menu. '</ul>'; //-- div da quebra
      $menu = $menu. '</li>';
      $menu = $menu. '</ul>';
	  $menu = $menu. '</li>' ;//--final do menu;
		 

    //MENU OUTLET MOBILE
   	$menu = $menu. '<li class="dropdown megamenu-fullwidth visible-xs"><a data-toggle="dropdown" class="dropdown-toggle" href="../../view/index/index.php?grupo=&wish=O"> OUTLET <b class="caret"> </b> </a>';
      $menu = $menu. '<ul class="dropdown-menu">';
         $menu = $menu. '<li class="megamenu-content ">';
               $menu = $menu. '<ul class="col-lg-2  col-sm-2 col-md-2 unstyled noMarginLeft newCollectionUl">';

                  $var = ' where length("GrupoProduto"."codGrupoProduto") > 2
                  and length("GrupoProduto"."codGrupoProduto") <= 4
                  and "GrupoProduto"."codGrupoProduto" like \''.$row['codGrupoProduto'].'%\' ';
               $fim = ' AND EXISTS(SELECT 1 FROM "Referencia" "R" WHERE EXISTS(SELECT 1 FROM "TabelaPrecoReferencia" "TPR" WHERE "TPR"."codReferencia" = "R"."codReferencia"
                        AND "TPR"."codTabelaPreco" in ('.$_SESSION["codTabelaPreco"].')
                        AND substr( "R"."codGrupo", 1, length( "GrupoProduto"."codGrupoProduto" )  ) = "GrupoProduto"."codGrupoProduto" ) 
                        and exists (SELECT 1 FROM "ReferenciaProduto" rp WHERE rp."codReferencia" = "R"."codReferencia" AND rp."xAtivo" <> \'N\' 
                        and case when "xVendaDisponivel" = \'S\' then "qtdEstoque" else 1 end > 0) )
                        Order by "GrupoProduto"."codGrupoProduto"'; // limit 4';// limit 10';
                  $sql2 = $ini.$var.$fim;

                  $qry2 = pg_query($conn, $sql2) or die('Erro ao obter dados da tabela Produto - '.$sql2);
                  while ($row2 = pg_fetch_assoc($qry2)) {
                     $menu = $menu. '<li><a href="../../view/index/index.php?grupo='.$row2['codGrupoProduto'].'&wish=O"> '.utf8_encode($row2['desGrupoProduto']).' </a></li>';

                     $var = ' where length("GrupoProduto"."codGrupoProduto") > 4
                     and length("GrupoProduto"."codGrupoProduto") <= 8
                     and "GrupoProduto"."codGrupoProduto" like \''.$row2['codGrupoProduto'].'%\' ';
                   $fim = ' AND EXISTS(SELECT 1 FROM "Referencia" "R" WHERE EXISTS(SELECT 1 FROM "TabelaPrecoReferencia" "TPR" WHERE "TPR"."codReferencia" = "R"."codReferencia"
                            AND "TPR"."codTabelaPreco" in ('.$_SESSION["codTabelaPreco"].')
                            AND substr( "R"."codGrupo", 1, length( "GrupoProduto"."codGrupoProduto" )  ) = "GrupoProduto"."codGrupoProduto" ) 
                            and exists (SELECT 1 FROM "ReferenciaProduto" rp WHERE rp."codReferencia" = "R"."codReferencia" AND rp."xAtivo" <> \'N\' 
                            and case when "xVendaDisponivel" = \'S\' then "qtdEstoque" else 1 end > 0) )
                            Order by "GrupoProduto"."codGrupoProduto"'; // limit 4';// limit 10';
                     $sql3 = $ini.$var.$fim;
                     $qry3 = pg_query($conn, $sql3) or die('Erro ao obter dados da tabela Produto - '.$sql3);
                     while ($row3 = pg_fetch_assoc($qry3)) {
                        $menu = $menu. '<li><a href="../../view/index/index.php?grupo='.$row3['codGrupoProduto'].'&wish=O"> <font size="1;">'.utf8_encode($row3['desGrupoProduto']).'</font> </a></li>';
                     }

                     $menu = $menu. '</ul>'; //-- div da quebra
                     $menu = $menu. '<ul class="col-lg-2  col-sm-2 col-md-2 unstyled noMarginLeft newCollectionUl">';

                  }
                  $menu = $menu. '<li><a href="../../view/index/index.php?grupo=&wish=O"> TODOS </a></li>';
               $menu = $menu. '</ul>'; //-- div da quebra
      $menu = $menu. '</li>';
      $menu = $menu. '</ul>';
   $menu = $menu. '</li>' ;//--final do menu;*/
  


   //die($menu);
   $_SESSION["menu"] = $menu;

   print $menu;