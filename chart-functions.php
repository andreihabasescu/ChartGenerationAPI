<?php

    function createJson($postId,$userID) {
        $mysql = new mysqli (
            'localhost', // locatia serverului (aici, masina locala)
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
           $queryEmotions = "SELECT anger, anticipation, disgust, fear, joy, sadness, surprise, trust FROM response;";
           $resultEmotions = $mysql->query($queryEmotions);
           
           $emotions = ['anger', 'anticipation', 'disgust', 'fear', 'joy', 'sadness', 'surprise', 'trust'];
           $intensities = [1,2,3];
           $data = [];

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

         //FOR TIME
            $set_date = date('Y-m-d H:i:s', strtotime('2023-06-14 23:00:00'));

            $queryTime = "SELECT (-1)*TIMESTAMPDIFF(MINUTE, date, CONCAT(DATE(date), ' 23:00:00')) AS time_difference FROM test_table WHERE id_post = 4;";
            $resultTime = $mysql->query($queryTime);

            $timeInterval = 60; //representing 60 minutes
            $step = 5; //cat dureaza un interval
            $intervals = [];

            for($i = $step; $i <= $timeInterval; $i += $step){
                $intervals[] =  $i;
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
            'labels' => [],
            'datasets' => [],
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

        while ($row = $resultTime->fetch_assoc()) {
            $timeDifference = $row['time_difference'];
            foreach ($intervals as $interval) {
                if (intval($timeDifference) <= $interval && intval($timeDifference) > $interval - 5) {
                    //var_dump($interval);
                    if (!isset($chartDataTime['datasets'][$interval])) {
                        $chartDataTime['datasets'][$interval] = 1;
                    } else {
                        $chartDataTime['datasets'][$interval]++;
                    }
                }
            }
        }


        $genderJson = json_encode($chartDataGender);
        $ageJson = json_encode($chartDataAge);
        $emotionJson = json_encode($chartDataEmotions);
        $timeJson = json_encode($chartDataTime);

        $myfile = fopen($_SERVER['DOCUMENT_ROOT'].'/WebProject23/output/'.$postId.'_'.$userID.'.json', "w");
        $content = $genderJson.'%%%'.$ageJson.'%%%'.$emotionJson.'%%%'.$timeJson;
        fwrite($myfile,$content);
        fclose($myfile);

        return $content;
    }

    function buildChart($postID,$userID) {
            $str = createJson($postID,$userID);//file_get_contents($_SERVER['DOCUMENT_ROOT'].'/ChartGenerationAPI/output/'.$postID.'_'.$userID.'.json',true);

            list($genderJson,$ageJson,$emotionJson,$timeJson) = explode("%%%",$str);

            $genderData = json_decode($genderJson);
            $ageData = json_decode($ageJson);
            $emotionData = json_decode($emotionJson);
            $timeData = json_decode($timeJson);

            require 'chart-view.php';
    }


    buildChart($postID,$userID);
?>