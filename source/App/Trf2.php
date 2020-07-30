<?php
    namespace Source\App;
    define("TRF2_BASE", "https://eproc.jfrj.jus.br/eproc/");
    use thiagoalessio\TesseractOCR\TesseractOCR;
    use Orbitale\Component\ImageMagick\Command;
    use Sunra\PhpSimple\HtmlDomParser;

   

    class Trf2
    {
        private $session, $cpf, $numProc, $uf;


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

    // Setter
        public function setSession($session) // Gera MD5 válido para ser usado numa sessão
        {
                // $this->session = md5($session);
                $this->session = "2f43b42fd833d1e77420a8dae7419000";

                return $this;
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
            $this->primeiroAcessoCpf();
        }

        public function numProcesso($data)
        {
            $this->setUf($data['uf']);
            $this->setCpf($data['numProc']);
        }

    // Específicos scraping
        
       public function primeiroAcessoCpf()
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
            curl_setopt($trfInicial, CURLOPT_COOKIE, "PHPSESSID=2f43b42fd833d1e77420a8dae7419000");
            $site = curl_exec($trfInicial); // Atribui retorno da página com os dados enviados à variável
            curl_close($trfInicial);
            $dom = HtmlDomParser::str_get_html($site);
            if(!$dom->find("input#txtCaptcha"))
            {
                if($dom->find("table.infraTable a"))
                {
                    $href = $dom->find("table.infraTable a")[0]->href;
                    $trfTable = curl_init(TRF2_BASE.$href);
                    curl_setopt($trfTable, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($trfTable, CURLOPT_COOKIE, "PHPSESSID=2f43b42fd833d1e77420a8dae7419000");
                    $site = curl_exec($trfTable); // Atribui retorno da página com os dados enviados à variável
                    curl_close($trfTable);
                    $dom = HtmlDomParser::str_get_html($site);
                    $dadosBrutos = $dom->find("form#frmProcessoLista")[0]->innertext();
                }
            }
            else
            {
                $ch = curl_init(TRF2_BASE."lib/captcha/Captcha.php");
                $fp = fopen("captcha.png" , "wb");
                curl_setopt($ch, CURLOPT_FILE, $fp);
                curl_setopt($ch, CURLOPT_COOKIE, "PHPSESSID=2f43b42fd833d1e77420a8dae7419000");
                curl_setopt($ch, CURLOPT_HEADER, 0);
                curl_exec($ch);
                curl_close($ch);
                fclose($fp);
            }

       }

       public function extrairDados($dadosBrutos)
       {
        return 0;
       }


    }















// Código para ver depois
            // public function captcha()
        // {
        //     $command = new Command("C:\Program Files\ImageMagick\magick.exe");
        //     $command->convert("captcha0.png")->output("captcha/teste.png")->resize('50x50')->run();
        // }
      
        // public function ocr()
        // {
        //     $ocr = new TesseractOCR();
        //     for ($i=0; $i < 9; $i++) 
        //     { 
        //         echo $ocr->image("captcha/captcha{$i}.png")->whitelist(range("a", "z"), range("0", "9"))->run();
        //         echo "\n";
        //     }
            
        // }

        // public function down()
        // {
        //     $trfInicial = curl_init('https://eproc.jfrj.jus.br/eproc/externo_controlador.php?acao=processo_consulta_publica');
        //     curl_setopt($trfInicial, CURLOPT_RETURNTRANSFER, true);
       
        //     // //Habilita post
        //     curl_setopt($trfInicial, CURLOPT_POST, true);
        //     // curl_setopt($trfInicial, CURLOPT_POSTFIELDS, $dados);
            
        //     $site = curl_exec($trfInicial); // Atribui retorno da página com os dados enviados à variável
        //     $dom = HtmlDomParser::str_get_html($site);
        //     if($dom->find("img#imgCaptcha"))
        //     {
        //         echo $dom->find("img#imgCaptcha", 0)->content;
        //         // $teste = file_get_contents("https://eproc.jfrj.jus.br/eproc/".$dom->find("img#imgCaptcha", 0)->src);
        //         // file_put_contents("vendor","teste.png");
        //     }
            


    // }