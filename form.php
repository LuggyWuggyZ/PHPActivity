
<?php 
  require 'data.inc.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>  
    <title>Fine Art Meme Maker</title>   
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:400,400i,700,800" rel="stylesheet">    
    <link rel="stylesheet" href="css/style.css">
    <script src="js/severactivity.js"></script>
</head>
<body>
    <h1>Fine Art Meme Maker</h1> 

    <section class="grid-container">
        <?php
            foreach ($paintings as $painting) {
                $filename = $painting['filename'];
                    $title = htmlspecialchars($painting['title'], ENT_QUOTES, 'UTF-8'); 
                //outputs thumbnails w width 100
                echo "<img src='result.php?file=$filename&width=100' data-value='$filename' alt='Thumbnail of {$title}'>";
            }
        ?>
    </section>     

    <form action="result.php" method="get">
        <label>Select Base Painting:</label>
        <select name="file" id="whichPainting">
            <?php
                foreach ($paintings as $painting) {
                    $filename = $painting['filename'];
                    $title = htmlspecialchars($painting['title'], ENT_QUOTES, 'UTF-8');
                     //outputs paintng options
                    echo "<option value='$filename'>{$title}</option>";
                }
            ?>
        </select>

        <label>Meme 1 Text:</label>
        <input type="text" name="text1" size=50 value="" placeholder="Enter your first caption" maxlength="500" />
        <label>Meme 1 Font Size:</label>
        12 <input type="range" name="size1" min=12 max=64  value=24 /> 64
        <label>Meme 2 Text:</label>
        <input type="text" name="text2" size=50 value="" placeholder="Enter your second caption" maxlength="500"/>   
        <label>Meme 2 Font Size:</label>
        12 <input type="range" name="size2" min=12 max=48 value=48 /> 48  
        <label>Image Width:</label>
        500 <input type="range" name="width" min=500 max=1000  value=500 /> 1000            

        <label for="height">Image Height (optional; blank matches width):</label>
        <input type="number" id="height" name="height" min="1" max="1000" placeholder="Same as width">

        <input type="submit" value="Click To See Meme" >
    </form>
   
</body>
</html>

