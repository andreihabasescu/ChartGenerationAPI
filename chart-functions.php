<?php

    function createJson($postId,$userID) {
        $mysql = new mysqli (
            '192.168.1.11', // locatia serverului (aici, masina locala)
            'asdf',       // numele de cont
            '123456',    // parola (atentie, in clar!)
            'web_project'   // baza de date
            );
       
            if (mysqli_connect_errno()) {
                die ('Conexiunea a esuat...');
            }
       
           //FOR GENDER
           $queryMales = "SELECT COUNT(*) AS male_count FROM response t INNER JOIN user_info u ON t.id_user = u.id WHERE u.gender = 'male' and t.id_post = $postId;";
           $resultMales = $mysql->query($queryMales);
           $rowMales = $resultMales->fetch_assoc();
           $maleCount = $rowMales['male_count'];
       
           $queryFemales = "SELECT COUNT(*) AS female_count FROM response t INNER JOIN user_info u ON t.id_user = u.id WHERE u.gender = 'female' and t.id_post = $postId;";
           $resultFemales = $mysql->query($queryFemales);
           $rowFemales = $resultFemales->fetch_assoc();
           $femaleCount = $rowFemales['female_count'];
       
           //FOR AGE
           //children (0-12)
           $queryChildren = "SELECT COUNT(*) AS children_count FROM ( SELECT TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE()) AS age FROM response t JOIN user_info u ON t.id_user = u.Id and t.id_post = $postId) AS subquery WHERE age >= 0 AND age <= 12";
           $resultChildren = $mysql->query($queryChildren);
           $rowChildren = $resultChildren->fetch_assoc();
           $childrenCount = $rowChildren['children_count'];
           //teen (13-18)
           $queryTeens = "SELECT COUNT(*) AS teens_count FROM ( SELECT TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE()) AS age FROM response t JOIN user_info u ON t.id_user = u.Id and t.id_post = $postId) AS subquery WHERE age > 12 AND age <= 18";
           $resultTeens = $mysql->query($queryTeens);
           $rowTeens = $resultTeens->fetch_assoc();
           $teensCount = $rowTeens['teens_count'];
           //young adults (19, 35)
           $queryYAdults = "SELECT COUNT(*) AS youngAdults_count FROM ( SELECT TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE()) AS age FROM response t JOIN user_info u ON t.id_user = u.Id and t.id_post = $postId) AS subquery WHERE age > 18 AND age <= 30";
           $resultYAdults = $mysql->query($queryYAdults);
           $rowYAdults = $resultYAdults->fetch_assoc();
           $yadultsCount = $rowYAdults['youngAdults_count'];
           //adults (36,59)
           $queryAdults = "SELECT COUNT(*) AS adults_count FROM ( SELECT TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE()) AS age FROM response t JOIN user_info u ON t.id_user = u.Id and t.id_post = $postId) AS subquery WHERE age > 30 AND age <= 59";
           $resultAdults = $mysql->query($queryAdults);
           $rowAdults = $resultAdults->fetch_assoc();
           $adultsCount = $rowAdults['adults_count'];
           //seniors
           $querySeniors = "SELECT COUNT(*) AS senior_count FROM ( SELECT TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE()) AS age FROM response t JOIN user_info u ON t.id_user = u.Id and t.id_post = $postId) AS subquery WHERE age > 59 ";
           $resultSeniors = $mysql->query($querySeniors);
           $rowSeniors = $resultSeniors->fetch_assoc();
           $seniorCount = $rowSeniors['senior_count'];
       
           //FOR EMOTIONS
           $queryEmotions = "SELECT anger, anticipation, disgust, fear, joy, sadness, surprise, trust FROM response where id_post=$postId;";
           $resultEmotions = $mysql->query($queryEmotions);
           
           $emotions = ['anger', 'anticipation', 'disgust', 'fear', 'joy', 'sadness', 'surprise', 'trust'];
           $intensities = [1,2,3];
           $data = [];

            //FOR TIME
            date_default_timezone_set('Europe/Bucharest');
            $set_date = date('Y-m-d H:i:s');

            $queryTime = "SELECT TIMESTAMPDIFF(MINUTE, p.start_date, r.time_of_submit) AS time_difference
            FROM posts_table p
            JOIN response r ON p.id_post = r.id_post where p.id_post = $postId;";
            $resultTime = $mysql->query($queryTime);
            
            $queryTimeFromUser = "SELECT (-1)*TIMESTAMPDIFF(MINUTE, end_date, start_date) AS this FROM posts_table WHERE id_post = $postId;";
            $resultTimeFromUser = $mysql->query($queryTimeFromUser);
            
            $aux = $resultTimeFromUser->fetch_assoc();
            $TotalInterval = intval($aux["this"]);
            //var_dump($TotalInterval);

            $step = intval($TotalInterval / 10); //representing 60 minutes
            $intervals = [];

            for($i = $step; $i < $TotalInterval; $i += $step){
                //var_dump($i);
                $intervals[] =  $i;
            }

           while($row = $resultEmotions->fetch_assoc()){
            foreach($emotions as $emotion){
                foreach($intensities as $intensity){
                    if ($row[$emotion] == $intensity) {
                        $count = 1;
                    } else {
                        $count = 0;
                    }
    
                    if (isset($data[$emotion][$intensity])) {
                        $data[$emotion][$intensity] += $count;
                    } else {
                        $data[$emotion][$intensity] = $count;
                    }
                }
            }
        }

        $chartDataGender = [
          ['Male', $maleCount],
          ['Female', $femaleCount]
        ];
    
        $chartDataAge = [
          ['Children', $childrenCount],
          ['Teens', $teensCount],
          ['Young Adults', $yadultsCount],
          ['Adults', $adultsCount],
          ['Seniors', $seniorCount]
        ];
        
        $chartDataEmotions = [
            'labels' => $emotions,
            'datasets' => [],
        ];

        $chartDataTime = [
            'labels' => $intervals,
            'datasets' => [0,0,0,0,0,0,0,0,0,0]
        ];

        foreach($intensities as $intensity){
            if($intensity == 1){
                $col = "#51EAEA";
                $name = "Not really";
            }else if($intensity == 2){
                $col = "#FCDDB0";
                $name = "Yes";
            }else {
                $col = "#FF9D76";
                $name = "Very much";
            }
    
    
            $dataset=[
                'label' => $name,
                'data' => [],
                'backgroundColor' => $col
            ];
            
            foreach ($emotions as $emotion) {
                if (isset($data[$emotion][$intensity])) {
                    $dataset['data'][] = $data[$emotion][$intensity];
                } else {
                    $dataset['data'][] = 0;
                }
            }
    
        $chartDataEmotions['datasets'][] = $dataset;
        }
        
        while($row = $resultTime->fetch_assoc()){
            $timeDifference = $row['time_difference'];
            foreach($intervals as $interval){
                $chartDataTime['label'] = $interval;
                if ((intval($timeDifference) > $interval - $step) && (intval($timeDifference) <= $interval)){
                    $chartDataTime['datasets'][floor($interval/$step)-1]++;
                    break;
                }
           }
        }

        $genderJson = json_encode($chartDataGender);
        $ageJson = json_encode($chartDataAge);
        $emotionJson = json_encode($chartDataEmotions);
        $timeJson = json_encode($chartDataTime);

        $myfile = fopen($_SERVER['DOCUMENT_ROOT'].'/ChartGenerationAPI/output/'.$postId.'_'.$userID.'.json', "w");
        $content = '['.$genderJson.','.$ageJson.','.$emotionJson.','.$timeJson.']';
        $ret = $genderJson.'%%%'.$ageJson.'%%%'.$emotionJson.'%%%'.$timeJson; //return data for Chart Generation
        fwrite($myfile,$content);
        fclose($myfile);

        return $ret;
    }

    function buildCSV($json,$postID,$userID) {
        list($genderJson,$ageJson,$emotionJson,$timeJson) = explode("%%%",$json);
        $arr = array();
        array_push($arr,$genderJson);
        array_push($arr,$ageJson);
        array_push($arr,$emotionJson);
        array_push($arr,$timeJson);
        
        $csv = 'output/'.$postID.'_'.$userID.'.csv';
    
        $file_pointer = fopen($csv, 'w');

        foreach($arr as $jsn) {
            $jsonans = json_decode($jsn, true);
            //var_dump($jsonans);
            if (is_array($jsn)) {
                foreach($jsonans as $i) {            
                    fputcsv($file_pointer, $i);
                }
            } else {
                echo('ERRRRRRRR HERRR '.$jsn);
                $arr1 = array($jsn);
                fputcsv($file_pointer, $arr1);
            }
        }
        
        fclose($file_pointer);

    }

    function buildChart($postID,$userID) {
        $str = createJson($postID,$userID);

        buildCSV($str,$postID,$userID);

        list($genderJson,$ageJson,$emotionJson,$timeJson) = explode("%%%",$str);

        $genderData = json_decode($genderJson);
        $ageData = json_decode($ageJson);
        $emotionData = json_decode($emotionJson);
        $timeData = json_decode($timeJson);

        require 'chart-view.php';

        $path = $postID.'_'.$userID;
        return $path;
    }

    function buildZip($fileName) {
        $zip = new ZipArchive();
        $path = 'compressed/'.$fileName.'.zip';

        $link = "error_creating_zip";

        if ($zip->open($path, ZipArchive::CREATE)) {
            $zip->addFile('output/'.$fileName.'.html',$fileName.'.html');
            $zip->addFile('output/'.$fileName.'.json',$fileName.'.json');
            $zip->close();

            $link = __DIR__.'/compressed/'.$fileName.'.zip';
        } 

        return $link;
    }

    $postID = $_GET['postID'];
    $userID = $_GET['userID'];

    if ($postID=="" || $userID=="") {
        $ret = "error: you must log in to access the API";

        $arr = array('response' => $ret);
        echo json_encode($arr);
    } else {
        $json_data = buildChart($postID,$userID);
        $link = buildZip($json_data);

        $arr = array('link' => $link);

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($arr);
    }
?>