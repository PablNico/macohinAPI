<?php
    namespace Source\App;
    define("URL_TRF1", "https://processual.trf1.jus.br/consultaProcessual/");
    use Sunra\PhpSimple\HtmlDomParser;

    class Trf1
    {
        private $numProc, $cpf, $numProcOrigin, $param;
        

        // Getters
            public function getNumProc()
            {
                    return $this->numProc;
            }

            
            
            public function getCpf()
            {
                    return $this->cpf;
            }

        
            public function getNumProcOrigin()
            {
                    return $this->numProcOrigin;
            }

            public function getParam()
            {
                    return $this->param;
            }
        
        // Setters
            public function setNumProc($numProc)
            {
                    $this->numProc = $numProc;

                    return $this;
            }

        
            public function setCpf($cpf)
            {
                $limpa = [".", "-"];
                $this->cpf = str_replace($limpa, "", $cpf);
                return $this;
            }

            public function setNumProcOrigin($numProcOrigin)
            {
                    $this->numProcOrigin = $numProcOrigin;

                    return $this;
            }
            
            public function setParam($param)
            {
                    $this->param = $param;

                    return $this;
            }

        // Específicos API


            public function cpf($data)
            {
                $this->setCpf($data['cpf']);
                $this->setParam($data['params']);

                echo $this->pesquisaCpf();
                return;
            }



            public function numProcOrigin($data)
            {
                echo json_encode(["Número Processo Originário:" => "{$data['num']}"]);
                return;
            }

        // Específicos pesquisa
            
            public function extrairDados($site)
            {
            // Apagar depois
                // //  	0045244-78.2014.4.01.3400
                // //
                // $iniciar = curl_init(URL_TRF1."processo.php/");
                // curl_setopt($iniciar, CURLOPT_RETURNTRANSFER, true);
                // $dados = [
                //     "proc" => $data['numProc'],
                //     "secao" => "DF",
                //     "pg" => "1",
                //     "origem_pagina" => "1",
                //     "enviar" => "Pesquisar",
                //     //"numeroProcesso" => "03AGdBq270hcVZ0ZrzhZ-oiYuFXl35NczKztt2DzBGJTACQxf0HspqJHaN1YDT6LfMFr8rKl3BvJ48AR2RslzdTMj8SWPrhE2jblc5OqMX-TwS941TDlhNv7SMKFQsRhpT7d7IVsLBua1wutXL0LnuWsATesJ-6YAaaXyMjQ0AVUSWzQ8JJW63v3AvhQ6g9hTozX3f7ide9z3Y8n1pRetNihEURTrzn0WC0IPTdLYIUQHD6r66BHEqFoEkeaNO-Z2dQkdtpDil9hx-uaUH-5SW-GzzsNT8k3NJXaZ68C6zR6Lm96I8G7HwoYv5YJxxvKp7h-hSKoW_ncRVblHxT1TOCq71dGzfyOcD5JSr7_h2lO4Wrb5_oxtOJzgksWNsuXHPaQ5AiqqyC-EU224s5-ivTvCkJvv7Ua21ig",
                //     "nmToken" => "numeroProcesso"
                // ];

                // curl_setopt($iniciar, CURLOPT_POST, true);
                
                // curl_setopt($iniciar, CURLOPT_POSTFIELDS, $dados);

                // $site = curl_exec($iniciar);

                // curl_close($iniciar); 
            // Apagar depois

                $dom = HtmlDomParser::str_get_html($site);
                $json = array();
                $limpa = [":", "\t", "\r" ,"\n", "<br/>", "&nbsp;"];

                switch (strtolower($this->getParam())) {
                    case 'processo':
                        $th = $dom->find("div#aba-processo table th");
                        $td = $dom->find("div#aba-processo table td");
                        foreach ($th as $key => $ths) 
                        {
                            foreach ($td as $tds) {
                                $newThs = str_replace($limpa, "", $ths->text());
                                $newTd = str_replace($limpa, "", $td[$key]->text());
                                array_push($json, [$newThs => $newTd]);
                                break;
                            }
                        }
                        break;


                    case 'movimentacao':
                        $titulos = ["Data", "Código", "Descrição", "Complemento"];
                        foreach ($dom->find("div#aba-movimentacao table tr") as $tr) 
                        {
                            $newLine = [];
                            $contador = 0;
                            foreach ($tr->find("td") as $td)
                            {
                                $newTd = str_replace($limpa, "", $td->innertext());
                                array_push($newLine, [$titulos[$contador] => $newTd]);
                                $contador+=1;
                            }
                            array_push($json, $newLine);
                        }
                        break;


                    case 'distribuicao':
                        $titulos = ["Data", "Descrição", "Juiz"];
                        foreach ($dom->find("div#aba-distribuicao table tr") as $tr) 
                        {
                            $contador = 0;
                            $newLine = [];
                            foreach ($tr->find("td") as $td)
                            {
                                $newTd = str_replace($limpa, "", $td->innertext());
                                array_push($newLine, [$titulos[$contador] => $newTd]);
                                $contador+=1;
                            }
                            array_push($json, $newLine);
                        }
                        break;


                    // case 'partes':
                    //     $titulos = ["Tipo", "Ent", "OAB", "Nome", "Características"];
                    //     foreach ($dom->find("div#aba-partes tbody") as $tr) 
                    //     {
                    //         var_dump($tr);
                    //         // echo "teste";
                    //         // $contador = 0;
                    //         // $newLine = [];
                    //         // foreach ($tr->find("td") as $td)
                    //         // {
                    //         //     $newTd = str_replace($limpa, "", $td->innertext());
                    //         //     array_push($newLine, [$titulos[$contador] => $newTd]);
                    //         //     $contador+=1;
                    //         // }
                    //         // array_push($json, $newLine);
                    //     }
                    //     break;

                }
                echo json_encode($json, JSON_UNESCAPED_UNICODE);

            }

            public function pesquisaCpf() // 28722930191
            {
    
            // Parte 1    

                // Inicia cURL para envio via POST
                $trfInicial = curl_init(URL_TRF1."/parte/listarPorCpfCnpj.php");
                curl_setopt($trfInicial, CURLOPT_RETURNTRANSFER, true);
               
                //Dados enviados via POST
                $dados = [
                    "cpf_cnpj" => $this->getCpf(),
                    "secao" => "TRF1",
                    "enviar" => "Pesquisar",
                    "nmToken" => "cpfCnpjParte"
                    //"pg" => "5",
                    //"g-recaptcha-response" => "",
                    //"cpfCnpjParte" => "03AGdBq24HCPYPp1gervE_vGDq4STkrXaMudQGf-3wsuOHGWtWamLKi6eVnvPGnKmoaQm32vtSafwEh5j44WjBTvgXtuC85gjJVGiyDeHwF6Jg3VPkDqzoAlnigMiQ4PjHv863ixWLvt0NID6TnztCAx5jt4S-QwnnzUGBgNUC3SYXwKc5qPgk1_FFwyrwSu5DikEA1B5zR73vtwMegKOf74KPAQG6mlK_by51wivtd-CMowE1XTe2swx4kQ9IVfax6IBw636Pi7c7iHzH9LJDxdjNMAMovXLJpgvX5_f_86crCXYTPG2i0Wi_IcTmqOz_eMmRtZKeyfcw6cKtMn7JcQztOBJBFT9-viRDhys1zg0T9oqOu8gqa3CAGD8k75kMACYaQuH502LEtdo7t7shFIHly3NLkZ75Nw",
                ];

                //Habilita post
                curl_setopt($trfInicial, CURLOPT_POST, true);
                curl_setopt($trfInicial, CURLOPT_POSTFIELDS, $dados);

                $site = curl_exec($trfInicial); // Atribui retorno da página com os dados enviados à variável
                curl_close($trfInicial);

            // Parte 2

                //Inicia Scrapping das informações 
                $dom = HtmlDomParser::str_get_html($site);
                if ($dom->find("table a.listar-processo") != NULL) 
                {
                    
                    // Pega o primeiro link de referencia, onde mostra processo originário e número do processo
                    // Também "Limpa" URL para ser usado com URL_TRF1
                    $href = str_replace("/consultaProcessual", "", $dom->find("table a.listar-processo")[0]->href);

                    // Faz nova requisição POST no link que acabamos de pegar
                    $trfHref = curl_init(URL_TRF1."{$href}");
                    curl_setopt($trfHref, CURLOPT_RETURNTRANSFER, true);
                    $site = curl_exec($trfHref);
                    curl_close($trfHref);

                    // Faz scrapping no novo link
                    $dom = HtmlDomParser::str_get_html($site);

                    // salva href da página com todas as informações do processo
                    $linkProcesso = $dom->find("table a")[0]->href;


                    $processo = $dom->find("table a")[0]->text(); 
                    $processoOrigin = $dom->find("table td")[1]->text();

                    // Atribui os números de processos ao objeto
                    $this->setNumProc($processo);
                    $this->setNumProcOrigin($processoOrigin);
                    
            // Parte 3
                    // Última requisição para extrair todos os dados
                    $linkProcesso = str_replace(" ", "%20",$linkProcesso);
                    $trfDados = curl_init("https://processual.trf1.jus.br{$linkProcesso}");
                    curl_setopt($trfDados, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($trfDados, CURLOPT_REFERER, "processual.trf1.jus.br/consultaProcessual/parte/listarPorCpfCnpj.php");
                    $site = curl_exec($trfDados); // Atribui retorno da página com os dados enviados à variável
                    curl_close($trfDados);
                    
                    $this->extrairDados($site);


                }
                else
                {
                    echo json_encode("Mensagem =>", $dom->find("div.notice")[0]->text());
                }



                
                
            }

    }