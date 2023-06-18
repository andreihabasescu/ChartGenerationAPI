<?php
    echo("STARTED CHART CREATION");
    ob_start();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Chart Example</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        html, body {
            margin: 0;
            height: 100%;
        }
        body {
            text-align: center;
        }
        .chart-container-gender {
            width: 350px;
            height: 350px;
            margin: 0 auto 20px;
        }
        .chart-container-age {
            width: 400px;
            height: auto;
            margin: 0 auto 20px;
            margin-top: 60px;
        }
        .chart-container-emotions {
            width: 500px;
            height: 500px;
            margin: 0 auto 20px;
        }
        
    </style>
</head>
<body>
<div class="chart-container-gender">
    <p>Gender comparison graph based on the answers:</p>
    <canvas id="GenderChart"></canvas>
</div>
<div class="chart-container-age">
    <p>Age distribution graph based on the answers:</p>
    <canvas id="AgeChart"></canvas>
</div>
<div class="chart-container-emotions">
    <p>Emotions distribution graph based on the answers:</p>
    <canvas id="EmotionChart"></canvas>
</div>


<script>
    var genderCtx = document.getElementById('GenderChart').getContext('2d');
    var ageCtx = document.getElementById('AgeChart').getContext('2d');
    var emotionCtx = document.getElementById('EmotionChart').getContext('2d');
  
    var genderData = <?php echo json_encode($genderData); ?>;
    var ageData = <?php echo json_encode($ageData); ?>;
    var emotionData = <?php echo json_encode($emotionData); ?>;
    console.log(emotionData);
    var genderChart = new Chart(genderCtx, {
        type: 'pie',
        data: {
            labels: genderData.map(item => item[0]),
            datasets: [{
                data: genderData.map(item => item[1]),
                backgroundColor: ['rgba(75, 192, 192, 0.2)', 'rgba(255, 99, 132, 0.2)'],
                borderColor: ['rgba(75, 192, 192, 1)', 'rgba(255, 99, 132, 1)'],
                borderWidth: 1
            }]
        },
        options: {
            // Customize chart options as needed
        }
    });

    var ageChart = new Chart(ageCtx, {
            type: 'bar',
            data: {
                labels: ageData.map(item => item[0]),
                datasets: [{
                    label: 'Count',
                    data: ageData.map(item => item[1]),
                    backgroundColor: 'rgba(75, 192, 192, 0.5)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
                }]
            },
            options: {
               // Customize chart options as needed
            }
        });
    
    var emotionChart =  new Chart(emotionCtx, {
            type: 'bar',
            data: emotionData,
            options: {
                scales: {
                    x: {
                        stacked: true,
                    },
                    y: {
                        stacked: true,
                    },
                },
                // Customize chart options as needed
            },
        });
  
</script>

</body>
</html>

<?php
    $content = ob_get_clean();
    if(!file_put_contents(__DIR__.'/'.$postID.'_'.$userID.'.html', $content)) echo 'Unable to save: '.$postID.'_'.$userID;
?>