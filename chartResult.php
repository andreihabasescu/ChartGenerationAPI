<?php
    
function charts(){

    $mysql = new mysqli (
        '192.168.1.11', // locatia serverului (aici, masina locala)
        'asdf',       // numele de cont
        '123456',    // parola (atentie, in clar!)
        'web_project'   // baza de date
        );

    if (mysqli_connect_errno()) {
        die ('Conexiunea a esuat...');
    }

    if (!($rez = $mysql->query ('select username, password from credentials'))) {
        die ('A survenit o eroare la interogare');
    }

    $credentials = array();

    while ($inreg = $rez->fetch_assoc()) {
        $credentials[] = $inreg;
    }
    return json_encode($credentials);
}
    header('Content-Type: application/json');
    $json = charts();
    echo ($json);
?>