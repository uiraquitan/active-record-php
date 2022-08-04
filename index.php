<?php
include_once "./conection.php";
include_once "./ActiveRecord.php";
include_once "./Cliente.php";

$cliente = new Cliente();

// $cliente->nome = "carlinha";
// $cliente->endereco = "Jardin atlantico";
// $cliente->telefone = "8125448618";
// // $cliente->id = 2;
// if ($cliente->save()) {
//     echo "Registro salvo!";
// } else {
//     echo "Registro <b>NÃO FOI</b> salvo!";
// }

// $cliente = Cliente::findFirst("nome = 'carlinha'");

// var_dump($cliente);

// if($cliente->delete()){
//     echo "deletado com sucesso";
// }else{
//     echo "Erro ao deletar";
// }
   


    // var_dump($cliente->toArray());
    // // echo $cliente->toJson();

    $recents = $cliente->listRecents(2);
    var_dump($recents);
    var_dump($cliente->count());

