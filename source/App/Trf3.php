<?php
    namespace Source\App;
    define("TRF3_BASE", "http://jef.trf3.jus.br/consulta/");
    define("TRF3_CONSULTA", "consultapro.php?tela=");
    use thiagoalessio\TesseractOCR\TesseractOCR;
    use KubAT\PhpSimple\HtmlDomParser;

    class Trf3
    {
        private $estado, $numProc, $cpf;

        
        // Getters
            public function getEstado()
            {
                    return $this->estado;
            }
            
            public function getNumProc()
            {
                    return $this->numProc;
            }

            public function getCpf()
            {
                    return $this->cpf;
            }

        // Setters    
            public function setEstado($estado)
            {
                    $this->estado = $estado;

                    return $this;
            }


            public function setNumProc($numProc)
            {
                    $this->numProc = $numProc;
    
                    return $this;
            }

            public function setCpf($cpf)
            {
                    $this->cpf = $cpf;

                    return $this;
            }
        // Específicos API
            
        public function cpf($data)
        {
            $this->setCpf($data['cpf']);
            $this->numCpf();
        }

        public function numProcesso($data)
        {
            $this->setNumProc($data['numProc']);
            $this->numProc();
        }
        
        // Específicos extração
            public function NumProc()
            {
                $trfInicial = curl_init(TRF3_BASE.TRF3_CONSULTA."1");
                curl_setopt($trfInicial, CURLOPT_RETURNTRANSFER, true);
                $site = curl_exec($trfInicial);
                $dom = HtmlDomParser::str_get_html($site);
                if ($dom->find("img img")) 
                {
                    $captchaSrc = TRF3_BASE.$dom->find("img img", 0)->src;
                    
                    $ch = curl_init($captchaSrc);
                    
                    $fp = fopen("captchaTRF3.jpg", "wb");

                    curl_setopt($ch, CURLOPT_FILE, $fp);
                    curl_setopt($ch, CURLOPT_HEADER, 0);
                    curl_exec($ch);
                    curl_close($ch);
                    fclose($fp);
                    $this->limpaCaptcha();
                    echo (new TesseractOCR("captcha-TRF2-limpo.png"))->run();

                }
                
                curl_close($trfInicial);
            }

            public function numCpf()
            {
                $trfInicial = curl_init(TRF3_BASE.TRF3_CONSULTA."2");
                curl_exec($trfInicial);
                curl_close($trfInicial);
            }
     
            public function limpaCaptcha()
            {
                system("magick convert -colorspace Gray -level 0,30%,0,1  captchaTRF3.jpg captcha-TRF3-limpo.jpg"); // Limpa Captcha
            } 
    }