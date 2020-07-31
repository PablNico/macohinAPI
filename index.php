<?php


    require __DIR__."/vendor/autoload.php";

    use CoffeeCode\Router\Router;
    use thiagoalessio\TesseractOCR\TesseractOCR;

    $router = new Router(URL_BASE);

    // Controllers

    $router->namespace("Source\App");

    /*
    * Rota padrão ao acessar sem parâmetros
    */
    $router->group(null);
    $router->get("/", "Error:padrao");

    /* 
    * Rotas TRF1
    */

    $router->group("trf1");
    $router->get("/numProcesso/{params}/{numProc}", "Trf1:numProcesso");
    $router->get("/cpf/{params}/{cpf}", "Trf1:cpf");
    $router->get("/numProcOrigin/{num}", "Trf1:numProcOrigin");
    
    /* 
    * Rotas TRF2
    */

    $router->group("trf2");
    $router->get("/{uf}/numProcesso/{numProc}", "Trf2:numProcesso");
    $router->get("/{uf}/cpf/{cpf}", "Trf2:cpf");
    

    /* 
    * Rotas Erros
    */
    $router->group("oops");
    $router->get("/{errcode}", "Error:error");
    
    $router->dispatch();

    if($router->error())
    {
        $router->redirect("/oops/{$router->error()}");
    }


