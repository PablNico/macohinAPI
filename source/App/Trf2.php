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
        private $cpf, $numProc, $uf;
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
    
    // Específicos API
        
        public function cpf($data)
        {
            $this->setUf(strtolower($data['uf']));
            $this->setCpf($data['cpf']);
            $this->acessoCpf("cpf");
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
       
       public function acessoCpf($tipo)
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

            if($tipo == "cpf")
            {
                $dados = [
                    "acao" => "processo_consulta_publica",
                    "acao_retorno" => "processo_consulta_publica",
                    "hdnInfraTipoPagina" => "1",
                    "sbmNovo" => "Consultar",
                    "rdoTipo" => "CPF",
                    "txtCpfCnpj" => $this->getCpf(),
                    "hdnInfraSelecoes" => "Infra"
                ];
            }
            elseif($tipo == "numProc")
            {
                $dados = [
                    "acao" => "processo_consulta_publica",
                    "acao_retorno" => "processo_consulta_publica",
                    "hdnInfraTipoPagina" => "1",
                    "sbmNovo" => "Consultar",
                    "rdoTipo" => "CPF",
                    "txtNumProcesso" => $this->getNumProc(),
                    "hdnInfraSelecoes" => "Infra"
                ];
            }
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
                    $trfInicial = curl_init(JFES."externo_controlador.php?acao=processo_consulta_publica");
                }
                else
                {
                    $trfInicial = curl_init(JFRJ."externo_controlador.php?acao=processo_consulta_publica");
                }
                curl_setopt($trfInicial, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($trfInicial, CURLOPT_POST, true);
                curl_setopt($trfInicial, CURLOPT_POSTFIELDS, $dados);
                curl_setopt($trfInicial, CURLOPT_COOKIE, $this->getSession());
                $site = curl_exec($trfInicial); // Atribui retorno da página com os dados enviados à variável
                curl_close($trfInicial);

                $this->acessoCpf($tipo);
                

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
                $this->extrairDados($dadosBrutos);
            }
       }

       public function extrairDados($dadosBrutos)
       {
        $dom = HtmlDomParser::str_get_html($dadosBrutos);
        foreach($dom->find("fieldset") as $fieldset)
        {
            $json = [];
            foreach($fieldset->find("legend") as $titulo)
            {
                array_push($json, $titulo->innertext());
                $subtitulo = [];
                $valor = [];
                echo $titulo->innertext();

                foreach($fieldset->find("label") as $key => $label)
                {
                    if($key%2 == 0)
                    {
                        array_push($subtitulo, $label->innertext());
                    }
                    elseif($key%2 == 1)
                    {
                        array_push($valor, $label->innertext());
                    }
                }
            }
        }
        // Tabela
       }


    }












