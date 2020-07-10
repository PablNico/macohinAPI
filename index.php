<?php


    require __DIR__."/vendor/autoload.php";

    use CoffeeCode\Router\Router;

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
    $router->get("/nome/{nome}", "Trf1:nome");
    $router->get("/cpf/{cpf}", "Trf1:cpf");
    $router->get("/nomeAdv/{nome}", "Trf1:nomeAdv");
    $router->get("/codOAB/{cod}", "Trf1:codOAB");
    $router->get("/numP rocOrigin/{num}", "Trf1:numProcOrigin");
    
    /* 
    * Rotas TRF4
    */

    $router->group("trf4");
    $router->get("/numProcesso/{numProc}", "Trf4:numProcesso");
    $router->get("/nome/{nome}", "Trf4:nome");
    $router->get("/cpf/{cpf}", "Trf4:cpf");
    $router->get("/nomeAdv/{nome}", "Trf4:numProcChave");
    $router->get("/codOAB/{cod}", "Trf4:codOAB");
    $router->get("/numProcOrigin/{num}", "Trf4:numProcOrigin");
    

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