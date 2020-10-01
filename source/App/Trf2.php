<?php
    namespace Source\App;
    define("JFRJ", "https://eproc.jfrj.jus.br/eproc/");
    define("JFES", "https://eproc.jfes.jus.br/eproc/");
    define("CAPTCHA", "lib/captcha/Captcha.php");
    define("CONTROLADOR", "externo_controlador.php?acao=processo_consulta_publica");
    use thiagoalessio\TesseractOCR\TesseractOCR;
    use KubAT\PhpSimple\HtmlDomParser;

   

    class Trf2
    {
        private $cpf, $numProc, $uf, $dadosBrutos;
        private $session = "PHPSESSID=7f43b42fd833d1e77420a8dae7419000";

        // Getters
            public function getSession()
            {
                    return $this->session;
            }


            public function getCpf()
            {
                    return $this->cpf;
            }


            public function getNumProc()
            {
                    return $this->numProc;
            }

            public function getUf()
            {
                    return $this->uf;
            }

            public function getDadosBrutos()
            {
                    return $this->dadosBrutos;
            }


        // Setters
            public function setCpf($cpf)
            {
                    $this->cpf = $cpf;

                    return $this;
            }


            public function setNumProc($numProc)
            {
                    $this->numProc = $numProc;

                    return $this;
            }

            public function setUf($uf)
            {
                    $this->uf = $uf;

                    return $this;
            }

            public function setDadosBrutos($dadosBrutos)
            {
                    $this->dadosBrutos = $dadosBrutos;

                    return $this;
            }
        
        // Específicos API
            
            public function cpf($data)
            {
                $this->setUf(strtolower($data['uf']));
                $this->setCpf($data['cpf']);
                $this->acessoCpf();
            }

            public function numProcesso($data)
            {
                $this->setUf(strtolower($data['uf']));
                $this->setNumProc($data['numProc']);
                $this->acessoCpf("numProc");
            }

        // Específicos scraping
            public function limpaCaptcha()
            {
                system("magick convert -colorspace Gray -level 0,30%,0,1 -crop 140x100+30+10 captchaTRF2.png captcha-TRF2-limpo.png"); // Limpa Captcha
            } 
            
            public function acessoCpf()
            {    
                if($this->getUf() == "es")
                {
                    $trfInicial = curl_init(JFES.CONTROLADOR);
                }

                else
                {
                    $trfInicial = curl_init(JFRJ.CONTROLADOR);
                }

                    curl_setopt($trfInicial, CURLOPT_RETURNTRANSFER, true);
                    
                    $dados = [
                        "acao" => "processo_consulta_publica",
                        "acao_retorno" => "processo_consulta_publica",
                        "hdnInfraTipoPagina" => "1",
                        "sbmNovo" => "Consultar",
                        "rdoTipo" => "CPF",
                        "txtCpfCnpj" => $this->getCpf(),
                        "hdnInfraSelecoes" => "Infra"
                    ];
                    
                    // elseif($tipo == "numProc")
                    // {
                    //     $dados = [
                    //         "acao" => "processo_consulta_publica",
                    //         "acao_retorno" => "processo_consulta_publica",
                    //         "hdnInfraTipoPagina" => "1",
                    //         "sbmNovo" => "Consultar",
                    //         "rdoTipo" => "CPF",
                    //         "txtNumProcesso" => $this->getNumProc(),
                    //         "hdnInfraSelecoes" => "Infra"
                    //     ];
                    // }

                    curl_setopt($trfInicial, CURLOPT_POST, true);
                    curl_setopt($trfInicial, CURLOPT_POSTFIELDS, $dados);
                    curl_setopt($trfInicial, CURLOPT_COOKIE, $this->getSession());
                    $site = curl_exec($trfInicial); // Atribui retorno da página com os dados enviados à variável
                    curl_close($trfInicial);
                    $dom = HtmlDomParser::str_get_html($site);
                    if(!$dom->find("input#txtCaptcha"))
                    {
                        $this->validaSite($site);
                    }
                    else
                    {
                        if($this->getUf() == "es")
                        {
                            $ch = curl_init(JFES.CAPTCHA);
                        }
                        else
                        {
                            $ch = curl_init(JFRJ.CAPTCHA);
                        }
                        $fp = fopen("captchaTRF2.png" , "wb");
                        
                        curl_setopt($ch, CURLOPT_FILE, $fp);
                        curl_setopt($ch, CURLOPT_COOKIE, $this->getSession());
                        curl_setopt($ch, CURLOPT_HEADER, 0);
                        curl_exec($ch);
                        curl_close($ch);
                        fclose($fp);

                        $this->limpaCaptcha();
                        
                        $dados["txtCaptcha"] = (new TesseractOCR("captcha-TRF2-limpo.png"))->run(); // Roda OCR
                    
                        if($this->getUf() == "es")
                        {
                            $trfInicial = curl_init(JFES.CONTROLADOR);
                        }
                        else
                        {
                            $trfInicial = curl_init(JFRJ.CONTROLADOR);
                        }
                        curl_setopt($trfInicial, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($trfInicial, CURLOPT_POST, true);
                        curl_setopt($trfInicial, CURLOPT_POSTFIELDS, $dados);
                        curl_setopt($trfInicial, CURLOPT_COOKIE, $this->getSession());
                        $site = curl_exec($trfInicial); // Atribui retorno da página com os dados enviados à variável
                        curl_close($trfInicial);

                        $this->acessoCpf();
                        

                    }

            }

            public function validaSite($site)
            {    
                    $dom = HtmlDomParser::str_get_html($site);
                    if($dom->find("table.infraTable a"))
                    {
                        $href = $dom->find("table.infraTable a")[0]->href;
                        if($this->getUf() == "es")
                        {
                            $trfTable = curl_init(JFES.$href);
                        }
                        else
                        {
                            $trfTable = curl_init(JFRJ.$href);
                        }
                        curl_setopt($trfTable, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($trfTable, CURLOPT_COOKIE, $this->getSession());
                        $site = curl_exec($trfTable); // Atribui retorno da página com os dados enviados à variável
                        curl_close($trfTable);
                        $dom = HtmlDomParser::str_get_html($site);
                        $dadosBrutos = $dom->find("form#frmProcessoLista")[0]->innertext();
                        $this->setDadosBrutos($dadosBrutos);
                        $this->extrairDados();
                        // $this->extrairEventos();

                    }
            }

            public function extrairCapa() // OK
            {
                $dadosBrutos = $this->getDadosBrutos();
                $dom = HtmlDomParser::str_get_html($dadosBrutos);
                $titulo = $dom->find("fieldset legend",0)->innertext();
                $chaves = [];
                $valores = [];
                foreach($dom->find('fieldset label[id^="lbl"]') as $label)
                {
                        array_push($chaves, $label->innertext);
                }
                foreach($dom->find('fieldset label[id^="txt"]') as $label)
                {
                        array_push($valores, $label->innertext);
                }
                return ([$titulo => array_combine($chaves, $valores)]);
            }

            public function extrairAssuntos() // OK
            {
                $dadosBrutos = $this->getDadosBrutos();
                $dom = HtmlDomParser::str_get_html($dadosBrutos);
                $titulo = $dom->find("fieldset legend", 1)->innertext();
                $chaves = [];
                $valores = [];

                foreach($dom->find("fieldset#fldAssuntos table th.infraTh") as $th)
                {
                    array_push($chaves, $th->innertext);            
                }
                
                foreach($dom->find("fieldset#fldAssuntos table tr td") as $td)
                {
                    array_push($valores, $td->innertext);
                }

                

                $arrayFinal = [];
                foreach(array_chunk($valores, 3) as $arraySeparado)
                {
                    array_push($arrayFinal, array_combine($chaves, $arraySeparado));
                }

                return [$titulo => $arrayFinal];

            }

            public function extrairPartes() // Fazer tudo
            {
                $dadosBrutos = $this->getDadosBrutos();
                $dom = HtmlDomParser::str_get_html($dadosBrutos);
                $titulo = $dom->find("fieldset legend", 2)->innertext();  
                $chaves = [];
                $valores = [];
                foreach($dom->find("fieldset#fldPartes table tr th") as $th)
                {
                    array_push($chaves, $th->innertext);
                }
                foreach($dom->find("fieldset#fldPartes table tr td") as $td)
                {
                    array_push($valores, str_replace(["&nbsp;", "<br/>", "<br>"],[" ", " "], $td->innertext));
                }
                $arrayFinal = (array_combine($chaves, $valores));

                return [$titulo=>$arrayFinal];
            }

            public function extrairInfosAdicionais() // OK
            {
                $dadosBrutos = $this->getDadosBrutos();
                $dom = HtmlDomParser::str_get_html($dadosBrutos);
                $titulo = $dom->find("fieldset legend", 3)->innertext();   
                $chaves = [];
                $valores = [];
                foreach($dom->find("fieldset#fldInformacoesAdicionais td[align=right] label") as $td)
                {
                    array_push($chaves, $td->innertext());
                }
                foreach($dom->find("fieldset#fldInformacoesAdicionais td label.infraLabelObrigatorio") as $td)
                {
                    array_push($valores, $td->innertext());
                }

                return ([$titulo => array_combine($chaves, $valores)]);

            }

            public function extrairEventos() // OK
            {
                $dadosBrutos = $this->getDadosBrutos();
                $dom = HtmlDomParser::str_get_html($dadosBrutos);
                $titulo = "Eventos";
                $chaves = [];
                $valores = [];
             
                $tabelaEventos = $dom->find("div#divInfraAreaProcesso table", 3);
                
                foreach($tabelaEventos->find("th") as $th)
                {
                    array_push($chaves, $th->innertext);
                }

                foreach($tabelaEventos->find("td") as $td)
                {
                    array_push($valores, $td->innertext);
                }

                $arrayFinal = [];
                foreach(array_chunk($valores, 5) as $arraySeparado)
                {
                    array_push($arrayFinal, array_combine($chaves, $arraySeparado));
                }

                return [$titulo => $arrayFinal];

            }

            public function extrairDados()
            {
                $all = [];
                
                array_push($all, $this->extrairCapa());
                array_push($all, $this->extrairAssuntos());
                array_push($all, $this->extrairPartes());
                array_push($all, $this->extrairInfosAdicionais());
                array_push($all, $this->extrairEventos());
                
                
                echo json_encode($all);
            }
 
    
    }