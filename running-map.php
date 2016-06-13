<!DOCTYPE html>
<html lang='en'>
<head>
  <meta charset='UTF-8'>
  <title>RUNNING MAP</title>
  <script src='https://api.mapbox.com/mapbox-gl-js/v0.19.1/mapbox-gl.js'></script>
	<link href='https://api.mapbox.com/mapbox-gl-js/v0.19.1/mapbox-gl.css' rel='stylesheet'/>
  <link rel='stylesheet' href='https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css' integrity='sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7' crossorigin='anonymous'>
  <link rel='stylesheet' href='http://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css'>
  <link rel='stylesheet' href='css/run.css'>
</head>
<body>
  <?php
    $conn = mysqli_connect('www.nbaramichai.com', 'running', 'iloveeatingafterrun', 'running');
    mysqli_query($conn, 'SET NAMES UTF8MB4');
    if (!$conn) {
      die(mysqli_connect_error());
    } else {
      $query = 'SELECT * FROM events';
      if ($result = mysqli_query($conn, $query)) {
        $features = [];
        while ($row = mysqli_fetch_assoc($result)) {
          $id = $row['id'];
          $name = $row['name'];
          $date = $row['date'];
          $link = $row['link'];
          $link_display = '';
          if (strlen($link) > 40) {
            $link_display = substr($link, 0, 40) . '...';
          } else {
            $link_display = $link;
          }

          $latitude = $row['latitude'];
          $longitude = $row['longitude'];
          $type = $row['type'];

          $distance = $row['distance'];
          if ($distance == '-') {
            $distance_display = '- KM';
          } else {
            $distance_display = str_replace('/', 'KM / ', $distance) . 'KM';
          }

          $date_time = strtotime($date);
          $date_display = Date('d F Y', $date_time);

          $description = "<div class='popup'>";
          $description .= "<p class='title'>$name</p>";
          $description .= "<p>Date : $date_display</p>";
          $description .= "<p>Distance : $distance_display</p>";
          $description .= "<p class=\"read-more\"><a href=\"#\" onclick=\"showMoreInfo($id);return false\">Read more</a></p>";
          $description .= "</div>";

          $geometry = ['type' => 'Point', 'coordinates' => [$longitude, $latitude]];
          $properties = ['id' => $id, 'title' => $name, 'description' => $description, 'name' => $name,
                         'date' => $date, 'dateDisplay' => $date_display, 'distance' => $distance,
                         'distanceDisplay' => $distance_display, 'type' => $type, 'link' => $link,
                         'linkDisplay' => $link_display];

          $feature = ['type' => 'Feature', 'geometry' => $geometry, 'properties' => $properties];
          $features[] = $feature;
        }
        $data = ['type' => 'FeatureCollection', 'features' => $features];
        $data = json_encode($data);
      } else {
        die(mysqli_error($conn));
      }
    }
  ?>

  <!-- <div id='control-panel'>
    <div class='form-group'>
      <label for='from-date'>From Date</label>
      <input type='text' class='form-control' id='from-date' placeholder='DD/MM/YYYY eg. 01/01/2016'>
    </div>
    <div class='form-group'>
      <label for='to-date'>To Date</label>
      <input type='text' class='form-control' id='to-date' placeholder='DD/MM/YYYY eg. 01/12/2016'>
    </div>
  </div> -->

  <div class='info-panel' id='info-panel'>
    <div id='info-container'>
      <h4 id='info-title'></h4>
      <p id='info-date'></p>
      <p id='info-distance'></p>
      <p id='info-link'></p>
    </div>
  </div>

  <div id='right-button-container' class='right-button-container'>
    <div id='search-button' class='right-button'>
      <!-- <img src='images/search.png' alt='Search'> -->
      <p id='search-icon' class='ion-ios-search-strong'>
    </div>
    <div id='bookmark-button' class='right-button'>
      <!-- <img src='images/star.png' alt='Bookmark'> -->
      <p id='bookmark-icon' class='ion-star'>
    </div>
  </div>

  <div id='right-panel' class='right-panel'>
    <div id='right-panel-header'>
      <p id='right-panel-header-text'></p>
    </div>
    <div id='search-panel' class='right-panel-content'>
      <form class='search'>
        <div class='form-group'>
          <label for='search-keyword'>Search by keyword :</label>
          <input type='text' class='form-control' id='search-keyword' placeholder='keyword...'>
        </div>
        <div class='form-group'>
          <label for='search-date'>Search by date :</label>
          <input type='text' class='form-control' id='search-date' placeholder='date (e.g. 1/8/16 - 1/9/16)'>
        </div>
        <div class='form-group'>
          <label>Search by distance :</label>
          <p><input class='search-distance' type='checkbox' name='distance' value='fun' checked><span class='checkbox-label'>Fun Run (&lt;10KM)</span></p>
          <p><input class='search-distance' type='checkbox' name='distance' value='mini' checked><span class='checkbox-label'>Mini Marathon (10KM)</span></p>
          <p><input class='search-distance' type='checkbox' name='distance' value='half' checked><span class='checkbox-label'>Half Marathon (21.1KM)</span></p>
          <p><input class='search-distance' type='checkbox' name='distance' value='full' checked><span class='checkbox-label'>Marathon (42.195KM)</span></p>
          <p><input class='search-distance' type='checkbox' name='distance' value='ultra' checked><span class='checkbox-label'>Ultra Marathon (&gt;42.195KM)</span></p>
        </div>
        <div class='form-group'>
          <label>Search by distance :</label>
          <p><input class='search-type' type='checkbox' name='type' value='road' checked><span class='checkbox-label'>Road Running</span></p>
          <p><input class='search-type' type='checkbox' name='type' value='trail' checked><span class='checkbox-label'>Trail Running</span></p>
          <p><input class='search-type' type='checkbox' name='type' value='tri' checked><span class='checkbox-label'>Triathlon</span></p>
          <p><input class='search-type' type='checkbox' name='type' value='others' checked><span class='checkbox-label'>Others</span></p>
        </div>
        <input id='search-submit-button' class='btn btn-primary' type='button' value='Search' onclick='filterMarkers()'>
      </form>
    </div>
    <div id='bookmark-panel' class='right-panel-content'>
      <p>Coming Soon...</p>
    </div>
  </div>

  <div id='map'></div>
  <script type='application/javascript' src='js/run.js'></script>
  <?php echo "<script>initial($data);</script>"; ?>
</body>
</html>