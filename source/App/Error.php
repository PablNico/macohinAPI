<?php
    namespace Source\App;

    class Error
    {
        public function error($data)
        {
            echo json_encode(["Erro:", $data['errcode']]);
        }
        
        /* 
        * Exibe mensagem das possíveis rotas
        */
        public function padrao($data)
        {
            echo json_encode(
                [
                    "Mensagem:", "Selecione uma rota",
                    "Rotas:", 
                    [
                        "TRF1" => 
                            [
                                "/numProcesso/{params}/{numProc}",
                                "/nome/{nome}",
                                "/cpf/{cpf}", 
                                "/nomeAdv/{nome}",
                                "/codOAB/{cod}",
                                "/numProcOrigin/{num}" 
                            ]
                    ],

                    [
                        "TRF4" => 
                            [
                                "/numProcesso/{params}/{numProc}",
                                "/nome/{nome}",
                                "/cpf/{cpf}", 
                                "/nomeAdv/{nome}",
                                "/codOAB/{cod}",
                                "/numProcOrigin/{num}" 
                            ]
                    ]
                ]
            );
        }
    }
?>