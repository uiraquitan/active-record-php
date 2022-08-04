<?php
    include_once "./conection.php";
    include_once "./ActiveRecord.php";
    include_once "./Cliente.php";

    $cliente = new Cliente();

    $cliente->nome = "Cliente1";
    $cliente->endereco = "Rua principal";
    $cliente->telefone = "11888888a88b";
    $cliente->id = 2;
    if ($cliente->save()) {
        echo "Registro salvo!";
    } else {
        echo "Registro <b>NÃO FOI</b> salvo!";
    }

    // var_dump($cliente);


    // var_dump($cliente->toArray());
    // // echo $cliente->toJson();
