<?php
    namespace Source\App;
    define("TRF2_BASE", "https://eproc.jfrj.jus.br/eproc/");
    use thiagoalessio\TesseractOCR\TesseractOCR;
    use Sunra\PhpSimple\HtmlDomParser;

   

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
            $this->setUf($data['uf']);
            $this->setCpf($data['cpf']);
            $this->acessoCpf();
        }

        public function numProcesso($data)
        {
            $this->setUf($data['uf']);
            $this->setCpf($data['numProc']);
        }

    // Específicos scraping
        
       public function acessoCpf()
       {    
            $trfInicial = curl_init(TRF2_BASE."externo_controlador.php?acao=processo_consulta_publica");
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
                $ch = curl_init(TRF2_BASE."lib/captcha/Captcha.php");
                $fp = fopen("captcha.png" , "wb");
                
                curl_setopt($ch, CURLOPT_FILE, $fp);
                curl_setopt($ch, CURLOPT_COOKIE, $this->getSession());
                curl_setopt($ch, CURLOPT_HEADER, 0);
                curl_exec($ch);
                curl_close($ch);
                fclose($fp);

                system("magick convert -colorspace Gray -level 0,30%,0,1 -crop 140x100+30+10 captcha.png captcha-limpo.png"); // Limpa Captcha
                
                $dados["txtCaptcha"] = (new TesseractOCR("captcha-limpo.png"))->run(); // Roda OCR
              
                $trfInicial = curl_init(TRF2_BASE."externo_controlador.php?acao=processo_consulta_publica");
                curl_setopt($trfInicial, CURLOPT_RETURNTRANSFER, true);
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
                    $this->acessoCpf();
                }

            }

       }

       public function validaSite($site)
       {    
            $dom = HtmlDomParser::str_get_html($site);
            if($dom->find("table.infraTable a"))
            {
                $href = $dom->find("table.infraTable a")[0]->href;
                $trfTable = curl_init(TRF2_BASE.$href);
                curl_setopt($trfTable, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($trfTable, CURLOPT_COOKIE, $this->getSession());
                $site = curl_exec($trfTable); // Atribui retorno da página com os dados enviados à variável
                curl_close($trfTable);
                $dom = HtmlDomParser::str_get_html($site);
                $dadosBrutos = $dom->find("form#frmProcessoLista")[0]->innertext();
                echo $dadosBrutos;
            }
       }

       public function extrairDados($dadosBrutos)
       {
        return 0;
       }


    }












