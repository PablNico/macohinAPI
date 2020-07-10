<?php
    namespace Source\App;
    define("URL_TRF1", "https://processual.trf1.jus.br/consultaProcessual/");
    use Sunra\PhpSimple\HtmlDomParser;

    class Trf1
    {
        private $numProc, $nome, $cpf, $nomeAdv, $codOAB, $numProcOrigin;
        

        // Getters
            public function getNumProc()
            {
                    return $this->numProc;
            }

            
            public function getNome()
            {
                    return $this->nome;
            }

            
            public function getCpf()
            {
                    return $this->cpf;
            }

            
            public function getNomeAdv()
            {
                    return $this->nomeAdv;
            }

            
            public function getCodOAB()
            {
                    return $this->codOAB;
            }

         
            public function getNumProcOrigin()
            {
                    return $this->numProcOrigin;
            }
        
        // Setters
            public function setNumProc($numProc)
            {
                    $this->numProc = $numProc;

                    return $this;
            }

        
            public function setNome($nome)
            {
                    $this->nome = $nome;

                    return $this;
            }

        
            public function setCpf($cpf)
            {
                    $this->cpf = $cpf;

                    return $this;
            }

        
            public function setNomeAdv($nomeAdv)
            {
                    $this->nomeAdv = $nomeAdv;

                    return $this;
            }

        
            public function setCodOAB($codOAB)
            {
                    $this->codOAB = $codOAB;

                    return $this;
            }

        
            public function setNumProcOrigin($numProcOrigin)
            {
                    $this->numProcOrigin = $numProcOrigin;

                    return $this;
            }

        // Específicos API
            public function numProcesso($data)
            {
                echo $this->extrairNumProcesso($data);
            }

            public function nome($data)
            {
                echo json_encode(["Nome:" => "{$data['nome']}"]);
                return;
            }

            public function cpf($data)
            {
                echo $this->pesquisaCpf($data['cpf']);
                return;
            }

            public function nomeAdv($data)
            {
                echo json_encode(["Nome Advogado:" => "{$data['nome']}"]);
                return;
            }

            public function codOAB($data)
            {
                echo json_encode(["Código OAB:" => "{$data['cod']}"]);
                return;
            }

            public function numProcOrigin($data)
            {
                echo json_encode(["Número Processo Originário:" => "{$data['num']}"]);
                return;
            }

        // Específicos pesquisa
            
            public function extrairNumProcesso($data)
            {
                //  	0045244-78.2014.4.01.3400
                //
                $iniciar = curl_init(URL_TRF1."processo.php/");
                curl_setopt($iniciar, CURLOPT_RETURNTRANSFER, true);
                $dados = [
                    "proc" => $data['numProc'],
                    "secao" => "DF",
                    "pg" => "1",
                    "origem_pagina" => "1",
                    "enviar" => "Pesquisar",
                    //"numeroProcesso" => "03AGdBq270hcVZ0ZrzhZ-oiYuFXl35NczKztt2DzBGJTACQxf0HspqJHaN1YDT6LfMFr8rKl3BvJ48AR2RslzdTMj8SWPrhE2jblc5OqMX-TwS941TDlhNv7SMKFQsRhpT7d7IVsLBua1wutXL0LnuWsATesJ-6YAaaXyMjQ0AVUSWzQ8JJW63v3AvhQ6g9hTozX3f7ide9z3Y8n1pRetNihEURTrzn0WC0IPTdLYIUQHD6r66BHEqFoEkeaNO-Z2dQkdtpDil9hx-uaUH-5SW-GzzsNT8k3NJXaZ68C6zR6Lm96I8G7HwoYv5YJxxvKp7h-hSKoW_ncRVblHxT1TOCq71dGzfyOcD5JSr7_h2lO4Wrb5_oxtOJzgksWNsuXHPaQ5AiqqyC-EU224s5-ivTvCkJvv7Ua21ig",
                    "nmToken" => "numeroProcesso"
                ];

                curl_setopt($iniciar, CURLOPT_POST, true);
                
                curl_setopt($iniciar, CURLOPT_POSTFIELDS, $dados);

                $site = curl_exec($iniciar);

                curl_close($iniciar); 
                
                $dom = HtmlDomParser::str_get_html($site);
                //$ret = $dom->getElementById("aba-processo")->childNodes(0)->childNodes(1)->innertext();
                $json = array();
                $titulos = ["Data", "Código", "Descrição", "Complemento"];
                $limpa = [":", "\t", "<br/>", "&nbsp;"];
                switch (strtolower($data['params'])) {
                    case 'processo':
                        $th = $dom->find("div#aba-processo table th");
                        $td = $dom->find("div#aba-processo table td");
                        foreach ($th as $key => $ths) 
                        {
                            foreach ($td as $tds) {
                                $newThs = str_replace($limpa, "", $ths->innertext());
                                $newTd = str_replace($limpa, "", $td[$key]->innertext());
                                array_push($json, [$newThs => $newTd]);
                                // echo "{$newThs} {$td[$key]->innertext()}<br>";
                                break;
                            }
                        }
                        break;
                    case 'movimentacao':
                        // $tr = $dom->find("div#aba-processo table tr");
                        
                        foreach ($dom->find("div#aba-movimentacao table tr") as $tr) 
                        {
                            $contador = 0;
                            foreach ($tr->find("td") as $td)
                            {
                                $newTd = str_replace($limpa, "", $td->innertext());
                                array_push($json, [$titulos[$contador] => $newTd]);
                                $contador+=1;
                            }
                            echo "<br>";
                        }

                        # code...
                        break;
                }
                return json_encode($json);

            }

            public function pesquisaCpf($data)
            {
                // 0045244-78.2014.4.01.3400
                // 28722930191
                $iniciar = curl_init(URL_TRF1."/parte/listarPorCpfCnpj.php");
                curl_setopt($iniciar, CURLOPT_RETURNTRANSFER, true);
                $dados = [
                    "cpf_cnpj" => "28722930191",
                    "secao" => "DF",
                    //"pg" => "5",
                    "enviar" => "Pesquisar",
                    //"g-recaptcha-response" => "",
                    //"cpfCnpjParte" => "03AGdBq24HCPYPp1gervE_vGDq4STkrXaMudQGf-3wsuOHGWtWamLKi6eVnvPGnKmoaQm32vtSafwEh5j44WjBTvgXtuC85gjJVGiyDeHwF6Jg3VPkDqzoAlnigMiQ4PjHv863ixWLvt0NID6TnztCAx5jt4S-QwnnzUGBgNUC3SYXwKc5qPgk1_FFwyrwSu5DikEA1B5zR73vtwMegKOf74KPAQG6mlK_by51wivtd-CMowE1XTe2swx4kQ9IVfax6IBw636Pi7c7iHzH9LJDxdjNMAMovXLJpgvX5_f_86crCXYTPG2i0Wi_IcTmqOz_eMmRtZKeyfcw6cKtMn7JcQztOBJBFT9-viRDhys1zg0T9oqOu8gqa3CAGD8k75kMACYaQuH502LEtdo7t7shFIHly3NLkZ75Nw",
                    "nmToken" => "cpfCnpjParte"
                ];

                curl_setopt($iniciar, CURLOPT_POST, true);
                
                curl_setopt($iniciar, CURLOPT_POSTFIELDS, $dados);
                $site = curl_exec($iniciar);
                curl_close($iniciar);
                //echo($site);
                $dom = HtmlDomParser::str_get_html($site);
                $href = $dom->find("table a.listar-processo")[0]->href;
                $href = str_replace("/consultaProcessual", "", $href);

                $iniciar = curl_init(URL_TRF1."{$href}");
                curl_setopt($iniciar, CURLOPT_RETURNTRANSFER, true);
                $site = curl_exec($iniciar);
                curl_close($iniciar);
                
                $dom = HtmlDomParser::str_get_html($site);
                $processo = $dom->find("table a")[0]->text();
                $data = ["numProc" => $processo, "params" => "processo"];
                return $this->extrairNumProcesso($data);
                
                
            }

    }